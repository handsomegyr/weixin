<?php
namespace Weixin\QrcodeManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 推广支持接口
 *
 * 为了满足用户渠道推广分析的需要，公众平台提供了生成带参数二维码的接口。
 * 使用该接口可以获得多个带不同场景值的二维码，用户扫描后，公众号可以接收到事件推送。
 * 目前有2种类型的二维码，分别是临时二维码和永久二维码，
 * 前者有过期时间，最大为1800秒，但能够生成较多数量，
 * 后者无过期时间，数量较少（目前参数只支持1--100000）。
 * 两种二维码分别适用于帐号绑定、用户来源统计等场景。
 * 用户扫描带场景值二维码时，可能推送以下两种事件：
 * 如果用户还未关注公众号，则用户可以关注公众号，关注后微信会将带场景值关注事件推送给开发者。
 * 如果用户已经关注公众号，在用户扫描后会自动进入会话，微信也会将带场景值扫描事件推送给开发者。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinQrcodeManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 创建二维码ticket
     *
     * 每次创建二维码ticket需要提供一个开发者自行设定的参数（scene_id），分别介绍临时二维码和永久二维码的创建二维码ticket过程。
     *
     * 临时二维码请求说明
     *
     * http请求方式: POST
     * URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
     * POST数据格式：json
     * POST数据例子：{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
     * 永久二维码请求说明
     *
     * http请求方式: POST
     * URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
     * POST数据格式：json
     * POST数据例子：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
     * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
     * {"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
     * 参数说明
     *
     * 参数	说明
     * expire_seconds	该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为30秒。
     * action_name	二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
     * action_info	二维码详细信息
     * scene_id	场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     * scene_str	场景值ID（字符串形式的ID），字符串类型，长度限制为1到64，仅永久二维码支持此字段
     * 返回说明
     *
     * 正确的Json返回结果:
     *
     * {"ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==","expire_seconds":60,"url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"}
     * 参数	说明
     * ticket	获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     * expire_seconds	该二维码有效时间，以秒为单位。 最大不超过2592000（即30天）。
     * url	二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
     * 错误的Json返回示例:
     *
     * {"errcode":40013,"errmsg":"invalid appid"}
     *
     * @author Kan
     *        
     */
    public function create($scene_id, $isTemporary = true, $expire_seconds = 0)
    {
        $params = array();
        if ($isTemporary) {
            $params['expire_seconds'] = min($expire_seconds, 1800);
            $params['action_name'] = "QR_SCENE";
            $params['action_info']['scene']['scene_id'] = $scene_id;
        } else {
            if (is_numeric($scene_id)) {
                $params['action_name'] = "QR_LIMIT_SCENE";
                $params['action_info']['scene']['scene_id'] = min($scene_id, 100000);
            } else {
                $params['action_name'] = "QR_LIMIT_STR_SCENE";
                $params['action_info']['scene']['scene_str'] = $scene_id;
            }
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'create?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明
            // 正确的Json返回结果:
            // {"ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhvMVp4NDNMRnNRAAIEesLvUQMECAcAAA==","expire_seconds":1800}
            return $rst;
        }
    }

    /**
     * 通过ticket换取二维码
     * 获取二维码ticket后，开发者可用ticket换取二维码图片。请注意，本接口无须登录态即可调用
     */
    public function getQrcodeUrl($ticket)
    {
        // 请求说明
        // HTTP GET请求（请使用https协议）
        // https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=TICKET
        // 返回说明
        // ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}";
    }
}
