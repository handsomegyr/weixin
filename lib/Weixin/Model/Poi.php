<?php
namespace Weixin\Model;

/**
 * POI 门店
 *
 * @author Kan
 *        
 */
class Poi
{

    /**
     * poi_id
     * 门店Id
     * 否
     */
    public $poi_id = NULL;

    /**
     * sid 商户自己的 id，用于后续审核通过收到 poi_id 的通知时，做对应关系。请商户自己保证唯一识别性 否
     */
    public $sid = NULL;

    /**
     * business_name 门店名称（仅为商户名，如：国美、麦当劳，不应包含地区、店号等信息，错误示例：北京国美）是
     */
    public $business_name = NULL;

    /**
     * branch_name 分店名称（不应包含地区信息、不应与门店名重复，错误示例：北京王府井店）否
     */
    public $branch_name = NULL;

    /**
     * province 门店所在的省份（直辖市填城市名,如：北京市） 是
     */
    public $province = NULL;

    /**
     * city 门店所在的城市 是
     */
    public $city = NULL;

    /**
     * district 门店所在地区 否
     */
    public $district = NULL;

    /**
     * address 门店所在的详细街道地址（不要填写省市信息） 是
     */
    public $address = NULL;

    /**
     * telephone 门店的电话（纯数字，区号、分机号均由“-”隔开） 是
     */
    public $telephone = NULL;

    /**
     * categories 门店的类型（详细分类参见分类附表，不同级分类用“,”隔开，如：美食，川菜，火锅）是
     */
    public $categories = NULL;

    /**
     * offset_type 坐标类型，1 为火星坐标（目前只能选 1） 是
     */
    public $offset_type = NULL;

    /**
     * longitude 门店所在地理位置的经度 是
     */
    public $longitude = NULL;

    /**
     * latitude 门店所在地理位置的纬度（经纬度均为火星坐标，最好选用腾讯地图标记的坐标）是
     */
    public $latitude = NULL;

    /**
     * photo_list 图片列表，url 形式，可以有多张图片，尺寸为640*340px。必须为上一接口生成的 url 是
     */
    public $photo_list = NULL;

    /**
     * recommend 推荐品，餐厅可为推荐菜；酒店为推荐套房；景点为推荐游玩景点等，针对自己行业的推荐内容 否
     */
    public $recommend = NULL;

    /**
     * special 特色服务，如免费 wifi，免费停车，送货上门等商户能提供的特色功能或服务 是
     */
    public $special = NULL;

    /**
     * introduction 商户简介，主要介绍商户信息等 否
     */
    public $introduction = NULL;

    /**
     * open_time 营业时间，24 小时制表示，用“-”连接，如8:00-20:00 是
     */
    public $open_time = NULL;

    /**
     * avg_price 人均价格，大于 0 的整数 否
     */
    public $avg_price = NULL;

    /**
     * 可用状态
     */
    public $available_state = 0;

    /**
     * 更新状态
     */
    public $update_status = 0;

    public function __construct($sid, $business_name, $branch_name, $province, $city, $district, $address, $telephone, array $categories, $offset_type, $longitude, $latitude, array $photo_list, $recommend, $special, $introduction, $open_time, $avg_price)
    {
        $this->sid = $sid; // 商户自己的 id，用于后续审核通过收到 poi_id 的通知时，做对应关系。请商户自己保证唯一识别性 否
        $this->business_name = $business_name; // 门店名称（仅为商户名，如：国美、麦当劳，不应包含地区、店号等信息，错误示例：北京国美）是
        $this->branch_name = $branch_name; // 分店名称（不应包含地区信息、不应与门店名重复，错误示例：北京王府井店）否
        $this->province = $province; // 门店所在的省份（直辖市填城市名,如：北京市） 是
        $this->city = $city; // 门店所在的城市 是
        $this->district = $district; // 门店所在地区 否
        $this->address = $address; // 门店所在的详细街道地址（不要填写省市信息） 是
        $this->telephone = $telephone; // 门店的电话（纯数字，区号、分机号均由“-”隔开） 是
        $this->categories = $categories; // 门店的类型（详细分类参见分类附表，不同级分类用“,”隔开，如：美食，川菜，火锅）是
        $this->offset_type = $offset_type; // 坐标类型，1 为火星坐标（目前只能选 1） 是
        $this->longitude = $longitude; // 门店所在地理位置的经度 是
        $this->latitude = $latitude; // 门店所在地理位置的纬度（经纬度均为火星坐标，最好选用腾讯地图标记的坐标）是
        $this->photo_list = $photo_list; // 图片列表，url 形式，可以有多张图片，尺寸为640*340px。必须为上一接口生成的 url 是
        $this->recommend = $recommend; // 推荐品，餐厅可为推荐菜；酒店为推荐套房；景点为推荐游玩景点等，针对自己行业的推荐内容 否
        $this->special = $special; // 特色服务，如免费 wifi，免费停车，送货上门等商户能提供的特色功能或服务 是
        $this->introduction = $introduction; // 商户简介，主要介绍商户信息等 否
        $this->open_time = $open_time; // 营业时间，24 小时制表示，用“-”连接，如8:00-20:00 是
        $this->avg_price = $avg_price; // 人均价格，大于 0 的整数 否
    }

    public function set_poi_id($poi_id)
    {
        $this->poi_id = $poi_id;
    }
    
    public function set_available_state($available_state)
    {
        $this->available_state = $available_state;
    }
    
    public function set_update_status($update_status)
    {
        $this->update_status = $update_status;
    }
}
