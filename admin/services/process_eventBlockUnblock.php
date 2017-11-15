<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
  require_once(dirname(__FILE__).'../../config/db_config.php');
  require_once(dirname(__FILE__).'/functions.php');
  if(isset($_POST["action"])){
    $output_header="output";
    $action=$_POST["action"];
    switch($action){
      case 'blockUnblockEvent':{
        $event_id=isset($_POST["event_id"])?trim($_POST["event_id"]):"";
        $flag=isset($_POST["flag"])?trim($_POST["flag"]):"";
        $response_Array=blockUnblockEvent($event_id,$flag);
      } break;
    }
  }
  display_output($output_header,$response_Array);
}
 ?>
