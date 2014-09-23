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

    public function __destruct()
    {}
}

