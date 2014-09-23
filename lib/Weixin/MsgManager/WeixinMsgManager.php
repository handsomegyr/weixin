<?php
namespace Weixin\MsgManager;

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
}