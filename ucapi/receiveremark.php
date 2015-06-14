<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$contact_id = $req['contact_id'];
$item_id = $req['item_id'];
$content = $req ['content'];

$response = receiveremark($contact_id,$item_id,$content);
$response_json = json_encode($response);//生成json数据
die($response_json);


/**
 * @Author:      name
 * @DateTime:    2015-06-09 15:14:33
 * @Description: 实现接受评论功能
 *					$user : 前端传送的userid
 *					$content:前端传送的评论内容
 * @Para			$resp['h']['ret'] = 0 接受成功并成功入库
 *					$resp['b']
 */


function receiveremark($contact_id,$item_id,$content){
   

    $resp = array();

    if($user&&$content){
    	//处理成功
    	resp['h']['ret'] = 0;
    }
    else{
    	resp['h']['ret'] = ERR_UNKNOWN;
    }
    
 }

?>