<?php
include_once('/750/xfs/vhost/17salsa.com/home/common.php');
include_once(S_ROOT.'./source/function_cp.php');
//include_once(S_ROOT.'./source/function_magic.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type:text/html; charset=utf-8");
$res = file_get_contents('php://input');
$data = json_decode($res,true);//生成array数组

$response = login($data);
$response_json = json_encode($response);//生成json数据
print_r($response_json);


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
	$response['ret'] = null;

	$username = $data['username'];
    $password = $data['password'];


	$cookietime = 0;
	$cookiecheck = $cookietime?' checked':'';
	$membername = $username;
	
	if(empty($username)) {
		$response['ret'] = false;
        return $response;
	}
	
	//同步获取用户源
	if(!$passport = getpassport($username, $password)) {
		return;
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
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
	ssetcookie('loginuser', $passport['username'], 31536000);
	ssetcookie('_refer', '');
	

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
	
/*
    unset($_SGLOBAL['creditrule']);
    unset($_SGLOBAL['appmenus']);
    unset($_SGLOBAL['appmenu']);
    unset($_SGLOBAL['magic']);
    unset($_SGLOBAL['ad']);
    unset($_SGLOBAL['app']);
*/


	$m_space = getspace($passport['uid']);

	//  个人主页的基本信息
	$response['user_id']        = $m_space['uid'];
	$response['user_name']      = $m_space['name'];
	$response['user_avatar']    = 'http://17salsa.com/center/data/avatar/' . $_SGLOBAL['avatarfile_1_middle_real'];
	$response['user_headpic']   = 'http://17salsa.com/center/data/avatar/' 
                                    . str_replace('middle','big',$_SGLOBAL['avatarfile_1_middle_real']);
	$response['user_gender']    = 1==$m_space['sex'] ? "男" : "女";
	$response['user_area']      = $m_space['resideprovince'] . " " . $m_space['residecity'];
	$response['user_sign']      = preg_replace('/\<img[^>]+>/', '', $m_space['note']) ;

    $response['ret'] = true;

	return $response;
}
?>

