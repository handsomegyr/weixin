<?php
namespace Weixin\CustomServiceManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 获取客服聊天记录接口
 *
 * 在需要时，开发者可以通过获取客服聊天记录接口，
 * 获取多客服的会话记录，包括客服和用户会话的所有消息记录和会话的创建、关闭等操作记录。
 * 利用此接口可以开发如“消息记录”、“工作监控”、“客服绩效考核”等功能。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinCustomServiceManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/customservice/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 查询分组
     *
     * @return mixed
     */
    public function get()
    {
        // 接口调用请求说明
        // http请求方式: POST
        // https://api.weixin.qq.com/cgi-bin/customservice/getrecord?access_token=ACCESS_TOKEN
        // 参数 说明
        // access_token 调用接口凭证
        $access_token = $this->weixin->getToken('access_token');
        $params = array();
        $params['access_token'] = $access_token;
        $rst = $this->weixin->get($this->_url . 'get', $params);
        if (! empty($rst['errcode'])) {
            // 错误时的JSON数据包示例（该示例为AppID无效错误）：
            
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // {
            // "groups": [
            // {
            // "id": 0,
            // "name": "未分组",
            // "count": 72596
            // },
            // {
            // "id": 1,
            // "name": "黑名单",
            // "count": 36
            // },
            // {
            // "id": 2,
            // "name": "星标组",
            // "count": 8
            // },
            // {
            // "id": 104,
            // "name": "华东媒",
            // "count": 4
            // },
            // {
            // "id": 106,
            // "name": "★不测试组★",
            // "count": 1
            // }
            // ]
            // }
            // 参数 说明
            // groups 公众平台分组信息列表
            // id 分组id，由微信分配
            // name 分组名字，UTF8编码
            // count 分组内用户数量
            return $rst;
        }
    }

    /**
     * 获取客服聊天记录接口
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/cgi-bin/customservice/getrecord?access_token=ACCESS_TOKEN
     * POST数据示例如下：
     * {
     * "starttime" : 123456789,
     * "endtime" : 987654321,
     * "openid" : "OPENID",
     * "pagesize" : 10,
     * "pageindex" : 1,
     * }
     *
     * @return mixed
     */
    public function getRecord($openid, $starttime, $endtime, $pageindex = 1, $pagesize = 1000)
    {        
        /**
         * openid 否 普通用户的标识，对当前公众号唯一
         * starttime 是 查询开始时间，UNIX时间戳
         * endtime 是 查询结束时间，UNIX时间戳，每次查询不能跨日查询
         * pagesize 是 每页大小，每页最多拉取1000条
         * pageindex 是 查询第几页，从1开始
         */
        $access_token = $this->weixin->getToken();
        $params = array();
        if ($openid) {
            $params['openid'] = $openid;
        }
        $params['starttime'] = $starttime;
        $params['endtime'] = $endtime;
        $params['pageindex'] = $pageindex;
        $params['pagesize'] = $pagesize;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getrecord?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // {
            // "recordlist": [
            // {
            // "worker": " test1",
            // "openid": "oDF3iY9WMaswOPWjCIp_f3Bnpljk",
            // "opercode": 2002,
            // "time": 1400563710,
            // "text": " 您好，客服test1为您服务。"
            // },
            // {
            // "worker": " test1",
            // "openid": "oDF3iY9WMaswOPWjCIp_f3Bnpljk",
            // "opercode": 2003,
            // "time": 1400563731,
            // "text": " 你好，有什么事情？ "
            // },
            // ]
            // }
            return $rst;
        }
    }
}
