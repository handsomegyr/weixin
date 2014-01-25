<?php
namespace Weixin\MsgManager\ReplyMsg;
use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\MsgManager\WeixinMsgManager;

/**
 * 发送消息-----发送被动响应消息接口
 * 对于每一个POST请求，开发者在响应包（Get）中返回特定XML结构，
 * 对该消息进行响应（现支持回复文本、图片、图文、语音、视频、音乐）。
 * 请注意，回复图片等多媒体消息时需要预先上传多媒体文件到微信服务器，
 * 只支持认证服务号。

 * 微信服务器在五秒内收不到响应会断掉连接，并且重新发起请求，
 * 总共重试三次，如果在调试中，发现用户无法收到响应的消息，
 * 可以检查是否消息处理超时。

 * 关于重试的消息排重，有msgid的消息推荐使用msgid排重。
 * 事件类型消息推荐使用FromUserName + CreateTime 排重。

 * 假如服务器无法保证在五秒内处理并回复，可以直接回复空串，
 * 微信服务器不会对此作任何处理，并且不会发起重试。 
 * 这种情况下，可以使用客服消息接口进行异步回复。
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinReplyMsgSender
{
	protected $weixinMsgManager;
	/**
	 * @param WeixinMsgManager $weixinMsgManager Connection factory object.
	 */
	public function __construct(WeixinMsgManager $weixinMsgManager) {
		$this->weixinMsgManager = $weixinMsgManager;
	}
	
	/**
	 * 回复文本
	 * @param string $toUser
	 * @param string $fromUser
	 * @param string $content
	 * @return string
	 */
	public function replyText($toUser,$fromUser,$content) {
		$time = time();
		return "
		<xml>
		<ToUserName><![CDATA[{$toUser}]]></ToUserName>
		<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
		<CreateTime>{$time}</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[{$content}]]></Content>
		</xml>";
	}
	
	/**
	* 回复图片消息
	* @param string $toUser
	* @param string $fromUser
	* @param string $media_id
	* @return string
	*/
	public function replyImage($toUser,$fromUser,$media_id) {
		$time = time();
		return "
		<xml>
		<ToUserName><![CDATA[{$toUser}]]></ToUserName>
		<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
		<CreateTime>{$time}</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		<Image>
		<MediaId><![CDATA[{$media_id}]]></MediaId>
		</Image>
		</xml>";
	}
	
	/**
	* 回复语音消息
	* @param string $toUser
	* @param string $fromUser
	* @param string $media_id
	* @return string
	*/
	public function replyVoice($toUser,$fromUser,$media_id) {
		$time = time();
		return "
		<xml>
		<ToUserName><![CDATA[{$toUser}]]></ToUserName>
		<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
		<CreateTime>{$time}</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		<Voice>
		<MediaId><![CDATA[{$media_id}]]></MediaId>
		</Voice>
		</xml>";
	}
	
	/**
	* 回复视频消息
	* @param string $toUser
	* @param string $fromUser
	* @param string $media_id
	* @param string $thumb_media_id
	 * @return string
	 */
	 public function replyVideo($toUser,$fromUser,$media_id,$thumb_media_id) {
	 	$time = time();
		 return "
		 <xml>
		 <ToUserName><![CDATA[{$toUser}]]></ToUserName>
		 <FromUserName><![CDATA[{$fromUser}]]></FromUserName>
		 <CreateTime>{$time}</CreateTime>
		 <MsgType><![CDATA[video]]></MsgType>
		 <Video>
		 <MediaId><![CDATA[{$media_id}]]></MediaId>
		 <ThumbMediaId><![CDATA[{$thumb_media_id}]]></ThumbMediaId>
		 </Video>
		 </xml>";
	 }
	
	 /**
	 * 回复音乐
	 * @param string $toUser
	 * @param string $fromUser
	 * @param string $title
	 * @param string $description
	 * @param string $musicUrl
	 * @param string $hqMusicUrl
	 * @param string $media_id
	 * @return string
	 */
	 public function replyMusic($toUser,$fromUser,$title,$description,$musicUrl,$hqMusicUrl='',$thumbMediaId=0) {
	 	$time = time();
	 	$hqMusicUrl = $hqMusicUrl=='' ? $musicUrl : $hqMusicUrl;
	
	 	return "
	 	<xml>
		<ToUserName><![CDATA[{$toUser}]]></ToUserName>
		<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
		<CreateTime>{$time}</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		<Music>
		<Title><![CDATA[{$title}]]></Title>
		<Description><![CDATA[{$description}]]></Description>
		<MusicUrl><![CDATA[{$musicUrl}]]></MusicUrl>
		<HQMusicUrl><![CDATA[{$hqMusicUrl}]]></HQMusicUrl>
		<ThumbMediaId><![CDATA[{$thumbMediaId}]]></ThumbMediaId>
		</Music>
		</xml>";
	}
	
	 /**
	 * 回复图文信息
	 * @param string $toUser
	 * @param string $fromUser
	 * @param array $articles
	 *
	 * 子元素
	 * $articles[] = $article
	 * 子元素结构
	 * $article['title']
	 * $article['description']
	 * $article['picurl'] 图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80
	 * $article['url']
	 *
	 * @return string
	 */
	 public function replyGraphText($toUser,$fromUser,Array $articles) {
	 	$time = time();
	 	if(!is_array($articles) || count($articles)==0)return '';
	 	$items = '';
	 	$articles = array_slice($articles, 0,10);
	 	$articleCount = count($articles);
		foreach($articles as $article) {
		 	if(mb_strlen($article['description'],'utf-8') > $this->_length) {
		 		$article['description'] = mb_substr($article['description'], 0, $this->WeixinMsgManager->getLength(), 'utf-8').'……';
		 	}
		 	$items .= "
		 	<item>
		 	<Title><![CDATA[{$article['title']}]]></Title>
		 	<Description><![CDATA[{$article['description']}]]></Description>
		 	<PicUrl><![CDATA[{$article['picurl']}]]></PicUrl>
		 	<Url><![CDATA[{$article['url']}]]></Url>
		 	</item>";
		 }
		return "
		<xml>
 		<ToUserName><![CDATA[{$toUser}]]></ToUserName>
 		<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
 		<CreateTime>{$time}</CreateTime>
 		<MsgType><![CDATA[news]]></MsgType>
 		<ArticleCount>{$articleCount}</ArticleCount>
 		<Articles>{$items}</Articles>
 		</xml>";
	}
}
