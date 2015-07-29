<?php
namespace Weixin\Model;

// ------------------------set base_info-----------------------------
$base_info = new BaseInfo("http://www.supadmin.cn/uploads/allimg/120216/1_120216214725_1.jpg", "海底捞", 0, "132元双人火锅套餐", "Color010", "使用时向服务员出示此券", "020-88888888", "不可与其他优惠同享\n 如需团购券发票，请在消费时向商户提出\n 店内均可使用，仅限堂食\n 餐前不可打包，餐后未吃完，可打包\n 本团购券不限人数，建议2人使用，超过建议人数须另收酱料费5元/位\n 本单谢绝自带酒水饮料", new DateInfo(1, 1397577600, 1399910400), new Sku(50000000));
$base_info->set_sub_title("");
$base_info->set_use_limit(1);
$base_info->set_get_limit(3);
$base_info->set_use_custom_code(false);
$base_info->set_bind_openid(false);
$base_info->set_can_share(true);
$base_info->set_url_name_type(1);
$base_info->set_custom_url("http://www.qq.com");
// ---------------------------set_card--------------------------------

$card = new Groupon($base_info, "以下锅底2 选1（有菌王锅、麻辣锅、大骨锅、番茄锅、清补凉锅、酸菜鱼锅可选）：\n 大锅1 份12 元\n 小锅2 份16 元\n 以下菜品2 选1\n 特级肥牛1 份30 元\n 洞庭鮰鱼卷1 份20元\n 其他\n鲜菇猪肉滑1 份18 元\n 金针菇1 份16 元\n 黑木耳1 份9 元\n 娃娃菜1 份8 元\n 冬瓜1份6 元\n 火锅面2 个6 元\n 欢乐畅饮2 位12 元\n 自助酱料2 位10 元");

// ----------------------check signature------------------------
$signature = new Signature();
$signature->add_data("123");
$signature->add_data("wasda");
$signature->add_data("_()@#(&");
echo $signature->get_signature();
?>