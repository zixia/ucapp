<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

showcontactInfo($data);


//实现好友个人主页信息展示，在朋友圈上方展示好友的基本信息
function showcontactInfo($data){
	$userid = $data['user_id'];
	$response = array();

	//根据userid 添加相关内容，返回$response

	//下面是我瞎填的东西
	if ($userid == 1111) {
		$response['user_id'] = 1111;
		$response['user_name'] = "紫霞";
		$response['user_headpic'] = "./img/heihei.jpg";
		$response['user_avatar'] = "./img/head.jpg";
		$response['user_sign'] = "帅帅坏坏哒";
	}

	if ($userid == 2222) {
		$response['user_id'] = 2222;
		$response['user_name'] = "zixia";
		$response['user_headpic'] = "./img/con2.jpg";
		$response['user_avatar'] = "./img/con1.jpg";
		$response['user_sign'] = "帅帅坏坏哒";
	}

	if ($userid == 3333) {
		$response['user_id'] = 3333;
		$response['user_name'] = "ruirui";
		$response['user_headpic'] = "./img/heihei.jpg";
		$response['user_avatar'] = "./img/con2.jpg";
		$response['user_sign'] = "帅帅坏坏哒";
	}

	if ($userid == 4444) {
		$response['user_id'] = 4444;
		$response['user_name'] = "芮芮";
		$response['user_headpic'] = "./img/dance.png";
		$response['user_avatar'] = "./img/1.jpg";
		$response['user_sign'] = "帅帅坏坏哒";
	}	

	$response_json = json_encode($response);//生成json数据
	print_r($response_json);
}
?>