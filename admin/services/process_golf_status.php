<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
  require_once(dirname(__FILE__).'../../config/db_config.php');
  $db=new DatabaseConnectionClass();
  if(isset($_POST["action"]) && isset($_POST["golf_id"])){
    $golf_course_id=$_POST["golf_id"];
    $action=$_POST["action"];
    switch($action){
      case 1:{
        $query="update golf_course set is_active='".intval(1)."' where golf_course_id='".$golf_course_id."'";
        if($db->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["function"]="changeStatus('".$golf_course_id."','2')";
          $response_Array["text"]='Active';
          $response_Array["class"]='btn btn-success';
        }
      } break;
      case 2:{
        $query="update golf_course set is_active='".intval(2)."' where golf_course_id='".$golf_course_id."'";
        if($db->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["function"]="changeStatus('".$golf_course_id."','1')";
          $response_Array["text"]='In-Active';
          $response_Array["class"]='btn btn-warning';
        }
      } break;
    }
  }
  //print_r($response_Array);
  echo json_encode($response_Array);
  exit();
}
 ?>
