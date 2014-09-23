<?php
namespace Weixin\MediaManager;

use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\WeixinClient;
use Weixin\WeixinOAuthRequest;

/**
 * 上传下载多媒体文件接口
 * 公众号在使用接口时，
 * 对多媒体文件、多媒体消息的获取和调用等操作，
 * 是通过media_id来进行的。通过本接口，
 * 公众号可以上传或下载多媒体文件。
 * 但请注意，每个多媒体文件（media_id）会在上传、
 * 用户发送到微信服务器3天后自动删除，以节省服务器资源。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinMediaManager
{

    protected $weixin;

    private $_url = 'http://file.api.weixin.qq.com/cgi-bin/media/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 上传多媒体文件
     * 公众号可调用本接口来上传图片、语音、视频等文件到微信服务器，
     * 上传后服务器会返回对应的media_id，公众号此后可根据该media_id来获取多媒体。
     * 请注意，media_id是可复用的，调用该接口需http协议。
     * 注意事项
     * 上传的多媒体文件有格式和大小限制，如下：
     * 图片（image）: 128K，支持JPG格式
     * 语音（voice）：256K，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：1MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * 媒体文件在后台保存时间为3天，即3天后media_id失效。
     *
     * @author Kan
     *        
     */
    public function upload($type, $media_path)
    {
        // 接口调用请求说明
        // http请求方式: POST/FORM
        // http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE
        // 调用示例（使用curl命令，用FORM表单方式上传一个多媒体文件）：
        // curl -F media=@test.jpg "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE"
        // 参数说明
        // 参数 是否必须 说明
        // access_token 是 调用接口凭证
        // type 是 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
        // media 是 form-data中媒体文件标识，有filename、filelength、content-type等信息
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['type'] = $type;
        $params['media'] = '@' . $media_path;
        $rst = $this->weixin->post($this->_url . 'upload', $params, true);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            // 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
            // {"errcode":40004,"errmsg":"invalid media type"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明
            // 正确情况下的返回JSON数据包结果如下：
            // {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
            // 参数 描述
            // type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb，主要用于视频与音乐格式的缩略图）
            // media_id 媒体文件上传后，获取时的唯一标识
            // created_at 媒体文件上传时间戳
            return $rst;
        }
    }

    /**
     * 下载多媒体文件
     * 公众号可调用本接口来获取多媒体文件。请注意，调用该接口需http协议。
     */
    public function get($media_id)
    {
        // 接口调用请求说明
        // http请求方式: GET
        // http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID
        // 请求示例（示例为通过curl命令获取多媒体文件）
        // curl -I -G "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID"
        // 参数说明
        
        // 参数 是否必须 说明
        // access_token 是 调用接口凭证
        // media_id 是 媒体文件ID
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['media_id'] = $media_id;
        $url = $this->_url . 'get';
        // 获取oAuthRequest对象
        $weixinOAuthRequest = new WeixinOAuthRequest();
        $weixinOAuthRequest->decode_json = false;
        $content = $weixinOAuthRequest->get($url, $params);
        if (Helpers::isJson($content)) {
            $rst = json_decode($content, true);
        } else {
            $rst = array();
            $rst['content'] = base64_encode($content);
        }
        
        // 返回说明
        if (! empty($rst['errcode'])) {
            // 错误情况下的返回JSON数据包示例如下（示例为无效媒体ID错误）：:
            // {"errcode":40007,"errmsg":"invalid media_id"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 正确情况下的返回HTTP头如下：
            // HTTP/1.1 200 OK
            // Connection: close
            // Content-Type: image/jpeg
            // Content-disposition: attachment; filename="MEDIA_ID.jpg"
            // Date: Sun, 06 Jan 2013 10:20:18 GMT
            // Cache-Control: no-cache, must-revalidate
            // Content-Length: 339721
            // curl -G "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID"
            return $rst;
        }
    }
}
