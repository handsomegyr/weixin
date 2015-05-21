<?php
namespace Weixin\MenuManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 自定义菜单接口
 * 自定义菜单能够帮助公众号丰富界面，
 * 让用户更好更快地理解公众号的功能。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinMenuManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/cgi-bin/menu/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 自定义菜单创建接口
     * 目前自定义菜单最多包括3个一级菜单，
     * 每个一级菜单最多包含5个二级菜单。
     * 一级菜单最多4个汉字，二级菜单最多7个汉字，
     * 多出来的部分将会以“...”代替。请注意，
     * 创建自定义菜单后，由于微信客户端缓存，
     * 需要24小时微信客户端才会展现出来。
     * 建议测试时可以尝试取消关注公众账号后再次关注，
     * 则可以看到创建后的效果。
     * 目前自定义菜单接口可实现两种类型按钮，如下：
     * click：
     * 用户点击click类型按钮后，
     * 微信服务器会通过消息接口推送消息类型为event	的结构给开发者
     * （参考消息接口指南），并且带上按钮中开发者填写的key值，
     * 开发者可以通过自定义的key值与用户进行交互；
     * view：
     * 用户点击view类型按钮后，
     * 微信客户端将会打开开发者在按钮中填写的url值	（即网页链接），
     * 达到打开网页的目的，建议与网页授权获取用户基本信息接口结合，
     * 获得用户的登入个人信息。
     *
     * @author Kan
     *        
     */
    public function create($menus)
    {
        // 接口调用请求说明
        // http请求方式：POST（请使用https协议） https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN
        // 请求示例
        // {
        // "button":[
        // {
        // "type":"click",
        // "name":"今日歌曲",
        // "key":"V1001_TODAY_MUSIC"
        // },
        // {
        // "type":"click",
        // "name":"歌手简介",
        // "key":"V1001_TODAY_SINGER"
        // },
        // {
        // "name":"菜单",
        // "sub_button":[
        // {
        // "type":"view",
        // "name":"搜索",
        // "url":"http://www.soso.com/"
        // },
        // {
        // "type":"view",
        // "name":"视频",
        // "url":"http://v.qq.com/"
        // },
        // {
        // "type":"click",
        // "name":"赞一下我们",
        // "key":"V1001_GOOD"
        // }]
        // }]
        // }
        // 参数说明
        // 参数 是否必须 说明
        // button 是 一级菜单数组，个数应为1~3个
        // sub_button 否 二级菜单数组，个数应为1~5个
        // type 是 菜单的响应动作类型，目前有click、view两种类型
        // name 是 菜单标题，不超过16个字节，子菜单不超过40个字节
        // key click类型必须 菜单KEY值，用于消息接口推送，不超过128字节
        // url view类型必须 网页链接，用户点击菜单可打开链接，不超过256字节
        $access_token = $this->weixin->getToken();
        $json = json_encode($menus, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'create?access_token=' . $access_token, $json);
        // 返回结果
        if (! empty($rst['errcode'])) {
            // 错误时的返回JSON数据包如下（示例为无效菜单名长度）：
            // {"errcode":40018,"errmsg":"invalid button name size"}
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 返回说明
            // 正确时的返回JSON数据包如下：
            // {"errcode":0,"errmsg":"ok"}
            return $rst;
        }
    }

    /**
     * 自定义菜单查询接口
     * 使用接口创建自定义菜单后，开发者还可使用接口查询自定义菜单的结构。
     */
    public function get()
    {
        // 请求说明
        // http请求方式：GET
        // https://api.weixin.qq.com/cgi-bin/menu/get?access_token=ACCESS_TOKEN
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $rst = $this->weixin->get($this->_url . 'get', $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 对应创建接口，正确的Json返回结果:
            // {
            // "menu": {
            // "button": [
            // {
            // "type": "click",
            // "name": "今日歌曲",
            // "key": "V1001_TODAY_MUSIC",
            // "sub_button": [ ]
            // },
            // {
            // "type": "click",
            // "name": "歌手简介",
            // "key": "V1001_TODAY_SINGER",
            // "sub_button": [ ]
            // },
            // {
            // "name": "菜单",
            // "sub_button": [
            // {
            // "type": "view",
            // "name": "搜索",
            // "url": "http://www.soso.com/",
            // "sub_button": [ ]
            // },
            // {
            // "type": "view",
            // "name": "视频",
            // "url": "http://v.qq.com/",
            // "sub_button": [ ]
            // },
            // {
            // "type": "click",
            // "name": "赞一下我们",
            // "key": "V1001_GOOD",
            // "sub_button": [ ]
            // }
            // ]
            // }
            // ]
            // }
            // }
            return $rst;
        }
    }

    /**
     * 自定义菜单删除接口
     * 使用接口创建自定义菜单后，开发者还可使用接口删除当前使用的自定义菜单。
     *
     * @return array
     */
    public function delete()
    {
        // 请求说明
        // http请求方式：GET
        // https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $rst = $this->weixin->get($this->_url . 'delete', $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            // 对应创建接口，正确的Json返回结果:
            // {"errcode":0,"errmsg":"ok"}
            return $rst;
        }
    }

    private function validateSubbutton($menus)
    {
        $ret = 0;
        foreach ($menus as $menu) {
            if (key_exists("sub_button", $menu)) {
                $sub_button_num = count($menu['sub_button']);
                if ($sub_button_num > 5 || $sub_button_num < 1) {
                    $ret = 40023;
                    break;
                }
                $ret = $this->validateSubbutton($menu['sub_button']);
                if ($ret)
                    break;
            }
        }
        return $ret;
    }

    private function validateKey($menu)
    {
        // click等点击类型必须
        if (in_array(strtolower($menu['type']), array(
            'click',
            'scancode_push',
            'scancode_waitmsg',
            'pic_sysphoto',
            'pic_photo_or_album',
            'pic_weixin',
            'location_select'
        ))) {
            if (strlen(trim($menu['key'])) < 1)
                return 40019;
        }
        // 按钮KEY值，用于消息接口(event类型)推送，不超过128字节
        if (strlen(trim($menu['key'])) > 128)
            return 40019;
        return 0;
    }

    private function validateName($menu)
    {
        // 按钮描述，既按钮名字，不超过16个字节，子菜单不超过40个字节
        if ($menu['fatherNode'])         // 子菜单
        {
            if (strlen($menu['name']) > 40)
                return 40018;
        } else         // 按钮
        {
            if (strlen($menu['name']) > 16)
                return 40018;
        }
        return 0;
    }

    public function validateMenu($menu)
    {
        $errcode = $this->validateName($menu);
        if ($errcode) {
            return $errcode;
        }
        $errcode = $this->validateKey($menu);
        if ($errcode) {
            return $errcode;
        }
        return 0;
    }

    public function validateAllMenus($menus)
    {
        // 按钮数组，按钮个数应为1~3个
        $button_num = count($menus);
        if ($button_num > 3 || $button_num < 1) {
            return 40017;
        }
        
        // 子按钮数组，按钮个数应为1~5个
        if ($this->validateSubbutton($menus)) {
            return 40023;
        }
    }

    /**
     * 获取自定义菜单配置接口
     * 本接口将会提供公众号当前使用的自定义菜单的配置，如果公众号是通过API调用设置的菜单，则返回菜单的开发配置，而如果公众号是在公众平台官网通过网站功能发布菜单，则本接口返回运营者设置的菜单配置。
     *
     * 请注意：
     *
     * 1、第三方平台开发者可以通过本接口，在旗下公众号将业务授权给你后，立即通过本接口检测公众号的自定义菜单配置，并通过接口再次给公众号设置好自动回复规则，以提升公众号运营者的业务体验。
     * 2、本接口与自定义菜单查询接口的不同之处在于，本接口无论公众号的接口是如何设置的，都能查询到接口，而自定义菜单查询接口则仅能查询到使用API设置的菜单配置。
     * 3、认证/未认证的服务号/订阅号，以及接口测试号，均拥有该接口权限。
     * 4、从第三方平台的公众号登录授权机制上来说，该接口从属于消息与菜单权限集。
     * 5、本接口中返回的mediaID均为临时素材（通过素材管理-获取临时素材接口来获取这些素材），每次接口调用返回的mediaID都是临时的、不同的，在每次接口调用后3天有效，若需永久使用该素材，需使用素材管理接口中的永久素材。
     * 接口调用请求说明
     *
     * http请求方式: GET（请使用https协议）
     * https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=ACCESS_TOKEN
     * 返回结果说明
     *
     * 如果公众号是在公众平台官网通过网站功能发布菜单，则本接口返回的自定义菜单配置样例如下：
     *
     * {
     * "is_menu_open": 1,
     * "selfmenu_info": {
     * "button": [
     * {
     * "name": "button",
     * "sub_button": {
     * "list": [
     * {
     * "type": "view",
     * "name": "view_url",
     * "url": "http://www.qq.com"
     * },
     * {
     * "type": "news",
     * "name": "news",
     * "news_info": {
     * "list": [
     * {
     * "title": "MULTI_NEWS",
     * "author": "JIMZHENG",
     * "digest": "text",
     * "show_cover": 0,
     * "cover_url": "http://mmbiz.qpic.cn/mmbiz/GE7et87vE9vicuCibqXsX9GPPLuEtBfXfK0HKuBIa1A1cypS0uY1wickv70iaY1gf3I1DTszuJoS3lAVLvhTcm9sDA/0",
     * "content_url": "http://mp.weixin.qq.com/s?__biz=MjM5ODUwNTM3Ng==&mid=204013432&idx=1&sn=80ce6d9abcb832237bf86c87e50fda15#rd",
     * "source_url": ""
     * },
     * {
     * "title": "MULTI_NEWS1",
     * "author": "JIMZHENG",
     * "digest": "MULTI_NEWS1",
     * "show_cover": 1,
     * "cover_url": "http://mmbiz.qpic.cn/mmbiz/GE7et87vE9vicuCibqXsX9GPPLuEtBfXfKnmnpXYgWmQD5gXUrEApIYBCgvh2yHsu3ic3anDUGtUCHwjiaEC5bicd7A/0",
     * "content_url": "http://mp.weixin.qq.com/s?__biz=MjM5ODUwNTM3Ng==&mid=204013432&idx=2&sn=8226843afb14ecdecb08d9ce46bc1d37#rd",
     * "source_url": ""
     * }
     * ]
     * }
     * },
     * {
     * "type": "video",
     * "name": "video",
     * "value": "http://61.182.130.30/vweixinp.tc.qq.com/1007_114bcede9a2244eeb5ab7f76d951df5f.f10.mp4?vkey=77A42D0C2015FBB0A3653D29C571B5F4BBF1D243FBEF17F09C24FF1F2F22E30881BD350E360BC53F&sha=0&save=1"
     * },
     * {
     * "type": "voice",
     * "name": "voice",
     * "value": "nTXe3aghlQ4XYHa0AQPWiQQbFW9RVtaYTLPC1PCQx11qc9UB6CiUPFjdkeEtJicn"
     * }
     * ]
     * }
     * },
     * {
     * "type": "text",
     * "name": "text",
     * "value": "This is text!"
     * },
     * {
     * "type": "img",
     * "name": "photo",
     * "value": "ax5Whs5dsoomJLEppAvftBUuH7CgXCZGFbFJifmbUjnQk_ierMHY99Y5d2Cv14RD"
     * }
     * ]
     * }
     * }
     * 如果公众号是通过API调用设置的菜单，自定义菜单配置样例如下：
     *
     * {
     * "is_menu_open": 1,
     * "selfmenu_info": {
     * "button": [
     * {
     * "type": "click",
     * "name": "今日歌曲",
     * "key": "V1001_TODAY_MUSIC"
     * },
     * {
     * "name": "菜单",
     * "sub_button": {
     * "list": [
     * {
     * "type": "view",
     * "name": "搜索",
     * "url": "http://www.soso.com/"
     * },
     * {
     * "type": "view",
     * "name": "视频",
     * "url": "http://v.qq.com/"
     * },
     * {
     * "type": "click",
     * "name": "赞一下我们",
     * "key": "V1001_GOOD"
     * }
     * ]
     * }
     * }
     * ]
     * }
     * }
     * 参数说明
     *
     * 参数	说明
     * is_menu_open 菜单是否开启，0代表未开启，1代表开启
     * selfmenu_info 菜单信息
     * button 菜单按钮
     * type 菜单的类型，公众平台官网上能够设置的菜单类型有view（跳转网页）、text（返回文本，下同）、img、photo、video、voice。使用API设置的则有8种，详见《自定义菜单创建接口》
     * name 菜单名称
     * value、url、key等字段 对于不同的菜单类型，value的值意义不同。官网上设置的自定义菜单：
     * Text:保存文字到value； Img、voice：保存mediaID到value； Video：保存视频下载链接到value； News：保存图文消息到news_info； View：保存链接到url。
     *
     * 使用API设置的自定义菜单： click、scancode_push、scancode_waitmsg、pic_sysphoto、pic_photo_or_album、	pic_weixin、location_select：保存值到key；view：保存链接到url
     *
     * news_info 图文消息的信息
     * title 图文消息的标题
     * digest 摘要
     * author 作者
     * show_cover 是否显示封面，0为不显示，1为显示
     * cover_url 封面图片的URL
     * content_url 正文的URL
     * source_url 原文的URL，若置空则无查看原文入口
     */
    public function getCurrentSelfMenuInfo()
    {        
        $access_token = $this->weixin->getToken();
        $params = array();
        $params['access_token'] = $access_token;
        $rst = $this->weixin->get('https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info', $params);
        // 返回说明
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
