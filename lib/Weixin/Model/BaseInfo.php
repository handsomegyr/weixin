<?php
namespace Weixin\Model;

/**
 * 基本的卡券数据
 */
class BaseInfo
{

    /**
     * logo_url
     * 卡券的商户logo，尺寸为300*300。
     * 是
     */
    public $logo_url = NULL;

    /**
     * code_type
     * code 码展示类型。
     * 是
     * "CODE_TYPE_TEXT"，文本
     * "CODE_TYPE_BARCODE"，一维码
     * "CODE_TYPE_QRCODE"，二维码；
     * “CODE_TYPE_ONLY_QRCODE”,二维码无 code 显示；
     * “CODE_TYPE_ONLY_BARCODE”,一维码无 code 显示；
     * 是
     */
    public $code_type = NULL;

    /**
     * brand_name
     * 商户名字,字数上限为12 个汉字。（填写直接提供服务的商户名， 第三方商户名填写在source 字段）
     * 是
     */
    public $brand_name = NULL;

    /**
     * title
     * 券名，字数上限为9 个汉字。(建议涵盖卡券属性、服务及金额)
     * 是
     */
    public $title = NULL;

    /**
     * sub_title
     * 券名的副标题，字数上限为18个汉字。
     * 否
     */
    public $sub_title = NULL;

    /**
     * color
     * 券颜色。按色彩规范标注填写Color010-Color100
     * 是
     */
    public $color = NULL;

    /**
     * notice
     * 使用提醒，字数上限为12 个汉字。（一句话描述，展示在首页，示例：请出示二维码核销卡券）
     * 是
     */
    public $notice = NULL;

    /**
     * description
     * 使用说明。长文本描述，可以分行，上限为1000 个汉字。
     * 是
     */
    public $description = NULL;

    /**
     * date_info
     * 使用日期，有效期的信息
     * 是
     *
     * @var DateInfo
     */
    public $date_info = NULL;

    /**
     * sku
     * 商品信息。
     * 是
     *
     * @var Sku
     */
    public $sku = NULL;

    /**
     * location_id_list
     * 门店位置ID。商户需在mp 平台上录入门店信息或调用批量导入门店信息接口获取门店位置ID。
     * 否
     */
    public $location_id_list = NULL;

    /**
     * use_custom_code
     * 是否自定义code 码。填写true或false，不填代表默认为false。（该权限申请及说明详见Q&A)
     * 否
     */
    public $use_custom_code = false;

    /**
     * bind_openid
     * 是否指定用户领取，填写true或false。不填代表默认为否。
     * 否
     */
    public $bind_openid = false;

    /**
     * can_share
     * 领取卡券原生页面是否可分享，填写true 或false，true 代表可分享。默认可分享。
     * 否
     */
    public $can_share = true;

    /**
     * can_give_friend
     * 卡券是否可转赠，填写true 或false,true 代表可转赠。默认可转赠。
     * 否
     */
    public $can_give_friend = true;

    /**
     * get_limit
     * 每人最大领取次数，不填写默认等于quantity。否
     */
    public $get_limit = NULL;

    /**
     * service_phone
     * 客服电话。
     * 否
     */
    public $service_phone = NULL;

    /**
     * source
     * 第三方来源名，例如同程旅游、格瓦拉。
     * 否
     */
    public $source = NULL;

    /**
     * custom_url_name
     * 商户自定义入口名称，与custom_url 字段共同使用，长度限制在 5 个汉字内。
     * 否
     */
    public $custom_url_name = NULL;

    /**
     * custom_url
     * 商户自定义url 地址，支持卡券页内跳转,跳转页面内容需与自定义cell 名称保持一致。
     * 否
     */
    public $custom_url = NULL;

    /**
     * custom_url_sub_title
     * 显示在入口右侧的 tips，长度限制在 6 个汉字内。
     * 否
     */
    public $custom_url_sub_title = NULL;

    /**
     * promotion_url_name
     * 营销场景的自定义入口。
     * 否
     */
    public $promotion_url_name = NULL;

    /**
     * promotion_url
     * 入口跳转外链的地址链接。
     * 否
     */
    public $promotion_url = NULL;

    /**
     * promotion_url_sub_title
     * 显示在入口右侧的 tips，长度限制在 6 个汉字内。
     * 否
     */
    public $promotion_url_sub_title = NULL;

    /**
     * status
     * 1：待审核，2：审核失败，3：通过审核， 4：已删除（飞机票的status 字段为1：正常2：已删除）
     *
     * //v2.0改成以下值
     * “CARD_STATUS_NOT_VERIFY”,
     * 待审核
     * “CARD_STATUS_VERIFY_FALL”,
     * 审核失败
     * “CARD_STATUS_VERIFY_OK”，
     * 通过审核
     * “CARD_STATUS_USER_DELETE” ，
     * 卡券被用户删除
     * “CARD_STATUS_USER_DISPATCH”，在公众平台投放过的卡券
     */
    public $status = NULL;

    /**
     * card_id
     * 否
     */
    public $card_id = NULL;

    /**
     * 以下字段都是用以微信摇一摇的时候设置
     */
    /**
     * GET_CUSTOM_CODE_MODE_DEPOSIT,字符串格式，该字段支持开发者导入code至微信后台。
     * get_custom_code_mode
     * 否
     */
    public $get_custom_code_mode = NULL;

    /**
     * can_shake
     * 填写 true,为参加摇礼券活动的标志位。
     * 否
     */
    public $can_shake = NULL;

    /**
     * 新年祝语标题，限制10个汉字以内
     * shake_slogan_title
     * 否
     */
    public $shake_slogan_title = NULL;

    /**
     * 新年祝语正文，限制16个汉字以内
     * shake_slogan_sub_title
     * 否
     */
    public $shake_slogan_sub_title = NULL;

    /**
     * use_limit
     * 每人使用次数限制。
     * 否
     */
    public $use_limit = NULL;
    
    // -----以下字段在v2.0废弃了--------------
    /**
     * url_name_type
     * 商户自定义cell 名称
     * 否
     * "URL_NAME_TYPE_TAKE_AWAY"，外卖
     * "URL_NAME_TYPE_RESERVATION"，在线预订
     * "URL_NAME_TYPE_USE_IMMEDIATELY"，立即使用
     * "URL_NAME_TYPE_APPOINTMENT”,在线预约
     * URL_NAME_TYPE_EXCHANGE,在线兑换
     * URL_NAME_TYPE_MALL,在线商城
     * "URL_NAME_TYPE_VEHICLE_INFORMATION，车辆信息（该权限申请及说明详见Q&A)
     * 否
     */
    public $url_name_type = NULL;

    /**
     * promotion_url_name_type
     * 特殊权限自定义 cell，权限需单独开通。
     * 否
     */
    public $promotion_url_name_type = NULL;

    public function __construct($logo_url, $brand_name, $code_type, $title, $color, $notice, $description, DateInfo $date_info, Sku $sku)
    {
        if (! $date_info instanceof DateInfo)
            exit("date_info Error");
        if (! $sku instanceof Sku)
            exit("sku Error");
        
        $this->logo_url = $logo_url;
        $this->code_type = $code_type;
        $this->brand_name = $brand_name;
        $this->title = $title;
        $this->color = $color;
        $this->notice = $notice;
        $this->description = $description;
        $this->date_info = $date_info;
        $this->sku = $sku;
    }

    public function set_sub_title($sub_title)
    {
        $this->sub_title = $sub_title;
    }

    public function set_location_id_list($location_id_list)
    {
        $this->location_id_list = $location_id_list;
    }

    public function set_use_custom_code($use_custom_code)
    {
        $this->use_custom_code = $use_custom_code;
    }

    public function set_bind_openid($bind_openid)
    {
        $this->bind_openid = $bind_openid;
    }

    public function set_can_share($can_share)
    {
        $this->can_share = $can_share;
    }

    public function set_can_give_friend($can_give_friend)
    {
        $this->can_give_friend = $can_give_friend;
    }

    public function set_get_limit($get_limit)
    {
        if (! is_int($get_limit))
            exit("get_limit must be integer");
        $this->get_limit = $get_limit;
    }

    public function set_service_phone($service_phone)
    {
        $this->service_phone = $service_phone;
    }

    public function set_source($source)
    {
        $this->source = $source;
    }

    public function set_custom_url_name($custom_url_name)
    {
        $this->custom_url_name = $custom_url_name;
    }

    public function set_custom_url($custom_url)
    {
        $this->custom_url = $custom_url;
    }

    public function set_custom_url_sub_title($custom_url_sub_title)
    {
        $this->custom_url_sub_title = $custom_url_sub_title;
    }

    public function set_promotion_url_name($promotion_url_name)
    {
        $this->promotion_url_name = $promotion_url_name;
    }

    public function set_promotion_url($promotion_url)
    {
        $this->promotion_url = $promotion_url;
    }

    public function set_promotion_url_sub_title($promotion_url_sub_title)
    {
        $this->promotion_url_sub_title = $promotion_url_sub_title;
    }

    public function set_card_id($card_id)
    {
        $this->card_id = $card_id;
    }

    public function set_status($status)
    {
        $this->status = $status;
    }

    /**
     * 以下字段都是用以微信摇一摇的时候设置
     */
    public function set_get_custom_code_mode($get_custom_code_mode)
    {
        $this->get_custom_code_mode = $get_custom_code_mode;
    }

    public function set_can_shake($can_shake)
    {
        $this->can_shake = $can_shake;
    }

    public function set_shake_slogan_title($shake_slogan_title)
    {
        $this->shake_slogan_title = $shake_slogan_title;
    }

    public function set_shake_slogan_sub_title($shake_slogan_sub_title)
    {
        $this->shake_slogan_sub_title = $shake_slogan_sub_title;
    }

    public function set_use_limit($use_limit)
    {
        if (! is_int($use_limit))
            exit("use_limit must be integer");
        $this->use_limit = $use_limit;
    }
    // -----以下字段在v2.0废弃了--------------
    public function set_url_name_type($url_name_type)
    {
        $this->url_name_type = $url_name_type;
    }

    public function set_promotion_url_name_type($promotion_url_name_type)
    {
        $this->promotion_url_name_type = $promotion_url_name_type;
    }

    public function getParams()
    {
        $params = array();
        if ($this->isNotNull($this->logo_url)) {
            $params['logo_url'] = $this->logo_url;
        }
        if ($this->isNotNull($this->code_type)) {
            $params['code_type'] = $this->code_type;
        }
        if ($this->isNotNull($this->brand_name)) {
            $params['brand_name'] = $this->brand_name;
        }
        if ($this->isNotNull($this->title)) {
            $params['title'] = $this->title;
        }
        if ($this->isNotNull($this->sub_title)) {
            $params['sub_title'] = $this->sub_title;
        }
        if ($this->isNotNull($this->color)) {
            $params['color'] = $this->color;
        }
        if ($this->isNotNull($this->notice)) {
            $params['notice'] = $this->notice;
        }
        if ($this->isNotNull($this->description)) {
            $params['description'] = $this->description;
        }
        if ($this->isNotNull($this->date_info)) {
            $params['date_info'] = $this->date_info->getParams();
        }
        if ($this->isNotNull($this->sku)) {
            $params['sku'] = $this->sku->getParams();
        }
        if ($this->isNotNull($this->location_id_list)) {
            $params['location_id_list'] = $this->location_id_list;
        }
        if ($this->isNotNull($this->use_custom_code)) {
            $params['use_custom_code'] = $this->use_custom_code;
        }
        if ($this->isNotNull($this->bind_openid)) {
            $params['bind_openid'] = $this->bind_openid;
        }
        if ($this->isNotNull($this->can_share)) {
            $params['can_share'] = $this->can_share;
        }
        if ($this->isNotNull($this->can_give_friend)) {
            $params['can_give_friend'] = $this->can_give_friend;
        }
        if ($this->isNotNull($this->get_limit)) {
            $params['get_limit'] = $this->get_limit;
        }
        if ($this->isNotNull($this->service_phone)) {
            $params['service_phone'] = $this->service_phone;
        }
        if ($this->isNotNull($this->source)) {
            $params['source'] = $this->source;
        }
        if ($this->isNotNull($this->custom_url_name)) {
            $params['custom_url_name'] = $this->custom_url_name;
        }
        if ($this->isNotNull($this->custom_url)) {
            $params['custom_url'] = $this->custom_url;
        }
        if ($this->isNotNull($this->custom_url_sub_title)) {
            $params['custom_url_sub_title'] = $this->custom_url_sub_title;
        }
        if ($this->isNotNull($this->promotion_url_name)) {
            $params['promotion_url_name'] = $this->promotion_url_name;
        }
        if ($this->isNotNull($this->promotion_url)) {
            $params['promotion_url'] = $this->promotion_url;
        }
        if ($this->isNotNull($this->promotion_url_sub_title)) {
            $params['promotion_url_sub_title'] = $this->promotion_url_sub_title;
        }
        
        /**
         * 以下字段都是用以微信摇一摇的时候设置
         */
        if ($this->isNotNull($this->get_custom_code_mode)) {
            $params['get_custom_code_mode'] = $this->get_custom_code_mode;
        }
        if ($this->isNotNull($this->can_shake)) {
            $params['can_shake'] = $this->can_shake;
        }
        if ($this->isNotNull($this->shake_slogan_title)) {
            $params['shake_slogan_title'] = $this->shake_slogan_title;
        }
        if ($this->isNotNull($this->shake_slogan_sub_title)) {
            $params['shake_slogan_sub_title'] = $this->shake_slogan_sub_title;
        }
        
        if ($this->isNotNull($this->use_limit)) {
            $params['use_limit'] = $this->use_limit;
        }
        // -----以下字段在v2.0废弃了--------------
        if ($this->isNotNull($this->url_name_type)) {
            $params['url_name_type'] = $this->url_name_type;
        }
        if ($this->isNotNull($this->promotion_url_name_type)) {
            $params['promotion_url_name_type'] = $this->promotion_url_name_type;
        }
        return $params;
    }

    protected function isNotNull($var)
    {
        return ! is_null($var);
    }
}
