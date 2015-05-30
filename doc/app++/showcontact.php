<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = showcontact($data);
$response_json = json_encode($response);//生成json数据
print_r($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:07:22
 * @description: 根据用户id展示用户所有联系人信息
 * @para:        $data['user_id'] 用户唯一识别id
 * @return:      $response object 所有联系人的所有信息
 *				 $response[]['contact_img']    	string 	联系人头像 	
 *														size:30px*30px
 *				 $response[]['contact_name']   	string	联系人昵称
 *				 $response[]['contact_id']	  	string	userid
 *				 $response[]['contact_region']  string	地域	 
 *				 $response[]['contact_sign']	string	签名
 *				 $response[]['contact_pic']		string	个人主页照片
 *														max_num:4 size:40px*40px
 */


function showcontact($data){
	$userid = $data['user_id'];
	$response = array();

	//下面是我瞎填的东西
	$item1 = array();
	$item1["contact_img"] = "./img/head.jpg";
	$item1["contact_name"] = "紫霞";
	$item1["contact_id"] = "1111";//其实就是user_id
	$item1["contact_region"] = "Beijing";
	$item1["contact_sign"] = "帅帅坏坏哒";
	$item1["contact_pic"] = array("img/1.jpg","img/con1.jpg","img/con2.jpg","img/con4.jpg");

	$item2 = array();
	$item2["contact_img"] = "./img/con1.jpg";
	$item2["contact_name"] = "zixia";
	$item2["contact_id"] = "2222";
	$item2["contact_region"] = "Beijing";
	$item2["contact_sign"] = "帅帅坏坏哒";
	$item2["contact_pic"] = array("img/head.jpg","img/heihei.jpg","img/dance.png","img/1.jpg");

	$item3 = array();
	$item3["contact_img"] = "./img/con2.jpg";
	$item3["contact_name"] = "ruirui";
	$item3["contact_id"] = "3333";
	$item3["contact_region"] = "Beijing";
	$item3["contact_sign"] = "帅帅坏坏哒";
	$item3["contact_pic"] = array("img/1.jpg","img/con1.jpg","img/con2.jpg","img/con4.jpg");

	$item4 = array();
	$item4["contact_img"] = "./img/1.jpg";
	$item4["contact_name"] = "芮芮";
	$item4["contact_id"] = "4444";
	$item4["contact_region"] = "Beijing";
	$item4["contact_sign"] = "帅帅坏坏哒";
	$item4["contact_pic"] = array("img/head.jpg","img/heihei.jpg","img/dance.png","img/1.jpg");

	$response = array($item1,$item2,$item3,$item4);
	
	return $response;
}
?>