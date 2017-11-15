<?php
//phpinfo();
$to="nawedita@soms.in";
$toName="nawedita";
$subject="test";
$_message="welcome";

/*include("Mail.php");
$mail = new Mail($to, $toName, $subject, $_message, $cc_to = '', $cc_name = '', $isSmtp = false);
	
	if(!$mail->Send()){
		return false;
	} else {
		return true;
	}*/
	$headers = "From: monika@soms.in>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$headers .= "X-Mailer: PHP \r\n";
			$mail = mail($to, $subject, $_message,$headers);
/*$to = "nawedita@soms.in";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: info@myrwa.in";

mail($to,$subject,$txt,$headers);
*/?>