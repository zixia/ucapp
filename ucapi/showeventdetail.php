<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$response = showevent($req);
$response_json = json_encode($response);//生成json数据
die($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-06-04 16:03:43
 * @Description: 展示活动列表信息,展示所有未结束的活动信息
 * @return       $response[] 所有消息的列表
 *               $response['event_id']                   活动id
 *               $response['event_title']        string  活动主题 
 *               $response['event_img']          string  活动图片 tips：仅传送一张图片
 *               $response['event_time']         string  活动时间 format:XX年XX月XX日  XX:XX --XX年XX月XX日  XX:XX
 *               $response['event_spot']         string  活动具体地址
 *               $response['event_people']       string  活动发起人
 *               $response['event_view']         string  此活动已被查看次数
 *               $response['event_participate']  string  活动参与人数
 *               $response['event_attention']    string  活动关注人数
 *               $response['event_type']         string  活动类型
 *               $response['event_ddl']          string  活动截止时间 fromat:XX年XX月XX日 XX:XX
 *               $response['event_maxmun']       string  活动限制人数
 *               $response['event_authen']       string  是否需要审核
 *               $response['event_intro']        string  活动介绍
 */

    function showevent($req){
        $event_id = $req['event_id'];

        $response = array();
        $response['event_id'] = 1234;
        $response['event_title'] = "【广州古巴莎莎舞俱乐部】6月9日新学期啦！";
        $response['event_img'] = "img/2.jpg";
        $response['event_time'] = "05月30日 21:00 - 06月30日 01:00";
        $response['event_spot'] = "广东 广州 珠江新城马场路16号 富力盈盛广场B座3楼会所 （地铁5号线 潭村D出口直走10m右侧）";
        $response['event_people'] = "左吟";
        $response['event_view'] = 41;
        $response['event_participate'] = 5;
        $response['event_attention'] = 0;
        $response['event_type'] = "舞会/聚会";
        $response['event_ddl'] = "06月29日 21:00";
        $response['event_maxmun'] = "不限";
        $response['event_authen'] = "不需要";
        $response['event_intro'] = "【舞种介绍-古巴莎莎舞】 Salsa属于拉丁风情舞的一种，相当于拉丁舞的交谊舞，主要起源于古巴。因为她的动感、易学以及自由丰富的表达形式，现流行于世界各大城市（北京，上海，广州几乎每个晚上都有Salsa舞会哦）。原汁原味的古巴salsa以她的热情奔放和韵律感而深受欢迎，是为喜爱跳舞的朋友聚集在一起享受音乐而存在的舞蹈。 ";

        return $response;

    }

?>
