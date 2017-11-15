<?php
//require_once(dirname(__FILE__).'/../config/db_config.php');
$filename="/home/putt2gether/public_html/puttdemo/api_v2/config/db_config.php";
require_once($filename);
global $database;


$today_date=date("Y-m-d");//$today_date=date("Y-m-d",strtotime("-1 day"));
// send emails
$sql = 'SELECT e.*,golf_users.user_name,golf_users.display_name FROM event_invite_emails e JOIN golf_users ON e.player_id = golf_users.user_id WHERE e.is_send = 0 GROUP BY e.player_id ORDER BY e.id ASC';
$rows = $database->FetchQuery($sql);
	
if(count($rows) > 0) {
	$updated_ids = array();
	$invte_ids = array();
	foreach($rows as $a=>$b) {
		if(trim($b['event_text']) !='' ) {
			sendmail($b['user_name'],$b['display_name'], $b['event_subject'], $b['event_text']);
		}
		else {
			$invte_ids[$b['event_id']][] = $b['user_name'];
		}
		$updated_ids[] = $b['id'];
	}
	if(count($invte_ids) > 0) {
		foreach($invte_ids as $a=>$b) {
			//sendEventInviteMail($a,$b);
		}
	}
	if(count($updated_ids) > 0) {
		$sql = "update event_invite_emails set is_send='1',send_date='".date('Y-m-d H:i:s')."' where id in (".implode(",",$updated_ids).")";
		$database->FetchQuery($sql);
	}
	
}

// send push
$sql = 'SELECT group_concat(push_notification_id) from push_notification_user_list WHERE push_type = 3 and is_invite_send=0 ORDER BY push_notification_id ASC';
 $rows = $database->FetchSingleValue($sql);


if(trim($rows) != '') {
	$updated_ids = array();
	
	$last_ids_arr = explode(',',$rows);
	//print_r($last_ids_arr);die;
	sendFCMPushCron($last_ids_arr,3);
	//print_r($notif_mth);die;
	if(count($last_ids_arr) > 0) {
		$sql = "update push_notification_user_list set is_invite_send='1' where push_notification_id in (".implode(",",$last_ids_arr).")";
		$database->FetchQuery($sql);
	}
}


function updateNotificationStatusCron($notification_id) {
	global $database;
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
		
		$queryResultIns = $database->FetchQuery($queryStringIns);
	}
	
}
		
function sendFCMPushCron($id,$type=0) {
	global $database;
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
		$result = $database->FetchQuery($sqlQueryGet); //print_r($result);die;
		$result_count = count($result);
		if($result_count > 0) {
			$up_ids = array();
			foreach($result as $a=>$b) {
				if(sendPushMessageCron($b) == 1) {
					$up_ids[] = $b['push_notification_id'];
				}
			}
			//print_r($up_ids);die;
			updateNotificationStatusCron($up_ids);
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}
		
function sendPushMessageCron($primary_array=array()){
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
?>