<?php
namespace Weixin\Model;

/**
 * 会员卡
 */
class MemberCard extends CardBase
{

    /**
     * supply_bonus
     * 是否支持积分，填写true 或false，如填写true，积分相关字段均为必填。填写false，积分字段无需填写。储值字段处理方式相同。
     * 是
     */
    public $supply_bonus = NULL;

    /**
     * supply_balance
     * 是否支持储值，填写true 或false。（该权限申请及说明详见Q&A)
     * 是
     */
    public $supply_balance = NULL;

    /**
     * bonus_cleared
     * 积分清零规则
     * 否
     */
    public $bonus_cleared = NULL;

    /**
     * bonus_rules
     * 积分规则
     * 否
     */
    public $bonus_rules = NULL;

    /**
     * balance_rules
     * 储值说明
     * 否
     */
    public $balance_rules = NULL;

    /**
     * prerogative
     * 特权说明
     * 是
     */
    public $prerogative = NULL;

    /**
     * bind_old_card_url
     * 绑定旧卡的url，与“activate_url”字段二选一必填。
     * 否
     */
    public $bind_old_card_url = NULL;

    /**
     * activate_url
     * 激活会员卡的url，与“bind_old_card_url”字段二选一必填。
     * 否
     */
    public $activate_url = NULL;

    public function __construct(BaseInfo $base_info, $supply_bonus, $supply_balance, $prerogative)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["MEMBER_CARD"];
        $this->create_key = 'member_card';
        $this->supply_bonus = $supply_bonus;
        $this->supply_balance = $supply_balance;
        $this->prerogative = $prerogative;
    }

    public function set_bonus_cleared($bonus_cleared)
    {
        $this->bonus_cleared = $bonus_cleared;
    }

    public function set_bonus_rules($bonus_rules)
    {
        $this->bonus_rules = $bonus_rules;
    }

    public function set_balance_rules($balance_rules)
    {
        $this->balance_rules = $balance_rules;
    }

    public function set_bind_old_card_url($bind_old_card_url)
    {
        $this->bind_old_card_url = $bind_old_card_url;
    }

    public function set_activate_url($activate_url)
    {
        $this->activate_url = $activate_url;
    }

    protected function getParams()
    {
        $params = array();
        if ($this->supply_bonus != NULL) {
            $params['supply_bonus'] = $this->supply_bonus;
        }
        if ($this->supply_balance != NULL) {
            $params['supply_balance'] = $this->supply_balance;
        }
        if ($this->bonus_cleared != NULL) {
            $params['bonus_cleared'] = $this->bonus_cleared;
        }
        if ($this->bonus_rules != NULL) {
            $params['bonus_rules'] = $this->bonus_rules;
        }
        if ($this->balance_rules != NULL) {
            $params['balance_rules'] = $this->balance_rules;
        }
        if ($this->prerogative != NULL) {
            $params['prerogative'] = $this->prerogative;
        }
        if ($this->bind_old_card_url != NULL) {
            $params['bind_old_card_url'] = $this->bind_old_card_url;
        }
        if ($this->activate_url != NULL) {
            $params['activate_url'] = $this->activate_url;
        }
        return $params;
    }
}
