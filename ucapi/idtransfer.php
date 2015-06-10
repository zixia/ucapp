<?php
require_once('inc/config.inc.php');
$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$req['idlist'] = array ( 1,2,3 );

$resp = idtransfer($req);
$resp_json = json_encode($resp);//生成json数据
die($resp_json);




/**
 * @Author:      ruirui
 * @DateTime:    2015-06-05 18:05:44
 * @Description: 发送id数组，返回id和相关信息数组
 *				$response[]['id'] 用户id
 *				$response[]['content']['username'] 用户昵称
 *				$response[]['content']['avatar'] 用户头像
 */


//实现朋友圈信息展示
function idtransfer($req){
    global $_SGLOBAL;

	$idlist = $req['idlist'];

	$resp['b'] = array();
    $resp['h'] = array();

    $result = array();

    foreach ( $idlist as $uid ) {
        $m_space = getspace($uid);

	    $result[$uid] = array(
                username    => $m_space['name'],
                avatar      => avatar($uid,'small',true)
            );
    }

    $resp['b'] = $result;
    $resp['h']['ret'] = ERR_OK;

	return $resp;
}
?>
