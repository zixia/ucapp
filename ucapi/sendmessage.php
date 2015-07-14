<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$user_id = $req['user_id'];//发送人的id
$txt = $req['txt'];//发送的内容


$response = sendmessage($user_id,$txt);
$response_json = json_encode($response);//生成json数据
die($response_json);


function sendmessage($user_id,$txt){
	


	return $response;
}
?>