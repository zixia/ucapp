<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

//$req['event_id'] = 2654;

$response = showevent($req);
$response_json = json_encode($response);//生成json数据
die($response_json);



/**
 * @Author:      ruirui
 * @DateTime:    2015-06-04 16:03:43
 * @Description: 展示活动列表信息,展示所有未结束的活动信息
 * @return       $response[] 所有消息的列表
 *               $response['event_id']                   活动id
 *               $response['event_title']        string  活动主题 
 *               $response['event_img']          string  活动图片 tips：仅传送一张图片
 *               $response['event_time']         string  活动时间 format:XX年XX月XX日  XX:XX --XX年XX月XX日  XX:XX
 *               $response['event_spot']         string  活动具体地址
 *               $response['event_people']       string  活动发起人
 *               $response['event_view']         string  此活动已被查看次数
 *               $response['event_participate']  string  活动参与人数
 *               $response['event_attention']    string  活动关注人数
 *               $response['event_type']         string  活动类型
 *               $response['event_ddl']          string  活动截止时间 fromat:XX年XX月XX日 XX:XX
 *               $response['event_maxmun']       string  活动限制人数
 *               $response['event_authen']       string  是否需要审核
 *               $response['event_intro']        string  活动介绍
 */

function showevent($req){
    global $_SGLOBAL;
    $eventid = $req['event_id'];


    $resp = array();
    $resp['b'] = array();
    $resp['h'] = array();

    $view = isset($_GET['view']) ? $_GET['view'] : "all";

    // 活动分类
    if(!@include_once(S_ROOT.'./data/data_eventclass.php')) {
        include_once(S_ROOT.'./source/function_cache.php');
        eventclass_cache();
    }

    if($eventid){// 显示活动内容

        if($view=="me"){//排除由space.php自动添加的$_GET[view]=me
            $view = "all";
        }

        // 活动信息
        $query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$eventid'");
        $event = $_SGLOBAL['db']->fetch_array($query);
        if(! $event){
            showmessage("event_does_not_exist"); // 活动不存在或者已被删除
        }
        if($event['grade'] == 0 && $event['uid'] != $_SGLOBAL['supe_uid'] && !checkperm('manageevent')){
            showmessage('event_under_verify');// 活动正在审核中
        }
        realname_set($event['uid'], $event['username']);
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid='$eventid' AND uid='$_SGLOBAL[supe_uid]'");
        $value = $_SGLOBAL['db']->fetch_array($query);
        if($value){
            $_SGLOBAL['supe_userevent'] = $value;
        } else {
            $_SGLOBAL['supe_userevent'] = array();
        }
        $allowmanage = false; // 活动管理权限
        if($value['status'] >= 3 || checkperm('manageevent')){
            $allowmanage = true;
        }

        // 私密活动，仅已参加活动的人和有管理权限的人或有邀请的人可见
        if($event['public'] == 0 && $_SGLOBAL['supe_userevent']['status'] < 2 && !$allowmanage){
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventinvite")." WHERE eventid = '$eventid' AND touid = '$_SGLOBAL[supe_uid]' LIMIT 1");
            $value = $_SGLOBAL['db']->fetch_array($query);
            if(empty($value)){
                showmessage("event_not_public"); // 这是一个私密活动，需要通过邀请才能查看
            }
        }

        if($view == "thread" && !$event['tagid']) {
            $view = "all";
        }
        // 按查看内容不同，获取不同数据
        if($view == "member"){
            // 查看成员
            $status = isset($_GET['status']) ? intval($_GET['status']) : 2;
            $submenus = array();
            if($status>1){
                $submenus['member']='class="active"';
            }elseif($status>0){
                $submenus['follow']=' class="active"';
            }elseif($status==0){
                $submenus['verify']=' class="active"';
            }

            $statussql = "";
            $orderby = " ORDER BY ue.dateline ASC";
            if($status >= 2){
                $statussql = " AND ue.status >= 2";// 包含组织者
                $orderby = " ORDER BY ue.status DESC";
            } else {
                $statussql = " AND ue.status = '$status'";
            }

            $filter = "";
            if($_GET['key']){
                $_GET['key'] = stripsearchkey($_GET['key']);
                $filter = " AND ue.username LIKE '%$_GET[key]%'";
            }

            $perpage = 10;
            $page = empty($_GET['page'])?1:intval($_GET['page']);
            if($page<1) $page=1;
            $start = ($page-1)*$perpage;

            //检查开始数
            ckstart($start, $perpage);
            $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname("userevent")." ue WHERE ue.eventid = '$eventid' $statussql $filter"),0);
            $members = $fuids = array();
            if($count){
                $query = $_SGLOBAL['db']->query("SELECT ue.*, sf.* FROM ".tname("userevent")." ue LEFT JOIN ".tname("spacefield")." sf ON ue.uid = sf.uid WHERE ue.eventid = '$eventid' $statussql $filter $orderby LIMIT $start, $perpage");
                while($value = $_SGLOBAL['db']->fetch_array($query)){
                    realname_set($value['uid'], $value['username']);
                    $members[] = $value;
                    $fuids[] = $value['uid'];
                }
            }

            //在线状态
            $ols = array();
            if($fuids) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                    if(!$value['magichidden']) {
                        $ols[$value['uid']] = $value['lastactivity'];
                    }
                }
            }

            // 待审核人数
            $verifynum = 0;
            if($_SGLOBAL['supe_userevent']['status'] >= 3){
                if($status == 0){
                    $verifynum = count($members);
                } else {
                    $verifynum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=0"), 0);
                }
            }

            $multi = multi($count, $perpage, $page, "space.php?do=event&id=$eventid&view=member&status=$status");

        } elseif($view == "pic") {

            $picid = isset($_GET['picid']) ? intval($_GET['picid']) : 0;

            // 照片总数
            $piccount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("eventpic")." WHERE eventid = '$eventid'"), 0);

            if ($picid) {

                $_GET['id'] = 0;

                //检索图片
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid='$picid' LIMIT 1");
                $pic = $_SGLOBAL['db']->fetch_array($query);
                realname_set($pic['uid'], $pic['username']);			

                include_once(S_ROOT.'./source/space_album.php');

            } else {
                // 查看活动照片列表
                $photolist = array();

                //分页
                $perpage = 12;
                $page = empty($_GET['page'])?1:intval($_GET['page']);
                if($page<1) $page=1;
                $start = ($page-1)*$perpage;

                //检查开始数
                ckstart($start, $perpage);

                //处理查询
                $theurl = "space.php?do=event&id=$eventid&view=pic";

                $badpicids = array();
                $query = $_SGLOBAL['db']->query("SELECT pic.*, ep.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid=pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid DESC LIMIT $start, $perpage");
                while($value = $_SGLOBAL['db']->fetch_array($query)){
                    if(!$value['filepath']){//照片已经被删除
                        $badpicids[] = $value['picid'];
                        continue;
                    }
                    realname_set($value['uid'], $value['username']);
                    $value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
                    $photolist[] = $value;
                }

                if($badpicids) {
                    $piccount = $piccount - count($badpicids);
                    $_SGLOBAL['db']->query("DELETE FROM ".tname("eventpic")." WHERE eventid='$eventid' AND picid IN (".simplode($badpicids).")");
                }

                if($piccount != $event['picnum']) {//更新数目
                    updatetable("event", array("picnum"=>$piccount),array("eventid"=>$eventid));
                }

                //分页
                $multi = multi($piccount, $perpage, $page, $theurl);
            }

        } elseif($view == "thread") {
            //活动话题
            //分页
            $perpage = 20;
            $page = empty($_GET['page'])?1:intval($_GET['page']);
            if($page<1) $page=1;
            $start = ($page-1)*$perpage;

            //检查开始数
            ckstart($start, $perpage);
            //处理查询
            $theurl = "space.php?do=event&id=$eventid&view=thread";

            $threadlist = array();
            $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." WHERE eventid='$eventid'"),0);
            if($count) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE eventid='$eventid' ORDER BY lastpost DESC LIMIT $start,$perpage");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                    realname_set($value['uid'], $value['username']);
                    $threadlist[] = $value;
                }
            }

            if($count != $event['threadnum']) {
                updatetable("event", array("threadnum"=>$count), array("eventid"=>$eventid));
            }

            //分页
            $multi = multi($count, $perpage, $page, $theurl);

        } elseif($view == "comment") {
            //活动留言
            //分页
            $perpage = 20;
            $page = empty($_GET['page'])?1:intval($_GET['page']);
            if($page<1) $page=1;
            $start = ($page-1)*$perpage;

            //检查开始数
            ckstart($start, $perpage);

            //处理查询
            $theurl = "space.php?do=event&id=$eventid&view=comment";
            $cid = empty($_GET['cid'])?0:intval($_GET['cid']);
            $csql = $cid?"cid='$cid' AND":'';

            $comments = array();
            $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$eventid' AND idtype='eventid'"),0);
            if($count) {
                $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$eventid' AND idtype='eventid' ORDER BY dateline DESC LIMIT $start,$perpage");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                    realname_set($value['authorid'], $value['author']);
                    $comments[] = $value;
                }
            }

            //分页
            $multi = multi($count, $perpage, $page, $theurl, '', 'comment_ul');

        } else {
            // 查看活动综合
            // 处理活动介绍
            include_once(S_ROOT.'./source/function_blog.php');
            $event['detail'] = blog_bbcode($event['detail']);

            // 海报
            if($event['poster']){
                $event['pic'] = pic_get($event['poster'], $event['thumb'], $event['remote'], 0);
            } else {
                $event['pic'] = $_SGLOBAL['eventclass'][$event['classid']]['poster'];
            }

            // 活动组织者
            $relateduids = array();//查找参加此活动的成员也参加的活动用
            $admins = array();
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status IN ('3', '4') ORDER BY status DESC");
            while($value = $_SGLOBAL['db']->fetch_array($query)){
                realname_set($value['uid'], $value['username']);
                $admins[] = $value;
                $relateduids[] = $value['uid'];
            }

            // 活动成员
            $members = array();
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=2 ORDER BY dateline DESC LIMIT 14");
            while($value = $_SGLOBAL['db']->fetch_array($query)){
                realname_set($value['uid'], $value['username']);
                $members[] = $value;
                $relateduids[] = $value['uid'];
            }

            // 感兴趣的
            $follows = array();
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid='$eventid' AND status=1 ORDER BY dateline DESC LIMIT 12");
            while($value = $_SGLOBAL['db']->fetch_array($query)){
                realname_set($value['uid'], $value['username']);
                $follows[] = $value;
            }

            // 待审核人数
            $verifynum = 0;
            if($_SGLOBAL['supe_userevent']['status'] >= 3){
                $verifynum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname("userevent")." WHERE eventid = '$eventid' AND status=0"),0);
            }

            // 参加这个活动的人也参加了那些活动
            $relatedevents = array();
            if($relateduids){
                $query = $_SGLOBAL['db']->query("SELECT e.*, ue.* FROM ".tname("userevent")." ue LEFT JOIN ".tname("event")." e ON ue.eventid=e.eventid WHERE ue.uid IN (".simplode($relateduids).") ORDER BY ue.dateline DESC LIMIT 0,8");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                    $relatedevents[$value['eventid']] = $value;
                }
            }

            // 活动留言，取20条
            $comments = array();
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE id='$eventid' AND idtype='eventid' ORDER BY dateline DESC LIMIT 20");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['authorid'], $value['author']);
                $comments[] = $value;
            }

            // 活动照片
            $photolist = $badpicids = array();
            $query = $_SGLOBAL['db']->query("SELECT pic.*, ep.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid = pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid DESC LIMIT 10");
            while($value = $_SGLOBAL['db']->fetch_array($query)){
                if(!$value['filepath']){//照片已经被删除
                    $badpicids[] = $value['picid'];
                    continue;
                }
                realname_set($value['uid'], $value['username']);
                $value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
                $photolist[] = $value;
            }

            if($badpicids) {
                $_SGLOBAL['db']->query("DELETE FROM ".tname("eventpic")." WHERE eventid='$eventid' AND picid IN (".simplode($badpicids).")");
            }

            //活动话题
            $threadlist = array();
            if($event['tagid']) {
                $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." WHERE eventid='$eventid'"),0);
                if($count) {
                    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE eventid='$eventid' ORDER BY lastpost DESC LIMIT 10");
                    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        realname_set($value['uid'], $value['username']);
                        $threadlist[] = $value;
                    }
                }
            }

            // 活动查看数加 1
            if($event['uid'] != $_SGLOBAL['supe_uid']){
                $_SGLOBAL['db']->query("UPDATE ".tname("event")." SET viewnum=viewnum+1 WHERE eventid='$eventid'");
                $event['viewnum'] += 1;
            }

            //活动开始倒计时
            if($event['starttime'] > $_SGLOBAL['timestamp']) {
                $countdown = intval((mktime(0,0,0,gmdate('m',$event['starttime']),gmdate('d',$event['starttime']),gmdate('Y',$event['starttime'])) -
                            mktime(0,0,0,gmdate('m',$_SGLOBAL['timestamp']),gmdate('d',$_SGLOBAL['timestamp']),gmdate('Y',$_SGLOBAL['timestamp']))) / 86400);
            }
        }


        //相关热点
        $topic = topic_get($event['topicid']);

        realname_get();

        $menu = array($view => ' class="active"');

    }

    $item['event_id']       = $event['eventid'];
    $item['event_title']    = $event['title'];
    //$item['event_img']      = 'http://17salsa.com/home/' . $event['pic'];
    $item['event_img']      = 'http://17salsa.com/home/attachment/' . $event['poster'];
    $item['event_time']     = $event['starttime'];
    $item['event_spot']     = $event['location'];
    $item['event_people']   = $event['username'];
    $item['event_view']     = $event['viewnum'];
    $item['event_participate']  = $event['membernum'];
    $item['event_attention']    = $event['follownum'];

    $item['event_type']     = $event['classid'];
    $item['event_ddl']      = $event['deadline'];
    $item['event_maxmun']   = $event['limitnum'];
    $item['event_authen']   = $event['verify'];
    $item['event_intro']    = $event['detail'];


    $resp['b'] = $item;
    $resp['h']['ret'] = ERR_OK;

    return $resp;
}

/*
    $response = array();
    $response['event_id'] = 1234;
    $response['event_title'] = "【广州古巴莎莎舞俱乐部】6月9日新学期啦！";
    $response['event_img'] = "img/2.jpg";
    $response['event_time'] = "05月30日 21:00 - 06月30日 01:00";
    $response['event_spot'] = "广东 广州 珠江新城马场路16号 富力盈盛广场B座3楼会所 （地铁5号线 潭村D出口直走10m右侧）";
    $response['event_people'] = "左吟";
    $response['event_view'] = 41;
    $response['event_participate'] = 5;
    $response['event_attention'] = 0;
    $response['event_type'] = "舞会/聚会";
    $response['event_ddl'] = "06月29日 21:00";
    $response['event_maxmun'] = "不限";
    $response['event_authen'] = "不需要";
    $response['event_intro'] = "";

*/

?>
