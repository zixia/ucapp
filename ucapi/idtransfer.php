<?php
require_once('inc/config.inc.php');
$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$response = idtransfer($req;);
$response_json = json_encode($response);//生成json数据
die($response_json);




/**
 * @Author:      ruirui
 * @DateTime:    2015-06-05 18:05:44
 * @Description: 发送id数组，返回id和相关信息数组
 *				$response[]['id'] 用户id
 *				$response[]['content']['username'] 用户昵称
 *				$response[]['content']['avatar'] 用户头像
 */


//实现朋友圈信息展示
function idtransfer($req){
	$idlist = $req['idlist'];

	$resp['b'] = array();
    $resp['h'] = array();

	$content = array("username"=>"zixia","avatar"=>"./img/con1.jpg");

	$response[0]['id'] = $idlist[0];
	$response[0]['content'] = $content;

	$resp['b'] = $response;


	return $resp;
}
?>