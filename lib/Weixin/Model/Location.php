<?php
namespace Weixin\Model;

class Location
{

    /**
     * id
     * 门店Id
     * 否
     */
    public $id = NULL;

    /**
     * business_name
     * 门店名称
     * 是
     */
    public $business_name = NULL;

    /**
     * branch_name
     * 分店名
     * 否
     */
    public $branch_name = NULL;

    /**
     * province
     * 门店所在的省
     * 是
     */
    public $province = NULL;

    /**
     * city
     * 门店所在的市
     * 是
     */
    public $city = NULL;

    /**
     * district
     * 门店所在的区
     * 是
     */
    public $district = NULL;

    /**
     * address
     * 门店所在的详细街道地址
     * 是
     */
    public $address = NULL;

    /**
     * telephone
     * 门店的电话
     * 是
     */
    public $telephone = NULL;

    /**
     * category
     * 门店的类型（酒店、餐饮、购物...）
     * 是
     */
    public $category = NULL;

    /**
     * longitude
     * 门店所在地理位置的经度
     * 是
     */
    public $longitude = NULL;

    /**
     * latitude
     * 门店所在地理位置的纬度
     * 是
     */
    public $latitude = NULL;

    public function __construct($business_name, $branch_name, $province, $city, $district, $address, $telephone, $category, $longitude, $latitude)
    {
        $this->business_name = $business_name; // 门店名称
        $this->branch_name = $branch_name; // 门店名称
        $this->province = $province; // 门店所在的省
        $this->city = $city; // 门店所在的市
        $this->district = $district; // 门店所在的区
        $this->address = $address; // 门店所在的详细街道地址
        $this->telephone = $telephone; // 门店的电话
        $this->category = $category; // 门店的类型（酒店、餐饮、购物...）
        $this->longitude = $longitude; // 门店所在地理位置的经度
        $this->latitude = $latitude; // 门店所在地理位置的纬度
    }

    public function set_id($id)
    {
        $this->id = $id;
    }
}
