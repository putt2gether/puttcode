<?php
 $host = "localhost";
$user = "soms_puttuser";
$pass = "soms@1234";
$dbname ="soms_putt2gether";
require_once(dirname(__FILE__)."/../creatnotification.php");


 define("DB_HOST", "localhost");
	define("DB_USER", "putt2get_golf");
    define("DB_PASSWORD", "golf@putt@123");
    define( "DB_NAME", "putt2get_v2_db" );
     define( "BASE_PATH", $_SERVER['DOCUMENT_ROOT'].'/'."puttdemo" );

$noti_calss=new notifcationCreationClass();
// create connection with database
$con = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);

// check whether database connection is successful 
if (!$con) {
// if connection not successful then stop the script and show the error
die('Could not connect to database: ' . mysql_error());
} else {
// if database connection successful then select the database
mysql_select_db(DB_NAME, $con) or die("no db connected");;
}
  // IMPORTANT: make sure you ORDER BY id column
  
  
  $sqlQueryGet = "select pnu.push_notification_id as push_notification_id,pnu.event_id,";
		$sqlQueryGet.= "pnu.notification_text as notification_text, pnu.notification_code,apd.token as device_udid,apd.os,apd.user_id as user_id,ev.admin_id,date(ev.event_start_date_time) as event_start_date,gf.format_name, pl.is_submit_score as submit_score,(select count(x.push_notification_id) from push_notification_user_list x where x.user_id = apd.user_id and x.is_read_by_user=0) as badge_count   from push_notification_user_list pnu  join golf_user_app_devices apd on apd.user_id=pnu.user_id left join event_table ev on pnu.event_id=ev.event_id left join game_format gf on ev.format_id = gf.format_id left join event_player_list pl on pnu.user_id = pl.player_id";
		$sqlQueryGet .= " where  apd.os='1' and pnu.is_read = 0 and apd.status='1' group by apd.token order by pnu.push_notification_id";

    $result = mysql_query($sqlQueryGet);
    ///$result = mysql_query("select push_notification_id, device_udid, notification_text from push_notification_user_list where is_read = 0 and device_os = 1 order by push_notification_id");

    

    //Setup stream (connect to Apple Push Server)
    
	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'passphrase', 'soms@1234');
	stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__) . '/ck.pem');
	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
	stream_set_blocking ($fp, 0); 
	
	// for live app : ssl://gateway.push.apple.com:2195

    if (!$fp) {
        //ERROR
        echo "Failed to connect (stream_socket_client): $err $errstrn";

    } else {
		//Setup notification message
		$body = array();
		$body['aps']['sound'] = 'notification.wav';
		$body['aps']['notifurl'] = 'http://clients.vfactor.in/putt2gether';
		$body['aps']['badge'] =1;//$body['aps']['badge'] = ($row['badge_count']>0 ? intval($row['badge_count']) : 0);
        $apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days
		while($row = mysql_fetch_array($result)) {
			$apple_identifier = $row["push_notification_id"];
			$body['aps']['alert'] =  $row["notification_text"];
			//$body['aps']['custom'] =  (object)array("event_id"=>"'".$row["event_id"]."'");
			$body['aps']['custom'] =  intval($row["event_id"]);
			$body['custom'] =  array('event_id'=>$row["event_id"],'admin_id'=>$row["admin_id"],'event_start_date_time'=>$row["event_start_date"],'notification_id'=>$row["push_notification_id"],"format_name"=>(is_null($row["format_name"]) ? '' : $row["format_name"]),"submit_score"=>$row["submit_score"]);
			$deviceToken = $row["device_udid"];
			$payload = json_encode($body);
			$msg = pack("C", 1) . pack("N", $apple_identifier) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload; //Enhanced Notification
			fwrite($fp, $msg); //SEND PUSH
			checkAppleErrorResponse($fp); 
		
			//We can check if an error has been returned while we are sending, but we also need to check once more after we are done sending in case there was a delay with error response.
			//Workaround to check if there were any errors during the last seconds of sending.
			usleep(500000); //Pause for half a second. Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
	
			checkAppleErrorResponse($fp);
	
			echo 'DONE!';
			$push_notification_id_arr[]=$row["push_notification_id"];
			///$sql = mysql_query("update push_notification_user_list set is_read=1 where push_notification_id='".$row["push_notification_id"]."'");
		}
		
		if(is_array($push_notification_id_arr) && count($push_notification_id_arr)>0) {
			$noti_calss->updateNotificationStatus($push_notification_id_arr);
                        /*foreach($push_notification_id_arr as $v){
				$noti_calss->updateNotificationStatus($v);
			}*/
		}
        mysql_close($con);
        fclose($fp);
    }

    //FUNCTION to check if there is an error response from Apple
    //         Returns TRUE if there was and FALSE if there was not
    function checkAppleErrorResponse($fp) {

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

    ?>