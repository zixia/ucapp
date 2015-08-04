<?php
require_once('inc/config.inc.php');

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

    $m_space = getspace($userid);
	$avatar_exists = ckavatar($userid);

	//下面是我瞎填的东西
	$response['user_id']        = $userid;
	$response['user_name']      = $m_space['username'];
	$response['user_nick']      = $m_space['name'] ? $m_space['name'] : $m_space['username'] ;
	$response['user_avatar']    = avatar($userid, 'middle', true);
	$response['user_headpic']   = avatar($userid, 'big', true);
	$response['user_sign']      = preg_replace('/\<img[^>]+>/', '', $m_space['note']) ;

	return $response;
}

?>
