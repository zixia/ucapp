<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

// $user_id = $req['u_id'];
$page = $req[page];

//$page=5;
$resp = getMessageThreads($page);
//print_r($resp);

$resp_json = json_encode($resp);//生成json数据
die($resp_json);


/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:47:18
 * @description: 展示与所有人联系人的所有消息记录
 * @para:        $data['user_id']                                     用户自己唯一识别id
 * @return:      $response                                    object    所有联系人的所有消息记录
 *                  $response[]['message_img']                    string    当前联系人的头像 size:70px*70px
 *                  $response[]['message_user']                string    当前联系人昵称
 *                  $response[]['message_user_id']                string    当前联系人user_id
 *                  $response[]['message_publish_time']        string    发送时间 GMT
 *                  $response[]['message_array']                array    当前联系人发送的所有消息
 *                  $response[]['message_array'][]['name']        string    right:消息从联系人方发过来
 *                                                                    left:消息从用户方发出去
 *                  $response[]['message_array'][]['content']    string    发送内容
 */

// 废弃函数
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



<?php

function getMessageThreads($page)
{
    global $_SGLOBAL, $_SCONFIG;

    $page = intval($page);

    $_GET[rewrite] = 'pm-filter-privatepm-page-1'; // page number start from 1

    if ( 0<$page )
        $_GET[rewrite] = 'pm-filter-privatepm-page-' . $page ;

    $resp = array();
    $resp['h'] = array();
    $resp['b'] = array();
    $resp['h']['ret'] = ERR_UNKNOWN;

    if(!$_SGLOBAL['supe_uid']) { // need login
        $resp['h']['ret'] = ERR_NEEDLOGIN;
        return $resp;
    }


//是否关闭站点
checkclose();


//处理rewrite
if($_SCONFIG['allowrewrite'] && isset($_GET['rewrite'])) {
    $rws = explode('-', $_GET['rewrite']);
    if($rw_uid = intval($rws[0])) {
        $_GET['uid'] = $rw_uid;
    } else {
        $_GET['do'] = $rws[0];
    }
    if(isset($rws[1])) {
        $rw_count = count($rws);
        for ($rw_i=1; $rw_i<$rw_count; $rw_i=$rw_i+2) {
            $_GET[$rws[$rw_i]] = empty($rws[$rw_i+1])?'':$rws[$rw_i+1];
        }
    }
    unset($_GET['rewrite']);
}

//允许动作
$dos = array('feed', 'doing', 'mood', 'blog', 'album', 'thread', 'mtag', 'friend', 'wall', 'tag', 'notice', 'share', 'topic', 'home', 'pm', 'event', 'poll', 'top', 'info', 'videophoto');

//获取变量
$isinvite = 0;
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$username = empty($_GET['username'])?'':$_GET['username'];
$domain = empty($_GET['domain'])?'':$_GET['domain'];
$do = (!empty($_GET['do']) && in_array($_GET['do'], $dos))?$_GET['do']:'index';

if($do == 'home') {
    $do = 'feed';
} elseif ($do == 'index') {
    //邀请好友
    $invite = empty($_GET['invite'])?'':$_GET['invite'];
    $code = empty($_GET['code'])?'':$_GET['code'];
    $reward = getreward('invitecode', 0);
    if($code && !$reward['credit']) {
        $isinvite = -1;
    } elseif($invite) {
        $isinvite = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT id FROM ".tname('invite')." WHERE uid='$uid' AND code='$invite' AND fuid='0'"), 0);
    }
}

//是否公开
if(empty($isinvite) && empty($_SCONFIG['networkpublic'])) {
    checklogin();//需要登录
}

//获取空间
if($uid) {
    $space = getspace($uid, 'uid');
} elseif ($username) {
    $space = getspace($username, 'username');
} elseif ($domain) {
    $space = getspace($domain, 'domain');
} elseif ($_SGLOBAL['supe_uid']) {
    $space = getspace($_SGLOBAL['supe_uid'], 'uid');
}

if($space) {

    //验证空间是否被锁定
    if($space['flag'] == -1) {
        showmessage('space_has_been_locked');
    }

    //隐私检查
    if(empty($isinvite) || ($isinvite<0 && $code != space_key($space, $_GET['app']))) {
        //游客
        if(empty($_SCONFIG['networkpublic'])) {
            checklogin();//需要登录
        }
        if(!ckprivacy($do)) {
            include template('space_privacy');
            exit();
        }
    }

    //别人只查看自己
    if(!$space['self']) {
        $_GET['view'] = 'me';
    } else if(empty($space['feedfriend']) && empty($_GET['view'])) {
        $_GET['view'] = 'all';
    }
    if ($_GET['view'] == 'me') {
        $space['feedfriend'] = '';
    }

} elseif($uid) {

    //判断当前用户是否删除
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$uid' AND flag='-1'");
    if($value = $_SGLOBAL['db']->fetch_array($query)) {
        showmessage('the_space_has_been_closed');
    }

    //未开通空间
    include_once(S_ROOT.'./uc_client/client.php');
    if($user = uc_get_user($uid, 1)) {
        $space = array('uid' => $user[0], 'username' => $user[1], 'dateline'=>$_SGLOBAL['timestamp'], 'friends'=>array());
        $_SN[$space['uid']] = $space['username'];
    }
}

//游客
if(empty($space)) {
    $space = array('uid'=>0, 'username'=>'guest', 'self'=>1);
    if($do == 'index') $do = 'feed';
}

//更新活动session
if($_SGLOBAL['supe_uid']) {

    getmember(); //获取当前用户信息

    if($_SGLOBAL['member']['flag'] == -1) {
        showmessage('space_has_been_locked');
    }

    //禁止访问
    if(checkperm('banvisit')) {
        ckspacelog();
        showmessage('you_do_not_have_permission_to_visit');
    }

    updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
}

//计划任务
if(!empty($_SCONFIG['cronnextrun']) && $_SCONFIG['cronnextrun'] <= $_SGLOBAL['timestamp']) {
    include_once S_ROOT.'./source/function_cron.php';
    runcron();
}

//
//include_once(S_ROOT."./source/space_{$do}.php");
//

include_once(S_ROOT.'./uc_client/client.php');

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

//print_r($list);
//include_onca template("space_pm");

    foreach ( $list as $message ) {
        $msg = array();

        $msg[id]    = $message[pmid];
        $msg[fid]   = $message[msgfromid];
        $msg[tid]   = $message[msgtoid];
        $msg[ts]    = $message[dateline];
        $msg['new'] = $message['new'];

        $msg[txt]   = $message[message];

        array_push($resp['b'], $msg);
    }

    $resp['h']['ret'] = ERR_OK;
    return $resp;
}

?>
