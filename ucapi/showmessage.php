<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$resp = showmessage1($req);

$resp_json = json_encode($resp);//生成json数据
die($resp_json);


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


function showmessage1($data) {
    global $_SGLOBAL;

	$resp = array();
	$resp['h'] = array();
	$resp['b'] = array();
    $resp['h']['ret'] = ERR_UNKNOWN;

    if(!$_SGLOBAL['supe_uid']) { // need login
        $resp['h']['ret'] = ERR_NEEDLOGIN;
        return $resp;
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
        $perpage = 25;
        $perpage = mob_perpage($perpage);

        $page = empty($_GET['page'])?0:intval($_GET['page']);
        if($page<1) $page = 1;

        $result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, 'inbox', $filter, 4096);

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

    foreach ( $list as $message ) {
        $msg = array();

    	$msg["message_img"]     = avatar($message['msgfromid'],'small',true);
    	$msg["message_user"]    = $message['msgfrom'];
    	$msg["message_user_id"] = $message['msgfromid'];
    	$msg["message_publish_time"] = "上午10点";
        $msg['message_array'] = array();

        $msg_item = array();
	    $msg_item["name"] = "right";
	    $msg_item["content"] = $message['message'];

        array_push($msg['message_array'], $msg_item);

        array_push($resp['b'], $msg);
    }

    $resp['h']['ret'] = ERR_OK;
	return $resp;
}

?>
