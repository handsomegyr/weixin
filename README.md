weixin
======

is a PHP (>= 5.3) client library for the Weixin

### Loading the library ###

Weixin relies on the autoloading features of PHP to load its files when needed and complies with the
[PSR-0 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) which makes
it compatible with most PHP frameworks. Autoloading is handled automatically when dependencies are
managed using Composer, but you can also leverage its own autoloader if you are going to use it in a
project or script without any PSR-0 compliant autoloading facility:

```php
// Prepend a base path if Weixin is not available in your "include_path".
require 'Weixin/Autoloader.php';

Weixin\Autoloader::register();
```

It is possible to easily create a [phar](http://www.php.net/manual/en/intro.phar.php) archive from
the repository just by launching `bin/create-phar`. The generated phar contains a stub defining an
autoloader function for Weixin, so you just need to require the phar to start using the library.
Alternatively, it is also possible to generate one single PHP file that holds every class like older
versions of Weixin by launching `bin/create-single-file`, but this practice __is not__ encouraged.


### 调用事例 ###

```php
try {
	
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
	
} catch (Exception $e) {
	echo($e->getMessage());
}
```
事例代码如下所示


