<?php
require_once('inc/config.inc.php');

$response = showhomepage();
$response_json = json_encode($response);//生成json数据
die($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:32:21
 * @description: 根据用户id显示个人发布过的状态
 * @para:        $data['
 * @return:      $response				object	所有发布过的信息
 *				 $response[]["ts"]		array	发布时间戳
 *				 $response[]["type"]	array	'img'代表发布内容包含图片 'txt'代表发布内容为纯文本
 *				 $response[]["txt"]		array	发布内容
 *				 $response[]["img"]		array	返回null不包含图片 array 图片>100px 
 *														
 *				 $response[]["like"]
 *				 $response[]["reply"]
 */



//实现朋友圈信息展示
function showhomepage(){
	$response = array();

	//下面是我瞎填的东西
	$item1 = array();
	$item1["ts"] = 1234124234;
	$item1["type"] = "img";// txt 朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item1["txt"] = "这是一段文字的";
	$item1["img"] = array("./img/1.jpg","./img/con2.jpg","./img/con1.jpg");//不包含图片返回null 图片>100px
	$item1["like"] = array(1,2);
	$item1["reply"] = array(array(1,"qqqq"),array(2,"xxx"));

	$response = array($item1,$item1);
	
	return $response;
}
?>
