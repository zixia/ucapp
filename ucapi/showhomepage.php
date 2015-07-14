<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组

$uid = intval( $req['user_id'] );

//$uid = 0;

if ( $uid > 0 ) {
    /*
     * 1、返回 uid 的个人动态
     */
    $resp = getUserFeeds($uid);
}else if ( $uid < 0 ) {
    /*
     * 2、返回全站动态
     */
    $resp  = getAllFeeds();
}else{ // uid 未设置
    /*
     * 3、返回当前登录用户的朋友圈
     */
    if ( $_SGLOBAL['supe_uid'] ) {
        // 登录用户
        $resp  = getFriendFeeds($_SGLOBAL['supe_uid']);
    }else{
        // 返回错误
        $resp = array ();
        $resp['h']['ret'] = ERR_NEEDLOGIN;
    }
}

//print_r($resp);
$resp[h][uid] = $uid;
$resp_json = json_encode($resp);//生成json数据
die($resp_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 11:32:21
 * @description: 根据用户id显示个人发布过的状态
 * @para:        $data['']              如果是null，则是朋友圈信息，如果有id展示某个用户的homepage
 * @return:      $response				object	所有发布过的信息
 *               $response[]["p"]      array   发布人
 *               $response[]["p"][0]      int   发布人id
 *               $response[]["p"][1]      string   发布人用户名
 *               $response[]["p"][2]      string   发布人头像
 *				 $response[]["ts"]		array	发布时间戳
 *				 $response[]["type"]	array	'img'代表发布内容包含图片 'txt'代表发布内容为纯文本
 *				 $response[]["txt"]		array	发布内容
 *				 $response[]["img"]		array	返回null不包含图片 array 图片>100px
 *
 *				 $response[]["like"]
 *				 $response[]["reply"]
 */



//实现朋友圈信息展示
function getAllFeeds(){
    global $_SGLOBAL;

    $resp = array();
    $resp['h']['ret'] = ERR_UNKNOWN;


    //允许动作
    $dos = array('feed', 'doing', 'mood', 'blog', 'album', 'thread', 'mtag', 'friend', 'wall', 'tag', 'notice', 'share', 'topic', 'home', 'pm', 'event', 'poll', 'top', 'info', 'videophoto');

    //获取变量
    $isinvite = 0;


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

    //处理
    //include_once(S_ROOT."./source/space_feed_json.php");



    //显示全站动态的好友数
    if(empty($_SCONFIG['showallfriendnum']) || $_SCONFIG['showallfriendnum']<1) $_SCONFIG['showallfriendnum'] = 10;
    //默认热点天数
    if(empty($_SCONFIG['feedhotday'])) $_SCONFIG['feedhotday'] = 2;

    //网站近况
    $isnewer = $space['friendnum']<$_SCONFIG['showallfriendnum']?1:0;
    if(empty($_GET['view']) && $space['self'] && $isnewer) {
        $_GET['view'] = 'all';//默认显示
    }

    //分页
    $perpage = $_SCONFIG['feedmaxnum']<50?50:$_SCONFIG['feedmaxnum'];
    $perpage = mob_perpage($perpage);

    if($_GET['view'] == 'hot') {
        $perpage = 50;
    }

    $start = empty($_GET['start'])?0:intval($_GET['start']);
    //检查开始数
    $perpage = 200;
    ckstart($start, $perpage);

    //今天时间开始线
    $_SGLOBAL['today'] = sstrtotime(sgmdate('Y-m-d'));

    //最少热度
    $minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];
    $_SGLOBAL['gift_appid'] = '1027468';

    if($_GET['view'] == 'all') {

        $wheresql = "1";//没有隐私
        $ordersql = "dateline DESC";
        $theurl = "space.php?uid=$space[uid]&do=$do&view=all";
        $f_index = '';

    } elseif($_GET['view'] == 'hot') {

        $wheresql = "hot>='$minhot'";
        $ordersql = "dateline DESC";
        $theurl = "space.php?uid=$space[uid]&do=$do&view=hot";
        $f_index = '';

    } else {

        if(empty($space['feedfriend'])) $_GET['view'] = 'me';

        if( $_GET['view'] == 'me') {
            $wheresql = "uid='$space[uid]'";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=me";
            $f_index = '';

        } else {
            $wheresql = "uid IN ('0',$space[feedfriend])";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=we";
            $f_index = 'USE INDEX(dateline)';
            $_GET['view'] = 'we';
            //不显示时间
            $_TPL['hidden_time'] = 1;
        }
    }

    //过滤
    $appid = empty($_GET['appid'])?0:intval($_GET['appid']);
    if($appid) {
        $wheresql .= " AND appid='$appid'";
    }
    $icon = empty($_GET['icon'])?'':trim($_GET['icon']);
    if($icon) {
        $wheresql .= " AND icon='$icon'";
    }
    $filter = empty($_GET['filter'])?'':trim($_GET['filter']);
    if($filter == 'site') {
        $wheresql .= " AND appid>0";
    } elseif($filter == 'myapp') {
        $wheresql .= " AND appid='0'";
    }

    $feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
    $count = $filtercount = 0;
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $f_index
            WHERE $wheresql
            ORDER BY $ordersql
            LIMIT $start,$perpage");

    if($_GET['view'] == 'me' || $_GET['view'] == 'hot') {
        //个人动态
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                realname_set($value['uid'], $value['username']);
                $feed_list[] = $value;
            }
            $count++;
        }
    } else {
        //要折叠的动态
        $hidden_icons = array();
        if($_SCONFIG['feedhiddenicon']) {
            $_SCONFIG['feedhiddenicon'] = str_replace(' ', '', $_SCONFIG['feedhiddenicon']);
            $hidden_icons = explode(',', $_SCONFIG['feedhiddenicon']);
        }
        $space['filter_icon'] = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(empty($feed_list[$value['hash_data']][$value['uid']])) {
                if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    if(ckicon_uid($value)) {
                        $ismyapp = is_numeric($value['icon'])?1:0;
                        if((($ismyapp && in_array('myop', $hidden_icons)) || in_array($value['icon'], $hidden_icons)) && !empty($icon_num[$value['icon']])) {
                            $hiddenfeed_num[$value['icon']]++;
                            $hiddenfeed_list[$value['icon']][] = $value;
                        } else {
                            if($ismyapp) {
                                $appfeed_list[$value['hash_data']][$value['uid']] = $value;
                            } else {
                                $feed_list[$value['hash_data']][$value['uid']] = $value;
                            }
                        }
                        $icon_num[$value['icon']]++;
                    } else {
                        $filtercount++;
                        $filter_list[] = $value;
                    }
                }
            }
            $count++;
        }
    }

    $olfriendlist = $visitorlist = $task = $ols = $birthlist = $myapp = $hotlist = $guidelist = array();
    $oluids = array();
    $topiclist = array();
    $newspacelist = array();

    if($space['self'] && empty($start)) {

        //短消息
        $space['pmnum'] = $_SGLOBAL['member']['newpm'];

        //举报管理
        if(checkperm('managereport')) {
            $space['reportnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('report')." WHERE new='1'"), 0);
        }

        //审核活动
        if(checkperm('manageevent')) {
            $space['eventverifynum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('event')." WHERE grade='0'"), 0);
        }

        //等待实名认证
        if($_SCONFIG['realname'] && checkperm('managename')) {
            $space['namestatusnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')." WHERE namestatus='0' AND name!=''"), 0);
        }

        //欢迎新成员
        if($_SCONFIG['newspacenum']>0) {
            $newspacelist = unserialize(data_get('newspacelist'));
            if(!is_array($newspacelist)) $newspacelist = array();
            foreach ($newspacelist as $value) {
                $oluids[] = $value['uid'];
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
            }
        }

        //最近访客列表
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,12");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            realname_set($value['vuid'], $value['vusername']);
            $visitorlist[$value['vuid']] = $value;
            $oluids[] = $value['vuid'];
        }

        //访客在线
        if($oluids) {
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($oluids).")");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    $ols[$value['uid']] = 1;
                } elseif ($visitorlist[$value['uid']]) {
                    unset($visitorlist[$value['uid']]);
                }
            }
        }

        $oluids = array();
        $olfcount = 0;
        if($space['feedfriend']) {
            //在线好友
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN ($space[feedfriend]) ORDER BY lastactivity DESC LIMIT 0,15");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $ols[$value['uid']] = 1;
                    $oluids[$value['uid']] = $value['uid'];
                    $olfcount++;
                }
            }
        }
        if($olfcount < 15) {
            //我的好友
            $query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username, num FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,30");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(empty($oluids[$value['uid']])) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $olfcount++;
                    if($olfcount == 15) break;
                }
            }
        }

        //获取任务
        include_once(S_ROOT.'./source/function_space.php');
        $task = gettask();

        //好友生日
        if($space['feedfriend']) {
            list($s_month, $s_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']-3600*24*3));//过期3天
            list($n_month, $n_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']));
            list($e_month, $e_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']+3600*24*7));
            if($e_month == $s_month) {
                $wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
            } else {
                $wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
            }
            $query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus,s.groupid,sf.birthyear,sf.birthmonth,sf.birthday
                    FROM ".tname('spacefield')." sf
                    LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
                    WHERE (sf.uid IN ($space[feedfriend])) AND ($wheresql)");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
                $value['istoday'] = 0;
                if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
                    $value['istoday'] = 1;
                }
                $key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
                $birthlist[$key][] = $value;
                ksort($birthlist);
            }
        }

        //积分
        $space['star'] = getstar($space['experience']);

        //域名
        $space['domainurl'] = space_domain($space);

        //热点
        if($_SCONFIG['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
            $hotlist_all = array();
            $hotstarttime = $_SGLOBAL['timestamp'] - $_SCONFIG['feedhotday']*3600*24;
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." USE INDEX(hot) WHERE dateline>='$hotstarttime' ORDER BY hot DESC LIMIT 0,10");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    if(empty($hotlist)) {
                        $hotlist[$value['feedid']] = $value;
                    } else {
                        $hotlist_all[$value['feedid']] = $value;
                    }
                }
            }
            $nexthotnum = $_SCONFIG['feedhotnum'] - 1;
            if($nexthotnum > 0) {
                if(count($hotlist_all)> $nexthotnum) {
                    $hotlist_key = array_rand($hotlist_all, $nexthotnum);
                    if($nexthotnum == 1) {
                        $hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
                    } else {
                        foreach ($hotlist_key as $key) {
                            $hotlist[$key] = $hotlist_all[$key];
                        }
                    }
                } else {
                    $hotlist = $hotlist_all;
                }
            }
        }

        //热闹
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY lastpost DESC LIMIT 0,1");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['pic'] = $value['pic']?pic_get($value['pic'], $value['thumb'], $value['remote']):'';
            $topiclist[] = $value;
        }


        //提醒总数
        $space['allnum'] = 0;
        foreach (array('notenum', 'addfriendnum', 'mtaginvitenum', 'eventinvitenum', 'myinvitenum', 'pokenum', 'reportnum', 'namestatusnum', 'eventverifynum') as $value) {
            $space['allnum'] = $space['allnum'] + $space[$value];
        }
    }

    //实名处理
    realname_get();

    //feed合并
    $list = array();

    if($_GET['view'] == 'hot') {
        //热点
        foreach ($feed_list as $value) {
            $value = mkfeed($value);
            $list['today'][] = $value;
        }
    } elseif($_GET['view'] == 'me') {
        //个人
        foreach ($feed_list as $value) {
            if($hotlist[$value['feedid']]) continue;
            $value = mkfeed($value);
            if($value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $value;
            } elseif ($value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $value;
            } else {
                $theday = sgmdate('Y-m-d', $value['dateline']);
                $list[$theday][] = $value;
            }
        }
    } else {
        //好友、全站
        foreach ($feed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            if($hotlist[$a_value['feedid']]) continue;
            $a_value = mkfeed($a_value, $actors);
            if($a_value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $a_value;
            } elseif ($a_value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $a_value;
            } else {
                $theday = sgmdate('Y-m-d', $a_value['dateline']);
                $list[$theday][] = $a_value;
            }
        }
        //应用
        foreach ($appfeed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            $a_value = mkfeed($a_value, $actors);
            $list['app'][] = $a_value;
        }
    }

    //获得个性模板
    $templates = $default_template = array();
    $tpl_dir = sreaddir(S_ROOT.'./template');
    foreach ($tpl_dir as $dir) {
        if(file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
            $tplicon = file_exists(S_ROOT.'./template/'.$dir.'/image/template.gif')?'template/'.$dir.'/image/template.gif':'image/tlpicon.gif';
            $tplvalue = array('name'=> $dir, 'icon'=>$tplicon);
            if($dir == $_SCONFIG['template']) {
                $default_template = $tplvalue;
            } else {
                $templates[$dir] = $tplvalue;
            }
        }
    }
    $_TPL['templates'] = $templates;
    $_TPL['default_template'] = $default_template;

    //标签激活
    $my_actives = array(in_array($_GET['filter'], array('site','myapp'))?$_GET['filter']:'all' => ' class="active"');
    $actives = array(in_array($_GET['view'], array('me','all','hot'))?$_GET['view']:'we' => ' class="active"');

    //if(empty($cp_mode)) include_once template("space_feed_json");


    $feeds = array();

    $circle_filter = array ( 'album', 'blog', 'thread', 'event', 'poll', 'doing' );

    foreach( $list as $day=>$values ){
        foreach( $values as $value ){
            if ( ! in_array( $value['icon'], $circle_filter ) ) {
                //echo "nexted $value[icon]\n";
                continue;
            }


            //print_r($value);
            $feed = array();

            $feed['txt'] = isset($value['body_template']) ? $value['body_template'] : $value['title_template'] ;
            $feed['txt'] = strip_tags ( html_entity_decode( $feed['txt'], ENT_QUOTES, "utf-8" ) );
            if ( empty($feed['txt']) ) continue;

            $feed["p"] = array($value['uid'],$value['username'],avatar($value['uid'],'middle',true) );


            $feed["ts"] = $value['dateline'];

            if ( 'doing'==$value['icon'] ) {
                $feed["type"] = 'txt';
            }else{
                $feed["type"] = 'img';
            }

            $feed["like"] = array(1,2);
            $feed["reply"] = array(array(1,"qqqq"),array(2,"xxx"));

            $feed["img"] = array();
            for ( $n=1; $n<=9; $n++ ) {
                if ( 0==strlen($value["image_$n"]) ) break;
                array_push( $feed['img'], "http://17salsa.com/home/" . $value["image_$n"]  );
            }

            if ( empty($feed['img']) ) {
                $feed['type'] = 'txt';
                $feed['img'] = null;
            }


            /*
               print_r($value);
               print_r($feed);
             */


            /*
               $feed['avatar_img']     = avatar($value['uid'], 'small', true);
               $feed['avatar_name']    = $value['username'];
               $feed['publish_time']   = gmdate("Y-m-d\TH:i:s\Z", $value['dateline']);
             */
            array_push($feeds, $feed);

            //var_dump($value);
        }
    }

    $resp['b'] = $feeds;
    $resp['h']['ret'] = ERR_OK;

    return $resp;
}

//筛选
function ckicon_uid($feed) {
    global $_SGLOBAL, $space, $_SCONFIG;

    if($space['filter_icon']) {
        $key = $feed['icon'].'|0';
        if(in_array($key, $space['filter_icon'])) {
            return false;
        } else {
            $key = $feed['icon'].'|'.$feed['uid'];
            if(in_array($key, $space['filter_icon'])) {
                return false;
            }
        }
    }
    return true;
}


function getUserFeeds($uid) {
    global $_SGLOBAL;

    $space = getspace($uid, 'uid');


    $_GET['view'] = 'me';

    //显示全站动态的好友数
    if(empty($_SCONFIG['showallfriendnum']) || $_SCONFIG['showallfriendnum']<1) $_SCONFIG['showallfriendnum'] = 10;
    //默认热点天数
    if(empty($_SCONFIG['feedhotday'])) $_SCONFIG['feedhotday'] = 2;

    //网站近况
    $isnewer = $space['friendnum']<$_SCONFIG['showallfriendnum']?1:0;
    if(empty($_GET['view']) && $space['self'] && $isnewer) {
        $_GET['view'] = 'all';//默认显示
    }

    //分页
    $perpage = $_SCONFIG['feedmaxnum']<50?50:$_SCONFIG['feedmaxnum'];
    $perpage = mob_perpage($perpage);

    if($_GET['view'] == 'hot') {
        $perpage = 50;
    }

    $start = empty($_GET['start'])?0:intval($_GET['start']);
    //检查开始数
    ckstart($start, $perpage);

    //今天时间开始线
    $_SGLOBAL['today'] = sstrtotime(sgmdate('Y-m-d'));

    //最少热度
    $minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];
    $_SGLOBAL['gift_appid'] = '1027468';

    {

        if(empty($space['feedfriend'])) $_GET['view'] = 'me';

        if( $_GET['view'] == 'me') {
            $wheresql = "uid='$space[uid]'";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=me";
            $f_index = '';

        } else {
            $wheresql = "uid IN ('0',$space[feedfriend])";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=we";
            $f_index = 'USE INDEX(dateline)';
            $_GET['view'] = 'we';
            //不显示时间
            $_TPL['hidden_time'] = 1;
        }
    }

    //过滤
    $appid = empty($_GET['appid'])?0:intval($_GET['appid']);
    if($appid) {
        $wheresql .= " AND appid='$appid'";
    }
    $icon = empty($_GET['icon'])?'':trim($_GET['icon']);
    if($icon) {
        $wheresql .= " AND icon='$icon'";
    }
    $filter = empty($_GET['filter'])?'':trim($_GET['filter']);
    if($filter == 'site') {
        $wheresql .= " AND appid>0";
    } elseif($filter == 'myapp') {
        $wheresql .= " AND appid='0'";
    }

    $feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
    $count = $filtercount = 0;
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $f_index
            WHERE $wheresql
            ORDER BY $ordersql
            LIMIT $start,$perpage");

    if($_GET['view'] == 'me' || $_GET['view'] == 'hot') {
        //个人动态
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                realname_set($value['uid'], $value['username']);
                $feed_list[] = $value;
            }
            $count++;
        }
    } else {
        //要折叠的动态
        $hidden_icons = array();
        if($_SCONFIG['feedhiddenicon']) {
            $_SCONFIG['feedhiddenicon'] = str_replace(' ', '', $_SCONFIG['feedhiddenicon']);
            $hidden_icons = explode(',', $_SCONFIG['feedhiddenicon']);
        }
        $space['filter_icon'] = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(empty($feed_list[$value['hash_data']][$value['uid']])) {
                if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    //if(ckicon_uid($value)) {
                    $ismyapp = is_numeric($value['icon'])?1:0;
                    if($_SCONFIG['my_showgift'] && $value['icon'] == $_SGLOBAL['gift_appid']) $ismyapp = 0;
                    if((($ismyapp && in_array('myop', $hidden_icons)) || in_array($value['icon'], $hidden_icons)) && !empty($icon_num[$value['icon']])) {
                        $hiddenfeed_num[$value['icon']]++;
                        $hiddenfeed_list[$value['icon']][] = $value;
                    } else {
                        if($ismyapp) {
                            $appfeed_list[$value['hash_data']][$value['uid']] = $value;
                        } else {
                            $feed_list[$value['hash_data']][$value['uid']] = $value;
                        }
                    }
                    $icon_num[$value['icon']]++;
                    //} else {
                    //	$filtercount++;
                    //	$filter_list[] = $value;
                    //}
                }
            }
            $count++;
        }
    }

    $olfriendlist = $visitorlist = $task = $ols = $birthlist = $myapp = $hotlist = $guidelist = array();
    $oluids = array();
    $topiclist = array();
    $newspacelist = array();

    if($space['self'] && empty($start)) {

        //短消息
        $space['pmnum'] = $_SGLOBAL['member']['newpm'];

        //举报管理
        if(checkperm('managereport')) {
            $space['reportnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('report')." WHERE new='1'"), 0);
        }

        //审核活动
        if(checkperm('manageevent')) {
            $space['eventverifynum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('event')." WHERE grade='0'"), 0);
        }

        //等待实名认证
        if($_SCONFIG['realname'] && checkperm('managename')) {
            $space['namestatusnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')." WHERE namestatus='0' AND name!=''"), 0);
        }

        //欢迎新成员
        if($_SCONFIG['newspacenum']>0) {
            $newspacelist = unserialize(data_get('newspacelist'));
            if(!is_array($newspacelist)) $newspacelist = array();
            foreach ($newspacelist as $value) {
                $oluids[] = $value['uid'];
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
            }
        }

        //最近访客列表
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,12");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            realname_set($value['vuid'], $value['vusername']);
            $visitorlist[$value['vuid']] = $value;
            $oluids[] = $value['vuid'];
        }

        //访客在线
        if($oluids) {
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($oluids).")");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    $ols[$value['uid']] = 1;
                } elseif ($visitorlist[$value['uid']]) {
                    unset($visitorlist[$value['uid']]);
                }
            }
        }

        $oluids = array();
        $olfcount = 0;
        if($space['feedfriend']) {
            //在线好友
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN ($space[feedfriend]) ORDER BY lastactivity DESC LIMIT 0,15");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $ols[$value['uid']] = 1;
                    $oluids[$value['uid']] = $value['uid'];
                    $olfcount++;
                }
            }
        }
        if($olfcount < 15) {
            //我的好友
            $query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username, num FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,30");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(empty($oluids[$value['uid']])) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $olfcount++;
                    if($olfcount == 15) break;
                }
            }
        }

        //获取任务
        include_once(S_ROOT.'./source/function_space.php');
        $task = gettask();

        //好友生日
        if($space['feedfriend']) {
            list($s_month, $s_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']-3600*24*3));//过期3天
            list($n_month, $n_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']));
            list($e_month, $e_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']+3600*24*7));
            if($e_month == $s_month) {
                $wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
            } else {
                $wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
            }
            $query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus,s.groupid,sf.birthyear,sf.birthmonth,sf.birthday
                    FROM ".tname('spacefield')." sf
                    LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
                    WHERE (sf.uid IN ($space[feedfriend])) AND ($wheresql)");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
                $value['istoday'] = 0;
                if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
                    $value['istoday'] = 1;
                }
                $key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
                $birthlist[$key][] = $value;
                ksort($birthlist);
            }
        }

        //积分
        $space['star'] = getstar($space['experience']);

        //域名
        $space['domainurl'] = space_domain($space);

        //热点
        if($_SCONFIG['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
            $hotlist_all = array();
            $hotstarttime = $_SGLOBAL['timestamp'] - $_SCONFIG['feedhotday']*3600*24;
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." USE INDEX(hot) WHERE dateline>='$hotstarttime' ORDER BY hot DESC LIMIT 0,10");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    if(empty($hotlist)) {
                        $hotlist[$value['feedid']] = $value;
                    } else {
                        $hotlist_all[$value['feedid']] = $value;
                    }
                }
            }
            $nexthotnum = $_SCONFIG['feedhotnum'] - 1;
            if($nexthotnum > 0) {
                if(count($hotlist_all)> $nexthotnum) {
                    $hotlist_key = array_rand($hotlist_all, $nexthotnum);
                    if($nexthotnum == 1) {
                        $hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
                    } else {
                        foreach ($hotlist_key as $key) {
                            $hotlist[$key] = $hotlist_all[$key];
                        }
                    }
                } else {
                    $hotlist = $hotlist_all;
                }
            }
        }

        //热闹
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY lastpost DESC LIMIT 0,1");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['pic'] = $value['pic']?pic_get($value['pic'], $value['thumb'], $value['remote']):'';
            $topiclist[] = $value;
        }


        //提醒总数
        $space['allnum'] = 0;
        foreach (array('notenum', 'addfriendnum', 'mtaginvitenum', 'eventinvitenum', 'myinvitenum', 'pokenum', 'reportnum', 'namestatusnum', 'eventverifynum') as $value) {
            $space['allnum'] = $space['allnum'] + $space[$value];
        }
    }

    //实名处理
    realname_get();

    //feed合并
    $list = array();

    if($_GET['view'] == 'hot') {
        //热点
        foreach ($feed_list as $value) {
            $value = mkfeed($value);
            $list['today'][] = $value;
        }
    } elseif($_GET['view'] == 'me') {
        //个人
        foreach ($feed_list as $value) {
            if($hotlist[$value['feedid']]) continue;
            $value = mkfeed($value);
            if($value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $value;
            } elseif ($value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $value;
            } else {
                $theday = sgmdate('Y-m-d', $value['dateline']);
                $list[$theday][] = $value;
            }
        }
    } else {
        //好友、全站
        foreach ($feed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            if($hotlist[$a_value['feedid']]) continue;
            $a_value = mkfeed($a_value, $actors);
            if($a_value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $a_value;
            } elseif ($a_value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $a_value;
            } else {
                $theday = sgmdate('Y-m-d', $a_value['dateline']);
                $list[$theday][] = $a_value;
            }
        }
        //应用
        foreach ($appfeed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            $a_value = mkfeed($a_value, $actors);
            $list['app'][] = $a_value;
        }
    }

    //获得个性模板
    $templates = $default_template = array();
    $tpl_dir = sreaddir(S_ROOT.'./template');
    foreach ($tpl_dir as $dir) {
        if(file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
            $tplicon = file_exists(S_ROOT.'./template/'.$dir.'/image/template.gif')?'template/'.$dir.'/image/template.gif':'image/tlpicon.gif';
            $tplvalue = array('name'=> $dir, 'icon'=>$tplicon);
            if($dir == $_SCONFIG['template']) {
                $default_template = $tplvalue;
            } else {
                $templates[$dir] = $tplvalue;
            }
        }
    }
    $_TPL['templates'] = $templates;
    $_TPL['default_template'] = $default_template;

    //标签激活
    $my_actives = array(in_array($_GET['filter'], array('site','myapp'))?$_GET['filter']:'all' => ' class="active"');
    $actives = array(in_array($_GET['view'], array('me','all','hot'))?$_GET['view']:'we' => ' class="active"');

    $feeds = array();

    $circle_filter = array ( 'album', 'blog', 'thread', 'event', 'poll', 'doing' );

    foreach( $feed_list as $value ){

        if ( ! in_array( $value['icon'], $circle_filter ) ) {
            //echo "nexted $value[icon]\n";
            continue;
        }

        $value = mkfeed($value);

        $feed = array();

        $feed['txt'] = isset($value['body_template']) ? $value['body_template'] : $value['title_template'] ;
        $feed['txt'] = strip_tags ( html_entity_decode( $feed['txt'], ENT_QUOTES, "utf-8" ) );
        if ( empty($feed['txt']) ) continue;

        $feed["p"] = array($value['uid'],$value['username'],avatar($value['uid'],'middle',true) );


        $feed["ts"] = $value['dateline'];

        if ( 'doing'==$value['icon'] ) {
            $feed["type"] = 'txt';
        }else{
            $feed["type"] = 'img';
        }

        $feed["like"] = array(1,2);
        $feed["reply"] = array(array(1,"qqqq"),array(2,"xxx"));

        $feed["img"] = array();
        for ( $n=1; $n<=9; $n++ ) {
            if ( 0==strlen($value["image_$n"]) ) break;
            array_push( $feed['img'], "http://17salsa.com/home/" . $value["image_$n"]  );
        }

        if ( empty($feed['img']) ) {
            $feed['type'] = 'txt';
            $feed['img'] = null;
        }


        array_push($feeds, $feed);
    }

    $resp['b'] = $feeds;
    $resp['h']['ret'] = ERR_OK;

    //print_r($resp);
    return $resp;
}

function getFriendFeeds($uid) {
    global $_SGLOBAL;

    $space = getspace($uid, 'uid');

    $_GET['view'] = 'we';

    //显示全站动态的好友数
    if(empty($_SCONFIG['showallfriendnum']) || $_SCONFIG['showallfriendnum']<1) $_SCONFIG['showallfriendnum'] = 10;
    //默认热点天数
    if(empty($_SCONFIG['feedhotday'])) $_SCONFIG['feedhotday'] = 2;

    //网站近况
    $isnewer = $space['friendnum']<$_SCONFIG['showallfriendnum']?1:0;
    if(empty($_GET['view']) && $space['self'] && $isnewer) {
        $_GET['view'] = 'all';//默认显示
    }

    //分页
    $perpage = $_SCONFIG['feedmaxnum']<50?50:$_SCONFIG['feedmaxnum'];
    $perpage = mob_perpage($perpage);

    if($_GET['view'] == 'hot') {
        $perpage = 50;
    }

    $start = empty($_GET['start'])?0:intval($_GET['start']);
    //检查开始数
    ckstart($start, $perpage);

    //今天时间开始线
    $_SGLOBAL['today'] = sstrtotime(sgmdate('Y-m-d'));

    //最少热度
    $minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];
    $_SGLOBAL['gift_appid'] = '1027468';

    if($_GET['view'] == 'all') {

        $wheresql = "1";//没有隐私
        $ordersql = "dateline DESC";
        $theurl = "space.php?uid=$space[uid]&do=$do&view=all";
        $f_index = '';

    } elseif($_GET['view'] == 'hot') {

        $wheresql = "hot>='$minhot'";
        $ordersql = "dateline DESC";
        $theurl = "space.php?uid=$space[uid]&do=$do&view=hot";
        $f_index = '';

    } else {

        if(empty($space['feedfriend'])) $_GET['view'] = 'me';

        if( $_GET['view'] == 'me') {
            $wheresql = "uid='$space[uid]'";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=me";
            $f_index = '';

        } else {
            $wheresql = "uid IN ('0',$space[feedfriend])";
            $ordersql = "dateline DESC";
            $theurl = "space.php?uid=$space[uid]&do=$do&view=we";
            $f_index = 'USE INDEX(dateline)';
            $_GET['view'] = 'we';
            //不显示时间
            $_TPL['hidden_time'] = 1;
        }
    }

    //过滤
    $appid = empty($_GET['appid'])?0:intval($_GET['appid']);
    if($appid) {
        $wheresql .= " AND appid='$appid'";
    }
    $icon = empty($_GET['icon'])?'':trim($_GET['icon']);
    if($icon) {
        $wheresql .= " AND icon='$icon'";
    }
    $filter = empty($_GET['filter'])?'':trim($_GET['filter']);
    if($filter == 'site') {
        $wheresql .= " AND appid>0";
    } elseif($filter == 'myapp') {
        $wheresql .= " AND appid='0'";
    }

    $feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
    $count = $filtercount = 0;
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $f_index
            WHERE $wheresql
            ORDER BY $ordersql
            LIMIT $start,$perpage");

    if($_GET['view'] == 'me' || $_GET['view'] == 'hot') {
        //个人动态
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                realname_set($value['uid'], $value['username']);
                $feed_list[] = $value;
            }
            $count++;
        }
    } else {
        //要折叠的动态
        $hidden_icons = array();
        if($_SCONFIG['feedhiddenicon']) {
            $_SCONFIG['feedhiddenicon'] = str_replace(' ', '', $_SCONFIG['feedhiddenicon']);
            $hidden_icons = explode(',', $_SCONFIG['feedhiddenicon']);
        }
        $space['filter_icon'] = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            if(empty($feed_list[$value['hash_data']][$value['uid']])) {
                if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    //				if(ckicon_uid($value)) {
                    $ismyapp = is_numeric($value['icon'])?1:0;
                    if($_SCONFIG['my_showgift'] && $value['icon'] == $_SGLOBAL['gift_appid']) $ismyapp = 0;
                    if((($ismyapp && in_array('myop', $hidden_icons)) || in_array($value['icon'], $hidden_icons)) && !empty($icon_num[$value['icon']])) {
                        $hiddenfeed_num[$value['icon']]++;
                        $hiddenfeed_list[$value['icon']][] = $value;
                    } else {
                        if($ismyapp) {
                            $appfeed_list[$value['hash_data']][$value['uid']] = $value;
                        } else {
                            $feed_list[$value['hash_data']][$value['uid']] = $value;
                        }
                    }
                    $icon_num[$value['icon']]++;
                    //				} else {
                    //					$filtercount++;
                    //				$filter_list[] = $value;
                    //				}
                }
            }
            $count++;
        }
    }

    $olfriendlist = $visitorlist = $task = $ols = $birthlist = $myapp = $hotlist = $guidelist = array();
    $oluids = array();
    $topiclist = array();
    $newspacelist = array();

    if($space['self'] && empty($start)) {

        //短消息
        $space['pmnum'] = $_SGLOBAL['member']['newpm'];

        //举报管理
        if(checkperm('managereport')) {
            $space['reportnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('report')." WHERE new='1'"), 0);
        }

        //审核活动
        if(checkperm('manageevent')) {
            $space['eventverifynum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('event')." WHERE grade='0'"), 0);
        }

        //等待实名认证
        if($_SCONFIG['realname'] && checkperm('managename')) {
            $space['namestatusnum'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')." WHERE namestatus='0' AND name!=''"), 0);
        }

        //欢迎新成员
        if($_SCONFIG['newspacenum']>0) {
            $newspacelist = unserialize(data_get('newspacelist'));
            if(!is_array($newspacelist)) $newspacelist = array();
            foreach ($newspacelist as $value) {
                $oluids[] = $value['uid'];
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
            }
        }

        //最近访客列表
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,12");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            realname_set($value['vuid'], $value['vusername']);
            $visitorlist[$value['vuid']] = $value;
            $oluids[] = $value['vuid'];
        }

        //访客在线
        if($oluids) {
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($oluids).")");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    $ols[$value['uid']] = 1;
                } elseif ($visitorlist[$value['uid']]) {
                    unset($visitorlist[$value['uid']]);
                }
            }
        }

        $oluids = array();
        $olfcount = 0;
        if($space['feedfriend']) {
            //在线好友
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN ($space[feedfriend]) ORDER BY lastactivity DESC LIMIT 0,15");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(!$value['magichidden']) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $ols[$value['uid']] = 1;
                    $oluids[$value['uid']] = $value['uid'];
                    $olfcount++;
                }
            }
        }
        if($olfcount < 15) {
            //我的好友
            $query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username, num FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,30");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if(empty($oluids[$value['uid']])) {
                    realname_set($value['uid'], $value['username']);
                    $olfriendlist[] = $value;
                    $olfcount++;
                    if($olfcount == 15) break;
                }
            }
        }

        //获取任务
        include_once(S_ROOT.'./source/function_space.php');
        $task = gettask();

        //好友生日
        if($space['feedfriend']) {
            list($s_month, $s_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']-3600*24*3));//过期3天
            list($n_month, $n_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']));
            list($e_month, $e_day) = explode('-', sgmdate('n-j', $_SGLOBAL['timestamp']+3600*24*7));
            if($e_month == $s_month) {
                $wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
            } else {
                $wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
            }
            $query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus,s.groupid,sf.birthyear,sf.birthmonth,sf.birthday
                    FROM ".tname('spacefield')." sf
                    LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
                    WHERE (sf.uid IN ($space[feedfriend])) AND ($wheresql)");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
                $value['istoday'] = 0;
                if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
                    $value['istoday'] = 1;
                }
                $key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
                $birthlist[$key][] = $value;
                ksort($birthlist);
            }
        }

        //积分
        $space['star'] = getstar($space['experience']);

        //域名
        $space['domainurl'] = space_domain($space);

        //热点
        if($_SCONFIG['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
            $hotlist_all = array();
            $hotstarttime = $_SGLOBAL['timestamp'] - $_SCONFIG['feedhotday']*3600*24;
            $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." USE INDEX(hot) WHERE dateline>='$hotstarttime' ORDER BY hot DESC LIMIT 0,10");
            while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
                    realname_set($value['uid'], $value['username']);
                    if(empty($hotlist)) {
                        $hotlist[$value['feedid']] = $value;
                    } else {
                        $hotlist_all[$value['feedid']] = $value;
                    }
                }
            }
            $nexthotnum = $_SCONFIG['feedhotnum'] - 1;
            if($nexthotnum > 0) {
                if(count($hotlist_all)> $nexthotnum) {
                    $hotlist_key = array_rand($hotlist_all, $nexthotnum);
                    if($nexthotnum == 1) {
                        $hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
                    } else {
                        foreach ($hotlist_key as $key) {
                            $hotlist[$key] = $hotlist_all[$key];
                        }
                    }
                } else {
                    $hotlist = $hotlist_all;
                }
            }
        }

        //热闹
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY lastpost DESC LIMIT 0,1");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $value['pic'] = $value['pic']?pic_get($value['pic'], $value['thumb'], $value['remote']):'';
            $topiclist[] = $value;
        }


        //提醒总数
        $space['allnum'] = 0;
        foreach (array('notenum', 'addfriendnum', 'mtaginvitenum', 'eventinvitenum', 'myinvitenum', 'pokenum', 'reportnum', 'namestatusnum', 'eventverifynum') as $value) {
            $space['allnum'] = $space['allnum'] + $space[$value];
        }
    }

    //实名处理
    realname_get();

    //feed合并
    $list = array();

    if($_GET['view'] == 'hot') {
        //热点
        foreach ($feed_list as $value) {
            $value = mkfeed($value);
            $list['today'][] = $value;
        }
    } elseif($_GET['view'] == 'me') {
        //个人
        foreach ($feed_list as $value) {
            if($hotlist[$value['feedid']]) continue;
            $value = mkfeed($value);
            if($value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $value;
            } elseif ($value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $value;
            } else {
                $theday = sgmdate('Y-m-d', $value['dateline']);
                $list[$theday][] = $value;
            }
        }
    } else {
        //好友、全站
        foreach ($feed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            if($hotlist[$a_value['feedid']]) continue;
            $a_value = mkfeed($a_value, $actors);
            if($a_value['dateline']>=$_SGLOBAL['today']) {
                $list['today'][] = $a_value;
            } elseif ($a_value['dateline']>=$_SGLOBAL['today']-3600*24) {
                $list['yesterday'][] = $a_value;
            } else {
                $theday = sgmdate('Y-m-d', $a_value['dateline']);
                $list[$theday][] = $a_value;
            }
        }
        //应用
        foreach ($appfeed_list as $values) {
            $actors = array();
            $a_value = array();
            foreach ($values as $value) {
                if(empty($a_value)) {
                    $a_value = $value;
                }
                $actors[] = "<a href=\"space.php?uid=$value[uid]\">".$_SN[$value['uid']]."</a>";
            }
            $a_value = mkfeed($a_value, $actors);
            $list['app'][] = $a_value;
        }
    }

    //获得个性模板
    $templates = $default_template = array();
    $tpl_dir = sreaddir(S_ROOT.'./template');
    foreach ($tpl_dir as $dir) {
        if(file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
            $tplicon = file_exists(S_ROOT.'./template/'.$dir.'/image/template.gif')?'template/'.$dir.'/image/template.gif':'image/tlpicon.gif';
            $tplvalue = array('name'=> $dir, 'icon'=>$tplicon);
            if($dir == $_SCONFIG['template']) {
                $default_template = $tplvalue;
            } else {
                $templates[$dir] = $tplvalue;
            }
        }
    }
    $_TPL['templates'] = $templates;
    $_TPL['default_template'] = $default_template;

    //标签激活
    $my_actives = array(in_array($_GET['filter'], array('site','myapp'))?$_GET['filter']:'all' => ' class="active"');
    $actives = array(in_array($_GET['view'], array('me','all','hot'))?$_GET['view']:'we' => ' class="active"');


    $feeds = array();

    $circle_filter = array ( 'album', 'blog', 'thread', 'event', 'poll', 'doing' );

    foreach( $feed_list as $alist ) {

        foreach ( $alist as $value ) {

            if ( ! in_array( $value['icon'], $circle_filter ) ) {
                //echo "nexted $value[icon]\n";
                continue;
            }

            $value = mkfeed($value);

            $feed = array();

            $feed['txt'] = isset($value['body_template']) ? $value['body_template'] : $value['title_template'] ;
            $feed['txt'] = strip_tags ( html_entity_decode( $feed['txt'], ENT_QUOTES, "utf-8" ) );
            if ( empty($feed['txt']) ) continue;

            $feed["p"] = array($value['uid'],$value['username'],avatar($value['uid'],'middle',true) );


            $feed["ts"] = $value['dateline'];

            if ( 'doing'==$value['icon'] ) {
                $feed["type"] = 'txt';
            }else{
                $feed["type"] = 'img';
            }


            $feed["img"] = array();
            for ( $n=1; $n<=9; $n++ ) {
                if ( 0==strlen($value["image_$n"]) ) break;
                array_push( $feed['img'], "http://17salsa.com/home/" . $value["image_$n"]  );
            }

            if ( empty($feed['img']) ) {
                $feed['type'] = 'txt';
                $feed['img'] = null;
            }

            $feed[id]      = $value[id] . '@' . $value[idtype];

            switch ( $value[idtype] ) {
            case 'blogid':
                $like_n_comment = get_blog_like_n_comment($value[id],$value[uid]);
                $feed[like]     = $like_n_comment[like];
                $feed[reply]    = $like_n_comment[comment];
                break;
            case 'picid':
                $like_n_comment = get_pic_like_n_comment($value[id],$value[uid]);
                $feed[like]     = $like_n_comment[like];
                $feed[reply]    = $like_n_comment[comment];
                break;
            default:
                break;
            }

            array_push($feeds, $feed);
        }
    }

    $resp['b'] = $feeds;
    $resp['h']['ret'] = ERR_OK;

    return $resp;

}

function get_feed_like($id,$idtype)
{
    return array(1,2,3);
}

function get_feed_reply($id,$idtype)
{
    return array(
        array(1,'xixi')
        ,array(2,'haha')
    );
}

function get_blog_like_n_comment($id, $uid)
{
    global $_SCONFIG, $_SGLOBAL;

    $_GET['id'] = $id;

$space = getspace($uid, 'uid');

$minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$classid = empty($_GET['classid'])?0:intval($_GET['classid']);

//表态分类
@include_once(S_ROOT.'./data/data_click.php');
$clicks = empty($_SGLOBAL['click']['blogid'])?array():$_SGLOBAL['click']['blogid'];

	//读取日志
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid='$id' AND b.uid='$space[uid]'");
	$blog = $_SGLOBAL['db']->fetch_array($query);
	//日志不存在
	if(empty($blog)) {
		//showmessage('view_to_info_did_not_exist');
        return array();
	}
	//检查好友权限
	if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {
		//没有权限
/*
		include template('space_privacy');
		exit();
*/
        return array();
	} elseif(!$space['self'] && $blog['friend'] == 4) {
		//密码输入问题
		$cookiename = "view_pwd_blog_$blog[blogid]";
		$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
		if($cookievalue != md5(md5($blog['password']))) {
			$invalue = $blog;
            /*
			include template('do_inputpwd');
			exit();
            */
            return array();
		}
	}

	//整理
	$blog['tag'] = empty($blog['tag'])?array():unserialize($blog['tag']);

	//处理视频标签
	include_once(S_ROOT.'./source/function_blog.php');
	$blog['message'] = blog_bbcode($blog['message']);

	$otherlist = $newlist = array();

	//有效期
	if($_SCONFIG['uc_tagrelatedtime'] && ($_SGLOBAL['timestamp'] - $blog['relatedtime'] > $_SCONFIG['uc_tagrelatedtime'])) {
		$blog['related'] = array();
	}
	if($blog['tag'] && empty($blog['related'])) {
		@include_once(S_ROOT.'./data/data_tagtpl.php');

		$b_tagids = $b_tags = $blog['related'] = array();
		$tag_count = -1;
		foreach ($blog['tag'] as $key => $value) {
			$b_tags[] = $value;
			$b_tagids[] = $key;
			$tag_count++;
		}
		if(!empty($_SCONFIG['uc_tagrelated']) && $_SCONFIG['uc_status']) {
			if(!empty($_SGLOBAL['tagtpl']['limit'])) {
				include_once(S_ROOT.'./uc_client/client.php');
				$tag_index = mt_rand(0, $tag_count);
				$blog['related'] = uc_tag_get($b_tags[$tag_index], $_SGLOBAL['tagtpl']['limit']);
			}
		} else {
			//自身TAG
			$tag_blogids = array();
			$query = $_SGLOBAL['db']->query("SELECT DISTINCT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($b_tagids).") AND blogid<>'$blog[blogid]' ORDER BY blogid DESC LIMIT 0,10");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$tag_blogids[] = $value['blogid'];
			}
			if($tag_blogids) {
				$query = $_SGLOBAL['db']->query("SELECT uid,username,subject,blogid FROM ".tname('blog')." WHERE blogid IN (".simplode($tag_blogids).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);//实名
					$value['url'] = "space.php?uid=$value[uid]&do=blog&id=$value[blogid]";
					$blog['related'][UC_APPID]['data'][] = $value;
				}
				$blog['related'][UC_APPID]['type'] = 'UCHOME';
			}
		}
		if(!empty($blog['related']) && is_array($blog['related'])) {
			foreach ($blog['related'] as $appid => $values) {
				if(!empty($values['data']) && $_SGLOBAL['tagtpl']['data'][$appid]['template']) {
					foreach ($values['data'] as $itemkey => $itemvalue) {
						if(!empty($itemvalue) && is_array($itemvalue)) {
							$searchs = $replaces = array();
							foreach (array_keys($itemvalue) as $key) {
								$searchs[] = '{'.$key.'}';
								$replaces[] = $itemvalue[$key];
							}
							$blog['related'][$appid]['data'][$itemkey]['html'] = stripslashes(str_replace($searchs, $replaces, $_SGLOBAL['tagtpl']['data'][$appid]['template']));
						} else {
							unset($blog['related'][$appid]['data'][$itemkey]);
						}
					}
				} else {
					$blog['related'][$appid]['data'] = '';
				}
				if(empty($blog['related'][$appid]['data'])) {
					unset($blog['related'][$appid]);
				}
			}
		}
		updatetable('blogfield', array('related'=>addslashes(serialize(sstripslashes($blog['related']))), 'relatedtime'=>$_SGLOBAL['timestamp']), array('blogid'=>$blog['blogid']));//更新
	} else {
		$blog['related'] = empty($blog['related'])?array():unserialize($blog['related']);
	}

	//作者的其他最新日志
	$otherlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,6");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) {
			$otherlist[] = $value;
		}
	}

	//最新的日志
	$newlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE hot>=3 ORDER BY dateline DESC LIMIT 0,6");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) {
			realname_set($value['uid'], $value['username']);
			$newlist[] = $value;
		}
	}

	//评论
	$perpage = 300;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);

	$count = $blog['replynum'];

	$list = array();
	if($count) {
		$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
		$csql = $cid?"cid='$cid' AND":'';

		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='blogid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);//实名
			$list[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, "space.php?uid=$blog[uid]&do=$do&id=$id", '', 'content');

	//访问统计
	if(!$space['self'] && $_SCOOKIE['view_blogid'] != $blog['blogid']) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET viewnum=viewnum+1 WHERE blogid='$blog[blogid]'");
		inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//延迟更新
		ssetcookie('view_blogid', $blog['blogid']);
	}

	//表态
	$hash = md5($blog['uid']."\t".$blog['dateline']);
	$id = $blog['blogid'];
	$idtype = 'blogid';

	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $blog["click_$key"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	//点评
	$clickuserlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,18");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);//实名
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}

	//热点
	$topic = topic_get($blog['topicid']);

	//实名
	realname_get();

    $resp = array();

    $resp[like]     = array();
    $resp[comment]  = array();

    foreach ( $clickuserlist as $click ) {
        array_push( $resp[like], $click[uid] );
    }

    foreach ( $list as $comment ) {
        array_push( $resp[comment], array( $comment[uid], $comment[message] ) );
    }

    return $resp;

//die("FT");
//print_r($clickuserlist);
//print_r($list);
//	$_TPL['css'] = 'blog';
//	include_once template("space_blog_view");

}

function get_pic_like_n_comment($picid, $uid)
{
    global $_SCONFIG, $_SGLOBAL;

    $_GET['picid'] = $picid;
    $space[uid] = $uid;

$minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];

$id = empty($_GET['id'])?0:intval($_GET['id']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;

//表态分类
@include_once(S_ROOT.'./data/data_click.php');
$clicks = empty($_SGLOBAL['click']['picid'])?array():$_SGLOBAL['click']['picid'];


	if(empty($_GET['goto'])) $_GET['goto'] = '';

	$eventid = intval($eventid);
	if(empty($eventid)) {
		//检索图片
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid='$picid' AND uid='$space[uid]' LIMIT 1");
		$pic = $_SGLOBAL['db']->fetch_array($query);
	}


	$picid = $pic['picid'];

	//图片不存在
	if(empty($picid)) {
		//showmessage('view_images_do_not_exist');
        return array();
	}

	if($eventid) {
		$theurl = "space.php?do=event&id=$eventid&view=pic&picid=$picid";
	} else {
		$theurl = "space.php?uid=$pic[uid]&do=$do&picid=$picid";
	}

	//获取相册
	$album = array();
	if($pic['albumid']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$pic[albumid]'");
		if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
			updatetable('pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));//相册丢失?
		}
	}

	if($album) {
		if($eventid) {
			//活动图片
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventpic")." WHERE eventid='$eventid' AND picid='$picid'");
			if (!$eventpic = $_SGLOBAL['db']->fetch_array($query)) {
				//showmessage('pic_not_share_to_event');// 图片没有共享到活动
                return array();
			}
			$album['picnum'] = $piccount;
		} else {
			//相册好友权限
			//ckfriend_album($album);
		}
	} else {
		$album['picnum'] = getcount('pic', array('uid'=>$pic['uid'], 'albumid'=>0));
		$album['albumid'] = $pic['albumid'] = '-1';
	}

	if($album['picnum']) {
		//当前张数
		if($_GET['goto']=='down') {
			$sequence = empty($_SCOOKIE['pic_sequence'])?$album['picnum']:intval($_SCOOKIE['pic_sequence']);
			$sequence++;
			if($sequence>$album['picnum']) {
				$sequence = 1;
			}
		} elseif($_GET['goto']=='up') {
			$sequence = empty($_SCOOKIE['pic_sequence'])?$album['picnum']:intval($_SCOOKIE['pic_sequence']);
			$sequence--;
			if($sequence<1) {
				$sequence = $album['picnum'];
			}
		} else {
			$sequence = 1;
		}
		ssetcookie('pic_sequence', $sequence);
	}

	//图片地址
	$pic['pic'] = pic_get($pic['filepath'], $pic['thumb'], $pic['remote'], 0);
	$pic['size'] = formatsize($pic['size']);

	//图片的EXIF信息
	$exifs = array();
	$allowexif = function_exists('exif_read_data');
	if(isset($_GET['exif']) && $allowexif) {
		include_once(S_ROOT.'./source/function_exif.php');
		$exifs = getexif($pic['pic']);
	}

	//图片评论
	$perpage = 50;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';
	$siteurl = getsiteurl();
	$list = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$pic[picid]' AND idtype='picid'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$pic[picid]' AND idtype='picid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);
			$list[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, $theurl, '', 'pic_comment');

	//标题
	if(empty($album['albumname'])) $album['albumname'] = lang('default_albumname');

	//图片全路径
	$pic_url = $pic['pic'];
	if(!preg_match("/^http\:\/\/.+?/i", $pic['pic'])) {
		$pic_url = getsiteurl().$pic['pic'];
	}
	$pic_url2 = rawurlencode($pic['pic']);


	//是否活动照片
	if(!$eventid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventpic")." ep LEFT JOIN ".tname("event")." e ON ep.eventid=e.eventid WHERE ep.picid='$picid'");
		$event = $_SGLOBAL['db']->fetch_array($query);
	}

	//表态
	$hash = md5($pic['uid']."\t".$pic['dateline']);
	$id = $pic['picid'];
	$idtype = 'picid';

	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $pic["click_$key"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	//点评
	$clickuserlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,18");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);//实名
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}

	//热闹
	$topic = topic_get($pic['topicid']);

		//实名
		realname_get();

		//include_once template("space_album_pic");
/*
print_r($clickuserlist);
print_r($list);

die("FT");
*/

    $resp = array();

    $resp[like]     = array();
    $resp[comment]  = array();

    foreach ( $clickuserlist as $click ) {
        array_push( $resp[like], $click[uid] );
    }

    foreach ( $list as $comment ) {
        array_push( $resp[comment], array( $comment[uid], $comment[message] ) );
    }

    return $resp;

}



?>
