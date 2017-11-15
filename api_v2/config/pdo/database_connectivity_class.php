<?php
require_once('constant_class.php');
require_once('execption_log_class.php');
require_once('debug_log_class.php');

/* select column_name from information_schema.columns where table_schema='yourdatabasename'   and table_name='yourtablename';	*/
class DatabaseConnectionClass
{
	private $databse_object_instance;
	private $sqlQueryString;
	private $settings;
	private $isConnected = false;
	private $exception_log_object;
	private $parameters;
	public function __construct()
	{ 			
		$this->exception_log_object = new ExceptionLog();
		/*if(DEBUG_FLAG_ON)
		{
			$this->debug_log_object = new DebugLog();		
		}*/
		$this->ConnectToDatabase();
		$this->parameters = array();
	}
		private function ConnectToDatabase()
		{
			$database_server_object = 'mysql:host='._DB_SERVER_.';dbname='._DB_NAME_.';port=3306';
$ps = _DB_PASSWD_;
$dbu = _DB_USER_;
//echo $database_server_object . " ".$ps. " ".$dbu;
			try 
			{
				$this->databse_object_instance = new PDO($database_server_object, $dbu, $ps, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

//$this->databse_object_instance = new PDO("mysql:host=localhost;dbname=insta_partyz_db;", "insta", "Ff@45722");


				$this->databse_object_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->databse_object_instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$this->isConnected = true;
			}
			catch (PDOException $e) 
			{
				echo $this->ExceptionLog($e->getMessage());
				die();
			}
		}

	 	public function CloseConnection()
	 	{
	 		$this->databse_object_instance = null;
	 	}
	
		private function Initialize($query,$parameters = "", $parameters_type = "")
		{
			if(!$this->isConnected) 
			{ 
				$this->ConnectToDatabase(); 
			}
			try 
			{//echo $query.'<br/>';
				$this->sqlQueryString = $this->databse_object_instance->prepare($query);
				
				# Add parameters to the parameter array	
				$this->bindMoreParamters($parameters,$parameters_type);

				if(!empty($this->parameters)) 
				{
					foreach($this->parameters as $param)
					{
						$parameters = explode("\x7F",$param);
						$this->sqlQueryString->bindParam($parameters[0],$parameters[1],$parameters[2]);
					}		
				}
				$this->succes = $this->sqlQueryString->execute();		
			}
			catch(PDOException $e)
			{
					# Write into log and display Exception
					echo $this->ExceptionLog($e->getMessage(), $query );
					die();
			}

			# Reset the parameters
			$this->parameters = array();
		}

		public function bindParamter($para, $value,$para_type)
		{	
			$this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . utf8_encode($value) . "\x7F" . $para_type;
		}
      
		public function bindMoreParamters($parray,$ptypearray)
		{
			if(empty($this->parameters) && is_array($parray)) {
				$columns = array_keys($parray);
				foreach($columns as $i => &$column)	{
					$this->bindParamter($column, $parray[$column],$ptypearray[$column]);
				}
			}
		}
  		
		public function FetchQuery($sql_query,$raw_paramters = null, $parameters_type =null, $fetchmode = PDO::FETCH_ASSOC)
		{
			$sql_query = trim($sql_query);

			$this->Initialize($sql_query,$raw_paramters,$parameters_type);

			$rawStatement = explode(" ", $sql_query);
			
			$statement = strtolower($rawStatement[0]);
			
				/*if(DEBUG_FLAG_ON)
				{
					if(isset($raw_paramters))
					{
						$inputParamter = implode(",",$raw_paramters);
					}
					else
					{
						$inputParamter ='';
					}
					$this->writeDebugLog($inputParamter,'Conceftvity Calls','107', $sql_query	 );
					
				}*/

			
			if ($statement === 'select' || $statement === 'show')
			{
				return $this->sqlQueryString->fetchAll($fetchmode);
			}
			elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) 
			{
				return $this->sqlQueryString->rowCount();	
			}	
			else 
			{
				return NULL;
			}
		}
		
     
		public function LastInsertId() 
		{
			return $this->databse_object_instance->lastInsertId();
		}	
	
		public function FetchAllColumn($sql_query,$raw_paramters = null,$parameters_type = null)
		{
			$this->Initialize($sql_query,$raw_paramters,$parameters_type);
			$Columns = $this->sqlQueryString->fetchAll(PDO::FETCH_NUM);		
			
			$column = null;

			foreach($Columns as $ColumnValue) 
			{
				$column[] = $ColumnValue[0];
			}

			return $column;
			
		}	
     
		public function FetchRow($sql_query,$raw_paramters = null, $parameters_type = null, $fetchmode = PDO::FETCH_ASSOC)
		{				
			$this->Initialize($sql_query,$raw_paramters,$parameters_type);
			return $this->sqlQueryString->fetch($fetchmode);			
		}
		
		public function FetchSingleValue($sqlQuery,$paramters = null, $parameters_type = null)
		{
			$this->Initialize($sqlQuery,$paramters,$parameters_type);
			return $this->sqlQueryString->fetchColumn();
		}
	   
		public function FetchProcedure($procedure_call_string,$raw_paramters = null, $parameters_type =null, $fetchmode = PDO::FETCH_ASSOC)
		{
			$results = array();
			if(!$this->isConnected) 
			{ 
				$this->ConnectToDatabase(); 
			}
			try 
			{
				$sql_query = trim($procedure_call_string);
				$this->sqlQueryString = $this->databse_object_instance->prepare($sql_query);//print_r($this->sqlQueryString);
				$this->bindMoreParamters($raw_paramters,$parameters_type);

				if(!empty($this->parameters)) 
				{
					foreach($this->parameters as $param)
					{
						$parameters = explode("\x7F",$param);
						$this->sqlQueryString->bindParam($parameters[0],$parameters[1]);//,$parameters[2]);
					}		
				}
//print_r( $sql_query);
				$this->succes = $this->sqlQueryString->execute();	
				$results= $this->sqlQueryString->fetchAll($fetchmode);
				$this->sqlQueryString->closeCursor();	
				unset($this->sqlQueryString);	
				$this->CloseConnection(); 		
				$this->isConnected = false;
				return($results);
			}
			catch(PDOException $e)
			{
					# Write into log and display Exception
					echo $this->ExceptionLog($e->getMessage(), $sql_query );
					die();
			}

			# Reset the parameters
			$this->parameters = array();
			
			
			
			
		}
		private function ExceptionLog($errorMessage , $sqlQuery = "")
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

		private function writeDebugLog($data_value, $file_name='',$line_number ='', $sql_query = '')
		{
			$debugValue ='';
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
			
			return $debugValue;
		}
		public function escape($value){ if($value=='') {return '';}
			$return = $this->databse_object_instance->quote($value);
			if(substr($return,0,1) == "'") {
				$return = substr($return,1);
			}
			
			if(substr($return,-1,1) == "'") {
				$return = substr($return,0,(strlen($return)-1));
			}
			
			return $return;
			
//return str_ireplace("'","",$this->databse_object_instance->quote($value));
//return $this->databse_object_instance->quote($value);
		}
}
?>
