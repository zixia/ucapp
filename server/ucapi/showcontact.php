<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

//$data['user_id'] = 1;

$resp = showcontact($data);
//print_r($resp);

$resp_json = json_encode($resp);//生成json数据
print_r($resp_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:07:22
 * @description: 根据用户id展示用户所有联系人信息
 * @para:        $data['user_id'] 用户唯一识别id
 * @return:      $response object 所有联系人的所有信息
 *				 $response[]['contact_img']    	string 	联系人头像
 *														size:30px*30px
 *				 $response[]['contact_name']   	string	联系人昵称
 *				 $response[]['contact_id']	  	string	userid
 *				 $response[]['contact_region']  string	地域
 *				 $response[]['contact_sign']	string	签名
 *				 $response[]['contact_pic']		string	个人主页照片
 *														max_num:4 size:40px*40px
 */

function showcontact($data)
{
    global $_SGLOBAL;
	$userid = $data['user_id'];
	$response = array();
    $response['ret'] = false;

    $space = getspace($userid);

    if(!defined('IN_UCHOME')) {
        exit('Access Denied');
    }

    //分页
    $perpage = 1000;
    $perpage = mob_perpage($perpage);

    $list = $ols = $fuids = array();
    $count = 0;
    $page = empty($_GET['page'])?0:intval($_GET['page']);
    if($page<1) $page = 1;
    $start = ($page-1)*$perpage;

    //检查开始数
    ckstart($start, $perpage);

    //处理查询
    $theurl = "space.php?uid=$space[uid]&do=$do";
    $actives = array('me'=>' class="active"');

    $_GET['view'] = 'me';

    //好友分组
    $wheresql = '';
    if($space['self']) {
        $groups = getfriendgroup();
        $group = !isset($_GET['group'])?'-1':intval($_GET['group']);
        if($group > -1) {
            $wheresql = "AND main.gid='$group'";
            $theurl .= "&group=$group";
        }
    }
    if($_GET['searchkey']) {
        $wheresql = "AND main.fusername='$_GET[searchkey]'";
        $theurl .= "&searchkey=$_GET[searchkey]";
    }

    if($space['friendnum']) {
        if($wheresql) {
            $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('friend')." main WHERE main.uid='$space[uid]' AND main.status='1' $wheresql"), 0);
        } else {
            $count = $space['friendnum'];
        }
        if($count) {
            $query = $_SGLOBAL['db']->query("SELECT s.*, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num
                    FROM ".tname('friend')." main
                    LEFT JOIN ".tname('space')." s ON s.uid=main.fuid
                    LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
                    WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
                    ORDER BY main.num DESC, main.dateline DESC
                    LIMIT $start,$perpage");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
                $value['p'] = rawurlencode($value['resideprovince']);
                $value['c'] = rawurlencode($value['residecity']);
                $value['group'] = $groups[$value['gid']];
                $value['isfriend'] = 1;
                $fuids[] = $value['uid'];
                $value['note'] = getstr($value['note'], 28, 0, 0, 0, 0, -1);
                $list[$value['uid']] = $value;
            }
        }

        //分页
        $multi = multi($count, $perpage, $page, $theurl);
        $friends = array();
        //取100好友用户名
        $query = $_SGLOBAL['db']->query("SELECT f.fusername, s.name, s.namestatus, s.groupid FROM ".tname('friend')." f
                LEFT JOIN ".tname('space')." s ON s.uid=f.fuid
                WHERE f.uid=$_SGLOBAL[supe_uid] AND f.status='1' ORDER BY f.num DESC, f.dateline DESC LIMIT 0,100");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $fusername = ($_SCONFIG['realname'] && $value['name'] && $value['namestatus'])?$value['name']:$value['fusername'];
            $friends[] = addslashes($fusername);
        }
        $friendstr = implode(',', $friends);
    }

    if($space['self']) {
        $groupselect = array($group => ' class="current"');

        //好友个数
        $maxfriendnum = checkperm('maxfriendnum');
        if($maxfriendnum) {
            $maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
        }
    }

    //在线状态
    if($fuids) {
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(!$value['magichidden']) {
                $ols[$value['uid']] = $value['lastactivity'];
            } elseif($list[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
                unset($list[$value['uid']]);
                $count = $count - 1;
            }
        }
    }

    realname_get();

    if(empty($_GET['view']) || $_GET['view'] == 'all') $_GET['view'] = 'me';
    $a_actives = array($_GET['view'].$_GET['type'] => ' class="current"');

    $response = array();

    foreach ( $list as $friend_id => $friend_arr ) {
        $friend = array();

        $friend["contact_name"] = $friend_arr['username'];
        $friend["contact_nick"] = $friend_arr['name'] ? $friend_arr['name'] : $friend_arr['username'] ;
    	$friend["contact_id"] = $friend_id;
    	$friend["contact_region"] =  $friend_arr['resideprovince'] . " " . $friend_arr['residecity'];
    	$friend["contact_sign"] = $friend_arr['spacenote'];
        $friend["contact_sign"] = preg_replace('#<img src="image/#', '<img src="http://17salsa.com/home/image/', $friend['contact_sign']);


	    $friend['contact_img']  = avatar($friend_id,'middle',true);

/*
            [contact_pic] => Array
                (
                    [0] => http://17salsa.com/home/attachment/201503/17/37_1426576018YR6k.jpg.thumb.jpg
                    [1] => http://17salsa.com/home/attachment/201406/4/37_14018954521f1Q.jpg.thumb.jpg
                    [2] => http://17salsa.com/home/attachment/201311/18/37_13847788965gzs.jpg.thumb.jpg
                    [3] => http://17salsa.com/home/attachment/201308/18/37_1376838633Ry7Y.jpg.thumb.jpg
                )

        $space = getspace($friend_id);
        require "inc/space_album.php";
        //print_r($list[0]['pic']);

        $friend['contact_pic'] = array();
        for ( $i=0; $i<4 && isset($list[$i]); $i++ ) {
            $url = 'http://17salsa.com/home/' . $list[$i]['pic'];
    	    array_push( $friend["contact_pic"], $url );
        }
*/

        array_push($response, $friend);
    }

    return $response;
}

?>
