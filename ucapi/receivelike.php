<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$contact_id = $req['contact_id'];
$item_id = $req['item_id'];

$response = receivelike($contact_id,$item_id);
$response_json = json_encode($response);//生成json数据
die($response_json);


/**
 * @Author:      name
 * @DateTime:    2015-06-09 15:14:33
 * @Description: 实现接受点赞功能
 * @Para			$resp['h']['r'] = ERR_OK 接受成功并成功入库
 *					$resp['b']
 */


function receivelike($contact_id,$item_id){
    $resp = array();
    $resp['h']['r'] = ERR_UNKNOWN;

    //$contact_id是发这个状态的联系人的user_id
    //$item_id时这个联系人发的第$item_id条消息
    

    //处理成功
    $resp['h']['r'] = ERR_OK;
    return $resp;
 }

?>
