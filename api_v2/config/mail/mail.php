<?php 
require_once(dirname(__FILE__).'/class.phpmailer.php');
//require_once('mail/class.smtp.php');

class Mail
{
	public $_isSmtp = true;
	public $_host = "smtp.gmail.com";//"103.13.97.182";
	public $_port = "587";
	public $_username = "info@putt2gether.com";
	public $_password = "qMF<qN4E";
	public $_to = "deepika@soms.in";
	public $_toName = "deepika";
	public $_from = "info@putt2gether.com";
	public $_fromName = "Admin";
	public $_cc_email = '';
	public $_cc_name = '';
	public $_message = '';
	public $_subject = '';
	public $_filepath = '';
	public $error = '';
	public $debug = 0;
	
	public function Mail($to, $toName, $subject, $message,$isSmtp = true,$filepath='',$from='',$from_name='',$cc_name=''){
		if(strpos($to,",")!==false) {
			$exp=explode(",",$to);
			$this->_to=$exp[0];
			unset($exp[0]);
			
		}
		else {
			$this->_to = $to;
		}
		$this->_toName = $toName;
		$this->_from = $from;
		$this->_fromName = $from_name;
		$this->_subject = $subject;
		$this->_message = ($message);
		$this->_cc_email = (isset($exp) && is_array($exp) && count($exp)>0) ? $exp : '';
		$this->_cc_name = $cc_name;
		$this->_isSmtp = $isSmtp;
		$this->_filepath = $filepath;
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
				$mail->SMTPSecure	= "tls";				// sets the prefix to the servier
				$mail->Host			= $this->_host;			// sets GMAIL as the SMTP server
				$mail->Port			= $this->_port;			// set the SMTP port for the GMAIL server
				$mail->Username		= $this->_username;		// GMAIL username
				$mail->Password		= $this->_password;		// GMAIL password
				
				$mail->SetFrom($this->_from, $this->_fromName);
				//$mail->AddReplyTo($this->_to, $this->_toName);
				$mail->Subject    = $this->_subject;
				$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
				$mail->MsgHTML($this->_message);
				
				//echo $this->_message;
				//die;
				$mail->AddAddress($this->_to, $this->_toName);
				/*if(!empty($this->_cc_email) && !empty($this->_cc_name))
					$mail->AddCC($this->_cc_email, $this->_cc_name);*/
					
				if(!empty($this->_cc_email)) {
					if(is_array($this->_cc_email) && count($this->_cc_email)>0) {
						foreach($this->_cc_email as $a=>$b) {
							$b=trim($b);	
							$mail->AddCC($b, $b);
						}
					}
					else {
						$mail->AddCC($this->_cc_email, $this->_cc_email);
					}
				}
				$mail->AddAttachment($this->_filepath);
				$mail->Mailer='smtp'; //echo'<pre>';print_r($mail);
				if($mail->Send()) {
					//echo "Mail Send";
					return true;
				} else {
					//echo $this->error .= "Mailer Error: " . $mail->ErrorInfo;
					return false;
				}
			}
		} 
	}
	
	
}

?>