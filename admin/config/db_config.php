<?php
require_once(dirname(__FILE__).'/mail/mail.php');

if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "localhost" ){
	ini_set('display_errors',1);
	define('_DB_NAME_', 'putt2get_myrwa_db');
	define('_DB_SERVER_', 'localhost');
	define('_DB_USER_', 'root');
	define('_DB_PASSWD_', '');
	define('__BASE_URL__', 'http://'.$_SERVER['HTTP_HOST']);
	define('__BASE_FOLDER__', '/admin');
	define('__BASE_URI__', __BASE_URL__.__BASE_FOLDER__.'/');
	define( "BASE_PATH", $_SERVER['DOCUMENT_ROOT'].__BASE_FOLDER__.'/');
}else{
	ini_set('display_errors',1);
	define('_DB_NAME_', 'putt2get_v2_db');
	define('_DB_SERVER_', 'localhost');
	define('_DB_USER_', 'putt2get_golf');
	define('_DB_PASSWD_', 'golf@putt@123');
	define('__BASE_URL__', 'http://'.$_SERVER['HTTP_HOST']);
	define('__BASE_FOLDER__', '/puttdemo/api_v2');
	define('__BASE_URI__', __BASE_URL__.__BASE_FOLDER__.'/');
	define( "BASE_PATH", $_SERVER['DOCUMENT_ROOT'].__BASE_FOLDER__.'/');

}

define('API_BASE_PATH', BASE_PATH.'api/');

require_once(dirname(__FILE__).'/pdo/constant_class.php');
require_once(dirname(__FILE__).'/pdo/database_connectivity_class.php');

$database = new DatabaseConnectionClass();

require_once(dirname(__FILE__).'/inc_constants.php');
require_once(dirname(__FILE__).'/mail/mail.php');
require_once(dirname(__FILE__).'/inc_tables.php');
require_once(dirname(__FILE__).'/inc_functions.php');
require_once(dirname(__FILE__).'/pushapns/sendpush.php');
?>
