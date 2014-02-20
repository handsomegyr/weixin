<?php
namespace Weixin\PayManager;
use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 微信支付接口
 * 为了更好地接入支付的整个流程，
 * 包括购买、通知、发货等，微信提供了一系列的支付相关API，以供第三方调用。
 * 
 * 注意：appSecret、paySignKey、partnerKey 是验证商户唯一性的安全标识，请妥善保管。
	对于appSecret 和paySignKey 的区别，可以这样认为：appSecret 是API 使用时的登录密码，会在网络中传播的；
	而paySignKey 是在所有支付相关数据传输时用于加密并进行身份校验的密钥，
	仅保留在第三方后台和微信后台，不会在网络中传播。
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinPayManager
{
	protected  $weixin;
	private $_url = 'https://api.weixin.qq.com/cgi-bin/pay/';
	
	//paySignKey 公众号支付请求中用于加密的密钥Key，可验证商户唯一身份，PaySignKey对应于支付场景中的appKey 值。
	public $paySignKey="";
	public function setPaySignKey($paySignKey)
	{
		$this->paySignKey = $paySignKey;
	}
	//partnerId 财付通商户身份标识。
	public $partnerId="";
	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
	}
	
	//partnerKey财付通商户权限密钥Key。
	public $partnerKey="";
	public function setPartnerKey($partnerKey)
	{
		$this->partnerKey = $partnerKey;
	}
	
	public function __construct(WeixinClient $weixin,$options=array()) {
		$this->weixin    = $weixin;
		//支付相关的Options
		if(empty($options['pay'])){
			$this->paySignKey=empty($options['pay']['paySignKey'])?"":$options['pay']['paySignKey'];
			$this->partnerId=empty($options['pay']['partnerId'])?"":$options['pay']['partnerId'];
			$this->partnerKey=empty($options['pay']['partnerKey'])?"":$options['pay']['partnerKey'];
		}
	}

	/**
	 * 发货通知delivernotify
	 * 为了更好地跟踪订单的情况，需要第三方在收到最终支付通知之后，调用发货通知API
	 * 告知微信后台该订单的发货状态。
	 * 请在收到支付通知发货后，一定调用发货通知接口，否则可能影响商户信誉和资金结算。
	 * @return mixed
	 */
	public function delivernotify($openid,$transid,$out_trade_no,$deliver_timestamp,$deliver_status,$deliver_msg)
	{
		//接口调用请求说明
		//Api 的url 为：https://api.weixin.qq.com/pay/delivernotify?access_token=xxxxxx
		//Url 中的参数只包含目前微信公众平台凭证access_token，而发货通知的真正的数据是
		//放在PostData 中的，格式为json，如下：
		//{
		//	"appid" : "wwwwb4f85f3a797777",
		//	"openid" : "oX99MDgNcgwnz3zFN3DNmo8uwa-w",
		//	"transid" : "111112222233333",
		//	"out_trade_no" : "555666uuu",
		//	"deliver_timestamp" : "1369745073",
		//	"deliver_status" : "1",
		//	"deliver_msg" : "ok",
		//	"app_signature" : "53cca9d47b883bd4a5c85a9300df3da0cb48565c",
		//	"sign_method" : "sha1"
		//}
		//其中，
		//appid 是公众平台账户的AppId；
		//openid 是购买用户的OpenId，这个已经放在最终支付结果通知的PostData 里了；
		//transid 是交易单号；
		//out_trade_no 是第三方订单号；
		//deliver_timestamp 是发货时间戳，这里指得是linux 时间戳；
		//deliver_status 是发货状态，1 表明成功，0 表明失败，失败时需要在deliver_msg 填上失败原因；
		//deliver_msg 是发货状态信息，失败时可以填上UTF8 编码的错误提示信息，比如“该商品已退款”；
		//app_signature 依然是根据1 中所讲的签名方式生成的签名，
		//参加签名字段为：appid、appkey、openid、transid、out_trade_no、deliver_timestamp、deliver_status、deliver_msg；
		//sign_method 是签名方法（不计入签名生成）；
		
		
		//参数	说明
		//access_token	 调用接口凭证
		$access_token = $this->weixin->getToken('access_token');
		$postData = array();
		$postData["appid"]=$this->weixin->getAppid();
		$postData["openid"]=$openid;
		$postData["transid"]=$transid;
		$postData["out_trade_no"]=$out_trade_no;
		$postData["deliver_timestamp"]=$deliver_timestamp;
		$postData["deliver_status"]=$deliver_status;
		$postData["deliver_msg"]=$deliver_msg;		

		//获取app_signature
		$para =array(
				"appid"=>$postData["appid"],
				"appkey"=>$this->paySignKey,
				"openid"=>$postData["openid"],
				"transid"=>$postData["transid"],
				"out_trade_no"=>$postData["out_trade_no"],
				"deliver_timestamp"=>$postData["deliver_timestamp"],
				"deliver_status"=>$postData["deliver_status"],
				"deliver_msg"=>$postData["deliver_msg"]);
		$postData["app_signature"]=$this->getPaySign($para);
		$postData["sign_method"]="sha1";
		
		$json = json_encode($postData,JSON_UNESCAPED_UNICODE);
		$rst = $this->weixin->post($this->_url.'delivernotify?access_token='.$access_token, $json);		
		if(!empty($rst['errcode']))
		{
			//如果有异常，会在errcode 和errmsg 描述出来。
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			//返回说明 正常时的返回JSON数据包示例：
			//微信公众平台在校验ok 之后，会返回数据表明是否通知成功，例如：
			//{"errcode":0,"errmsg":"ok"}
			return $rst;
		}
	}
	
	/**
	 * 订单查询 orderquery
	 * 因为某一方技术的原因，可能导致商家在预期时间内都收不到最终支付通知，
	 * 此时商家可以通过该API 来查询订单的详细支付状态。
	 * @return mixed
	 */
	public function orderquery($out_trade_no)
	{
		//接口调用请求说明
		//Api 的url 为： https://api.weixin.qq.com/pay/orderquery?access_token=xxxxxx
		//Url 中的参数只包含目前微信公众平台凭证access_token，而发货通知的真正的数据是
		//放在PostData 中的，格式为json，如下：
		//{
		//	"appid" : "wwwwb4f85f3a797777",
		//	"package" : "out_trade_no=11122&partner=1900090055&sign=4e8d0df3da0c3d0df38f",
		//	"timestamp" : "1369745073",
		//	"app_signature" : "53cca9d47b883bd4a5c85a9300df3da0cb48565c",
		//	"sign_method" : "sha1"
		//}
		//其中，
		//appid 是公众平台账户的AppId；
		//package 是查询订单的关键信息数据，包含第三方唯一订单号out_trade_no、财付通商户
		//身份标识partner（即前文所述的partnerid） 、签名sign，其中sign 是对参数字典序排序并
		//使用&联合起来，最后加上&key=partnerkey（唯一分配），进行md5 运算，再转成全大写，
		//最终得到sign，对于示例，就是：sign=md5（out_trade_no=11122&partner=1900090055&key=xxxxxx）.toupper；
		//timestamp 是linux 时间戳；
		//app_signature 依然是根据1 中所讲的签名方式生成的签名，
		//参加签名字段为：appid、appkey、package、timestamp；
		//sign_method 是签名方法（不计入签名生成）；		
		
		//参数	说明
		//access_token	 调用接口凭证
		$access_token = $this->weixin->getToken('access_token');
		$postData = array();
		$postData["appid"]=$this->weixin->getAppid();
		
		//获取package
		$para =array("out_trade_no"=>$out_trade_no,"partner"=>$this->partnerId);
		$package = $this->getPackage($para);	
		$postData["package"]=$package;
		
		$postData["timestamp"]=time();
		//获取app_signature
		$para =array(
				"appid"=>$postData["appid"],
				"appkey"=>$this->paySignKey,
				"package"=>$postData["package"],
				"timestamp"=>$postData["timestamp"]);
		$postData["app_signature"]=$this->getPaySign($para);
		$postData["sign_method"]="sha1";
	
		$json = json_encode($postData,JSON_UNESCAPED_UNICODE);
		$rst = $this->weixin->post($this->_url.'delivernotify?access_token='.$access_token, $json);
		if(!empty($rst['errcode']))
		{
			//如果有异常，会在errcode 和errmsg 描述出来。
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			//返回说明 正常时的返回JSON数据包示例：
			//微信公众平台在校验ok 之后，会返回数据表明是否通知成功，例如：
			//{"errcode":0,"errmsg":"ok", ...... }
			//如果查询成功，会返回详细的订单数据，如下：
			//{
			//	"errcode":0,
			//	"errmsg":"ok",
			//	"order_info":
			//	{
			//		"ret_code":0,
			//		"ret_msg":"",
			//		"input_charset":"GBK",
			//		"trade_state":"0",
			//		"trade_mode":"1",
			//		"partner":"1900000109",
			//		"bank_type":"CMB_FP",
			//		"bank_billno":"207029722724",
			//		"total_fee":"1",
			//		"fee_type":"1",
			//		"transaction_id":"1900000109201307020305773741",
			//		"out_trade_no":"2986872580246457300",
			//		"is_split":"false",
			//		"is_refund":"false",
			//		"attach":"",
			//		"time_end":"20130702175943",
			//		"transport_fee":"0",
			//		"product_fee":"1",
			//		"discount":"0",
			//		"rmb_total_fee":""
			//		}
			//	}
			//对于详细的订单信息，放在order_info 中的json 数据中，各个字段的含义如下：
			//ret_code 是查询结果状态码，0 表明成功，其他表明错误；
			//ret_msg 是查询结果出错信息；
			//input_charset 是返回信息中的编码方式；
			//trade_state 是订单状态，0 为成功，其他为失败；
			//trade_mode 是交易模式，1 为即时到帐，其他保留；
			//partner 是财付通商户号，即前文的partnerid；
			//bank_type 是银行类型；
			//bank_billno 是银行订单号；
			//total_fee 是总金额，单位为分；
			//fee_type 是币种，1 为人民币；
			//transaction_id 是财付通订单号；
			//out_trade_no 是第三方订单号；
			//is_split 表明是否分账，false 为无分账，true 为有分账；
			//is_refund 表明是否退款，false 为无退款，ture 为退款；
			//attach 是商家数据包，即生成订单package 时商家填入的attach；
			//time_end 是支付完成时间；
			//transport_fee 是物流费用，单位为分；
			//product_fee 是物品费用，单位为分；
			//discount 是折扣价格，单位为分；
			//rmb_total_fee 是换算成人民币之后的总金额，单位为分，一般看total_fee 即可。
			return $rst;
		}
	}
	
	/*
	 * package 生成方法
	*/
	protected function getPackage(array $para)
	{
		if(empty($this->partnerKey)){
			throw new Exception('partnerKey is empty');
		}
		//由于package 中携带了生成订单的详细信息，因此在微信将对package 里面的内容进行鉴权，
		//确定package 携带的信息是真实、有效、合理的。因此，这里将定义生成package 字符串的方法。
		//a.对所有传入参数按照字段名的ASCII 码从小到大排序（字典序）后，
		//使用URL 键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1；
		//除去数组中的空值和签名参数
		$paraFilter = Helpers::paraFilter($para);
		//对数组排序
		$paraFilter = Helpers::argSort($paraFilter);
		$string1 = implode('&',$paraFilter);
		
		//b. 在string1 最后拼接上key=paternerKey 得到stringSignTemp 字符串， 
		//并对stringSignTemp 进行md5 运算，再将得到的字符串所有字符转换为大写，得到sign 值signValue。
		$sign = string1.'&key='.$this->partnerKey;
		$sign = strtoupper(md5($sign));
		
		//c.对string1 中的所有键值对中的value 进行urlencode 转码，按照a 步骤重新拼接成字符串，得到string2。
		//对于js 前端程序，一定要使用函数encodeURIComponent 进行urlencode编码（注意！进行urlencode时要将空格转化为%20而不是+）。
		array_walk($paraFilter, function($val,$key) use(&$paraFilter){
			$paraFilter[$key] = urlencode($val);
		});
		$string2 = implode('&',$paraFilter);
		
		//d.将sign=signValue 拼接到string2 后面得到最终的package 字符串。				
		$package=$string2.'&sign='.$sign;
		return $package;
	}
	
	/*
	 * 支付签名（paySign）生成方法
	 */
	protected function getPaySign(array $para)
	{
		if(empty($this->paySignKey)){
			throw new Exception('paySignKey is empty');
		}
			
		//对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，
		//使用URL 键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1。
		//这里需要注意的是所有参数名均为小写字符，例如appId 在排序后字符串则为appid；
		
		//将所有key改为小写字符
		$paraFilter=array();
		array_walk($paraFilter, function($val,$key) use($para){
			$paraFilter[strtolower($key)] = $val;
		});		
		//除去数组中的空值和签名参数
		$paraFilter = Helpers::paraFilter($paraFilter);
		//对数组排序
		$paraFilter = Helpers::argSort($paraFilter);
		$string1 = implode('&',$paraFilter);
		
		//对string1 作签名算法，字段名和字段值都采用原始值（此时package 的value 就对应了
		//使用2.6 中描述的方式生成的package），不进行URL 转义。
		//具体签名算法为paySign =SHA1(string1)。
		$paySign=sha1($string1);
		return $paySign;
	}
}
