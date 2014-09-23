<?php
namespace Weixin\ShortUrlManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 长链接转短链接接口
 *
 * 将一条长链接转成短链接。
 *
 * 主要使用场景：
 * 开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，
 * 将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinShortUrlManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/shorturl/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 将一条长链接转成短链接
     */
    public function long2short($long_url)
    {
        /**
         * 接口调用请求说明
         *
         * http请求方式: POST
         * https://api.weixin.qq.com/cgi-bin/shorturl?access_token=ACCESS_TOKEN
         * 参数说明
         *
         * 参数	是否必须	说明
         * access_token 是 调用接口凭证
         * action 是 此处填long2short，代表长链接转短链接
         * long_url 是 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
         */
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['action'] = "long2short";
        $params['long_url'] = $long_url;
        $rst = $this->weixin->post($this->_url, $params);
        if (! empty($rst['errcode'])) {
            /**
             * {"errcode":40013,"errmsg":"invalid appid"}
             */
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            /**
             * {"errcode":0,"errmsg":"ok","short_url":"http:\/\/w.url.cn\/s\/AvCo6Ih"}
             */
            return $rst;
        }
    }
}
