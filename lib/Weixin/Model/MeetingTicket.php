<?php
namespace Weixin\Model;

/**
 * 会议门票
 */
class MeetingTicket extends CardBase
{

    /**
     * meeting_detail
     * 会议详情。
     * 是
     */
    public $meeting_detail = NULL;

    /**
     * map_url
     * 会场导览图
     * 否
     */
    public $map_url = NULL;

    public function __construct(BaseInfo $base_info, $meeting_detail)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["MEETING_TICKET"];
        $this->create_key = 'meeting_ticket';
        $this->meeting_detail = $meeting_detail;
    }

    public function set_map_url($map_url)
    {
        $this->map_url = $map_url;
    }

    protected function getParams()
    {
        $params = array();
        
        if ($this->isNotNull($this->map_url)) {
            $params['map_url'] = $this->map_url;
        }
        return $params;
    }
}
