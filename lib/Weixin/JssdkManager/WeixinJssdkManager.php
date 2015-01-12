<?php
namespace Weixin\JssdkManager;

use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 微信JS接口
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinJssdkManager
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

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
        $this->appId = $this->weixin->getAppId();
        $this->appSecret = $this->weixin->getAppSecret();
    }

    /**
     * jsapi_ticket
     *
     * 生成签名之前必须先了解一下jsapi_ticket，jsapi_ticket是公众号用于调用微信JS接口的临时票据。正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。由于获取jsapi_ticket的api调用次数非常有限，频繁刷新jsapi_ticket会导致api调用受限，影响自身业务，开发者必须在自己的服务全局缓存jsapi_ticket 。
     *
     * 参考以下文档获取access_token（有效期7200秒，开发者必须在自己的服务全局缓存access_token）：../15/54ce45d8d30b6bf6758f68d2e95bc627.html
     * 用第一步拿到的access_token 采用http GET方式请求获得jsapi_ticket（有效期7200秒，开发者必须在自己的服务全局缓存jsapi_ticket）：https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=ACCESS_TOKEN&type=jsapi
     * 成功返回如下JSON：
     *
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
     * "expires_in":7200
     * }
     * 获得jsapi_ticket之后，就可以生成JS-SDK权限验证的签名了。
     *
     * @return unknown
     */
    public function getJsApiTicket()
    {
        // access_token 调用接口凭证
        $access_token = $this->weixin->getToken('access_token');
        $params = array(
            'type' => 'jsapi',
            'access_token' => $access_token
        );
        $rst = $this->weixin->get($this->_url . 'ticket/getticket', $params);
        if (! empty($rst['errcode'])) {
            // 如果有异常，会在errcode 和errmsg 描述出来。
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 签名算法
     *
     * 签名生成规则如下：参与签名的字段包括noncestr（随机字符串）, 有效的jsapi_ticket, timestamp（时间戳）, url（当前网页的URL，不包含#及其后面部分） 。对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1。这里需要注意的是所有参数名均为小写字符。对string1作sha1加密，字段名和字段值都采用原始值，不进行URL 转义。
     *
     *
     * 即signature=sha1(string1)。 示例：
     *
     * noncestr=Wm3WZYTPz0wzccnW
     * jsapi_ticket=sM4AOVdWfPE4DxkXGEs8VMCPGGVi4C3VM0P37wVUCFvkVAy_90u5h9nbSlYy3-Sl-HhTdfl2fzFy1AOcHKP7qg
     * timestamp=1414587457
     * url=http://mp.weixin.qq.com
     *
     * 步骤1. 对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1：
     *
     * jsapi_ticket=sM4AOVdWfPE4DxkXGEs8VMCPGGVi4C3VM0P37wVUCFvkVAy_90u5h9nbSlYy3-Sl-HhTdfl2fzFy1AOcHKP7qg&noncestr=Wm3WZYTPz0wzccnW&timestamp=1414587457&url=http://mp.weixin.qq.com
     *
     * 步骤2. 对string1进行sha1签名，得到signature：
     *
     * f4d90daf4b3bca3078ab155816175ba34c443a7b
     * 注意事项
     *
     * 签名用的noncestr和timestamp必须与wx.config中的nonceStr和timestamp相同。
     * 签名用的url必须是调用JS接口页面的完整URL。
     * 出于安全考虑，开发者必须在服务器端实现签名的逻辑。
     *
     * @return array
     */
    public function getSignPackage($url)
    {
        $ret = $this->getJsApiTicket();
        $jsapiTicket = $ret['ticket'];
        
        // $url = "{$http}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $timestamp = time();
        $nonceStr = Helpers::createNonceStr();
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        
        $signPackage = array(
            "appId" => $this->getAppId(),
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
}
