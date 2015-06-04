<?php

include_once('/750/xfs/vhost/17salsa.com/home/common.php');
include_once(S_ROOT.'./source/function_cp.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = showevent();
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
 */

    function showevent(){
        $response = array();
        $item1 = array();
        $item1['event_id'] = 1234;
        $item1['event_title'] = "【广州古巴莎莎舞俱乐部】6月9日新学期啦！";
        $item1['event_img'] = "img/2.jpg";
        $item1['event_time'] = "05月30日 21:00 - 06月30日 01:00";
        $item1['event_spot'] = "广东 广州 珠江新城马场路16号 富力盈盛广场B座3楼会所 （地铁5号线 潭村D出口直走10m右侧）";
        $item1['event_people'] = "左吟";
        $item1['event_view'] = 41;
        $item1['event_participate'] = 5;
        $item1['event_attention'] = 0;

        $item2 = array();
        $item2['event_id'] = 1235;
        $item2['event_title'] = "【广州古巴莎莎舞俱乐部】6月9日新学期啦！";
        $item2['event_img'] = "img/2.jpg";
        $item2['event_time'] = "05月30日 21:00 - 06月30日 01:00";
        $item2['event_spot'] = "广东 广州 珠江新城马场路16号 富力盈盛广场B座3楼会所 （地铁5号线 潭村D出口直走10m右侧）";
        $item2['event_people'] = "左吟";
        $item2['event_view'] = 41;
        $item2['event_participate'] = 5;
        $item2['event_attention'] = 0;

        $response = array($item1,$item2);

        return $response;

    }

?>
