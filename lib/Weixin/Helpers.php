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
    public static function isJson ($string) {
    	if(strpos($string, "{")!==false) {
    		json_decode($string);
    		return (json_last_error() == JSON_ERROR_NONE);
    	}
    	else {
    		return false;
    	}
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    public static function paraFilter($para) {
    	$para_filter = array();
    	while (list ($key, $val) = each ($para)) {
    		if($key == "sign" || $key == "sign_type" || $val == "")continue;
    		else	$para_filter[$key] = $para[$key];
    	}
    	return $para_filter;
    }
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    public static function argSort($para) {
    	ksort($para);
    	reset($para);
    	return $para;
    }
}
