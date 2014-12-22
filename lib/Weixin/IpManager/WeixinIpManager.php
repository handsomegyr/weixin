<?php
namespace Weixin\IpManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 获取微信服务器IP地址接口
 * 如果公众号基于安全等考虑，需要获知微信服务器的IP地址列表，以便进行相关限制，可以通过该接口获得微信服务器IP地址列表。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 * @author young <youngyang@icatholic.net.cn>
 */
class WeixinIpManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 获取微信服务器IP地址接口
     * 如果公众号基于安全等考虑，需要获知微信服务器的IP地址列表，以便进行相关限制，可以通过该接口获得微信服务器IP地址列表。
     *
     * 接口调用请求说明
     *
     * http请求方式: GET
     * https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=ACCESS_TOKEN
     * 参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 公众号的access_token
     * 返回说明
     *
     * 正常情况下，微信会返回下述JSON数据包给公众号：
     *
     * {
     * "ip_list":["127.0.0.1","127.0.0.1"]
     * }
     * 参数	说明
     * ip_list 微信服务器IP地址列表
     * 错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
     *
     * {"errcode":40013,"errmsg":"invalid appid"}
     *
     * @return mixed
     */
    public function getcallbackip()
    {
        $params = array();
        $access_token = $this->weixin->getToken();
        $rst = $this->weixin->get($this->_url . 'getcallbackip?access_token=' . $access_token, $params);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
