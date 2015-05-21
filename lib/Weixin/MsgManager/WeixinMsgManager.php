<?php
namespace Weixin\MsgManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;
use Weixin\MsgManager\ReplyMsg\WeixinReplyMsgSender;
use Weixin\MsgManager\CustomMsg\WeixinCustomMsgSender;
use Weixin\MsgManager\MassMsg\WeixinMassMsgSender;
use Weixin\MsgManager\TemplateMsg\WeixinTemplateMsgSender;

/**
 * 发送消息接口
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinMsgManager
{

    private $_length = 140;

    public function getLength()
    {
        return $this->_length;
    }

    protected $weixin;

    /**
     * GET WeixinClient object.
     *
     * @return WeixinClient
     */
    public function getWeixin()
    {
        return $this->weixin;
    }

    protected $weixinReplyMsgSender;

    /**
     * GET WeixinReplyMsgSender object.
     *
     * @return WeixinReplyMsgSender
     */
    public function getWeixinReplyMsgSender()
    {
        return $this->weixinReplyMsgSender;
    }

    protected $weixinCustomMsgSender;

    /**
     * GET WeixinCustomMsgSender object.
     *
     * @return WeixinCustomMsgSender
     */
    public function getWeixinCustomMsgSender()
    {
        return $this->weixinCustomMsgSender;
    }

    protected $weixinMassMsgSender;

    /**
     * GET WeixinMassMsgSender object.
     *
     * @return WeixinMassMsgSender
     */
    public function getWeixinMassMsgSender()
    {
        return $this->weixinMassMsgSender;
    }

    protected $weixinTemplateMsgSender;

    /**
     * GET WeixinTemplateMsgSender object.
     *
     * @return WeixinTemplateMsgSender
     */
    public function getWeixinTemplateMsgSender()
    {
        return $this->weixinTemplateMsgSender;
    }

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
        // 发送被动响应消息发射器
        $this->weixinReplyMsgSender = new WeixinReplyMsgSender($this, $options);
        // 发送客服消息发射器
        $this->weixinCustomMsgSender = new WeixinCustomMsgSender($this, $options);
        // 发送高级群发消息发射器
        $this->weixinMassMsgSender = new WeixinMassMsgSender($this, $options);
        // 发送模板消息发射器
        $this->weixinTemplateMsgSender = new WeixinTemplateMsgSender($this, $options);
    }

    /**
     * 获取自动回复规则
     *
     * 开发者可以通过该接口，获取公众号当前使用的自动回复规则，
     * 包括关注后自动回复、消息自动回复（60分钟内触发一次）、关键词自动回复。
     *
     * 请注意：
     *
     * 1、第三方平台开发者可以通过本接口，
     * 在旗下公众号将业务授权给你后，
     * 立即通过本接口检测公众号的自动回复配置，
     * 并通过接口再次给公众号设置好自动回复规则，
     * 以提升公众号运营者的业务体验。
     * 2、本接口仅能获取公众号在公众平台官网的自动回复功能中设置的自动回复规则，
     * 若公众号自行开发实现自动回复，或通过第三方平台开发者来实现，则无法获取。
     * 3、认证/未认证的服务号/订阅号，以及接口测试号，均拥有该接口权限。
     * 4、从第三方平台的公众号登录授权机制上来说，该接口从属于消息与菜单权限集。
     * 5、本接口中返回的mediaID均为临时素材（通过素材管理-获取临时素材接口来获取这些素材），
     * 每次接口调用返回的mediaID都是临时的、不同的，在每次接口调用后3天有效，
     * 若需永久使用该素材，需使用素材管理接口中的永久素材。
     * 接口调用请求说明
     *
     * http请求方式: GET（请使用https协议）
     * https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token=ACCESS_TOKEN
     * 返回结果说明
     *
     * 返回的JSON格式样例：（注意，格式化前的json使用‘\’作为转义符）
     *
     * {
     * "is_add_friend_reply_open": 1,
     * "is_autoreply_open": 1,
     * "add_friend_autoreply_info": {
     * "type": "text",
     * "content": "Thanks for your attention!"
     * },
     * "message_default_autoreply_info": {
     * "type": "text",
     * "content": "Hello, this is autoreply!"
     * },
     * "keyword_autoreply_info": {
     * "list": [
     * {
     * "rule_name": "autoreply-news",
     * "create_time": 1423028166,
     * "reply_mode": "reply_all",
     * "keyword_list_info": [
     * {
     * "type": "text",
     * "match_mode": "contain",
     * "content": "news测试"//此处content即为关键词内容
     * }
     * ],
     * "reply_list_info": [
     * {
     * "type": "news",
     * "news_info": {
     * "list": [
     * {
     * "title": "it's news",
     * "author": "jim",
     * "digest": "it's digest",
     * "show_cover": 1,
     * "cover_url": "http://mmbiz.qpic.cn/mmbiz/GE7et87vE9vicuCibqXsX9GPPLuEtBfXfKbE8sWdt2DDcL0dMfQWJWTVn1N8DxI0gcRmrtqBOuwQHeuPKmFLK0ZQ/0",
     * "content_url": "http://mp.weixin.qq.com/s?__biz=MjM5ODUwNTM3Ng==&mid=203929886&idx=1&sn=628f964cf0c6d84c026881b6959aea8b#rd",
     * "source_url": "http://www.url.com"
     * }
     * ]
     * }
     * },
     * {
     * "type": "news",
     * "news_info": {
     * "list": [
     * {
     * "title": "MULTI_NEWS",
     * "author": "JIMZHENG",
     * "digest": "text",
     * "show_cover": 0,
     * "cover_url": "http://mmbiz.qpic.cn/mmbiz/GE7et87vE9vicuCibqXsX9GPPLuEtBfXfK0HKuBIa1A1cypS0uY1wickv70iaY1gf3I1DTszuJoS3lAVLvhTcm9sDA/0",
     * "content_url": "http://mp.weixin.qq.com/s?__biz=MjM5ODUwNTM3Ng==&mid=204013432&idx=1&sn=80ce6d9abcb832237bf86c87e50fda15#rd",
     * "source_url": ""
     * },
     * {
     * "title": "MULTI_NEWS4",
     * "author": "JIMZHENG",
     * "digest": "MULTI_NEWSMULTI_NEWSMULTI_NEWSMULTI_NEWSMULTI_NEWSMULT",
     * "show_cover": 1,
     * "cover_url": "http://mmbiz.qpic.cn/mmbiz/GE7et87vE9vicuCibqXsX9GPPLuEtBfXfKbE8sWdt2DDcL0dMfQWJWTVn1N8DxI0gcRmrtqBOuwQHeuPKmFLK0ZQ/0",
     * "content_url": "http://mp.weixin.qq.com/s?__biz=MjM5ODUwNTM3Ng==&mid=204013432&idx=5&sn=b4ef73a915e7c2265e437096582774af#rd",
     * "source_url": ""
     * }
     * ]
     * }
     * }
     * ]
     * },
     * {
     * "rule_name": "autoreply-voice",
     * "create_time": 1423027971,
     * "reply_mode": "random_one",
     * "keyword_list_info": [
     * {
     * "type": "text",
     * "match_mode": "contain",
     * "content": "voice测试"
     * }
     * ],
     * "reply_list_info": [
     * {
     * "type": "voice",
     * "content": "NESsxgHEvAcg3egJTtYj4uG1PTL6iPhratdWKDLAXYErhN6oEEfMdVyblWtBY5vp"
     * }
     * ]
     * },
     * {
     * "rule_name": "autoreply-text",
     * "create_time": 1423027926,
     * "reply_mode": "random_one",
     * "keyword_list_info": [
     * {
     * "type": "text",
     * "match_mode": "contain",
     * "content": "text测试"
     * }
     * ],
     * "reply_list_info": [
     * {
     * "type": "text",
     * "content": "hello!text!"
     * }
     * ]
     * },
     * {
     * "rule_name": "autoreply-video",
     * "create_time": 1423027801,
     * "reply_mode": "random_one",
     * "keyword_list_info": [
     * {
     * "type": "text",
     * "match_mode": "equal",
     * "content": "video测试"
     * }
     * ],
     * "reply_list_info": [
     * {
     * "type": "video",
     * "content": "http://61.182.133.153/vweixinp.tc.qq.com/1007_114bcede9a2244eeb5ab7f76d951df5f.f10.mp4?vkey=7183E5C952B16C3AB1991BA8138673DE1037CB82A29801A504B64A77F691BF9DF7AD054A9B7FE683&sha=0&save=1"
     * }
     * ]
     * }
     * ]
     * }
     * }
     *
     * 参数说明
     *
     * 参数	说明
     * is_add_friend_reply_open 关注后自动回复是否开启，0代表未开启，1代表开启
     * is_autoreply_open 消息自动回复是否开启，0代表未开启，1代表开启
     * add_friend_autoreply_info 关注后自动回复的信息
     * type 自动回复的类型。关注后自动回复和消息自动回复的类型仅支持文本（text）、图片（img）、语音（voice）、视频（video），关键词自动回复则还多了图文消息
     * content 对于文本类型，content是文本内容，对于图片、语音、视频类型，content是mediaID
     * message_default_autoreply_info 消息自动回复的信息
     * keyword_autoreply_info 关键词自动回复的信息
     * rule_name 规则名称
     * create_time 创建时间
     * reply_mode 回复模式，reply_all代表全部回复，random_one代表随机回复其中一条
     * keyword_list_info 匹配的关键词列表
     * match_mode 匹配模式，contain代表消息中含有该关键词即可，equal表示消息内容必须和关键词严格相同
     * news_info 图文消息的信息
     * title 图文消息的标题
     * digest 摘要
     * author 作者
     * show_cover 是否显示封面，0为不显示，1为显示
     * cover_url 封面图片的URL
     * content_url 正文的URL
     * source_url 原文的URL，若置空则无查看原文入口
     */
    public function getCurrentAutoreplyInfo()
    {
        $access_token = $this->weixin->getToken();
        
        $rst = $this->weixin->get($this->_url . 'get_current_autoreply_info?access_token=' . $access_token, array());
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}