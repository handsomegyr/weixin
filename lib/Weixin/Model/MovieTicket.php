<?php
namespace Weixin\Model;

/**
 * 电影票
 */
class MovieTicket extends CardBase
{

    /**
     * detail 电影票详情
     * 否
     */
    public $detail = NULL;

    public function __construct(BaseInfo $base_info)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["MOVIE_TICKET"];
        $this->create_key = 'movie_ticket';
    }

    public function set_detail($detail)
    {
        $this->detail = $detail;
    }

    protected function getParams()
    {
        $params = array();
        
        if ($this->detail != NULL) {
            $params['detail'] = $this->detail;
        }
        return $params;
    }
}
