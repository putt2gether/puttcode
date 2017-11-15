<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else{
  require_once(dirname(__FILE__).'../../config/db_config.php');
  require_once(dirname(__FILE__).'/functions.php');
  global $database;
  $response_Array=array();
  $error=array();
  $action=isset($_POST['action'])?trim($_POST['action']):"";

    $today_date=date("Y-m-d H:i:s");
    switch($action){
      case 'add':{
        $event_id=isset($_POST['event_id'])?trim($_POST['event_id']):"";
        $banner_title=isset($_POST['banner_title'])?trim($database->escape($_POST['banner_title'])):"";
        $image_href=isset($_POST['image_href'])?trim($_POST['image_href']):"";
        $type=isset($_POST['type'])?$_POST['type']:array();
        $is_active=isset($_POST['is_active'])?trim($_POST['is_active']):"";
		$btype=isset($_POST['btype'])?$_POST['btype']:0;
		
		if($btype > 0) {
			$type = array($btype);
		}
		
        if($banner_title==""){
          $error["title_error"]="Banner title is empty.";
        }
        if($image_href=="" || filter_var($image_href, FILTER_VALIDATE_URL) === false){
          //$error["link_error"]="Banner link is empty or invalid.";
        }
        if(count($type)<=0){
          $error["location_error"]="Select at least one location.";
        }
        if($is_active==""){
          $error["status_error"]="Select status.";
        }
        if(isset($_FILES["banner_image"]) && $_FILES["banner_image"]["size"]>0){
          $image_name=$_FILES["banner_image"]["name"];
          $from_path=$_FILES["banner_image"]["tmp_name"];
          $to_path=_UPLOADS_BANNER_PATH_;
          $extension=explode(".",$image_name);
          $img_ext=end($extension);
          $img_ext_arr = array('jpg','gif','png','jpeg');
          if(!in_array($img_ext,$img_ext_arr)) {
            $error['image_error'] = "ERROR - Upload Image Only (".implode(',',$img_ext_arr)." extensions are allowed)";
          }
          else{
            $image_name=md5(time()).".".$img_ext;
            move_uploaded_file($from_path,$to_path.$image_name);
          }
        }
        if(count($error)==0){
          foreach($type as $type_v){
            $query='INSERT INTO '._EVENT_BANNER_.'(event_id,type,title,image_path,image_href,is_active,create_date,modified_date)
             values("'.$event_id.'","'.$type_v.'",'.$banner_title.',"'.$image_name.'","'.$image_href.'","'.$is_active.'","'.$today_date.'","'.$today_date.'")';
            $result=$database->FetchQuery($query);
          }
          if($result){
            $response_Array["status"]=1;
            $response_Array["message"]="Banner has been added Successfully.";
          }
          else{
            $response_Array["status"]=0;
            $response_Array["message"]="Unable to add banner.";
          }
        }
        else{
          $error["status"]=0;
          $response_Array=$error;
        }
      } break;
      case 'edit':{
        $event_id=isset($_POST['event_id'])?trim($_POST['event_id']):"";
        $banner_id=isset($_POST['banner_id'])?trim($_POST['banner_id']):"";
        $banner_title=isset($_POST['banner_title'])?trim($_POST['banner_title']):"";
        $image_href=isset($_POST['image_href'])?trim($_POST['image_href']):"";
        $type=isset($_POST['type'])?$_POST['type']:array();
        $btype=isset($_POST['btype'])?$_POST['btype']:0;
		
		if($btype > 0) {
			$type = array($btype);
		}
		
        $is_active=isset($_POST['is_active'])?trim($_POST['is_active']):"";
        if($banner_title==""){
          $error["title_error"]="Banner title is empty.";
        }
        if($image_href=="" || filter_var($image_href, FILTER_VALIDATE_URL) === false){
          $error["link_error"]="Banner link is empty or invalid.";
        }
        if(count($type)<=0){
          $error["location_error"]="Select at least one location.";
        }
        if($is_active==""){
          $error["status_error"]="Select status.";
        }
        if(isset($_FILES["banner_image"]) && $_FILES["banner_image"]["size"]>0){
          $image_name=$_FILES["banner_image"]["name"];
          $from_path=$_FILES["banner_image"]["tmp_name"];
          $to_path=_UPLOADS_BANNER_PATH_;
          $extension=explode(".",$image_name);
          $img_ext=end($extension);
          $img_ext_arr = array('jpg','gif','png','jpeg');
          if(!in_array($img_ext,$img_ext_arr)) {
            $error['image_error'] = "ERROR - Upload Image Only (".implode(',',$img_ext_arr)." extensions are allowed)";
          }
          else{
            $select_query="select image_path from "._EVENT_BANNER_." where id='".$banner_id."' and event_id='".$event_id."'";
            $image_name=$database->FetchSingleValue($select_query);
            $select_query="select count(id) from "._EVENT_BANNER_." where image_path='".$image_name."'";
            $image_count=$database->FetchSingleValue($select_query);
            if($image_count==1){
              unlink(_UPLOADS_BANNER_PATH_.$image_name);
            }
            $image_name=md5(time()).".".$img_ext;
            move_uploaded_file($from_path,$to_path.$image_name);
          }
        }
        if(count($error)==0){
            //$query="INSERT INTO "._EVENT_BANNER_."(event_id,type,title,image_path,image_href,is_active,create_date,modified_date)
            // values('".$event_id."','".$type_v."','".$banner_title."','".$image_name."','".$image_href."','".$is_active."','".$today_date."','".$today_date."')";
            $query="UPDATE "._EVENT_BANNER_." SET type='".$type[0]."',
            title='".$banner_title."',
            image_href='".$image_href."',
            is_active='".$is_active."',
            modified_date='".$today_date."'";
            if(isset($image_name)){
              $query.=",image_path='".$image_name."'";
            }
            $query.=" where event_id='".$event_id."' and id='".$banner_id."'";
            $result=$database->FetchQuery($query);
          if($result){
            $response_Array["status"]=1;
            $response_Array["message"]="Banner has been updated Successfully.";
          }
          else{
            $response_Array["status"]=0;
            $response_Array["message"]="Unable to updated banner.";
          }
        }
        else{
          $error["status"]=0;
          $response_Array=$error;
        }
      } break;
      case 'del':{
        $banner_id=trim($_POST['banner_id']);
        $select_query="select image_path from "._EVENT_BANNER_." where id='".$banner_id."'";
        $image_name=$database->FetchSingleValue($select_query);
        $select_query="select count(id) from "._EVENT_BANNER_." where image_path='".$image_name."'";
        $image_count=$database->FetchSingleValue($select_query);
        if($image_count==1){
          unlink(_UPLOADS_BANNER_PATH_.$image_name);
        }
        $data=$database->FetchQuery($select_query);
        $query="DELETE FROM "._EVENT_BANNER_." WHERE id='".$banner_id."'";
        $result=$database->FetchQuery($query);
        if($result){
          $response_Array['status']=1;
        }
        else{
          $response_Array['status']=0;
        }
      } break;
      case 'status':{
        $banner_id=isset($_POST["banner_id"])?trim($_POST["banner_id"]):"";
        $flag=isset($_POST["flag"])?trim($_POST["flag"]):"";
        $response_Array=activeInactiveBanner($banner_id,$flag);
      } break;
      default:{
        $response_Array["status"]="default";
      }

    }
  //print_r($response_Array);
  echo json_encode($response_Array);
  exit();
}
 ?>
