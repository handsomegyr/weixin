<?php
namespace Weixin\CardManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;
use Weixin\Model\CardBase;

/**
 * 微信卡券接口
 * 为丰富微信平台使用场景，增加卡券功能，并开放接口供商家调用。
 * 目前卡券支持优惠券（代金券、折扣券、礼品券、团购券）、会员卡、门票、电影票、飞机票、红包。
 * 商户通过在微信后台生成卡券（1.1 创建卡券接口），获取card_id，并标注可领取的库存数量，
 * 微信侧会对商户生成的卡券进行审核，审核不超过3 个工作日。完成后通过事件推送方式告知审核结果。
 * 审核通过后，商户可通过二维码（2.1 生成卡券二维码接口）
 * 或JS API（2.2 添加到卡包JS API）引导用户将卡券添加至微信卡包。
 * 微信允许商家自定义code，以支持自有卡券体系的商户使用微信卡券功能。
 * JS API 增加域名限制说明，公众号会话内仅支持调起指定有权限的域名，
 * 详情参考正文（2.2 添加到卡包JS API 、3.2 拉起卡券列表JSAPI）。
 * 所有API 接口POST 的数据只支持utf8 编码。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 * @author young <youngyang@icatholic.net.cn>
 */
class WeixinCardManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/card/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 上传LOGO接口
     * 开发者需调用该接口上传商户图标至微信服务器，获取相应 logo_url，用于卡券创建。
     * 注意事项
     * 1.上传的图片限制文件大小限制 1MB，像素为 300*300，支持 JPG 格式。
     * 2.调用接口获取的 logo_url 进支持在微信相关业务下使用，否则会做相应处理。
     * 接口调用请求说明
     * 协议 https
     * http 请求方式 POST/FROM
     * 请求 Url https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=ACCESS_TOKEN
     * POST 数据格式 json
     * 请求参数说明
     * 参数 说明 是否必填
     * access_token 调用接口凭证 是
     * buffer 文件的数据流是POST 数据
     * 调用示例（使用 curl 命令，用 FORM 表单方式上传一个图片） ：
     * curl –F buffer=@test.jpg
     * 返回数据说明
     * 返回正确的示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * {"url":"http://mmbiz.qpic.cn/mmbiz/iaL1LJM1mF9aRKPZJkmG8xXhiaHqkKSVMMWeN3hLut7X7hicFNjakmxibMLGWpXrEXB33367o7zHN0CwngnQY7zb7g/0"}
     * }
     * 返回错误的示例
     * {"errcode":40009,"errmsg":"invalid image size"}
     * 字段 说明
     * url 商户 logo_url
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @throws Exception
     * @return Ambigous <\Weixin\Http\mixed, multitype:, string, number, boolean, mixed>
     */
    public function uploadLogoUrl($logo)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['buffer'] = '@' . $logo;
        $rst = $this->weixin->post('https://api.weixin.qq.com/cgi-bin/media/uploadimg', $params, true);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 创建卡券 ----获取颜色列表接口
     * 接口说明
     * 获得卡券的最新颜色列表，用于创建卡券。
     * 接口调用请求说明
     * 协议https
     * http 请求方式GET / POST
     * 请求Url https://api.weixin.qq.com/card/getcolors?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "colors":[
     * {"name":"Color010","value":"#61ad40"},
     * {"name":"Color020","value":"#169d5c"},
     * {"name":"Color030","value":"#239cda"}
     * ]
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * colors 列表
     * name 可以填入的color 名称
     * value 对应的颜色数值
     *
     * @return mixed
     */
    public function getcolors()
    {
        $params = array();
        $access_token = $this->weixin->getToken();
        $rst = $this->weixin->get($this->_url . 'getcolors?access_token=' . $access_token, $params);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 创建卡券 ----创建卡券
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/create?access_token=ACCESS_TOKEN
     * POST 数据格式json
     * 请求参数说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * 数据示例：
     * { "card": {
     * "card_type": "GROUPON",
     * "groupon": {
     * "base_info": {
     * "logo_url":
     * "http:\/\/www.supadmin.cn\/uploads\/allimg\/120216\/1_120216214725_1.jpg",
     * "brand_name": "海底捞",
     * "code_type":"CODE_TYPE_TEXT",
     * "title": "132 元双人火锅套餐",
     * "sub_title": "",
     * "color": "Color010",
     * "notice": "使用时向服务员出示此券",
     * "service_phone": "020-88888888",
     * "description": "不可与其他优惠同享\n 如需团购券发票，请在消费时向商户提出\n 店内均可
     * 使用，仅限堂食\n 餐前不可打包，餐后未吃完，可打包\n 本团购券不限人数，建议2 人使用，超过建议人
     * 数须另收酱料费5 元/位\n 本单谢绝自带酒水饮料",
     * "date_info": {
     * "type": 1,
     * "begin_timestamp": 1397577600,
     * "end_timestamp": 1399910400
     * },
     * "sku": {
     * "quantity": 50000000
     * },
     * "use_limit": 1,
     * "get_limit": 3,
     * "use_custom_code": false,
     * "bind_openid": false,
     * "can_share": true,
     * "can_give_friend"：true,
     * "location_id_list" : [123, 12321, 345345]，
     * "url_name_type": "URL_NAME_TYPE_RESERVATION",
     * "custom_url": "http://www.qq.com",
     * "source": "大众点评"
     * },
     * "deal_detail": "以下锅底2 选1（有菌王锅、麻辣锅、大骨锅、番茄锅、清补凉锅、酸菜鱼锅可
     * 选）：\n 大锅1 份12 元\n 小锅2 份16 元\n 以下菜品2 选1\n 特级肥牛1 份30 元\n 洞庭鮰鱼卷1 份
     * 20 元\n 其他\n 鲜菇猪肉滑1 份18 元\n 金针菇1 份16 元\n 黑木耳1 份9 元\n 娃娃菜1 份8 元\n 冬
     * 瓜1 份6 元\n 火锅面2 个6 元\n 欢乐畅饮2 位12 元\n 自助酱料2 位10 元"}
     * }}
     * 6
     *
     * @return mixed
     */
    public function create(CardBase $card)
    {
        $params = array();
        $params['card'] = $card->getParams4Create();
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'create?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券投放 ----生成卡券二维码
     * 创建卡券后，商户可通过接口生成一张卡券二维码供用户扫码后添加卡券到卡包。
     * 自定义code 的卡券调用接口时，post 数据中需指定code，非自定义code 不需指定，指定openid
     * 同理。指定后的二维码只能被扫描领取一次。
     * 注：该接口仅支持卡券功能，供已开通卡券功能权限的商户（订阅号、服务号）调用。
     * 已认证服务号的商户也可使用高级接口中“生成带参数的二维码”接口生成卡券二维码（请求URL:https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN），
     * post 参数与下表一致。
     * 获取二维码ticket 后，开发者可用ticket 换取二维码图片详情见
     * http://mp.weixin.qq.com/wiki/index.php?title=生成带参数的二维码
     *
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/qrcode/create?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "action_name": "QR_CARD",
     * "action_info": {
     * "card": {
     * "card_id": "pFS7Fjg8kV1IdDz01r4SQwMkuCKc",
     * "code": "198374613512",
     * "openid": "oFS7Fjl0WsZ9AMZqrI80nbIq8xrA",
     * "expire_seconds": "1800",
     * "is_unique_code": false,
     * "outer_id" : 1
     * }
     * }
     * }
     * 字段说明是否必填
     * card_id 卡券ID 是
     * code 指定卡券code 码，只能被领一次。use_custom_code 字段为true 的卡券必须填写，非自定义code 不必填写。否
     * openid 指定领取者的openid，只有该用户能领取。bind_openid 字段为true 的卡券必须填写，非自定义openid 不必填写。否
     * expire_seconds 指定二维码的有效时间，范围是60 ~ 1800 秒。不填默认为永久有效。否
     * is_unique_code 指定下发二维码，生成的二维码随机分配一个code，领取后不可再次扫描。填写true 或false。默认false。否
     * balance 红包余额， 以分为单位。 红包类型必填 （LUCKY_MONEY） ，其他卡券类型不填。否
     * outer_id 领取场景值，用于领取渠道的数据统计，默认值为 0，字段类型为整型。用户领取卡券后触发的事件推送中会带上此自定义场景值。
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhv
     * MVp4NDNMRnNRAAIEesLvUQMECAcAAA=="
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * ticket
     * 获取的二维码ticket，凭借此ticket 可以在有
     * 效时间内换取二维码。
     * 说明：
     * 获取二维码ticket 后，开发者可用ticket 换取二维码图片。换取指引参考：
     * http://mp.weixin.qq.com/wiki/index.php?title=生成带参数的二维码
     *
     * @return mixed
     */
    public function qrcodeCreate($card_id, $code = "", $openid = "", $expire_seconds = null, $is_unique_code = false, $balance = 0, $outer_id = 0)
    {
        $params = array();
        $params['action_name'] = "QR_CARD";
        $params['action_info']['card'] = array();
        $params['action_info']['card']['card_id'] = $card_id;
        if (! empty($code)) {
            $params['action_info']['card']['code'] = $code;
        }
        if (! empty($openid)) {
            $params['action_info']['card']['openid'] = $openid;
        }
        if (! empty($expire_seconds)) {
            $params['action_info']['card']['expire_seconds'] = $expire_seconds;
        }
        $params['action_info']['card']['is_unique_code'] = $is_unique_code;
        
        if (! empty($balance)) {
            $params['action_info']['card']['balance'] = $balance;
        }
        $params['action_info']['card']['outer_id'] = $outer_id;
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'qrcode/create?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取api_ticket
     * api_ticket 是用于调用微信 JSAPI 的临时票据， 有效期为 7200 秒， 通过 access_token来获取。
     * 注：由于获取 api_ticket 的 api 调用次数非常有限，频繁刷新 api_ticket 会导致 api 调用受限，影响自身业务，开发者需在自己的服务存储与更新 api_ticket。
     * 接口调用请求说明
     * 协议 https
     * http 请求方式 GET
     * 请求 Url https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=ACCESS_TOKEN&type=wx_card
     * POST 数据格式 json
     * 请求参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * 返回数据说明
     * 数据示例：
     * 微信卡券接口文档
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
     * "expires_in":7200
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * ticket api_ticket，签名中所需凭证
     * expires_in 有效时间
     */
    public function getApiTicket()
    {
        $params = array();
        $params['type'] = 'wx_card';
        $access_token = $this->weixin->getToken();
        $rst = $this->weixin->get('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $access_token, $params);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券核销部分 ----消耗code
     * 接口说明
     * 消耗code 接口是核销卡券的唯一接口。
     * 自定义code（use_custom_code 为true）的优惠券，在code 被核销时，必须调用此接口。
     * 用于将用户客户端的code 状态变更。
     * 自定义code 的卡券调用接口时， post 数据中需包含card_id，非自定义code 不需上报。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/code/consume?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code":"110201201245"
     * "card_id":"pFS7Fjg8kV1IdDz01r4SQwMkuCKc"
     * }
     * 字段说明是否必填
     * code 要消耗的序列号。是
     * card_id 要消耗序列号所述的card_id，创建卡券时use_custom_code 填写true 时必填。非自定义code不必填写。否
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "card":{"card_id":"pFS7Fjg8kV1IdDz01r4SQwMkuCKc"},
     * "openid":"oFS7Fjl0WsZ9AMZqrI80nbIq8xrA"
     * }
     * 字段说明
     * errcode 错误码，0：正常，40099：该code 已被核销
     * errmsg 错误信息
     * openid 用户openid
     * card_id 卡券ID
     *
     * @return mixed
     */
    public function codeConsume($code, $card_id = "")
    {
        $params = array();
        $params['code'] = $code;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'code/consume?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券核销部分 ----code 解码接口
     *
     * 接口说明
     * code 解码接口支持两种场景：
     * 1.商家获取choos_card_info 后，将card_id 和encrypt_code 字段通过解码接口，获取真实code。
     * 2.卡券内跳转外链的签名中会对code 进行加密处理，通过调用解码接口获取真实code。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/code/decrypt?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "encrypt_code":
     * "XXIzTtMqCxwOaawoE91+VJdsFmv7b8g0VZIZkqf4GWA60Fzpc8ksZ/5ZZ0DVkXdE"
     * }
     * 字段说明是否必填
     * encrypt_code 通过choose_card_info 获取的加密字符串是
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "code":"751234212312"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * code 卡券真实序列号
     *
     * @return mixed
     */
    public function codeDecrypt($encrypt_code)
    {
        $params = array();
        $params['encrypt_code'] = $encrypt_code;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'code/decrypt?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----删除卡券
     *
     * 接口说明
     * 删除卡券接口允许商户删除任意一类卡券。删除卡券后，该卡券对应已生成的领取用二维码、添加到卡包JS API 均会失效。
     * 注意：如用户在商家删除卡券前已领取一张或多张该卡券依旧有效。即删除卡券不能删除已被用户领取，保存在微信客户端中的卡券。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/delete?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "card_id": "p1Pj9jr90_SQRaVqYI239Ka1erkI"
     * }
     * 字段说明
     * card_id 卡券ID
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function delete($card_id)
    {
        $params = array();
        $params['card_id'] = $card_id;
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'delete?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----查询code
     * 接口说明
     * 调用查询code 接口可获取code 的有效性（非自定义code），该code 对应的用户openid、卡券有效期等信息。
     * 自定义code（use_custom_code 为true）的卡券调用接口时，post 数据中需包含card_id，非自定义code 不需上报。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/code/get?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code":"110201201245"
     * }
     * 字段说明
     * code 要查询的序列号
     * card_id 要消耗序列号所述的card_id ， 生成券时use_custom_code 填写true 时必填。非自定义code 不必填写。
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "openid":"oFS7Fjl0WsZ9AMZqrI80nbIq8xrA",
     * "card":{
     * "card_id":"pFS7Fjg8kV1IdDz01r4SQwMkuCKc",
     * "begin_time": 1404205036,
     * "end_time": 1404205036,
     * }
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * openid 用户openid
     * card_id 卡券ID
     * begin_time 起始使用时间
     * end_time 结束时间注：固定时长有效期会根据用户实际领取时间转换，如用户2013 年10 月1 日领取，固定时长有效期为90 天，即有效时间为2013 年10 月1 日-12 月29 日有效。
     *
     *
     * @return mixed
     */
    public function codeGet($code, $card_id = "")
    {
        $params = array();
        $params['code'] = $code;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'code/get?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----批量查询卡列表
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/batchget?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "offset": 0,
     * "count": 10
     * }
     * 字段说明是否必填
     * offset 查询卡列表的起始偏移量，从0 开始，即offset: 5 是指从从列表里的第六个开始读取。是
     * count 需要查询的卡片的数量（数量最大50） 是
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "card_id_list":["ph_gmt7cUVrlRk8swPwx7aDyF-pg"],
     * "total_num":1
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * card_id_list 卡id 列表
     * total_num 该商户名下card_id 总数
     *
     *
     * @return mixed
     */
    public function batchget($offset = 0, $count = 50)
    {
        $params = array();
        $params['offset'] = $offset;
        $params['count'] = min($count, 50);
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'batchget?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----查询卡券详情
     *
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/get?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "card_id":"pFS7Fjg8kV1IdDz01r4SQwMkuCKc"
     * }
     * 字段说明
     * card_id 卡券ID
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode": 0,
     * "errmsg": "ok",
     * "card": {
     * "card_type": "GROUPON",
     * "groupon": {
     * "base_info": {
     * "status": 1,
     * "id": "p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "logo_url":
     * "http://www.supadmin.cn/uploads/allimg/120216/1_120216214725_1.jpg",
     * "appid": "wx588def6b0089dd48",
     * "code_type": "CODE_TYPE_TEXT",
     * "brand_name": "海底捞",
     * "title": "132 元双人火锅套餐",
     * "sub_title": "",
     * "date_info": {
     * "type": 1,
     * "begin_timestamp": 1397577600,
     * "end_timestamp": 1399910400
     * },
     * "color": "#3373bb",
     * "notice": "使用时向服务员出示此券",
     * "service_phone": "020-88888888",
     * "description": "不可与其他优惠同享\n 如需团购券发票，请在消费时向商户提出\n 店
     * 内均可使用，仅限堂食\n 餐前不可打包，餐后未吃完，可打包\n 本团购券不限人数，建议2 人使用，超
     * 过建议人数须另收酱料费5 元/位\n 本单谢绝自带酒水饮料",
     * "use_limit": 1,
     * "get_limit": 3,
     * "can_share": true,
     * "location_id_list" : [123, 12321, 345345]
     * "url_name_type": "URL_NAME_TYPE_RESERVATION",
     * "custom_url": "http://www.qq.com",
     * "source": "大众点评"
     * "sku": {
     * "quantity": 0
     * }
     * },
     * "deal_detail": "以下锅底2 选1（有菌王锅、麻辣锅、大骨锅、番茄锅、清补凉锅、酸菜鱼
     * 锅可选）：\n 大锅1 份12 元\n 小锅2 份16 元\n 以下菜品2 选1\n 特级肥牛1 份30 元\n 洞庭鮰鱼卷
     * 1 份20 元\n 其他\n 鲜菇猪肉滑1 份18 元\n 金针菇1 份16 元\n 黑木耳1 份9 元\n 娃娃菜1 份8 元
     * \n 冬瓜1 份6 元\n 火锅面2 个6 元\n 欢乐畅饮2 位12 元\n 自助酱料2 位10 元",
     * }
     * }
     * }
     * }
     * 字段详情：
     * 字段说明
     * card 卡券信息部分
     * card_type
     * 卡券类型。
     * 通用券：GENERAL_COUPON;
     * 团购券：GROUPON;
     * 折扣券：DISCOUNT;
     * 礼品券：GIFT;
     * 代金券：CASH;
     * 会员卡：MEMBER_CARD;
     * 门票：SCENIC_TICKET；
     * 电影票：MOVIE_TICKET；
     * 飞机票：BOARDING_PASS；
     * 红包: LUCKY_MONEY；
     * general_coupon
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * default_detail 描述文本。
     * groupon
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * deal_detail 团购券专用，团购详情。
     * gift
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * gift 礼品券专用，表示礼品名字。
     * cash
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * least_cost 代金券专用，表示起用金额（单位为分）。
     * reduce_cost 代金券专用，表示减免金额（单位为分）。
     * discount
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * discount 折扣券专用，表示打折额度（百分比）。填30就是七折。
     * member_card
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * supply_bonus 是否支持积分，填写true 或false，如填写true，积分相关字段均为必填。填写false，积分字段无需填写。储值字段处理方式相同。
     * supply_balance 是否支持储值，填写true 或false。
     * bonus_cleared 积分清零规则。
     * bonus_rules 积分规则。
     * balance_rules 储值说明。
     * prerogative 特权说明。
     * bind_old_card_url 绑定旧卡的url。与“activate_url”二选一必填。
     * activate_url激活会员卡的url。与“bind_old_card_url”二选一必填。
     * scenic_ticket
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * ticket_class 票类型，例如平日全票，套票等。
     * guide_url 导览图url。
     * movie_ticket
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * detail 电影票详情。
     * boarding_pass
     * base_info 基本的卡券数据，见下表，所有卡券通用。
     * from 起点。
     * to 终点。
     * flight 航班。
     * departure_time 起飞时间。
     * landing_time 降落时间。
     * check_in_url 在线值机的链接。
     * air_model 机型。
     * lucky_money base_info 基本的卡券数据，见下表，所有卡券通用。
     * base_info 字段描述
     * 字段说明
     * base_info 基本的卡券数据
     * id card_id
     * logo_url 卡券的商户logo
     * code_type code 码展示类型"CODE_TYPE_TEXT"，文本"CODE_TYPE_BARCODE"，一维码"CODE_TYPE_QRCODE"，二维码
     * brand_name 商户名字
     * title 券名
     * color 券颜色。色彩规范标注值对应的色值。如#3373bb
     * notice 使用提醒。（一句话描述，展示在首页）
     * service_phone 客服电话。
     * description 使用说明。长文本描述，可以分行。
     * use_limit 每人使用次数限制。
     * get_limit 每人最大领取次数。
     * use_custom_code 是否自定义code 码。
     * bind_openid 是否指定用户领取。
     * can_share 领取卡券原生页面是否可分享，填写true 或false，true 代表可分享。默认可分享。
     * can_give_friend 卡券是否可转赠，填写true 或false,true 代表可转赠。默认可转赠。
     * location_id_list 门店位置ID。
     * date_info 使用日期，有效期的信息。
     * type 使用时间的类型。1：固定日期区间，2：固定时长（自领取后按天算）
     * begin_timestamp 固定日期区间专用，表示起用时间。
     * end_timestamp 固定日期区间专用，表示结束时间。
     *
     *
     * @return mixed
     */
    public function get($card_id)
    {
        $params = array();
        $params['card_id'] = $card_id;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'get?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----更改code
     * 接口说明
     * 为确保转赠后的安全性，微信允许自定义code的商户对已下发的code进行更改。
     * 注：为避免用户疑惑，建议仅在发生转赠行为后（发生转赠后，微信会通过事件推送的方式告
     * 知商户被转赠的卡券code）对用户的code进行更改。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/code/update?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code": "12345678",
     * "card_id": "p1Pj9jr90_SQRaxxxxxxxx",
     * "new_code": "3495739475"
     * }
     * 返回数据说明
     * 数据示例：
     * {
     * 字段说明
     * code 卡券的code 编码
     * card_id 卡券ID
     * new_code 新的卡券code 编码
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function codeUpdate($code, $card_id, $new_code)
    {
        $params = array();
        $params['code'] = $code;
        $params['card_id'] = $card_id;
        $params['new_code'] = $new_code;
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'code/update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----设置卡券失效接口
     * 接口说明
     * 为满足改票、退款等异常情况，可调用卡券失效接口将用户的卡券设置为失效状态。
     * 注：设置卡券失效的操作不可逆，即无法将设置为失效的卡券调回有效状态，商家须慎重调用该接口。
     * 接口调用请求说明
     * 协议https
     * http 请求方式GET / POST
     * 请求Url https://api.weixin.qq.com/card/code/unavailable?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code": "12312313"
     * }
     * 或自定义code 卡券的请求。
     * {
     * "code": "12312313",
     * "card_id": "xxxx_card_id"
     * }
     * 字段说明
     * code 需要设置为失效的code
     * card_id 自定义code 的卡券必填。非自定义code 的卡券不填。
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     *
     *
     * @return mixed
     */
    public function codeUnavailable($code, $card_id = "")
    {
        $params = array();
        $params['code'] = $code;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'code/unavailable?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 卡券管理 ----更改卡券信息接口
     * 接口说明
     * 支持更新部分通用字段及特殊卡券（会员卡、飞机票、电影票、红包）中特定字段的信息。
     * 注：若卡券当前状态为审核失败或者审核成功，调用该接口更新信息后会重新送审，卡券状态变更
     * 为待审核。已被用户领取的卡券会实时更新票面信息。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/update?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "card_id": "xxxxxxxxxxxxx",
     * "member_card": {
     * "base_info": {
     * "logo_url":
     * "http:\/\/www.supadmin.cn\/uploads\/allimg\/120216\/1_120216214725_1.jpg",
     * "color": "Color010",
     * "notice": "使用时向服务员出示此券",
     * "service_phone": "020-88888888",
     * "description": "不可与其他优惠同享\n 如需团购券发票，请在消费时向商户提出\n
     * 店内均可使用，仅限堂食\n 餐前不可打包，餐后未吃完，可打包\n 本团购券不限人数，建议2 人使用，超
     * 过建议人数须另收酱料费5 元/位\n 本单谢绝自带酒水饮料"
     * "location_id_list" : [123, 12321, 345345]
     * },
     * "bonus_cleared": "aaaaaaaaaaaaaa",
     * "bonus_rules": "aaaaaaaaaaaaaa",
     * "prerogative": ""
     * }
     * }
     * 字段说明是否必填
     * card_id 卡券id 是
     * member_card
     * bonus_cleared 积分清零规则否
     * bonus_rules 积分规则否
     * balance_rules 储值说明否
     * prerogative 特权说明否
     * boarding_pass
     * departure_time 起飞时间否
     * landing_time 降落时间否
     * scenic_ticket guide_url 导览图url 否
     * movie_ticket detail 电影票详情否
     * base_info 字段描述
     * 字段说明 是否必填
     * base_info 基本的卡券数据。是
     * logo_url 卡券的商户logo。否
     * color 券颜色。按色彩规范标注填写Color010-Color100否
     * notice 使用提醒。（一句话描述，展示在首页）否
     * service_phone 客服电话。否
     * description使用说明。长文本描述，可以分行。否
     * location_id_list 门店id 列表。否
     * url_name_type 自定义跳转入口的名字。否
     * custom_url 自定义跳转的url。否
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     *
     * @return mixed
     */
    public function update(CardBase $card)
    {
        $params = $card->getParams4Update();
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 库存修改接口
     *  接口说明
     * 增减某张卡券的库存。
     *  接口调用请求说明
     * 协议 https
     * http 请求方式 POST
     * 请求 Url https://api.weixin.qq.com/card/modifystock?access_token=TOKEN
     * POST 数据格式 json
     *  请求参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     *  POST 数据
     * 数据示例：
     * 微信卡券接口文档
     * {
     * "card_id": "xxxx_card_id",
     * "increase_stock_value": 1231231,
     * "reduce_stock_value": 1231231
     * }
     * 字段 说明
     * card_id 卡券 ID。
     * increase_stock_value 增加多少库存，可以不填或填 0
     * reduce_stock_value 减少多少库存，可以不填或填 0
     *  返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * errmsg 错误信
     */
    public function modifyStock($card_id, $increase_stock_value = 0, $reduce_stock_value = 0)
    {
        $params = array();
        $params['card_id'] = $card_id;
        $params['increase_stock_value'] = $increase_stock_value;
        $params['reduce_stock_value'] = $reduce_stock_value;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'card/modifystock?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 开发者权限 ----设置测试用户白名单
     *
     * 接口说明
     * 由于卡券有审核要求，为方便公众号调试，可以设置一些测试帐号，这些帐号可以领取未通过审核的卡券，体验整个流程。
     * 注：同时支持“openid”、“username”两种字段设置白名单，总数上限为10 个。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/testwhitelist/set?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "openid": [
     * "o1Pj9jmZvwSyyyyyyBa4aULW2mA",
     * "o1Pj9jmZvxxxxxxxxxULW2mA"
     * ],
     * "username": [
     * "afdvvf",
     * "abcd"
     * ]
     * }
     * 字段说明
     * openid 测试的openid 列表
     * username 测试的微信号列表
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     *
     *
     * @return mixed
     */
    public function testwhitelistSet(array $openids, array $usernames)
    {
        $params = array();
        $params['openid'] = $openids;
        $params['username'] = $usernames;
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'testwhitelist/set?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----会员卡----激活/绑定会员卡
     *
     * 接口说明
     * 支持会员卡激活或绑定，update会员卡编号及积分信息。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/membercard/activate?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "init_bonus": 100,
     * "init_balance": 200,
     * "membership_number": "AAA00000001",
     * "code": "12312313",
     * "card_id": "xxxx_card_id",
     * "custom_field_value1": "xxxxx",
     * }
     * 或
     * {
     * "bonus": “www.xxxx.com”,
     * "balance": “www.xxxx.com”,
     * "membership_number": "AAA00000001",
     * "code": "12312313",
     * "card_id": "xxxx_card_id"，
     * "custom_field_value1": "xxxxx",
     * }
     * 字段说明
     * init_bonus 初始积分，不填为0。
     * init_balance 初始余额，不填为0。
     * bonus_url 积分查询，仅用于init_bonus无法同步的情况填写，调转外链查询积分 否
     * balance_url 余额查询，仅用于init_balance无法同步的情况填写，调转外链查询积分。 否
     * membership_number 必填，会员卡编号，作为序列号显示在用户的卡包里。
     * code 创建会员卡时获取的code。
     * card_id 卡券ID。自定义code 的会员卡必填card_id，非自定义code 的会员卡不必填。
     * activate_begin_time 激活后的有效起始时间。若不填写默认以创建时的data_info 为准。时间戳格式。 否
     * activate_end_time 激活后的有效截至时间。若不填写默认以创建时的data_info 为准。时间戳格式。 否
     * init_custom_field_value1 创建时字段custom_field1定义类型的初始值，限制为4个汉字，12字节。
     * init_custom_field_value2 创建时字段custom_field2定义类型的初始值，限制为4个汉字，12字节。
     * init_custom_field_value3 创建时字段custom_field3定义类型的初始值，限制为4个汉字，12字节。
     * (说明custom_field1定义类型的初始值
     * FIELD_NAME_TYPE_LEVE 等级
     * FIELD_NAME_TYPE_COUPON 优惠券
     * FIELD_NAME_TYPE_MILEAGE 里程
     * FIELD_NAME_TYPE_STAMP 印花
     * FIELD_NAME_TYPE_ACHIEVEMENT 成就
     * FIELD_NAME_TYPE_DISCOUNT 折扣)
     *
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常。
     * errmsg 错误信息。
     *
     *
     * @return mixed
     */
    public function membercardActivate($membership_number, $code, $card_id, $init_bonus = 0, $init_balance = 0, $init_custom_field_value1 = "", $init_custom_field_value2 = "", $init_custom_field_value3 = "", $bonus_url = "", $balance_url = "", $activate_begin_time = "", $activate_end_time = "")
    {
        $params = array();
        $params['membership_number'] = $membership_number;
        $params['code'] = $code;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        if (! empty($init_custom_field_value1)) {
            $params['init_custom_field_value1'] = $init_custom_field_value1;
        }
        if (! empty($init_custom_field_value2)) {
            $params['init_custom_field_value2'] = $init_custom_field_value2;
        }
        if (! empty($init_custom_field_value3)) {
            $params['init_custom_field_value3'] = $init_custom_field_value3;
        }
        if (! empty($bonus_url)) {
            $params['bonus_url'] = $bonus_url;
        }
        if (! empty($balance_url)) {
            $params['balance_url'] = $balance_url;
        }
        if (! empty($activate_begin_time)) {
            $params['activate_begin_time'] = $activate_begin_time;
        }
        if (! empty($activate_end_time)) {
            $params['activate_end_time'] = $activate_end_time;
        }
        
        $params['init_bonus'] = $init_bonus;
        $params['init_balance'] = $init_balance;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'membercard/activate?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----会员卡----会员卡交易
     *
     *
     * 接口说明
     * 会员卡交易后每次积分及余额变更需通过接口通知微信，便于后续消息通知及其他扩展功能。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/membercard/updateuser?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code": "12312313",
     * "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "record_bonus": "消费30元，获得3积分",
     * "add_bonus": 3,
     * "add_balance": -3000
     * "record_balance": "购买焦糖玛琪朵一杯，扣除金额30元。"
     * }
     * 字段说明是否必填
     * code 要消耗的序列号。是
     * add_bonus 需要变更的积分，扣除积分用“-“表示。否
     * record_bonus 商家自定义积分消耗记录，不超过14 个汉字。否
     * add_balance 需要变更的余额，扣除金额用“-”表示。单位为分否
     * record_balance 商家自定义金额消耗记录，不超过14 个汉字。否
     * card_id 要消耗序列号所述的card_id。自定义code 的会员卡必填。否
     * custom_field_value1 创建时字段custom_field1义类型的最新数值，限制为4个汉字，12字节。 否
     * custom_field_value2 创建时字段custom_field2义类型的最新数值，限制为4个汉字，12字节。 否
     * custom_field_value3 创建时字段custom_field3义类型的最新数值，限制为4个汉字，12字节。 否
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "result_bonus": 100,
     * "result_balance": 200
     * "openid":"oFS7Fjl0WsZ9AMZqrI80nbIq8xrA"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * result_bonus 当前用户积分总额
     * result_balance 当前用户预存总金额
     * openid 用户openid
     *
     *
     * @return mixed
     */
    public function membercardUpdateuser($code, $card_id, $add_bonus = 0, $record_bonus = "", $add_balance = 0, $record_balance = "", $custom_field_value1 = "", $custom_field_value2 = "", $custom_field_value3 = "")
    {
        $params = array();
        $params['code'] = $code;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        $params['add_bonus'] = $add_bonus;
        $params['record_bonus'] = $record_bonus;
        $params['add_balance'] = $add_balance;
        $params['record_balance'] = $record_balance;
        if (! empty($custom_field_value1)) {
            $params['custom_field_value1'] = $custom_field_value1;
        }
        if (! empty($custom_field_value2)) {
            $params['custom_field_value2'] = $custom_field_value2;
        }
        if (! empty($custom_field_value3)) {
            $params['custom_field_value3'] = $custom_field_value3;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'membercard/updateuser?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----会员卡----会员卡公告接口
     * 接口说明
     * 支持开发者调用该接口对指定会员卡用户下发公告消息。
     * 注：每个用户每个月仅能收到4条公告消息。
     *
     * 接口调用请求说明
     * 协议
     * https http
     * 请求方式 POST
     * 请求Url https://api.weixin.qq.com/card/announcement/send?access_token=TOKEN
     * POST数据格式 json
     *
     * 数据示例：
     * {"code": "12312313",
     * "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "text": "会员日特大优惠",
     * "url": "www.xxx.com",
     * "thumb_url": "www.xxx.com",
     * "end_time": 1422724261,
     * }
     * 字段说明是否必填
     * code 会员卡初始code码。是
     * card_id 展示公告的卡券ID。 是
     * end_time 公告展示截止时间 ，Unix时间戳格式，从调用公告接口时刻起不超过七天时长。 是
     * text 公告文字，不超过16个汉字。否url点击公告跳转的URL。否
     * thumb_url 可配置公告过期后出现在历史公告列表中的图片。需调用上传logo接口获取URL。 否
     */
    public function announcementSend($code, $card_id, $end_time, $text = "", $thumb_url = "")
    {
        $params = array();
        $params['code'] = $code;
        $params['card_id'] = $card_id;
        $params['end_time'] = $end_time;
        if (! empty($text)) {
            $params['text'] = $text;
        }
        if (! empty($thumb_url)) {
            $params['thumb_url'] = $thumb_url;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'announcement/send?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----会员卡----更新公告接口
     * 接口说明
     * 支持开发者对某条公告进行内容更新。更新公告不会有消息提醒，且更新时间不能后延。
     *
     * 接口调用请求说明
     * 协议https http
     * 请求方式 POST
     * 请求Url https://api.weixin.qq.com/card/announcement/update?access_token=TOKEN
     * POST数据格式 json
     *
     * 数据示例：
     * {
     * "create_time":1430141581,
     * "code": "12312313",
     * "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "text": "会员日特大优惠",
     * "url": "www.xxx.com",
     * "thumb_url": "www.xxx.com",
     * "end_time": 1422724261,
     * }
     *
     * 字段说明是否必填
     * code 卡券的code码。是
     * card_id 展示公告的卡券ID。是
     * create_time 以创建公告的时间作为更新该用户手上会员卡公告的唯一标识。是
     * text 公告文字，不超过16个汉字。否
     * url 点击公告跳转的URL。否
     * thumb_url 可配置公告过期后出现在历史公告列表中的图片。需调用上传logo接口获取URL。否
     * end_time 公告展示截止时间，Unix时间戳格式。特别注意，更新公告截止时间仅支持前移，不支持后延。否
     * close 填写true则关闭该公告展示。默认为false。特别注意，公告更新为关闭状态后不可恢复。否
     */
    public function announcementUpdate($code, $card_id, $create_time, $text = '', $url = '', $thumb_url = '', $end_time = '', $close = FALSE)
    {
        $params = array();
        $params['code'] = $code;
        $params['card_id'] = $card_id;
        $params['create_time'] = $create_time;
        if (! empty($text)) {
            $params['text'] = $text;
        }
        if (! empty($url)) {
            $params['url'] = $url;
        }
        if (! empty($thumb_url)) {
            $params['thumb_url'] = $thumb_url;
        }
        if (! empty($end_time)) {
            $params['end_time'] = $end_time;
        }
        if (! empty($close)) {
            $params['close'] = $close;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'announcement/update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----会员卡----短信投放会员卡
     *
     * 接口说明
     * 支持商户调用该接口生成可拉起会员卡领取页面的URL。仅限于将URL嵌入短信中，用于投放会员卡。
     * 开发者注意事项
     * 1）该接口权限级别较高，需邮件weixincard@tencent.com申请权限。申请需提供appid及需要调起会员卡的cardid。
     * 2）新增字段outer_id，支持商户将自定义场景值填入，实现短信渠道投放卡券的数据统计。例：设置短信拉起卡包接口中的outer_id字段值为1，
     * 当用户通过短信链接领取卡券时，会触发带有相应场景值（<OuterId>1</OuterId>）的事件推送。
     *
     * 接口调用请求说明
     * 协议https http请求方式POST
     * 请求Url https://api.weixin.qq.com/card/sms/geturl?access_token=TOKEN
     * POST数据格式 json
     *
     * 接口请求说明
     * 数据示例：
     * {
     * "card_id":"pXch-joPiv-iPCSa7qjxEYSbivCg",
     * "code":"237968569845",
     * "outer_id" : 1
     * }
     *
     * 字段说明是否必填
     * card_id生成卡券时获得的card_id。是
     * code卡券code码，自定义code为必填。否
     * outer_id领取场景值，用于领取渠道的数据统计，默认值为0。字段类型为整型。否
     *
     * 字段返回说明
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "url":http://w.url.cn/s/AfXtbyv，
     * "url":"weixin://cardpackage/?encrystr=Q2fS4IbnFr6RF44Wz7BqUS70xz01UYY3Ng3lQeIcmyW79GWc4Cdb1XJhcj0BhAZUXIu4wC-pFnBJqzN_hRFHfA_Xxi5uCbUQEWdndWfxR18"
     * }
     *
     * 字段说明是否必填
     * err_msg ok ，调用成功
     * system error，系统错误
     * missing required fields，缺少必填字段
     * url 嵌入短信中发送给用户的url，返回长链和短链均可调起原生领卡页面。
     */
    public function smsGeturl($card_id, $code = '', $outer_id = 0)
    {
        $params = array();
        $params['card_id'] = $card_id;
        if (! empty($code)) {
            $params['code'] = $code;
        }
        if (! empty($outer_id)) {
            $params['outer_id'] = $outer_id;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'sms/geturl?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----电影票----更新电影票
     *
     * 接口说明
     * 领取电影票后通过调用“更新电影票”接口update 电影信息及用户选座信息。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/movieticket/updateuser?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code" : "277217129962",
     * "card_id": "p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "ticket_class": "4D",
     * "show_time": 1408493192,
     * "duration"：120,
     * "screening_room": "5 号影厅",
     * "seat_number": [ "5 排14 号" , "5 排15 号" ]
     * }
     * 字段说明是否必填
     * code 电影票的序列号。是
     * card_id 电影票card_id。自定义code 的电影票为必填，非自定义code 的电影票不必填。否
     * ticket_class 电影票的类别，如2D、3D。是
     * show_time 电影放映时间对应的时间戳。是
     * duration 放映时长，填写整数。是
     * screening_room 该场电影的影厅信息。是
     * seat_number 座位号。是
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     *
     * @return mixed
     */
    public function movieticketUpdateuser($code, $card_id, $ticket_class, $show_time, $duration, $screening_room, array $seat_number)
    {
        $params = array();
        $params['code'] = $code;
        $params['ticket_class'] = $ticket_class;
        $params['show_time'] = $show_time;
        $params['duration'] = $duration;
        $params['screening_room'] = $screening_room;
        $params['seat_number'] = $seat_number;
        
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'movieticket/updateuser?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----飞机票----在线选座
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/boardingpass/checkin?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code": "198374613512",
     * "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI",
     * "passenger_name": "乘客姓名",
     * "class": "舱等",
     * "seat": "座位号",
     * "boarding_time": 1409137710，
     * "gate": "登机口",
     * "etkt_bnr": "电子客票号",
     * "qrcode_data": "二维码数据",
     * "is_cancel ": false
     * }
     * 字段说明是否必填
     * code 飞机票的序列号是
     * card_id 需办理值机的机票card_id。自定义code 的飞机票为必填。否
     * passenger_name 乘客姓名，上限为15 个汉字。是
     * class 舱等，如头等舱等，上限为5 个汉字。是
     * seat 乘客座位号。否
     * gate 登机口。如发生登机口变更，建议商家实时调用该接口变更。否
     * boarding_time 登机时间，只显示“时分”不显示日期，按时间戳格式填写。如发生登机时间变更，建议商家实时调用该接口变更。否
     * etkt_bnr 电子客票号，上限为14 个数字。是
     * qrcode_data二维码数据。乘客用于值机的二维码字符串，微信会通过此数据为用户生成值机用的二维码。否
     * is_cancel是否取消值机。填写true 或false。true 代表取消，如填写true 上述字段（如calss 等）均不做判断，机票返回未值机状态，乘客可重新值机。默认填写false 否
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function boardingpassCheckin($code, $card_id, $passenger_name, $class, $etkt_bnr, $seat = "", $qrcode_data = "", $is_cancel = false)
    {
        $params = array();
        $params['code'] = $code;
        $params['passenger_name'] = $passenger_name;
        $params['class'] = $class;
        $params['seat'] = $seat;
        $params['etkt_bnr'] = $etkt_bnr;
        $params['qrcode_data'] = $qrcode_data;
        $params['is_cancel'] = $is_cancel;
        
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'boardingpass/checkin?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 会议门票
     * 更新会议门票接口
     *  接口说明
     * 支持调用“更新会议门票”接口 update 入场时间、区域、座位等信息。
     *  接口调用请求说明
     * 协议 https
     * http 请求方式 POST
     * 微信卡券接口文档
     * 请求 Url https://api.weixin.qq.com/card/meetingticket/updateuser?access_token=TOKEN
     * POST 数据格式 json
     *  请求参数说明
     * 参数 是否必须 说明
     * access_token 是 调用接口凭证
     * POST 数据 是 Json 数据
     *  POST 数据
     * 数据示例：
     * {
     * "code": "717523732898",
     * "card_id": "pXch-jvdwkJjY7evUFV-sGsoMl7A",
     * "zone" : "C 区",
     * "entrance" : "东北门",
     * "seat_number" : "2 排 15 号"
     * }
     * 字段 说明 是否必填
     * code 用户的门票唯一序列号 是
     * card_id
     * 要 更 新 门 票 序 列 号 所 述 的 card_id ， 生 成 券 时
     * use_custom_code 填写 true 时必填。
     * 否
     * zone 区域 否
     * entrance 入口 否
     * seat_number 座位号。 否
     * begin_time 开场时间 是
     * end_time 结束时间 是
     *  返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常
     * 微信卡券接口文档
     * errmsg 错误信息
     *
     * @return mixed
     */
    public function meetingticketUpdateuser($code, $card_id, $begin_time, $end_time, $zone, $entrance, $seat_number)
    {
        $params = array();
        $params['code'] = $code;
        $params['begin_time'] = $begin_time;
        $params['end_time'] = $end_time;
        $params['zone'] = $zone;
        $params['entrance'] = $entrance;
        $params['seat_number'] = $seat_number;
        
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'meetingticket/updateuser?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 特殊卡票 ----红包----更新红包金额
     * 接口说明
     * 支持领取红包后通过调用“更新红包”接口update 红包余额。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/luckymoney/updateuserbalance?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "code": "12312313",
     * "card_id": "xxxx_card_id",
     * "balance": 1231231
     * }
     * 字段说明
     * code 红包的序列号
     * card_id 自定义code 的卡券必填。非自定义code 可不填。
     * balance 红包余额
     * 返回数据说明
     * 数据示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     *
     *
     * @return mixed
     */
    public function luckymoneyUpdateuserbalance($code, $balance, $card_id = "")
    {
        $params = array();
        $params['code'] = $code;
        $params['balance'] = $balance;
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'luckymoney/updateuserbalance?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 导入code 接口
     * 接口说明
     * 开发者需调用该接口将自定义code 导入微信卡券后台，由微信侧代理存储并下发code，本接口仅用于支持微信摇卡券活动。
     * 注：
     * 1）单次调用接口传入code 的数量上限为100 个。
     * 2）每一个 code 均不能为空串，且不能重复填入相同code，否则会导入失败。
     * 3）导入失败支持重复导入，提示成功为止。
     *
     * 接口调用请求说明
     * 协议 https
     * http 请求方式 POST
     * 请求Url http://api.weixin.qq.com/card/code/deposit?access_token=ACCESS_TOKEN
     * POST 数据格式 json
     * 请求参数说明
     * 参数 说明 是否必填
     * access_token 调用接口凭证 是
     * POST 数据
     * 数据示例
     * 字段 说明 是否必填
     * card_id 参与活动的卡券ID 是
     * code 需导入微信卡券后台的自定义code，上限为100 个 是
     * {
     * "card_id": "pDF3iY0_dVjb_Pua96MMewA96qvA",
     * "code": [
     * "11111",
     * "22222",
     * "33333",
     * "44444",
     * "55555"
     * ]
     * }
     * 返回数据说明
     * 返回示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * }
     * 字段 说明
     * errcode 错误码，0 为正常；40109：code 数量超过100 个
     * errmsg 错误信息
     */
    public function codeDeposit($card_id, array $codes)
    {
        $params = array();
        $params['card_id'] = $card_id;
        $params['code'] = $codes;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post('http://api.weixin.qq.com/card/code/deposit?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 核查code接口
     * 接口说明
     * 支持开发者调用该接口查询code导入情况。
     * 接口调用请求说明
     * 协议 https
     * http请求方式 POST
     * 请求Url http://api.weixin.qq.com/card/code/checkcode?access_token=ACCESS_TOKEN
     * POST数据格式 json
     * 请求参数说明
     * 参数 说明 是否必填
     * access_token 调用接口凭证 是
     * POST数据
     * 数据示例
     * {
     * "card_id":"pDF3iY0_dVjb_Pua96MMewA96qvA",
     * "code":[
     * "11111",
     * "22222",
     * "33333",
     * "44444",
     * "55555"
     * ]
     * }
     * 字段 说明 是否必填
     * card_id 参与活动的卡券ID 是
     * code 需导入微信卡券后台的自定义code，上限为100个 是
     * 返回数据说明
     * 返回示例：
     * {
     * "errcode":0,
     * "errmsg":"ok"
     * "exist_code":["11111","22222","33333"],
     * "not_exist_code":["44444","55555"]
     * }
     * 字段 说明
     * errcode 错误码，0为正常。
     * errmsg 错误信息。
     * exist_code 已经成功存入的code。
     * not_exist_code 没有存入的code。
     */
    public function codeCheck($card_id, array $codes)
    {
        $params = array();
        $params['card_id'] = $card_id;
        $params['code'] = $codes;
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post('http://api.weixin.qq.com/card/code/checkcode?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
    
    // ------------以下接口在V2.0废弃了----------------
    // ------------以下门店接口废弃 改用POI门店接口----------------
    
    /**
     * 创建卡券 ----批量导入门店信息
     * 支持商户调用该接口批量导入/新建门店信息，获取门店ID。
     * 注：通过该接口导入的门店信息将进入门店审核流程，审核期间可正常使用。
     * 若导入的门店信息未通过审核，则会被剔除出门店列表。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/location/batchadd?access_token=TOKEN
     * POST 数据格式json
     *
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例
     * {"location_list":[
     * {
     * "business_name":"TIT 创意园1 号店",
     * "province":"广东省",
     * "city":"广州市",
     * "district":"海珠区",
     * "address":"中国广东省广州市海珠区艺苑路11 号",
     * "telephone":"020-89772059",
     * "category":"房产小区",
     * "longitude":"115.32375",
     * "latitude":"25.097486"
     * },
     * {
     * "business_name":"TIT 创意园2 号店",
     * "province":"广东省",
     * "city":"广州市",
     * "district":"海珠区",
     * "address":"中国广东省广州市海珠区艺苑路12 号",
     * "telephone":"020-89772059",
     * "category":"房产小区",
     * "longitude":"113.32375",
     * "latitude":"23.097486"
     * }]}
     * 字段说明是否必填
     * business_name 门店名称是
     * province 门店所在的省是
     * city 门店所在的市是
     * district 门店所在的区是
     * address 门店所在的详细街道地址是
     * telephone 门店的电话是
     * longitude 门店所在地理位置的经度是
     * latitude 门店所在地理位置的纬度是
     * 返回数据说明
     * 导入成功示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "location_id_list":[271262077,271262079]
     * }
     * 插入失败示例：
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "location_id_list":[271262077,-1]
     * }
     * 字段说明
     * errcode 错误码，0 为正常。
     * errmsg 错误信息。
     * location_id 门店ID。插入失败的门店返回数值“-1”，请
     * 核查必填字段后单独调用接口导入。
     *
     * @return mixed
     */
    public function locationBatchadd(array $location_list)
    {
        $params = array(
            "location_list" => $location_list
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'location/batchadd?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 创建卡券 ----拉取门店列表
     * 获取在公众平台上申请创建的门店列表，用于创建卡券。
     * 注：请按表格要求填写“门店信息导入表”。
     * 接口调用请求说明
     * 协议https
     * http 请求方式POST
     * 请求Url https://api.weixin.qq.com/card/location/batchget?access_token=TOKEN
     * POST 数据格式json
     * 请求参数说明
     * 参数是否必须说明
     * access_token 是调用接口凭证
     * POST 数据是Json 数据
     * POST 数据
     * 数据示例：
     * {
     * "offset": 0,
     * "count": 2
     * }
     * 字段说明
     * offset 偏移量，0 开始
     * count 拉取数量
     * 注：“offset”，“count”都为0 时默认拉取全部门店。
     * 返回数据说明
     * 数据示例：
     * { "errcode": 0,
     * "errmsg": "ok",
     * "location_list": [
     * {
     * "location_id": 493,
     * "name": "steventao home",
     * "phone": "020-12345678",
     * "address": "广东省广州市番禺区广东省广州市番禺区南浦大道",
     * "longitude": 113.280212402,
     * "latitude": 23.0350666046
     * },
     * {
     * "location_id": 468,
     * "name": "TIT 创意园B4",
     * "phone": "020-12345678",
     * "address": "广东省广州市海珠区",
     * "longitude": 113.325248718,
     * "latitude": 23.1008300781
     * }
     * ],
     * "count": 2
     * }
     * 字段说明
     * errcode 错误码，0 为正常
     * errmsg 错误信息
     * location_list
     * location_id 门店ID
     * name 门店名称
     * phone 联系电话
     * address 详细地址
     * longitude 经度
     * latitude 纬度
     * count 拉取门店数量
     *
     * @return mixed
     */
    public function locationBatchget($offset = 0, $count = 0)
    {
        $params = array();
        $params['offset'] = $offset;
        $params['count'] = $count;
        
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'location/batchget?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
