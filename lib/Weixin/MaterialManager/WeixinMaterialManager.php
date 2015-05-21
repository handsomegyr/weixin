<?php
namespace Weixin\MaterialManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 素材管理-永久素材管理器
 *
 * 除了3天就会失效的临时素材外，开发者有时需要永久保存一些素材，届时就可以通过本接口新增永久素材。
 *
 * 请注意：
 *
 * 1、新增的永久素材也可以在公众平台官网素材管理模块中看到
 * 2、永久素材的数量是有上限的，请谨慎新增。图文消息素材和图片素材的上限为5000，其他类型为1000
 * 3、素材的格式大小等要求与公众平台官网一致。具体是，图片大小不超过2M，支持bmp/png/jpeg/jpg/gif格式，语音大小不超过5M，长度不超过60秒，支持mp3/wma/wav/amr格式
 * 4、调用该接口需https协议
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 *        
 */
class WeixinMediaManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/material/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 新增永久图文素材
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=ACCESS_TOKEN
     * 调用示例
     *
     * {
     * "articles": [{
     * "title": TITLE,
     * "thumb_media_id": THUMB_MEDIA_ID,
     * "author": AUTHOR,
     * "digest": DIGEST,
     * "show_cover_pic": SHOW_COVER_PIC(0 / 1),
     * "content": CONTENT,
     * "content_source_url": CONTENT_SOURCE_URL
     * },
     * //若新增的是多图文素材，则此处应还有几段articles结构
     * ]
     * }
     * 参数说明
     *
     * 参数	是否必须	说明
     * title 是 标题
     * thumb_media_id 是 图文消息的封面图片素材id（必须是永久mediaID）
     * author 是 作者
     * digest 是 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * show_cover_pic 是 是否显示封面，0为false，即不显示，1为true，即显示
     * content 是 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * content_source_url 是 图文消息的原文地址，即点击“阅读原文”后的URL
     * 返回说明
     *
     * {
     * "media_id":MEDIA_ID
     * }
     * 返回的即为新增的图文消息素材的media_id。
     */
    public function addNews(array $articles)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['articles'] = $articles;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->post($this->_url . 'add_news?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 修改永久图文素材
     * 开发者可以通过本接口对永久图文素材进行修改。
     *
     * 请注意：
     *
     * 1、也可以在公众平台官网素材管理模块中保存的图文消息（永久图文素材）
     * 2、调用该接口需https协议
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=ACCESS_TOKEN
     * 调用示例
     *
     * {
     * "media_id":MEDIA_ID,
     * "index":INDEX,
     * "articles": {
     * "title": TITLE,
     * "thumb_media_id": THUMB_MEDIA_ID,
     * "author": AUTHOR,
     * "digest": DIGEST,
     * "show_cover_pic": SHOW_COVER_PIC(0 / 1),
     * "content": CONTENT,
     * "content_source_url": CONTENT_SOURCE_URL
     * }
     * }
     * 参数说明
     *
     * 参数	是否必须	说明
     * media_id 是 要修改的图文消息的id
     * index 是 要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * title 是 标题
     * thumb_media_id 是 图文消息的封面图片素材id（必须是永久mediaID）
     * author 是 作者
     * digest 是 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * show_cover_pic 是 是否显示封面，0为false，即不显示，1为true，即显示
     * content 是 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * content_source_url 是 图文消息的原文地址，即点击“阅读原文”后的URL
     * 返回说明
     *
     * {
     * "errcode": ERRCODE,
     * "errmsg": ERRMSG
     * }
     * 正确时errcode的值应为0。
     *
     * @param string $media_id            
     * @param unknown $media_id            
     */
    public function updateNews($media_id, $index = 0, array $article = array())
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['media_id'] = $media_id;
        $params['index'] = $index;
        $params['articles'] = $article;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->post($this->_url . 'update_news?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 新增其他类型永久素材
     *
     * 接口调用请求说明
     *
     * 通过POST表单来调用接口，表单id为media，包含需要上传的素材内容，有filename、filelength、content-type等信息。请注意：图片素材将进入公众平台官网素材管理模块中的默认分组。
     *
     * http请求方式: POST，需使用https
     * https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN
     * 调用示例（使用curl命令，用FORM表单方式新增一个其他类型的永久素材，curl命令的使用请自行查阅资料）
     * 参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * type 是 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * media 是 form-data中媒体文件标识，有filename、filelength、content-type等信息
     * 新增永久视频素材需特别注意
     *
     * 在上传视频素材时需要POST另一个表单，id为description，包含素材的描述信息，内容格式为JSON，格式如下：
     *
     * {
     * "title":VIDEO_TITLE,
     * "introduction":INTRODUCTION
     * }
     * 新增永久视频素材的调用示例：
     *
     * curl "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN" -F media=@media.file -F description='{"title":VIDEO_TITLE, "introduction":INTRODUCTION}'
     * 参数说明
     *
     * 参数	是否必须	说明
     * title 是 视频素材的标题
     * introduction 是 视频素材的描述
     * 返回说明
     *
     * {
     * "media_id":MEDIA_ID
     * }
     * 返回参数说明
     *
     * 参数	描述
     * media_id 新增的永久素材的media_id
     * 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
     *
     * {"errcode":40007,"errmsg":"invalid media_id"}
     */
    public function addMaterial($type, $media, array $description = array())
    {
        if ($type == "video" && empty($description)) {
            throw new \Exception("在上传视频素材时需要素材的描述信息");
        }
        
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['type'] = $type;
        $params['media'] = $media;
        $params['description'] = json_encode($description, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'add_material', $params, true);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取永久素材
     * 在新增了永久素材后，开发者可以根据media_id来获取永久素材，需要时也可保存到本地。
     *
     * 请注意：
     *
     * 1、获取永久素材也可以获取公众号在公众平台官网素材管理模块中新建的图文消息、语音、视频等素材（但需要先通过获取素材列表来获知素材的media_id）
     * 2、临时素材无法通过本接口获取
     * 3、调用该接口需https协议
     * 接口调用请求说明
     *
     * http请求方式: POST,https调用
     * https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=ACCESS_TOKEN
     * 调用示例
     *
     * {
     * "media_id":MEDIA_ID
     * }
     * 参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * media_id 是 要获取的素材的media_id
     * 返回说明
     *
     * 如果请求的素材为图文消息，则响应如下：
     *
     * {
     * "news_item":
     * [
     * {
     * "title":TITLE,
     * "thumb_media_id"::THUMB_MEDIA_ID,
     * "show_cover_pic":SHOW_COVER_PIC(0/1),
     * "author":AUTHOR,
     * "digest":DIGEST,
     * "content":CONTENT,
     * "url":URL,
     * "content_source_url":CONTENT_SOURCE_URL
     * },
     * //多图文消息有多篇文章
     * ]
     * }
     * 如果返回的是视频消息素材，则内容如下：
     *
     * {
     * "title":TITLE,
     * "description":DESCRIPTION,
     * "down_url":DOWN_URL,
     * }
     * 其他类型的素材消息，则响应的直接为素材的内容，开发者可以自行保存为文件。例如：
     *
     * 示例
     * curl "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=ACCESS_TOKEN" -d '{"media_id":"61224425"}' > file
     * 返回参数说明
     *
     * 参数	描述
     * title 图文消息的标题
     * thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     * show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * author 作者
     * digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * content 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * url 图文页的URL
     * content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     * 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
     *
     * {"errcode":40007,"errmsg":"invalid media_id"}
     */
    public function getMaterial($media_id)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['media_id'] = $media_id;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->post($this->_url . 'get_material?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 删除永久素材
     * 在新增了永久素材后，开发者可以根据本接口来删除不再需要的永久素材，节省空间。
     *
     * 请注意：
     *
     * 1、请谨慎操作本接口，因为它可以删除公众号在公众平台官网素材管理模块中新建的图文消息、语音、视频等素材（但需要先通过获取素材列表来获知素材的media_id）
     * 2、临时素材无法通过本接口删除
     * 3、调用该接口需https协议
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=ACCESS_TOKEN
     * 调用示例
     *
     * {
     * "media_id":MEDIA_ID
     * }
     * 参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * media_id 是 要获取的素材的media_id
     * 返回说明
     *
     * {
     * "errcode":ERRCODE,
     * "errmsg":ERRMSG
     * }
     * 正常情况下调用成功时，errcode将为0。
     *
     * @param string $media_id            
     */
    public function delMaterial($media_id)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['media_id'] = $media_id;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->post($this->_url . 'del_material?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取素材总数
     * 开发者可以根据本接口来获取永久素材的列表，需要时也可保存到本地。
     *
     * 请注意：
     *
     * 1.永久素材的总数，也会计算公众平台官网素材管理中的素材
     * 2.图片和图文消息素材（包括单图文和多图文）的总数上限为5000，其他素材的总数上限为1000
     * 3.调用该接口需https协议
     * 接口调用请求说明
     *
     * http请求方式: GET
     * https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=ACCESS_TOKEN
     * 返回说明
     *
     * {
     * "voice_count":COUNT,
     * "video_count":COUNT,
     * "image_count":COUNT,
     * "news_count":COUNT
     * }
     * 返回参数说明
     *
     * 参数	描述
     * voice_count 语音总数量
     * video_count 视频总数量
     * image_count 图片总数量
     * news_count 图文总数量
     * 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
     *
     * {"errcode":-1,"errmsg":"system error"}
     */
    public function getMaterialCount()
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->get($this->_url . 'get_materialcount?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取素材列表
     * 在新增了永久素材后，开发者可以分类型获取永久素材的列表。
     *
     * 请注意：
     *
     * 1、获取永久素材的列表，也会包含公众号在公众平台官网素材管理模块中新建的图文消息、语音、视频等素材（但需要先通过获取素材列表来获知素材的media_id）
     * 2、临时素材无法通过本接口获取
     * 3、调用该接口需https协议
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=ACCESS_TOKEN
     * 调用示例
     *
     * {
     * "type":TYPE,
     * "offset":OFFSET,
     * "count":COUNT
     * }
     * 参数说明
     *
     * 参数	是否必须	说明
     * type 是 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * offset 是 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * count 是 返回素材的数量，取值在1到20之间
     * 返回说明
     *
     * 永久图文消息素材列表的响应如下：
     *
     * {
     * "total_count": TOTAL_COUNT,
     * "item_count": ITEM_COUNT,
     * "item": [{
     * "media_id": MEDIA_ID,
     * "content": {
     * "news_item": [{
     * "title": TITLE,
     * "thumb_media_id": THUMB_MEDIA_ID,
     * "show_cover_pic": SHOW_COVER_PIC(0 / 1),
     * "author": AUTHOR,
     * "digest": DIGEST,
     * "content": CONTENT,
     * "url": URL,
     * "content_source_url": CONTETN_SOURCE_URL
     * },
     * //多图文消息会在此处有多篇文章
     * ]
     * },
     * "update_time": UPDATE_TIME
     * },
     * //可能有多个图文消息item结构
     * ]
     * }
     * 其他类型（图片、语音、视频）的返回如下：
     *
     * {
     * "total_count": TOTAL_COUNT,
     * "item_count": ITEM_COUNT,
     * "item": [{
     * "media_id": MEDIA_ID,
     * "name": NAME,
     * "update_time": UPDATE_TIME
     * },
     * //可能会有多个素材
     * ]
     * }
     * 返回参数说明
     *
     * 参数	描述
     * total_count 该类型的素材的总数
     * item_count 本次调用获取的素材的数量
     * title 图文消息的标题
     * thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     * show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * author 作者
     * digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * content 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * url 图文页的URL
     * content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     * update_time 这篇图文消息素材的最后更新时间
     * name 文件名称
     * 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
     *
     * {"errcode":40007,"errmsg":"invalid media_id"}
     */
    public function batchGetMaterial($type, $offset = 0, $count = 20)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['type'] = $type;
        $params['offset'] = $offset;
        $params['count'] = $count;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $rst = $this->weixin->post($this->_url . 'batchget_material?access_token=' . $access_token, $json);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
