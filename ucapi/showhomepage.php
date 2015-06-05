<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$req = json_decode($res,true);//生成array数组


$response = showhomepage($req);
$response_json = json_encode($response);//生成json数据
//print_r($response);
die($response_json);

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
function showhomepage($req){
    $flag = $req['flag'];
    global $_SGLOBAL;

    $resp = array();
    $resp['h']['ret'] = ERR_UNKNOWN;
    

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


            $feed = array();

            $feed['txt'] = isset($value['body_template']) ? $value['body_template'] : $value['title_template'] ;
            $feed['txt'] = strip_tags ( html_entity_decode( $feed['txt'], ENT_QUOTES, "utf-8" ) );
            if ( empty($feed['txt']) ) continue;

            $feed["p"] = array(1,"阿布","./img/1.jpg");//tbd


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


?>
