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

    private $_url2 = 'https://api.weixin.qq.com/customservice/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
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
     * 返回说明 正常时的返回JSON数据包示例：
     * {
     * "recordlist": [
     * {
     * "worker": " test1",
     * "openid": "oDF3iY9WMaswOPWjCIp_f3Bnpljk",
     * "opercode": 2002,
     * "time": 1400563710,
     * "text": " 您好，客服test1为您服务。"
     * },
     * {
     * "worker": " test1",
     * "openid": "oDF3iY9WMaswOPWjCIp_f3Bnpljk",
     * "opercode": 2003,
     * "time": 1400563731,
     * "text": " 你好，有什么事情？ "
     * },
     * ]
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
            return $rst;
        }
    }

    /**
     * 获取客服基本信息
     *
     * 开发者通过本接口，根据AppID获取公众号中所设置的客服基本信息，包括客服工号、客服昵称、客服登录账号。
     * 开发者利用客服基本信息，结合客服接待情况，可以开发例如“指定客服接待”等功能。
     *
     * 接口调用说明
     *
     * http请求方式: GET
     * https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=ACCESS_TOKEN
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "kf_list": [
     * {
     * "kf_account": "test1@test",
     * "kf_nick": "ntest1",
     * "kf_id": "1001"
     * "kf_headimg": "http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjfUS8Ym0GSaLic0FD3vN0V8PILcibEGb2fPfEOmw/0"
     * },
     * {
     * "kf_account": "test2@test",
     * "kf_nick": "ntest2",
     * "kf_id": "1002"
     * "kf_headimg": "http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjfUS8Ym0GSaLic0FD3vN0V8PILcibEGb2fPfEOmw/0"
     * },
     * {
     * "kf_account": "test3@test",
     * "kf_nick": "ntest3",
     * "kf_id": "1003"
     * "kf_headimg": "http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjfUS8Ym0GSaLic0FD3vN0V8PILcibEGb2fPfEOmw/0"
     * }
     * ]
     * }
     * 参数 说明
     * kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * kf_nick 客服昵称
     * kf_id 客服工号
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     */
    public function getkflist()
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->get($this->_url . 'customservice/getkflist?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取在线客服接待信息
     *
     * 开发者通过本接口，根据AppID获取公众号中当前在线的客服的接待信息，
     * 包括客服工号、客服登录账号、客服在线状态（手机在线、PC客户端在线、手机和PC客户端全都在线）、
     * 客服自动接入最大值、客服当前接待客户数。
     * 开发者利用本接口提供的信息，结合客服基本信息，
     * 可以开发例如“指定客服接待”等功能；结合会话记录，
     * 可以开发”在线客服实时服务质量监控“等功能。
     *
     * 接口调用请求说明
     *
     * http请求方式: GET
     * https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=ACCESS_TOKEN
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "kf_online_list": [
     * {
     * "kf_account": "test1@test",
     * "status": 1,
     * "kf_id": "1001",
     * "auto_accept": 0,
     * "accepted_case": 1
     * },
     * {
     * "kf_account": "test2@test",
     * "status": 1,
     * "kf_id": "1002",
     * "auto_accept": 0,
     * "accepted_case": 2
     * }
     * ]
     * }
     * 参数 说明
     * kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * status 客服在线状态 1：pc在线，2：手机在线。若pc和手机同时在线则为 1+2=3
     * kf_id 客服工号
     * auto_accept 客服设置的最大自动接入数
     * accepted_case 客服当前正在接待的会话数
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     */
    public function getonlinekflist()
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->get($this->_url . 'customservice/getonlinekflist?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 添加客服账号
     *
     * 开发者通过本接口可以为公众号添加客服账号，每个公众号最多添加10个客服账号。
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/customservice/kfaccount/add?access_token=ACCESS_TOKEN
     * POST数据说明
     *
     * POST数据示例如下：
     * {
     * "kf_account" : test1@test,
     * "nickname" : “客服1”,
     * "password" : "pswmd5",
     * }
     * 参数 是否必须 说明
     * kf_account 是 完整客服账号，格式为：账号前缀@公众号微信号，账号前缀最多10个字符，必须是英文或者数字字符。如果没有公众号微信号，请前往微信公众平台设置。
     * nickname 是 客服昵称，最长6个汉字或12个英文字符
     * password 是 客服账号登录密码，格式为密码明文的32位加密MD5值
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "errcode" : 0,
     * "errmsg" : "ok",
     * }
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     *
     * @param string $kf_account            
     * @param string $nickname            
     * @param string $password            
     */
    public function kfaccountAdd($kf_account, $nickname, $password)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['kf_account'] = $kf_account;
        $params['nickname'] = $nickname;
        $params['password'] = $password;
        
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url2 . 'kfaccount/add?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 邀请绑定客服帐号
     * 新添加的客服帐号是不能直接使用的，只有客服人员用微信号绑定了客服账号后，方可登录Web客服进行操作。此接口发起一个绑定邀请到客服人员微信号，客服人员需要在微信客户端上用该微信号确认后帐号才可用。尚未绑定微信号的帐号可以进行绑定邀请操作，邀请未失效时不能对该帐号进行再次绑定微信号邀请。
     * 调用说明
     * http请求方式: POST
     * https://api.weixin.qq.com/customservice/kfaccount/inviteworker?access_token=ACCESS_TOKEN
     * POST数据示例如下：
     * {
     * "kf_account" : "test1@test",
     * "invite_wx" : "test_kfwx"
     * }
     * 参数说明
     * 参数 说明
     * kf_account 完整客服帐号，格式为：帐号前缀@公众号微信号
     * invite_wx 接收绑定邀请的客服微信号
     * 返回说明
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "errcode" : 0,
     * "errmsg" : "ok"
     * }
     * 主要返回码
     * 返回码 说明
     * 0 成功
     * 65400 API不可用，即没有开通/升级到新版客服
     * 65401 无效客服帐号
     * 65407 邀请对象已经是本公众号客服
     * 65408 本公众号已发送邀请给该微信号
     * 65409 无效的微信号
     * 65410 邀请对象绑定公众号客服数量达到上限（目前每个微信号最多可以绑定5个公众号客服帐号）
     * 65411 该帐号已经有一个等待确认的邀请，不能重复邀请
     * 65412 该帐号已经绑定微信号，不能进行邀请
     */
    public function inviteWorker($kf_account, $invite_wx)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['kf_account'] = $kf_account;
        $params['invite_wx'] = $invite_wx;
        
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url2 . 'kfaccount/inviteworker?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 设置客服信息
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/customservice/kfaccount/update?access_token=ACCESS_TOKEN
     * POST数据说明
     *
     * POST数据示例如下：
     * {
     * "kf_account" : test1@test,
     * "nickname" : “客服1”,
     * "password" : "pswmd5",
     * }
     * 参数 是否必须 说明
     * kf_account 是 完整客服账号，格式为：账号前缀@公众号微信号
     * nickname 是 客服昵称，最长6个汉字或12个英文字符
     * password 是 客服账号登录密码，格式为密码明文的32位加密MD5值
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "errcode" : 0,
     * "errmsg" : "ok",
     * }
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     *
     * @param string $kf_account            
     * @param string $nickname            
     * @param string $password            
     */
    public function kfaccountUpdate($kf_account, $nickname, $password)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['kf_account'] = $kf_account;
        $params['nickname'] = $nickname;
        $params['password'] = $password;
        
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url2 . 'kfaccount/update?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 上传客服头像
     *
     * 开发者可调用本接口来上传图片作为客服人员的头像，头像图片文件必须是jpg格式，推荐使用640*640大小的图片以达到最佳效果。
     *
     * 接口调用请求说明
     *
     * http请求方式: POST/FORM
     * http://api.weixin.qq.com/customservice/kfacount/uploadheadimg?access_token=ACCESS_TOKEN&kf_account=KFACCOUNT
     *
     * 调用示例（使用curl命令，用FORM表单方式上传一个多媒体文件）：
     * curl -F media=@test.jpg "https://api.weixin.qq.com/customservice/kfacount/uploadheadimg?access_token=ACCESS_TOKEN&kf_account=KFACCOUNT"
     * 参数说明
     *
     * 参数 是否必须 说明
     * kf_account 是 完整客服账号，格式为：账号前缀@公众号微信号
     * media 是 form-data中媒体文件标识，有filename、filelength、content-type等信息
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "errcode" : 0,
     * "errmsg" : "ok",
     * }
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     *
     * @param string $kf_account            
     * @param string $media_path            
     */
    public function kfacountUploadheadimg($kf_account, $media_path)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $params['kf_account'] = $kf_account;
        $params['media'] = '@' . $media_path;
        $rst = $this->weixin->post($this->_url2 . 'kfacount/uploadheadimg', $params, true);
        
        // 返回结果
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 删除客服账号
     *
     * 接口调用请求说明
     *
     * http请求方式: GET
     * https://api.weixin.qq.com/customservice/kfaccount/del?access_token=ACCESS_TOKEN&kf_account=KFACCOUNT
     * 参数说明
     *
     * 参数 是否必须 说明
     * kf_account 是 完整客服账号，格式为：账号前缀@公众号微信号
     * 返回说明
     *
     * 返回数据示例（正确时的JSON返回结果）：
     * {
     * "errcode" : 0,
     * "errmsg" : "ok",
     * }
     * 错误时微信会返回错误码等信息，请根据错误码查询错误信息:全局返回码说明
     *
     * @param string $kf_account            
     */
    public function kfaccountDel($kf_account)
    {
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['kf_account'] = $kf_account;
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url2 . 'kfaccount/del?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
