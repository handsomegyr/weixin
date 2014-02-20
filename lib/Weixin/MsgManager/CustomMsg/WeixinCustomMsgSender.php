<?php
namespace Weixin\MsgManager\CustomMsg;
use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\MsgManager\WeixinMsgManager;

/**
 * 发送消息-----发送客服消息接口
 * 当用户主动发消息给公众号的时候，
 * 微信将会把消息数据推送给开发者，
 * 开发者在一段时间内（目前为24小时）可以调用客服消息接口，
 * 通过POST一个JSON数据包来发送消息给普通用户，
 * 在24小时内不限制发送次数。
 * 此接口主要用于客服等有人工消息处理环节的功能，
 * 方便开发者为用户提供更加优质的服务。
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinCustomMsgSender
{
	protected $weixinMsgManager;
	private $_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
	
	public function __construct(WeixinMsgManager $weixinMsgManager,$options=array()) {
		$this->weixinMsgManager = $weixinMsgManager;
	}

	/**
	 * 发送消息
	 * 该接口用于发送从iwebsite 微信模块发送过来的消息
	 * @param string $msg
	 * @return string
	 */
	public function send($msg)
	{
		$access_token = $this->weixinMsgManager->getWeixin()->getToken();
		if(is_array($msg)){
			$json = json_encode($msg,JSON_UNESCAPED_UNICODE);
		}
		$rst = $this->weixinMsgManager->getWeixin()->post($this->_url.'?access_token='.$access_token, $json);
		// 返回结果
		if(!empty($rst['errcode']))
		{
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			return $rst;
		}
	}

	/**
	 * 发送文本消息
	 * @param string $toUser
	 * @param string $content
	 * @return string
	 */
	public function sendText($toUser,$content)
	{
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "text";
		$ret['text']["content"] = $content;
		return $this->send($ret);
	}

	/**
	 * 发送图片消息
	 * @param string $toUser
	 * @param string $media_id
	 * @return string
	 */
	public function sendImage($toUser,$media_id) {
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "image";
		$ret['image']["media_id"] = $media_id;
		return $this->send($ret);
	}

	/**
	 * 发送语音消息
	 * @param string $toUser
	 * @param string $media_id
	 * @return string
	 */
	public function sendVoice($toUser,$media_id) {
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "voice";
		$ret['image']["media_id"] = $media_id;
		return json_encode($ret);
	}

	/**
	 * 发送视频消息
	 * @param string $toUser
	 * @param string $media_id
	 * @param string $thumb_media_id
	 * @return string
	 */
	public function sendVideo($toUser,$media_id,$thumb_media_id) {
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "video";
		$ret['image']["media_id"] = $media_id;
		$ret['image']["thumb_media_id"] = $thumb_media_id;
		return $this->send($ret);
	}

	/**
	 * 发送音乐消息
	 * @param string $toUser
	 * @param string $title
	 * @param string $description
	 * @param string $musicurl
	 * @param string $hqmusicurl
	 * @param string $thumb_media_id
	 * @return string
	 */
	public function sendMusic($toUser,$title,$description,$musicurl,$hqmusicurl,$thumb_media_id) {
		$hqmusicurl = $hqmusicurl=='' ? $musicurl : $hqmusicurl;
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "video";
		$ret['music']["title"] = $title;
		$ret['music']["description"] = $description;
		$ret['music']["musicurl"] = $musicurl;
		$ret['music']["hqmusicurl"] = $hqmusicurl;
		$ret['music']["thumb_media_id"] = $thumb_media_id;
		return $this->send($ret);
	}

	/**
	 * 发送图文消息
	 * @param string $toUser
	 * @param string $articles
	 * @return string
	 */
	public function sendGraphText($toUser,Array $articles) {
		if(!is_array($articles) || count($articles)==0)return '';
		$items = array();
		$articles = array_slice($articles, 0,10);//图文消息条数限制在10条以内。
		$articleCount = count($articles);
		foreach($articles as $article) {
			if(mb_strlen($article['description'],'utf-8') > $this->_length) {
				$article['description'] = mb_substr($article['description'], 0,  $this->WeixinMsgManager->getLength(), 'utf-8').'……';
			}
			$items[] = array('title'=>$article['title'],'description'=>$article['description'],'url'=>$article['url'],'picurl'=>$article['picurl']);
		}
		$ret =array();
		$ret['touser'] = $toUser;
		$ret['msgtype'] = "news";
		$ret['news']["articles"] = $items;
		return $this->send($ret);
	}
}
