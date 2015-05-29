<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

showmessage($data);


//实现发送信息展示
function showmessage($data){
	$userid = $data['user_id'];
	$response = array();

	//根据userid 添加相关内容，返回$response

	//下面是我瞎填的东西

	$item1 = array();
	$item1["message_img"] = "./img/head.jpg";
	$item1["message_user"] = "紫霞";
	$item1["message_user_id"] = "1111";
	$item1["message_publish_time"] = "上午10点";
	//right代表信息从外面发过来，left代表信息从自己这里发出去
	$messageitem1["name"] = "right";
	$messageitem1["content"] = "我来啦";
	$messageitem2["name"] = "right";
	$messageitem2["content"] = "好呀好呀";
	$messageitem3["name"] = "left";
	$messageitem3["content"] = "真的么";
	$messageitem4["name"] = "right";
	$messageitem4["content"] = "快点快点把代码调通";
	$item1["message_array"] = array($messageitem1,$messageitem2,$messageitem3,$messageitem4);


	$item2 = array();
	$item2["message_img"] = "./img/con1.jpg";
	$item2["message_user"] = "zixia";
	$item2["message_user_id"] = "2222";
	$item2["message_publish_time"] = "上午10点";
	//right代表信息从外面发过来，left代表信息从自己这里发出去
	$messageitem1["name"] = "left";
	$messageitem1["content"] = "qqqqq";
	$messageitem2["name"] = "right";
	$messageitem2["content"] = "ddddddddd";
	$messageitem3["name"] = "left";
	$messageitem3["content"] = "aaaaaa";
	$messageitem4["name"] = "right";
	$messageitem4["content"] = "vvvvvvvvv";
	$item2["message_array"] = array($messageitem1,$messageitem2,$messageitem3,$messageitem4);


	$response = array($item1,$item2);
	

	$response_json = json_encode($response);//生成json数据
	print_r($response_json);
}
?>