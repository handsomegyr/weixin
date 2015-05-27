<?php
namespace Weixin\Model;

/**
 * 折扣券
 */
class Discount extends CardBase
{

    /**
     * discount
     * 折扣券专用，表示打折额度（百分比）。填30 就是七折。
     * 是
     */
    public $discount = NULL;

    public function __construct(BaseInfo $base_info, $discount)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["DISCOUNT"];
        $this->create_key = 'discount';
        $this->discount = $discount;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->discount != NULL) {
            $params['discount'] = $this->discount;
        }
        return $params;
    }
}
