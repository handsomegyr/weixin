weixin
======

is a PHP (>= 5.3) client library for the Weixin

调用实例如下

//如果之前获得access_token，那么在生成WeixinClient对象的时候，直接指定
//$access_token = "RWRVPpT1O9SEyN615puzCOQ9uQfgQK0SA63gWUxNo2ABjgHFdnCL82BnFB_wQGeZH4prBLfn17Qz0WSwcwdLW6A2YvX1yN46dDB2-BggdXkqpM0AZXO4lfZ0LSC_5ABj8NxKLxJkqv565EBja32Gpw";
//$client = new Weixin\WeixinClient("wxf0fb0fa333cdbd5f","aa1bce11453640719681a9d3858dc0d8",$access_token);


//如果之前没有获得过access_token，那么通过getAccessToken方法 获取access_token
$client = new Weixin\WeixinClient("wxf0fb0fa333cdbd5f","aa1bce11453640719681a9d3858dc0d8");
$rst = $client->getAccessToken();
$access_token = $rst['access_token'];
 
echo $access_token;
echo "<br/>";
//发送客服文本消息
$client->getWeixinMsgManager()->getWeixinCustomMsgSender()->sendText("oosvgjvqK6r51ce7wpyy1DyG97oc", "测试");

//下载多媒体文件
$ret= $client->getWeixinMediaManager()->get("8DPMeaEEqlFPVJS1HZG_NCiHmC8NQqhKAwh99-W3GbRJxqLpdVRZLrCbCuIBmmWR");
$fileContent = base64_decode($ret['content']);
$tmpfname = sys_get_temp_dir().'/'.uniqid().'.jpg';
//保存在本地
file_put_contents($tmpfname, $fileContent);

//获取微信用户信息
$userinfo =$client->getWeixinUserManager()->getUserInfo("oosvgjvqK6r51ce7wpyy1DyG97oc");
print_r($userinfo);
echo "<br/>";

die("OK");
