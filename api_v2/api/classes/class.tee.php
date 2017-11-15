<?php
class Tee{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = _TEE_TBL_;
		
	}
	
	function getTeeList()
    {
		$queryString = "select tee_id, tee_name, color_code from "._TEE_TBL_." where is_active != 1 order by tee_name asc";
        $TeeListArray =$fdata = array();
        $queryResult = $this->db->FetchQuery($queryString);
		
		if(count($queryResult) > 0){
               foreach($queryResult as $i=>$rowValues )
                {
		            $TeeListArray[] = $rowValues ;
	            }
				
			$fdata['status'] = '1';
			$fdata['TeeList'] = $TeeListArray;
			$fdata['message']="Tee Listing";
        } 
        else{
			$fdata['status'] = '1';
			$fdata['TeeList'] = '';
			$fdata['message']="Tee List Not Found";
            }
			return $fdata;
    }

		
		function getTeeColorCode($filetr)
        {
			$data = $filetr;
			$fdata = array();
            $tee_id=(isset($data['tee_id']) && $data['tee_id']!="")?$data['tee_id']:"0";
			 $tee_name=(isset($data['tee_name']) && $data['tee_name']!="")?$data['tee_name']:"";
            if(($tee_id > 0) || ($tee_name != '' )){
			$queryString = "select color_code from tee where tee_id ='".$tee_id."' OR tee_name ='".$tee_name."' limit 1";
			
            $TeeListArray = array();
            $queryResult = $this->db->FetchSingleValue($queryString);
			
            if(count($queryResult) > 0) 
            {
				$rowValues = $queryResult;
				$fdata['status'] = '1';
				$fdata['Color'] = $rowValues;
				$fdata['message']="Color Code";
            } 
            else
            {
				$fdata['status'] = '0';
				$fdata['Color'] = '';
				$fdata['message']="No Color Code Found";
            }
		}else{
				$fdata['status'] = '0';
				$fdata['Color'] = '';
				$fdata['message']="Required Field Are Empty";
		}
		
			return $fdata ;
    }
	
}
?>
