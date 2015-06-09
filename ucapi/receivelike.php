<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$user = $req['user'];

$response = receivelike($user);
$response_json = json_encode($response);//生成json数据
die($response_json);


/**
 * @Author:      name
 * @DateTime:    2015-06-09 15:14:33
 * @Description: 实现接受点赞功能
 * @Para			$resp['h']['ret'] = 0 接受成功并成功入库
 *					$resp['b']
 */


function receivelike($user){
   

    $resp = array();

    if($user){
    	//处理成功
    	resp['h']['ret'] = 0;
    }
    else{
    	resp['h']['ret'] = ERR_UNKNOWN;
    }
    
 }

?>