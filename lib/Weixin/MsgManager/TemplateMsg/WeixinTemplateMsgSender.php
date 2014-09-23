<?php
namespace Weixin\MsgManager\TemplateMsg;

use Weixin\WeixinException;
use Weixin\MsgManager\WeixinMsgManager;

/**
 * 模板消息仅用于公众号向用户发送重要的服务通知，
 * 只能用于符合其要求的服务场景中，如信用卡刷卡通知，商品购买成功通知等。
 * 不支持广告等营销类消息以及其它所有可能对用户造成骚扰的消息。
 *
 * 关于使用规则，请注意：
 *
 * 1、所有服务号都可以在功能->添加功能插件处看到申请模板消息功能的入口，
 * 但只有认证后的服务号才可以申请模板消息的使用权限并获得该权限；
 * 2、需要选择公众账号服务所处的2个行业，每月可更改1次所选行业；
 * 3、在所选择行业的模板库中选用已有的模板进行调用；
 * 4、每个账号可以同时使用10个模板。
 * 关于接口文档，请注意：
 *
 * 1、模板消息调用时主要需要模板ID和模板中各参数的赋值内容；
 * 2、模板中参数内容必须以".DATA"结尾，否则视为保留字；
 * 3、模板保留符号"{{ }}"。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinTemplateMsgSender
{

    protected $weixinMsgManager;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

    public function __construct(WeixinMsgManager $weixinMsgManager, $options = array())
    {
        $this->weixinMsgManager = $weixinMsgManager;
    }

    /**
     * 发送模板消息
     *
     * @param string $touser            
     * @param string $template_id            
     * @param string $url            
     * @param string $topcolor            
     * @param array $data            
     *
     * @throws Exception
     * @return array
     */
    public function send($touser, $template_id, $url, $topcolor, array $data)
    {
        /**
         * {
         * "touser":"OPENID",
         * "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
         * "url":"http://weixin.qq.com/download",
         * "topcolor":"#FF0000",
         * "data":{
         * "first": {
         * "value":"您好，您已成功消费。",
         * "color":"#0A0A0A"
         * },
         * "keynote1":{
         * "value":"海记汕头牛肉",
         * "color":"#CCCCCC"
         * },
         * "keynote2": {
         * "value":"8703514836",
         * "color":"#CCCCCC"
         * },
         * "keynote3":{
         * "value":"2014-08-03 19:35",
         * "color":"#CCCCCC"
         * },
         * "remark":{
         * "value":"欢迎再次购买。",
         * "color":"#173177"
         * }
         * }
         */
        $params = array();
        $params['touser'] = $touser;
        $params['template_id'] = $template_id;
        $params['url'] = $url;
        $params['topcolor'] = $topcolor;
        $params['data'] = $data;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $access_token = $this->weixinMsgManager->getWeixin()->getToken();
        $rst = $this->weixinMsgManager->getWeixin()->post($this->_url . '?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
