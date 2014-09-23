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
 * 对于appSecret 和paySignKey 的区别，可以这样认为：appSecret 是API 使用时的登录密码，会在网络中传播的；
 * 而paySignKey 是在所有支付相关数据传输时用于加密并进行身份校验的密钥，
 * 仅保留在第三方后台和微信后台，不会在网络中传播。
 * 
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinPayManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/';
    
    // appId公众号身份标识。
    private $appId = "";

    public function getAppId()
    {
        if (empty($this->appId)) {
            throw new \Exception('AppId未设定');
        }
        return $this->appId;
    }
    
    // appSecret公众平台API(参考文档API 接口部分)的权限获取所需密钥Key，在使用所有公众平台API 时，都需要先用它去换取access_token，然后再进行调用。
    private $appSecret = "";

    public function getAppSecret()
    {
        if (empty($this->appSecret)) {
            throw new \Exception('AppSecret未设定');
        }
        return $this->appSecret;
    }
    
    // paySignKey 公众号支付请求中用于加密的密钥Key，可验证商户唯一身份，PaySignKey对应于支付场景中的appKey 值。
    private $paySignKey = "";

    public function setPaySignKey($paySignKey)
    {
        $this->paySignKey = $paySignKey;
    }

    public function getPaySignKey()
    {
        if (empty($this->paySignKey)) {
            throw new \Exception('PaySignKey未设定');
        }
        return $this->paySignKey;
    }
    
    // partnerId 财付通商户身份标识。
    private $partnerId = "";

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
    }

    public function getPartnerId()
    {
        if (empty($this->partnerId)) {
            throw new \Exception('PartnerId未设定');
        }
        return $this->partnerId;
    }
    // partnerKey财付通商户权限密钥Key。
    private $partnerKey = "";

    public function setPartnerKey($partnerKey)
    {
        $this->partnerKey = $partnerKey;
    }

    public function getPartnerKey()
    {
        if (empty($this->partnerKey)) {
            throw new \Exception('PartnerKey未设定');
        }
        return $this->partnerKey;
    }

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
        $this->appId = $this->weixin->getAppId();
        $this->appSecret = $this->weixin->getAppSecret();
        
        // 支付相关的Options
        if (isset($options['pay'])) {
            $this->paySignKey = empty($options['pay']['paySignKey']) ? "" : $options['pay']['paySignKey'];
            $this->partnerId = empty($options['pay']['partnerId']) ? "" : $options['pay']['partnerId'];
            $this->partnerKey = empty($options['pay']['partnerKey']) ? "" : $options['pay']['partnerKey'];
        }
    }

    /**
     * 发货通知delivernotify
     * 为了更好地跟踪订单的情况，需要第三方在收到最终支付通知之后，调用发货通知API
     * 告知微信后台该订单的发货状态。
     * 请在收到支付通知发货后，一定调用发货通知接口，否则可能影响商户信誉和资金结算。
     *
     *
     * @param string $openid            
     * @param string $transid            
     * @param string $out_trade_no            
     * @param string $deliver_timestamp            
     * @param string $deliver_status            
     * @param string $deliver_msg            
     * @param string $sign_method            
     * @throws WeixinException
     * @return Ambigous <mixed, string>
     */
    public function delivernotify($openid, $transid, $out_trade_no, $deliver_timestamp, $deliver_status, $deliver_msg, $sign_method = 'sha1')
    {
        /**
         * 接口调用请求说明
         * Api 的url
         * 为：https://api.weixin.qq.com/pay/delivernotify?access_token=xxxxxx
         * Url 中的参数只包含目前微信公众平台凭证access_token，而发货通知的真正的数据是
         * 放在PostData 中的，格式为json，如下：
         * {
         * "appid" : "wwwwb4f85f3a797777",
         * "openid" : "oX99MDgNcgwnz3zFN3DNmo8uwa-w",
         * "transid" : "111112222233333",
         * "out_trade_no" : "555666uuu",
         * "deliver_timestamp" : "1369745073",
         * "deliver_status" : "1",
         * "deliver_msg" : "ok",
         * "app_signature" : "53cca9d47b883bd4a5c85a9300df3da0cb48565c",
         * "sign_method" : "sha1"
         * }
         * 其中，
         * appid 是公众平台账户的AppId；
         * openid 是购买用户的OpenId，这个已经放在最终支付结果通知的PostData 里了；
         * transid 是交易单号；
         * out_trade_no 是第三方订单号；
         * deliver_timestamp 是发货时间戳，这里指得是linux 时间戳；
         * deliver_status 是发货状态，1 表明成功，0 表明失败，失败时需要在deliver_msg 填上失败原因；
         * deliver_msg 是发货状态信息，失败时可以填上UTF8 编码的错误提示信息，比如“该商品已退款”；
         * app_signature 依然是根据1 中所讲的签名方式生成的签名，
         * 参加签名字段为：appid、appkey、openid、transid、out_trade_no、deliver_timestamp、deliver_status、deliver_msg；
         * sign_method 是签名方法（不计入签名生成）；
         */
        
        // 参数 说明
        // access_token 调用接口凭证
        $access_token = $this->weixin->getToken('access_token');
        $postData = array();
        $postData["appid"] = $this->getAppId();
        $postData["openid"] = $openid;
        $postData["transid"] = $transid;
        $postData["out_trade_no"] = $out_trade_no;
        $postData["deliver_timestamp"] = $deliver_timestamp;
        $postData["deliver_status"] = $deliver_status;
        $postData["deliver_msg"] = $deliver_msg;
        
        // 获取app_signature
        $para = array(
            "appid" => $postData["appid"],
            "appkey" => $this->getPaySignKey(),
            "openid" => $postData["openid"],
            "transid" => $postData["transid"],
            "out_trade_no" => $postData["out_trade_no"],
            "deliver_timestamp" => $postData["deliver_timestamp"],
            "deliver_status" => $postData["deliver_status"],
            "deliver_msg" => $postData["deliver_msg"]
        );
        
        $postData["app_signature"] = $this->getPaySign($para);
        $postData["sign_method"] = $sign_method;
        
        $json = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'pay/delivernotify?access_token=' . $access_token, $json);
        if (! empty($rst['errcode'])) {
            // 如果有异常，会在errcode 和errmsg 描述出来。
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // 微信公众平台在校验ok 之后，会返回数据表明是否通知成功，例如：
            // {"errcode":0,"errmsg":"ok"}
            return $rst;
        }
    }

    /**
     * 订单查询 orderquery
     * 因为某一方技术的原因，可能导致商家在预期时间内都收不到最终支付通知，
     * 此时商家可以通过该API 来查询订单的详细支付状态。
     *
     * @param string $out_trade_no            
     * @param string $timestamp            
     * @param string $sign_method            
     * @throws WeixinException
     * @return Ambigous <mixed, string>
     */
    public function orderquery($out_trade_no, $timestamp, $sign_method = "sha1")
    {
        /**
         * 接口调用请求说明
         * Api 的url 为：
         * https://api.weixin.qq.com/pay/orderquery?access_token=xxxxxx
         * Url 中的参数只包含目前微信公众平台凭证access_token，而发货通知的真正的数据是
         * 放在PostData 中的，格式为json，如下：
         * {
         * "appid" : "wwwwb4f85f3a797777",
         * "package"
         * :"out_trade_no=11122&partner=1900090055&sign=4e8d0df3da0c3d0df38f",
         * "timestamp" : "1369745073",
         * "app_signature" : "53cca9d47b883bd4a5c85a9300df3da0cb48565c",
         * "sign_method" : "sha1"
         * }
         * 其中，
         * appid 是公众平台账户的AppId；
         * package 是查询订单的关键信息数据，包含第三方唯一订单号out_trade_no、财付通商户
         * 身份标识partner（即前文所述的partnerid） 、签名sign，其中sign 是对参数字典序排序并
         * 使用&联合起来，最后加上&key=partnerkey（唯一分配），进行md5 运算，再转成全大写，
         * 最终得到sign，对于示例，就是：sign=md5（out_trade_no=11122&partner=1900090055&key=xxxxxx）.toupper；
         * timestamp 是linux 时间戳；
         * app_signature 依然是根据1 中所讲的签名方式生成的签名，
         * 参加签名字段为：appid、appkey、package、timestamp；
         * sign_method 是签名方法（不计入签名生成）；
         */
        
        // 参数 说明
        // access_token 调用接口凭证
        $access_token = $this->weixin->getToken('access_token');
        
        $postData = array();
        $postData["appid"] = $this->getAppId();
        
        // 获取package
        $para = array(
            "out_trade_no" => $out_trade_no,
            "partner" => $this->getPartnerId()
        );
        $package = $this->createPackage($para);
        $postData["package"] = $package;
        
        $postData["timestamp"] = $timestamp;
        // 获取app_signature
        $para = array(
            "appid" => $postData["appid"],
            "appkey" => $this->getPaySignKey(),
            "package" => $postData["package"],
            "timestamp" => $postData["timestamp"]
        );
        $postData["app_signature"] = $this->getPaySign($para);
        $postData["sign_method"] = $sign_method;
        
        $json = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'pay/orderquery?access_token=' . $access_token, $json);
        if (! empty($rst['errcode'])) {
            // 如果有异常，会在errcode 和errmsg 描述出来。
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // 微信公众平台在校验ok 之后，会返回数据表明是否通知成功，例如：
            // {"errcode":0,"errmsg":"ok", ...... }
            // 如果查询成功，会返回详细的订单数据，如下：
            // {
            // "errcode":0,
            // "errmsg":"ok",
            // "order_info":
            // {
            // "ret_code":0,
            // "ret_msg":"",
            // "input_charset":"GBK",
            // "trade_state":"0",
            // "trade_mode":"1",
            // "partner":"1900000109",
            // "bank_type":"CMB_FP",
            // "bank_billno":"207029722724",
            // "total_fee":"1",
            // "fee_type":"1",
            // "transaction_id":"1900000109201307020305773741",
            // "out_trade_no":"2986872580246457300",
            // "is_split":"false",
            // "is_refund":"false",
            // "attach":"",
            // "time_end":"20130702175943",
            // "transport_fee":"0",
            // "product_fee":"1",
            // "discount":"0",
            // "rmb_total_fee":""
            // }
            // }
            // 对于详细的订单信息，放在order_info 中的json 数据中，各个字段的含义如下：
            // ret_code 是查询结果状态码，0 表明成功，其他表明错误；
            // ret_msg 是查询结果出错信息；
            // input_charset 是返回信息中的编码方式；
            // trade_state 是订单状态，0 为成功，其他为失败；
            // trade_mode 是交易模式，1 为即时到帐，其他保留；
            // partner 是财付通商户号，即前文的partnerid；
            // bank_type 是银行类型；
            // bank_billno 是银行订单号；
            // total_fee 是总金额，单位为分；
            // fee_type 是币种，1 为人民币；
            // transaction_id 是财付通订单号；
            // out_trade_no 是第三方订单号；
            // is_split 表明是否分账，false 为无分账，true 为有分账；
            // is_refund 表明是否退款，false 为无退款，ture 为退款；
            // attach 是商家数据包，即生成订单package 时商家填入的attach；
            // time_end 是支付完成时间；
            // transport_fee 是物流费用，单位为分；
            // product_fee 是物品费用，单位为分；
            // discount 是折扣价格，单位为分；
            // rmb_total_fee 是换算成人民币之后的总金额，单位为分，一般看total_fee 即可。
            return $rst;
        }
    }

    /**
     * 获取Native（原生）支付URL定义
     *
     * @param string $productid            
     * @param string $noncestr            
     * @param string $timestamp            
     * @return string
     */
    public function getNativePayUrl($productid, $noncestr, $timestamp)
    {
        /**
         * Native（原生）支付URL 是一系列具有weixin://wxpay/bizpayurl?前缀的url，同时后面
         * 紧跟着一系列辨别商家的键值对。Native（原生）支付URL 的规则如下：
         * weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXXX&productid=XXXXXX&timestamp=XXXXXX&noncestr=XXXXXX
         * 其中xxxxxx 为商户需要填写的内容，具体参数定义如下：
         * 参数 必填 说明
         * appid 是 字段名称：公众号id；
         * 字段来源：商户注册具有支付权限的公众号成功后即可获得；
         * 传入方式：由商户直接传入。
         * timestamp 是 字段名称：时间戳；
         * 字段来源：商户生成从1970 年1 月1 日00：00：00 至今的秒数，即当前的时间；
         * 由商户生成后传入。取值范围：32 字符以下
         * noncestr 是 字段名称：随机字符串；
         * 字段来源：商户生成的随机字符串；取值范围：长度为32 个字符以下。
         * 由商户生成后传入。取值范围：32 字符以下
         * productid 是 字段名称：商品唯一id；
         * 字段来源：商户需要定义并维护自己的商品id，这个id 与一张订单等价，
         * 微信后台凭借该id 通过Post商户后台获取交易必须信息。由商户生成后传入。取值范围：32字符以下
         * sign 是 字段名称：签名；
         * 字段来源：对前面的其他字段与appKey 按照字典序排序后，使用SHA1 算法得到的结果。由商户生成后传入。
         * 参与sign 签名的字段包括：appid、timestamp、noncestr、productid 以及appkey
         */
        $appid = $this->getAppId();
        $para = array(
            "appid" => $appid,
            "appkey" => $this->getPaySignKey(),
            "timestamp" => $timestamp,
            "noncestr" => $noncestr,
            "productid" => $productid
        );
        $sign = $this->getPaySign($para);
        return "weixin://wxpay/bizpayurl?sign={$sign}&appid={$appid}&productid={$productid}&timestamp={$timestamp}&noncestr={$noncestr}";
    }

    /**
     * Native（原生）支付回调商户后台获取package 在公众平台接到用户点击上述特殊Native（原生）支付的URL
     * 之后，会调用注册时填写的商家获取订单Package 的回调URL。 假设回调URL
     * 为https://www.outdomain.com/cgi-bin/bizpaygetpackage
     *
     *
     * @param string $package            
     * @param string $noncestr            
     * @param string $timestamp            
     * @param string $SignMethod            
     * @param string $retcode            
     * @param string $reterrmsg            
     * @return string
     */
    public function getPackageForNativeUrl($package, $noncestr, $timestamp, $SignMethod = "sha1", $retcode = 0, $reterrmsg = "ok")
    {
        /**
         * 为了返回Package 数据，回调URL 必须返回一个xml 格式的返回数据，形如：
         * <xml>
         * <AppId><![CDATA[wwwwb4f85f3a797777]]></AppId>
         * <Package><![CDATA[a=1&url=http%3A%2F%2Fwww.qq.com]]></Package>
         * <TimeStamp> 1369745073</TimeStamp>
         * <NonceStr><![CDATA[iuytxA0cH6PyTAVISB28]]></NonceStr>
         * <RetCode>0</RetCode>
         * <RetErrMsg><![CDATA[ok]]></RetErrMsg>
         * <AppSignature><![CDATA[53cca9d47b883bd4a5c85a9300df3da0cb48565c]]>
         * </AppSignature>
         * <SignMethod><![CDATA[sha1]]></SignMethod>
         * </xml>
         * 其中，AppSignature 依然是根据前文paySign 所讲的签名方式生成的签名，
         * 参与签名的字段为：appid、appkey、package、timestamp、noncestr、retcode、reterrmsg。
         * package 的生成规则请参考JS API 所定义的package 生成规则。这里就不再赘述了。
         * 其中，对于一些第三方觉得商品已经过期或者其他错误的情况，可以在RetCode 和
         * RetErrMsg 中体现出来，RetCode 为0 表明正确，可以定义其他错误；当定义其他错误时，
         * 可以在RetErrMsg 中填上UTF8 编码的错误提示信息，比如“该商品已经下架”，客户端会直接提示出来。
         */
        $appid = $this->getAppId();
        // 获取app_signature
        $para = array(
            "appid" => $appid,
            "appkey" => $this->getPaySignKey(),
            "package" => $package,
            "timestamp" => $timestamp,
            "noncestr" => $noncestr,
            "retcode" => $retcode,
            "reterrmsg" => $reterrmsg
        );
        $AppSignature = $this->getPaySign($para);
        return "<xml><AppId><![CDATA[{$appid}]]></AppId><NonceStr><![CDATA[{$noncestr}]]></NonceStr><Package><![CDATA[{$package}]]></Package><RetCode>{$retcode}</RetCode><RetErrMsg><![CDATA[{$reterrmsg}]]></RetErrMsg><TimeStamp>{$timestamp}</TimeStamp><AppSignature><![CDATA[{$AppSignature}]]></AppSignature><SignMethod><![CDATA[{$SignMethod}]]></SignMethod></xml>";
    }

    /**
     * package 生成方法
     *
     * @param array $para            
     * @throws Exception
     * @return string
     */
    public function createPackage(array $para)
    {
        // 由于package 中携带了生成订单的详细信息，因此在微信将对package 里面的内容进行鉴权，
        // 确定package 携带的信息是真实、有效、合理的。因此，这里将定义生成package 字符串的方法。
        // a.除sign 字段外，对所有传入参数按照字段名的ASCII 码从小到大排序（字典序）后，
        // 使用URL 键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1；
        // 除去数组中的空值和签名参数
        unset($para['sign']);
        $paraFilter = Helpers::paraFilter($para);
        // 对数组排序
        $paraFilter = Helpers::argSort($paraFilter);
        $string1 = Helpers::createLinkstring($paraFilter);
        
        // b. 在string1 最后拼接上key=paternerKey 得到stringSignTemp 字符串，
        // 并对stringSignTemp 进行md5 运算，再将得到的字符串所有字符转换为大写，得到sign 值signValue。
        $sign = $string1 . '&key=' . $this->getPartnerKey();
        $sign = strtoupper(md5($sign));
        $paraFilter['sign'] = $sign;
        // // 获取sign
        // $sign = $this->getSign($paraFilter);
        
        // c.对string1 中的所有键值对中的value 进行urlencode 转码，按照a 步骤重新拼接成字符串，得到string2。
        // 对于js 前端程序，一定要使用函数encodeURIComponent
        // 进行urlencode编码（注意！进行urlencode时要将空格转化为%20而不是+）。
        $paraFilter = Helpers::argSort($paraFilter);
        $string2 = Helpers::createLinkstringUrlencode($paraFilter);
        
        // d.将sign=signValue 拼接到string2 后面得到最终的package 字符串。
        $package = $string2; // . '&sign=' . $sign;
        return $package;
    }

    /**
     * 签名（Sign）生成方法
     *
     * @param array $para            
     * @throws Exception
     * @return string
     */
    public function getSign(array $para)
    {
        
        // a.除sign 字段外，对所有传入参数按照字段名的ASCII 码从小到大排序（字典序）后，
        // 使用URL 键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1；
        // 除去数组中的空值和签名参数
        $paraFilter = Helpers::paraFilter($para);
        // 对数组排序
        $paraFilter = Helpers::argSort($paraFilter);
        $string1 = Helpers::createLinkstring($paraFilter);
        
        // b. 在string1 最后拼接上key=paternerKey 得到stringSignTemp 字符串，
        // 并对stringSignTemp 进行md5 运算，再将得到的字符串所有字符转换为大写，得到sign 值signValue。
        $sign = string1 . '&key=' . $this->getPartnerKey();
        $sign = strtoupper(md5($sign));
        
        return $sign;
    }

    /**
     * 支付签名（paySign）生成方法
     *
     * @param array $para            
     * @throws Exception
     * @return string
     */
    public function getPaySign(array $para)
    {
        
        // 对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，
        // 使用URL 键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1。
        // 这里需要注意的是所有参数名均为小写字符，例如appId 在排序后字符串则为appid；
        
        // 将所有key改为小写字符
        $paraFilter = array();
        foreach ($para as $key => $value) {
            $paraFilter[strtolower($key)] = $value;
        }
        // 除去数组中的空值和签名参数
        $paraFilter = Helpers::paraFilter($paraFilter);
        // 增加或修改appkey
        $paraFilter['appkey'] = $this->getPaySignKey();
        // 对数组排序
        $paraFilter = Helpers::argSort($paraFilter);
        $string1 = Helpers::createLinkstring($paraFilter);
        
        // 对string1 作签名算法，字段名和字段值都采用原始值（此时package 的value 就对应了
        // 使用2.6 中描述的方式生成的package），不进行URL 转义。
        // 具体签名算法为paySign =SHA1(string1)。
        $paySign = sha1($string1);
        return $paySign;
    }

    /**
     *
     *
     *
     * 获取JS API 时所需的订单详情（package）
     * 在商户调起JS API 时，
     * 商户需要此时确定该笔订单详情，
     * 并将该订单详情通过一定的
     * 方式进行组合放入package。
     * JS API 调用后，微信将通过package 的内容生成预支付单。
     * 下面将定义package的所需字段列表以及签名方法。
     *
     * @param string $body            
     * @param string $attach            
     * @param string $out_trade_no            
     * @param string $total_fee            
     * @param string $notify_url            
     * @param string $spbill_create_ip            
     * @param string $time_start            
     * @param string $time_expire            
     * @param string $transport_fee            
     * @param string $product_fee            
     * @param string $goods_tag            
     * @param string $bank_type            
     * @param string $fee_type            
     * @param string $input_charset            
     * @return string
     */
    public function getPackage4JsPay($body, $attach, $out_trade_no, $total_fee, $notify_url, $spbill_create_ip, $time_start, $time_expire, $transport_fee, $product_fee, $goods_tag, $bank_type = "WX", $fee_type = 1, $input_charset = "GBK")
    {
        /**
         * package 所需字段列表
         * 参数 必填 说明
         * bank_type 是 银行通道类型，由于这里是使用的微信公众号支付，
         * 因此这个字段固定为WX，注意大写。参数取值："WX"。
         * body 是 商品描述。参数长度：128 字节以下。
         * attach 否 附加数据，原样返回。128 字节以下。
         * partner 是 商户号,即注册时分配的partnerId。
         * out_trade_no 是 商户系统内部的订单号,32 个字符内、
         * 可包含字母,确保在商户系统唯一。参数取值范围：32 字节以下。
         * total_fee 是 订单总金额，单位为分。
         * fee_type 是 现金支付币种,取值：1（人民币）,默认值是1，暂只支持1。
         * notify_url 是 通知URL,在支付完成后,接收微信通知支付结果的URL,
         * 需给绝对路径,255 字符内, 格式如:http://wap.tenpay.com/tenpay.asp。取值范围：255 字节以内。
         * spbill_create_ip 是 订单生成的机器IP，指用户浏览器端IP，
         * 不是商户服务器IP，格式为IPV4 整型。取值范围：15 字节以内。
         * time_start 否 交易起始时间， 也是订单生成时间， 格式为yyyyMMddHHmmss，
         * 如2009 年12 月25 日9 点10 分10秒表示为20091225091010。时区为GMT+8 beijing。
         * 该时间取自商户服务器。取值范围：14 字节。
         * time_expire 否 交易结束时间， 也是订单失效时间， 格式为yyyyMMddHHmmss，
         * 如2009 年12 月27 日9 点10 分10秒表示为20091227091010。时区为GMT+8 beijing。
         * 该时间取自商户服务器。取值范围：14 字节。
         * transport_fee 否
         * 物流费用，单位为分。如果有值，必须保证transport_fee+product_fee=total_fee。
         * product_fee 否 商品费用，单位为分。如果有值，必须保证transport_fee+product_fee=total_fee。
         * goods_tag 否 商品标记，优惠券时可能用到。
         * input_charset 是 传入参数字符编码。取值范围："GBK"、"UTF-8"。默认："GBK"
         */
        
        // 获取package
        $para = array(
            "bank_type" => $bank_type,
            "body" => $body,
            "attach" => $attach,
            "partner" => $this->getPartnerId(),
            "out_trade_no" => $out_trade_no,
            "total_fee" => $total_fee,
            "fee_type" => $fee_type,
            "notify_url" => $notify_url,
            "spbill_create_ip" => $spbill_create_ip,
            "time_start" => $time_start,
            "time_expire" => $time_expire,
            "transport_fee" => $transport_fee,
            "product_fee" => $product_fee,
            "goods_tag" => $goods_tag,
            "input_charset" => $input_charset
        );
        $package = $this->createPackage($para);
        return $package;
    }

    /**
     * 标记客户的投诉处理状态。 updatefeedback
     *
     *
     * @param string $openid            
     * @param string $feedbackid            
     * @throws Exception
     * @return Ambigous <mixed, string>
     */
    public function updateFeedback($openid, $feedbackid)
    {
        /**
         * 接口调用请求说明
         * Api的url为： https://api.weixin.qq.com/payfeedback/update?access_token=xxxxx&openid=XXXX&feedbackid=xxxx
         * Url中的参数包含目前微信公众平台凭证access_token，
         * 和客户投诉对应的单号feedbackid，以及openid 微信公众平台在校验ok之后，
         * 会返回数据表明是否通知成功，例如： {"errcode":0,"errmsg":"ok"}
         * 如果有异常，会在 errcode和 errmsg描述出来，如果成功errcode就为0。
         */
        
        // 参数 说明
        // access_token 调用接口凭证
        $access_token = $this->weixin->getToken('access_token');
        $params = array();
        $params["access_token"] = $access_token;
        $params["openid"] = $openid;
        $params["feedbackid"] = $feedbackid;
        
        $rst = $this->weixin->get($this->_url . 'payfeedback/update', $params);
        if (! empty($rst['errcode'])) {
            // 如果有异常，会在errcode 和errmsg 描述出来。
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // 微信公众平台在校验ok 之后，会返回数据表明是否通知成功，例如：
            // {"errcode":0,"errmsg":"ok"}
            return $rst;
        }
    }
}
