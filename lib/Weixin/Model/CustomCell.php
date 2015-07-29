<?php
namespace Weixin\Model;

/**
 * 会员卡类型专属营销入口，会员卡激活前后均显示
 */
class CustomCell
{

    /**
     * name
     * 入口名称
     * 是
     */
    public $name = NULL;

    /**
     * tips
     * 入口右侧提示语，6个汉字内
     * 否
     */
    public $tips = NULL;

    /**
     * url
     * 入口跳转链接
     * 是
     */
    public $url = NULL;

    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function set_tips($tips)
    {
        $this->tips = $tips;
    }

    public function getParams()
    {
        $params = array();
        if ($this->isNotNull($this->name)) {
            $params['name'] = $this->name;
        }
        if ($this->isNotNull($this->tips)) {
            $params['tips'] = $this->tips;
        }
        if ($this->isNotNull($this->url)) {
            $params['url'] = $this->url;
        }
        return $params;
    }
    
    protected function isNotNull($var)
    {
        return ! is_null($var);
    }
}
