<?php
function __autoload($class_name) {
	$class=strtolower($class_name);
	$classFile=API_BASE_PATH.'classes/class.'.$class.'.php';
	if(is_file($classFile) && !class_exists($class)){
		include_once($classFile);
	}
	else {
		display_error("{$class}.php not found");
	}
}

function getDateTime($datetime='',$format='Y-m-d H:i:s') {
	$format = trim($format)=='' ? 'Y-m-d H:i:s' : $format;
	$datetime = (trim($datetime)=='') ? date($format) : $datetime;
	return date($format,strtotime($datetime));
}

function print_this($arr) {
	echo '<pre>';print_r($arr);echo '</pre>';
}

function display_error($mess="") {
	$mess = ($mess=='') ? "Not Found" : $mess;
	header("HTTP/1.0 412 05::{$mess}");
	echo $mess;
	exit;
}

function md5_password($pass='') {
	$key = '01210';
	if(trim($pass) == '') {
		$pass = generateRandomString(8);
	}
	return array($pass,md5($key.$pass));
}

function getNewAccessToken() {
	return substr(base64encode(time().generateRandomString(50)),0,100);
}

function base64encode($input) {
	$default = "ABCDEFQRSTUVWXYZabcdefghiuvwxyz012789+/=";
	$custom  = "ZYXWVUTSRQFEDCBAzyxwvuihgfedcba987210+/$";
	return strtr(base64_encode($input), $default, $custom);
}

function base64decode($input) {
	$default = "ABCDEFQRSTUVWXYZabcdefghiuvwxyz012789+/=";
	$custom  = "ZYXWVUTSRQFEDCBAzyxwvuihgfedcba987210+/$";
	return base64_decode(strtr($input, $custom, $default));
}

function display_data($header,$array,$type='json') {
	if(is_array($array) && count($array)>0) {

		$new_array = $new_array_main = array();
		$new_array_main = $array;

		if($type=='json') {
			header('Content-type: application/json');
			$new_array1 = array($header=>$new_array_main);
			$json = json_encode($new_array1);
			echo $json;
			exit;
		}
		else {
			$response = '';
			header('Content-type: text/xml');
			$response .=  "<".$header.">";
			foreach($new_array as $index => $data) {
				if(is_array($data)) {
					foreach($data as $key => $value) {
						$response .=  "<".$key.">";
						if(is_array($value)) {
							foreach($value as $tag => $val) {
								$response .=  "<".$tag.">".$val."</".$tag.">";
							}
						}
						$response .= "</".$key.">";
					}
				}
			}
			$response .= "</".$header.">";
			echo $response;
			exit;
		}
	}
	$msg = is_string($array) && trim($array)!='' ? $array : 'Invalid Method/Output type';
	display_error($msg);
}


function display_output($header,$array,$type='json') {
	$header = '';
	if(is_array($array) && count($array)>0) {

		$new_array = $new_array_main = array();
		$new_array_main = $array;

		if($type=='json') {
			//ob_clean();
			header('Content-type: application/json');
			$new_array1 = $array;
			$json = json_encode($new_array1);
			echo $json;
			exit;
		}
		else {
			$response = '';
			header('Content-type: text/xml');
			$response .=  "<".$header.">";
			foreach($new_array as $index => $data) {
				if(is_array($data)) {
					foreach($data as $key => $value) {
						$response .=  "<".$key.">";
						if(is_array($value)) {
							foreach($value as $tag => $val) {
								$response .=  "<".$tag.">".$val."</".$tag.">";
							}
						}
						$response .= "</".$key.">";
					}
				}
			}
			$response .= "</".$header.">";
			echo $response;
			exit;
		}
	}
	$msg = is_string($array) && trim($array)!='' ? $array : 'Invalid Method/Output type';
	display_error($msg);
}


function getLatLongFromAddress($address,$region='') {
	$address=urlencode(str_replace(" ","+",$address));

	$region=(trim($region)!="") ? "&region={$region}" : "";

	$url = "http://maps.google.com/maps/api/geocode/json?address={$address}&sensor=false".$region;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	 $response = curl_exec($ch);
	curl_close($ch);
	$response_a = json_decode($response);
	if(isset($response_a->results[0]->geometry->location->lat) && isset($response_a->results[0]->geometry->location->lng)) {
		return array($response_a->results[0]->geometry->location->lat,$response_a->results[0]->geometry->location->lng);
	}
	else {
		return false;
	}
}

function generateRandomString($length = 10, $mode="sln") {
	$characters = "";
	if(strpos($mode,"s")!==false){$characters.="abcdefghijklmnopqrstuvwxyz";}
	if(strpos($mode,"l")!==false){$characters.="ABCDEFGHIJKLMNOPQRSTUVWXYZ";}
	if(strpos($mode,"n")!==false){$characters.="0123456789";}

	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
	$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function sendEmail($body='',$email='',$fullname='',$subject='') {
	global $_SERVER;
	if(strtolower($_SERVER['HTTP_HOST'])=='localhost'){
		// return "mail send successfully";
	}

	$signature = '';

	$msg  = '';

	$body .=$signature;
	// tell the class to use Sendmail
	if(!empty($email)) {
		$mail =  new Mail($email,$fullname,$subject,$body);

		if($mail->Send()){
			$msg = 'Mail Send Successfully';
		}
		else{
			$msg = 'Sorry!! unable to send email.';
		}
	}
	return $msg;
}

function getCurlRequest($url,$postfields = array()) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
	return $server_output;
}

function sendUserRegisterMail($udata) {

}

function resize_image($filedir,$thumbdir,$new_width,$new_height){
	$size=1600;
	$filedir =$filedir; // the directory for the original image
	$thumbdir =$thumbdir; // the directory for the thumbnail image
	$prefix = 'small_'; // the prefix to be added to the original name
	$mode = '0666';
	$prod_img = $filedir;
	$imageFileType = pathinfo($prod_img,PATHINFO_EXTENSION);
		$prod_img_thumb = $thumbdir;
		chmod ($prod_img, octdec($mode));
		$sizes = getimagesize($prod_img);
		$aspect_ratio = $sizes[1]/$sizes[0];
		if ($sizes[0] >= $size)
	 {
		 $new_height =$new_height;
		 $new_width =$new_width;
	 }
	 else
	 {
		$new_height =$new_height;
		$new_width =$new_width;
	}

	$destimg=ImageCreateTrueColor($new_width,$new_height) or die('Problem In Creating image');
	if($imageFileType=='png'||$imageFileType=='PNG')
	{
		$srcimg=ImageCreateFromPNG($prod_img) or die('Problem In opening Source Image');
	}
	
	else if($imageFileType=='jpeg'||$imageFileType=='JPEG')
	{
		$srcimg=ImageCreateFromJPEG($prod_img) or die('Problem In opening Source Image');
	}
	
	else if($imageFileType=='jpg'||$imageFileType=='JPG')
	{
		$srcimg=ImageCreateFromJPEG($prod_img) or die('Problem In opening Source Image');
	}
	
	else if($imageFileType=='gif'||$imageFileType=='GIF')
	{
		$srcimg=ImageCreateFromGIF($prod_img) or die('Problem In opening Source Image');
	}
	
	if(function_exists('imagecopyresampled'))
	{
		imagecopyresampled($destimg,$srcimg,0,0,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg)) or die('Problem In resizing');
	}
	
	else
	{
		Imagecopyresized($destimg,$srcimg,0,0,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg)) or die('Problem In resizing');
	}
	
	ImageJPEG($destimg,$prod_img_thumb,90)
	or die('Problem In saving');
	imagedestroy($destimg);
	}

function err_page() {
	header("HTTP/1.0 404 Not Found");
	echo "<h1>Page Not Found</h1>";
	exit;	
}

function formatData($header,$data,$format='json'){
 //  print_r($data);
	if($format == 'json') 
	{
		header('Content-type: application/json'); 
		return json_encode(array($header=>$data));
	}
	else 
	{
		$response = '';
		header('Content-type: text/xml');
		$response .=  "<".$header.">";
		foreach($data as $index => $data) 
		{
			if(is_array($data)) 
			{
				foreach($data as $key => $value) 
				{
					//echo $value;
					$response .=  "<".$key.">";
					if(is_array($value)) 
					{
						foreach($value as $tag => $val) 
						{
							$response .=  "<".$tag.">".$val."</".$tag.">";
						}
					}
					$response .= "</".$key.">";
				}
			}
		}
		$response .= "</".$header.">";
	}
	return $response;
}

function filter($data){
	global $database;
    $data = trim(htmlentities(strip_tags($data)));
    if (get_magic_quotes_gpc())
    {
        $data = stripslashes($data);
    }
    $data = $database->escape($data);
    return $data;
}

//RequestToParticipate
//AcceptRejectEvent
//addFriend

function SendAlert($sender_id,$receiver_id,$subject,$message,$event_id=0){
	global $database ;
	if($sender_id!="" && $receiver_id!="" && $subject!="" && $message!=""){
		$type=($event_id > 0)?"Event":"Invitation";
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$sqlQuery = "insert into alerts (";
		$sqlQuery .= " sender_id,";
		$sqlQuery .= " receiver_id,";
		$sqlQuery .= " event_id,";
		$sqlQuery .= " type,";
		$sqlQuery .= " subject,";
		$sqlQuery .= " message,";
		$sqlQuery .= " send_date,";
		$sqlQuery .= " ip_address";
		$sqlQuery .= " ) values (";
		$sqlQuery .= $sender_id.", ";
		$sqlQuery .= " '".$receiver_id."',";
		$sqlQuery .= " '".$event_id."',";
		$sqlQuery .= " '".$type."',";
		$sqlQuery .= ' "'.$database->escape($subject).'",';
		$sqlQuery .= ' "'.$database->escape($message).'",';
		$sqlQuery .= " now(),";
		$sqlQuery .= " '".$ip_address."'";
		$sqlQuery .= " )";
		$insrt= $database->FetchQuery($sqlQuery);
                       
	}	
}

function getTeeName($tee,$color=''){
	global $database ; 
		$queryString = "select tee_name,color_code from tee where tee_id = ". $tee;
		//echo "<br>".$queryString;
		$tee_name='';$rowValues=array();
		$rowValues= $database->FetchRow($queryString);
		//print_r($rowValues);die;
		//$rowValues = mysql_fetch_assoc($queryResult);
		$tee_name=$rowValues['tee_name'];
		
		if($color==""){
		return $tee_name;
		}else{
		return $rowValues;	
		}
}

function getEventTee($tee){
	
	$teeArr=(array)json_decode($tee);
	$teeVal=array();
	
	foreach($teeArr as $tee){
	$teeVal[]=(array)$tee;	
	}
	$menArr=isset($teeVal[0]['men'])?getTeeName($teeVal[0]['men'],1):array();
	$laArr=isset($teeVal[1]['ladies'])?getTeeName($teeVal[1]['ladies'],1):array();
	$juArr=isset($teeVal[2]['junior'])?getTeeName($teeVal[2]['junior'],1):array();
	$men=isset($menArr['tee_name'])?$menArr['tee_name']:"";
	$ladies=isset($laArr['tee_name'])?$laArr['tee_name']:"";
	$junior=isset($juArr['tee_name'])?$juArr['tee_name']:"";
	$mencolor=isset($menArr['color_code'])?hex2rgb($menArr['color_code']):"";
	$ladiescolor=isset($laArr['color_code'])?hex2rgb($laArr['color_code']):"";
	$juniorcolor=isset($juArr['color_code'])?hex2rgb($juArr['color_code']):"";
	
	$men_tee_id=isset($teeVal[0]['men'])?$teeVal[0]['men']:"1";
	$lady_tee_id=isset($teeVal[1]['ladies'])?$teeVal[1]['ladies']:"1";
	$junior_tee_id=isset($teeVal[2]['junior'])?$teeVal[2]['junior']:"1";
	
	return array('Men'=>$men,'men_tee_id'=>$men_tee_id,'MenColor'=>$mencolor,'Ladies'=>$ladies,'lady_tee_id'=>$lady_tee_id,'LadiesColor'=>$ladiescolor,'Junior'=>$junior,'junior_tee_id'=>$junior_tee_id,'JuniorColor'=>$juniorcolor);
}

function sendmail($to,$toname,$subject, $message,$from='',$from_name=''){	

$contentMsg='';
	$contentMsg='<html>
<head>
<title>newsletter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- Save for Web Slices (newsletter.psd) -->
<table id="Table_01" width="593" height="369" border="0" cellpadding="0" cellspacing="0" style="margin:20px auto;border:4px solid #0b5a97;">
	<tr>
		<td rowspan="6">
			<img src="'.__BASE_URI__.'newsletter/newsletter_01.png" width="1" height="369" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td colspan="3">
			<img src="'.__BASE_URI__.'newsletter/newsletter_02.png" width="320" height="39" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td colspan="2" rowspan="4">
			<img src="'.__BASE_URI__.'newsletter/newsletter_03.png" width="271" height="314" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="39" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_04.png" width="320" height="115" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="115" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3" style="font-family:arial;font-size:12px;padding:10px 0;padding-left:26px;color:#9b9e9f;line-height:18px;">Golf is a precision club and ball sport in which competing players (or golfers) use various clubs to hit balls into a series of holes on a course using as few strokes.</td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="98" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_06.png" width="25" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td rowspan="2">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_07.png" width="192" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_08.png" width="103" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="62" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_09.png" width="130" height="55" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td rowspan="2">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_10.png" width="141" height="55" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="1" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<img src="'.__BASE_URI__.'newsletter/newsletter_11.png" width="320" height="54" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="54" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
</table>
<!-- End Save for Web Slices -->
</body>
</html>';
	
	$obj=new Mail($to, $toname, $subject,$contentMsg);
	if($from==""){
	$obj->_from='feedback@putt2gether.com';
	$obj->_fromName='Team putt2gether';
	}else{
	$obj->_from=$from;
	$obj->_fromName=$from_name;
	}	
	$obj->Send();
}
function sendregmail($to,$toname,$subject, $message,$from='',$from_name=''){	
	$contentMsg='';
	$contentMsg='<html>
<head>
<title>newsletter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- Save for Web Slices (newsletter.psd) -->
<table id="Table_01" width="593" height="369" border="0" cellpadding="0" cellspacing="0" style="margin:20px auto;border:4px solid #0b5a97;">
	<tr>
		<td rowspan="6">
			<img src="'.__BASE_URI__.'newsletter/newsletter_01.png" width="1" height="369" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td colspan="3">
			<img src="'.__BASE_URI__.'newsletter/newsletter_02.png" width="320" height="39" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td colspan="2" rowspan="4">
			<img src="'.__BASE_URI__.'newsletter/newsletter_03.png" width="271" height="314" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="39" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_04.png" width="320" height="115" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="115" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3" style="font-family:arial;font-size:12px;padding:10px 0;padding-left:26px;color:#9b9e9f;line-height:18px;">Golf is a precision club and ball sport in which competing players (or golfers) use various clubs to hit balls into a series of holes on a course using as few strokes.</td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="98" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_06.png" width="25" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td rowspan="2">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_07.png" width="192" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_08.png" width="103" height="63" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="62" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td rowspan="2">
			<img src="'.__BASE_URI__.'newsletter/newsletter_09.png" width="130" height="55" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td rowspan="2">
			<a href="#"><img src="'.__BASE_URI__.'newsletter/newsletter_10.png" width="141" height="55" alt="" style="float:left;margin:0;padding:0;outline:none;"></a></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="1" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<img src="'.__BASE_URI__.'newsletter/newsletter_11.png" width="320" height="54" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
		<td>
			<img src="'.__BASE_URI__.'newsletter/spacer.gif" width="1" height="54" alt="" style="float:left;margin:0;padding:0;outline:none;"></td>
	</tr>
</table>
<!-- End Save for Web Slices -->
</body>
</html>';
	//yecho $contentMsg; die;
	$obj=new Mail($to, $toname, $subject,$contentMsg);
	if($from==""){
	$obj->_from='feedback@putt2gether.com';
	$obj->_fromName='Team putt2gether';
	}else{
	$obj->_from=$from;
	$obj->_fromName=$from_name;
	}	
	
	
	$obj->Send();
}

function sendgolfreq($to,$toname,$subject, $message,$from='',$from_name=''){	
	$contentMsg='';
	$contentMsg='<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head><body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
'.$message.'
</body></html>';
	
	$obj=new Mail($to, $toname, $subject,$contentMsg);
	if($from==""){
	$obj->_from='feedback@putt2gether.com';
	$obj->_fromName='Team Putt2gether';
	}else{
	$obj->_from=$from;
	$obj->_fromName=$from_name;
	}	
	
	
	$obj->Send();
}

function hex2rgb($hex) {
if($hex!=""){
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb1["r"] =$r;
    $rgb1["g"] =$g;
	 $rgb1["b"] =$b;//array($r, $g, $b);
   $rgb=$rgb1;
}else{
$rgb="";
}
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}

function  sendScoreCard111($event_id=0,$player_id=0){
global $database;
$dirPath = "puttdemo";
		$pathString = "http://" . $_SERVER['SERVER_NAME']. ":" . $_SERVER['SERVER_PORT'] . "/".$dirPath;
		$event_id = $event_id;
		$player_id = $player_id;
		$sum_par3 = 0;
		$sum_par4 = 0;
		$sum_par5 = 0;
		$sum_first9 = 0;
		$sum_last9 = 0;
		$sum_par_first9 = 0;
		$sum_par_last9 = 0;
		$count_par3 = 0;
		$count_par4 = 0;
		$count_par5 = 0;
		$no_of_eagle  = 0;
		$no_of_birdies  = 0;
		$no_of_pars  = 0;
		$no_of_bogeys  = 0;
		$no_of_double_bogeys  = 0;
		$score_array = array();
		$total_sum = 0;
		$return_array = array();		
		$format ='json';
		$queryString = " select golf_course_id,event_name, is_handicap,format_id,is_started, event_start_date_time";
		$queryString .= " from event_table";
		$queryString .= " where event_id =".$event_id;

		$result1 =$database->FetchRow($queryString);
		$golf_course_id = $result1['golf_course_id'];
		$event_name = $result1['event_name'];
		$is_handicap = $result1['is_handicap'];
		$format_id = $result1['format_id'];
		$is_started = $result1['is_started'];
		$event_start_date_time = $result1['event_start_date_time'];
		
		$queryString1 = "select full_name, user_name from golf_users where user_id ='".$player_id."'";
		$result =$database->FetchRow($queryString1);
		
		$full_name= $result['full_name'];
		$user_name= $result['user_name'];

		$queryString1 = " select golf_course_name,city_id  from golf_course where golf_course_id =".$golf_course_id;
		$result =$database->FetchRow($queryString1);
	
		$golf_course_name = $result['golf_course_name'];
		$city_id = $result['city_id'];


		$locationQuery="select city_name, state_id from city where city_id='".$city_id."'";
		$result =$database->FetchRow($locationQuery) ;
		$city_name = $result['city_name'];
		$state_id= $result['state_id'];
		
		$locationQuery="select state_name from state where state_id='".$state_id."'";
		$state_name =$database->FetchSingleValue($locationQuery) ;
		
		$queryString1 = " select handicap_value from event_score_calc ";
		$queryString1 .= " where event_id =".$event_id;
		$queryString1 .= " and player_id =".$player_id;

		$handicap_value =$database->FetchSingleValue($queryString1) ;
		
		$queryString = " select is_submit_score";
		$queryString .= " from event_player_list";
		$queryString .= " where event_id =".$event_id;
		$queryString .= " and player_id =".$player_id;
		$is_started =$database->FetchSingleValue($queryString) ;
		
		if($is_started=="1")
		{

			$par_value_array = array();
			$hole_index_array = array();
			$par_sum = 0;
			$hole_sum = 0;
			$queryString = "select ";
			$queryString .= " golf_hole_index_id, ";
			$queryString .= " par_value_1,";
			$queryString .= " par_value_2,";
			$queryString .= " par_value_3,";
			$queryString .= " par_value_4,";
			$queryString .= " par_value_5,";
			$queryString .= " par_value_6,";
			$queryString .= " par_value_7,";
			$queryString .= " par_value_8,";
			$queryString .= " par_value_9,";
			$queryString .= " par_value_10,";
			$queryString .= " par_value_11,";
			$queryString .= " par_value_12,";
			$queryString .= " par_value_13,";
			$queryString .= " par_value_14,";
			$queryString .= " par_value_15,";
			$queryString .= " par_value_16,";
			$queryString .= " par_value_17,";
			$queryString .= " par_value_18,";
			$queryString .= " hole_index_1,";
			$queryString .= " hole_index_2,";
			$queryString .= " hole_index_3,";
			$queryString .= " hole_index_4,";
			$queryString .= " hole_index_5,";
			$queryString .= " hole_index_6,";
			$queryString .= " hole_index_7,";
			$queryString .= " hole_index_8,";
			$queryString .= " hole_index_9,";
			$queryString .= " hole_index_10,";
			$queryString .= " hole_index_11,";
			$queryString .= " hole_index_12,";
			$queryString .= " hole_index_13,";
			$queryString .= " hole_index_14,";
			$queryString .= " hole_index_15,";
			$queryString .= " hole_index_16,";
			$queryString .= " hole_index_17,";
			$queryString .= " hole_index_18";
			$queryString .= " from golf_hole_index";
			$queryString .= " where golf_course_id = ".$golf_course_id;

			$result =$database->FetchRow($queryString) ;
		
		
			$golf_hole_index_id = $result['golf_hole_index_id'];
			$par_value_1 = $result['par_value_1'];
			$par_value_2 = $result['par_value_2'];
			$par_value_3 = $result['par_value_3'];
			$par_value_4 = $result['par_value_4'];
			$par_value_5 = $result['par_value_5'];
			$par_value_6 = $result['par_value_6'];
			$par_value_7 = $result['par_value_7'];
			$par_value_8 = $result['par_value_8'];
			$par_value_9 = $result['par_value_9'];
			$par_value_10 = $result['par_value_10'];
			$par_value_11 = $result['par_value_11'];
			$par_value_12 = $result['par_value_12'];
			$par_value_13 = $result['par_value_13'];
			$par_value_14 = $result['par_value_14'];
			$par_value_15 = $result['par_value_15'];
			$par_value_16 = $result['par_value_16'];
			$par_value_17 = $result['par_value_17'];
			$par_value_18 = $result['par_value_18'];
			$hole_index_1 = $result['hole_index_1'];
			$hole_index_2 = $result['hole_index_2'];
			$hole_index_3 = $result['hole_index_3'];
			$hole_index_4 = $result['hole_index_4'];
			$hole_index_5 = $result['hole_index_5'];
			$hole_index_6 = $result['hole_index_6'];
			$hole_index_7 = $result['hole_index_7'];
			$hole_index_8 = $result['hole_index_8'];
			$hole_index_9 = $result['hole_index_9'];
			$hole_index_10 = $result['hole_index_10'];
			$hole_index_11 = $result['hole_index_11'];
			$hole_index_12 = $result['hole_index_12'];
			$hole_index_13 = $result['hole_index_13'];
			$hole_index_14 = $result['hole_index_14'];
			$hole_index_15 = $result['hole_index_15'];
			$hole_index_16 = $result['hole_index_16'];
			$hole_index_17 = $result['hole_index_17'];
			$hole_index_18 = $result['hole_index_18'];
			
				
			$par_value_array[] = $par_value_1;
			$par_value_array[] = $par_value_2;
			$par_value_array[] = $par_value_3;
			$par_value_array[] = $par_value_4;
			$par_value_array[] = $par_value_5;
			$par_value_array[] = $par_value_6;
			$par_value_array[] = $par_value_7;
			$par_value_array[] = $par_value_8;
			$par_value_array[] = $par_value_9;
			$par_value_array[] = $par_value_10;
			$par_value_array[] = $par_value_11;
			$par_value_array[] = $par_value_12;
			$par_value_array[] = $par_value_13;
			$par_value_array[] = $par_value_14;
			$par_value_array[] = $par_value_15;
			$par_value_array[] = $par_value_16;
			$par_value_array[] = $par_value_17;
			$par_value_array[] = $par_value_18;
			
			$par_sum = $par_sum + $par_value_1;
			$par_sum = $par_sum + $par_value_2;
			$par_sum = $par_sum + $par_value_3;
			$par_sum = $par_sum + $par_value_4;
			$par_sum = $par_sum + $par_value_5;
			$par_sum = $par_sum + $par_value_6;
			$par_sum = $par_sum + $par_value_7;
			$par_sum = $par_sum + $par_value_8;
			$par_sum = $par_sum + $par_value_9;
			$par_sum = $par_sum + $par_value_10;
			$par_sum = $par_sum + $par_value_11;
			$par_sum = $par_sum + $par_value_12;
			$par_sum = $par_sum + $par_value_13;
			$par_sum = $par_sum + $par_value_14;
			$par_sum = $par_sum + $par_value_15;
			$par_sum = $par_sum + $par_value_16;
			$par_sum = $par_sum + $par_value_17;
			$par_sum = $par_sum + $par_value_18;

			$hole_sum = $hole_sum + $hole_index_1;
			$hole_sum = $hole_sum + $hole_index_2;
			$hole_sum = $hole_sum + $hole_index_3;
			$hole_sum = $hole_sum + $hole_index_4;
			$hole_sum = $hole_sum + $hole_index_5;
			$hole_sum = $hole_sum + $hole_index_6;
			$hole_sum = $hole_sum + $hole_index_7;
			$hole_sum = $hole_sum + $hole_index_8;
			$hole_sum = $hole_sum + $hole_index_9;
			$hole_sum = $hole_sum + $hole_index_10;
			$hole_sum = $hole_sum + $hole_index_11;
			$hole_sum = $hole_sum + $hole_index_12;
			$hole_sum = $hole_sum + $hole_index_13;
			$hole_sum = $hole_sum + $hole_index_14;
			$hole_sum = $hole_sum + $hole_index_15;
			$hole_sum = $hole_sum + $hole_index_16;
			$hole_sum = $hole_sum + $hole_index_17;
			$hole_sum = $hole_sum + $hole_index_18;
			
			foreach ($par_value_array as $key => $value) 
			{
				$queryString1 = " select event_score_calc.score_entry_".($key+1)." from event_score_calc ";
				$queryString1 .= " where event_id =".$event_id;
				$queryString1 .= " and player_id =".$player_id;
				
				$gross_score =$database->FetchSingleValue($queryString1) ;
				
				$score_array[] = $gross_score;
				$total_sum = $total_sum + $gross_score;
				if($value == 3)
				{
					$sum_par3 = $sum_par3 + $gross_score;
					$count_par3++;
				}
				else if($value == 4)
				{
					$sum_par4 = $sum_par4 + $gross_score;
					$count_par4++;
				}
				else if($value == 5)
				{
					$sum_par5 = $sum_par5 + $gross_score;
					$count_par5++;
				}
				if($key < 9)
				{
					$sum_first9 = $sum_first9 + $gross_score;
					$sum_par_first9 = $sum_par_first9 + $value;
				}
				else if($key >= 9)
				{
					$sum_last9 = $sum_last9 + $gross_score;
					$sum_par_last9 = $sum_par_last9 + $value;
				}
				$difference =  $gross_score - $value;
				if( $difference <= -2)
				{
					$no_of_eagle = $no_of_eagle + 1;
				}
				else if( $difference == -1)
				{
					$no_of_birdies = $no_of_birdies + 1;
				}
				else if( $difference == 0)
				{
					$no_of_pars = $no_of_pars + 1;
				}
				else if( $difference == 1)
				{
					$no_of_bogeys = $no_of_bogeys + 1;
				}
				else if( $difference >= 2)
				{
					$no_of_double_bogeys = $no_of_double_bogeys + 1;
				}

			}
			//print_r($score_array);
			$avg_par3 = round($sum_par3 /$count_par3,2);
			$avg_par4 = round($sum_par4 /$count_par4,2);
			$avg_par5 = round($sum_par5 /$count_par5,2);
			$avg_first9 = round($sum_first9/9,2);
			$avg_last9 = round($sum_last9/9,2);
			
		}	
		$newsletter_body ="";
		$newsletter_body .= "<html>\n";
		$newsletter_body .= "<head>\n";
		$newsletter_body .= "<title>newsletter</title>\n";
		$newsletter_body .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
		$newsletter_body .= "<style>\n";
		$newsletter_body .= ".table-grid td {\n";
		$newsletter_body .= "	border-bottom: 1px solid #b7b7b8;\n";
		$newsletter_body .= "	text-align: center;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= ".bg-green {\n";
		$newsletter_body .= "	background-color: #325604;\n";
		$newsletter_body .= "	color: #fff;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= ".bg-blue {\n";
		$newsletter_body .= "	background-color: #0a5b86;\n";
		$newsletter_body .= "	color: #fff;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= ".bg-gray {\n";
		$newsletter_body .= "	background-color: #939494;\n";
		$newsletter_body .= "	color: #fff;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= ".bg-black {\n";
		$newsletter_body .= "	background-color: #000000;\n";
		$newsletter_body .= "	color: #fff;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= ".bg-yellow {\n";
		$newsletter_body .= "	background-color: #f2a942;\n";
		$newsletter_body .= "	color: #fff;\n";
		$newsletter_body .= "}\n";
		$newsletter_body .= "</style>\n";
		$newsletter_body .= "</head>\n";
		$newsletter_body .= "<body bgcolor=\"#FFFFFF\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n";

		$newsletter_body .= "<table width=\"860\" border=\"0\" cellspacing=\"0\" cellpadding=\"15\" bgcolor=\"#dee3d8\">\n";
		$newsletter_body .= "  <tr>\n";
		$newsletter_body .= "    <td><table id=\"Table_01\" width=\"830\" height=\"953\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#ffffff\" style=\"font-family:arial;font-size:14px;color:#000;\">\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"2\" rowspan=\"3\"><a href=\"#\"><img src=\"".$pathString."/images/newsletter_01.png\" width=\"124\" height=\"111\" alt=\"\"></a></td>\n";
		$newsletter_body .= "          <td colspan=\"7\" rowspan=\"3\"></td>\n";
		$newsletter_body .= "          <td colspan=\"2\" rowspan=\"2\"></td>\n";
		$newsletter_body .= "          <td colspan=\"3\" rowspan=\"3\"></td>\n";
		$newsletter_body .= "          <td></td>\n";
		$newsletter_body .= "          <td colspan=\"3\" rowspan=\"3\"></td>\n";
		$newsletter_body .= "          <td><img src=\"images/spacer.gif\" width=\"1\" height=\"59\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td rowspan=\"2\"><a href=\"#\"><img src=\"".$pathString."/images/newsletter_07.png\" width=\"95\" height=\"52\" alt=\"\"></a></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"2\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"2\"><a href=\"#\"><img src=\"".$pathString."/images/newsletter_08.png\" width=\"89\" height=\"50\" alt=\"\"></a></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"50\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" bgcolor=\"#325604\" style=\"text-align:center;font-family:arial;font-size:14px;color:#fff;\"><strong>Welcome to putt2gether</strong> - a live leaderboard app with score calculation across popular formats. </td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"39\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"5\" style=\"padding:0 40px;font-family:arial;font-size:14px;color:#000;line-height:22px;\"><span style=\"font-size:18px;\">".$full_name."</span><br>\n";
		$newsletter_body .= "            ".$golf_course_name."<br>\n";
		$newsletter_body .= "            ".$$city_name."Noida<br>\n";
		$newsletter_body .= "            Regular Tees - 72.0 / 127<br>\n";
		$newsletter_body .= "            ".date("F d,Y",strtotime($event_start_date_time)).", ".$city_name.", ".$state_name."</td>\n";
		$newsletter_body .= "          <td colspan=\"5\" style=\"line-height:22px;\" align=\"center\">Create your own events and invite <br>\n";
		$newsletter_body .= "            friends to participate<br>\n";
		$newsletter_body .= "            <img src=\"".$pathString."/images/golf1.png\" width=\"257\" height=\"205\"></td>\n";
		$newsletter_body .= "          <td colspan=\"8\" style=\"line-height:22px;\" align=\"center\">Real time live scoring for all the <br>\n";
		$newsletter_body .= "            participants<br>\n";
		$newsletter_body .= "            <img src=\"".$pathString."/images/golf2.png\" width=\"257\" height=\"205\" style=\"float:left;\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"274\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" bgcolor=\"#325604\" style=\"padding:0 15px;color:#fff;font-size:19px;font-weight:normal;\">Leaderboard</td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"39\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" valign=\"top\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"9\" class=\"table-grid\">\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td width=\"30%\" style=\"text-align:left;color:#818181;\">Hole</td>\n";
		$newsletter_body .= "                <td width=\"3%\">1</td>\n";
		$newsletter_body .= "                <td width=\"3%\">2</td>\n";
		$newsletter_body .= "                <td width=\"3%\">3</td>\n";
		$newsletter_body .= "                <td width=\"3%\">4</td>\n";
		$newsletter_body .= "                <td width=\"3%\">5</td>\n";
		$newsletter_body .= "                <td width=\"3%\">6</td>\n";
		$newsletter_body .= "                <td width=\"3%\">7</td>\n";
		$newsletter_body .= "                <td width=\"3%\">8</td>\n";
		$newsletter_body .= "                <td width=\"3%\">9</td>\n";
		$newsletter_body .= "                <td width=\"3%\">10</td>\n";
		$newsletter_body .= "                <td width=\"3%\">11</td>\n";
		$newsletter_body .= "                <td width=\"3%\">12</td>\n";
		$newsletter_body .= "                <td width=\"3%\">13</td>\n";
		$newsletter_body .= "                <td width=\"3%\">14</td>\n";
		$newsletter_body .= "                <td width=\"3%\">15</td>\n";
		$newsletter_body .= "                <td width=\"3%\">16</td>\n";
		$newsletter_body .= "                <td width=\"3%\">17</td>\n";
		$newsletter_body .= "                <td width=\"3%\">18</td>\n";
		$newsletter_body .= "                <td width=\"10%\">Total</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td style=\"text-align:left;color:#818181;\">Par</td>\n";
		$newsletter_body .= "                <td>".$par_value_1."</td>\n";
		$newsletter_body .= "                <td>".$par_value_2."</td>\n";
		$newsletter_body .= "                <td>".$par_value_3."</td>\n";
		$newsletter_body .= "                <td>".$par_value_4."</td>\n";
		$newsletter_body .= "                <td>".$par_value_5."</td>\n";
		$newsletter_body .= "                <td>".$par_value_6."</td>\n";
		$newsletter_body .= "                <td>".$par_value_7."</td>\n";
		$newsletter_body .= "                <td>".$par_value_8."</td>\n";
		$newsletter_body .= "                <td>".$par_value_9."</td>\n";
		$newsletter_body .= "                <td>".$par_value_10."</td>\n";
		$newsletter_body .= "                <td>".$par_value_11."</td>\n";
		$newsletter_body .= "                <td>".$par_value_12."</td>\n";
		$newsletter_body .= "                <td>".$par_value_13."</td>\n";
		$newsletter_body .= "                <td>".$par_value_14."</td>\n";
		$newsletter_body .= "                <td>".$par_value_15."</td>\n";
		$newsletter_body .= "                <td>".$par_value_16."</td>\n";
		$newsletter_body .= "                <td>".$par_value_17."</td>\n";
		$newsletter_body .= "                <td>".$par_value_18."</td>\n";
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px;\">".$par_sum."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td style=\"text-align:left;color:#818181;\">Index</td>\n";
		$newsletter_body .= "                <td>".$hole_index_1."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_2."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_3."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_4."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_5."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_6."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_7."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_8."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_9."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_10."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_11."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_12."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_13."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_14."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_15."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_16."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_17."</td>\n";
		$newsletter_body .= "                <td>".$hole_index_18."</td>\n";
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px;\">".$hole_sum."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td bgcolor=\"#e9eaeb\" style=\"font-size:18px;text-align:left;\">".$full_name."<span style=\"color:#325604;\">(".$handicap_value.")</span></td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[0]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-blue\">".$score_array[1]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[2]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-gray\">".$score_array[3]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-blue\">".$score_array[4]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-gray\">".$score_array[5]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-black\">".$score_array[6]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[7]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-yellow\">".$score_array[8]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[9]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-blue\">".$score_array[10]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[11]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-gray\">".$score_array[12]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-blue\">".$score_array[13]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-gray\">".$score_array[14]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-black\">".$score_array[16]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-green\">".$score_array[16]."</td>\n";
		$newsletter_body .= "                <td class=\"bg-yellow\">".$score_array[17]."</td>\n";
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px;\">".$total_sum."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "            </table></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"194\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" bgcolor=\"#325604\" style=\"padding:0 15px;color:#fff;font-size:19px;font-weight:normal;\">Key Stats</td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"39\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\" style=\"font-size:18px;\">\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td>Par 3 Avg: </td>\n";
		$newsletter_body .= "                <td>".$avg_par3."</td>\n";
		$newsletter_body .= "                <td>Front 9:</td>\n";
		$newsletter_body .= "                <td>".$sum_first9." / ".$sum_par_first9."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td>Par 4 Avg: </td>\n";
		$newsletter_body .= "                <td>".$avg_par4."</td>\n";
		$newsletter_body .= "                <td>Back 9:</td>\n";
		$newsletter_body .= "                <td>".$sum_last9." / ".$sum_par_last9."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td>Par 5 Avg:</td>\n";
		$newsletter_body .= "                <td>".$avg_par5."</td>\n";
		$newsletter_body .= "                <td>&nbsp;</td>\n";
		$newsletter_body .= "                <td>&nbsp;</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "            </table></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"143\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" height=\"27\" style=\"border-top:1px solid #ccc;\">&nbsp;</td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"27\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td align=\"center\" style=\"font-size:18px;\">No:</td>\n";
		$newsletter_body .= "          <td colspan=\"2\" align=\"center\" class=\"bg-yellow\" style=\"font-size:18px;\">".$no_of_eagle."</td>\n";
		$newsletter_body .= "          <td width=\"19\">&nbsp;</td>\n";
		$newsletter_body .= "          <td colspan=\"2\" align=\"center\" class=\"bg-blue\" style=\"font-size:18px;\">".$no_of_birdies."</td>\n";
		$newsletter_body .= "          <td width=\"19\">&nbsp;</td>\n";
		$newsletter_body .= "          <td align=\"center\" class=\"bg-green\" style=\"font-size:18px;\">".$no_of_pars."</td>\n";
		$newsletter_body .= "          <td width=\"19\"></td>\n";
		$newsletter_body .= "          <td colspan=\"3\" align=\"center\" class=\"bg-gray\" style=\"font-size:18px;\">".$no_of_bogeys."</td>\n";
		$newsletter_body .= "          <td width=\"18\"></td>\n";
		$newsletter_body .= "          <td colspan=\"4\" align=\"center\" class=\"bg-black\" style=\"font-size:18px;\">".$no_of_double_bogeys."</td>\n";
		$newsletter_body .= "          <td width=\"17\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"37\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td>&nbsp;</td>\n";
		$newsletter_body .= "          <td colspan=\"2\" align=\"center\" style=\"font-size:18px;\">Eagle +</td>\n";
		$newsletter_body .= "          <td>&nbsp;</td>\n";
		$newsletter_body .= "          <td colspan=\"2\" align=\"center\" style=\"font-size:18px;\">Birdie</td>\n";
		$newsletter_body .= "          <td>&nbsp;</td>\n";
		$newsletter_body .= "          <td align=\"center\" style=\"font-size:18px;\">Par</td>\n";
		$newsletter_body .= "          <td>&nbsp;</td>\n";
		$newsletter_body .= "          <td colspan=\"3\" align=\"center\" style=\"font-size:18px;\">Bogey</td>\n";
		$newsletter_body .= "          <td>&nbsp;</td>\n";
		$newsletter_body .= "          <td colspan=\"3\" align=\"center\" style=\"font-size:18px;\">Double Bogey +</td>\n";
		$newsletter_body .= "          <td colspan=\"2\">&nbsp;</td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"49\" alt=\"\"></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"62\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"62\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"73\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"19\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"57\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"78\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"19\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"135\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"19\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"32\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"57\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"46\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"18\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"16\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"95\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"23\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"1\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td><img src=\"".$pathString."/images/spacer.gif\" width=\"17\" height=\"1\" alt=\"\"></td>\n";
		$newsletter_body .= "          <td></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "      </table></td>\n";
		$newsletter_body .= "  </tr>\n";
		$newsletter_body .= "</table>\n";
		$newsletter_body .= "\n";
		$newsletter_body .= "</body>\n";
		$newsletter_body .= "</html>\n";



	
	
/*
		$user_email ="admin@myrwa.in";


    $headers .= "From: ".$user_email."\n";
    $headers .= "Reply-To: " . $user_email ."\r\n" .'X-Mailer: PHP/' . phpversion();
     

		
//$user_name; //"somebody@example.com, somebodyelse@example.com";
		$subject = "Score Card News Letter";
		
		if(mail($to,$subject,$newsletter_body,$headers))
echo "Password recovery instructions been sent to your email<br>";
else

echo "eee";*/

		//echo $newsletter_body;
		$subject="Score Card";
$to = $user_name;
$toname=$full_name;


$obj=new Mail($to, $toname, $subject,$newsletter_body);
	if($from==""){
	$obj->_from='feedback@putt2gether.com';
	$obj->_fromName='Team Putt2gether';
	}else{
	$obj->_from=$from;
	$obj->_fromName=$from_name;
	}	
	$obj->Send();
}

function get_bitly_short_url($url,$login='',$appkey='',$format='txt') {
if($login=='') {$login='sachinsoms';}
if($appkey=='') {$appkey='R_39c2d30b2ae84d269d5a0bc776b96715';}
	$connectURL = 'http://api.bit.ly/v3/shorten?login='.$login.'&apiKey='.$appkey.'&uri='.urlencode($url).'&format='.$format;
	return curl_get_result($connectURL);
}



/* returns a result form url */
function curl_get_result($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function  sendScoreCard($event_id=0,$player_id=0,$return_html=false,$only_body=false,$saveImg=false){
		
		
	
                $dirPath = "puttdemo";
		$pathString = "http://clients.vfactor.in/putt2gether/".$dirPath;

		$event_id = $event_id;
		$player_id = $player_id;
		$sum_par3 = 0;
		$sum_par4 = 0;
		$sum_par5 = 0;
		$sum_first9 = 0;
		$sum_last9 = 0;
		$sum_par_first9 = 0;
		$sum_par_last9 = 0;
		
		$count_par3 = 0;
		$count_par4 = 0;
		$count_par5 = 0;
		$no_of_eagle  = 0;
		$no_of_birdies  = 0;
		$no_of_pars  = 0;
		$no_of_bogeys  = 0;
		$no_of_double_bogeys  = 0;
		$score_array = array();
		$total_sum = 0;
		
		if (mysqli_connect_errno())
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$return_array = array();		

		$format ='json';
		$queryString = " select golf_course_id,event_name, is_handicap,format_id,is_started, event_start_date_time,total_hole_num,hole_start_from,event_name";
		$queryString .= " from event_table";
		$queryString .= " where event_id =".$event_id;

		$result = mysql_query($queryString) or die (mysql_error()); 
		list($golf_course_id,$event_name, $is_handicap,$format_id,$is_started,$event_start_date_time,$total_hole_num,$hole_start_from,$event_name) = mysql_fetch_row($result);
		
		$col_span =$total_hole_num;
		
		if($total_hole_num==9 && $hole_start_from==10) {
			$total_hole_num=18;
		}
		
		$queryString1 = "select full_name, user_name from golf_users where user_id ='".$player_id."'";
		$result = mysql_query($queryString1) or die (mysql_error()); 
		list($full_name,$user_name) = mysql_fetch_row($result);

		$queryString1 = " select golf_course_name,city_id";
		$queryString1 .= " from golf_course";
		$queryString1 .= " where golf_course_id =".$golf_course_id;
		$result = mysql_query($queryString1) or die (mysql_error()); 
		list($golf_course_name,$city_id) = mysql_fetch_row($result);


		$locationQuery="select city_name, state_id from city where city_id='".$city_id."'";
		$result = mysql_query($locationQuery) or die (mysql_error()); 
		list($city_name,$state_id) = mysql_fetch_row($result);
		
		$locationQuery="select state_name from state where state_id='".$state_id."'";
		$result = mysql_query($locationQuery) or die (mysql_error()); 
		list($state_name) = mysql_fetch_row($result);
		
		$queryString1 = " select handicap_value from event_score_calc ";
		$queryString1 .= " where event_id =".$event_id;
		$queryString1 .= " and player_id =".$player_id;

		$result = mysql_query($queryString1) or die (mysql_error()); 
		list($handicap_value) = mysql_fetch_row($result);
		
		$queryString = " select is_submit_score";
		$queryString .= " from event_player_list";
		$queryString .= " where event_id =".$event_id;
		$queryString .= " and player_id =".$player_id;
		$result = mysql_query($queryString) or die (mysql_error()); 
		list($is_started) = mysql_fetch_row($result);
		if($is_started=="1")
		{

			$par_value_array = array();
			$hole_index_array = array();
			$par_sum = 0;
			$hole_sum = 0;
			$queryString = "select ";
			$queryString .= " golf_hole_index_id, ";
			
			$qarr=array(); $qarr1=array();
			
			for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
				$qarr[]=" par_value_{$i}";
				$qarr1[]=" hole_index_{$i}";
			}
			
			$queryString .= implode(",",$qarr);
			$queryString .= ",".implode(",",$qarr1);
			$queryString .= " from golf_hole_index";
			$queryString .= " where golf_course_id = ".$golf_course_id;


			$result = mysql_query($queryString) or die (mysql_error()); 
			
				
			$row_index = mysql_fetch_array($result);
				
			$golf_hole_index_id = $row_index['golf_hole_index_id'];
			
			$par_value_array=array();
			$hole_index_arr_all=array();
			for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
				$par_value_array[$i] = $row_index["par_value_{$i}"];
				$hole_index_arr_all[$i] = $row_index["hole_index_{$i}"];
			}
			
			
			$par_sum = array_sum($par_value_array);
			$hole_sum = array_sum($hole_index_arr_all);
			
			
			
			foreach ($par_value_array as $key => $value) 
			{
				$queryString1 = " select event_score_calc.score_entry_".$key." from event_score_calc ";
				$queryString1 .= " where event_id =".$event_id;
				$queryString1 .= " and player_id =".$player_id;
				
				$result = mysql_query($queryString1) or die (mysql_error()); 
				list($gross_score) = mysql_fetch_row($result);


				$score_array[$key] = $gross_score;
				$total_sum = $total_sum + $gross_score;
				if($value == 3)
				{
					$sum_par3 = $sum_par3 + $gross_score;
					$count_par3++;
				}
				else if($value == 4)
				{
					$sum_par4 = $sum_par4 + $gross_score;
					$count_par4++;
				}
				else if($value == 5)
				{
					$sum_par5 = $sum_par5 + $gross_score;
					$count_par5++;
				}
				if($key <= 9)
				{
					$sum_first9 = $sum_first9 + $gross_score;
					$sum_par_first9 = $sum_par_first9 + $value;
				}
				else if($key > 9)
				{
					$sum_last9 = $sum_last9 + $gross_score;
					$sum_par_last9 = $sum_par_last9 + $value;
				}
				$difference =  $gross_score - $value;
				if( $difference <= -2)
				{
					$no_of_eagle = $no_of_eagle + 1;
				}
				else if( $difference == -1)
				{
					$no_of_birdies = $no_of_birdies + 1;
				}
				else if( $difference == 0)
				{
					$no_of_pars = $no_of_pars + 1;
				}
				else if( $difference == 1)
				{
					$no_of_bogeys = $no_of_bogeys + 1;
				}
				else if( $difference >= 2)
				{
					$no_of_double_bogeys = $no_of_double_bogeys + 1;
				}

			}
			//print_r($score_array);
			$avg_par3 = ($count_par3>0) ? round($sum_par3 /$count_par3,2) : 0;
			$avg_par4 = ($count_par4>0) ? round($sum_par4 /$count_par4,2) : 0;
			$avg_par5 = ($count_par5>0) ? round($sum_par5 /$count_par5,2) : 0;
			$avg_first9 = round($sum_first9/9,2);
			$avg_last9 = round($sum_last9/9,2);
			
		}
		
		$row_style = "width:100%;float:left;";
		$row_div_style = "display:inline-block;padding:4px 1%;float:left;";
		$table_grid_row_style="border-bottom: 1px solid #b7b7b8;text-align: center;width:2%;height:22px;";
		
		$box_width = ($total_hole_num==9) ? "5.3" : "1.4";
		
		$fb_str = base64_encode(json_encode(array("type"=>"facebook","event_id"=>$event_id,"player_id"=>$player_id)));;
		$tw_str = base64_encode(json_encode(array("type"=>"twitter","event_id"=>$event_id,"player_id"=>$player_id)));;
		$fb_share_link = "http://www.facebook.com/share.php?u=".urlencode($pathString.'/share/scorecard.php?id='.$fb_str);;
		$tw_share_link_o = ($pathString.'/share/scorecard.php?id='.$tw_str);
		$tw_share_link = get_bitly_short_url(($pathString.'/share/scorecard.php?id='.$tw_str));
		$scorecard_link = $pathString.'/share/image.php?raw=1&id='.$fb_str;;
		$event_date = date("M d,Y",strtotime($event_start_date_time));
		$tw_msg = "Follow my official putt2gether scorecard played on {$event_date} at {$golf_course_name} {$tw_share_link}";
		$twlink = "https://twitter.com/intent/tweet?hashtags=&original_referer=".urlencode($tw_share_link_o)."&ref_src=".urlencode("twsrc^tfw")."&related=&text=".urlencode($tw_msg)."&tw_p=tweetbutton&url=&via=putt2gether";
		
		
		
		
		
		$newsletter_body ="";
		if(!$only_body) {
			$newsletter_body .= "<html>\n";
			$newsletter_body .= "<head><meta http-equiv='Content-Type'  content='text/html charset=UTF-8' />\n";
			$newsletter_body .= "<title>putt2gether scorecard</title>\n";
			$newsletter_body .= '<meta name="viewport" content="width=860, width=device-width, initial-scale=1.0">';
			$newsletter_body .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
			/*$newsletter_body .= "<style>\n";
			
			
			$newsletter_body .= ".bg-blue {\n";
			$newsletter_body .= "	background-color: #0a5b86;color: #fff;";
			$newsletter_body .= "}\n";
			$newsletter_body .= ".bg-gray {\n";
			$newsletter_body .= "	background-color: #939494; color: #fff;";
			$newsletter_body .= "}\n";
			$newsletter_body .= ".bg-black {\n";
			$newsletter_body .= "	background-color: #000000;	color: #fff;";
			$newsletter_body .= "}\n";
			$newsletter_body .= ".bg-yellow {\n";
			$newsletter_body .= "background-color:#f2a942; color:#fff;";
			$newsletter_body .= "}\n";
			$newsletter_body .= "</style>\n";*/
			$newsletter_body .= "</head>\n";
			$newsletter_body .= "<body bgcolor=\"#FFFFFF\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n";
		}
		
/*
		$newsletter_body .= "<table width=\"860\" border=\"0\" cellspacing=\"0\" cellpadding=\"15\" bgcolor=\"#dee3d8\">\n";
		$newsletter_body .= "  <tr>\n";
		$newsletter_body .= "    <td>";*/
		
		$newsletter_body .= "<table id=\"Table_01\" width=\"830\" height=\"953\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#ffffff\" style=\"font-family:arial;font-size:14px;color:#000;\">\n";
		$newsletter_body .= "        <tr>\n
          <td colspan=\"18\"><img src=\"".$pathString."/images/banner.jpg\" width=\"829\" height=\"273\" alt=\"\"></td>
          <td></td>
        </tr>\n";
		if($saveImg) {$newsletter_body .= "        <tr><td colspan=\"18\">";
			$newsletter_body .= '<table width="100%" border="0" cellspacing="0" cellpadding="10" style="border:1px solid #ccc;">
              <tr>
                <td style="border-right:1px solid #ccc;border-bottom:1px solid #ccc;width:25%">Date</td>
                <td style="border-right:1px solid #ccc;border-bottom:1px solid #ccc;width:25%">Player Name</td>
                <td style="border-right:1px solid #ccc;border-bottom:1px solid #ccc;width:25%">Golf Course</td>
                <td style="border-bottom:1px solid #ccc;width:25%">Location</td>
              </tr>
              <tr>
                <td style="border-right:1px solid #ccc;width:25%">'.date("F d,Y",strtotime($event_start_date_time)).'</td>
                <td style="border-right:1px solid #ccc;width:25%">'.$full_name.'</td>
                <td style="border-right:1px solid #ccc;width:25%">'.$golf_course_name.'</td>
                <td style="width:25%">'.$city_name."<br/>".$state_name.'</td>
              </tr>
            </table></td></tr>';
		}
		else {
			$newsletter_body .= "        <tr>\n";
			
			
			
					$newsletter_body .= "<td colspan=\"5\" style=\"padding:0 9px;font-family:arial;font-size:14px;color:#000;line-height:22px;\"><em>".date("F d,Y",strtotime($event_start_date_time))."</em><br><span style=\"font-size:15px;font-weight:bold;\">".$full_name."</span><br>".$golf_course_name."<br>
	".$city_name.", ".$state_name."</td>";
	
			$newsletter_body .= "<td colspan=\"5\" style=\"line-height:22px;\" align=\"center\"><a href=\"{$fb_share_link}\" target=\"_blank\"><img src=\"".$pathString."/images/facebook.jpg\" width=\"245\" height=\"60\" alt=\"\"></a></td>
			  <td colspan=\"8\" style=\"line-height:22px;\" align=\"center\"><a  href=\"{$twlink}\" target=\"_blank\"><img src=\"".$pathString."/images/twitter.jpg\" width=\"245\" height=\"60\" alt=\"\"></a></td>
			  <td></td>";
			$newsletter_body .= "        </tr>\n";
		}
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" bgcolor=\"#325604\" style=\"padding:0 15px;color:#fff;font-size:19px;font-weight:normal;\">Scorecard</td>\n";
		$newsletter_body .= "          <td></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" valign=\"top\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"9\" class=\"\">\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td width=\"30%\" style=\"text-align:left;color:#818181;border-bottom: solid 1px #b7b7b8;border-right: solid 1px #b7b7b8;\">Hole</td>\n";
		for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
			$newsletter_body .= "                <td width=\"3%\" style=\"border-bottom: 1px solid #b7b7b8;border-right: solid 1px #b7b7b8; text-align: center;\">{$i}</td>\n";
		}
		
		$newsletter_body .= "                <td width=\"10%\" style=\"border-bottom: 1px solid #b7b7b8; text-align: center;\">Total</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td style=\"text-align:left;color:#818181;border-bottom: solid 1px #b7b7b8;border-right: solid 1px #b7b7b8;\">Par</td>\n";
		
		foreach($par_value_array as $par_key => $par_value) {
			$newsletter_body .= "<td style=\"border-bottom: 1px solid #b7b7b8;border-right: solid 1px #b7b7b8; text-align: center;\">".$par_value."</td>\n";
		}
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;\">".$par_sum."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td style=\"text-align:left;color:#818181;border-right: solid 1px #b7b7b8;\">Index</td>\n";
		
		foreach($hole_index_arr_all as $hole_index_key => $hole_index_value) {
			$newsletter_body .= "<td style=\"border-right: solid 1px #b7b7b8; text-align: center;\">".$hole_index_value."</td>\n";
		}
		
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;\"></td>\n";
		
		//$hole_sum
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "                <td bgcolor=\"#e9eaeb\" style=\"font-size:18px;text-align:left;\">".$full_name."<span style=\"color:#325604;\">(".$handicap_value.")</span></td>\n";
		
		$green_style="background-color: #325604;";
		$gray_style="background-color: #939494;";
		$blue_style="background-color: #0a5b86;";
		$black_style="background-color: #000000;";
		$golden_style="background-color: #f2a942;";
		
		for($x=$hole_start_from;$x<=$total_hole_num;$x++) {
			$y=$x;
			$my_score=$score_array[$x];
			$hole_score = $par_value_array[$x];
			$diff = ($hole_score - $my_score);
			if($diff == 1 ) {
				$bg_color = $blue_style;
			}
			elseif($diff == -1) {
				$bg_color = $gray_style;
			}
			elseif($diff >= 2) {
				$bg_color = $golden_style;
			}
			elseif($diff <= -2) {
				$bg_color = $black_style;
			}
			elseif($diff == 0) {
				$bg_color = $green_style;
			}
			else {
				$bg_color = '';
			}
			$newsletter_body .= "<td style=\" {$bg_color}	color: #fff; text-align: center;border-right: solid 1px #ffffff;\">".$my_score."</td>\n";
		}
		
		$newsletter_body .= "                <td style=\"color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;\">".$total_sum."</td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "            </table></td>\n";
		$newsletter_body .= "          <td></td>\n";
		$newsletter_body .= "        </tr>\n";
		
		$newsletter_body .= "  <tr>
          <td colspan=\"18\" bgcolor=\"#dbe2d4\" style=\"padding:0 9px;color:#325604;font-size:19px;font-weight:normal;\">Key Stats</td>
          <td></td>
        </tr>";
		
		$newsletter_body .= "<tr>
          <td colspan=\"18\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\" style=\"font-size:18px;\">
              <tr>
                <td style=\"float:right;\">Par 3 Avg: </td>
                <td><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par3,1)."</span></td>
                <td style=\"float:right;\">Par 4 Avg:</td>
                <td><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par4,1)."</span></td>
                <td style=\"float:right;\">Par 5 Avg:</td>
                <td><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par5,1)."</span></td>
              </tr>
              <tr>
			  	<td colspan=\"2\"></td>";
				
                $newsletter_body .= "<td align=\"center\">Front 9- ".$sum_first9." / ".$sum_par_first9."</td>";
				
				
               	$newsletter_body .= "<td align=\"center\">Back 9- ".$sum_last9." / ".$sum_par_last9."</td>";
				
				$newsletter_body .= "<td colspan=\"2\"></td>
              </tr>
              
            </table></td>
          <td></td>
        </tr>";
		
		
		
		$newsletter_body .= "        <tr>\n";
		$newsletter_body .= "          <td colspan=\"18\" height=\"27\" style=\"border-top:1px solid #ccc;\"> </td>\n";
		$newsletter_body .= "          <td></td>\n";
		$newsletter_body .= "        </tr>\n";
		$newsletter_body .= "<tr>
          <td colspan=\"18\" height=\"27\">
          		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                  <tr>
                    <td align=\"right\" width=\"15%\" style=\"padding-right:10px;\">Eagle +</td>
                    <td align=\"center\" class=\"bg-yellow\" style=\"background-color:#f2a942; color:#fff; font-size:18px;padding:5px 3px;\" >".$no_of_eagle."</td>
                    <td align=\"right\" width=\"15%\" style=\"padding-right:10px;\">Birdie</td>
                    <td align=\"center\" class=\"bg-blue\"  style=\"background-color: #0a5b86;color: #fff; font-size:18px;padding:5px 3px;\">".$no_of_birdies."</td>
                    <td align=\"right\" width=\"15%\" style=\"padding-right:10px;\">Par</td>
                    <td align=\"center\" style=\"background-color: #325604;	color: #fff; font-size:18px;padding:5px 3px;\">".$no_of_pars."</td>
                    <td align=\"right\" width=\"15%\" style=\"padding-right:10px;\">Bogey</td>
                    <td align=\"center\" class=\"bg-gray\" style=\"background-color: #939494; color: #fff; font-size:18px;padding:5px 3px;\">".$no_of_bogeys."</td>
                    <td align=\"right\" width=\"15%\" style=\"padding-right:10px;\">D.Bogey +</td>
                    <td align=\"center\" class=\"bg-black\" style=\"background-color: #000000;	color: #fff; font-size:18px;padding:5px 3px;\">".$no_of_double_bogeys."</td>
                    <td width=\"50\"> </td>
                  </tr>
                </table>

          </td>
          <td></td>
        </tr>";
		$newsletter_body .= "<tr>
          <td colspan=\"18\" style=\"padding:0px;color:#fff;font-size:14px;font-weight:normal;\" align=\"center\">&nbsp;</td>
          <td></td>
        </tr>";
		 $newsletter_body .= "<tr>
          <td colspan=\"18\" bgcolor=\"#325604\" style=\"padding:0 15px;color:#fff;font-size:14px;font-weight:normal;\" align=\"center\"><strong>putt2gether</strong> - Your own leaderboard app</td>
          <td></td>
        </tr>";	
		
		
		$newsletter_body .= "      </table>";
		/*
		$newsletter_body .= "</td>\n";
		$newsletter_body .= "  </tr>\n";
		$newsletter_body .= "</table>\n";*/
		if(!$only_body) {
			$newsletter_body .= "\n";
			$newsletter_body .= "</body>\n";
			$newsletter_body .= "</html>\n";
		}
				
		
		
		/*$newsletter_body ="";
		if(!$only_body) {
			$newsletter_body .= "<html>\n";
			$newsletter_body .= "<head><meta http-equiv='Content-Type'  content='text/html Content-Type: text/html; charset=ISO-8859-1' />\n";
			$newsletter_body .= "<title>putt2gether scorecard</title>\n";
			$newsletter_body .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
			$newsletter_body .= "</head>\n";
			$newsletter_body .= "<body bgcolor=\"#FFFFFF\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n";
		}
		$newsletter_body .= "<div id=\"Table_01\" style=\"font-family:arial;font-size:14px;color:#000;width:830px;background-color:#fff;\">\n";
		$newsletter_body .= "<div style=\"{$row_style}\"><img src=\"".$pathString."/images/banner.jpg\" width=\"829\" height=\"273\" alt=\"\"></div>\n";
		if($saveImg) {
		$newsletter_body .= "<div style=\"{$row_style}\">";
        $newsletter_body .= "<div style=\"{$row_div_style} width:98%;padding:10px 1%;display:table\">";
        $newsletter_body .= "<div style=\"{$row_div_style} width:98%;border:1px solid #ccc;padding:0;display:flex;float:left;\">";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;float:none;padding:5px;border-right:1px solid #ccc;\">Date</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;float:none;padding:5px;border-right:1px solid #ccc;\">Player Name</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;float:none;padding:5px;border-right:1px solid #ccc;\">Golf Course</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;float:none;padding:5px;\">Location</div>";
        $newsletter_body .= "</div> ";
        $newsletter_body .= "<div style=\"{$row_div_style} width:98%;padding:0;border:1px solid #ccc;border-top:0; display:flex;float:left;\">";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;border-right:1px solid #ccc;float:left;padding:5px;min-height:39px;\">".date("F d,Y",strtotime($event_start_date_time))."</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;border-right:1px solid #ccc;float:left;padding:5px;min-height:39px;\">".$full_name."</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;border-right:1px solid #ccc;float:left;padding:5px;min-height:39px;\">".$golf_course_name."</div>";
        $newsletter_body .= "<div style=\"{$row_div_style} width:23.4%;padding:5px;min-height:39px;\">".$city_name."<br/>".$state_name."</div></div></div></div>";
		}
		else {
		
			$newsletter_body .= "<div style=\"{$row_style}\">\n";
			$newsletter_body .= "<div style=\"{$row_div_style} width:98%;padding:10px 1%\">\n";
			$newsletter_body .= "<div style=\"{$row_div_style} font-family:arial;font-size:14px;color:#000;line-height:22px;margin-right: 50px;width:185px;\"><em>".date("F d,Y",strtotime($event_start_date_time))."</em><br><span style=\"font-size:15px;font-weight:bold;\">".$full_name."</span><br>".$golf_course_name."<br>
		".$city_name.", ".$state_name."</div>\n";
			$newsletter_body .= "<div style=\"{$row_div_style} line-height:22px;padding: 19px 2%;\" align=\"center\"><a href=\"{$fb_share_link}\" target=\"_blank\"><img src=\"".$pathString."/images/facebook.jpg\" width=\"245\" height=\"60\" alt=\"\"></a></div>
			  <div style=\"{$row_div_style} line-height:22px;padding: 19px 2%;\" align=\"center\"><a href=\"{$twlink}\" target=\"_blank\"><img src=\"".$pathString."/images/twitter.jpg\" width=\"245\" height=\"60\" alt=\"\"></a></div>";
			$newsletter_body .= "        </div>\n";
			$newsletter_body .= "        </div>\n";
		}
		
		$newsletter_body .= "<div style=\"{$row_style}\">
          <div style=\"{$row_div_style} padding:5px 9px;color:#fff;font-size:19px;font-weight:normal;width:98%;background-color:#325604;\">Scorecard</div>
        </div>\n";
		$newsletter_body .= "<div width=\"98%\" class=\"table-grid\"><div style=\"{$row_style}\">\n";
		$newsletter_body .= "<div  style=\"{$row_div_style} text-align:left;color:#818181;border-right: solid 1px #b7b7b8;width:20%;padding: 10px;border-bottom: solid 1px #b7b7b8;\">Hole</div>\n";
		for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
			$newsletter_body .= " <div width=\"3%\" style=\"{$row_div_style} border-bottom: 1px solid #b7b7b8;border-right: solid 1px #b7b7b8; text-align: center;width:{$box_width}%;padding: 10px;\">{$i}</div>\n";
		}
		
		$newsletter_body .= " <div width=\"10%\" style=\"{$row_div_style} border-bottom: 1px solid #b7b7b8; text-align: center;padding: 10px;\">Total</div>\n";
		$newsletter_body .= " </div>\n";
		$newsletter_body .= " <div style=\"{$row_style}\">\n";
		$newsletter_body .= " <div style=\"{$row_div_style} text-align:left;color:#818181;border-right: solid 1px #b7b7b8;width:20%;padding: 10px;border-bottom: solid 1px #b7b7b8;\">Par</div>\n";
		
		foreach($par_value_array as $par_key => $par_value) {
			$newsletter_body .= " <div style=\"{$row_div_style} border-bottom: 1px solid #b7b7b8;border-right: solid 1px #b7b7b8; text-align: center;width:{$box_width}%;padding: 10px;\">".$par_value."</div>\n";
		}
		$newsletter_body .= " <div style=\"{$row_div_style} color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;padding: 7px;width:4.5%\">".$par_sum."</div>\n";
		$newsletter_body .= " </div>\n";
		$newsletter_body .= " <div style=\"{$row_style}\">\n";
		$newsletter_body .= " <div style=\"{$row_div_style} text-align:left;color:#818181;border-right: solid 1px #b7b7b8;width:20%;padding: 10px;\">Index</div>\n";
		
		foreach($hole_index_arr_all as $hole_index_key => $hole_index_value) {
			$newsletter_body .= " <div style=\"{$row_div_style} border-right: solid 1px #b7b7b8; text-align: center;width:{$box_width}%;padding: 10px;\">".$hole_index_value."</div>\n";
		}
		
		$newsletter_body .= " <div style=\"{$row_div_style} color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;width: 4.5%;height: 21px;padding: 7px;\"></div>\n";
		
		//$hole_sum
		$newsletter_body .= " </div>\n";
		$newsletter_body .= " <div>\n";
		$newsletter_body .= " <div style=\"{$row_div_style} font-size:18px;text-align:left;width:20.8%;background-color:#e9eaeb;padding: 7px;\">".$full_name."<span style=\"color:#325604;\">(".$handicap_value.")</span></div>\n";
		
		$green_style="background-color: #325604;";
		$gray_style="background-color: #939494;";
		$blue_style="background-color: #0a5b86;";
		$black_style="background-color: #000000;";
		$golden_style="background-color: #f2a942;";
		
		for($x=$hole_start_from;$x<=$total_hole_num;$x++) {
			$y=$x;
			$my_score=$score_array[$x];
			$hole_score = $par_value_array[$x];
			$diff = ($hole_score - $my_score);
			if($diff == 1 ) {
				$bg_color = $blue_style;
			}
			elseif($diff == -1) {
				$bg_color = $gray_style;
			}
			elseif($diff >= 2) {
				$bg_color = $golden_style;
			}
			elseif($diff <= -2) {
				$bg_color = $black_style;
			}
			elseif($diff == 0) {
				$bg_color = $green_style;
			}
			else {
				$bg_color = '';
			}
			$newsletter_body .= " <div style=\"{$row_div_style}  {$bg_color}	color: #fff; text-align: center;border-right: solid 1px #ffffff;width:{$box_width}%;padding: 10px;\">".$my_score."</div>\n";
		}
		
		$newsletter_body .= " <div style=\"{$row_div_style} color:#818181;font-size:18px; border-bottom: 1px solid #b7b7b8; text-align: center;width: 4.5%; padding: 7px;\">".$total_sum."</div>\n";
		$newsletter_body .= "</div>\n";
		$newsletter_body .= " </div>\n";
		
		$newsletter_body .= "<div style=\"{$row_style} color:#325604;font-size:19px;font-weight:normal;background-color:#dbe2d4; margin-top:30px;\"><div style=\"{$row_div_style} padding:10px; \">Key Stats</div>
          </div>\n";
		
		$newsletter_body .= "<div style=\"{$row_style}margin-top: 20px;font-size: 18px;\">
        	<div style=\"{$row_style}\">
            	<div style=\"{$row_div_style} width:21%;margin-left: 85px;\">
                    <div style=\"{$row_div_style}\">Par 3 Avg: </div>
                    <div style=\"{$row_div_style}\"><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par3,1)."</span></div>
                </div>
                <div style=\"{$row_div_style} width:27%;margin-left: 55px;\">
                    <div style=\"{$row_div_style}\">Par 4 Avg:</div>
                    <div style=\"{$row_div_style}\"><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par4,1)."</span></div>
                </div>
                <div style=\"{$row_div_style} width:26%;margin-left: 20px;\">
                    <div style=\"{$row_div_style}\">Par 5 Avg:</div>
                    <div style=\"{$row_div_style}\"><span style=\"background-color:#eaeaea;border-radius:20px;padding:7px;\">".number_format($avg_par5,1)."</span></div>
                </div>
              </div>
            <div style=\"{$row_style}\">
            	<div style=\"{$row_div_style} width:30%;text-align:center;padding-left:19%;margin: 20px 0 20px 0;font-size: 18px;\">
                    <div style=\"text-align:center;\">Front 9- ".$sum_first9." / ".$sum_par_first9."</div>
                </div>
                <div style=\"{$row_div_style} width:29%;margin: 20px 0 20px 0;font-size: 18px;\">
                    <div style=\" text-align:center;\">Back 9- ".$sum_last9." / ".$sum_par_last9."</div>
                </div>
              </div>
        </div>
        <div style=\"{$row_style} border-top:1px solid #ccc;height:17px;\">
          
        </div>";
		
		$newsletter_body .= "<div style=\"{$row_style}\">
        	<div style=\"{$row_div_style} padding:9px 1%;width:98%\">
                <div style=\"{$row_div_style} padding-right:10px;width:12%;text-align:right;\">Eagle +</div>
                <div align=\"center\" class=\"bg-yellow\" style=\"{$row_div_style} background-color:#f2a942;color:#fff;font-size:18px;padding:5px;width:20px;\">".$no_of_eagle."</div>
                <div style=\"{$row_div_style} padding-right:10px;width:12%;text-align:right;\">Birdie</div>
                <div align=\"center\" class=\"bg-blue\" style=\"{$row_div_style} background-color: #0a5b86;color: #fff;font-size:18px;padding:5px;width:20px;\">".$no_of_birdies."</div>
                <div style=\"{$row_div_style} padding-right:10px;width:12%;text-align:right;\">Par</div>
                <div align=\"center\" class=\"bg-green\" style=\"{$row_div_style} background-color: #325604;	color: #fff; font-size:18px;padding:5px;width:20px;\">".$no_of_pars."</div>
                <div style=\"{$row_div_style} padding-right:10px;width:12%;text-align:right;\">Bogey</div>
                <div align=\"center\" class=\"bg-gray\" style=\"{$row_div_style} background-color: #939494; color: #fff; font-size:18px;padding:5px;width:20px;\">".$no_of_bogeys."</div>
                <div style=\"{$row_div_style} padding-right:10px;width:12%;text-align:right;\">D.Bogey +</div>
                <div align=\"center\" class=\"bg-black\" style=\"{$row_div_style} background-color: #000000;	color: #fff;font-size:18px;padding:5px;width:20px;\">".$no_of_double_bogeys."</div>
                <div width=\"50\" style=\"{$row_div_style} \"></div>
            </div>
          </div>";
		
		$newsletter_body .= "<div style=\"{$row_style}\">
          <div colspan=\"18\" style=\"{$row_div_style} \" height=\"27\"></div>
        </div>
        <div style=\"{$row_style} color:#fff;font-size:14px;font-weight:normal;background-color:#325604;text-align:center;\" ><div style=\"{$row_div_style};padding:12px;float:none; \"><strong>putt2gether</strong> - Your own leaderboard app</div>
        </div>
      </div>";
		if(!$only_body) {
			$newsletter_body .= "\n";
			$newsletter_body .= "</body>\n";
			$newsletter_body .= "</html>\n";
		}*/
		//if($saveImg){echo saveScorecardImage($scorecard_link,$event_id,$player_id);}
		if($return_html) {
			$jsn = $newsletter_body;
			/*$jsn = str_replace('\n','',$jsn);
			$jsn = str_replace('\r','',$jsn);
			$jsn = str_replace('\t','',$jsn);*/ 
			//$jsn = str_replace('width:','min-width:',$jsn);
			$jsn = trim(preg_replace('/\s+/', ' ', $jsn));
			
			return html_entity_decode(stripslashes($jsn));;
		}
		else {
			$subject="putt2gether scorecard";
			$to = $user_name;
			$toname=$full_name; //echo $newsletter_body;
			$obj=new Mail($to, $toname, $subject,$newsletter_body);
			if($from==""){
				$obj->_from='info@putt2gether.com';
				$obj->_fromName='Team Putt2gether';
			}else{
				$obj->_from=$from;
				$obj->_fromName=$from_name;
			}	
			$obj->Send();
		}
}

function saveScorecardImage($path='',$event_id,$player_id) {
	$file_name = $event_id.'_'.$player_id.'.jpg';
	//$file_path = 'share/scorecard/images/'.$event_id.'/';
	$file_path = 'share/scorecard/images/';
	$basepath=BASE_PATH.'/'.$file_path.$file_name;;
	//$baseurl=__BASE_URI__.'share/scorecard/images/'.$event_id.'/'.$file_name;;
	$baseurl=__BASE_URI__.'share/scorecard/images/'.$file_name;;
	if(trim($path)=='') {
		$fb_str = base64_encode(json_encode(array("type"=>"facebook","event_id"=>$event_id,"player_id"=>$player_id)));;
		$path = __BASE_URI__.'share/index.php?id='.$fb_str;;
	}
	if(file_exists($basepath)) {
		return $baseurl;
	}
	else {
		$path_full = "http://api.screenshotmachine.com/?key=b83c4f&size=F&format=cacheLimit=0&timeout=0&url=".urlencode($path);
		$base64code=file_get_contents($path_full);
		convertRawData2Image($base64code,$basepath,true);
		//die;
		//echo $html=sendScoreCard($event_id,$player_id,true,true,false);die;
		//$html = "data:image/png;base64,".base64_encode(($html));
		//saveImageFromBase64($html,$file_path,$file_name);
		return $baseurl;
	}
}

function saveImageFromBase64($base64img,$save_path,$file_name=''){

    require_once('Thumbnail.php'); 
    $serverdir=BASE_PATH.'/'.$save_path;;
    $thumbserverdir=BASE_PATH.'/'.$save_path.'thumb/';
    if(!is_dir($serverdir)){
        mkdir($serverdir);
        chmod($serverdir,0777);
    }
    if(!is_dir($thumbserverdir)){
        mkdir($thumbserverdir);
        chmod($thumbserverdir,0777);
    }
    $datetime=time();
   
    
    if(trim($file_name)=='') {
    	$file_name= md5($datetime.rand(5345,6564565)).'.png';
    }

    $file = $serverdir.$file_name;//die;
    $success =  convertRawData2Image($base64img,$file);
	
    
    
    if($success){
    
    	$thumbdir = $thumbserverdir.$file_name;
    
        $thumb = new Thumbnail($file);
        $thumb->resize(240,180);
        $thumb->save($thumbdir);
        //return  $file_name;
        return  true;
     }
	 else{
        return 0;
    }	 	
}

function convertRawData2Image($rawDataString, $outputImageFile,$raw=false) {


    $imageFilePointer = fopen($outputImageFile, "wb");
	if(!$raw) {
    	$imageActualData = explode(',', $rawDataString); $ss = str_replace(' ', '+', $imageActualData[1]);
		$ss = base64_decode($ss);
	}
	else {
		//$ss = resizeImage($outputImageFile,$rawDataString,'830','920',0,0,1);
		$ss = $rawDataString;
		
	}
    fwrite($imageFilePointer, $ss);
    fclose($imageFilePointer);
	
	makeIcons_MergeCenter($outputImageFile,$outputImageFile,830,865);
	
	/*$im = imagecreatefromjpeg($outputImageFile );
	list($width,$height) = getimagesize($outputImageFile);
	
	$thumb_im = imagecreatetruecolor(830, 865);
	
	//$thumb_im = imagecrop($im, $to_crop_array);
	imagecopyresized($thumb_im, $im, 0, 0, 830,0, 830, 865, $width, $height);
	unlink($outputImageFile);
	imagejpeg($thumb_im, $outputImageFile, 100);*/
	
	//$img = $imageActualData[1];
    return $outputImageFile;
}

function makeIcons_MergeCenter($src, $dst, $dstx, $dsty){

//$src = original image location
//$dst = destination image location
//$dstx = user defined width of image
//$dsty = user defined height of image

$allowedExtensions = 'jpg jpeg gif png';

$name = explode(".", $src);
$currentExtensions = $name[count($name)-1];
$extensions = explode(" ", $allowedExtensions);

for($i=0; count($extensions)>$i; $i=$i+1){
if($extensions[$i]==$currentExtensions)
{ $extensionOK=1; 
$fileExtension=$extensions[$i]; 
break; }
}

if($extensionOK){

$size = getimagesize($src);
$width = $size[0];
$height = $size[1];

if($width >= $dstx && $height >= $dsty){

$proportion_X = $width / $dstx;
$proportion_Y = $height / $dsty;

if($proportion_X > $proportion_Y ){
$proportion = $proportion_Y;
}else{
$proportion = $proportion_X ;
}
$target['width'] = $dstx * $proportion;
$target['height'] = $dsty * $proportion;

$original['diagonal_center'] = 
round(sqrt(($width*$width)+($height*$height))/2);
$target['diagonal_center'] = 
round(sqrt(($target['width']*$target['width'])+
($target['height']*$target['height']))/2);

$crop = round($original['diagonal_center'] - $target['diagonal_center']);

if($proportion_X < $proportion_Y ){
$target['x'] = 0;
$target['y'] = round((($height/2)*$crop)/$target['diagonal_center']);
}else{
$target['x'] =  round((($width/2)*$crop)/$target['diagonal_center']);
$target['y'] = 0;
}

if($fileExtension == "jpg" || $fileExtension=='jpeg'){ 
$from = imagecreatefromjpeg($src); 
}elseif ($fileExtension == "gif"){ 
$from = imagecreatefromgif($src); 
}elseif ($fileExtension == 'png'){
$from = imagecreatefrompng($src);
}

$new = imagecreatetruecolor($dstx,$dsty);

imagecopyresampled ($new,  $from,  0, 0, $target['x'], 
$target['y'], $dstx, $dsty, $target['width'], $target['height']);

unlink($src);

if($fileExtension == "jpg" || $fileExtension == 'jpeg'){ 
imagejpeg($new, $dst, 70); 
}elseif ($fileExtension == "gif"){ 
imagegif($new, $dst); 
}elseif ($fileExtension == 'png'){
imagepng($new, $dst);
}
}
}
}

function time_ago($ptime){
	if(!is_numeric($ptime)) {$ptime=strtotime($ptime);}
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );
	
	$b_skip = array('hour','hours','minute','minutes','second','seconds');

    foreach ($a as $secs => $str)
    {
		$d = $etime / $secs;
		if ($d >= 1)
		{
			$r = round($d);
			$z = ($r > 1 ? $a_plural[$str] : $str);
			
			return (!in_array($z,$b_skip)) ? date("d M Y - H:i",$ptime) : ($r . ' ' . $z . ' ago');
		}
    }
}

function sendPushNotification($type=0) { //sleep(3);
	$push = new sendPushClass();
	return $push->sendPushNotification('');
}

function sendEventInviteMail($emails,$event_id) {
	global $database;
	if(!(is_numeric($event_id) && $event_id>0)) {return false;}
	$emails = is_array($emails) ? $emails : array($emails);
	$emails = array_unique($emails);
	if(count($emails)>0) {
		$sql = 'select e.*,u.display_name,u.full_name from event_list_view e left join golf_users u on e.admin_id = u.user_id where e.event_id = "'.$event_id.'" ';
		
		$event = $database->FetchRow($sql);
		if($event) {
			
			$admin_name = (trim($event['display_name'])!='') ? $event['display_name'] : $event['full_name'];;
			
			$subject = 'Golf event invite from '.$admin_name;
			
			$msg = '';
			$msg="Hi!<br />I'm inviting you to a golf event '".$event['event_name']."' at ".$event['golf_course_name']." using the putt2gether app.<br /><br />";
			$msg.="Follow these 3 STEPS to join the event :<br /><br />";
			$msg.="<strong>Step 1 :</strong> Download putt2gether (links for iOS & android)<br /><a href='https://itunes.apple.com/in/app/putt2gether-live-leaderboard/id1002496721?mt=8'><img src='".__BASE_URI__."newsletter/app-store.png'  style='float:left;margin:0;padding:0;outline:none;'></a> <a href='https://play.google.com/store/apps/details?id=com.putt2gether'><img src='".__BASE_URI__."newsletter/google-play.png'  style='float:left;margin:0;padding:0;outline:none;'></a><br><br>
<div style='clear:both;'></div><br />";
			$msg.="<strong>Step 2 :</strong> Go to 'Request to Participate' under Invites section<br /><br />";
			$msg.="<strong>Step 3 :</strong> Select golf course name = '".$event['golf_course_name']."', select date = '".date("d/M/y",strtotime($event['event_start_date_time']))."', select event =  '".$event['event_name']."' and send the request to join the event.<br /><br />";
			$msg.="Thanks<br />";
			$msg.=$admin_name;
			$cnt=0; $ins_arr = array();
			
			// get all emails already invited
			$sql2 = "select email from event_invite_players where event_id='{$event_id}'";
			$result2 = $database->FetchQuery($sql2);
			
			$all_emails = array();
			foreach($result2 as $s) {
				$all_emails[] = $s['email'];
			}
			
			// save into invite table
			foreach($emails as $a=>$b) {
				if(!in_array(trim($b),$all_emails)) {
					$ins_arr[]='("'.$event_id.'", "'.$database->escape(trim($b)).'", "0", now(), now(), "'.$database->escape(trim($subject)).'", "'.$database->escape(trim($msg)).'")';
					$all_emails[] = trim($b);
				}
			}
			if(count($ins_arr)>0) {
				$sql_insert = "INSERT INTO `event_invite_players` (`event_id`, `email`, `is_send`, `create_date`, `modified_date`, `inv_subject`, `invite_msg`) VALUES ".implode(',',$ins_arr);
				return ($database->FetchQuery($sql_insert)) ? true : false;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}
function GetLatLngFromIP($ip='') {
	global $_SERVER;
	if(trim($ip)=='') {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$arr = (unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip)));

	if(isset($arr["geoplugin_latitude"]) && isset($arr["geoplugin_longitude"])) {
		return array($arr["geoplugin_latitude"],$arr["geoplugin_longitude"]);
	}
	return false;
}

if(isset($_GET['test']) && $_GET['test']=='1') {
	echo phpinfo();
	//echo sendScoreCard(276,3,true);
}

 function generatePIN($digits){
    $i = 0; //counter
    $pin = ""; //our default pin is blank.
    while($i < $digits){
        //generate a random number between 0 and 9.
        $pin .= mt_rand(0, 9);
        $i++;
    }
    return $pin;
}

function getCountryList(){
	global $database;
	$queryString = "SELECT c.country_id,ct.country_name,ct.phonecode FROM golf_course g LEFT JOIN city c ON c.city_id = g.city_id left join country ct ON ct.country_id=c.country_id
	WHERE g.city_id !=  '0' GROUP BY c.country_id order by c.country_id asc";
	return $featchUser1 =  $database->FetchQuery($queryString);
}

function createUser($data){
	global $database ;
	$emeilId = (isset($data['email_id']) && $data['email_id'] != '')?$data['email_id']:'';
	$name = (isset($data['name']) && $data['name'] != '')?$data['name']:'';
	$handicap = (isset($data['handicap']) && $data['handicap'] != '')?$data['handicap']:'0';
	$userValue = 0;
	$sqlQuery='insert into '.TABLE_GOLF_USERS.' set user_name="'.$database->escape($emeilId).'",alternate_email_id="'.$database->escape($emeilId).'",is_new="1", full_name="'.$name.'", display_name="'.$name.'",password="", activation_password="",is_active = "0"';	
	$addUser =  $database->FetchQuery($sqlQuery);
	$userId = $database->LastInsertId();
								
				//die;				
	if($userId > 0){
		$sqlQuery1="insert into ".TABLE_USERS." set user_id='".$userId."',self_handicap='".$handicap."'";	
		$addUser =  $database->FetchQuery($sqlQuery1);
		$userValue  = $userId ;
	}
	return $userValue ;
	
}

function isExistAccessToken($access_token,$user_id){
	
	global $database;
	$queryString = "SELECT user_id FROM ".TABLE_GOLF_USERS." where authorization_key = '".$access_token."' AND user_id = ".$user_id." ";
	$userId = $database->FetchSingleValue($queryString);
	if(isset($userId) && $userId >0){
		return true;
	}else{
		return false;
	}
}	
	function updateToken($access_token,$user_id){
	
		global $database;
		$queryString = "UPDATE ".TABLE_GOLF_USERS." set authorization_key = '".$access_token."' AND user_id = ".$user_id." ";
		return $database->FetchQuery($queryString);

	}
	
function newEventPlayer($data){
	
		global $database ;
		$event_id = (isset($data['event_id']) && $data['event_id'] != '')?$data['event_id']:'';
		$user_id = (isset($data['user_id']) && $data['user_id'] != '')?$data['user_id']:'0';
		$creation_date=date("Y-m-d H:i:s");
		$sqlQuery="insert into "._INVITED_EVENT_PLAYER_." set player_id='".$user_id."',event_id='".$event_id."',is_active='1', creation_date='".$creation_date."'";	
		$addPlayerUser =  $database->FetchQuery($sqlQuery);
		return $addPlayerUser ;
		
	}

 function getUserNameById($user_id){
	
		global $database;
		$queryString = "Select full_name FROM golf_users WHERE user_id = ".$user_id." ";
		return $database->FetchSingleValue($queryString);

	}
function getCalculatedPercentage($data,$total,$flag=1){

	$min_val = min($data);
	$max_val = max($data);
	$min_key = array_search(min($data), $data);
	$max_key = array_search(max($data), $data);

	if($total == 101){
		$data[$max_key] = $max_val -1 ;
	}elseif($total == 99){
		$data[$min_key]  = $min_val+1;
	}else{
		$data  = $data ;
	}
return $data;

}
?>