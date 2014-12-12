<?php
namespace Weixin\Model;

/**
 * 飞机票
 */
class BoardingPass extends CardBase
{

    /**
     * from
     * 起点，上限为18 个汉字。
     * 是
     */
    public $from = NULL;

    /**
     * to
     * 终点，上限为18 个汉字。
     * 是
     */
    public $to = NULL;

    /**
     * flight
     * 航班
     * 是
     */
    public $flight = NULL;

    /**
     * departure_time
     * 起飞时间，上限为17 个汉字。
     * 否
     */
    public $departure_time = NULL;

    /**
     * landing_time
     * 降落时间，上限为17 个汉字。
     * 否
     */
    public $landing_time = NULL;

    /**
     * check_in_url
     * 在线值机的链接
     * 否
     */
    public $check_in_url = NULL;

    /**
     * air_model
     * 机型，上限为8 个汉字
     * 否
     */
    public $air_model = NULL;

    public function __construct(BaseInfo $base_info, $from, $to, $flight)
    {
        parent::__construct($base_info);
        $this->card_type = self::$CARD_TYPE["BOARDING_PASS"];
        $this->create_key = 'boarding_pass';
        $this->from = $from;
        $this->to = $to;
        $this->flight = $flight;
    }

    public function set_departure_time($departure_time)
    {
        $this->departure_time = $departure_time;
    }

    public function set_landing_time($landing_time)
    {
        $this->landing_time = $landing_time;
    }

    public function set_check_in_url($check_in_url)
    {
        $this->check_in_url = $check_in_url;
    }

    public function set_air_model($air_model)
    {
        $this->air_model = $air_model;
    }

    protected function getParams()
    {
        $params = array();
        
        if ($this->from != NULL) {
            $params['from'] = $this->from;
        }
        if ($this->to != NULL) {
            $params['to'] = $this->to;
        }
        if ($this->flight != NULL) {
            $params['flight'] = $this->flight;
        }
        if ($this->departure_time != NULL) {
            $params['departure_time'] = $this->departure_time;
        }
        if ($this->landing_time != NULL) {
            $params['landing_time'] = $this->landing_time;
        }
        if ($this->check_in_url != NULL) {
            $params['check_in_url'] = $this->check_in_url;
        }
        if ($this->air_model != NULL) {
            $params['air_model'] = $this->air_model;
        }
        return $params;
    }
}
