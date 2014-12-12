<?php
namespace Weixin\Model;

/**
 * 红包
 */
class LuckyMoney extends CardBase
{

    public function __construct(BaseInfo $base_info)
    {
        parent::__construct($base_info);
        $this->create_key = 'lucky_money';
        $this->card_type = self::$CARD_TYPE["LUCKY_MONEY；"];
    }
}
