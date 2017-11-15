<?php
require_once(dirname(__FILE__)."/../configdb.php");

$id = isset($_GET['id']) ? $_GET['id'] : "";
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
//print_r($arr);die;
if((!is_numeric($player_id) || $player_id<=0) && (!is_numeric($event_id) || $event_id<=0)) {
	err_page();
}

echo $html=sendScoreCard($event_id,$player_id,true,true,true);//die;

//echo file_get_contents("http://putt2gether.com/puttdemo/share/image.php?raw=1&id=?".$id);
?>
<style>* {margin:0px;}</style>