<?php
namespace Weixin\Model;

/**
 * 礼品券
 */
class Gift extends CardBase
{

    /**
     * gift
     * 礼品券专用，表示礼品名字。
     * 是
     */
    public $gift = NULL;

    public function __construct(BaseInfo $base_info, $gift)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["GIFT"];
        $this->create_key = 'gift';
        $this->gift = $gift;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->isNotNull($this->gift)) {
            $params['gift'] = $this->gift;
        }
        return $params;
    }
}
