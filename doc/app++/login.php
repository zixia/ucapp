<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组


$response = login($data);
$response_json = json_encode($response);//生成json数据
print_r($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 10:57:49
 * @description: 实现用户登录
 * @para:        $data['username']			string 	前端传递的用户名
 * @para:        $data['password'] 			string 	前端传递的密码 
 * @return:      $response 					array 	用户的个人信息:
 *				 $response['ret'] 			boolen 	登陆成功true 登陆失败false boolen
 *				 $response['user_id']  				用户唯一识别id
 *				 $response['user_name']		string 	用户昵称
 *				 $response['user_avatar']	string 	用户头像
 *				 $response['user_headpic']	string 	用户朋友圈头图
 *				 $response['user_gender']	string 	用户性别
 *				 $response['user_area']		string 	地域
 *				 $response['user_sign']		string 	个人签名
 *			 
 */

function login($data){
	$response = array();
	$response['ret'] = null;

	if ($data['username']=='zixia'&&$data['password']==123456) {
		$response['ret'] = true;

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

	return $response;
}

?>

