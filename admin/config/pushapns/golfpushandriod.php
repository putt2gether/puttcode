<?php
 $host = "localhost";
$user = "putt2get_golf";
$pass = "golf@putt@123";
$dbname ="putt2get_v2_db";
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
mysql_select_db(DB_NAME, $con);
}
            $apiKey ='AIzaSyAiN4JsylIUOmrX_GzsXh_HQcXefaQTg9w';  //viral
			/*$sqlQueryGet = "select pnu.push_notification_id,pnu.event_id,";
			$sqlQueryGet .= "pnu.notification_text, pnu.notification_code,apd.token,apd.os  from push_notification_user_list pnu  join golf_user_app_devices apd on apd.user_id=pnu.user_id ";*/
			$sqlQueryGet = "select pnu.push_notification_id as push_notification_id,pnu.event_id,";
		$sqlQueryGet.= "pnu.notification_text as notification_text, pnu.notification_code,apd.token as device_udid,apd.os,apd.user_id as user_id,ev.admin_id,date(ev.event_start_date_time) as event_start_date,gf.format_name, pl.is_submit_score as submit_score  from push_notification_user_list pnu  join golf_user_app_devices apd on apd.user_id=pnu.user_id left join event_table ev on pnu.event_id=ev.event_id left join game_format gf on ev.format_id = gf.format_id left join event_player_list pl on pnu.user_id = pl.player_id";
			$sqlQueryGet .= " where pnu.is_read = 0 and apd.os='2' and apd.status='1' group by apd.token order by pnu.push_notification_id";

echo $sqlQueryGet;//die;
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
				$row["token"] = $row['device_udid'];
				$message = $row['notification_text'];
				$ncode = $row['notification_code'];
				$event_id = $row['event_id'];
				$custom = array('event_id'=>$row["event_id"],'admin_id'=>$row["admin_id"],'event_start_date_time'=>$row["event_start_date"],'notification_id'=>$row["push_notification_id"],"format_name"=>(is_null($row["format_name"]) ? '' : $row["format_name"]),"submit_score"=>$row["submit_score"]);
					$fields = array(
						'registration_ids'  => array($row["token"]),
						'data'              => array( "message" => $message,"title" => $message,"alert"=>$message,"custom"=>$custom,"badge"=>($row['badge_count']>0 ? intval($row['badge_count']) : 0),"sound"=>"notification.wav"),
						);
					curl_setopt( $curlClientObject, CURLOPT_POSTFIELDS, json_encode( $fields ) );
					$resultCurl = curl_exec($curlClientObject);
					$curl_result_arr=array();
					$curl_result_arr=json_decode($resultCurl,true); echo '<pre>';print_r($fields);print_r($curl_result_arr);//die;
					
				
					if(is_array($curl_result_arr) && (isset($curl_result_arr["success"]) || isset($curl_result_arr["failure"]))) {
						if(isset($curl_result_arr["success"]) && $curl_result_arr["success"]==1) {
							$push_notification_id_arr[]=$row["push_notification_id"];
						}
					}
				
				///echo  $resultCurl;
				}
				if(is_array($push_notification_id_arr) && count($push_notification_id_arr)>0) {
					foreach($push_notification_id_arr as $v){
						$noti_calss->updateNotificationStatus($v);
					}
				}
			       /*	while($row = mysql_fetch_array($queryResult)) 
				{
					
				
				$message = $row['notification_text'];
					$fields = array(
						'registration_ids'  => array($row["device_udid"]),
						'data'              => array( "message" => $message,"title" => $message ),
						); //print_r($fields);
					curl_setopt( $curlClientObject, CURLOPT_POSTFIELDS, json_encode( $fields ) );
					$resultCurl = curl_exec($curlClientObject);
				
			//	$noti_calss->updateNotificationStatus($row["push_notification_id"]);
				
				}*/
					curl_close($curlClientObject);
			}
			
	
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
			  ////echo  "update event_table set is_noti_save_befor_15='1' where event_id='".$res['event_id']."' ";
			   mysql_query("update event_table set is_noti_save_befor_15='1' where event_id='".$res['event_id']."' ");
		   }}
	} 
	
	 addeventnotification();
   
?>