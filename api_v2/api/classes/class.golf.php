<?php
class Golf{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = _GOLF_COURSE_TBL_;
		
	}	
function getGolfcourseNerabyDistance($data){//$lat='';$lon=''; 
//print_r($data);die;

	    $fdata = $error = array();
		$lat = (isset($data['latitude']))?$data['latitude']:'';
		$lon = (isset($data['longitude']))?$data['longitude']:'';
		$ip = (isset($data['ip_address']))?$data['ip_address']:'';
$user_id = (isset($data['user_id']))?$data['user_id']:'0';
$current_date=date("Y-m-d");
		
			if($lat=='') {$lat_arr=GetLatLngFromIP($ip);if(isset($lat_arr[0])){$lat=$lat_arr[0];}}
			if($lon=='') {$lon_arr=GetLatLngFromIP($ip);if(isset($lon_arr[1])){$lon=$lon_arr[1];}}
		
				$origLat   = $lat; 
				$origLon   = $lon;
				$dist      = 15000; 
				
				$NearBy =  array();
				 $Sql = "SELECT gc.golf_course_name,gc.golf_course_id,gc.city_id,gc.latitude,gc.longitude,c.city_name, 3956 * 2 * 
				ASIN(SQRT( POWER(SIN(($origLat - abs(gc.latitude))*pi()/180/2),2)
				+COS($origLat*pi()/180 )*COS(abs(gc.latitude)*pi()/180)
				*POWER(SIN(($origLon-gc.longitude)*pi()/180/2),2))) 
				as distance,count(e.event_id) as event_count  FROM ".$this->table." gc left join "._CITY_TBL_." as c on c.city_id=gc.city_id   left join "._EVENT_TBL_." as e on e.golf_course_id=gc.golf_course_id  and DATE(e.event_start_date_time)>='".$current_date."' and e.is_started not in (2,3,4) and e.is_public='1' WHERE gc.longitude between ($origLon-$dist/abs(cos(radians($origLat))*69)) 
				and ($origLon+$dist/abs(cos(radians($origLat))*69)) and gc.latitude between ($origLat-($dist/69)) and ($origLat+($dist/69)) and gc.is_active=1 and gc.latitude!='' and gc.longitude!='' group by gc.golf_course_id having distance < $dist ORDER BY distance ";
				$result = $this->db->FetchQuery($Sql)	;
			 $fdata = array();

			if(count($result)>0)
			{
			  foreach($result as $p=>$row)
			   {
// $q = 'SELECT p.player_id,e.event_id FROM event_table as e LEFT JOIN event_player_list p ON p.event_id=e.event_id WHERE e.golf_course_id = '.$row['golf_course_id'].' AND DATE(e.event_start_date_time)>="'.date("Y-m-d").'" and e.is_started not in (2,3,4)'; 
// $isEvent= $this->db->FetchRow($q);

		$request_to_participate=0;
		if($user_id > 0){
			/* $q="select event_id,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id  and is_accepted in (0,1)) as request_to_participate_event from event_table e where e.is_public='1' and e.is_started in (0,1) and DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.golf_course_id='".$row['golf_course_id']."'";	 */

$q="select event_id,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id  and is_accepted ='0' and add_player_type='1') as request_to_participate_event,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))) as is_exist_user from event_table e where e.is_public='1' and e.is_started in (0,1) and DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.golf_course_id='".$row['golf_course_id']."'";
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
        $row['has_event']=($request_to_participate > 0)?"1":"0";			  
/*if($row['event_count'] == 1){
					   $row['has_event']=1;
					   }else{
					   $row['has_event']=0;
					}*/
				if(strlen($row['golf_course_name'])>30) {
					$row['golf_course_name'] = (substr($row['golf_course_name'],0,27).'...');
				}
				 $NearBy[] = array(
							   'golf_course_name'=>$row['golf_course_name'],
							   'golf_course_id'=>$row['golf_course_id'],
							   'city_name'=>$row['city_name'],
							   'city_id'=>$row['city_id'],
							   'lat'=>$row['latitude'],
							   'lon'=>$row['longitude'],
							   'Distance'=>$row['distance'],
							   'has_event'=>$row['has_event']
						 );
						 
				 
			   }
			  
				$fdata['status'] = '1';
				$fdata['GolfcourseNerabyDistance'] = $NearBy;
				$fdata['message']="Golf Course List";
			}else{
				$fdata['status'] = '0';
				$fdata['GolfcourseNerabyDistance'] = '';
				$fdata['message']="Golf Course Empty";
			}
		
			return $fdata;
	}
	function getHoleTeeValue($golfcourseid){
		 $queryString = "select tee_value1 from golf_hole_index where golf_course_id = ". $golfcourseid;
		$queryResult = $this->db->FetchRow($queryString)	;
		$teeValue=$queryResult['tee_value1'];
		
		$teeValueArr=array();
		if($teeValue!=""){
			$teeValueArr=(array)json_decode($teeValue);
			$teeValueArrMen=(array)$teeValueArr['Men'];
			arsort($teeValueArrMen);
			$teeValueArrLadies=(array)$teeValueArr['Ladies'];
			arsort($teeValueArrLadies);
			$teeValueArrJunior=(array)$teeValueArr['Junior'];
			arsort($teeValueArrJunior);

			return array('Men'=>$teeValueArrMen,'Ladies'=>$teeValueArrLadies,'Junior'=>$teeValueArrJunior);
		}
	}

		
	function getGolfCourseTee($data){
			$fdata = $error = array();
			$golfcourseid=isset($data['golfcourseid'])?$data['golfcourseid']:"";
			//$option=isset($data['option'])?$data['option']:"";
			$mainTeeArr=array();  $ladiesTeeArr=array(); $menTeeArr=array(); $juniorTeeArr=array();
			if($golfcourseid > 0){
					$queryString = "select *  from "._GOLF_COURSE_TEE_TBL_." where golf_course_id = ". $golfcourseid;
					$queryResult = $this->db->FetchQuery($queryString)	;
					if(count($queryResult) > 0){
						
						$teeValue=$this->getHoleTeeValue($golfcourseid);
						$menArr=isset($teeValue['Men'])?$teeValue['Men']:array();	
						$ladiesArr=isset($teeValue['Ladies'])?$teeValue['Ladies']:array();	
						$juniorArr=isset($teeValue['Junior'])?$teeValue['Junior']:array();	
						if(count($menArr) > 0){
							foreach($menArr as $tee=>$teeValue){
								$name=$tee;//getTeeName($tee);
								$teeArr=$this->getTeeDetail($name);
								$teeId=$teeArr['tee_id'];
								$teecolor=$teeArr['color_code'];
$code=$teeValue;
								$menTeeArr[]=array('tee_id'=>$teeId,'tee_name'=>$name,'tee_color'=>$teecolor,'code'=>$code);								
							}
						}
						if(count($ladiesArr) > 0){
							foreach($ladiesArr as $tee=>$teeValue){
								$name=$tee;//getTeeName($tee);
								$teeArr=$this->getTeeDetail($name);
								$teeId=$teeArr['tee_id'];
								$teecolor=$teeArr['color_code'];
								$code=$teeValue;
$ladiesTeeArr[]=array('tee_id'=>$teeId,'tee_name'=>$name,'tee_color'=>$teecolor,'code'=>$code);								
								
							}
						}
						if(count($juniorArr) > 0){
							foreach($juniorArr as $tee=>$teeValue){
								$name=$tee;//getTeeName($tee);
								$teeArr=$this->getTeeDetail($name);
								$teeId=$teeArr['tee_id'];
								$teecolor=$teeArr['color_code'];
$code=$teeValue;
								$juniorTeeArr[]=array('tee_id'=>$teeId,'tee_name'=>$name,'tee_color'=>$teecolor,'code'=>$code);	
							}
						}
						
						$mainTeeArr=array('Men'=>$menTeeArr,'Ladies'=>$ladiesTeeArr,'Junior'=>$juniorTeeArr);
					
					
					$fdata['status'] = '1';
					$data_post['golf_course_id'] = $golfcourseid;
					//$data_post['option'] = $option;
					//$event = new Events();
					//$fdata['spot_hole_numbers'] = $event->showholenumbers($data_post);
					$fdata['GolfCourseTee'] = $mainTeeArr;
					$fdata['message']="Golf Course Tee List.";	
					
					}else
					{
					$fdata['status'] = '0';
					$fdata['GolfCourseTee'] = '';
					$fdata['message']="Golf Not Exist.";	
					}
			}else{
				$fdata['status'] = '0';
				$fdata['GolfCourseTee'] = '';
				$fdata['message']="Required Field Not Found";	
			}
			return $fdata ;
	}


	
        function getCityGolfCourseList($data)
        {
			$cityId = (isset($data['cityId']) && $data['cityId'] > 0)?$data['cityId']:0;
			$searchkey = (isset($data['search_keyword']) && $data['search_keyword'] != '')?$data['search_keyword']:'';
			$searchkey = ($searchkey=='' && isset($data['searchkey']) && $data['searchkey'] != '')?$data['searchkey']:$searchkey;
			$user_id = (isset($data['user_id']))?$data['user_id']:'0';
			$condition='';
			if($searchkey!=""){
				$condition.="  and (gc.golf_course_name like '%".$searchkey."%' or c.city_name like '%".$searchkey."%' or cnt.country_name like '%".$searchkey."%') ";
			}
			if($cityId>0){
				$condition.="  and gc.city_id = ". $cityId;
			}
			$queryString = "select gc.golf_course_id, gc.golf_course_name,gc.latitude,gc.longitude,c.city_id,c.city_name,count(e.event_id) as event_count  from ".$this->table." as gc left join "._CITY_TBL_." as c on c.city_id=gc.city_id left join "._COUNTRY_TBL_." as cnt on cnt.country_id=c.country_id left join "._EVENT_TBL_." as e on e.golf_course_id=gc.golf_course_id and DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.is_started not in (2,3,4) where gc.is_active =1  ".  $condition . " group by  gc.golf_course_id ";
			
			$cityGolfCourseArray = array();
			$queryResult = $this->db->FetchQuery($queryString)	;
			if(count($queryResult) > 0) 
			{
				foreach($queryResult as $i=>$rowValues)
				{
					
					/*if($rowValues['event_count']>0){
						$rowValues['has_event']=1;
						}else{
						$rowValues['has_event']=0;
							}*/
					if(strlen($rowValues['golf_course_name'])>30) {
						$rowValues['golf_course_name'] = (substr($rowValues['golf_course_name'],0,27).'...');
					}
					$request_to_participate=0;
					$total_event=0;
					if($user_id > 0){
					   $q="select event_id,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id  and is_accepted ='0' and add_player_type='1') as request_to_participate_event,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))) as is_exist_user from event_table e where e.is_public='1' and e.is_started in (0,1) AND DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.golf_course_id='".$rowValues['golf_course_id']."'";
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
                    $rowValues['has_event']=($request_to_participate > 0)?"1":"0";					
					$cityGolfCourseArray[] = $rowValues ;
				}
				$fdata['status'] = '1';
				$fdata['CityGolfCourseList'] = $cityGolfCourseArray;
				$fdata['message']="Golf Course List According to City";	
			} 
			else
			{
				$fdata['status'] = '1';
				$fdata['CityGolfCourseList'] = '';
				$fdata['message']="No Golf Course Found";	
			}
		return $fdata ;
        }
		
		function getTeeDetail($tee){
		$queryString = "select tee_id,color_code from tee  where tee_name = '".$tee."'";
		$queryResult = $this->db->FetchQuery($queryString)	;
		$rowValues='';
		foreach($queryResult as $i=>$c) 
		{
			$rowValues['tee_id']=$c['tee_id'];
			//$rowValues['color_code']=hex2rgb($c['color_code']);
			$rowValues['color_code']=$c['color_code'];
		}
		return $rowValues;
    }

function createTemporaryGolfCourse($data){
	  $fdata =array();
	  $name = isset($data['name'])?$data['name']:'';
	  $city = isset($data['city'])?$data['city']:'';
	  $state = isset($data['state'])?$data['state']:'';
	  $country = isset($data['country'])?$data['country']:'';
	  $event_id = isset($data['event_id'])?$data['event_id']:0;
	  $created_by = isset($data['created_by'])?$data['created_by']:0;
	  $is_active = 2;
	  $is_approved = 0;
	  $create_temporary = isset($data['create_temporary'])?$data['create_temporary']:0;
	  $par_values=isset($data['par_values'])?$data['par_values']:array();
	  $index_values=isset($data['index_values'])?$data['index_values']:array();
	  $countryId = $stateId = $cityId = 0;
	  $no_of_holes = 18;
	  $ip = $_SERVER['REMOTE_ADDR'];
	  $color_code = 1;
	  $creation_date=date("Y-m-d H:i:s");
	  $address = $city.','.$state.','.$country ;  
	   
	   if($create_temporary == 1) {
		   $fdata['status_type'] = '1';
	   }
	   else {
		   $fdata['status_type'] = '2';
	   }
	   
	  if($created_by >0){
	   
	   $add = getLatLongFromAddress($address);
	   if(isset($add) && $add === false && is_array($add) && count($add) == true){
		$lat = $add[0]; 
		$long = $add[1];  
	   }else{
		$lat = 0; 
		$long = 0; 
	   }
	   $countrydata = 'select country_id from '._COUNTRY_TBL_.' where country_name ="'.$this->db->escape($country).'"';
	   $countryId = $this->db->FetchSingleValue($countrydata) ;
	   
	  
	   if(!isset($countryId)  || $countryId <= 0){
	
		 $sqlQuery1='insert into '._COUNTRY_TBL_.' set country_name="'.$this->db->escape($country).'",is_active=0';
		$this->db->FetchQuery($sqlQuery1); 
		$countryId = $this->db->LastInsertId();
		
		$sqlQuery2='insert into '._STATE_TBL_.' set country_id="'.$countryId.'" , state_name ="'.$this->db->escape($state).'"';
		$this->db->FetchQuery($sqlQuery2); 
		$stateId = $this->db->LastInsertId();
		
		$sqlQuery3='insert into '._CITY_TBL_.' set country_id="'.$countryId.'" , state_id = "'.$stateId.'", city_name="'.$this->db->escape($city).'"';
		$this->db->FetchQuery($sqlQuery3); 
		$cityId = $this->db->LastInsertId();
		
	   }
	   elseif(isset($countryId)  && $countryId > 0){
		$statedata = 'select state_id from '._STATE_TBL_.' where state_name ="'.$this->db->escape($state).'"';
		$stateId = $this->db->FetchSingleValue($statedata) ;
		
		if(!isset($stateId)  || $stateId <= 0){
		 
		 $sqlQuery2='insert into '._STATE_TBL_.' set country_id="'.$countryId.'" , state_name ="'.$this->db->escape($state).'"';
		 $this->db->FetchQuery($sqlQuery2); 
		 $stateId = $this->db->LastInsertId();
		 
		 $sqlQuery3='insert into '._CITY_TBL_.' set country_id="'.$countryId.'" , state_id = "'.$stateId.'", city_name="'.$this->db->escape($city).'"';
		 $this->db->FetchQuery($sqlQuery3); 
		 $cityId = $this->db->LastInsertId();
		}
		elseif(isset($stateId)  && $stateId > 0){
		 $citydata = 'select city_id from '._CITY_TBL_.' where city_name ="'.$this->db->escape($city).'"';
		 $cityId = $this->db->FetchSingleValue($citydata) ;
		 
		 if(!isset($cityId)  || $cityId <= 0){
		  
		  $sqlQuery3='insert into '._CITY_TBL_.' set country_id="'.$countryId.'" , state_id = "'.$stateId.'", city_name="'.$this->db->escape($city).'"';
		  $this->db->FetchQuery($sqlQuery3); 
		  $cityId = $this->db->LastInsertId();
		 }
		}
	   }
	   $conditions=array();
	   if(count($par_values) >0){
		for($i =1; $i <= 18; $i++){
		 $par_id = 'par_'.$i;
		 $par_value = isset($par_values[$par_id]) ? $par_values[$par_id] : 0;
		 $conditions[] = 'par_value_'.$i.' = "'.$par_value.'"'; 
		}
	   }
	   
	   if(count($par_values) >0){
		for($i =1; $i <= 18; $i++){
		 $par_id = 'index_'.$i;
		 $par_value = isset($index_values[$par_id]) ? $index_values[$par_id] : 0;
		 $conditions[] = 'hole_index_'.$i.' = "'.$par_value.'"'; 
		}
	   }
	   
	   for($i =1; $i <= 18; $i++){
		$par_id = 'tee_value'.$i; $vv = '{"Men":{"Black":"600"},"Ladies":{"Black":"600"},"Junior":{"Black":"600"}}';
		$conditions[] = "tee_value{$i} = '{$vv}'"; 
	   }
	   $cond = '';
	   if(count($conditions) >0){
		$cond = ','.implode(',', $conditions);
	   }
	   $data = 'insert into '._GOLF_COURSE_TBL_.' set golf_course_name="'.$this->db->escape($name).'", city_id="'.$cityId.'", created_by="'.$created_by.'", number_of_holes="'.$no_of_holes.'", is_active="'.$is_active.'", is_approved="'.$is_approved.'", event_id="'.$event_id.'", creation_date="'.$creation_date.'", ip_address= "'.$ip.'", latitude= "'.$lat.'", longitude="'.$long.'" ';
	   $golfdata = $this->db->FetchQuery($data); 
	   $golfId = $this->db->LastInsertId();
	   
	   $teedata = "insert into "._GOLF_COURSE_TEE_TBL_." set golf_course_id='".$golfId."', men='".$color_code."', ladies='".$color_code."', junior='".$color_code."', add_date='".$creation_date."'";
	   $teedat = $this->db->FetchQuery($teedata); 
	   $golfTeeId = $this->db->LastInsertId();
	   
	   $golfindex = 'insert into '._GOLF_HOLE_INDEX_.' set golf_course_id="'.$golfId.'", num_hole="'.$no_of_holes.'" '.$cond.'';
	   $this->db->FetchQuery($golfindex); 
	    $fdata['status'] = '1';
	    $fdata['golfcourseid'] = $golfId;
		 $fdata['message'] = 'Golf Couse Created';
	  }else{
		  $fdata['status'] = '0';
 $fdata['golfcourseid'] = '0';
		  $fdata['message'] = 'Required Field Not Found';
	  }
	return $fdata;
	 }


function getRecentGolfCourseList($data)
        {
          $fdata = array();
		  $player_id = $data['player_id'];
		  $limit = $data['limit'];
			if($player_id > 0){
				$limit_str = ($limit > 0) ? " limit {$limit} " : '';;
					$condition=''; 
					 $eve_que="select event_id from event_player_list where player_id='".$player_id."' and is_accepted='1' order by event_id desc {$limit_str}"; 
					$eve_que  =  $this->db->FetchQuery($eve_que); 
					if(count($eve_que)>0){
					foreach($eve_que as $i=>$eve_id_res){
						$eve_id[]=$eve_id_res['event_id'];
						}
					   $event_id_imp=implode(',',$eve_id);
					
					$golf_c_que="select  golf_course_id from event_table where event_id in ($event_id_imp) order by event_id desc {$limit_str}";  
					$golf_c_que  =  $this->db->FetchQuery($golf_c_que); 

					foreach($golf_c_que as $g=>$golf_course_id_res){
						$glf_id[]=$golf_course_id_res['golf_course_id'];
						}
						$glf_id = array_unique($glf_id);
						$golf_c_id_imp=implode(',',$glf_id);
						
						
					$queryString = "select g.golf_course_id, g.golf_course_name,g.latitude,g.longitude,c.city_name,c.city_id, count(e.event_id) as event_count from golf_course as   g   left join city c on  g.city_id=c.city_id   left join event_table as e on e.golf_course_id=g.golf_course_id  and DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.is_started not in (2,3,4) where g.golf_course_id in ($golf_c_id_imp) group by g.golf_course_id " ;
					$queryString .= $limit_str;
					/*echo $queryString;
					die;*/
					$recentGolfCourseArray = array();
					$queryResult  =  $this->db->FetchQuery($queryString); 
					if(count($queryResult)>0) 
					{ $recentGolfCourseArrayTemp = array();
						foreach($queryResult as $i=>$rowValues)
						{
							
							
							$request_to_participate=0;
							$total_event=0;
							if($player_id > 0){
								$q="select event_id,(select count(*) from event_player_list where player_id ='".$player_id."' and event_id=e.event_id  and is_accepted in (0,1)) as request_to_participate_event from event_table e where e.is_public='1' and e.is_started in (0,1) and DATE(e.event_start_date_time)>='".date("Y-m-d")."' and e.golf_course_id='".$rowValues['golf_course_id']."'";	
								$resdata = $this->db->FetchQuery($q);
								if(count($resdata)>0){
								  foreach($resdata as $gp=>$grow){
									$total_event++;
									if($grow['request_to_participate_event']==0){
										$request_to_participate++;
									}
								  }
								}
							}
							$rowValues['has_event']=($request_to_participate > 0)?"1":"0";
							if(strlen($rowValues['golf_course_name'])>30) {
								$rowValues['golf_course_name'] = (substr($rowValues['golf_course_name'],0,27).'...');
							}
							$recentGolfCourseArrayTemp[$rowValues['golf_course_id']] = $rowValues ;
						}
						$recentGolfCourseArray = array();
						
						foreach($glf_id as $a) {
							$recentGolfCourseArray[] = $recentGolfCourseArrayTemp[$a]; 
						}
						$fdata['status']  = '1';
						$fdata['data']  = $recentGolfCourseArray;
						$fdata['message']  = 'recentGolfCourseList';
						
					} 
					else
					{
						
						$fdata['status']  = '0';
						$fdata['message']  = 'no recent golf course available';
					}
				}else{
						$fdata['status']  = '0';
						$fdata['message']  = 'no recent golf course available';
						
					}
			}else{
				$fdata['status']  = '0';
				$fdata['message']  = 'Required field not found';
				
			}
			return $fdata;
        }
 function getCountryGolfCourseList($data)
        {
			$country_id = (isset($data['country_id']) && $data['country_id'] > 0)?$data['country_id']:0;
			$user_id = (isset($data['user_id']))?$data['user_id']:'0';
			$condition='';
			if($searchkey!=""){
				$condition.="  and (gc.golf_course_name like '%".$searchkey."%' or c.city_name like '%".$searchkey."%' or cnt.country_name like '%".$searchkey."%') ";
			}
			if(isset($country_id) && $country_id >0){
				
			$queryString = "SELECT g.golf_course_name,g.golf_course_id,co.country_id FROM `city` c LEFT JOIN golf_course g ON g.city_id=c.city_id LEFT JOIN country co ON co.country_id = c.country_id where c.country_id = '".$country_id."' and golf_course_name !='' and g.is_active=1 and is_approved=1 group by golf_course_id order by golf_course_name ASC";
			
			$cityGolfCourseArray = array();
			$queryResult = $this->db->FetchQuery($queryString)	;
			if(count($queryResult) > 0) 
			{
				foreach($queryResult as $i=>$rowValues)
				{
				
					if(strlen($rowValues['golf_course_name'])>30) {
						$rowValues['golf_course_name'] = (substr($rowValues['golf_course_name'],0,27).'...');
					}
							
					$cityGolfCourseArray[] = $rowValues ;
				}
				$fdata['status'] = '1';
				$fdata['data'] = $cityGolfCourseArray;
				$fdata['message']="Golf Course List According to Country";	
			} 
			else
			{
				$fdata['status'] = '0';
				$fdata['message']="No Golf Course Found";	
			}
			}else
			{
				$fdata['status'] = '0';
				$fdata['message']="Required Field Not Found";	
			}
		return $fdata ;
        }
}
?>