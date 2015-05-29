<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

showhomepage($data);


//实现朋友圈信息展示
function showhomepage($data){
	$userid = $data['user_id'];
	$response = array();

	//根据userid 添加相关内容，返回$response

	//下面是我瞎填的东西
	$item1 = array();
	$item1["publish_time_month"] = "五月";
	$item1["publish_time_date"] = "16";
	$item1["publis_time"] = "2014年12月21日 凌晨1:30";
	$item1["type"] = "pic";//朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item1["content_info"] = "这是一段文字的";
	$item1["content_img"] = array("./img/1.jpg","./img/con2.jpg","./img/con1.jpg");
	$item1["reply_heart"] = array("aa","bb");
	$item1["reply"] = array(array("name"=>"ruirui1","content"=>"qqqq"),
		array("name"=>"ruirui2","content"=>"dddddddd"));

	$item2 = array();
	$item2["publish_time_month"] = "五月";
	$item2["publish_time_date"] = "16";
	$item2["publis_time"] = "2014年12月21日 凌晨1:30";
	$item2["type"] = "word";//朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item2["content_info"] = "这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的这是一段文字的";
	$item2["content_img"] = null;
	$item2["reply_heart"] = array("aa","bb");
	$item2["reply"] = array(array("name"=>"ruirui1","content"=>"qqqq"),
		array("name"=>"ruirui2","content"=>"dddddddd"));

	$item3 = array();
	$item3["publish_time_month"] = "五月";
	$item3["publish_time_date"] = "16";
	$item3["publis_time"] = "2014年12月21日 凌晨1:30";
	$item3["type"] = "pic";//朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item3["content_info"] = "这是一段文字的";
	$item3["content_img"] = array("./img/2.jpg");
	$item3["reply_heart"] = array("aa","bb");
	$item3["reply"] = array(array("name"=>"ruirui1","content"=>"qqqq"),
		array("name"=>"ruirui2","content"=>"dddddddd"));

	$item4 = array();
	$item4["publish_time_month"] = "五月";
	$item4["publish_time_date"] = "16";
	$item4["publis_time"] = "2014年12月21日 凌晨1:30";
	$item4["type"] = "word";//朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item4["content_info"] = "这是一段文字的";
	$item4["content_img"] =null;
	$item4["reply_heart"] = array("aa","bb");
	$item4["reply"] = array(array("name"=>"ruirui1","content"=>"qqqq"),
		array("name"=>"ruirui2","content"=>"dddddddd"));

	$item5 = array();
	$item5["publish_time_month"] = "五月";
	$item5["publish_time_date"] = "16";
	$item5["publis_time"] = "2014年12月21日 凌晨1:30";
	$item5["type"] = "pic";//朋友圈发布的是纯文字内容就是word，是图片加文字或者纯图片就是pic
	$item5["content_info"] = "这是一段文字的";
	$item5["content_img"] = array("./img/1.jpg","./img/1.jpg","./img/1.jpg","./img/1.jpg");
	$item5["reply_heart"] = array("aa","bb");
	$item5["reply"] = array(array("name"=>"ruirui1","content"=>"qqqq"),
		array("name"=>"ruirui2","content"=>"dddddddd"));


	$response = array($item1,$item2,$item3,$item4,$item5);
	

	$response_json = json_encode($response);//生成json数据
	print_r($response_json);
}
?>