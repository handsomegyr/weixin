<?php
namespace Weixin\Model;

/**
 * 通用券
 */
class GeneralCoupon extends CardBase
{

    /**
     * default_detail
     * 描述文本
     * 是
     */
    public $default_detail = NULL;

    public function __construct(BaseInfo $base_info, $default_detail)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["GENERAL_COUPON"];
        $this->create_key = 'general_coupon';
        $this->default_detail = $default_detail;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->isNotNull($this->default_detail)) {
            $params['default_detail'] = $this->default_detail;
        }
        return $params;
    }
}
