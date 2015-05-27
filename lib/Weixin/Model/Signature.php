<?php
namespace Weixin\Model;

class Signature
{

    public function __construct()
    {
        $this->data = array();
    }

    public function add_data($str)
    {
        array_push($this->data, (string) $str);
    }

    public function get_signature()
    {
        sort($this->data);
        return sha1(implode($this->data));
    }
}
