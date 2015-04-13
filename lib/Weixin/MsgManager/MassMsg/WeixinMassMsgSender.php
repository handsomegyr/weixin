<?php
namespace Weixin\MsgManager\MassMsg;

use Weixin\WeixinException;
use Weixin\MsgManager\WeixinMsgManager;

/**
 * 在公众平台网站上，为订阅号提供了每天一条的群发权限，
 * 为服务号提供每月（自然月）4条的群发权限。
 * 而对于某些具备开发能力的公众号运营者，
 * 可以通过高级群发接口，实现更灵活的群发能力。
 *
 * 请注意：
 *
 * 1、该接口暂时仅提供给已微信认证的服务号
 * 2、虽然开发者使用高级群发接口的每日调用限制为100次，但是用户每月只能接收4条，请小心测试
 * 3、无论在公众平台网站上，还是使用接口群发，用户每月只能接收4条群发消息，多于4条的群发将对该用户发送失败。
 * 4、具备微信支付权限的公众号，在使用高级群发接口上传、群发图文消息类型时，可使用<a>标签加入外链
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinMassMsgSender
{

    public $is_to_all = false;

    protected $weixinMsgManager;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/message/mass/';

    public function __construct(WeixinMsgManager $weixinMsgManager, $options = array())
    {
        $this->weixinMsgManager = $weixinMsgManager;
    }

    /**
     * 根据分组进行群发
     *
     * @param array $params            
     * @throws Exception
     * @return array
     */
    public function sendAll($params)
    {
        $access_token = $this->weixinMsgManager->getWeixin()->getToken();
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        $rst = $this->weixinMsgManager->getWeixin()->post($this->_url . 'sendall?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 发送文本消息
     *
     * @param string $group_id            
     * @param string $content            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendTextByGroup($group_id, $content, $title = "", $description = "")
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if (! empty($this->is_to_all)) {
            $ret['filter']['is_to_all'] = $this->is_to_all;
        }
        $ret['msgtype'] = 'text';
        $ret['text']['content'] = $content;
        $ret['text']['title'] = $title;
        $ret['text']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送图片消息
     *
     * @param string $group_id            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendImageByGroup($group_id, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if (! empty($this->is_to_all)) {
            $ret['filter']['is_to_all'] = $this->is_to_all;
        }
        $ret['msgtype'] = 'image';
        $ret['image']['media_id'] = $media_id;
        $ret['image']['title'] = $title;
        $ret['image']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送语音消息
     *
     * @param string $group_id            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendVoiceByGroup($group_id, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if (! empty($this->is_to_all)) {
            $ret['filter']['is_to_all'] = $this->is_to_all;
        }
        $ret['msgtype'] = 'voice';
        $ret['voice']['media_id'] = $media_id;
        $ret['voice']['title'] = $title;
        $ret['voice']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送视频消息
     *
     * @param string $group_id            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendVideoByGroup($group_id, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if (! empty($this->is_to_all)) {
            $ret['filter']['is_to_all'] = $this->is_to_all;
        }
        $ret['msgtype'] = 'mpvideo';
        $ret['mpvideo']['media_id'] = $media_id;
        $ret['mpvideo']['title'] = $title;
        $ret['mpvideo']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 发送图文消息
     *
     * @param string $group_id            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendGraphTextByGroup($group_id, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['filter']['group_id'] = $group_id;
        if (! empty($this->is_to_all)) {
            $ret['filter']['is_to_all'] = $this->is_to_all;
        }
        $ret['msgtype'] = 'mpnews';
        $ret['mpnews']['media_id'] = $media_id;
        $ret['mpnews']['title'] = $title;
        $ret['mpnews']['description'] = $description;
        return $this->sendAll($ret);
    }

    /**
     * 根据OpenID列表群发
     *
     * @param array $params            
     * @throws Exception
     * @return array
     */
    public function send($params)
    {
        $access_token = $this->weixinMsgManager->getWeixin()->getToken();
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        $rst = $this->weixinMsgManager->getWeixin()->post($this->_url . 'send?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 发送文本消息
     *
     * @param array $toUsers            
     * @param string $content            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendTextByOpenid(array $toUsers, $content, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'text';
        $ret['text']['content'] = $content;
        $ret['text']['title'] = $title;
        $ret['text']['description'] = $description;
        return $this->send($ret);
    }

    /**
     * 发送图片消息
     *
     * @param array $toUsers            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendImageByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'image';
        $ret['image']['media_id'] = $media_id;
        $ret['image']['title'] = $title;
        $ret['image']['description'] = $description;
        return $this->send($ret);
    }

    /**
     * 发送语音消息
     *
     * @param array $toUsers            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendVoiceByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'voice';
        $ret['voice']['media_id'] = $media_id;
        $ret['voice']['title'] = $title;
        $ret['voice']['description'] = $description;
        return $this->send($ret);
    }

    /**
     * 发送视频消息
     *
     * @param array $toUsers            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendVideoByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'mpvideo';
        $ret['mpvideo']['media_id'] = $media_id;
        $ret['mpvideo']['title'] = $title;
        $ret['mpvideo']['description'] = $description;
        return $this->send($ret);
    }

    /**
     * 发送图文消息
     *
     * @param array $toUsers            
     * @param string $media_id            
     * @param string $title            
     * @param string $description            
     * @return array
     */
    public function sendGraphTextByOpenid(array $toUsers, $media_id, $title = "", $description = "")
    {
        $ret = array();
        $ret['touser'] = $toUsers;
        $ret['msgtype'] = 'mpnews';
        $ret['mpnews']['media_id'] = $media_id;
        $ret['mpnews']['title'] = $title;
        $ret['mpnews']['description'] = $description;
        return $this->send($ret);
    }

    /**
     * 删除群发
     *
     * @param string $msgid            
     * @return array
     */
    public function delete($msgid)
    {
        $ret = array();
        $ret['msgid'] = $msgid;
        
        $access_token = $this->weixinMsgManager->getWeixin()->getToken();
        if (is_array($ret)) {
            $json = json_encode($ret, JSON_UNESCAPED_UNICODE);
        }
        $rst = $this->weixinMsgManager->getWeixin()->post($this->_url . 'delete?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 预览接口【订阅号与服务号认证后均可用】
     * 开发者可通过该接口发送消息给指定用户，在手机端查看消息的样式和排版。
     *
     * @param array $params            
     * @return array
     */
    public function preview($params)
    {
        $access_token = $this->weixinMsgManager->getWeixin()->getToken();
        if (is_array($params)) {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        $rst = $this->weixinMsgManager->getWeixin()->post($this->_url . 'preview?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
