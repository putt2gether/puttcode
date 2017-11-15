<?php
class createNotification
    {
	public $db,$data = array();
	
	function __construct()
		{
		global $database;
		$this->db = $database;
        }
        function generatePushNotification($event_id,$msg_id,$player_id_from,$player_id_to,$handicap_value=0,$message='')
        {

          
			switch($msg_id)
			{
				case 1:
					$this->generateTyp1Msg($event_id);
				break;
				case 2:
					$this->generateTyp2Msg($event_id);
				break;
				case 3:
					$this->generateTyp3Msg($event_id,$player_id_to);
				break;
				case 4:
					$this->generateTyp4Msg($event_id);
				break;
				case 5:
					$this->generateTyp5Msg($event_id,$player_id_from,$player_id_to,$message);
				break;
				case 6:
					$this->generateTyp6Msg($event_id,$player_id_to);
				break;
				case 7:
					$this->generateTyp7Msg($event_id,$player_id_from);
				break;
				case 8:
					$this->generateTyp8Msg($event_id,$player_id_from);				
				break;
				case 9:
					$this->generateTyp9Msg($event_id,$player_id_from,$player_id_to,$handicap_value);				
				break;
			}
		}
		
		function generateTyp1Msg($event_id)
		{
			$queryString = "select event_name from event_list_view";
			$queryString .= " where event_id =".$event_id;
			$event_name = $this->db->FetchSingleValue($queryString);
			
			$notification_text = "Your event '".$event_name."' is scheduled to start in 15 mins";
			$queryString = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString .= " where user_id ";
			$queryString .= " in ";
			$queryString .= " (select player_id from event_player_list where event_id =".$event_id;
			$queryString .= " and is_accepted='1' order by player_id)";
			
			$queryResult = $this->db->FetchQuery($queryString);
			
			if(count($queryResult) > 0) 
			{
				$last_ids_arr = array();
				foreach($queryResult as $i=>$rowValues)
				{


					$not_code= array("id"=>$event_id,"str"=>"start");
					$not_code_json=json_encode($not_code);
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."',";
					$queryStringIns .= '"'.$this->db->escape($rowValues['device_token']).'",';
					$queryStringIns .= '"'.$this->db->escape($rowValues['device_os']).'",';
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .=  "'".$not_code_json."',";
					$queryStringIns .= "0,now(),1)";
                   /// echo $queryStringIns;
				   $queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_ids_arr[] = $this->db->LastInsertId();
				}
				$this->sendFCMPush($last_ids_arr,1);
			} 
			
			return $notification_text;
		}
		
		function generateTyp2Msg($event_id)
		{
			$queryString = "select event_name from event_list_view";
			$queryString .= " where event_id =".$event_id;
			$event_name = $this->db->FetchSingleValue($queryString);
			
			$notification_text = "Your event '".$event_name."' has started. Tap to enter score";
			$queryString = "select user_id, registered_mobile_number, device_token,device_os from golf_users ";
			$queryString .= " where user_id ";
			$queryString .= " in ";
			$queryString .= " (select player_id from event_player_list where event_id =".$event_id;
			$queryString .= " and is_accepted='1' and player_id != event_admin_id order by player_id)";
			$queryResult = $this->db->FetchQuery($queryString);
			if(count($queryResult) > 0) 
			{
				$last_ids_arr = array();
				foreach($queryResult as $i=>$rowValues)
				{
					
					$not_code= array("id"=>$event_id,"str"=>"startnow");
					$not_code_json=json_encode($not_code);

					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."',";
					$queryStringIns .= '"'.$this->db->escape($rowValues['device_token']).'",';
					$queryStringIns .= "'".$rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "'".$not_code_json."',";
					$queryStringIns .= "0,now(),2)";
					
										
				//	echo $queryStringIns ; 	
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_ids_arr[] = $this->db->LastInsertId();
				}
				$this->sendFCMPush($last_ids_arr,2);
			} 
		}
		
		function generateTyp3Msg($event_id,$user_id)
		{
			 $queryString = "select event_name,golf_course_name,date_format(event_start_date_time,'%d/%m/%Y') as event_date,admin_id from event_list_view";
			$queryString .= " where event_id =".$event_id;
			
			$result = $this->db->FetchRow($queryString);
			$event_name = $result['event_name'];
			$golf_course_name =$result['golf_course_name'];
			$event_date = $result['event_date'];
			$admin_id = $result['admin_id'];
			
			///$event_date = date("d/m/Y",strtotime($event_date));
			
			$queryString2 = "select full_name from golf_users where user_id=$admin_id";
			$full_name = $this->db->FetchSingleValue($queryString2);

			$notification_text = "You have been invited by ".$full_name." to participate in '".$event_name."' at '".$golf_course_name."' on '".$event_date."'. Tap here to Accept / Decline";
			
			$queryString1 = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString1.= " where user_id ";
			
			$queryString1.= " = '{$user_id}' ";
			$queryResult1 = $this->db->FetchQuery($queryString1);
		
			if(count($queryResult1) > 0) 
			{
				$last_ids_arr = array();
				foreach($queryResult1 as $i=>$rowValues)
				{
					if($rowValues['user_id']==$admin_id){continue;}
					$not_code= array("id"=>$event_id,"str"=>"invite");
					$not_code_json=json_encode($not_code);
					
					
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."',";
					$queryStringIns .= '"'.$this->db->escape($rowValues['device_token']).'",';
					$queryStringIns .= "'".$rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "'".$not_code_json."',";
					$queryStringIns .= "0,now(),3)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_ids_arr[] = $this->db->LastInsertId();
				}
				//$this->sendFCMPush($last_ids_arr,3);
			} 
			
			return $notification_text;
		}
		
		function generateTyp4Msg($event_id)
		{
			$queryString = "select event_name from event_list_view";
			$queryString .= " where event_id =".$event_id;
			$event_name = $this->db->FetchSingleValue($queryString);
			
			$notification_text = "The Event '".$event_name."' has now concluded. Tap here to view the leaderboard.";

			$queryString = "select user_id, registered_mobile_number, device_token,device_os from golf_users ";
			$queryString .= " where user_id ";
			$queryString .= " in ";
			$queryString .= " (select player_id from event_player_list where event_id =".$event_id;
			$queryString .= " and is_accepted='1' order by player_id)";
			
			$queryResult = $this->db->FetchQuery($queryString);
			
			if(count($queryResult) > 0) 
			{
				$last_ids_arr = array();
				foreach($queryResult as $i=>$rowValues)
				{
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."',";
					$queryStringIns .= '"'.$this->db->escape($rowValues['device_token']).'",';
					$queryStringIns .= '"'.$rowValues['device_os'].'",';
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "0,now(),4)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_ids_arr[] = $this->db->LastInsertId();
				}
				$this->sendFCMPush($last_ids_arr,4);
			} 
		}
		
		function generateTyp5Msg($event_id,$player_id_from,$player_id_to,$message='')
		{ //echo $event_id.'______'.$player_id_from.'______'.$player_id_to;die;
		
			if($message == '') {
				$queryString = "select full_name from golf_users";
				$queryString .= " where user_id =".$player_id_from;
				$full_name = $this->db->FetchSingleValue($queryString);
				
				$notification_text = $full_name." has delegated his score entry to you. Tap here to enter his/her score.";
			}
			else {
				$notification_text = $message;
			}
			$queryString = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString .= " where user_id =".$player_id_to." ";
			$rowValues= $this->db->FetchRow($queryString);
			
			
			if(count($rowValues) > 0) 
			{
				$last_ids_arr = array();
				$queryStringIns = " insert into push_notification_user_list";
				$queryStringIns .= " (user_id, event_id,  ";
				$queryStringIns .= "device_udid,device_os,notification_text,is_read,creation_time,push_type)";
				$queryStringIns .= " values('";
				$queryStringIns .= $rowValues['user_id']."','";
				$queryStringIns .= $event_id."','";
				$queryStringIns .= $rowValues['device_token']."','";
				$queryStringIns .= $rowValues['device_os']."',";
				$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
				$queryStringIns .= "0,now(),5)";
				$queryResultIns = $this->db->FetchRow($queryStringIns);
				$last_ids_arr[] = $this->db->LastInsertId();
				$this->sendFCMPush($last_ids_arr,5);
			} 
		}
		function generateTyp6Msg($event_id,$player_id_to)
		{
		
			$notification_text = "The final scores basis the Peoria calcuations have been published . Tap here to view the leaderboard.";

			$queryString = "select user_id,registered_mobile_number,device_token,device_os from golf_users ";
			$queryString .= " where user_id =".$player_id_to." ";
			
			$queryResult = $this->db->FetchRow($queryString);
			if(count($queryResult) > 0) 
			{
				$last_ids_arr = array();
				foreach($queryResult as $i => $rowValues)
				{
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."','";
					$queryStringIns .= $rowValues['device_token']."','";
					$queryStringIns .= $rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "0,now(),6)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_ids_arr[] = $this->db->LastInsertId();
				}
				$this->sendFCMPush($last_ids_arr,6);
			} 
		}
		
		
		function generateTyp7Msg($event_id,$player_id)
		{
			$queryString = "select event_name,golf_course_name,date_format(event_start_date_time,'%d/%m/%Y %H:%i:%s') as event_date,admin_id from event_list_view";
			$queryString .= " where event_id =".$event_id;
			
			$result = $this->db->FetchRow($queryString);
			
			$event_name = $result['event_name'];
			$golf_course_name = $result['golf_course_name'];
			$event_date = $result['event_date'];
			$admin_id = $result['admin_id'];
			
			$event_date = date("d/m/Y",strtotime($event_date));
			
			 $queryString2 = "select full_name from golf_users where user_id='".$player_id."'";
			$full_name = $this->db->FetchSingleValue($queryString2);
	
	         $notification_text = $full_name." has requested to participate in the event  '".trim($event_name)."' . Tap here to accept/Decline the request";
			$queryString1 = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString1.= " where user_id ='".$player_id."'";
			$rowValues = $this->db->FetchRow($queryString1);
			if(count($rowValues) > 0) 
			{
				   $not_code= array("id"=>$event_id,"str"=>"invite");
					$not_code_json=json_encode($not_code);
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $admin_id."','";
					$queryStringIns .= $event_id."','";
					$queryStringIns .= $rowValues['device_token']."','";
					$queryStringIns .= $rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "'".$not_code_json."',";
					$queryStringIns .= "0,now(),7)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_id = $this->db->LastInsertId();
					$this->sendFCMPush($last_id,7);
				}
			
			
			return $notification_text;
		}
		
		function generateTyp8Msg($event_id,$player_id)
		{
			$queryString = "select event_name,golf_course_name,date_format(event_start_date_time,'%d/%m/%Y %H:%i:%s') as event_date,admin_id from event_list_view";
			$queryString .= " where event_id =".$event_id;
			$result = $this->db->FetchRow($queryString);
			
			$event_name = $result['event_name'];
			$golf_course_name = $result['golf_course_name'];
			$event_date = $result['event_date'];
			$admin_id = $result['admin_id'];
			
			$event_date = date("d/m/Y",strtotime($event_date));
			
		   
		   $sql_qu="select is_accepted from event_player_list where event_id ='".$event_id."' and player_id ='".$player_id."'";
		   $is_accepted = $this->db->FetchSingleValue($sql_qu);
		  
		   if($is_accepted=='1'){
	         $notification_text = " Your request to participate in the event '".trim($event_name)."' has been accepted";
		   }elseif($is_accepted=='2'){
		   
	         $notification_text = "Your request to participate in the event '".trim($event_name)."' has been declined";
			 
		   }
			 
			$queryString1 = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString1.= " where user_id ='".$player_id."' ";
			
			$rowValues = $this->db->FetchRow($queryString1);
			if(count($rowValues) > 0) 
			{
				    $not_code= array("id"=>$event_id,"str"=>"invite");
					$not_code_json=json_encode($not_code);
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."','";
					$queryStringIns .= $rowValues['device_token']."','";
					$queryStringIns .= $rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "'".$not_code_json."',";
					$queryStringIns .= "0,now(),8)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_id = $this->db->LastInsertId();
					$this->sendFCMPush($last_id,8);
				}
			
			
			return $notification_text;
		}
		
		function generateTyp9Msg($event_id,$admin_id,$player_id,$new_handicap_value) {
			
			$queryString = "select e.event_name,u.full_name as admin_full_name from event_list_view e left join golf_users u on e.admin_id = u.user_id ";
			$queryString .= " where e.event_id =".$event_id." and u.user_id='".$admin_id."'";
			
			$result = $this->db->FetchRow($queryString);
			
			$event_name = $result['event_name'];
			$admin_full_name= $result['admin_full_name'];
			
		   $notification_text = $admin_full_name." has edited your handicap for the event '".$event_name."'. Your revised handicap for the event is ".$new_handicap_value;
			 
		   
			 
			$queryString1 = "select user_id, registered_mobile_number, device_token, device_os from golf_users ";
			$queryString1.= " where user_id ='".$player_id."' ";
			$rowValues = $this->db->FetchRow($queryString1);
			
			if(count($rowValues) > 0) 
			{
				   
					$not_code= array("id"=>$event_id,"str"=>"invite");
					$not_code_json=json_encode($not_code);
					$queryStringIns = " insert into push_notification_user_list";
					$queryStringIns .= " (user_id, event_id,  ";
					$queryStringIns .= "device_udid,device_os,notification_text,notification_code,is_read,creation_time,push_type)";
					$queryStringIns .= " values('";
					$queryStringIns .= $rowValues['user_id']."','";
					$queryStringIns .= $event_id."','";
					$queryStringIns .= $rowValues['device_token']."','";
					$queryStringIns .= $rowValues['device_os']."',";
					$queryStringIns .= '"'.$this->db->escape($notification_text).'",';
					$queryStringIns .= "'".$not_code_json."',";
					$queryStringIns .= "0,now(),9)";
					$queryResultIns = $this->db->FetchQuery($queryStringIns);
					$last_id = $this->db->LastInsertId();
					$this->sendFCMPush($last_id,9);
				}
			
			
			return $notification_text;
		}
		
		function updateNotificationStatus($notification_id) {
			if(is_array($notification_id) && count($notification_id)>0) {
				$push_notification_id = $notification_id;
			}
			elseif(is_numeric($notification_id) && $notification_id>0) {
				$push_notification_id = array($notification_id);
			}
			else {
				$push_notification_id = array();
			}
			
			if(count($push_notification_id)>0) {
				$str = (count($push_notification_id)>1) ? " in (".implode(",",$push_notification_id).")" : " ='".$push_notification_id[0]."'";
				$queryStringIns = " update push_notification_user_list";
				$queryStringIns .= " set is_read = 1,last_modified_time='".date('Y-m-d H:i:s')."'";
				$queryStringIns .= " where push_notification_id ".$str;
				
				$queryResultIns = $this->db->FetchQuery($queryStringIns);
			}
			
		}
		
		function sendFCMPush($id,$type=0) { //echo $id.'___-';die;//return true;
			if(is_array($id) && count($id)>0) {
				$id_arr = $id;
			}
			elseif(is_numeric($id) && $id>0) {
				$id_arr = array($id);
			}
			else {
				$id_arr = array();
			}
		
			if(count($id_arr) > 0) {
				$str = (count($id_arr)>1 ? (" pnu.push_notification_id in (".implode(",",$id_arr).") and") : " pnu.push_notification_id = '".$id_arr[0]."' and");
				
				//$sqlQueryGet = "select * from push_notification_user_list where push_notification_id".$str." and is_read = 0";
				
				$sqlQueryGet = "select pnu.push_notification_id as push_notification_id,pnu.event_id,";
				$sqlQueryGet.= "pnu.notification_text as notification_text, pnu.notification_code,apd.token as device_udid,apd.os,apd.user_id as user_id,ev.admin_id,date(ev.event_start_date_time) as event_start_date,gf.format_name, pl.is_submit_score as submit_score,(select count(x.push_notification_id) from push_notification_user_list x where x.user_id = apd.user_id and x.is_read_by_user=0) as badge_count  from push_notification_user_list pnu  join golf_user_app_devices apd on apd.user_id=pnu.user_id inner join event_table ev on pnu.event_id=ev.event_id left join game_format gf on ev.format_id = gf.format_id left join event_player_list pl on pnu.user_id = pl.player_id";
				$sqlQueryGet.= " where {$str} pnu.is_read = 0 and apd.status='1' group by apd.token order by pnu.push_notification_id";
				//echo $sqlQueryGet;die;
				$result = $this->db->FetchQuery($sqlQueryGet); //print_r($result);die;
				$result_count = count($result);
				if($result_count > 0) {
					$up_ids = array();
					foreach($result as $a=>$b) {
						if($this->sendPushMessage($b) == 1) {
							$up_ids[] = $b['push_notification_id'];
						}
					}
					//print_r($up_ids);die;
					$this->updateNotificationStatus($up_ids);
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		
		function sendPushMessage($primary_array=array()){
			if(!is_array($primary_array) || count($primary_array)==0){
				return false;
			}
			else {
				
				$push_arr = $primary_array;//print_r($push_arr);die;
				$primary_array = $fields = $secondary_array = array();
				
				$custom = array('push_type'=>3,'notification_id'=>$push_arr['push_notification_id'],'message'=>$push_arr['notification_text']);
				$cust = json_encode($custom);
				$primary_array=array('title'=>'PUTT2GETHER','body'=>$push_arr['notification_text']);
				
				
				//$secondary_array=array("message"=>$push_arr['notification_text'],"title"=>'PUTT2GETHER TITLE',"alert"=>'PUTT2GETHER ALERT',"custom"=>$custom);
				
				
				$device_type=$push_arr['os'];
				$device_token=$push_arr['device_udid'];
				// $device_type :: 1 for IOS and 2 for Android
				switch($device_type){
					case 2:{
						//$apiKey ='AIzaSyD0UqKhfhCzVqJAE9tGcyr_nRD1RZTwx9w';
						//$apiKey ='AIzaSyBWo3EuiImcU2n5IUt0fxt1k8DTXGHfcTs'; // current key
						$apiKey ='AIzaSyAi-aOQe1pKQiBRpY03jHx_smh0Wj4DnVI'; // new live key
						$fields['registration_ids']=array($device_token);
						
						$secondary_array['data']["message"]=$push_arr['notification_text'];
						$secondary_array['data']["title"]='PUTT2GETHER TITLE';
						$secondary_array['data']["alert"]='PUTT2GETHER ALERT';
						$secondary_array['data']["custom"]=$custom;
						$secondary_array["registration_ids"]=$fields['registration_ids'];
						
					}break;
					case 1:{
						$apiKey ='AIzaSyAV-MMQesXke48iIpoPQ-qixlUMHa2wgFs';//'AIzaSyBAcF368f9VjXuBZ4d3zQi7lE3ehALNcUc';//'AIzaSyAV-MMQesXke48iIpoPQ-qixlUMHa2wgFs';
						$fields['to']=$device_token;
						//$primary_array['sound']="default";
						/*
						$secondary_array['aps']['alert']["body"]=$push_arr['notification_text'];
						$secondary_array['aps']['alert']["title"]='PUTT2GETHER TITLE';
						$secondary_array['aps']["badge"]=$push_arr['push_notification_id'];
						$secondary_array['aps']['sound']="default";*/
						$secondary_array['notification']["body"]=$push_arr['notification_text'];
						$secondary_array['notification']["title"]='';
						//$secondary_array['notification']["badge"]=$push_arr['push_notification_id'];
						$secondary_array['notification']["sound"]='notification.wav';
						$secondary_array["custom"]=$cust;
						$secondary_array["to"]=$fields['to'];
					}break;
					default:{}
				}
				
				
				
				
				if(isset($apiKey) && $apiKey!='') {
				
					$curlClientObject = curl_init();
					$andriodGoogleUrl = 'https://fcm.googleapis.com/fcm/send';
					
					$notificationMessageHeaders = array('Authorization:key='.$apiKey,'Content-Type:application/json');
					
					curl_setopt( $curlClientObject, CURLOPT_URL, $andriodGoogleUrl);
					curl_setopt( $curlClientObject, CURLOPT_POST, true);
					curl_setopt( $curlClientObject, CURLOPT_HTTPHEADER, $notificationMessageHeaders);
					curl_setopt( $curlClientObject, CURLOPT_RETURNTRANSFER, true);
					
					curl_setopt($curlClientObject, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curlClientObject, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
					
					//end init push notification code
					$fields=$secondary_array;
					
					$fields['priority']='high';
					//$fields['notification']=$primary_array;
					
					if($device_type == 2) {
						$fields['registration_ids'] = $secondary_array["registration_ids"];
						unset($secondary_array["registration_ids"]);
					}
					elseif($device_type == 1) {
						$fields['to'] = $secondary_array["to"];
						unset($secondary_array["to"]);
					}
					
					//echo json_encode($fields);print_r($fields);die;
					curl_setopt($curlClientObject, CURLOPT_POSTFIELDS, json_encode($fields));
					$resultCurl = curl_exec($curlClientObject); //echo $resultCurl;die;
					$curl_result_arr=array();
					$curl_result_arr=json_decode($resultCurl,true);
					//print_r($curl_result_arr);die;
					$is_send=0;
					
					if(is_array($curl_result_arr) && (isset($curl_result_arr["success"]) || isset($curl_result_arr["failure"]))) {
						if(isset($curl_result_arr["success"]) && $curl_result_arr["success"]==1) {
							$is_send=1;
						}
					}
					curl_close($curlClientObject);
				}//echo 'no key found';die;
				return $is_send;
			}
			return false;
		}
		
		
		function markNotificationIsRead($data) {
			$user_id=isset($data['user_id'])?$data['user_id']:"";
			$is_read=isset($data['is_read'])?$data['is_read']:"1";
			$notification_id=isset($data['notification_id'])?intval($data['notification_id']):"0";
			

			if($notification_id<=0) {
				$errorMessage = array("status:41","message"=>"Required fields Not Found ");
				return $errorMessage;
			}
			
			$user_id_str = (is_numeric($user_id) && $user_id>0) ? " and user_id='$user_id' " : "";
			
			if($user_id > 0) {
				$chk_Otp='select count(1) as c from push_notification_user_list where user_id="'.$user_id.'" and is_read_by_user="0"';  
				$noti_count  = $this->db->FetchSingleValue($chk_Otp);
				$notification_count = ($noti_count > 0) ? '1' : '0';
			}
			else {
				$notification_count = 0;
			}
			$queryString = $this->db->FetchSingleValue("select push_notification_id from push_notification_user_list where push_notification_id='$notification_id' {$user_id_str}");
			
			if($queryString>0) {
				$st = $this->db->FetchQuery("update push_notification_user_list set is_read_by_user='{$is_read}' where push_notification_id='$notification_id' {$user_id_str}");
				$succArray=array("message"=>"notification updated successfully",'notification_count'=>$notification_count);
				return $succArray;
			}
			else {
				$errorMessage = array("status:41","message"=>"No Notification found",'notification_count'=>$notification_count);
				return $errorMessage;
			}
			$errorMessage = array("status:41","message"=>"Required fields Not Found ");
			return $errorMessage;
		}
		
		function sendnotification($data){
			
		// API access key from Google API's Console
	define( 'API_ACCESS_KEY', 'AIzaSyCIy28OWe_GZC13ZyXr-xGALTVEpE9EqTA' );
		///define( 'API_ACCESS_KEY', 'AIzaSyDgr1TrhnodAUEZ8ulrPrSE4QeCIsqFUyE' );
		///	define( 'API_ACCESS_KEY', 'AIzaSyAiN4JsylIUOmrX_GzsXh_HQcXefaQTg9w' );
		
		
		$registrationIds = array( $data['push_notification_id'] );
		/// $data['msg_id'];
		switch($data['msg_id'])
			{
				case 1:
			$mess=$this->generateTyp1Msg($data['event_id']);
				break;
				case 2:
		   $mess=$this->generateTyp2Msg($data['event_id']);
				break;
				case 3:
		   $mess= $this->generateTyp3Msg($data['event_id']);
				break;
				case 4:
		   $mess=$this->generateTyp4Msg($data['event_id']);
				break;
				case 5:
		   $mess=$this->generateTyp5Msg($event_id,$player_id_from,$player_id_to);
				break;
				case 6:
		   $mess=$this->generateTyp6Msg($event_id);
				break;
			}
		
		echo  $mess;
		
		// prep the bundle
		$msg = array
		(
			'alert' 	=> "test test msg",			
			'vibrate'	=> 1,
			'sound'		=> 1,
			'largeIcon'	=> 'large_icon',
			'smallIcon'	=> 'small_icon'
		);
		
		$fields = array
		(
			'registration_ids' 	=> $registrationIds,
			'data'			=> $msg
		);
		 if(is_array($data)){
			foreach ($data as $key => $value) {
				$fields['data'][$key] = $value;
			}
		}
		$headers = array
		(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		 
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
		
		echo $result;
		$message = "The message to send";
		}
} 
?>