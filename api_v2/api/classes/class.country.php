<?php
class Country{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = _COUNTRY_TBL_;
		
	}
	
	function getCountryList($data){
		$cdata = array();
		$user_id = (isset($data['user_id']))?$data['user_id']:'0';
		$show_all = (isset($data['show_all']) && $data['show_all']=='1')?'1':'0';
		
		if($show_all == 1) {
			$queryString = "SELECT country_id,country_name,phonecode FROM ".$this->table." where is_active=1 order by country_id asc";
		}
		else {
			$queryString = "SELECT c.country_id,ct.country_name,ct.phonecode FROM golf_course g LEFT JOIN city c ON c.city_id = g.city_id left join country ct ON ct.country_id=c.country_id WHERE g.city_id !=  '0' and c.country_id > 0 and ct.country_name!='' and ct.is_active=1 GROUP BY c.country_id order by ct.country_name asc";
		}
		
//$queryString = "SELECT country_id,country_name,phonecode FROM ".$this->table." where is_active=1 order by country_id asc";
           $queryResult  = $this->db->FetchQuery($queryString);

			if(count($queryResult) > 0){
				$countryArray = array();
				foreach($queryResult as $v=>$c){
					$request_to_participate=0;
					$total_event=0;
					if($user_id > 0){
					$q="SELECT g.city_id,c.country_id, e.event_id, (SELECT count( * ) FROM event_player_list WHERE player_id ='".$user_id."' AND event_id = e.event_id and is_accepted ='0' and add_player_type='1' ) AS request_to_participate_event,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))) as is_exist_user FROM event_table e LEFT JOIN golf_course g ON g.golf_course_id = e.golf_course_id LEFT JOIN city c ON c.city_id = g.city_id WHERE e.is_public = '1' AND e.is_started IN ( 0, 1 ) AND DATE(e.event_start_date_time)>='".date("Y-m-d")."' AND c.country_id ='".$c['country_id']."' ";
					$resdata = $this->db->FetchQuery($q);
					if(count($resdata)>0){
					  foreach($resdata as $gp=>$grow){
						$total_event++;
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
					$countryArray[] = $c;
				}
				$cdata['CountryList'] = $countryArray;			
				
			}else{
				
				$cdata['Error'] = array('status'=>21);
			}
            return $cdata;
	}
	
}
?>