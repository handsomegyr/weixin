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
     * code_type code 码展示类型。是
     * "CODE_TYPE_TEXT"，文本"CODE_TYPE_BARCODE"，一维码"CODE_TYPE_QRCODE"，二维码
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
     * 使用提醒，字数上限为9 个汉字。（一句话描述，展示在首页，示例：请出示二维码核销卡券）
     * 是
     */
    public $notice = NULL;

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
     * description
     * 使用说明。长文本描述，可以分行，上限为1000 个汉字。
     * 是
     */
    public $description = NULL;

    /**
     * use_limit
     * 每人使用次数限制。
     * 否
     */
    public $use_limit = NULL;

    /**
     * get_limit
     * 每人最大领取次数，不填写默认等于quantity。否
     */
    public $get_limit = NULL;

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
     * location_id_list
     * 门店位置ID。商户需在mp 平台上录入门店信息或调用批量导入门店信息接口获取门店位置ID。
     * 否
     */
    public $location_id_list = NULL;

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
     * custom_url
     * 商户自定义url 地址，支持卡券页内跳转,跳转页面内容需与自定义cell 名称保持一致。
     * 否
     */
    public $custom_url = NULL;

    /**
     * card_id
     * 否
     */
    public $card_id = NULL;

    /**
     * status
     * 1：待审核，2：审核失败，3：通过审核， 4：已删除（飞机票的status 字段为1：正常2：已删除）
     */
    public $status = NULL;
    
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

    public function set_service_phone($service_phone)
    {
        $this->service_phone = $service_phone;
    }

    public function set_source($source)
    {
        $this->source = $source;
    }

    public function set_use_limit($use_limit)
    {
        if (! is_int($use_limit))
            exit("use_limit must be integer");
        $this->use_limit = $use_limit;
    }

    public function set_get_limit($get_limit)
    {
        if (! is_int($get_limit))
            exit("get_limit must be integer");
        $this->get_limit = $get_limit;
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

    public function set_location_id_list(array $location_id_list)
    {
        $this->location_id_list = $location_id_list;
    }

    public function set_url_name_type($url_name_type)
    {
        $this->url_name_type = $url_name_type;
    }

    public function set_custom_url($custom_url)
    {
        $this->custom_url = $custom_url;
    }

    public function set_card_id($card_id)
    {
        $this->card_id = $card_id;
    }
    
    public function set_status($status)
    {
        $this->status = $status;
    }
    
    public function getParams()
    {
        $params = array();
        if ($this->logo_url != NULL) {
            $params['logo_url'] = $this->logo_url;
        }
        if ($this->code_type != NULL) {
            $params['code_type'] = $this->code_type;
        }
        if ($this->brand_name != NULL) {
            $params['brand_name'] = $this->brand_name;
        }
        if ($this->title != NULL) {
            $params['title'] = $this->title;
        }
        if ($this->sub_title != NULL) {
            $params['sub_title'] = $this->sub_title;
        }
        if ($this->color != NULL) {
            $params['color'] = $this->color;
        }
        if ($this->notice != NULL) {
            $params['notice'] = $this->notice;
        }
        if ($this->service_phone != NULL) {
            $params['service_phone'] = $this->service_phone;
        }
        if ($this->source != NULL) {
            $params['source'] = $this->source;
        }
        if ($this->description != NULL) {
            $params['description'] = $this->description;
        }
        if ($this->use_limit != NULL) {
            $params['use_limit'] = $this->use_limit;
        }
        if ($this->get_limit != NULL) {
            $params['get_limit'] = $this->get_limit;
        }
        if ($this->use_custom_code != NULL) {
            $params['use_custom_code'] = $this->use_custom_code;
        }
        if ($this->bind_openid != NULL) {
            $params['bind_openid'] = $this->bind_openid;
        }
        if ($this->can_share != NULL) {
            $params['can_share'] = $this->can_share;
        }
        if ($this->can_give_friend != NULL) {
            $params['can_give_friend'] = $this->can_give_friend;
        }
        if ($this->location_id_list != NULL) {
            $params['location_id_list'] = $this->location_id_list;
        }
        if ($this->date_info != NULL) {
            $params['date_info'] = $this->date_info->getParams();
        }
        if ($this->sku != NULL) {
            $params['sku'] = $this->sku->getParams();
        }
        if ($this->url_name_type != NULL) {
            $params['url_name_type'] = $this->url_name_type;
        }
        if ($this->custom_url != NULL) {
            $params['custom_url'] = $this->custom_url;
        }
        return $params;
    }
}
