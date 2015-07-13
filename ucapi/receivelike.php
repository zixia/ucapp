<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组
$item_id = $req['item_id'];

//$item_id = '22395@blogid';

$resp = receivelike($item_id);
$resp_json = json_encode($resp);//生成json数据

//print_r($resp_json);
die($resp_json);


/**
 * @Author:      name
 * @DateTime:    2015-06-09 15:14:33
 * @Description: 实现接受点赞功能
 * @Para			$resp['h']['r'] = ERR_OK 接受成功并成功入库
 *					$resp['b']
 */


function receivelike($item_id){
    $resp = array();
    $resp['h']['r'] = ERR_UNKNOWN;

    //$item_id是某个朋友圈的消息

    if ( ! preg_match('/^(\d+)@(.+)$/', $item_id, $matches) ) {
        return $resp;
    }

    $id         = $matches[1];
    $idtype     = $matches[2];

    cp_click_flower($id, $idtype);

    //处理成功
    $resp['h']['r'] = ERR_OK;
    return $resp;
 }


function cp_click_flower($id, $idtype)
{
    global $_SGLOBAL;

    $clickid = 0;

    switch ( $idtype ) {
    case 'blogid':  $clickid = 4;   break; // 献花
    case 'tid':     $clickid = 14;  break; // 献花
    case 'picid':   $clickid = 9;   break; // 献花
    default:    // unsupported
        return -1;
    }

    $ac = 'click';
    $op = 'add';

//允许的方法
/*
$acs = array('space', 'doing', 'upload', 'comment', 'blog', 'album', 'relatekw', 'common', 'class',
	'swfupload', 'thread', 'mtag', 'poke', 'friend',
	'avatar', 'profile', 'theme', 'import', 'feed', 'privacy', 'pm', 'share', 'advance', 'invite','sendmail',
	'userapp', 'task', 'credit', 'password', 'domain', 'event', 'poll', 'topic',
	'click','magic', 'top', 'videophoto');
$ac = (empty($_GET['ac']) || !in_array($_GET['ac'], $acs))?'profile':$_GET['ac'];
$op = empty($_GET['op'])?'':$_GET['op'];
*/

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

//信息
switch ($idtype) {
	case 'picid':
		$sql = "SELECT p.*, s.username, a.friend, pf.hotuser FROM ".tname('pic')." p
			LEFT JOIN ".tname('picfield')." pf ON pf.picid=p.picid
			LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid
			LEFT JOIN ".tname('space')." s ON s.uid=p.uid
			WHERE p.picid='$id'";
		$tablename = tname('pic');
		break;
	case 'tid':
		$sql = "SELECT t.*, p.hotuser FROM ".tname('thread')." t
			LEFT JOIN ".tname('post')." p ON p.tid='$id' AND p.isthread='1'
			WHERE t.tid='$id'";
		$tablename = tname('thread');
		break;
	default:
		$idtype = 'blogid';
		$sql = "SELECT b.*, bf.hotuser FROM ".tname('blog')." b
			LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
			WHERE b.blogid='$id'";
		$tablename = tname('blog');
		break;
}
$query = $_SGLOBAL['db']->query($sql);
if(!$item = $_SGLOBAL['db']->fetch_array($query)) {
	showmessage('click_item_error');
}



	if(!checkperm('allowclick') ) {
		showmessage('no_privilege');
	}

	if($item['uid'] == $_SGLOBAL['supe_uid']) {
		showmessage('click_no_self');
	}

	//黑名单
	if(isblacklist($item['uid'])) {
		showmessage('is_blacklist');
	}

	//检查是否点击过了
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')." WHERE uid='$space[uid]' AND id='$id' AND idtype='$idtype'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		// showmessage('click_have');
        return 0;
	}

	//参与
	$setarr = array(
		'uid' => $space['uid'],
		'username' => $_SGLOBAL['supe_username'],
		'id' => $id,
		'idtype' => $idtype,
		'clickid' => $clickid,
		'dateline' => $_SGLOBAL['timestamp']
	);
	inserttable('clickuser', $setarr);

	//更新数量
	$_SGLOBAL['db']->query("UPDATE $tablename SET click_{$clickid}=click_{$clickid}+1 WHERE $idtype='$id'");

	//更新热度
	hot_update($idtype, $id, $item['hotuser']);

	//实名
	realname_set($item['uid'], $item['username']);
	realname_get();

	//动态
	$fs = array();
	switch ($idtype) {
		case 'blogid':
			$fs['title_template'] = cplang('feed_click_blog');
			$fs['title_data'] = array(
				'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
				'subject' => "<a href=\"space.php?uid=$item[uid]&do=blog&id=$item[blogid]\">$item[subject]</a>",
				'click' => $click['name']
			);
			$note_type = 'clickblog';
			$q_note = cplang('note_click_blog', array("space.php?uid=$item[uid]&do=blog&id=$item[blogid]", $item['subject']));
			break;
		case 'tid':
			$fs['title_template'] = cplang('feed_click_thread');
			$fs['title_data'] = array(
				'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
				'subject' => "<a href=\"space.php?uid=$item[uid]&do=thread&id=$item[tid]\">$item[subject]</a>",
				'click' => $click['name']
			);
			$note_type = 'clickthread';
			$q_note = cplang('note_click_thread', array("space.php?uid=$item[uid]&do=thread&id=$item[tid]", $item['subject']));
			break;
		case 'picid':

			$fs['title_template'] = cplang('feed_click_pic');
			$fs['title_data'] = array(
				'touser' => "<a href=\"space.php?uid=$item[uid]\">{$_SN[$item['uid']]}</a>",
				'click' => $click['name']
			);
			$fs['images'] = array(pic_get($item['filepath'], $item['thumb'], $item['remote']));
			$fs['image_links'] = array("space.php?uid=$item[uid]&do=album&picid=$item[picid]");
			$fs['body_general'] = $item['title'];
			$note_type = 'clickpic';
			$q_note = cplang('note_click_pic', array("space.php?uid=$item[uid]&do=album&picid=$item[picid]"));
			break;
	}

	//事件发布
	if(empty($item['friend']) && ckprivacy('click', 1)) {

		feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
	}

	//奖励访客
	getreward('click', 1, 0, $idtype.$id);

	//统计
	updatestat('click');

	//通知
	notification_add($item['uid'], $note_type, $q_note);

//	showmessage('click_success', $_SGLOBAL['refer']);

//include_once(template('cp_click'));
}

?>
