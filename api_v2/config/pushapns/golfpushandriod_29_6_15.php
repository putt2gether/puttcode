<?php
 $host = "localhost";
$user = "myrwa_golf";
$pass = "myrwa@1234";
$dbname ="myrwa_golf";
require_once(dirname(__FILE__)."/../creatnotification.php");

$noti_calss=new notifcationCreationClass();
			
// create connection with database
$con = mysql_connect($host,$user,$pass);

// check whether database connection is successful 
if (!$con) {
// if connection not successful then stop the script and show the error
die('Could not connect to database: ' . mysql_error());
} else {
// if database connection successful then select the database
mysql_select_db($dbname, $con);
}
    $apiKey ='AIzaSyAiN4JsylIUOmrX_GzsXh_HQcXefaQTg9w';  //viral
			$sqlQueryGet = "select push_notification_id, device_udid, ";
			$sqlQueryGet .= " notification_text from push_notification_user_list ";
			$sqlQueryGet .= " where is_read = 0 and device_os = 2 order by push_notification_id";
			$queryResult = mysql_query($sqlQueryGet);
			if(mysql_num_rows($queryResult)) 
			{
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
				
				while($row = mysql_fetch_array($queryResult)) 
				{
				$message = $row['notification_text'];
					$fields = array(
						'registration_ids'  => array($row["device_udid"]),
						'data'              => array( "message" => $message,"title" => $message ),
						); //print_r($fields);
					curl_setopt( $curlClientObject, CURLOPT_POSTFIELDS, json_encode( $fields ) );
					$resultCurl = curl_exec($curlClientObject);
					
					curl_close($curlClientObject);
				$noti_calss->updateNotificationStatus($row["push_notification_id"]);
				
				}
				
			}
			echo  $resultCurl;
	
 function addeventnotification()
 {
	  $noti_calss=new notifcationCreationClass();
	 $todaydate=date("Y-m-d");
	  $todaytime=time();
	  $fifteen=strtotime(date("Y-m-d H:i:s",strtotime("+16 minutes")));
	  /// "select event_id,event_name,event_start_time,event_start_date_time,admin_id from event_table where date(event_start_date_time)='".$todaydate."' and  (CONCAT(event_start_date_time,'',event_start_time)>'$todaytime' and time(event_start_time)<='$fifteen')";
   $sql= "SELECT event_id, event_name, event_start_time, event_start_date_time, admin_id
FROM event_table
WHERE is_noti_save_befor_15='0' and DATE( event_start_date_time ) =  '".$todaydate."'
AND (UNIX_TIMESTAMP( (
CONCAT( date(event_start_date_time),' ', event_start_time ) )
) >  '".$todaytime."' )
AND (UNIX_TIMESTAMP( (
CONCAT( date(event_start_date_time),' ', event_start_time ) )
) <=  '".$fifteen."')";

  $query=mysql_query($sql);
	    if(mysql_num_rows($query)>0){
	       while($res=mysql_fetch_array($query)){
			 
			   $noti_calss->generatePushNotification($res['event_id'],1,$res['admin_id'],0);
			  echo  "update event_table set is_noti_save_befor_15='1' where event_id='".$res['event_id']."' ";
			   mysql_query("update event_table set is_noti_save_befor_15='1' where event_id='".$res['event_id']."' ");
		   }}
	} 
	
	 addeventnotification();
   
?>