<?php
require_once(dirname(__FILE__)."/../configdb.php");
//$file_path1 = 'temp_bas.txt';
ob_start();
$id = isset($_GET['id']) ? $_GET['id'] : "";
$base64 = isset($_POST['data']) ? $_POST['data'] : "";
if(trim($id)=='') {
	err_page();
}
$json = base64_decode($id);
$arr = json_decode($json,true);
if(!is_array($arr) || count($arr)==0) {
	err_page();
}
if(!isset($arr['type']) || !isset($arr['player_id']) || !isset($arr['event_id'])) {
	err_page();
}

$player_id = $arr['player_id'];
$type = strtolower($arr['type']);
$event_id = $arr['event_id'];

if($type!="facebook" && $type!="twitter") {
	err_page();
}

if(trim($base64)=='') {
	err_page();
}

$file_name = $event_id.'_'.$player_id.'.png';
$file_path = 'share/scorecard/images/'.$event_id.'/';
$basepath=BASE_PATH.'/'.$file_path.$file_name;;
$baseurl=BASE_URL.'share/scorecard/images/'.$event_id.'/'.$file_name;;
ob_clean();
saveImageFromBase64($base64,$file_path,$file_name);
?>