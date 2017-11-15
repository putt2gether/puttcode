<?php
class users{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = TABLE_USERS;
		$this->golfUser_table = TABLE_GOLF_USERS;
	}
	
	function login($filter=array()) {
		$fdata = $error = $Udata= array();
		$data = $filter;
		
		$email_id = isset($data['email']) ? trim($data['email']) : "";
		$pass = isset($data['password']) ? trim($data['password']) : "";
		//$token=isset($data["token"])? $data["token"] :"" ;
		$device_token=isset($data["device_token"])? $data["device_token"] :"" ;
		$device_os=isset($data["device_os"])? $data["device_os"]  :"";
		$authorization_key=isset($data["access_token"])? $data["access_token"]  :"";
		$email_id  = trim($email_id);
		if($email_id == '') {
			$fdata['Error']['msg']= "Please Enter Email Address";	
		}
		elseif($pass == '') {

			$fdata['Error']['msg'] = "Please Enter Password";	
		}
		
		elseif(!filter_var($email_id, FILTER_VALIDATE_EMAIL)) 
			{

			$fdata['Error']['msg'] = "Please Enter valid Email Address";	
		}
		else{
			
			$user_data='SELECT user_id FROM '.$this->golfUser_table.' WHERE user_name = "'.$email_id.'" '; 
			$featchUser1 =  $this->db->FetchSingleValue($user_data);
			
			if($featchUser1 <= 0){
				$fdata['Error']['msg'] = "Email Id Not Exist";	
			
			}else
			{
				$queryString = "select gu.user_id,gu.user_name,gu.full_name,gu.display_name,gu.latest_event_id,gu.format_id,up.photo_url,gu.authorization_key,up.self_handicap from ".$this->golfUser_table." as gu  left join ".$this->table." up on gu.user_id=up.user_id where gu.user_name = '".trim(($email_id))."' and gu.password = '".md5($pass)."'";
				$user_data  = $this->db->FetchQuery($queryString);

				if(isset($user_data[0]) && is_array($user_data[0]) && count($user_data[0])>0) {
					$user_data = $user_data[0];	
					// UPDATE token
					/* if($token != ''){
						updateToken($token,$user_data['user_id']);
					}
					 */
					// Update Authorization Key
					if($authorization_key != ''){
						$query12 = "update ".$this->golfUser_table." set authorization_key=".$authorization_key."  WHERE user_name!='".$email_id."'";
						$this->db->FetchQuery($query12);
					}
					if(isset($device_token) && $device_token != ''){
					$query1 = "update golf_user_app_devices set status=0 where token='".$device_token."' and os='".$device_os."' and user_id!='".$user_data['user_id']."'";
						$usrValues  = $this->db->FetchQuery($query1);
					}
					
					$uvalue = $eveValues = array();
					$uvalue['user_id']=$user_data['user_id'];
					$uvalue['user_name']=$user_data['user_name'];
$uvalue['self_handicap']=$user_data['self_handicap'];
					$uvalue['full_name']=$user_data['full_name'];
					$uvalue['display_name']=$user_data['display_name'];
					$uvalue['token']=$user_data['authorization_key'];
					$eveValues['latest_event_id']=($user_data['latest_event_id'] > 0)?$user_data['latest_event_id']:"";
					$eveValues['format_id']=($user_data['format_id'] > 0)?$user_data['format_id']:"";
					$uvalue['photo_url']=($user_data['photo_url']!="")? __BASE_URI__."images/profile/".$user_data['photo_url']:__BASE_URI__."images/profile/default.jpg";
					
					
					
					if($device_token!=''){
						$this->updateUserDeviceInfo($uvalue['user_id'],$device_token,$device_os);	
					}
					$userArray[] = $uvalue ;
					$evetArray[] = $eveValues ;
					$Udata= $userArray;

					$rdata = array();
					$rdata['status'] = '1';	
					$rdata['Full Name'] = $Udata;	
					$rdata['Event'] = $evetArray;
					$rdata['msg'] = 'Success Login';
					$fdata['data'] = $rdata;
					
				}else
				{
					$fdata['Error']['msg'] = "Please Enter valid Password";	
				}
			}
		}
			return $fdata ;			
	}

function register($data){
		$userInfo =array();
		$fullName=isset($data['fullname'])?trim($data['fullname']):"";
		$handicap=isset($data['handicap'])?$data['handicap']:"";
		$email=isset($data['email'])?$data['email']:"";
		$token=isset($data['token'])?$data['token']:"";
		$device_os=isset($data['device_os'])?$data['device_os']:"";
		$country=isset($data['country'])?$data['country']:"";
		$country_code=isset($data['country_code'])?$data['country_code']:"";
		$phone=isset($data['phone'])?$data['phone']:(isset($data['mobile']) ? $data['mobile'] : "");
		$device_token=isset($data['device_token'])?$data['device_token']:"";
		$password=isset($data['password'])?$data['password']:"";
$golf_course_id=isset($data['golf_course_id'])?$data['golf_course_id']:"0";
		$email = trim($email);
		$authorization_code= generateRandomString(5);
		$datetym= getDateTime();
		$authorization_key = md5($datetym + $authorization_code );
		$fullnamesmall=strtolower($fullName);
		$display_name=ucwords($fullnamesmall);//isset($data['display_name'])?$data['display_name']:"";
		$fullName=ucwords($fullnamesmall);
		$int = strlen($phone);
		$min = 8;
		$max = 10;
		$a[] = 'status:41';
		if($fullName  == ''){
			$a['message'] =  "Please Enter Full Name";
			$userInfo['Error'] =$a;	
				
		}
		elseif(strlen($fullName) < 4) {
			$a['message'] =  "Display name must be of at least 4 characters";
			$userInfo['Error'] =$a;	

		}
		elseif($handicap  == ''){
				$a['message'] =  "Please Enter Handicap Value";
			$userInfo['Error'] =$a;	

		}
		elseif($handicap > 30){
							$a['message'] = "Handicap Value must be between 0 and 30";
			$userInfo['Error'] =$a;	
			
		}
		elseif($country  == ''){
	$a['message'] = "Please Select country";
			$userInfo['Error'] =$a;	
			
		}
		elseif($phone  == ''){
		$a['message'] = "Please Enter Mobile Number";
			$userInfo['Error'] =$a;	
			
		}
		
		elseif (!(is_numeric($phone))) {
		$a['message'] = "Contact Number Must be numeric";
			$userInfo['Error'] =$a;	
		}		
			
		elseif (filter_var($int, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
		$a['message'] = "Contact number must be 8 or 10 digits";
			$userInfo['Error'] =$a ;
			
		}
				 	
		
		elseif($email  == ''){
$a['message'] = "Please Enter Email Address";
			$userInfo['Error'] =$a ;
				
		}
		elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$a['message'] = "please Enter Valid Email Address";
			$userInfo['Error'] =$a ;
			
		}
		elseif($password  == ''){
			$a['message'] = "Please Enter password";
			$userInfo['Error'] =$a ;
		}
		else
		{
			$sqlchk1="select display_name from golf_users where display_name!='' and display_name='".($display_name)."' limit 1"; 
			$queryResult1  = $this->db->FetchSinglevalue($sqlchk1);
			
			if(isset($queryResult1) && $queryResult1 != ''){
$a['message'] = "Display Name must be unique.This Name is already registered with putt2gether.";	
			$userInfo['Error'] =$a ;
				
			}
			
			$sqlchk="select is_new from golf_users where user_name='".($email)."' and is_new='0' limit 1"; 
			 $is_new  = $this->db->FetchSingleValue($sqlchk); 
			if(isset($is_new) && trim($is_new)  !=''){
				$a['message'] = "This email is already registered with putt2gether.";
			$userInfo['Error'] =$a ;
					
			}

			else
			{
if(!isset($userInfo['Error']) && !is_array($userInfo['Error']) && count($userInfo['Error']) <=0){

				$sqlchk4="select user_id from golf_users where user_name='".($email)."' and is_new='1' limit 1";
				$userId = $this->db->FetchSinglevalue($sqlchk4);
				
				if($userId > 0){
					$sqlQuery="update golf_users set is_new='0',user_name='".($email)."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',password='".md5($password)."',activation_password='".($password)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',golf_course_id= '".$golf_course_id."' where user_id='".$userId."'";	
					$updtaeUser =  $this->db->FetchQuery($sqlQuery);
				   
					$sqlQuery1="update user_profile set self_handicap='".$handicap."',country='".$country."' where user_id='".$userId."'";	
					$updtaeUser1 =  $this->db->FetchQuery($sqlQuery1);
					
					$sqlQuery2="update event_player_list set is_new='0' where player_id='".$userId."'";	
					$updtaeUser2 =  $this->db->FetchQuery($sqlQuery2);
					$this->updateUserDeviceInfo($userId,$device_token,$device_os);
							
				}else
				{
					$creation_date=date("Y-m-d H:i:s");
					$sqlQuery="insert into golf_users set user_name='".$email."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',password='".md5($password)."',activation_password='".($password)."',creation_date='".$creation_date."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',golf_course_id= '".$golf_course_id."'";	
					$addUser =  $this->db->FetchQuery($sqlQuery);
					$userId = $this->db->LastInsertId();
					
					if($userId){
						$sqlQuery1="insert into user_profile set user_id='".$userId."',self_handicap='".$handicap."',country='".$country."'";	
						$addUser2 = $this->db->FetchQuery($sqlQuery1);
						$this->updateUserDeviceInfo($userId,$device_token,$device_os);
					}
				}
				
				$link=__BASE_URI__;
				$subject="Welcome to Putt2gether";
				$message="Hi ".$fullName.",<br><br>Welcome to putt2gether - your own Live Golf Leaderboard!<br><br>You have registered successfully and we are delighted to have you on board.  We're sure that putt2gether will enrich your golfing experience. <br><br>
				We would love to hear from you regarding any feedback. Please drop us a mail at feedback@putt2gether.com!<br />
				<br />
				<div style='clear:both'></div>
				<a href='https://itunes.apple.com/in/app/putt2gether-live-leaderboard/id1002496721?mt=8' style='float:left; display:block'><img src='".__BASE_URI__."newsletter/app-store.png'  style='float:left;margin:0;padding:0;outline:none;'></a>
				<a href='https://play.google.com/store/apps/details?id=com.putt2gether'  style='float:left; display:block'><img src='".__BASE_URI__."newsletter/google-play.png'  style='float:left;margin:0;padding:0;outline:none;'></a><br><br>
				<div style='clear:both'></div>			
				<br><br>Happy Golfing,<br>Team putt2gether";
				sendregmail($email, $fullName, $subject, $message);						
				$uArr['user_id']=$userId;
				$uArr['token']=$authorization_key;
				$uArr['photo_url']=__BASE_URI__."profile/default.jpg";
				$uArr['message']="Register Succesfully";	
			
				$userInfo['User'] =$uArr;	
				}
			}						
		}
			return $userInfo ;
        }	
	
	function updateUserDeviceInfo($user_id,$device_token,$device_os,$logout=false)
	{
		$duser = array();
		
		 $today_date=date("Y-m-d H:i:s");
		 $status= ($logout) ? 0 : 1; 
		 $device_token=(isset($device_token)  && $device_token!='') ? $device_token :'';
		 $column = ($logout) ? "last_logout='".$today_date."'" : "last_login='".$today_date."'";
		 
		 $device_exist = "select id from golf_user_app_devices where user_id='".$user_id."' and  token='".trim($device_token)."' and os='".$device_os."'";
		$deviceIdValues  = $this->db->FetchSingleValue($device_exist);
	
		 if($deviceIdValues>0){
			 $queryString = "update golf_user_app_devices set status='$status',{$column} where id='".$deviceIdValues."'  ";
			 $user_data  = $this->db->FetchQuery($queryString);

			 }
			 else{
				 if($device_token!='' && $device_os!='' &&  trim($user_id)!='' && strlen($device_token)>=64){
				 $insert_device="insert into golf_user_app_devices set user_id='".$user_id."',token='".($device_token)."',os='".$device_os."',status='{$status}',create_date='".$today_date."',{$column} ";
				  $user_data  = $this->db->FetchQuery($insert_device);
				 }
			}
			$devicdata[] = array("user_id"=>$user_id,"device_token"=>$device_token,"device_os"=>$device_os);
			$duser['status'] = 1;
			$duser['data'] = $devicdata ;
				
			return $duser ;	
	}
	
	public function forgotPassword($filter=array()){
		$fdata = $error = array();
		$data = $filter;
		$email_id = isset($data['email']) ? trim($data['email']) : "";
		$token = isset($data['token']) ? trim($data['token']) : "";
$email_id  = trim($email_id);
		if($email_id == '') {
			$error[] = "Please Enter Email Address";
		}
		
		elseif(!filter_var($email_id, FILTER_VALIDATE_EMAIL)){
			$error[] = "Enter Valid Email Address";
		}
		else{
			$query = 'select user_id,display_name from '.$this->golfUser_table.' where user_name="'.$email_id.'"';
			$user_data  = $this->db->FetchQuery($query);
			if(!count($user_data)>0 && !count($user_data)==1){
				$error[] = "Email Address does not exist.";
			}
			
		}
		/* if($token == ''){
			$error[] = "Authorization key not found";
		}else{	
			$tokenId = isExistAccessToken($token,$user_data[0]['user_id']);
			if(!isset($tokenId) && $tokenId > 0){
				$error[] = "Token Id not Exist.";
			}
		} */
		if(count($error) == 0) { 
			$otp= generatePIN(6);
			$time=getDateTime();
			$exp_time=date("Y-m-d H:i:s",strtotime('+30 minutes',strtotime($time)));
			
			 $insert_Query="update ".$this->golfUser_table." set user_otp='".$otp."',exp_time='".$exp_time."',otp_status='".intval(0)."' WHERE user_id=".$user_data[0]['user_id']."";
			
			if($this->db->FetchQuery($insert_Query)){
				$messages="<table>";
				$messages.="<tr><td>Hello ".$user_data[0]["display_name"]." ,</td></tr>";
				$messages.="<tr><td>As per your request for reset password your OTP is : <strong>".$otp."</strong></td></tr>";
				$messages.="<tr><td>&nbsp;</td></tr>";
				$messages.="<tr><td>Thanks</td></tr>";
				$messages.="<tr><td>Putt2gether Team</td></tr>";
				$messages.="</table>";
				$subject = 'Forgot Password OTP';
				$mail_status=sendEmail($messages,$email_id,$user_data[0]["display_name"],$subject);
				$fdata = array();
				$fdata['status'] = '1';
$fdata['user_id'] = $user_data[0]['user_id'];
				$fdata['Success']='your Forgot Password Request Accepted. Please Check Given Email- Id';
				
			}
		}
		else {
			$fdata['status'] = '0';
			$fdata['Error'] = implode(' ',$error);
		}
		return $fdata;
	}

	public function verifyotp($filter=array()){
		$fdata = $error = array();
		$data = $filter;
		$user_id = isset($data['user_id']) ? trim($data['user_id']) : "";
		$otp = isset($data['otp']) ? trim($data['otp']) : "";
		if($user_id == '' || $otp == ''){
			$error[] = "Please Enter Otp";
		}
		else{
			$chk_Otp='select user_id,exp_time from '.$this->golfUser_table.' where user_id="'.$user_id.'" and user_otp="'.$otp.'" and otp_status="'.intval(0).'"';
			$user_data  = $this->db->FetchQuery($chk_Otp);
		  if(!count($user_data)>0 && !count($user_data)==1){
				$error[] = "Please Enter Valid OTP.";
			}
			else{
				if(strtotime(trim($user_data[0]["exp_time"]))<=strtotime(trim(getDateTime()))){
					$error[] = "Your OTP has been expired.";
				}
			}
		}
		if(count($error)==0){
			$update_Query="update ".$this->golfUser_table." set otp_status='".intval(1)."' where user_id='".$user_id."' and user_otp='".$otp."'";
			if($this->db->FetchQuery($update_Query)){
				$fdata = array();
				$fdata['status'] = '1';
				$fdata['message']="OTP Verified. Now you can change your password";
			}
		}
		else {
			$fdata['status'] = '0';
			$fdata['error'] = implode(' ',$error);
		}
		return $fdata;
	}

	public function updatePassword($filter=array()){
	$fdata = $error = array();
	$data = $filter;
	$otp = isset($data['otp']) ? trim($data['otp']) : "";
	$user_id = isset($data['user_id']) ? ($data['user_id']) : "0";
	$new_password = isset($data['new_password']) ? trim($data['new_password']) : "";
	$confirm_password = isset($data['confirm_password']) ? trim($data['confirm_password']) : "";
	$token = isset($data['token']) ? trim($data['token']) : "";
	if(!(is_numeric($user_id))){
$error[] = "User Id must be numeric";
}
	if($new_password=='' || $confirm_password=='' || $otp=='' || $user_id==''){
		$error[] = "All Field Are Required";
	}
	if(strlen($confirm_password) <= 5){
		$error[] = "Password should be greater then 6 character";
	}
	if($new_password !=  $confirm_password){
		$error[] = "New Password and Confirm Password Must Be same ";
	}
	if(count($error)==0){
		$password=md5($confirm_password);
		$chk_Otp='select user_id from '.$this->golfUser_table.' where user_id="'.$user_id.'" and user_otp="'.$otp.'" and otp_status="'.intval(1).'"';  
		$user_data  = $this->db->FetchSingleValue($chk_Otp);
		if($user_data>0){ 
		$update_Query="update ".$this->golfUser_table." set password='".$password."', activation_password='".$confirm_password."' where user_id='".$user_id."'"; 
		$uue = $this->db->FetchQuery($update_Query);
			
				$fdata = array();
				$fdata['status'] = '1';
				$fdata['message']="Password has been changed successfully.";
			
			
		}
		else{
			$error[]="Otp is wrong or Otp is not verified.";
			$fdata['status'] = '0';
			$fdata['message'] = implode(' ',$error);
		}
	}
	else {
		$fdata['status'] = '0';
		$fdata['message'] = implode(' ',$error);
	}
		return $fdata;
	}
		function getUserProfileDetail($data)
    {

          $fdata = array();
		$user_id=isset($data['user_id'])?$data['user_id']:"";
		$flag=isset($data['flag'])?$data['flag']:"0";
	
	if(!(is_numeric($user_id))){
			$fdata['status'] = '0';
			
			$fdata['message'] = 'Invalid User Id';
		}
		if($user_id > 0){
			$query = "Select user_id from golf_users where user_id = ".$user_id."";
			$isExist  = $this->db->FetchSingleValue($query);
			if(isset($isExist) && $isExist >0){
		$sqlchk="select count(grp.grp_member_id) as total_group_member,g.user_id,g.user_name,g.full_name,g.display_name,g.registered_mobile_number as contact_no,g.country_code as country_code,u.self_handicap as handicap_value,u.address,u.country,u.designation,u.photo_url,golf.golf_course_name from golf_users g left join user_profile u ON u.user_id=g.user_id left join golf_course golf ON golf.golf_course_id = g.golf_course_id LEFT JOIN golf_group_members as grp ON grp.user_id = g.user_id and grp.user_id='".$user_id."' and grp.is_active =1 where  g.user_id='".$user_id."' limit 1"; 
			$rowValues  = $this->db->FetchRow($sqlchk);
		
			if(is_array($rowValues) && count($rowValues) > 0) 
			{
$purl = (isset($rowValues['photo_url']) && $rowValues['photo_url'] != '')?$rowValues['photo_url']:'';
if($purl != ''){
$rowValues['thumb_url']=DISPLAY_PROFILE_PATH.'thumb/'.$purl;	
$rowValues['photo_url']=DISPLAY_PROFILE_PATH.$purl;
}else{
$rowValues['thumb_url']='';			
$rowValues['photo_url']='';
}
$rowValues['golf_course_name']=(isset($rowValues['golf_course_name']) && $rowValues['golf_course_name'] !='')?$rowValues['golf_course_name']:'';
			if(isset($flag) && $flag != 1){
			$fdata['status'] = '1';
			$fdata['data'] = $rowValues;
			$fdata['message'] = 'User Details';
			}else{
			$fdata['status'] = '1';
			$fdata['data'] = $rowValues;
			}
			
			}else{
			$fdata['status'] = '0';
			
			$fdata['message'] = 'User not exist in database';
			
			}
		}else{
			$fdata['status'] = '0';
			
			$fdata['message'] = 'User not exist in database';
		}			
		}else{
			$fdata['status'] = '0';
			
			$fdata['message'] = 'Required Field Not Found';
		}
	
		return $fdata ;
}
/*
function socialLogin($data){
		$fdata =array();
		$fullName=isset($data['full_name'])?$data['full_name']:"";
		$handicap=0;
		$country_code=0;
		$country='';
		$email=isset($data['email_id'])?$data['email_id']:"";
		$token=isset($data['token'])?$data['token']:"";
		$device_os=isset($data['device_os'])?$data['device_os']:"";
		$phone=isset($data['mobile_number'])?$data['mobile_number']:"";
		$device_token=isset($data['device_token'])?$data['device_token']:"";
		$photo_url=isset($data['photo_url'])?$data['photo_url']:"";
		$facebook_id=isset($data['facebook_id'])?$data['facebook_id']:"";
		$authorization_code= generateRandomString(5);
		$datetym= getDateTime();
		$authorization_key = md5($datetym + $authorization_code );
		$fullnamesmall=strtolower($fullName);
		$display_name=ucwords($fullnamesmall);//isset($data['display_name'])?$data['display_name']:"";
		$fullName=ucwords($fullnamesmall);
		$int = strlen($phone);
		$min = 8;
		$max = 10;
		$email = trim($$email);
				 $sqlchk4="select user_id from golf_users where (user_name='".($email)."' OR facebook_id='".$facebook_id."') and is_new='0'";
				 $userId = $this->db->FetchSinglevalue($sqlchk4);
				
				if($userId > 0){
					$sqlQuery="update golf_users set is_new='0',user_name='".($email)."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',password='".md5($password)."',activation_password='".($password)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',facebook_id='".$facebook_id."' where user_id='".$userId."'";	
					$updtaeUser =  $this->db->FetchQuery($sqlQuery);
				   
					$sqlQuery1="update user_profile set self_handicap='".$handicap."',country='".$country."',photo_url='".$photo_url."' where user_id='".$userId."'";	
					$updtaeUser1 =  $this->db->FetchQuery($sqlQuery1);
					
					$sqlQuery2="update event_player_list set is_new='0' where player_id='".$userId."'";	
					$updtaeUser2 =  $this->db->FetchQuery($sqlQuery2);
					
					$this->updateUserDeviceInfo($userId,$device_token,$device_os);
							
				}else
				{
					$creation_date=date("Y-m-d H:i:s");
					$sqlQuery="insert into golf_users set user_name='".$email."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',password='".md5($password)."',activation_password='".($password)."',creation_date='".$creation_date."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',facebook_id='".$facebook_id."'";	
					$addUser =  $this->db->FetchQuery($sqlQuery);
					$userId = $this->db->LastInsertId();
					
					if($userId){
						$sqlQuery1="insert into user_profile set user_id='".$userId."',self_handicap='".$handicap."',country='".$country."', photo_url='".$photo_url."'";	
						$addUser2 = $this->db->FetchQuery($sqlQuery1);
						$this->updateUserDeviceInfo($userId,$device_token,$device_os);
					}
				}
				
					$queryString = "select latest_event_id,format_id from ".$this->golfUser_table." where user_id = '".$userId."'";
					$user_data  = $this->db->FetchRow($queryString);
					$uvalue = $eveValues = array();
					$uvalue['user_id']=$userId;
					$uvalue['user_name']=$display_name;
					$uvalue['self_handicap']=$handicap;
					$uvalue['full_name']==$display_name;
					$uvalue['display_name']=$display_name;
					$uvalue['token']=$authorization_key;
					$eveValues['latest_event_id']=($user_data['latest_event_id'] > 0)?$user_data['latest_event_id']:"";
					$eveValues['format_id']=($user_data['format_id'] > 0)?$user_data['format_id']:"";
					$uvalue['photo_url']=($user_data['photo_url']!="")? $user_data['photo_url']:"";

					$userArray[] = $uvalue ;
					$evetArray[] = $eveValues ;
					$Udata= $userArray;

					$rdata = array();
					$rdata['status'] = '1';	
					$rdata['Full Name'] = $Udata;	
					$rdata['Event'] = $evetArray;
					$rdata['msg'] = 'Success Login';
					$fdata['data'] = $rdata;
			                return $fdata ;
        }
*/
	
function socialLogin($data){
		$fdata =array();
		$fullName=isset($data['full_name'])?$data['full_name']:"";
		$handicap=0;
		$country_code=0;
		$country='';
		$email=isset($data['email_id'])?$data['email_id']:"";
		$token=isset($data['token'])?$data['token']:"";
		$device_os=isset($data['device_os'])?$data['device_os']:"";
		$phone=isset($data['mobile_number'])?$data['mobile_number']:"";
		$device_token=isset($data['device_token'])?$data['device_token']:"";
		$photo_url=isset($data['photo_url'])?$data['photo_url']:"";
		$facebook_id=isset($data['facebook_id'])?$data['facebook_id']:"";
		$authorization_code= generateRandomString(5);
		$datetym= getDateTime();
		$authorization_key = md5($datetym + $authorization_code );
		$fullnamesmall=strtolower($fullName);
		$display_name=ucwords($fullnamesmall);//isset($data['display_name'])?$data['display_name']:"";
		$fullName=ucwords($fullnamesmall);
		$int = strlen($phone);
		$min = 8;
		$max = 10;
		//$photo_url= str_replace("data:image/jpeg;base64,", "", $photo_url);
				 $sqlchk4="select user_id from golf_users where (user_name='".($email)."' OR facebook_id='".$facebook_id."') and is_new='0'";
				 $userId = $this->db->FetchSinglevalue($sqlchk4);
				
				if($userId > 0){
/*if($photo_url!="")
			{
				$base64_string=$photo_url;
				$photo_url=time()."_".md5(time().$userId).".jpg";
				ob_clean();
				$img_str = base64_decode($base64_string);
				
				$im = true;
				if ($im !== false) {
					file_put_contents(UPLOADS_PROFILE_PATH.$photo_url, $img_str);
					resize_image(UPLOADS_PROFILE_PATH.$photo_url,UPLOADS_PROFILE_PATH."thumb/".$photo_url."",320,320);
					
				}
				else {
					//echo 'correupted image';die;
				}
			}else{
					$photo_url= '';
				} */
					$sqlQuery="update golf_users set is_new='0',user_name='".($email)."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',password='".md5($password)."',activation_password='".($password)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',facebook_id='".$facebook_id."' where user_id='".$userId."'";	
					$updtaeUser =  $this->db->FetchQuery($sqlQuery);
				   
					$sqlQuery1="update user_profile set self_handicap='".$handicap."',country='".$country."',photo_url='".$photo_url."' where user_id='".$userId."'";	
					$updtaeUser1 =  $this->db->FetchQuery($sqlQuery1);
					
					$sqlQuery2="update event_player_list set is_new='0' where player_id='".$userId."'";	
					$updtaeUser2 =  $this->db->FetchQuery($sqlQuery2);
					
					$this->updateUserDeviceInfo($userId,$device_token,$device_os);
							
				}else
				{
					$creation_date=date("Y-m-d H:i:s");
					$sqlQuery="insert into golf_users set user_name='".$email."',display_name='".($display_name)."',full_name='".($fullName)."',alternate_email_id='".($email)."',token='".$token."',device_token='".($device_token)."',device_os='".$device_os."',password='".md5($password)."',activation_password='".($password)."',creation_date='".$creation_date."',country_code='".$country_code."',registered_mobile_number='".$phone."',authorization_key= '".$authorization_key."',facebook_id='".$facebook_id."'";	
					$addUser =  $this->db->FetchQuery($sqlQuery);
					$userId = $this->db->LastInsertId();
					
					if($userId){
						$sqlQuery1="insert into user_profile set user_id='".$userId."',self_handicap='".$handicap."',country='".$country."', photo_url='".$photo_url."'";	
						$addUser2 = $this->db->FetchQuery($sqlQuery1);
						$this->updateUserDeviceInfo($userId,$device_token,$device_os);
					}
				}
				
					$queryString = "select latest_event_id,format_id from ".$this->golfUser_table." where user_id = '".$userId."'";
					$user_data  = $this->db->FetchRow($queryString);
					
					$uvalue = $eveValues = array();
					$uvalue['user_id']=$userId;
					$uvalue['user_name']=$email;
					$uvalue['self_handicap']=$handicap;
					$uvalue['full_name']=$display_name;
					$uvalue['display_name']=$display_name;
					$uvalue['token']=$authorization_key;
					$eveValues['latest_event_id']=($user_data['latest_event_id'] > 0)?$user_data['latest_event_id']:"";
					$eveValues['format_id']=($user_data['format_id'] > 0)?$user_data['format_id']:"";
					//$uvalue['photo_url']=DISPLAY_PROFILE_PATH.'thumb/'.$photo_url;
$uvalue['photo_url'] = $photo_url ;
					$userArray[] = $uvalue ;
					$evetArray[] = $eveValues ;
					$Udata= $userArray;

					$rdata = array();
					$rdata['status'] = '1';	
					$rdata['Full Name'] = $Udata;	
					$rdata['Event'] = $evetArray;
					$rdata['msg'] = 'Success Login';
					$fdata['data'] = $rdata;
				
									
		
			return $fdata ;
        }

function updateUserHandicap($data){
	
		$fdata = array();
		$userId=$data['user_id'];
		$eventId=$data['event_id'];
		$handicap_value=$data['handicap_value'];
		
		if($userId==""  && $eventId=="" && $handicap_value==""){
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field can not blank.';	
		}
		elseif($handicap_value > 30){
			$fdata['status'] = '0';
			$fdata['message'] = 'Handicap Value must be between 0 and 30';
		}
		else
		{
			
		$queryString = "select golf_course_id,admin_id from event_list_view where event_id =".$eventId."";
		$result  = $this->db->FetchRow($queryString);
		$golf_course_id = (isset($result['golf_course_id']) && $result['golf_course_id'] >0)?$result['golf_course_id']:0;
		
		if($golf_course_id > 0)
		{
			$admin_id =$result['admin_id'];
            $queryString = "select event_id,participant_id,handicap_value from golf_course_user_handicap where participant_id = '".trim($userId)."' and event_id = ".$eventId." and golf_course_id= ".$golf_course_id;
			$row_arr  = $this->db->FetchRow($queryString);
			$participant_id = (isset($row_arr['participant_id']) && $row_arr['participant_id'] >0)?$row_arr['participant_id']:0;
			$update_handicap=false;
			if ($participant_id > 0 ) 
			{ 
				if($row_arr['handicap_value']!=$handicap_value) {
					$queryString = "update golf_course_user_handicap set handicap_value='".$handicap_value."' where event_id='".trim($eventId)."' and participant_id='".$userId."' and golf_course_id= ".$golf_course_id;
					$result  = $this->db->FetchQuery($queryString);
					$update_handicap=true;
				}
			}
			else{ 
				$queryString = "insert into golf_course_user_handicap(";
				$queryString .= " event_id, golf_course_id,participant_id, handicap_value,ip_address)";
				$queryString .= " values (";
				$queryString .= "'".trim($eventId)."',";
				$queryString .= "'".trim($golf_course_id)."',";
				$queryString  .= $userId.",";
				$queryString .= "'".$handicap_value."',";
				$queryString  .= "'".$_SERVER['REMOTE_ADDR'];
				$queryString  .="')"; //echo $queryString;
				$result  = $this->db->FetchQuery($queryString);
				$update_handicap=true;
			}
			if($update_handicap) {
				if($admin_id!=$userId){
					$noti_calss=new createNotification();
					$noti_calss->generatePushNotification($eventId,9,$admin_id,$userId,$handicap_value);
					sendPushNotification();
				}
				$status = '1';
				$message = "Handicap Updated successfully.";
			}
			else {
				$status = '0';
				$message = "Enter Different Handicap Value.";
				
			}
			$fdata['status'] = $status;
			$fdata['message'] = $message;
			}
			else{
				
			$fdata['status'] = '0';
			$fdata['message'] = 'Event not exists in database.';
			}
			}
			return $fdata;
		}

function updateProfile($data)
	{
	
		$user_id=isset($data['user_id'])?$data['user_id']:"";
		$full_name=isset($data['full_name'])?$data['full_name']:"";
		$contact_number=isset($data['contact_number'])?$data['contact_number']:"";
		$country_code=isset($data['country_code'])?$data['country_code']:"";
		$country=isset($data['country'])?$data['country']:"";
		$password=isset($data['password'])?$data['password']:"";
		$profile_img=isset($data['profile_image'])?$data['profile_image']:"";
		$handicap=isset($data['handicap_value'])?$data['handicap_value']:"";
		$email=isset($data['email'])?$data['email']:"";
		$int = strlen($contact_number);
$golf_course_id=isset($data['golf_course_id'])?$data['golf_course_id']:"0";
		$min = 8;
		$max = 10;
		$status = 0;
$profile_img = str_replace("data:image/jpeg;base64,", "", $profile_img);
		if($full_name  == ''){
			$message =  "Please Enter Full Name";
		
				
		}
		elseif(strlen($full_name) < 4) {
			$message =  "Display name must be of at least 4 characters";
			
		}
		elseif($handicap  == ''){
			$message =  "Please Enter Handicap Value";
			

		}
		elseif($handicap > 30){
			$message = "Handicap Value must be between 0 and 30";
				
			
		}
		elseif($country  == ''){
			$message = "Please Select country";
		
		}
		elseif($contact_number  == ''){
			$message = "Please Enter Mobile Number";
	
			
		}
		
		elseif (!(is_numeric($contact_number))) {
		$message = "Contact Number Must be numeric";
				
		}		
			
		elseif (filter_var($int, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max))) === false) {
		$message = "Contact number must be 8 or 10 digits";
			
		}
		if($email == '') {
			$message= "Please Enter Email Address";	
		}
		elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) 
			{

			$message = "Please Enter valid Email Address";	
		}
	
		else{
			
			$sqlchk="select user_id from ".$this->golfUser_table." where user_id='".$user_id."'";
			$isExist  = $this->db->FetchSingleValue($sqlchk);
			if($isExist > 0) 
			{
				
			$sqlchk1="select user_id from golf_users where display_name='".($full_name)."' and user_id !='".$isExist."'";
			$queryResult1  = $this->db->FetchSinglevalue($sqlchk1);
		
			if(isset($queryResult1) && $queryResult1  !=''){
				$message = "Display Name must be unique.This Name is already registered with putt2gether.";	
			}
			else{
			$sqlchk1="select user_id from golf_users where user_name='".($email)."' and user_id != '".$isExist."'";
			$queryResult1  = $this->db->FetchSinglevalue($sqlchk1);
		
			if($queryResult1 > 0){
				$message = "Email-Id Already Exist.";	
			}else{
			$updcon='';$updcon1='';	
			
			if($profile_img!="")
			{
				$base64_string=$profile_img;
				$profile_img=time()."_".md5(time().$isExist).".jpg";
				ob_clean();
				$img_str = base64_decode($base64_string);
				
				$im = true;
				if ($im !== false) {
					file_put_contents(UPLOADS_PROFILE_PATH.$profile_img, $img_str);
					resize_image(UPLOADS_PROFILE_PATH.$profile_img,UPLOADS_PROFILE_PATH."thumb/".$profile_img."",320,320);
					
				}
				else {
					//echo 'correupted image';die;
				}
			}else{
					$profile_img= '';
				}
				
			if($password!=""){
				$updcon.=" ,password='".md5($password)."',activation_password='".($password)."'";
			}
			if($email!=""){
				$updcon.=" ,user_name='".($email)."'";
			}
			
			$sqlQuery="update ".TABLE_GOLF_USERS." set last_modified_date=now(),full_name='".$full_name."', display_name='".$full_name."', registered_mobile_number='".$contact_number."',golf_course_id='".$golf_course_id."', country_code='".$country_code."' ".$updcon." where user_id='".$user_id."'";	
			
			$this->db->FetchQuery($sqlQuery);
			
			$sqlQuery1="update user_profile set self_handicap='".$handicap."',photo_url='".$profile_img."', country='".addslashes($country)."' ".$updcon1." where user_id='".$user_id."'";	
			$this->db->FetchQuery($sqlQuery1);
			$status = '1';
			$message = 'Profile Updated.';
			
			}
			}
			}else{
			$status = '0';
			$message = 'User not exist in database.';
			}				
		}
		$fdata['status'] = $status;
		$fdata['message'] = $message;
		return $fdata ;
	}

public function editprofile($filter=array()){
	$fdata = $error = array();
	$data = $filter;
	$user_id = isset($data['user_id']) ? trim($data['user_id']) : "";
	
	$user_image = isset($data['user_image']) ? trim($data['user_image']) : "";
	if($user_image!=""){
				$base64_string=$user_image;
				$user_image=time()."_".md5(time().$user_id).".jpg";
			}
	
		
			if(file_put_contents(UPLOADS_PROFILE_PATH.$user_image, base64_decode($base64_string))){
			 resize_image(UPLOADS_PROFILE_PATH.$user_image,UPLOADS_PROFILE_PATH."thumb/$user_image",320,320);
			}
		
	
  return $fdata;
}

function getPlayerEventStatus($data){
			$fdata = array();
				$format ="json";
				$user_id=(isset($data['player_id']) && $data['player_id'] > 0)?$data['player_id']:0;
				$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:0;
				if($user_id=='0'  ||  $event_id=='0' ){
					$fdata['status'] = '0';
					$fdata['message'] = 'Required field not found';
						
				}else{
					$sqlchk="select event_list_id,is_accepted from event_player_list where player_id='".$user_id."' and event_id='".$event_id."' and add_player_type='1' ";
					$rowValues4 =$this->db->FetchRow($sqlchk);
				
					$sqlchk2="select event_list_id,is_accepted from event_player_list where player_id='".$user_id."' and event_id='".$event_id."' and add_player_type='0' ";
					$queryResult2 =$this->db->FetchRow($sqlchk2);
					if(isset($rowValues4) && is_array($rowValues4) && count($rowValues4) > 0){
							 	$event_list_id=($rowValues4['event_list_id'] > 0)?$rowValues4['event_list_id']:"";
								$is_accepted=$rowValues4['is_accepted'];
								if($is_accepted==1 || $is_accepted=='1'){$stat_text="accepted";}elseif($is_accepted==2 || $is_accepted=='2'){$stat_text="rejected";}elseif($is_accepted==0  || $is_accepted=='0'){$stat_text="pending";}
					}
					elseif(is_array($queryResult2) && count($queryResult2) > 0){
						$event_list_id=($queryResult2['event_list_id'] > 0)?$queryResult2['event_list_id']:"";
						$is_accepted="4";
						$stat_text="already invited";
						}else{
						$is_accepted="3";
						$stat_text="new";
						}
					$succArray=array("event_id"=>$event_id,"player_id"=>$user_id,"is_accepted"=>$is_accepted,'event_list_id'=>$event_list_id,"is_accepted_text"=>$stat_text);
$fdata['status'] ='1';				
$fdata['data'] =$succArray;				
$fdata['message'] ='Success';				
															
				}
				return $fdata;
}

function ViewUserProfile($data){
		$fdata = array();
		$user_id=isset($data['user_id'])?$data['user_id']:"0";
	
		if($user_id <= 0){
			$fdata['status'] = '0';
			$fdata['message'] = 'Invalid User Id';
		}
		else{
				$events = new Events;
				$stats = new Stats;
				$fdata = $this->getUserProfileDetail(array("user_id"=>$user_id,"flag"=>1));
				if($fdata['status'] == 1){
				//$fdata['events_history'] = $events->MyEventHistory(array("user_id"=>$user_id,"flag"=>1));
				$score = $stats->getStatsPiChart(array("user_id"=>$user_id,"flag"=>1));
				unset($score['no_of_eagle']); 
				unset($score['no_of_birdies']); 
				unset($score['no_of_pars']); 
				unset($score['no_of_double_bogeys']); 
				unset($score['curent_position']); 
				unset($score['no_of_bogeys']); 
				$fdata['average_score'] = (isset($score['gross_score']) && $score['gross_score'] >0)?$score['gross_score']:0;
				}
			}
		
		return $fdata;
	}

}
?>