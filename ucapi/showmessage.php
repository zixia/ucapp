<?php

$req_headers = apache_request_headers();

header("Content-Type:text/html; charset=utf-8");
header('Access-Control-Allow-Origin: ' . $req_headers['Origin'] );
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, withCredentials");
header('Access-Control-Allow-Credentials: true');


include_once('/750/xfs/vhost/17salsa.com/home/common.php');
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./uc_client/client.php');

$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = showmessage1($data);

$response_json = json_encode($response);//生成json数据
die($response_json);


/**

threadInfo[] = getPrivateMessageThread(start,num)

getPrivateMessageThreadDetail(id,start,num)

 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:47:18
 * @description: 展示与所有人联系人的所有消息记录
 * @para:        $data['user_id'] 									用户自己唯一识别id
 * @return:      $response									object	所有联系人的所有消息记录
 * 			     $response[]['message_img']					string	当前联系人的头像 size:70px*70px
 * 			     $response[]['message_user']				string	当前联系人昵称
 * 			     $response[]['message_user_id']				string	当前联系人user_id
 * 			     $response[]['message_publish_time']		string	发送时间 GMT
 * 			     $response[]['message_array']				array	当前联系人发送的所有消息	
 * 			     $response[]['message_array'][]['name']		string	right:消息从联系人方发过来
 *																	left:消息从用户方发出去 
 * 			     $response[]['message_array'][]['content']	string	发送内容	
 */

/*
define('RET_ERR_UNKNOWN', -1);
define('RET_ERR_NOLOGIN', 1);
*/

function showmessage1($data) {
    global $_SGLOBAL;

	$response = array();
    $response['ret'] = false;

    if(!$_SGLOBAL['supe_uid']) { // need login
        return $response;
    }

    $list = array();

    $pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
    $touid = empty($_GET['touid'])?0:intval($_GET['touid']);
    $daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

    if($_GET['subop'] == 'view') {

        if($touid) {
            $list = uc_pm_view($_SGLOBAL['supe_uid'], 0, $touid, $daterange);
            $pmid = empty($list)?0:$list[0]['pmid'];
        } elseif($pmid) {
            $list = uc_pm_view($_SGLOBAL['supe_uid'], $pmid);
        }

        $actives = array($daterange=>' class="active"');

    } elseif($_GET['subop'] == 'ignore') {

        $ignorelist = uc_pm_blackls_get($_SGLOBAL['supe_uid']);
        $actives = array('ignore'=>' class="active"');

    } else {

        $filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'systempm', 'announcepm'))?$_GET['filter']:($space['newpm']?'newpm':'privatepm');

        //分页
        $perpage = 10;
        $perpage = mob_perpage($perpage);

        $page = empty($_GET['page'])?0:intval($_GET['page']);
        if($page<1) $page = 1;

        $result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, 'inbox', $filter, 100);

//die("$_SGLOBAL[supe_uid], $page, $perpage, 'inbox', $filter, 100");
//print_r($result);
        $count = $result['count'];
        $list = $result['data'];

        $multi = multi($count, $perpage, $page, "space.php?do=pm&filter=$filter");

        if($_SGLOBAL['member']['newpm']) {
            //取消新短消息提示
            updatetable('space', array('newpm'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
            //UCenter
            uc_pm_ignore($_SGLOBAL['supe_uid']);
        }

        $actives = array($filter=>' class="active"');
    }

    //实名
    if($list) {
        $today = $_SGLOBAL['timestamp'] - ($_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset'] * 3600) % 86400;
        foreach ($list as $key => $value) {

            realname_set($value['msgfromid'], $value['msgfrom']);

            $value['daterange'] = 5;
            if($value['dateline'] >= $today) {
                $value['daterange'] = 1;
            } elseif($value['dateline'] >= $today - 86400) {
                $value['daterange'] = 2;
            } elseif($value['dateline'] >= $today - 172800) {
                $value['daterange'] = 3;
            }
            $list[$key] = $value;
        }
        realname_get();
    }

    $response = array();

    foreach ( $list as $message ) {
        $msg = array();

    	$msg["message_img"] = "http://17salsa.com/home/template/default/image/logo.gif";
    	$msg["message_user"]    = $message['msgfrom'];
    	$msg["message_user_id"] = $message['msgfromid'];
    	$msg["message_publish_time"] = "上午10点";
        $msg['message_array'] = array();

        $msg_item = array();
	    $msg_item["name"] = "right";
	    $msg_item["content"] = $message['message'];

        array_push($msg['message_array'], $msg_item);

        array_push($response, $msg);
    }

	return $response;
}

?>
