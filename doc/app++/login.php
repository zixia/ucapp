<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

login($data);


//实现用户登录
function login($data){
	$response = array();
	$response['ret'] = null;

	if ($data['username']=='zixia'&&$data['password']==123456) {
		$response['ret'] = true;
		$response['sid'] = '0123456789';

		//  个人主页的基本信息
		$response['user_id'] = 2222;
		$response['user_name']="芮芮";
		$response['user_avatar']="./img/1.jpg";
		$response['user_headpic']="./img/3.jpg";
		$response['user_gender']="女";
		$response['user_area']="北京 海淀";
		$response['user_sign']="感觉自己萌萌哒";
	}
	else
		$response['ret'] = false;

	$response_json = json_encode($response);//生成json数据
	print_r($response_json);
}
?>