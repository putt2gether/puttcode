<?php
class City{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
	
	}
	
	function getCityList($data){
		    $cdata =$stateArray=array();		 
			$stateId = (isset($data['state_id']) && $data['state_id'] >0)?$data['state_id']:0;
			$user_id = (isset($data['user_id']))?$data['user_id']:'0';
			if($stateId > 0){
				$queryString ="select g.city_id, c.city_name from golf_course g left join city c on c.city_id = g.city_id WHERE g.city_id != '0'";
				$queryString .= " and c.state_id = ". $stateId;
				$queryString .= " GROUP BY g.city_id order by c.city_name asc";
				$queryResult  = $this->db->FetchQuery($queryString);
			
				if(count($queryResult) > 0){
					$stateArray = array();
					foreach($queryResult as $v=>$c){
						$request_to_participate=0;
						$total_event=0;
						if($user_id > 0){
						$q="SELECT g.city_id,c.state_id, e.event_id, (SELECT count( * ) FROM event_player_list WHERE player_id ='".$user_id."' AND event_id = e.event_id AND is_accepted ='0' and add_player_type='1') AS request_to_participate_event,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))) as is_exist_user FROM event_table e LEFT JOIN golf_course g ON g.golf_course_id = e.golf_course_id LEFT JOIN city c ON c.city_id = g.city_id WHERE e.is_public = '1' AND e.is_started IN ( 0, 1 ) AND DATE(e.event_start_date_time)>='".date("Y-m-d")."' AND c.city_id ='".$c['city_id']."'";	
						$resdata = $this->db->FetchQuery($q);
						if(count($resdata)>0){
						  foreach($resdata as $gp=>$grow){
							if($grow['request_to_participate_event']>0){
					$request_to_participate++;
				}
				if($grow['is_exist_user']<=0){
					$request_to_participate++;
				}
						  }
						}
						}
						$c['has_event']=($request_to_participate > 0)?"1":"0";
						$stateArray[] = $c;
					}
					$cdata['status'] = '1';
					$cdata['StateList'] = $stateArray;	
					$cdata['msg'] = 'State List';
					
				}
				else{
					$cdata['status'] = '1';
					$cdata['StateList'] = '';	
					$cdata['msg'] = 'City List Empty';
				}
			}else{
					$cdata['status'] = '0';
					$cdata['StateList'] = '';	
					$cdata['msg'] = 'Please Select State First';
				}
            return $cdata ;
	
	}
	
	
	
}
?>