<?php
namespace Weixin\Model;

/**
 * 卡的基类
 */
abstract class CardBase
{

    public static $CARD_TYPE = Array(
        "GENERAL_COUPON" => "GENERAL_COUPON",
        "GROUPON" => "GROUPON",
        "DISCOUNT" => "DISCOUNT",
        "GIFT" => "GIFT",
        "CASH" => "CASH",
        "MEMBER_CARD" => "MEMBER_CARD",
        "SCENIC_TICKET" => "SCENIC_TICKET",
        "MOVIE_TICKET" => "MOVIE_TICKET",
        "BOARDING_PASS" => "BOARDING_PASS",
        "LUCKY_MONEY" => "LUCKY_MONEY"
    );

    /**
     *
     * @var BaseInfo
     */
    public $base_info = NULL;

    public $card_type = NULL;

    public $create_key = NULL;

    public $card_id = NULL;

    public function __construct(BaseInfo $base_info)
    {
        $this->base_info = $base_info;
        $this->card_id = $base_info->card_id;
    }

    protected function getParams()
    {
        $params = array();
        return $params;
    }

    public function getParams4Create()
    {
        $params = array();
        $params['card_type'] = $this->card_type;
        $params[$this->create_key]['base_info'] = $this->base_info->getParams();
        
        $selfParams = $this->getParams();
        foreach ($selfParams as $key => $value) {
            $params[$this->create_key][$key] = $value;
        }
        return $params;
    }

    public function getParams4Update()
    {
        $params = array();
        $params['card_id'] = $this->card_id;
        $params[$this->create_key]['base_info'] = $this->base_info->getParams();
        $selfParams = $this->getParams();
        foreach ($selfParams as $key => $value) {
            $params[$this->create_key][$key] = $value;
        }
        return $params;
    }
}
