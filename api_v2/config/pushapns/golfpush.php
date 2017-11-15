<?php
 $host = "localhost";
$user = "myrwa_golf";
$pass = "myrwa@1234";
$dbname = "myrwa_golf_n";

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
$result = mysql_query("select push_notification_id, device_udid, notification_text from push_notification_user_list where is_read = 0 and device_os = 1 order by push_notification_id");

$body['aps']['badge'] = 2;

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'passphrase', 'test123');
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
    stream_set_blocking ($fp, 0); 

if (!$fp) {

 echo "Failed to connect (stream_socket_client): $err $errstrn";

} else {


// Keep push alive (waiting for delivery) for 90 days
$apple_expiry = time() + (90 * 24 * 60 * 60); 
//echo $apple_expiry ;
// Loop thru tokens from database
while($row = mysql_fetch_array($result)) {

$body = array();

$body['aps']['notifurl'] = 'http://www.myrwa.in/golf';
$body['aps'] = array('alert' => $row['notification_text']);
$apple_identifier = $row["push_notification_id"];
//$apple_identifier = 'AIzaSyAiN4JsylIUOmrX_GzsXh_HQcXefaQTg9w';
$deviceToken = $row["device_udid"];
$payload = json_encode($body);

          /* echo 'Apple Identifier#####'.$apple_identifier;
echo 'Apple expiry#####'.$apple_expiry;
echo 'Device token#####'.$deviceToken; 
print_r($payload);
die;*/

// Enhanced Notification
$msg = pack("C", 1) . pack("N", $apple_identifier) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload; 
            
fwrite($fp, $msg);

checkAppleErrorResponse($fp); 
}

// Workaround to check if there were any errors during the last seconds of sending.
// Pause for half a second. 
// Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
usleep(500000); 

checkAppleErrorResponse($fp);

echo 'Completed';

mysql_close($con);
fclose($fp);

}

// FUNCTION to check if there is an error response from Apple
// Returns TRUE if there was and FALSE if there was not
function checkAppleErrorResponse($fp) {

//byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). 
// Should return nothing if OK.

//NOTE: Make sure you set stream_set_blocking($fp, 0) or else fread will pause your script and wait 
// forever when there is no response to be sent.

$apple_error_response = fread($fp, 6);

if ($apple_error_response) {

// unpack the error response (first byte 'command" should always be 8)
$error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response); 

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