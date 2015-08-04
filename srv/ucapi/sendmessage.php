<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$user_id = intval($req['user_id']);//接收人的id
$txt = $req['txt'];//发送的内容

//$user_id = 59347;
//$txt = 'test api';

$resp = array();
$resp[h][ret] = ERR_UNKNOWN;

if ( 0>=$user_id || 0>=strlen($txt) ) {
    $resp[h][ret] = ERR_PARAM;
}else if ( 0>=$_SGLOBAL['supe_uid'] ) { // need login
    $resp[h][ret] = ERR_NEEDLOGIN;
}else{ // OK
    $resp = sendmessage($user_id,$txt);
}

//print_r($resp);

$resp_json = json_encode($resp);//生成json数据
die($resp_json);


function sendmessage($user_id,$txt)
{
    global $_SGLOBAL;

    $resp = array();
    $resp[h][ret] = ERR_UNKNOWN;

    $_POST[message] = $txt;
    $_GET[touid]    = $user_id;

    $_GET[ac]       = 'pm';
    $_GET[op]       = 'send';
    //$_GET[pmid]     = 78162
    $_GET[daterange]= 1;


//允许的方法
$acs = array('space', 'doing', 'upload', 'comment', 'blog', 'album', 'relatekw', 'common', 'class',
	'swfupload', 'thread', 'mtag', 'poke', 'friend',
	'avatar', 'profile', 'theme', 'import', 'feed', 'privacy', 'pm', 'share', 'advance', 'invite','sendmail',
	'userapp', 'task', 'credit', 'password', 'domain', 'event', 'poll', 'topic',
	'click','magic', 'top', 'videophoto');
$ac = (empty($_GET['ac']) || !in_array($_GET['ac'], $acs))?'profile':$_GET['ac'];
$op = empty($_GET['op'])?'':$_GET['op'];

//权限判断
if(empty($_SGLOBAL['supe_uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		ssetcookie('_refer', rawurlencode('cp.php?ac='.$ac));
	}
	showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
}

//获取空间信息
$space = getspace($_SGLOBAL['supe_uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}

//是否关闭站点
if(!in_array($ac, array('common', 'pm'))) {
	checkclose();
	//空间被锁定
	if($space['flag'] == -1) {
		showmessage('space_has_been_locked');
	}
	//禁止访问
	if(checkperm('banvisit')) {
		ckspacelog();
		showmessage('you_do_not_have_permission_to_visit');
	}
	//验证是否有权限玩应用
	if($ac =='userapp' && !checkperm('allowmyop')) {
		showmessage('no_privilege');
	}
}

//菜单
$actives = array($ac => ' class="active"');

//include_once(S_ROOT.'./source/cp_'.$ac.'.php');


$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
if($uid) {
	$touid = $uid;
} else {
	$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
}
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

include_once S_ROOT.'./uc_client/client.php';


	//ÅÐ¶ÏÊÇ·ñ·¢²¼Ì«¿ì
	$waittime = interval_check('post');
	if($waittime > 0) {
		//showmessage('operating_too_fast','',1,array($waittime));
        $resp[h][ret] = ERR_CALLTOOFAST;
        return $resp;
	}

	//ÐÂÓÃ»§¼ûÏ°
	//cknewuser();

	//ºÚÃûµ¥
	if($touid) {
		if(isblacklist($touid)) {
			showmessage('is_blacklist');
		}
	}


		//·¢ËÍÏûÏ¢
		$username = empty($_POST['username'])?'':$_POST['username'];

		$message = trim($_POST['message']);
		if(empty($message)) {
			showmessage('unable_to_send_air_news');
		}
		$subject = '';

		$return = 0;
		if($touid) {
			//Ö±½Ó¸øÒ»¸öÓÃ»§·¢PM
			$return = uc_pm_send($_SGLOBAL['supe_uid'], $touid, $subject, $message, 1, $pmid, 0);

			//·¢ËÍÓÊ¼þÍ¨Öª
			if($return > 0) {
				smail($touid, '', cplang('friend_pm',array($_SN[$space['uid']], getsiteurl().'space.php?do=pm')), '', 'friend_pm');
			}

		} elseif($username) {
			$newusers = array();
			$users = explode(',', $username);
			foreach ($users as $value) {
				$value = trim($value);
				if($value) {
					$newusers[] = $value;
				}
			}
			if($newusers) {
				$return = uc_pm_send($_SGLOBAL['supe_uid'], implode(',', $newusers), $subject, $message, 1, $pmid, 1);
			}

			//·¢ËÍÓÊ¼þÍ¨Öª
			$touid = 0;
			if($return > 0) {
				$query = $_SGLOBAL['db']->query('SELECT uid FROM '.tname('space').' WHERE username IN ('.simplode($users).')');
				while($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(empty($touid)) $touid = $value['uid'];
					smail($value['uid'], '', cplang('friend_pm',array($_SN[$space['uid']], getsiteurl().'space.php?do=pm')), '', 'friend_pm');
				}
			}
		}

		if($return > 0) {
			//¸üÐÂ×îºó·¢²¼Ê±¼ä
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastpost='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
            // OK!
			//showmessage('do_success', "space.php?do=pm&filter=privatepm");
            $resp[h][ret] = ERR_OK;
		} else {
/*
			if(in_array($return, array(-1,-2,-3,-4))) {
				showmessage('message_can_not_send'.abs($return));
			} else {
				showmessage('message_can_not_send');
			}
*/
            $resp[h][ret] = ERR_UNKNOWN;
		}


//include_once template("cp_pm");


    return $resp;
}


?>
