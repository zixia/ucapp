<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

showcontact($data);


//实现联系人信息展示
function showcontact($data){
	$userid = $data['user_id'];
	$response = array();

	//根据userid 添加相关内容，返回$response

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
	

	$response_json = json_encode($response);//生成json数据
	print_r($response_json);
}
?>