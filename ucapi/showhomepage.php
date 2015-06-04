<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = showhomepage($data);
$response_json = json_encode($response);//生成json数据
print_r($response);
die($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:32:21
 * @description: 根据用户id显示个人发布过的状态
 * @para:        $data['
 * @return:      $response							object	所有发布过的信息
 *				 $response[]["publish_time_month"]	array	发布月份 输出大写 如“五月”
 *				 $response[]["publish_time_date"]	array	发布日期 输出两位数字 如“08”
 *				 $response[]["publis_time"]			array	发布时间 format:XXXX年XX月XX日 XX:XX
 *				 $response[]["type"]				array	"pic"消息包含图片
 *															"word"纯文字消息
 *				 $response[]["content_info"]		array	消息文字内容
 *				 $response[]["content_img"]			null	不包含图片 
 *													array	包含图片	图片>100px 
 *				 $response[]["reply_heart"]			array	点赞人昵称
 *				 $response[]["reply"]	      		object
 *				 $response[]["reply"][]['name']		string	评论人昵称
 *				 $response[]["reply"][]['content']	string	评论内容
 */

//实现朋友圈信息展示
function showhomepage($data){
	$userid = $data['user_id'];
	$response = array();

	//下面是我瞎填的东西
	$item1 = array();
	$item1["ts"] = 1234124234;
	$item1["type"] = "img";// txt 朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item1["txt"] = "这是一段文字的";
	$item1["img"] = array("./img/1.jpg","./img/con2.jpg","./img/con1.jpg");
	$item1["like"] = array(1,2);
	$item1["reply"] = array(array(1,"qqqq"),array(2,"xxx"));

	$response = array($item1,$item1);
	
	return $response;
}
?>
