<?php
namespace Weixin\DatacubeManager;

use Weixin\WeixinException;
use Weixin\WeixinClient;

/**
 * 数据统计接口
 * 通过数据接口，开发者可以获取与公众平台官网统计模块类似但更灵活的数据，还可根据需要进行高级处理。
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class WeixinDatacubeManager
{

    protected $weixin;

    private $_url = 'https://api.weixin.qq.com/datacube/';

    public function __construct(WeixinClient $weixin, $options = array())
    {
        $this->weixin = $weixin;
    }

    /**
     * 用户分析数据接口指的是用于获得公众平台官网数据统计模块中用户分析数据的接口，具体接口列表如下（暂无用户属性数据接口）：
     *
     * 接口名称	最大时间跨度	接口调用地址（必须使用https）
     * 获取用户增减数据（getusersummary） 7	https://api.weixin.qq.com/datacube/getusersummary?access_token=ACCESS_TOKEN
     * 获取累计用户数据（getusercumulate） 7	https://api.weixin.qq.com/datacube/getusercumulate?access_token=ACCESS_TOKEN
     * 最大时间跨度是指一次接口调用时最大可获取数据的时间范围，如最大时间跨度为7是指最多一次性获取7天的数据。access_token的实际值请通过“获取access_token”来获取。
     *
     *
     * 接口调用请求说明
     *
     * 用户分析数据接口（包括接口列表中的所有接口）需要向相应接口调用地址POST以下示例数据包：
     *
     * {
     * "begin_date": "2014-12-02",
     * "end_date": "2014-12-07"
     * }
     * 调用参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * begin_date 是 获取数据的起始日期，begin_date和end_date的差值需小于“最大时间跨度”（比如最大时间跨度为1时，begin_date和end_date的差值只能为0，才能小于1），否则会报错
     * end_date 是 获取数据的结束日期，end_date允许设置的最大值为昨日
     * 返回说明
     *
     * 正常情况下，获取用户增减数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "user_source": 0,
     * "new_user": 0,
     * "cancel_user": 0
     * }
     * //后续还有ref_date在begin_date和end_date之间的数据
     * ]
     * }
     * 正常情况下，获取累计用户数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "cumulate_user": 1217056
     * },
     * //后续还有ref_date在begin_date和end_date之间的数据
     * ]
     * }
     * 返回参数说明
     *
     * 参数	说明
     * ref_date 数据的日期
     * user_source 用户的渠道，数值代表的含义如下：0代表其他 30代表扫二维码 17代表名片分享 35代表搜号码（即微信添加朋友页的搜索） 39代表查询微信公众帐号 43代表图文页右上角菜单
     * new_user 新增的用户数量
     * cancel_user 取消关注的用户数量，new_user减去cancel_user即为净增用户数量
     * cumulate_user 总用户量
     * 错误时微信会返回错误码等信息，具体错误码查询，请见：全局返回码说明
     */
    public function getUserSummary($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getusersummary?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUserCumulate($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getusercumulate?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 图文分析数据接口指的是用于获得公众平台官网数据统计模块中图文分析数据的接口，具体接口列表如下：
     *
     * 接口名称	最大时间跨度	接口调用地址（必须使用https）
     * 获取图文群发每日数据（getarticlesummary） 1	https://api.weixin.qq.com/datacube/getarticlesummary?access_token=ACCESS_TOKEN
     * 获取图文群发总数据（getarticletotal） 1	https://api.weixin.qq.com/datacube/getarticletotal?access_token=ACCESS_TOKEN
     * 获取图文统计数据（getuserread） 3	https://api.weixin.qq.com/datacube/getuserread?access_token=ACCESS_TOKEN
     * 获取图文统计分时数据（getuserreadhour） 1	https://api.weixin.qq.com/datacube/getuserreadhour?access_token=ACCESS_TOKEN
     * 获取图文分享转发数据（getusershare） 7	https://api.weixin.qq.com/datacube/getusershare?access_token=ACCESS_TOKEN
     * 获取图文分享转发分时数据（getusersharehour） 1	https://api.weixin.qq.com/datacube/getusersharehour?access_token=ACCESS_TOKEN
     * 最大时间跨度是指一次接口调用时最大可获取数据的时间范围，如最大时间跨度为7是指最多一次性获取7天的数据。access_token的实际值请通过“获取access_token”来获取。
     *
     *
     * 接口调用请求说明
     *
     * 图文分析数据接口（包括接口列表中的所有接口）需要向相应接口调用地址POST以下示例数据包：
     *
     * {
     * "begin_date": "2014-12-08",
     * "end_date": "2014-12-08"
     * }
     * 调用参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * begin_date 是 获取数据的起始日期，begin_date和end_date的差值需小于“最大时间跨度”（比如最大时间跨度为1时，begin_date和end_date的差值只能为0，才能小于1），否则会报错
     * end_date 是 获取数据的结束日期，end_date允许设置的最大值为昨日
     * 返回说明
     *
     * 正常情况下，获取图文群发每日数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-08",
     * "msgid": "10000050_1",
     * "title": "12月27日 DiLi日报",
     * "int_page_read_user": 23676,
     * "int_page_read_count": 25615,
     * "ori_page_read_user": 29,
     * "ori_page_read_count": 34,
     * "share_user": 122,
     * "share_count": 994,
     * "add_to_fav_user": 1,
     * "add_to_fav_count": 3
     * }
     * //后续会列出该日期内所有被阅读过的文章（仅包括群发的文章）在当天的阅读次数等数据
     * ]
     * }
     * 正常情况下，获取图文群发总数据接口的返回JSON数据包如下（请注意，details中，每天对应的数值为该文章到该日为止的总量（而不是当日的量））。 额外需要注意获取图文群发每日数据（getarticlesummary）和获取图文群发总数据（getarticletotal）的区别如下：
     *
     * 1、前者获取的是某天所有被阅读过的文章（仅包括群发的文章）在当天的阅读次数等数据。
     * 2、后者获取的是，某天群发的文章，从群发日起到接口调用日（但最多统计发表日后7天数据），每天的到当天的总等数据。例如某篇文章是12月1日发出的，发出后在1日、2日、3日的阅读次数分别为1万，则getarticletotal获取到的数据为，距发出到12月1日24时的总阅读量为1万，距发出到12月2日24时的总阅读量为2万，距发出到12月1日24时的总阅读量为3万。
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-14",
     * "msgid": "202457380_1",
     * "title": "马航丢画记",
     * "details": [
     * {
     * "stat_date": "2014-12-14",
     * "target_user": 261917,
     * "int_page_read_user": 23676,
     * "int_page_read_count": 25615,
     * "ori_page_read_user": 29,
     * "ori_page_read_count": 34,
     * "share_user": 122,
     * "share_count": 994,
     * "add_to_fav_user": 1,
     * "add_to_fav_count": 3
     * },
     * //后续还会列出所有stat_date符合“ref_date（群发的日期）到接口调用日期”（但最多只统计7天）的数据
     * ]
     * },
     * //后续还有ref_date（群发的日期）在begin_date和end_date之间的群发文章的数据
     * ]
     * }
     * 正常情况下，获取图文统计数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "int_page_read_user": 45524,
     * "int_page_read_count": 48796,
     * "ori_page_read_user": 11,
     * "ori_page_read_count": 35,
     * "share_user": 11,
     * "share_count": 276,
     * "add_to_fav_user": 5,
     * "add_to_fav_count": 15
     * },
     * //后续还有ref_date在begin_date和end_date之间的数据
     * ]
     * }
     * 正常情况下，获取图文统计分时数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "ref_hour": 1200,
     * "int_page_read_user": 0,
     * "int_page_read_count": 0,
     * "ori_page_read_user": 4,
     * "ori_page_read_count": 25517,
     * "share_user": 4,
     * "share_count": 96,
     * "add_to_fav_user": 1,
     * "add_to_fav_count": 3
     * }
     * //后续还有ref_hour逐渐增大,以列举1天24小时的数据
     * ]
     * }
     * 正常情况下，获取图文分享转发数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "share_scene": 1,
     * "share_count": 207,
     * "share_user": 11
     * },
     * {
     * "ref_date": "2014-12-07",
     * "share_scene": 5,
     * "share_count": 23,
     * "share_user": 11
     * }
     * //后续还有不同share_scene（分享场景）的数据，以及ref_date在begin_date和end_date之间的数据
     * ]
     * }
     * 正常情况下，获取图文分享转发每日数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "ref_hour": 1200,
     * "share_scene": 1,
     * "share_count": 72,
     * "share_user": 4
     * }
     * //后续还有不同share_scene的数据，以及ref_hour逐渐增大的数据。由于最大时间跨度为1，所以ref_date此处固定
     * ]
     * }
     * 返回参数说明
     *
     * 参数	说明
     * ref_date 数据的日期，需在begin_date和end_date之间
     * ref_hour 数据的小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时
     * stat_date 统计的日期，在getarticletotal接口中，ref_date指的是文章群发出日期， 而stat_date是数据统计日期
     * msgid 这里的msgid实际上是由msgid（图文消息id）和index（消息次序索引）组成， 例如12003_3， 其中12003是msgid，即一次群发的id消息的； 3为index，假设该次群发的图文消息共5个文章（因为可能为多图文）， 3表示5个中的第3个
     * title 图文消息的标题
     * int_page_read_user 图文页（点击群发图文卡片进入的页面）的阅读人数
     * int_page_read_count 图文页的阅读次数
     * ori_page_read_user 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0
     * ori_page_read_count 原文页的阅读次数
     * share_scene 分享的场景
     * 1代表好友转发 2代表朋友圈 3代表腾讯微博 255代表其他
     *
     * share_user 分享的人数
     * share_count 分享的次数
     * add_to_fav_user 收藏的人数
     * add_to_fav_count 收藏的次数
     * target_user 送达人数，一般约等于总粉丝数（需排除黑名单或其他异常情况下无法收到消息的粉丝）
     * 错误时微信会返回错误码等信息，具体错误码查询，请见：全局返回码说明
     */
    public function getArticleSummary($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getarticlesummary?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getArticleTotal($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getarticletotal?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUserRead($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getuserread?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUserReadHour($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getuserreadhour?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUserShare($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getusershare?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUserShareHour($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getusersharehour?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 消息分析数据接口指的是用于获得公众平台官网数据统计模块中消息分析数据的接口，具体接口列表如下（暂无消息关键词数据接口）：
     *
     * 接口名称	最大时间跨度	接口调用地址（必须使用https）
     * 获取消息发送概况数据（getupstreammsg） 7	https://api.weixin.qq.com/datacube/getupstreammsg?access_token=ACCESS_TOKEN
     * 获取消息分送分时数据（getupstreammsghour） 1	https://api.weixin.qq.com/datacube/getupstreammsghour?access_token=ACCESS_TOKEN
     * 获取消息发送周数据（getupstreammsgweek） 30	https://api.weixin.qq.com/datacube/getupstreammsgweek?access_token=ACCESS_TOKEN
     * 获取消息发送月数据（getupstreammsgmonth） 30	https://api.weixin.qq.com/datacube/getupstreammsgmonth?access_token=ACCESS_TOKEN
     * 获取消息发送分布数据（getupstreammsgdist） 15	https://api.weixin.qq.com/datacube/getupstreammsgdist?access_token=ACCESS_TOKEN
     * 获取消息发送分布周数据（getupstreammsgdistweek） 30	https://api.weixin.qq.com/datacube/getupstreammsgdistweek?access_token=ACCESS_TOKEN
     * 获取消息发送分布月数据（getupstreammsgdistmonth） 30	https://api.weixin.qq.com/datacube/getupstreammsgdistmonth?access_token=ACCESS_TOKEN
     * 最大时间跨度是指一次接口调用时最大可获取数据的时间范围，如最大时间跨度为7是指最多一次性获取7天的数据。access_token的实际值请通过“获取access_token”来获取。
     *
     * 关于周数据与月数据，请注意：每个月/周的周期数据的数据标注日期在当月/当周的第一天（当月1日或周一）。在某一月/周过后去调用接口，才能获取到该周期的数据。比如，在12月1日以（11月1日-11月5日）作为（begin_date和end_date）调用获取月数据接口，可以获取到11月1日的月数据（即11月的月数据）。
     *
     * 接口调用请求说明
     *
     * 消息分析数据接口（包括接口列表中的所有接口）需要向相应接口调用地址POST以下示例数据包：
     *
     * {
     * "begin_date": "2014-12-07",
     * "end_date": "2014-12-08"
     * }
     * 调用参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * begin_date 是 获取数据的起始日期，begin_date和end_date的差值需小于“最大时间跨度”（比如最大时间跨度为1时，begin_date和end_date的差值只能为0，才能小于1），否则会报错
     * end_date 是 获取数据的结束日期，end_date允许设置的最大值为昨日
     * 返回说明
     *
     * 获取消息发送概况数据接口需要向相应接口调用地址POST以下数据包：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "msg_type": 1,
     * "msg_user": 282,
     * "msg_count": 817
     * }
     * //后续还有同一ref_date的不同msg_type的数据，以及不同ref_date（在时间范围内）的数据
     * ]
     * }
     * 获取消息分送分时数据接口需要向相应接口调用地址POST以下数据包：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "ref_hour": 0,
     * "msg_type": 1,
     * "msg_user": 9,
     * "msg_count": 10
     * }
     * //后续还有同一ref_hour的不同msg_type的数据，以及不同ref_hour的数据，ref_date固定，因为最大时间跨度为1
     * ]
     * }
     * 获取消息发送周数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-08",
     * "msg_type": 1,
     * "msg_user": 16,
     * "msg_count": 27
     * }
     * //后续还有同一ref_date下不同msg_type的数据，及不同ref_date的数据
     * ]
     * }
     * 获取消息发送月数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-11-01",
     * "msg_type": 1,
     * "msg_user": 7989,
     * "msg_count": 42206
     * }
     * //后续还有同一ref_date下不同msg_type的数据，及不同ref_date的数据
     * ]
     * }
     * 获取消息发送分布数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "count_interval": 1,
     * "msg_user": 246
     * }
     * //后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
     * ]
     * }
     * 获取消息发送分布周数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "count_interval": 1,
     * "msg_user": 246
     * }
     * //后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
     * ]
     * }
     * 获取消息发送分布月数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "count_interval": 1,
     * "msg_user": 246
     * }
     * //后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
     * ]
     * }
     * 返回参数说明
     *
     * 参数	说明
     * ref_date 数据的日期，需在begin_date和end_date之间
     * ref_hour 数据的小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时
     * msg_type 消息类型，代表含义如下：
     * 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）
     *
     * msg_user 上行发送了（向公众号发送了）消息的用户数
     * msg_count 上行发送了消息的消息总数
     * count_interval 当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”
     * int_page_read_count 图文页的阅读次数
     * ori_page_read_user 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0
     * 错误时微信会返回错误码等信息，具体错误码查询，请见：全局返回码说明
     */
    public function getUpstreamMsg($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsg?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgHour($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsghour?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgWeek($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsgweek?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgMonth($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsgmonth?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgDist($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsgdist?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgDistWeek($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsgdistweek?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getUpstreamMsgDistMonth($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getupstreammsgdistmonth?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 接口分析数据接口指的是用于获得公众平台官网数据统计模块中接口分析数据的接口，具体接口列表如无用户属性数据接口）：
     *
     * 接口名称	最大时间跨度	接口调用地址（必须使用https）
     * 获取接口分析数据（getinterfacesummary） 30	https://api.weixin.qq.com/datacube/getinterfacesummary?access_token=ACCESS_TOKEN
     * 获取接口分析分时数据（getinterfacesummaryhour） 1	https://api.weixin.qq.com/datacube/getinterfacesummaryhour?access_token=ACCESS_TOKEN
     * 最大时间跨度是指一次接口调用时最大可获取数据的时间范围，如最大时间跨度为7是指最多一次性获取7天的数据。access_token的实际值请通过“获取access_token”来获取。
     *
     *
     * 接口调用请求说明
     *
     * 接口分析数据接口（包括接口列表中的所有接口）需要向相应接口调用地址POST以下示例数据包：
     *
     * {
     * "begin_date": "2014-12-07",
     * "end_date": "2014-12-07"
     * }
     * 调用参数说明
     *
     * 参数	是否必须	说明
     * access_token 是 调用接口凭证
     * begin_date 是 获取数据的起始日期，begin_date和end_date的差值需小于“最大时间跨度”（比如最大时间跨度为1时，begin_date和end_date的差值只能为0，才能小于1），否则会报错
     * end_date 是 获取数据的结束日期，end_date允许设置的最大值为昨日
     * 返回说明
     *
     * 正常情况下，获取接口分析数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-07",
     * "callback_count": 36974,
     * "fail_count": 67,
     * "total_time_cost": 14994291,
     * "max_time_cost": 5044
     * }
     * //后续还有不同ref_date（在begin_date和end_date之间）的数据
     * ]
     * }
     * 正常情况下，获取接口分析分时数据接口的返回JSON数据包如下：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2014-12-01",
     * "ref_hour": 0,
     * "callback_count": 331,
     * "fail_count": 18,
     * "total_time_cost": 167870,
     * "max_time_cost": 5042
     * }
     * //后续还有不同ref_hour的数据
     * ]
     * }
     * 返回参数说明
     *
     * 参数	说明
     * ref_date 数据的日期
     * ref_hour 数据的小时
     * callback_count 通过服务器配置地址获得消息后，被动回复用户消息的次数
     * fail_count 上述动作的失败次数
     * total_time_cost 总耗时，除以callback_count即为平均耗时
     * max_time_cost 最大耗时
     * 错误时微信会返回错误码等信息，具体错误码查询，请见：全局返回码说明
     */
    public function getInterfaceSummary($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getinterfacesummary?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    public function getInterfaceSummaryHour($begin_date, $end_date)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getinterfacesummaryhour?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 拉取卡券概况数据接口
     *
     * 接口说明
     *
     * 支持调用该接口拉取本商户的总体数据情况，包括时间区间内的各指标总量。
     *
     * 特别注意： 1. 查询时间区间需<=62天，否则报错{errcode: 61501，errmsg: "date range error"}；
     *
     * 2. 传入时间格式需严格参照示例填写”2015-06-15”，否则报错{errcode":61500,"errmsg":"date format error"}
     *
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/datacube/getcardbizuininfo?access_token=ACCESS_TOKEN
     * 请求参数说明
     *
     * 参数	是否必须	说明
     * access_token	是	调用接口凭证
     * POST数据	是	Json数据
     * POST数据
     *
     * ｛
     * "begin_date":"2015-06-15", //请开发者按示例格式填写日期，否则会报错date format error
     * "end_date":"2015-06-30",
     * "cond_source": 0
     * ｝
     *
     * 参数说明：
     *
     * 字段	说明	是否必填	类型	示例值
     * begin_date	查询数据的起始时间。	是	string(16)	2015-06-15
     * end_date	查询数据的截至时间。	是	string(16)	2015-06-30
     * cond_source	卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据	是	unsigned int	0
     * 返回数据说明 数据示例：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2015-06-23",
     * "view_cnt": 1,
     * "view_user": 1,
     * "receive_cnt": 1,
     * "receive_user": 1,
     * "verify_cnt": 0,
     * "verify_user": 0,
     * "given_cnt": 0,
     * "given_user": 0,
     * "expire_cnt": 0,
     * "expire_user": 0
     * }
     * ]
     * }
     *
     * 字段说明：
     *
     * 字段	说明
     * ref_date	日期信息
     * view_cnt	浏览次数
     * view_user	浏览人数
     * receive_cnt	领取次数
     * receive_user	领取人数
     * verify_cnt	使用次数
     * verify_user	使用人数
     * given_cnt	转赠次数
     * given_user	转赠人数
     * expire_cnt	过期次数
     * expire_user	过期人数
     */
    public function getCardBizuinInfo($begin_date, $end_date, $cond_source = 1)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date,
            "cond_source" => $cond_source
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getcardbizuininfo?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取免费券数据接口
     *
     * 接口说明
     *
     * 支持开发者调用该接口拉取免费券（优惠券、团购券、折扣券、礼品券）在固定时间区间内的相关数据。
     *
     * 特别注意：
     *
     * 1. 该接口目前仅支持拉取免费券（优惠券、团购券、折扣券、礼品券）的卡券相关数据，暂不支持特殊票券（电影票、会议门票、景区门票、飞机票）数据。
     *
     * 2. 查询时间区间需<=62天，否则报错{"errcode:" 61501，errmsg: "date range error"}；
     *
     * 3. 传入时间格式需严格参照示例填写如”2015-06-15”，否则报错｛"errcode":"date format error"｝
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/datacube/getcardcardinfo?access_token=ACCESS_TOKEN
     * 请求参数说明
     *
     * 参数	是否必须	说明
     * access_token	是	调用接口凭证
     * POST数据	是	Json数据
     * POST数据
     *
     * ｛
     * "begin_date":"2015-06-15",
     * "end_date":"2015-06-30",
     * "cond_source": 0,
     * "card_id": "po8pktyDLmakNY2fn2VyhkiEPqGE"
     * ｝
     *
     * 参数说明：
     *
     * 字段	说明	是否必填	类型	示例值
     * begin_date	查询数据的起始时间。	是	string(16)	2015-06-15
     * end_date	查询数据的截至时间。	是	string(16)	2015-06-30
     * cond_source	卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据	是	unsigned int	0
     * card_id	卡券ID。填写后，指定拉出该卡券的相关数据。	否	string(32)	po8pktyDLmakNY2fn2VyhkiEPqGE
     *
     * 返回数据说明 数据示例：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2015-06-23",
     * "card_id": "po8pktyDLmakNY2fn2VyhkiEPqGE",
     * "card_type":3,
     * "view_cnt": 1,
     * "view_user": 1,
     * "receive_cnt": 1,
     * "receive_user": 1,
     * "verify_cnt": 0,
     * "verify_user": 0,
     * "given_cnt": 0,
     * "given_user": 0,
     * "expire_cnt": 0,
     * "expire_user": 0
     * }
     * ]
     * }
     *
     * 字段说明：
     *
     * 字段	说明
     * ref_date	日期信息
     * card_id	卡券ID
     * card_type	cardtype:0：折扣券，1：代金券，2：礼品券，3：优惠券，4：团购券（暂不支持拉取特殊票券类型数据，电影票、飞机票、会议门票、景区门票）
     * view_cnt	浏览次数
     * view_user	浏览人数
     * receive_cnt	领取次数
     * receive_user	领取人数
     * verify_cnt	使用次数
     * verify_user	使用人数
     * given_cnt	转赠次数
     * given_user	转赠人数
     * expire_cnt	过期次数
     * expire_user	过期人数
     */
    public function getCardCardInfo($begin_date, $end_date, $cond_source = 1, $card_id = '')
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date,
            "cond_source" => $cond_source
        );
        if (! empty($card_id)) {
            $params['card_id'] = $card_id;
        }
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getcardcardinfo?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }

    /**
     * 拉取会员卡数据接口
     *
     * 接口简介及开发者注意事项
     *
     * 为支持开发者调用API查看卡券相关数据，微信卡券团队封装数据接口并面向具备卡券功能权限的开发者开放使用。开发者调用该接口可获取本商户下的所有卡券相关的总数据以及指定卡券的相关数据。开发过程请务必注意以下事项：
     *
     * 1.查询时间区间需<=62天，否则报错{errcode: 61501，errmsg: "date range error"}；
     *
     * 2.传入时间格式需严格参照示例填写”2015-06-15”，否则报错{errcode":61500,"errmsg":"date format error"}；
     *
     * 3.需在获取卡券相关数据前区分卡券创建渠道：公众平台创建、调用卡券接口创建。
     *
     * 接口说明
     *
     * 支持开发者调用该接口拉取公众平台创建的会员卡相关数据。
     *
     *
     * 接口调用请求说明
     *
     * http请求方式: POST
     * https://api.weixin.qq.com/datacube/getcardmembercardinfo?access_token=ACCESS_TOKEN
     * 参数说明
     *
     * 参数	是否必须	说明
     * POST数据	是	Json数据
     * access_token	是	调用接口凭证
     * POST数据
     *
     * ｛
     * "begin_date":"2015-06-15",
     * "end_date":"2015-06-30",
     * "cond_source": 0
     * ｝
     * 参数说明：
     *
     * 字段	说明	是否必填	类型	示例值
     * begin_date	查询数据的起始时间。	是	string(16)	2015-06-15
     * end_date	查询数据的截至时间。	是	string(16)	2015-06-30
     * cond_source	卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据	是	unsigned int	0
     * 返回数据说明 数据示例：
     *
     * {
     * "list": [
     * {
     * "ref_date": "2015-06-23",
     * "view_cnt": 0,
     * "view_user": 0,
     * "receive_cnt": 0,
     * "receive_user": 0,
     * "active_user": 0,
     * "verify_cnt": 0,
     * "verify_user": 0,
     * "total_user": 86,
     * "total_receive_user": 95
     * ]
     * }
     * 字段说明：
     *
     * 字段	说明
     * ref_date	日期信息
     * view_cnt	浏览次数
     * view_user	浏览人数
     * receive_cnt	领取次数
     * receive_user	领取人数
     * verify_cnt	使用次数
     * verify_user	使用人数
     * active_user	激活人数
     * total_user	有效会员总人数
     * total_receive_user	历史领取会员卡总人数
     */
    public function getCardMembercardInfo($begin_date, $end_date, $cond_source = 1)
    {
        $params = array(
            "begin_date" => $begin_date,
            "end_date" => $end_date,
            "cond_source" => $cond_source
        );
        $access_token = $this->weixin->getToken();
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $rst = $this->weixin->post($this->_url . 'getcardmembercardinfo?access_token=' . $access_token, $json);
        
        if (! empty($rst['errcode'])) {
            throw new WeixinException($rst['errmsg'], $rst['errcode']);
        } else {
            return $rst;
        }
    }
}
