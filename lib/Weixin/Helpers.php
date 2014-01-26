<?php
namespace Weixin;

/**
 * Defines a few helper methods.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class Helpers
{    
    /**
     *
     * 检测一个字符串否为Json字符串
     * @param string $string
     * @return true/false
     *
     */
    public public function isJson ($string) {
    	if(strpos($string, "{")!==false) {
    		json_decode($string);
    		return (json_last_error() == JSON_ERROR_NONE);
    	}
    	else {
    		return false;
    	}
    }
}
