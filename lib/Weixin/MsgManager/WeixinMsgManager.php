<?php
namespace Weixin\MsgManager;
use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\WeixinClient;
use Weixin\MsgManager\ReplyMsg\WeixinReplyMsgSender;
use Weixin\MsgManager\CustomMsg\WeixinCustomMsgSender;

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

	protected  $weixin;
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

	public function __construct(WeixinClient $weixin) {
		$this->weixin = $weixin;
		//发送被动响应消息发射器
		$this->weixinReplyMsgSender = new WeixinReplyMsgSender($this);
		//发送客服消息发射器
		$this->weixinCustomMsgSender = new WeixinCustomMsgSender($this);
	}

}