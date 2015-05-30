<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = showcontactInfo($data);
$response_json = json_encode($response);//生成json数据
print_r($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:23:09
 * @description: 根据联系人id展示单个联系人的详细信息
 * @para:        $data['user_id'] 					用户唯一识别id
 * @return:       $response 				array 	当前联系人的个人信息
 *				 $response['user_id']    			userid 	
 *				 $response['user_name']   	string	用户昵称
 *				 $response['user_headpic']	string	朋友圈头图
 *													size:不限制 建议>400px	
 *				 $response['user_avatar']  	string	用户头像
 *													size:60px*60px;	 
 *				 $response['user_sign']		string	签名
 *		 
 */

function showcontactInfo($data){
	$userid = $data['user_id'];
	$response = array();

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

	return $response;
}
?>