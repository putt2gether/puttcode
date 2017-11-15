<?php

include(dirname(__FILE__).'/class.phpmailer.php');
include(dirname(__FILE__).'/class.smtp.php');

class Mail
{
	public $_isSmtp = true;
	public $_host = "localhost";//"103.13.97.182";
	public $_port = "465";
	public $_username = "info.myrwa.in";
	public $_password = "info@1234";
	public $_to = "";
	public $_toName = "";
	public $_from = "info.myrwa.in";
	public $_fromName = "Admin";
	public $_cc_email = '';
	public $_cc_name = '';
	public $_message = '';
	public $_subject = '';
	public $error = '';
	public $debug = 0;
	
	public function Mail($to, $toName, $subject, $message, $cc_to = '', $cc_name = '', $isSmtp = true){
		$this->_to = $to;
		$this->_toName = $toName;
		$this->_subject = $subject;
		$this->_message = ($message);
		$this->_cc_email = $cc_to;
		$this->_cc_name = $cc_name;
		$this->_isSmtp = $isSmtp;
	}
	
	public function Send(){
		if($this->_isSmtp){
			if(empty($this->error) && $this->error==''){
				$mail = new PHPMailer();
				$mail->IsSMTP();							// telling the class to use SMTP
				$mail->SMTPDebug	= $this->debug;			// enables SMTP debug information (for testing)
															// 0 = disable
															// 1 = errors and messages
															// 2 = messages only
				$mail->SMTPAuth 	= true; 				// enable SMTP authentication
				$mail->SMTPSecure	= "ssl";				// sets the prefix to the servier
				$mail->Host			= $this->_host;			// sets GMAIL as the SMTP server
				$mail->Port			= $this->_port;			// set the SMTP port for the GMAIL server
				$mail->Username		= $this->_username;		// GMAIL username
				$mail->Password		= $this->_password;		// GMAIL password
				
				$mail->SetFrom($this->_from, $this->_fromName);
				//$mail->AddReplyTo($this->_to, $this->_toName);
				$mail->Subject    = $this->_subject;
				$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
				$mail->MsgHTML($this->_message);
				$mail->AddAddress($this->_to, $this->_toName);
				if(!empty($this->_cc_email) && !empty($this->_cc_name))
					$mail->AddCC($this->_cc_email, $this->_cc_name);
				
				//$mail->AddAttachment("Path to uploaded files");
				
				if($mail->Send()) {
					$this->_log("To: ".$this->_to.", Subject: ".$this->_subject);
					return true;
				} else {
					$this->error .= "Mailer Error: " . $mail->ErrorInfo;
					return false;
				}
			}
		} else {
			$headers = "From: "._MAIL_FROM_NAME_."<" . strip_tags(_MAIL_FROM_) . ">\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$headers .= "X-Mailer: PHP \r\n";
			$mail = mail($this->_to, $this->_subject, $this->_message,$headers);
		}
	}
	
	private function _log($_log=''){
		$myFile = dirname(__FILE__)."/../../log/mailer_".date('Y_m_d').".txt";
		$fContent = fopen($myFile, 'a+');
		$fDataString = "\n".$_log." @".date('d-m-Y H:i:s');
		fwrite($fContent, $fDataString);
		fclose($fContent);
	}
	
}

?>