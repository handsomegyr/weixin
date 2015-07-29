<?php
namespace Weixin\Model;

/**
 * 代金券
 */
class Cash extends CardBase
{

    /**
     * least_cost
     * 代金券专用，表示起用金额（单位为分）
     * 否
     */
    public $least_cost = NULL;

    /**
     * reduce_cost
     * 代金券专用，表示减免金额（单位为分）
     * 是
     */
    public $reduce_cost = NULL;

    public function __construct(BaseInfo $base_info, $reduce_cost)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["CASH"];
        $this->create_key = 'cash';
        $this->reduce_cost = $reduce_cost;
    }

    public function set_least_cost($least_cost)
    {
        $this->least_cost = $least_cost;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->isNotNull($this->least_cost)) {
            $params['least_cost'] = $this->least_cost;
        }
        if ($this->isNotNull($this->reduce_cost)) {
            $params['reduce_cost'] = $this->reduce_cost;
        }
        return $params;
    }
}
