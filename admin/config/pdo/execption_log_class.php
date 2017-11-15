<?php
class ExceptionLog 
{

	private $path = 'errorlogs/';
	public function __construct() 
	{
		date_default_timezone_set("Asia/Calcutta");	
	}
	public function write($error_message) 
	{
		$current_date_time = new DateTime();
		$log_file_name = $this->path . $current_date_time->format('d-m-Y').'.txt';
		if(is_dir($this->path))
		{
			if(!file_exists($log_file_name))
			{
				$file_handler_object  = fopen($log_file_name, 'a+') or die('you don\'t have permission to create or open file in the folder !');
				$log_file_namecontent = 'Time : ' . $current_date_time->format('H:i:s').' ==> ' . $error_message ."\r\n";
				fwrite($file_handler_object, $log_file_namecontent);
				fclose($file_handler_object);
			}
			else 
			{
				$this->edit($log_file_name,$current_date_time, $error_message);
			}
		}
		else 
		{
			if(mkdir($this->path,0777) === true) 
			{
				$this->write($error_message);  
			}	
		}
	}
	private function edit($log_file_name,$current_date_time,$error_message) 
	{
		$log_file_namecontent = 'Time : ' . $current_date_time->format('H:i:s').' ==> ' . $error_message ."\r\n";
		$log_file_namecontent = $log_file_namecontent . file_get_contents($log_file_name);
		file_put_contents($log_file_name, $log_file_namecontent);
	}
	public function ExceptionLog($errorMessage , $sqlQuery = "")
	{
		$exception  = 'Unhandled Exception. <br />';
		$exception .= $errorMessage;
			if(!empty($sqlQuery)) 
		{
			$errorMessage .= " ===> The SQL Statement is : "  . $sqlQuery;
		}
		$this->exception_log_object->write($errorMessage);
		return $exception;
	}
}
?>