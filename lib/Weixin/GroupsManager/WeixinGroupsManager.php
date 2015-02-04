<?php
namespace Weixin\GroupsManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 分组管理接口
 * 开发者可以使用接口，
 * 对公众平台的分组进行查询、创建、修改操作，
 * 也可以使用接口在需要时移动用户到某个分组。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinGroupsManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/groups/';

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
        // http请求方式: GET（请使用https协议）
        // https://api.weixin.qq.com/cgi-bin/groups/get?access_token=ACCESS_TOKEN
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
     * 创建分组
     * 一个公众账号，最多支持创建500个分组
     *
     * @param
     *            $name
     * @return mixed
     */
    public function create($name)
    {
        // 接口调用请求说明
        // http请求方式: POST（请使用https协议）
        // https://api.weixin.qq.com/cgi-bin/groups/create?access_token=ACCESS_TOKEN
        // POST数据格式：json
        // POST数据例子：{"group":{"name":"test"}}
        // 参数 说明
        // access_token 调用接口凭证
        // name 分组名字（30个字符以内）
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['group']['name'] = $name;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'create?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            // 错误时的JSON数据包示例（该示例为AppID无效错误）：
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // {
            // "group": {
            // "id": 107,
            // "name": "test"
            // }
            // }
            // 参数 说明
            // id 分组id，由微信分配
            // name 分组名字，UTF8编码
            return $rst;
        }
    }

    /**
     * 修改分组名
     *
     * @param
     *            $id
     * @param
     *            $name
     * @return mixed
     */
    public function update($id, $name)
    {
        // 接口调用请求说明
        
        // http请求方式: POST（请使用https协议）
        // https://api.weixin.qq.com/cgi-bin/groups/update?access_token=ACCESS_TOKEN
        // POST数据格式：json
        // POST数据例子：{"group":{"id":108,"name":"test2_modify2"}}
        // 参数 说明
        // access_token 调用接口凭证
        // id 分组id，由微信分配
        // name 分组名字（30个字符以内）
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['group']['id'] = $id;
        $params['group']['name'] = $name;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            // 错误时的JSON数据包示例（该示例为AppID无效错误）：
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // {"errcode": 0, "errmsg": "ok"}
            return $rst;
        }
    }

    /**
     * 移动用户分组
     *
     * @param
     *            $openid
     * @param
     *            $to_groupid
     * @return mixed
     */
    public function membersUpdate($openid, $to_groupid)
    {
        // 接口调用请求说明
        // http请求方式: POST（请使用https协议）
        // https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=ACCESS_TOKEN
        // POST数据格式：json
        // POST数据例子：{"openid":"oDF3iYx0ro3_7jD4HFRDfrjdCM58","to_groupid":108}
        // 参数 说明
        // access_token 调用接口凭证
        // openid 用户唯一标识符
        // to_groupid 分组id
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['openid'] = $openid;
        $params['to_groupid'] = $to_groupid;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'members/update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            // 错误时的JSON数据包示例（该示例为AppID无效错误）：
            // {"errcode":40013,"errmsg":"invalid appid"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明 正常时的返回JSON数据包示例：
            // {"errcode": 0, "errmsg": "ok"}
            return $rst;
        }
    }

    /**
     * 查询用户所在分组
     *
     * @param
     *            $openid
     * @return mixed
     */
    public function getid($openid)
    {
        /**
         * 通过用户的OpenID查询其所在的GroupID。 接口调用请求说明
         *
         * http请求方式: POST（请使用https协议）
         * https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=ACCESS_TOKEN
         * POST数据格式：json
         * POST数据例子：{"openid":"od8XIjsmk6QdVTETa9jLtGWA6KBc"}
         * 参数说明
         *
         * 参数	说明
         * access_token 调用接口凭证
         * openid 用户的OpenID
         * 返回说明 正常时的返回JSON数据包示例：
         *
         * {
         * "groupid": 102
         * }
         * 参数说明
         *
         * 参数	说明
         * groupid 用户所属的groupid
         * 错误时的JSON数据包示例（该示例为OpenID无效错误）：
         *
         * {"errcode":40003,"errmsg":"invalid openid"}
         */
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['openid'] = $openid;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getid?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 批量移动用户分组
     *
     * 接口调用请求说明
     *
     * http请求方式: POST（请使用https协议）
     * https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token=ACCESS_TOKEN
     * POST数据格式：json
     * POST数据例子：{"openid_list":["oDF3iYx0ro3_7jD4HFRDfrjdCM58","oDF3iY9FGSSRHom3B-0w5j4jlEyY"],"to_groupid":108}
     * 参数说明
     *
     * 参数	说明
     * access_token 调用接口凭证
     * openid_list 用户唯一标识符openid的列表（size不能超过50）
     * to_groupid 分组id
     * 返回说明 正常时的返回JSON数据包示例：
     *
     * {"errcode": 0, "errmsg": "ok"}
     * 错误时的JSON数据包示例（该示例为AppID无效错误）：
     *
     * {"errcode":40013,"errmsg":"invalid appid"}
     */
    public function membersBatchUpdate(array $openid_list, $to_groupid)
    {        
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['openid_list'] = $openid_list;
        $params['to_groupid'] = $to_groupid;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'members/batchupdate?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
