<?php
$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$user_id = $req['user_id'];//发布状态者user_id
$content = $req['content'];//发布状态的内容

if($user_id&&$content){
	$resp['h']['ret'] = 0;
    return $resp;
}

else{
	$resp['h']['ret'] = 1;
    return $resp;
}
?>