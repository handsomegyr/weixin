<?php
namespace Weixin\SemanticManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 语义理解接口
 * 微信开放平台语义理解接口调用（http请求）简单方便，
 * 用户无需掌握语义理解及相关技术，
 * 只需根据自己的产品特点，
 * 选择相应的服务即可搭建一套智能语义服务。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 * @author young <youngyang@icatholic.net.cn>
 */
class WeixinSemanticManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/semantic/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 发送语义理解请求
     *
     * 接口调用请求说明
     * http请求方式: POST（请使用https协议）
     * https://api.weixin.qq.com/semantic/semproxy/search?access_token=YOUR_ACCESS_TOKEN
     *
     * POST数据格式：JSON
     * POST数据例子：
     * {
     * "query":"查一下明天从北京到上海的南航机票",
     * "city":"北京",
     * "category": "flight,hotel",
     * "appid":"wxaaaaaaaaaaaaaaaa",
     * "uid":"123456"
     * }
     * 参数说明
     *
     * 参数 是否必须 参数类型 说明
     * access_token 是 String 根据appid和appsecret获取到的token
     * query 是 String 输入文本串
     * category 是 String 需要使用的服务类型，多个用“，”隔开，不能为空
     * latitude 见接口协议文档 Float 纬度坐标，与经度同时传入；与城市二选一传入
     * longitude 见接口协议文档 Float 经度坐标，与纬度同时传入；与城市二选一传入
     * city 见接口协议文档 String 城市名称，与经纬度二选一传入
     * region 见接口协议文档 String 区域名称，在城市存在的情况下可省；与经纬度二选一传入
     * appid 是 String 公众号唯一标识，用于区分公众号开发者
     * uid 否 String 用户唯一id（非开发者id），用户区分公众号下的不同用户（建议填入用户openid），如果为空，则无法使用上下文理解功能。appid和uid同时存在的情况下，才可以使用上下文理解功能。
     * 注：单类别意图比较明确，识别的覆盖率比较大，所以如果只要使用特定某个类别，建议将category只设置为该类别。
     *
     * 返回说明 正常情况下，微信会返回下述JSON数据包:
     *
     * {
     * “errcode”:0,
     * “query”:”查一下明天从北京到上海的南航机票”,
     * “type”:”flight”,
     * “semantic”:{
     * “details”:{
     * “start_loc”:{
     * “type”:”LOC_CITY”,
     * “city”:”北京市”,
     * “city_simple”:”北京”,
     * “loc_ori”:”北京”
     * },
     * “end_loc”: {
     * “type”:”LOC_CITY”,
     * “city”:”上海市”,
     * “city_simple”:”上海”,
     * “loc_ori”:”上海”
     * },
     * “start_date”: {
     * “type”:”DT_ORI”,
     * “date”:”2014-03-05”,
     * “date_ori”:”明天”
     * },
     * “airline”:”中国南方航空公司”
     * },
     * “intent”:”SEARCH”
     * }
     * 返回参数说明
     *
     * 参数 是否必须 参数类型 说明
     * errcode 是 Int 表示请求后的状态
     * query 是 String 用户的输入字符串
     * type 是 String 服务的全局类型id，详见协议文档中垂直服务协议定义
     * semantic 是 Object 语义理解后的结构化标识，各服务不同
     * result 否 Array 部分类别的结果
     * answer 否 String 部分类别的结果html5展示，目前不支持
     * text 否 String 特殊回复说明
     *
     * @return mixed
     */
    public function search($query, $category, $latitude, $longitude, $city, $region, $appid, $uid)
    {
        $access_token = $this->weixin->getToken();
        $params = array(
            "query" => $query,
            "category" => $category,
            "appid" => $appid,
            "uid" => $uid
        );
        if (! empty($city)) {
            $params["city"] = $city;
        } else {
            $params["latitude"] = $latitude;
            $params["longitude"] = $longitude;
        }
        if (! empty($region)) {
            $params["region"] = $region;
        }
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'semproxy/search?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
