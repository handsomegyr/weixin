<?php
namespace Weixin\Model;

/**
 * 门票
 */
class ScenicTicket extends CardBase
{

    /**
     * ticket_class
     * 票类型，例如平日全票，套票等。
     * 否
     */
    public $ticket_class = NULL;

    /**
     * guide_url
     * 导览图url
     * 否
     */
    public $guide_url = NULL;

    public function __construct(BaseInfo $base_info)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["SCENIC_TICKET"];
        $this->create_key = 'scenic_ticket';
    }

    public function set_ticket_class($ticket_class)
    {
        $this->ticket_class = $ticket_class;
    }

    public function set_guide_url($guide_url)
    {
        $this->guide_url = $guide_url;
    }

    protected function getParams()
    {
        $params = array();
        
        if ($this->isNotNull($this->ticket_class)) {
            $params['ticket_class'] = $this->ticket_class;
        }
        if ($this->isNotNull($this->guide_url)) {
            $params['guide_url'] = $this->guide_url;
        }
        return $params;
    }
}
