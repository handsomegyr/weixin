<?php
namespace Weixin\MenuManager;
use Weixin\Helpers;
use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 自定义菜单接口
 * 自定义菜单能够帮助公众号丰富界面，
 * 让用户更好更快地理解公众号的功能。
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinMenuManager
{
	protected $weixin;
	private $_url = 'https://api.weixin.qq.com/cgi-bin/menu/';

	public function __construct(WeixinClient $weixin) {
		$this->weixin  = $weixin;
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
		目前自定义菜单接口可实现两种类型按钮，如下：
		click：
		用户点击click类型按钮后，
		微信服务器会通过消息接口推送消息类型为event	的结构给开发者
		（参考消息接口指南），并且带上按钮中开发者填写的key值，
		开发者可以通过自定义的key值与用户进行交互；
		view：
		用户点击view类型按钮后，
		微信客户端将会打开开发者在按钮中填写的url值	（即网页链接），
		达到打开网页的目的，建议与网页授权获取用户基本信息接口结合，
		获得用户的登入个人信息。
	 * @author Kan
	 *
	 */
	public function create($menus)
	{
		//接口调用请求说明
		// http请求方式：POST（请使用https协议） https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN
		// 请求示例
		//  {
		//      "button":[
		//      {
		//           "type":"click",
		//           "name":"今日歌曲",
		//           "key":"V1001_TODAY_MUSIC"
		//       },
		//       {
		//            "type":"click",
		//            "name":"歌手简介",
		//            "key":"V1001_TODAY_SINGER"
		//       },
		//       {
		//            "name":"菜单",
		//            "sub_button":[
		//            {
		//                "type":"view",
		//                "name":"搜索",
		//                "url":"http://www.soso.com/"
		//             },
		//             {
		//                "type":"view",
		//                "name":"视频",
		//                "url":"http://v.qq.com/"
		//             },
		//             {
		//                "type":"click",
		//                "name":"赞一下我们",
		//                "key":"V1001_GOOD"
		//             }]
		//        }]
		//  }
		// 参数说明
		// 参数	是否必须	说明
		// button	 是	 一级菜单数组，个数应为1~3个
		// sub_button	 否	 二级菜单数组，个数应为1~5个
		// type	 是	 菜单的响应动作类型，目前有click、view两种类型
		// name	 是	 菜单标题，不超过16个字节，子菜单不超过40个字节
		// key	 click类型必须	 菜单KEY值，用于消息接口推送，不超过128字节
		// url	 view类型必须	 网页链接，用户点击菜单可打开链接，不超过256字节
		$access_token = $this->weixin->getToken();
		$json = json_encode($menus,JSON_UNESCAPED_UNICODE);
		$rst = $this->weixin->post($this->_url.'create?access_token='.$access_token, $json);
		// 返回结果
		if(!empty($rst['errcode']))
		{
			// 错误时的返回JSON数据包如下（示例为无效菜单名长度）：
			// {"errcode":40018,"errmsg":"invalid button name size"}
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			//返回说明
			// 正确时的返回JSON数据包如下：
			// {"errcode":0,"errmsg":"ok"}
			return $rst;
		}

	}
	/**
	 * 自定义菜单查询接口
	 * 使用接口创建自定义菜单后，开发者还可使用接口查询自定义菜单的结构。
	 *
	 */
	public function get()
	{
		//请求说明
		//http请求方式：GET
		//https://api.weixin.qq.com/cgi-bin/menu/get?access_token=ACCESS_TOKEN
		$access_token = $this->weixin->getToken();
		$params = array();
		$params['access_token'] = $access_token;
		$rst = $this->weixin->get($this->_url.'get',$params);
		//返回说明
		if(!empty($rst['errcode']))
		{
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			//对应创建接口，正确的Json返回结果:
			//     	{
			//     		"menu": {
			//     		"button": [
			//     		{
			//     			"type": "click",
			//     			"name": "今日歌曲",
			//     			"key": "V1001_TODAY_MUSIC",
			//     			"sub_button": [ ]
			//     		},
			//     		{
			//     			"type": "click",
			//     			"name": "歌手简介",
			//     			"key": "V1001_TODAY_SINGER",
			//     			"sub_button": [ ]
			//     		},
			//     		{
			//     			"name": "菜单",
			//     		"sub_button": [
			//     		{
			//     			"type": "view",
			//     			"name": "搜索",
			//     			"url": "http://www.soso.com/",
			//     			"sub_button": [ ]
			//     		},
			//     		{
			//     			"type": "view",
			//     			"name": "视频",
			//     			"url": "http://v.qq.com/",
			//     			"sub_button": [ ]
			//     		},
			//     		{
			//     			"type": "click",
			//     			"name": "赞一下我们",
			//     			"key": "V1001_GOOD",
			//     			"sub_button": [ ]
			//     		}
			//     		]
			//     		}
			//     		]
			//     	}
			//     	}
			return $rst;
		}

	}

	/**
	 * 自定义菜单删除接口
	 * 使用接口创建自定义菜单后，开发者还可使用接口删除当前使用的自定义菜单。
	 * @return array
	 */
	public function delete()
	{
		//请求说明
		//http请求方式：GET
		//https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN
		$access_token = $this->weixin->getToken();
		$params = array();
		$params['access_token'] = $access_token;
		$rst = $this->weixin->get($this->_url.'delete',$params);
		//返回说明
		if(!empty($rst['errcode']))
		{
			throw new WeixinException($rst['errmsg'],$rst['errcode']);
		}
		else
		{
			//对应创建接口，正确的Json返回结果:
			//{"errcode":0,"errmsg":"ok"}
			return $rst;
		}
	}

	private function validateSubbutton($menus)
	{
		$ret = 0;
		foreach ($menus as $menu) {
			if(key_exists("sub_button", $menu))
			{
				$sub_button_num = count($menu['sub_button']);
				if($sub_button_num>5 || $sub_button_num<1 )
				{
					$ret = 40023;
					break;
				}
				$ret = $this->validateSubbutton($menu['sub_button']);
				if($ret) break;
			}
		}
		return $ret;
	}

	private function validateKey($menu)
	{
		//类型为click必须
		if(strtolower($menu['type']) == 'click'){
			if(strlen(trim($menu['key']))<1) return 40019;
		}
		//按钮KEY值，用于消息接口(event类型)推送，不超过128字节
		if(strlen(trim($menu['key']))>128) return 40019;
		return 0;
	}

	private function validateName($menu)
	{
		//按钮描述，既按钮名字，不超过16个字节，子菜单不超过40个字节
		if($menu['fatherNode'])//子菜单
		{
			if(strlen($menu['name'])>40) return 40018;
		}
		else//按钮
		{
			if(strlen($menu['name'])>16) return 40018;
		}
		return 0;
	}

	public function validateMenu($menu)
	{
		$errcode = $this->validateName($menu);
		if($errcode)
		{
			return $errcode;
		}
		$errcode = $this->validateKey($menu);
		if($errcode)
		{
			return $errcode;
		}
		return 0;
	}

	public function validateAllMenus($menus)
	{
		//按钮数组，按钮个数应为1~3个
		$button_num =count($menus);
		if($button_num>3 || $button_num<1 )
		{
			return 40017;
		}

		//子按钮数组，按钮个数应为1~5个
		if($this->validateSubbutton($menus))
		{
			return 40023;
		}

	}
}
