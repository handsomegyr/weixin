<?php
namespace Weixin\UserManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 用户管理-----获取用户基本信息接口
 * 在关注者与公众号产生消息交互后，
 * 公众号可获得关注者的OpenID（
 * 加密后的微信号，每个用户对每个公众号的OpenID是唯一的。
 * 对于不同公众号，同一用户的openid不同）。
 * 公众号可通过本接口来根据OpenID获取用户基本信息，
 * 包括昵称、头像、性别、所在城市、语言和关注时间。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinUserManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/user/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 获取用户基本信息
     * 开发者可通过OpenID来获取用户基本信息。请使用https协议。
     */
    public function getUserInfo($openid)
    {
        // http请求方式: GET
        // https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID
        // access_token 是 调用接口凭证
        // openid 是 普通用户的标识，对当前公众号唯一
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['openid'] = $openid;
        $rst = $this->weixin->get($this->_url . 'info', $params);
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            /*
             * { "subscribe": 1, "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M", "nickname": "Band", "sex": 1, "language": "zh_CN", "city": "广州", "province": "广东", "country": "中国", "headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0", "subscribe_time": 1382694957 }
             */
            return $rst;
        }
    }

    /**
     * 获取关注者列表
     * 公众号可通过本接口来获取帐号的关注者列表，
     * 关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成。
     * 一次拉取调用最多拉取10000个关注者的OpenID，
     * 可以通过多次拉取的方式来满足需求。
     */
    public function getUser($next_openid = "")
    {
        // http请求方式: GET（请使用https协议）
        // https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=NEXT_OPENID
        // access_token 是 调用接口凭证
        // next_openid 是 第一个拉取的OPENID，不填默认从头开始拉取
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['next_openid'] = $next_openid;
        $rst = $this->weixin->get($this->_url . 'get', $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            // 错误时返回JSON数据包（示例为无效AppID错误）：
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正确时返回JSON数据包：
            /* {"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"} */
            // 参数 说明
            // total 关注该公众账号的总用户数
            // count 拉取的OPENID个数，最大值为10000
            // data 列表数据，OPENID的列表
            // next_openid 拉取列表的后一个用户的OPENID
            return $rst;
        }
    }

    /**
     * 设置备注名
     * 开发者可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号
     */
    public function updateRemark($openid, $remark)
    {
        /**
         * https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=ACCESS_TOKEN
         * POST数据格式：JSON
         * POST数据例子：
         * {
         * "openid":"oDF3iY9ffA-hqb2vVvbr7qxf6A0Q",
         * "remark":"pangzi"
         * }
         */
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['openid'] = $openid;
        $params['remark'] = $remark; // 新的备注名，长度必须小于30字符
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'info/updateremark?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            // 错误时的JSON数据包示例（该示例为AppID无效错误）：
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明
            // 正常时的返回JSON数据包示例：
            // {
            // "errcode":0,
            // "errmsg":"ok"
            // }
            return $rst;
        }
    }
}
