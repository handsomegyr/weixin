<?php
namespace Weixin\SnsUserManager;

use Weixin\WeixinException;
use Weixin\WeixinSnsClient;

/**
 * 用户管理----网页授权获取用户基本信息接口
 *
 * 如果用户在微信中（Web微信除外）访问公众号的第三方网页，
 * 公众号开发者可以通过此接口获取当前用户基本信息（包括昵称、性别、城市、国家）。
 * 利用用户信息，可以实现体验优化、用户来源统计、帐号绑定、用户身份鉴权等功能。
 * 请注意，“获取用户基本信息接口是在用户和公众号产生消息交互时，
 * 才能根据用户OpenID获取用户基本信息，而网页授权的方式获取用户基本信息，
 * 则无需消息交互，只是用户进入到公众号的网页，就可弹出请求用户授权的界面，
 * 用户授权后，就可获得其基本信息（此过程甚至不需要用户已经关注公众号。）”
 *
 * 本接口是通过OAuth2.0来完成网页授权的，是安全可靠的，关于OAuth2.0的详细介绍，
 * 可以参考OAuth2.0协议标准。在微信公众号请求用户网页授权之前，
 * 开发者需要先到公众平台网站的我的服务页中配置授权回调域名。
 * 请注意，这里填写的域名不要加http://
 *
 * 关于配置授权回调域名的说明：
 * 授权回调域名配置规范为全域名，比如需要网页授权的域名为：www.qq.com，
 * 配置以后此域名下面的页面http://www.qq.com/music.html 、
 * http://www.qq.com/login.html 都可以进行OAuth2.0鉴权。
 * 但http://pay.qq.com 、 http://music.qq.com 、 http://qq.com
 * 无法进行OAuth2.0鉴权。
 *
 * 具体而言，网页授权流程分为三步：
 * 1 引导用户进入授权页面同意授权，获取code
 * 2 通过code换取网页授权access_token（与基础支持中的access_token不同）
 * 3 如果需要，开发者可以刷新网页授权access_token，避免过期
 * 4 通过网页授权access_token和openid获取用户基本信息
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinSnsUserManager
{

    protected $weixin;

    public function __construct(WeixinSnsClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     *
     * 如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。
     *
     * 请求方法
     *
     * http：GET（请使用https协议）
     * https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
     * 参数说明
     *
     * 参数	描述
     * access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * openid	用户的唯一标识
     * lang	返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * 返回说明
     *
     * 正确时返回的JSON数据包如下：
     *
     * {
     * "openid":" OPENID",
     * "nickname": NICKNAME,
     * "sex":"1",
     * "province":"PROVINCE"
     * "city":"CITY",
     * "country":"COUNTRY",
     * "headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
     * "privilege":[
     * "PRIVILEGE1"
     * "PRIVILEGE2"
     * ],
     * "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     * 参数	描述
     * openid	用户的唯一标识
     * nickname	用户昵称
     * sex	用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     * province	用户个人资料填写的省份
     * city	普通用户个人资料填写的城市
     * country	国家，如中国为CN
     * headimgurl	用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
     * privilege	用户特权信息，json 数组，如微信沃卡用户为（chinaunicom）
     * unionid	只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。详见：获取用户个人信息（UnionID机制）
     *
     * 错误时微信会返回JSON数据包如下（示例为openid无效）:
     *
     * {"errcode":40003,"errmsg":" invalid openid "}
     */
    public function getSnsUserInfo($openid, $lang = 'zh_CN')
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['openid'] = $openid;
        $params['lang'] = $lang;
        $rst = $this->weixin->get("https://api.weixin.qq.com/sns/userinfo", $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 检验授权凭证（access_token）是否有效
     *
     * 请求方法
     *
     * http：GET（请使用https协议）
     * https://api.weixin.qq.com/sns/auth?access_token=ACCESS_TOKEN&openid=OPENID
     * 参数说明
     *
     * 参数	描述
     * access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * openid	用户的唯一标识
     * 返回说明
     *
     * 正确的Json返回结果：
     *
     * { "errcode":0,"errmsg":"ok"}
     * 错误时的Json返回示例：
     *
     * { "errcode":40003,"errmsg":"invalid openid"}
     */
    public function auth($openid)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['openid'] = $openid;
        $rst = $this->weixin->get("https://api.weixin.qq.com/sns/auth", $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
