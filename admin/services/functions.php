<?php
function properText($value){
  return trim(ucwords(strtolower($value)));
}
function checkCountry($country,$country_code){
  if(trim($country)=="" || trim($country_code)==""){
    return false;
  }
  else{
    $db=new DatabaseConnectionClass();
    $count_Query='select country_id from country where REPLACE(country_name,"\r","")='.$country;
    $count=$db->FetchSingleValue($count_Query);
    if($count<=0){
      $query='INSERT into country (country_name,phonecode) values ('.$country.','.$country_code.')';
      if($db->FetchQuery($query)){
        return $country_id=$db->LastInsertId();
      }
    }
    else{
      return $country_id=$count;
    }
  }
}
function checkState($state,$country_id){
  if(trim($state)=="" || !is_numeric($country_id)){
    return false;
  }
  else{
    $db=new DatabaseConnectionClass();
    $count_Query='select state_id from state where REPLACE(state_name,"\r","")='.$state.' and country_id='.$country_id;
    $count=$db->FetchSingleValue($count_Query);
    if($count<=0){
      $query='INSERT into state (country_id,state_name) values ('.$country_id.','.$state.')';
      if($db->FetchQuery($query)){
        return $state_id=$db->LastInsertId();
      }
    }
    else{
      return $state_id=$count;
    }
  }
}
function checkCity($city,$state_id,$country_id){
  if(trim($city)=="" || !is_numeric($country_id) || !is_numeric($state_id)){
    return false;
  }
  else{
    $db=new DatabaseConnectionClass();
    $count_Query='select city_id from city where  REPLACE(city_name,"\r","")='.$city.' and state_id='.$state_id.' and country_id='.$country_id;
    $count=$db->FetchSingleValue($count_Query);
    if($count<=0){
      $query='INSERT into city (country_id,state_id,city_name) values ('.$country_id.','.$state_id.','.$city.')';
      if($db->FetchQuery($query)){
        return $city_id=$db->LastInsertId();
      }
    }
    else{
      return $city_id=$count;
    }
  }
}
function escape($value){
  return mysql_real_escape_string($value);
}

function blockUnblockEvent($event_id="",$flag=""){
  global $database;
  $response_Array=array();
  if(trim($event_id)=="" || trim($flag)==""){
    $response_Array["status"]=0;
  }
  else{
    switch($flag){
    case 0:{
        $query="update "._EVENT_TBL_." set is_active='".intval(0)."' where event_id='".$event_id."'";
        if($database->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["action"]="1";
          $response_Array["text"]='Un-block';
          $response_Array["class"]='btn btn-success';
        }
      } break;
      case 1:{
        $query="update "._EVENT_TBL_." set is_active='".intval(1)."' where event_id='".$event_id."'";
        if($database->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["action"]="0";
          $response_Array["text"]='Block';
          $response_Array["class"]='btn btn-warning';
        }
      } break;
      default:{
        $response_Array["status"]=0;
      }
  }
  }
  return $response_Array;
}
function activeInactiveBanner($banner_id="",$flag=""){
  global $database;
  $response_Array=array();
  if(trim($banner_id)=="" || trim($flag)==""){
    $response_Array["status"]=0;
  }
  else{
    switch($flag){
    case 0:{
        $query="update "._EVENT_BANNER_." set is_active='".intval(0)."' where id='".$banner_id."'";
        if($database->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["action"]="1";
          $response_Array["text"]='In-active';
          $response_Array["class"]='btn btn-warning';
        }
      } break;
      case 1:{
        $query="update "._EVENT_BANNER_." set is_active='".intval(1)."' where id='".$banner_id."'";
        if($database->FetchQuery($query)){
          $response_Array["status"]=1;
          $response_Array["action"]="0";
          $response_Array["text"]='Active';
          $response_Array["class"]='btn btn-success';
        }
      } break;
      default:{
        $response_Array["status"]=0;
      }
  }
  }
  return $response_Array;
}
?>
