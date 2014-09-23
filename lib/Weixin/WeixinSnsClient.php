<?php
namespace Weixin;

use Weixin\WeixinOAuthRequest;
use Weixin\WeixinException;
use Weixin\SnsUserManager\WeixinSnsUserManager;

/**
 * 微信公众平台的网页授权调用接口类.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinSnsClient
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

    protected $weixinUserManager;

    /**
     * GET WeixinSnsUserManager object.
     *
     * @return WeixinSnsUserManager
     */
    public function getWeixinUserManager()
    {
        return $this->weixinUserManager;
    }

    public function __construct($appid, $secret, $access_token = NULL, $refresh_token = NULL, $options = array())
    {
        $this->_appid = $appid;
        $this->_secret = $secret;
        $this->_access_token = $access_token;
        $this->_refresh_token = $refresh_token;
        // 获取oAuthRequest对象
        $this->weixinOAuthRequest = new WeixinOAuthRequest();
        // 用户管理
        $this->weixinUserManager = new WeixinSnsUserManager($this, $options);
    }

    /**
     * 授权连接
     */
    private function authorizeURL()
    {
        return 'https://open.weixin.qq.com/connect/oauth2/authorize';
    }

    /**
     * authorize接口,用户同意授权，获取code
     *
     * 对应API：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF}
     *
     * @param string $url
     *            授权后的回调地址
     * @param string $scope
     *            应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     *            snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，
     *            即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @param string $response_type
     *            默认值为code
     * @param string $state
     *            重定向后会带上state参数，开发者可以填写任意参数值
     * @param string $wechat_redirect
     *            直接在微信打开链接，可以不填此参数。做页面302重定向时候，必须带此参数
     * @return string
     */
    public function getAuthorizeURL($url, $scope = "snsapi_userinfo", $response_type = 'code', $state = "")
    {
        // appid 是 公众号的唯一标识
        // redirect_uri 是 授权后重定向的回调链接地址
        // response_type 是 返回类型，请填写code
        // scope 是 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
        // state 否 重定向后会带上state参数，开发者可以填写任意参数值
        // #wechat_redirect 否 直接在微信打开链接，可以不填此参数。做页面302重定向时候，必须带此参数
        $params = array();
        $params['appid'] = $this->_appid;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        $params['scope'] = $scope;
        $params['state'] = $state;
        return $this->authorizeURL() . "?" . http_build_query($params) . "#wechat_redirect";
    }

    /**
     * access_token连接地址
     */
    private function accessTokenURL()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    /**
     * refreshToken连接地址
     */
    private function refreshTokenURL()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    }

    /**
     * 通过code换取网页授权access_token
     * 首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。
     * 公众号可通过下述接口来获取网页授权access_token。如果网页授权的作用域为snsapi_base，
     * 则本步骤中获取到网页授权access_token的同时，
     * 也获取到了openid，snsapi_base式的网页授权流程即到此为止。
     */
    public function getSnsAccessToken($code)
    {
        // 请求方法
        // 获取code后，请求以下链接获取access_token：
        // https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
        // 参数说明
        // 参数 是否必须 说明
        // appid 是 公众号的唯一标识
        // secret 是 公众号的appsecret
        // code 是 填写第一步获取的code参数
        // grant_type 是 填写为authorization_code
        $params = array();
        $params['appid'] = $this->_appid;
        $params['secret'] = $this->_secret;
        $params['code'] = $code;
        $params['grant_type'] = 'authorization_code';
        $rst = $this->get($this->accessTokenURL(), $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            // 错误时微信会返回JSON数据包如下（示例为Code无效错误）:
            // {"errcode":40029,"errmsg":"invalid code"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正确时返回的JSON数据包如下：
            // {
            // "access_token":"ACCESS_TOKEN",
            // "expires_in":7200,
            // "refresh_token":"REFRESH_TOKEN",
            // "openid":"OPENID",
            // "scope":"SCOPE"
            // }
            // 参数 描述
            // access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
            // expires_in access_token接口调用凭证超时时间，单位（秒）
            // refresh_token 用户刷新access_token
            // openid 用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
            // scope 用户授权的作用域，使用逗号（,）分隔
            $this->_access_token = $rst['access_token'];
            $this->_refresh_token = $rst['refresh_token'];
            $rst['grant_type'] = 'authorization_code';
            $rst['code'] = $code;
        }
        return $rst;
    }

    /**
     * 刷新access_token（如果需要）
     * 由于access_token拥有较短的有效期，
     * 当access_token超时后，可以使用refresh_token进行刷新，
     * refresh_token拥有较长的有效期（7天、30天、60天、90天），
     * 当refresh_token失效的后，需要用户重新授权。
     */
    public function getSnsRefreshToken($refresh_token)
    {
        // 请求方法
        // 获取第二步的refresh_token后，请求以下链接获取access_token：
        // https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
        // 参数 是否必须 说明
        // appid 是 公众号的唯一标识
        // grant_type 是 填写为refresh_token
        // refresh_token 是 填写通过access_token获取到的refresh_token参数
        $params = array();
        $params['appid'] = $this->_appid;
        $params['grant_type'] = 'refresh_token';
        $params['refresh_token'] = $refresh_token;
        $rst = $this->get($this->refreshTokenURL(), $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            // 错误时微信会返回JSON数据包如下（示例为Code无效错误）:
            // {"errcode":40029,"errmsg":"invalid code"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正确时返回的JSON数据包如下：
            // {
            // "access_token":"ACCESS_TOKEN",
            // "expires_in":7200,
            // "refresh_token":"REFRESH_TOKEN",
            // "openid":"OPENID",
            // "scope":"SCOPE"
            // }
            // 参数 描述
            // access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
            // expires_in access_token接口调用凭证超时时间，单位（秒）
            // refresh_token 用户刷新access_token
            // openid 用户唯一标识
            // scope 用户授权的作用域，使用逗号（,）分隔
            $this->_access_token = $rst['access_token'];
            $this->_refresh_token = $rst['refresh_token'];
            $this->_access_token = $rst['access_token'];
            $rst['grant_type'] = 'refresh_token';
        }
        return $rst;
    }

    /**
     * 获取Token
     * 
     * @param string $key            
     * @return string
     */
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

