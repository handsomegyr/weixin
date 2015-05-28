<?php
namespace Weixin\PoiManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;


/**
 * POI 门店管理接口
 * 门店管理接口为商户提供门店批量导入、查询、修改、删除等主要功能，方便商户快速、高效进
 * 行门店管理和操作。
 * 商户在使用门店管理接口时需注意以下几个问题：
 *  门店信息全部需要经过审核方能生效，门店创建完成后，只会返回创建成功提示，并不能
 * 获得 poi_id，只有经过审核后才能获取 poi_id，收到微信推送的审核结果通知，并使用在微
 * 信各个业务场景中；
 *  为保证在审核通过后，获取到的 poi_id 能与商户自身数据做对应，将会允许商户在创建时
 * 提交自己内部或自定义的 sid(字符串格式，微信不会对唯一性进行校验，请商户自己保证)，
 * 用于后续获取 poi_id 后对数据进行对应；
 *  门店的可用状态 available_state，将标记门店相应审核状态，只有审核通过状态，才能进行
 * 更新，更新字段仅限扩展字段（表 1 中前 11 个字段） ；
 *  扩展字段属于公共编辑信息，提交更新后将由微信进行审核采纳，但扩展字段更新并不影
 * 响门店的可用状态（即 available_state 仍为审核通过） ，但 update_status 状态变为 1，更新中
 * 状态，此时不可再次对门店进行更新，直到微信审核采纳后；
 *  在 update_status 为 1，更新中状态下的门店，此时调用 getpoi 接口，获取到的扩展字段为更
 * 新的最新字段，但并不是最终结果，仍需等待微信编辑对扩展字段的建议进行采纳后，最
 * 终决定是否生效（有可能更新字段不被采纳） ；
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 * @author young <youngyang@icatholic.net.cn>
 */
class WeixinPoiManager
{

    protected $weixin;

    private $_url = 'http://api.weixin.qq.com/cgi-bin/poi/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 上传图片
     * 1.1 接口说明
     * 用 POI 接口新建门店时所使用的图片 url 必须为微信自己域名的 url，因此需要先用上传图片接
     * 口上传图片并获取 url，再创建门店。上传的图片限制文件大小限制 1MB，支持 JPG 格式
     * 1.2 接口调用请求说明
     * 协议 https
     * http 请求方式 POST/FORM
     * 请求 Url https://file.api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=ACCESS_TOKEN
     * POST 数据格式 buffer
     * 1.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * buffer 数据 是 图片文件的数据流
     * 1.4 返回数据
     * 导入成功示例：
     * {
     * "url":"http://mmbiz.qpic.cn/XXXXX"
     * }
     * 插入失败示例（errcode 不为 0，errmsg 为相应错误信息） ：
     * {
     * "errcode":40001,
     * "errmsg":"invalid credential"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常。
     * errmsg 错误信息。
     *
     * @throws Exception
     * @return Ambigous <\Weixin\Http\mixed, multitype:, string, number, boolean, mixed>
     */
    public function uploadImg($img)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['buffer'] = '@' . $img;
        $rst = $this->weixin->post('https://file.api.weixin.qq.com/cgi-bin/media/uploadimg', $params, true);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 2 创建门店
     * 2.1 接口说明
     * 创建门店接口是为商户提供创建自己门店数据的接口，
     * 门店数据字段越完整，商户页面展示越丰富，能够更多的吸引用户，并提高曝光度。
     * 创建门店接口调用成功后会返回 errcode、errmsg，但不会实时返回 poi_id。
     * 成功创建后，门店信息会经过审核，审核通过后方可使用，并获取 poi_id，
     * 该 id 为门店的唯一 id，强烈建议自行存储该 id，并为后续调用使用。
     * 2.2 接口调用请求说明
     * 协议 http
     * http 请求方式 POST
     * 请求 Url http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token=TOKEN
     * POST 数据格式 json
     * 2.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     * 2.4 POST 数据示例
     * Json 数据示例
     * 字段说明
     * 字段 说明 是否必填
     * {"business ":{
     * "base_info":{
     * "sid":"33788392",
     * "business_name":"麦当劳",
     * "branch_name":"艺苑路店",
     * "province":"广东省",
     * "city":"广州市",
     * "district":"海珠区",
     * "address":"艺苑路 11 号",
     * "telephone":"020-12345678",
     * "categories":["美食,快餐小吃"],
     * "offset_type":1,
     * "longitude":115.32375,
     * "latitude":25.097486,
     * "photo_list":[{"photo_url":"https:// XXX.com"}，{"photo_url":"https://XXX.com"}],
     * "recommend":"麦辣鸡腿堡套餐，麦乐鸡，全家桶",
     * "special":"免费 wifi，外卖服务",
     * "introduction":"麦当劳是全球大型跨国连锁餐厅，1940 年创立于美国，在世界上
     * 大约拥有 3 万间分店。主要售卖汉堡包，以及薯条、炸鸡、汽水、冰品、沙拉、水果等
     * 快餐食品",
     * "open_time":"8:00-20:00",
     * "avg_price":35
     * }
     * }
     * }
     * sid 商户自己的 id，用于后续审核通过收到 poi_id 的通知时，做对应关系。请商户自己保证唯一识别性 否
     * business_name 门店名称（仅为商户名，如：国美、麦当劳，不应包含地区、店号等信息，错误示例：北京国美）是
     * branch_name 分店名称（不应包含地区信息、不应与门店名重复，错误示例：北京王府井店）否
     * province 门店所在的省份（直辖市填城市名,如：北京市） 是
     * city 门店所在的城市 是
     * district 门店所在地区 否
     * address 门店所在的详细街道地址（不要填写省市信息） 是
     * telephone 门店的电话（纯数字，区号、分机号均由“-”隔开） 是
     * categories 门店的类型（详细分类参见分类附表，不同级分类用“,”隔开，如：美食，川菜，火锅）是
     * offset_type 坐标类型，1 为火星坐标（目前只能选 1） 是
     * longitude 门店所在地理位置的经度 是
     * latitude 门店所在地理位置的纬度（经纬度均为火星坐标，最好选用腾讯地图标记的坐标）是
     * photo_list 图片列表，url 形式，可以有多张图片，尺寸为640*340px。必须为上一接口生成的 url 是
     * recommend 推荐品，餐厅可为推荐菜；酒店为推荐套房；景点为推荐游玩景点等，针对自己行业的推荐内容 否
     * special 特色服务，如免费 wifi，免费停车，送货上门等商户能提供的特色功能或服务 是
     * introduction 商户简介，主要介绍商户信息等 否
     * open_time 营业时间，24 小时制表示，用“-”连接，如8:00-20:00 是
     * avg_price 人均价格，大于 0 的整数 否
     * 表 1 门店字段表
     * 2.5 返回数据
     * 导入成功示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 插入失败示例（errcode 不为 0，errmsg 为相应错误信息） ：
     * {
     * "errcode":40001,
     * "errmsg":"invalid credential"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常。
     * errmsg 错误信息。
     *
     * @return mixed
     */
    public function addPoi(Weixin\Model\Poi $poi)
    {
        $base_info = array();
        $base_info['sid'] = $poi->sid;
        $base_info['business_name'] = $poi->business_name;
        $base_info['branch_name'] = $poi->branch_name;
        $base_info['province'] = $poi->province;
        $base_info['city'] = $poi->city;
        $base_info['district'] = $poi->district;
        $base_info['address'] = $poi->address;
        $base_info['telephone'] = $poi->telephone;
        $base_info['categories'] = $poi->categories;
        $base_info['offset_type'] = $poi->offset_type;
        $base_info['longitude'] = $poi->longitude;
        $base_info['latitude'] = $poi->latitude;
        $base_info['photo_list'] = $poi->photo_list;
        $base_info['recommend'] = $poi->recommend;
        $base_info['special'] = $poi->special;
        $base_info['introduction'] = $poi->introduction;
        $base_info['open_time'] = $poi->open_time;
        $base_info['avg_price'] = $poi->avg_price;
        
        $params = array();
        $params['business']['base_info'] = $base_info;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'addpoi?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 4 查询门店信息
     * 4.1 接口说明
     * 在审核通过并获取 poi_id 后，商户可以利用 poi_id，查询具体某条门店的信息。
     * 若在查询时，update_status 字段为 1，表明在 5 个工作日内曾用 update 接口修改过门店扩展字段，
     * 该扩展字段为最新的修改字段，尚未经过审核采纳，因此不是最终结果。
     * 最终结果会在 5 个工作日内，最终确认是否采纳，并前端生效（但该扩展字段的采纳过程不影响门店的可用性，
     * 即 available_state仍为审核通过状态）
     * 注：扩展字段为公共编辑信息（大家都可修改） ，修改将会审核，并决定是否对修改建议进行采纳，
     * 但不会影响该门店的生效可用状态
     * 4.2 接口调用请求说明
     * 协议 http
     * http 请求方式 POST
     * 请求 Url http://api.weixin.qq.com/cgi-bin/poi/getpoi?access_token=TOKEN
     * POST 数据格式 json
     * 4.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     * 4.4 POST 数据
     * 数据示例
     * {
     * "poi_id":"271262077"
     * }
     * 4.5 返回数据说明
     * 返回数据
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "business ":{
     * "base_info":{
     * "sid":"001",
     * "business_name":"麦当劳",
     * "branch_name":"艺苑路店",
     * "province":"广东省",
     * "city":"广州市",
     * "address":"海珠区艺苑路 11 号",
     * "telephone":"020-12345678",
     * "categories":["美食,快餐小吃"],
     * "offset_type":1,
     * "longitude":115.32375,
     * "latitude":25.097486,
     * "photo_list":[{"photo_url":"https:// XXX.com"} ， {"photo_url":"https://XXX.com"}],
     * "recommend":"麦辣鸡腿堡套餐，麦乐鸡，全家桶",
     * "special":"免费 wifi，外卖服务",
     * "introduction":"麦当劳是全球大型跨国连锁餐厅，1940 年创立于美国，在世界上大
     * 约拥有 3 万间分店。主要售卖汉堡包，以及薯条、炸鸡、汽水、冰品、沙拉、水果等快餐食品",
     * "open_time":"8:00-20:00",
     * "avg_price":35
     * "available_state":3
     * "update_status":0
     * }
     * }
     * }
     * 字段 说明
     * errcode 错误码，0 为正常。
     * errmsg 错误信息。
     * available_state 门店是否可用状态。1 表示系统错误、2 表示审核中、3 审核通过、4 审核驳回。当该字段为 1、2、4 状态时，poi_id 为空
     * update_status 扩展字段是否正在更新中。1 表示扩展字段正在更新中，尚未生效，不允许再次更新； 0 表示扩展字段没有在更新中或更新已生效，可以再次更新
     * business 门店信息，字段内容同前
     *
     * @return mixed
     */
    public function getPoi($poi_id)
    {
        $params = array();
        $params['poi_id'] = $poi_id;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getpoi?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 查询门店列表
     * 5.1 接口说明
     * 商户可以通过该接口，批量查询自己名下的门店 list，并获取已审核通过的 poi_id（审核中和审核驳回的不返回 poi_id） 、
     * 商户自身 sid 用于对应、商户名、分店名、地址字段。
     * 5.2 接口调用说明
     * 协议 http
     * http 请求方式 POST
     * 请求 Url http://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token=TOKEN
     * POST 数据格式 json
     * 5.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     * 5.4 POST 数据
     * 数据示例：
     * {
     * "begin":0,
     * "limit":10
     * }
     * 字段说明：
     * 字段 说明 是否必填
     * begin 开始位置，0 即为从第一条开始查询 是
     * limit 返回数据条数，最大允许 50，默认为 20 是
     * 5.5 返回数据说明
     * 数据示例：
     * 第一条未审核通过，有 poi_id，全部字段；第二条未审核不通过，无 poi_id，仅有基础字段
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * "business_list":[
     * {"base_info":{
     * "sid":"100",
     * "poi_id":"271864249",
     * "business_name":"麦当劳",
     * "branch_name":"艺苑路店",
     * "address":"艺苑路 11 号",
     * "available_state":3
     * }}，
     * {"base_info":{
     * "sid":"101",
     * "business_name":"麦当劳",
     * "branch_name":"赤岗路店",
     * "address":"赤岗路 102 号",
     * "available_state":4
     * }}],
     * "total_count":"2",
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * total_count 门店总数量
     * 注：其他字段同前
     *
     * @return mixed
     */
    public function getPoiList($begin = 0, $limit = 20)
    {
        $params = array();
        $params['begin'] = $begin;
        $params['limit'] = $limit;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getpoilist?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 6 删除门店
     * 6.1 接口说明
     * 商户可以通过该接口，删除已经成功创建的门店。请商户慎重调用该接口，门店信息被删除后，
     * 可能会影响其他与门店相关的业务使用，如卡券等。
     * 同样，该门店信息也不会在微信的商户详情页显示，不会再推荐入附近功能。
     * 6.2 接口调用说明
     * 协议 http
     * http 请求方式 POST
     * 请求 Url http://api.weixin.qq.com/cgi-bin/poi/delpoi?access_token=TOKEN
     * POST 数据格式 json
     * 6.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     * 6.4 POST 数据
     * 数据示例：
     * {
     * "poi_id": "271262077"
     * }
     * 字段说明：
     * 字段 说明
     * poi_id 门店 ID
     * 6.5 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function delPoi($poi_id)
    {
        $params = array();
        $params['poi_id'] = $poi_id;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'delpoi?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 修改门店服务信息
     * 7.1 接口说明
     * 商户可以通过该接口，修改门店的服务信息，包括：图片列表、营业时间、推荐、特色服务、简介、人均价格、电话 7 个字段。
     * 目前基础字段包括（名称、坐标、地址等不可修改）
     * 7.2 接口调用说明
     * 协议 http
     * http 请求方式 POST
     * 请求 Url http://api.weixin.qq.com/cgi-bin/poi/updatepoi?access_token=TOKEN
     * POST 数据格式 json
     * 7.3 参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     * 7.4 POST 数据
     * 数据示例：
     * {"business ":{
     * "base_info":{
     * "poi_id ":"271864249"
     * "telephone ":"020-12345678"
     * "photo_list":[{"photo_url":"https:// XXX.com"}，{"photo_url":"https://XXX.com"}],
     * "recommend":"麦辣鸡腿堡套餐，麦乐鸡，全家桶",
     * "special":"免费 wifi，外卖服务",
     * "introduction":"麦当劳是全球大型跨国连锁餐厅，1940 年创立于美国，在世界
     * 上大约拥有 3 万间分店。主要售卖汉堡包，以及薯条、炸鸡、汽水、冰品、沙拉、水
     * 果等快餐食品",
     * "open_time":"8:00-20:00",
     * "avg_price":35
     * }
     * }
     * }
     * 字段说明：
     * 全部字段内容同前，特别注意，以上 7 个字段，若有填写内容则为覆盖更新，若无内容则视为不修改，维持原有内容。
     * photo_list 字段为全列表覆盖，若需要增加图片，需将之前图片同样放入list 中，在其后增加新增图片。
     * 如：已有 A、B、C 三张图片，又要增加 D、E 两张图，则需要调用该接口，photo_list 传入 A、B、C、D、E 五张图片的链接。
     * 7.5 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function updatePoi($poi_id, $telephone = "", array $photo_list = array(), $recommend = "", $special = "", $introduction = "", $open_time = "", $avg_price = "")
    {
        $base_info = array();
        $base_info['poi_id'] = $poi_id;
        if (! empty($telephone)) {
            $base_info['telephone'] = $telephone;
        }
        if (! empty($photo_list)) {
            $base_info['photo_list'] = $photo_list;
        }
        if (! empty($recommend)) {
            $base_info['recommend'] = $recommend;
        }
        if (! empty($special)) {
            $base_info['special'] = $special;
        }
        if (! empty($introduction)) {
            $base_info['introduction'] = $introduction;
        }
        if (! empty($open_time)) {
            $base_info['open_time'] = $open_time;
        }
        if (! empty($avg_price)) {
            $base_info['avg_price'] = $avg_price;
        }
        $params = array();
        $params['business']['base_info'] = $base_info;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'updatepoi?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
