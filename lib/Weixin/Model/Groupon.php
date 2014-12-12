<?php
namespace Weixin\Model;

/**
 * 团购券
 */
class Groupon extends CardBase
{

    /**
     * deal_detail
     * 团购券专用，团购详情.
     * 是
     */
    public $deal_detail = NULL;

    public function __construct(BaseInfo $base_info, $deal_detail)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["GROUPON"];
        $this->create_key = 'groupon';
        $this->deal_detail = $deal_detail;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->deal_detail != NULL) {
            $params['deal_detail'] = $this->deal_detail;
        }
        return $params;
    }
}
