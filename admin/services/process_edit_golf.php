<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
session_start();
require_once(dirname(__FILE__).'../../config/db_config.php');
require_once(dirname(__FILE__).'/functions.php');
$db = $database;
$result= $error= array();
$golf_course_id=isset($_POST["golf_course_id"]) ? trim($_POST['golf_course_id']) : "";
$golf_course_name=isset($_POST["golf_course_name"]) ? properText($_POST["golf_course_name"]) : "";
if($golf_course_name!=""){
  $golf_course_name=$database->escape($golf_course_name);
}
else{
  $error["name_error"]="Golf Course Name is Empty.";
}
//$number_of_holes=isset($_POST["number_of_holes"]) ? trim($_POST['number_of_holes']) : "";
$number_of_holes=18;
$country=isset($_POST["country"]) ? properText($_POST["country"]) : "";
if(!is_numeric($country) && $country!=""){
$country=$database->escape($country);
$country_code=isset($_POST["country_code"]) ? trim($_POST['country_code']) : "";
$country_id=checkCountry($country,$country_code);
}
else{
$country_id=$country;
}
$state=isset($_POST["state"]) ? properText($_POST["state"]) : "";
if(!is_numeric($state) && $state!=""){
  $state=$database->escape($state);
  $state_id=checkState($state,$country_id);
}
else{
  $state_id=$state;
}
$city=isset($_POST["city"]) ? properText($_POST["city"]) : "";
if(!is_numeric($city) && $city!=""){
  $city=$database->escape($city);
  $city_id=checkCity($city,$state_id,$country_id);
}
else{
  $city_id=$city;
}
$latitude=isset($_POST["latitude"]) ? trim($_POST['latitude']) : "";
$longitude=isset($_POST["longitude"]) ? trim($_POST['longitude']) : "";
$hole_index=isset($_POST["hole_index"]) ? $_POST['hole_index'] : "";
$par_value=isset($_POST["par_value"]) ? $_POST['par_value'] : "";
$tee_value_exp=isset($_POST["tee_value"]) ? $_POST['tee_value'] : "";
//second section
if($country==''){
  $error["country_error"]="Country Name is Empty.";
}
if(isset($_POST["country_code"]) && $_POST["country_code"]==""){
  $country_code=isset($_POST["country_code"]) ? trim($_POST['country_code']) : "";
  if(trim($country_code)==''){
    $error["country_code_error"]="Country Code is Empty.";
  }
}
if($state==''){
  $error["state_error"]="State Name is Empty.";
}
if($city==''){
  $error["city_error"]="City Name is Empty.";
}
if($latitude>90 || $latitude<-90 || !is_numeric($latitude)){
	$error["lat_error"] = "In-valid Latitude.";
}
if($longitude>180 || $longitude<-180 || !is_numeric($latitude)){
	$error["long_error"] = "In-valid Longitude.";
}
foreach($tee_value_exp as $key=>$value){
  if(trim($value)=="" || trim(strtolower($value))=="select"){
    unset($tee_value_exp[$key]);
  }
}
$value_count=array_count_values($tee_value_exp);
foreach($value_count as $count){
  if($count>1){
    $error["tee_value_error"]="You have selected duplicate color.";
    break;
  }
}
$value_count=array_count_values($hole_index);
foreach($value_count as $count){
  if($count>1){
    $error["hole_index_error"]="You have entered duplicate values for hole index.";
    break;
  }
}
foreach($par_value as $key=>$value){
  if(trim($value)==""){
    unset($par_value[$key]);
    unset($hole_index[$key]);
  }
}
foreach($hole_index as $key=>$value){
  if(trim($value)==""){
    unset($hole_index[$key]);
    unset($par_value[$key]);
  }
}
if(count($hole_index)<18 || count($par_value)<18){
  $error["hole_index_error"]="Please select all index values and par values.";
}
//for tee value
if(count($tee_value_exp)>0){
$j=1;
$tee_value=array();
$num=array("700","600","500","400","300","200","100");
    $tee_map=array();
if(is_array($tee_value_exp) && count($tee_value_exp)>0){
  foreach($tee_value_exp as $k=>$v){
    $new_teeval[$v]=$num[$k];
    $v=strtoupper($v);
    if(trim($v)==strtoupper('Black')){
    $tee_map[]=1;
    }
    else if(trim($v)==strtoupper('Blue')){
    $tee_map[]=2;
    }else if(trim($v)==strtoupper('Red')){
    $tee_map[]=3;
    }else if(trim($v)==strtoupper('Yellow')){
    $tee_map[]=4;
    }else if(trim($v)==strtoupper('White')){
    $tee_map[]=5;
    }else if(trim($v)==strtoupper('Green')){
    $tee_map[]=6;
    }else if(trim($v)==strtoupper('Gold')){
    $tee_map[]=7;
    }
  }
}
$final=array("Men"=>$new_teeval,"Ladies"=>$new_teeval,"Junior"=>$new_teeval);
$tee_value=json_encode($final);
$tee_map_value='';
if(is_array($tee_map) && count($tee_map)>0){
  sort($tee_map,SORT_NUMERIC);
  $tee_map_value=implode(',',$tee_map);
}
unset($new_teeval);
unset($tee_value_exp);
unset($tee_map);
}
else{
  $error["tee_value_error"]="Please Select Tee Values.";
}
//end second section++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if(count($error)==0){
  $today_date=date("Y-m-d H:i:s");
  //golf course
  $golf_course_exist='select golf_course_id from golf_course where TRIM(UPPER(golf_course_name))='.trim(strtoupper($golf_course_name)).' and city_id="'.$city_id.'" and golf_course_id!="'.$golf_course_id.'"';
  $golf_course_count=$db->FetchQuery($golf_course_exist);
  if(count($golf_course_count)==0){
    $sql='UPDATE golf_course SET golf_course_name='.$golf_course_name.',number_of_holes='.$number_of_holes.',city_id='.$city_id.',latitude='.$latitude.',longitude='.$longitude.',created_by='.$_SESSION["a_user_id"].' where golf_course_id='.$golf_course_id;
    $db->FetchQuery($sql);
    //code for second section
    $query_2="UPDATE golf_hole_index SET num_hole='".$number_of_holes."',";
    $sn=1;
    foreach($hole_index as $index){
      $query_2.="hole_index_$sn='".$index."',";
     $sn++;
    }
    $sn=1;
    $par_sum=0;
    foreach($par_value as $value){
      $par_sum+=$value;
      $query_2.="par_value_$sn='".$value."',";
     $sn++;
    }
    for($i=1;$i<=18;$i++){
      $query_2.="tee_value$i='".$tee_value."',";
    }
    $query_2.="total_par='".$par_sum."' where golf_course_id='".$golf_course_id."'";
    $db->FetchQuery($query_2);
    $query_3="UPDATE golf_course_tee SET men='".$tee_map_value."',ladies='".$tee_map_value."',junior='".$tee_map_value."' where golf_course_id='".$golf_course_id."'";
    $db->FetchQuery($query_3);
    $result["status"]="1";
    $result["result"]="Golf Course Updated Successfully.";
  }
  else{
    $error["result"]="Golf Course Already Exist.";
    $error["status"]="0";
    $result=$error;
  }
}
else{
  $error["result"]="Error Found.";
  $error["status"]="0";
  $result=$error;
}
echo json_encode($result);
exit();
}
?>
