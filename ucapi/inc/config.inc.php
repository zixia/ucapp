<?php
$req_headers = apache_request_headers();

header("Content-Type:text/html; charset=utf-8");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// allow ajax pass cookie
header('Access-Control-Allow-Origin: ' . $req_headers['Origin'] );
header('Access-Control-Allow-Credentials: true');

require_once('error.inc.php');

include_once('/750/xfs/vhost/17salsa.com/home/common.php');
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./uc_client/client.php');

/*
 * API Protocol Error Number Table
 */
define('ERR_UNKNOWN',       -1  );
define('ERR_NEEDLOGIN',     1   );

?>
