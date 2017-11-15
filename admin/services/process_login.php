<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
  require_once(dirname(__FILE__).'../../config/db_config.php');
  $db=new DatabaseConnectionClass();
  $user_Name=$_POST['admin_Username'];
  $user_Password=$_POST['admin_Password'];
  $user_Password=md5($user_Password);
  $query="select user_id,full_name,user_level from golf_users where user_name='".$user_Name."' and password='".$user_Password."' and approved='".intval(1)."' and user_type='".intval(0)."'";
  $user_data=$db->FetchQuery($query);
  if(is_array($user_data) && isset($user_data[0]) && count($user_data)==1){
    $user_data=$user_data[0];
    session_start();
    $_SESSION['a_user_id']=$user_data["user_id"];
    $_SESSION['a_user_name']=$user_data["full_name"];
    $_SESSION['a_user_level']=$user_data["user_level"];

    $response_Array["status"]="1";
    $response_Array["message"]="<strong>Redirecting to dashboard....</strong>";
  }
  else{
    $response_Array["status"]="0";
    $response_Array['message']="<strong>You are not authorized user, please enter correct username and password.</strong>";
  }
  echo json_encode($response_Array);
  exit();
}
 ?>
