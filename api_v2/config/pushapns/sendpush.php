<?php

//require_once(dirname(__FILE__)."/../creatnotification.php");

class sendPushClass{
	public $db, $ios_fp, $android_fp;
	public $sleep_time=5; //sleep time for same user between two push
	
	function __construct(){
		global $dbConectionLink;
		$this->db = $dbConectionLink;
	}
	
	public function sendPushNotification($type=0) {
		$str = (is_numeric($type) && ($type==1 || $type==2)) ? " apd.os='{$type}' and " : "";
		$sqlQueryGet = "select pnu.push_notification_id as push_notification_id,pnu.event_id,";
		$sqlQueryGet.= "pnu.notification_text as notification_text, pnu.notification_code,apd.token as device_udid,apd.os,apd.user_id as user_id,ev.admin_id,date(ev.event_start_date_time) as event_start_date,gf.format_name, pl.is_submit_score as submit_score,(select count(x.push_notification_id) from push_notification_user_list x where x.user_id = apd.user_id and x.is_read_by_user=0) as badge_count  from push_notification_user_list pnu  join golf_user_app_devices apd on apd.user_id=pnu.user_id inner join event_table ev on pnu.event_id=ev.event_id left join game_format gf on ev.format_id = gf.format_id left join event_player_list pl on pnu.user_id = pl.player_id";
		$sqlQueryGet.= " where {$str} pnu.is_read = 0 and apd.status='1' group by apd.token order by pnu.push_notification_id";
		$result = mysql_query($sqlQueryGet);
		$nor = mysql_num_rows($result);
		if($nor>0) {
			if($type<=0) {
				$this->connectIOS();
				$this->connectAndroid();
			}
			elseif($type=='1') {
				$this->connectIOS();
			}
			elseif($type=='2') {
				$this->connectAndroid();
			}
			else {
				return false;
			}
			$ids_arr = array();
			$last_user_id=0;
			while($row = mysql_fetch_array($result)) {
				if($last_user_id==$row['user_id']) {sleep($this->sleep_time);}
				if($row["os"]=='1' && $this->sendIOSPush($row)) {
					$ids_arr[]=$row["push_notification_id"];
				}
				elseif($row["os"]=='2' && $this->sendAndroidPush($row)) {
					$ids_arr[]=$row["push_notification_id"];
				}
				else {
					continue;
				}
				$last_user_id=$row['user_id'];
			}
			
			if(count($ids_arr)>0) {
				$noti_calss=new notifcationCreationClass();
				$noti_calss->updateNotificationStatus($ids_arr);
			}
			if($type<=0) {
				$this->disconnectIOS();
				$this->disconnectAndroid();
			}
			elseif($type=='1') {
				$this->disconnectIOS();
			}
			elseif($type=='2') {
				$this->disconnectAndroid();
			}
			else {
				return false;
			}
		}
	}
	
	private function connectIOS() {
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'passphrase', 'soms@1234');
		stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__) . '/ck.pem');
		$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		stream_set_blocking ($fp, 0);
		$this->ios_fp = $fp;
	}
	
	private function connectAndroid() {
		$apiKey ='AIzaSyAiN4JsylIUOmrX_GzsXh_HQcXefaQTg9w';  //viral
		$curlClientObject = curl_init();
		$andriodGoogleUrl = 'https://android.googleapis.com/gcm/send';
		$notificationMessageHeaders = array( 
			'Authorization: key=' . $apiKey,
			'Content-Type: application/json'
		);
		curl_setopt( $curlClientObject, CURLOPT_URL, $andriodGoogleUrl);
		curl_setopt( $curlClientObject, CURLOPT_POST, true );
		curl_setopt( $curlClientObject, CURLOPT_HTTPHEADER, $notificationMessageHeaders);
		curl_setopt( $curlClientObject, CURLOPT_RETURNTRANSFER, true );
		$this->android_fp = $curlClientObject;
	}
	
	private function disconnectIOS() {
		fclose($this->ios_fp);
	}
	
	private function disconnectAndroid() {
		curl_close($this->android_fp);
	}
	
	private function sendIOSPush($push_data) {
		$row = $push_data;
		//Setup stream (connect to Apple Push Server)
		//var_dump($this->ios_fp);
		$fp = $this->ios_fp;
		
		if (!$fp) {
        	//  "Failed to connect (stream_socket_client): $err $errstrn";
			return false;
    	}
		else {
			$body = array();
			$body['aps']['sound'] = 'notification.wav';
			$body['aps']['notifurl'] = 'http://clients.vfactor.in/putt2gether';
			$body['aps']['badge'] =1;//$body['aps']['badge'] = ($row['badge_count']>0 ? intval($row['badge_count']) : 0);
			$apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days
			
			$apple_identifier = $row["push_notification_id"];
			$body['aps']['alert'] = $row["notification_text"];
			$body['aps']['custom'] =  intval($row["event_id"]);
			$body['custom'] =  array('event_id'=>$row["event_id"],'admin_id'=>$row["admin_id"],'event_start_date_time'=>$row["event_start_date"],'notification_id'=>$row["push_notification_id"]);
			$deviceToken = $row["device_udid"];
			$payload = json_encode($body);
			$msg = pack("C", 1) . pack("N", $apple_identifier) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload; //Enhanced Notification
			fwrite($fp, $msg); //SEND PUSH
			$this->checkAppleErrorResponse($fp); 
		
			//We can check if an error has been returned while we are sending, but we also need to check once more after we are done sending in case there was a delay with error response.
			//Workaround to check if there were any errors during the last seconds of sending.
			usleep(500000); //Pause for half a second. Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
	
			$this->checkAppleErrorResponse($fp);
			return true;
		}
	}
	
	private function sendAndroidPush($push_data) {
		$curlClientObject = $this->android_fp;
		$row = $push_data;
		$message = $row['notification_text'];
		$ncode = $row['notification_code'];
		$event_id = $row['event_id']; 
                $custom = array('event_id'=>$row["event_id"],'admin_id'=>$row["admin_id"],'event_start_date_time'=>$row["event_start_date"],'notification_id'=>$row["push_notification_id"],"format_name"=>(is_null($row["format_name"]) ? '' : $row["format_name"]),"submit_score"=>$row["submit_score"]);
		$fields = array(
			'registration_ids'  => array($row["device_udid"]),
			'data'              => array( "message" => $message,"title" => $message,"alert"=>$message,"custom"=>$custom,"badge"=>($row['badge_count']>0 ? intval($row['badge_count']) : 0),"sound"=>"notification.wav"),
			);
		curl_setopt( $curlClientObject, CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$resultCurl = curl_exec($curlClientObject);
		$curl_result_arr=array();
		$curl_result_arr=json_decode($resultCurl,true);
		
	
		if(is_array($curl_result_arr) && (isset($curl_result_arr["success"]))) {
			if(isset($curl_result_arr["success"]) && $curl_result_arr["success"]==1) {
				return true;
			}
		}
		return false;
	}
	
	private function checkAppleErrorResponse($fp) {

       $apple_error_response = fread($fp, 6); //byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
       //NOTE: Make sure you set stream_set_blocking($fp, 0) or else fread will pause your script and wait forever when there is no response to be sent.

       if ($apple_error_response) {

            $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response); //unpack the error response (first byte 'command" should always be 8)

            if ($error_response['status_code'] == '0') {
                $error_response['status_code'] = '0-No errors encountered';

            } else if ($error_response['status_code'] == '1') {
                $error_response['status_code'] = '1-Processing error';

            } else if ($error_response['status_code'] == '2') {
                $error_response['status_code'] = '2-Missing device token';

            } else if ($error_response['status_code'] == '3') {
                $error_response['status_code'] = '3-Missing topic';

            } else if ($error_response['status_code'] == '4') {
                $error_response['status_code'] = '4-Missing payload';

            } else if ($error_response['status_code'] == '5') {
                $error_response['status_code'] = '5-Invalid token size';

            } else if ($error_response['status_code'] == '6') {
                $error_response['status_code'] = '6-Invalid topic size';

            } else if ($error_response['status_code'] == '7') {
                $error_response['status_code'] = '7-Invalid payload size';

            } else if ($error_response['status_code'] == '8') {
                $error_response['status_code'] = '8-Invalid token';

            } else if ($error_response['status_code'] == '255') {
                $error_response['status_code'] = '255-None (unknown)';

            } else {
                $error_response['status_code'] = $error_response['status_code'].'-Not listed';

            }

            echo '<br><b>+ + + + + + ERROR</b> Response Command:<b>' . $error_response['command'] . '</b>&nbsp;&nbsp;&nbsp;Identifier:<b>' . $error_response['identifier'] . '</b>&nbsp;&nbsp;&nbsp;Status:<b>' . $error_response['status_code'] . '</b><br>';
            echo 'Identifier is the rowID (index) in the database that caused the problem, and Apple will disconnect you from server. To continue sending Push Notifications, just start at the next rowID after this Identifier.<br>';

            return true;

       }
       return false;
    }
	
} 

?>