<?php
 $host = "localhost";
$user = "soms_puttuser";
$pass = "soms@1234";
$dbname ="soms_putt2gether";


 define("DB_HOST", "localhost");
	define("DB_USER", "putt2get_golf");
    define("DB_PASSWORD", "golf@putt");
    define( "DB_NAME", "putt2get_demo_golf" );
     define( "BASE_PATH", $_SERVER['DOCUMENT_ROOT'].'/'."puttdemo" );


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
    $result = mysql_query("select push_notification_id, device_udid, notification_text from push_notification_user_list where is_read = 0 and device_os = 1 order by push_notification_id");

    

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
		$body['aps']['badge'] = 1;
        	$apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days
		//while($row = mysql_fetch_array($result)) {
			$apple_identifier = $row["push_notification_id"];
			$body['aps']['alert'] =  "foreground message test.";
			$deviceToken = "791d8ba84721c987e942177a1722c97d76b82bb4e5148f33f69bb9fbb1571e5b";
			$payload = json_encode($body);
			$msg = pack("C", 1) . pack("N", $apple_identifier) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload; //Enhanced Notification
			fwrite($fp, $msg); //SEND PUSH
			checkAppleErrorResponse($fp); 
		
			//We can check if an error has been returned while we are sending, but we also need to check once more after we are done sending in case there was a delay with error response.
			//Workaround to check if there were any errors during the last seconds of sending.
			usleep(500000); //Pause for half a second. Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
	
			checkAppleErrorResponse($fp);
	
			echo 'DONE!';
			
			
		//}
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