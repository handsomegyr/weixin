<?php
namespace Weixin;

use Weixin\WeixinOAuthRequest;
use Weixin\WeixinException;
use Weixin\MsgManager\WeixinMsgManager;
use Weixin\GroupsManager\WeixinGroupsManager;
use Weixin\MediaManager\WeixinMediaManager;
use Weixin\MenuManager\WeixinMenuManager;
use Weixin\QrcodeManager\WeixinQrcodeManager;
use Weixin\UserManager\WeixinUserManager;
use Weixin\PayManager\WeixinPayManager;
use Weixin\ShortUrlManager\WeixinShortUrlManager;
use Weixin\CustomServiceManager\WeixinCustomServiceManager;
use Weixin\SemanticManager\WeixinSemanticManager;
use Weixin\CardManager\WeixinCardManager;
use Weixin\IpManager\WeixinIpManager;
use Weixin\JssdkManager\WeixinJssdkManager;
use Weixin\DatacubeManager\WeixinDatacubeManager;

/**
 * 微信公众平台的调用接口类.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinClient
{

    private $_appid = null;

    public function getAppid()
    {
        return $this->_appid;
    }

    private $_secret = null;

    public function getAppSecret()
    {
        return $this->_secret;
    }

    private $_access_token = null;

    private $_refresh_token = null;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/';

    protected $weixinMsgManager;

    /**
     * GET WeixinMsgManager object.
     *
     * @return WeixinMsgManager
     */
    public function getWeixinMsgManager()
    {
        return $this->weixinMsgManager;
    }

    protected $weixinUserManager;

    /**
     * GET WeixinUserManager object.
     *
     * @return WeixinUserManager
     */
    public function getWeixinUserManager()
    {
        return $this->weixinUserManager;
    }

    protected $weixinQrcodeManager;

    /**
     * GET WeixinQrcodeManager object.
     *
     * @return WeixinQrcodeManager
     */
    public function getWeixinQrcodeManager()
    {
        return $this->weixinQrcodeManager;
    }

    protected $weixinMenuManager;

    /**
     * GET WeixinMenuManager object.
     *
     * @return WeixinMenuManager
     */
    public function getWeixinMenuManager()
    {
        return $this->weixinMenuManager;
    }

    protected $weixinGroupsManager;

    /**
     * GET WeixinGroupsManager object.
     *
     * @return WeixinGroupsManager
     */
    public function getWeixinGroupsManager()
    {
        return $this->weixinGroupsManager;
    }

    protected $weixinMediaManager;

    /**
     * GET WeixinMediaManager object.
     *
     * @return WeixinMediaManager
     */
    public function getWeixinMediaManager()
    {
        return $this->weixinMediaManager;
    }

    protected $weixinPayManager;

    /**
     * GET WeixinPayManager object.
     *
     * @return WeixinPayManager
     */
    public function getWeixinPayManager()
    {
        return $this->weixinPayManager;
    }

    protected $weixinShortUrlManager;

    /**
     * GET WeixinShortUrlManager object.
     *
     * @return WeixinShortManager
     */
    public function WeixinShortUrlManager()
    {
        return $this->weixinShortUrlManager;
    }

    protected $weixinCustomServiceManager;

    /**
     * GET WeixinCustomServiceManager object.
     *
     * @return WeixinShortManager
     */
    public function WeixinCustomServiceManager()
    {
        return $this->weixinCustomServiceManager;
    }

    protected $weixinSemanticManager;

    /**
     * GET WeixinSemanticManager object.
     *
     * @return WeixinSemanticManager
     */
    public function WeixinSemanticManager()
    {
        return $this->weixinSemanticManager;
    }

    protected $weixinCardManager;

    /**
     * GET WeixinCardManager object.
     *
     * @return WeixinCardManager
     */
    public function WeixinCardManager()
    {
        return $this->weixinCardManager;
    }

    protected $weixinIpManager;

    /**
     * GET WeixinIpManager object.
     *
     * @return WeixinIpManager
     */
    public function WeixinIpManager()
    {
        return $this->weixinIpManager;
    }

    protected $weixinJssdkManager;

    /**
     * GET WeixinJssdkManager object.
     *
     * @return WeixinJssdkManager
     */
    public function WeixinJssdkManager()
    {
        return $this->weixinJssdkManager;
    }

    protected $weixinDatacubeManager;

    /**
     * GET WeixinDatacubeManager object.
     *
     * @return WeixinDatacubeManager
     */
    public function WeixinDatacubeManager()
    {
        return $this->weixinDatacubeManager;
    }

    public function __construct($appid, $secret, $access_token = NULL, $refresh_token = NULL, $options = array())
    {
        $this->_appid = $appid;
        $this->_secret = $secret;
        $this->_access_token = $access_token;
        $this->_refresh_token = $refresh_token;
        
        // 获取oAuthRequest对象
        $this->weixinOAuthRequest = new WeixinOAuthRequest();
        // 发送消息管理
        $this->weixinMsgManager = new WeixinMsgManager($this, $options);
        // 用户管理
        $this->weixinUserManager = new WeixinUserManager($this, $options);
        // 推广支持
        $this->weixinQrcodeManager = new WeixinQrcodeManager($this, $options);
        // 自定义菜单
        $this->weixinMenuManager = new WeixinMenuManager($this, $options);
        // 分组管理
        $this->weixinGroupsManager = new WeixinGroupsManager($this, $options);
        // 上传下载多媒体文件管理
        $this->weixinMediaManager = new WeixinMediaManager($this, $options);
        // 微信支付管理
        $this->weixinPayManager = new WeixinPayManager($this, $options);
        // 长链接转短链接管理
        $this->weixinShortUrlManager = new WeixinShortUrlManager($this, $options);
        // 多客服功能管理
        $this->weixinCustomServiceManager = new WeixinCustomServiceManager($this, $options);
        // 语义理解功能管理
        $this->weixinSemanticManager = new WeixinSemanticManager($this, $options);
        // 微信卡券功能管理
        $this->weixinCardManager = new WeixinCardManager($this, $options);
        // 微信服务器IP管理
        $this->weixinIpManager = new WeixinIpManager($this, $options);
        // 微信服务器JS管理
        $this->weixinJssdkManager = new WeixinJssdkManager($this, $options);
        // 微信服务器数据统计管理
        $this->weixinDatacubeManager = new WeixinDatacubeManager($this, $options);
    }

    /**
     * 获取access_token
     * access_token是公众号的全局唯一票据，
     * 公众号调用各接口时都需使用access_token。
     * 正常情况下access_token有效期为7200秒，
     * 重复获取将导致上次获取的access_token失效。
     * 公众号可以使用AppID和AppSecret调用本接口来获取access_token。
     * AppID和AppSecret可在开发模式中获得（需要已经成为开发者，且帐号没有异常状态）。
     * 注意调用所有微信接口时均需使用https协议。
     */
    public function getAccessToken()
    {
        // http请求方式: GET
        // https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
        $params = array();
        $params['grant_type'] = 'client_credential';
        $params['appid'] = $this->_appid;
        $params['secret'] = $this->_secret;
        $rst = $this->get($this->_url . 'token', $params);
        
        if (! empty($rst['errcode'])) {
            // 错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正常情况下，微信会返回下述JSON数据包给公众号：
            // {"access_token":"ACCESS_TOKEN","expires_in":7200}
            // 参数 说明
            // access_token 获取到的凭证
            // expires_in 凭证有效时间，单位：秒
            $this->_access_token = $rst['access_token'];
            $rst['grant_type'] = 'client_credential';
        }
        return $rst;
    }

    public function getToken($key = "access_token")
    {
        $token = array(
            'access_token' => $this->_access_token,
            'refresh_token' => $this->_refresh_token
        );
        return $token[$key];
    }

    /**
     * GET wrappwer for oAuthRequest.
     *
     * @return mixed
     */
    public function get($url, $parameters = array())
    {
        $response = $this->weixinOAuthRequest->get($url, $parameters);
        return $response;
    }

    /**
     * POST wreapper for oAuthRequest.
     *
     * @return mixed
     */
    public function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->weixinOAuthRequest->post($url, $parameters, $multi);
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    public function delete($url, $parameters = array())
    {
        $response = $this->weixinOAuthRequest->delete($url, $parameters);
        return $response;
    }

    /**
     * 有效性校验
     */
    public function verify($verifyCode)
    {
        $echoStr = isset($_GET["echostr"]) ? trim($_GET["echostr"]) : '';
        if (! empty($echoStr)) {
            if ($this->checkSignature($verifyCode)) {
                exit($echoStr);
            }
        }
    }

    /**
     * 签名校验
     *
     * @param string $verifyCode            
     * @return boolean
     */
    public function checkSignature($verifyCode)
    {
        if (empty($verifyCode))
            throw new WeixinException("请设定校验签名所需的verify_code");
        
        $verifyCode = trim($verifyCode);
        $signature = isset($_GET['signature']) ? trim($_GET['signature']) : '';
        $timestamp = isset($_GET['timestamp']) ? trim($_GET['timestamp']) : '';
        $nonce = isset($_GET['nonce']) ? trim($_GET['nonce']) : '';
        $tmpArr = array(
            $verifyCode,
            $timestamp,
            $nonce
        );
        sort($tmpArr, SORT_STRING); // 按照字符串来进行比较，否则在某些数字的情况下，sort的结果与微信要求不符合，官方文档中给出的签名算法有误
        $tmpStr = sha1(implode($tmpArr));
        return $tmpStr === $signature ? true : false;
    }

    /**
     * 获取信息接收信息
     *
     * @return array
     */
    public function recieve()
    {
        $postStr = file_get_contents('php://input');
        $datas = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $datas = Helpers::object2array($datas);
        
        if (isset($datas['Event']) && $datas['Event'] === 'LOCATION') {
            $Latitude = isset($datas['Latitude']) ? floatval($datas['Latitude']) : 0;
            $Longitude = isset($datas['Longitude']) ? floatval($datas['Longitude']) : 0;
            $datas['coordinate'] = array(
                $Latitude,
                $Longitude
            );
        }
        
        if (isset($datas['MsgType']) && $datas['MsgType'] === 'location') {
            $Location_X = isset($datas['Location_X']) ? floatval($datas['Location_X']) : 0;
            $Location_Y = isset($datas['Location_Y']) ? floatval($datas['Location_Y']) : 0;
            $datas['coordinate'] = array(
                $Location_X,
                $Location_Y
            );
        }
        
        return $datas;
    }

    public function __destruct()
    {}
}

