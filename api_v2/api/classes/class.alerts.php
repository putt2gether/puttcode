<?php
class Alerts{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
	
	}
function getUserAlertsNotification($data){
			
				$fdata = $AlertsArray=array();$readalert=0;$unreadalert=0;
				$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
				if($user_id==""){
					$fdata['status'] = '0';
					$fdata['message'] = 'Required field not found';
					
				}else{
					 $d = array();
					 
					//$queryString = " select a.alert_id,a.subject ,a.event_id,a.message,a.read_status,a.send_date,date(a.send_date) as alert_date from alerts a where a.receiver_id='".$user_id."' and type= 'Invitation'";
					//$queryString = " select a.push_notification_id as alert_id ,a.event_id,a.notification_text as message,a.is_read_by_user as read_status,a.push_type,a.last_modified_time as send_date,date(a.last_modified_time) as alert_date,e.is_started as event_status,e.admin_id from push_notification_user_list a left join event_list_view e on a.event_id = e.event_id where a.user_id='".$user_id."' order by a.push_notification_id desc";
					$queryString = "select a.push_notification_id as alert_id ,a.event_id,a.notification_text as message,a.is_read_by_user as read_status,a.push_type,a.last_modified_time as send_date,date(a.last_modified_time) as alert_date,e.is_started as event_status,e.admin_id,date(e.event_start_date_time) as event_date,p.is_accepted,p.is_submit_score from push_notification_user_list a left join event_list_view e on a.event_id = e.event_id left join event_player_list p on e.event_id = p.event_id and p.player_id = '".$user_id."' where a.user_id='".$user_id."' and e.admin_id is not null order by a.push_notification_id desc";
					
					$queryResult  = $this->db->FetchQuery($queryString);
					
					if(count($queryResult)>0) {
						foreach($queryResult as $i=>$rowValues) {
							$AlertsArray[] = $rowValues ;
							if($rowValues['read_status']==1){
								$readalert++;
							}
							else{
								$unreadalert++;	
							}
							$opush_type = $rowValues['push_type'];
							if($rowValues['is_accepted'] == '2') {
								$rowValues['push_type'] = '0';
							}
							
							if(($rowValues['event_date']) < (date('Y-m-d'))) {
								$rowValues['push_type'] = '0';
							}
							
							if($rowValues['is_accepted'] == '1' && $rowValues['is_submit_score'] == '0') {
								$rowValues['push_type'] = $opush_type;
							}
							$rowValues['send_date'] = date('d-M-Y h:i a',strtotime($rowValues['send_date']));
							
							
							
							$d[] = $rowValues;
						}
						
						/*
						// remove if less than 5 days
						$newarr = $d;
						$d = array();
						$readalert = $unreadalert = 0;
						foreach($newarr as $a=>$rowValues) {
							if($rowValues['push_type'] == 0 && date('Y-m-d',strtotime($rowValues['alert_date'])) < (date('Y-m-d',strtotime('-5 days')))) {
								if($rowValues['read_status']==1){
									$readalert++;
								}
								else{
									$unreadalert++;	
								}
								$d[] = $rowValues;
							}
						}
						*/
					}
					$fdata['status'] = '1';
					$fdata['data'] = $d;
					$fdata['total_read'] = $readalert;
					$fdata['total_unread'] = $unreadalert;
					$fdata['total'] = $readalert+$unreadalert;
					$fdata['message'] = 'Alert data';
				}
				return $fdata; 
		}
		
		function readunreadalertstatus($data,$read_status){
			
				$AlertsArray=array();
				$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
				$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
				if($user_id==""){
					$errorMessage = array("status:21","message"=>"Required field not found");
					return formatData("Error",$errorMessage ,$format);	
				}else{
					$queryString = "update alerts set read_status='".$read_status."' WHERE receiver_id='".$user_id."'";
					$queryResult = mysql_query($queryString) or die(mysql_error());
					$AlertsArray=array("message"=>"Status updated.");
					return formatData("Alerts",$AlertsArray,$format);													
				}
		}
		
		function readunreadeventinvitationstatus($data,$read_status){
			if (mysqli_connect_errno())
				{
					printf("Connect failed: %s\n", mysqli_connect_error());
					exit();
				}
				$format ="json";
				$AlertsArray=array();
				//$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"";
				$player_id=(isset($data['player_id']) && $data['player_id'] > 0)?$data['player_id']:"";
				if($player_id==""){
					$errorMessage = array("status:21","message"=>"Required field not found");
					return formatData("Error",$errorMessage ,$format);	
				}else{
					$queryString = "update event_player_list set read_status='".$read_status."' WHERE player_id='".$player_id."'";//event_id='".$event_id."' and 
					$queryResult = mysql_query($queryString) or die(mysql_error());
					$AlertsArray=array("message"=>"Status updated.");
					return formatData("Success",$AlertsArray,$format);													
				}
		}
	
}
?>