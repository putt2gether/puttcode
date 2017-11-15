<?php
class DebugLog 
{
	private $path = 'debuglogs/';
	public function __construct() 
	{
		date_default_timezone_set("Asia/Calcutta");	
//		$this->path  =  $pathString.$this->path;	
	}
	public function write($data_value) 
	{
		$current_date_time = new DateTime();
		$log_file_name = $this->path . $current_date_time->format('d-m-Y').'.txt';
		if(is_dir($this->path))
		{
			if(!file_exists($log_file_name))
			{
				$file_handler_object  = fopen($log_file_name, 'a+') or die('you don\'t have permission to create or open file in the folder !');
				$log_file_namecontent = 'Time : ' . $current_date_time->format('H:i:s').' ==> ' . $data_value ."\r\n";
				fwrite($file_handler_object, $log_file_namecontent);
				fclose($file_handler_object);
			}
			else 
			{
				$this->edit($log_file_name,$current_date_time, $data_value);
			}
		}
		else 
		{
			if(mkdir($this->path,0777) === true) 
			{
				$this->write($data_value);  
			}	
		}
	}
	private function edit($log_file_name,$current_date_time,$data_value) 
	{
		$log_file_namecontent = 'Time : ' . $current_date_time->format('H:i:s').' ==> ' . $data_value ."\r\n";
		$log_file_namecontent = $log_file_namecontent . file_get_contents($log_file_name);
		file_put_contents($log_file_name, $log_file_namecontent);
	}
	
	private function writeDebugLog($data_value, $file_name='',$line_number ='', $sql_query = '')
	{
		
		if(!empty($file_name))
		{
			$debugValue  = "Debug Start. in file ".$file_name." at line number ".$line_number."\r\n";
		}
		if(!empty($sql_query)) 
		{
			$debugValue .= "\r\nSql Statement : "  . $sql_query."\r\n";
		}
		if(!empty($data_value)) 
		{
			$debugValue .= "The values are \r\n";
			$debugValue .= $data_value."\r\n";
		}
		$this->debug_log_object->write($debugValue);
	}
}
?>