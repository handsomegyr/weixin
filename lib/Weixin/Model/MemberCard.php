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
     * custom_field1
     * 自定义会员信息类目，会员卡激活后显示
     *
     * 否
     */
    public $custom_field1 = NULL;

    /**
     * custom_field2
     * 自定义会员信息类目，会员卡激活后显示
     * 否
     */
    public $custom_field2 = NULL;

    /**
     * custom_field3
     * 自定义会员信息类目，会员卡激活后显示
     * 否
     */
    public $custom_field3 = NULL;

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
     * need_push_on_view
     * true为用户点击进入会员卡时是否推送事件。详情见六、进入会员卡事件推送。
     * 否
     */
    public $need_push_on_view = NULL;

    /**
     * 会员卡类型专属营销入口，会员卡激活前后均显示。
     * 否
     */
    public $custom_cell1 = NULL;

    /**
     * 会员卡类型专属营销入口，会员卡激活前后均显示。
     * 否
     */
    public $custom_cell2 = NULL;

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

    public function set_custom_field1(CustomField $custom_field1)
    {
        $this->custom_field1 = $custom_field1;
    }

    public function set_custom_field2(CustomField $custom_field2)
    {
        $this->custom_field2 = $custom_field2;
    }

    public function set_custom_field3(CustomField $custom_field3)
    {
        $this->custom_field3 = $custom_field3;
    }

    public function set_need_push_on_view($need_push_on_view)
    {
        $this->need_push_on_view = $need_push_on_view;
    }

    public function set_custom_cell1(CustomCell $custom_cell1)
    {
        $this->custom_cell1 = $custom_cell1;
    }

    public function set_custom_cell2(CustomCell $custom_cell2)
    {
        $this->custom_cell2 = $custom_cell2;
    }

    protected function getParams()
    {
        $params = array();
        
        $params['supply_bonus'] = $this->supply_bonus;
        
        $params['supply_balance'] = $this->supply_balance;
        
        if ($this->custom_field1 != NULL) {
            $params['custom_field1'] = $this->custom_field1->getParams();
        }
        if ($this->custom_field2 != NULL) {
            $params['custom_field2'] = $this->custom_field2->getParams();
        }
        if ($this->custom_field3 != NULL) {
            $params['custom_field3'] = $this->custom_field3->getParams();
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
        if ($this->need_push_on_view != NULL) {
            $params['need_push_on_view'] = $this->need_push_on_view;
        }
        if ($this->custom_cell1 != NULL) {
            $params['custom_cell1'] = $this->custom_cell1->getParams();
        }
        if ($this->custom_cell2 != NULL) {
            $params['custom_cell2'] = $this->custom_cell2->getParams();
        }
        return $params;
    }
}
