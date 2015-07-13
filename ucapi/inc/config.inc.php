<?php
header("Content-Type:text/html; charset=utf-8");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$origin = '*' ;
if ( function_exists('apache_request_headers') ) {
    $req_headers = apache_request_headers();
    $origin = $req_headers['Origin'];
}

// allow ajax pass cookie
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');


include_once('/750/xfs/vhost/17salsa.com/home/common.php');
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./uc_client/client.php');

/*
 * API Protocol Error Number Table
 */
define('ERR_OK',            0   );

define('ERR_UNKNOWN',       -1  );
define('ERR_NEEDLOGIN',     1   );


error_reporting(E_ALL);
?>
