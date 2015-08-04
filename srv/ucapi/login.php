<?php
require_once('inc/config.inc.php');

$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组



/*
   $data['username'] = 'ruiruibupt';
   $data['password'] = '123456';
*/

$response = login($data);
$response_json = json_encode($response);//生成json数据

die($response_json);

/**
 * @Author:      ruirui
 * @DateTime:    2015-05-30 10:57:49
 * @description: 实现用户登录
 * @para:        $data['username']			string 	前端传递的用户名
 * @para:        $data['password'] 			string 	前端传递的密码 
 * @return:      $response 					array 	用户的个人信息:
 *				 $response['ret'] 			boolen 	登陆成功true 登陆失败false boolen
 *				 $response['user_id']  				用户唯一识别id
 *				 $response['user_name']		string 	用户昵称
 *				 $response['user_avatar']	string 	用户头像
 *				 $response['user_headpic']	string 	用户朋友圈头图
 *				 $response['user_gender']	string 	用户性别
 *				 $response['user_area']		string 	地域
 *				 $response['user_sign']		string 	个人签名
 *			 
 */


function login($data) {
    global $_SGLOBAL;

    $response = array();
    $response['ret'] = false;

    $username = $data['username'];
    $password = $data['password'];

    if( empty($username) || empty($password) ) {
        return $response;
    }


    // end do_login.php


    if($_SGLOBAL['supe_uid']) {
        // already login
    }

    $refer = empty($_GET['refer'])?rawurldecode($_SCOOKIE['_refer']):$_GET['refer'];
    preg_match("/(admincp|do|cp)\.php\?ac\=([a-z]+)/i", $refer, $ms);
    if($ms) {
        if($ms[1] != 'cp' || $ms[2] != 'sendmail') $refer = '';
    }
    if(empty($refer)) {
        $refer = 'space.php?do=home';
    }

    //没有登录表单
    $_SGLOBAL['nologinform'] = 1;


    $cookietime = 315360000;

    $cookiecheck = $cookietime?' checked':'';
    $membername = $username;


    //同步获取用户源
    if(!$passport = getpassport($username, $password)) {
        // login failed
        return $response;
    }

    $setarr = array(
            'uid' => $passport['uid'],
            'username' => addslashes($passport['username']),
            'password' => md5("$passport[uid]|$_SGLOBAL[timestamp]")//本地密码随机生成
            );

    include_once(S_ROOT.'./source/function_space.php');
    //开通空间
    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
    if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
        $space = space_open($setarr['uid'], $setarr['username'], 0, $passport['email']);
    }

    $_SGLOBAL['member'] = $space;

    //实名
    realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);

    //检索当前用户
    $query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
    if($value = $_SGLOBAL['db']->fetch_array($query)) {
        $setarr['password'] = addslashes($value['password']);
    } else {
        //更新本地用户库
        inserttable('member', $setarr, 0, true);
    }

    //清理在线session
    insertsession($setarr);

    //设置cookie
    //$response['auth'] = authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'); // zixia 201506 save cookie to token

    ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
    ssetcookie('loginuser', $passport['username'], 31536000);
    ssetcookie('_refer', '');

    //同步登录
    if($_SCONFIG['uc_status']) {
        include_once S_ROOT.'./uc_client/client.php';
        $ucsynlogin = uc_user_synlogin($setarr['uid']);
    } else {
        $ucsynlogin = '';
    }

    //好友邀请
    if($invitearr) {
        //成为好友
        invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
    }
    $_SGLOBAL['supe_uid'] = $space['uid'];
    //判断用户是否设置了头像
    $reward = $setarr = array();
    $experience = $credit = 0;
    $avatar_exists = ckavatar($space['uid']);
    if($avatar_exists) {
        if(!$space['avatar']) {
            //奖励积分
            $reward = getreward('setavatar', 0);
            $credit = $reward['credit'];
            $experience = $reward['experience'];
            if($credit) {
                $setarr['credit'] = "credit=credit+$credit";
            }
            if($experience) {
                $setarr['experience'] = "experience=experience+$experience";
            }
            $setarr['avatar'] = 'avatar=1';
            $setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
        }
    } else {
        if($space['avatar']) {
            $setarr['avatar'] = 'avatar=0';
        }
    }

    if($setarr) {
        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
    }

    if(empty($_POST['refer'])) {
        $_POST['refer'] = 'space.php?do=home';
    }

    realname_get();

    //showmessage('login_success', $app?"userapp.php?id=$app":$_POST['refer'], 1, array($ucsynlogin));

    $membername = empty($_SCOOKIE['loginuser'])?'':sstripslashes($_SCOOKIE['loginuser']);
    $cookiecheck = ' checked';


    // end do_login.php


    $m_space = getspace($passport['uid']);

    //  个人主页的基本信息
    $response['user_id']        = $m_space['uid'];
    $response['user_name']      = $m_space['name'];
    $response['user_avatar']    = avatar($m_space['uid'], 'middle', true);
    $response['user_headpic']   = avatar($m_space['uid'], 'big', true);
    $response['user_gender']    = 1==$m_space['sex'] ? "男" : "女";
    $response['user_area']      = $m_space['resideprovince'] . " " . $m_space['residecity'];
    $response['user_sign']      = preg_replace('/\<img[^>]+>/', '', $m_space['note']) ;

//    print_r($m_space);
    $group = $m_space[groupid];

/*
禁止：12 13 4 8
5 (普通) 6 (中级) 7(高级)
*/
    switch ($group) {
    case 4:
    case 8:
    case 12:
    case 13:
        /* 禁止会员 */
    case 5: // 普通会员
    case 6: // 中级会员
    //case 7: // 高级会员
        $response['ret'] = false;
        $response['txt'] = '17SALSA手机APP内测中。目前暂时仅接受中级以上用户登录。有任何疑问可以联络 salsa@17salsa.com ';
        break;
    default:
        $response['ret'] = true;
        break;
    }

    return $response;
}
?>

