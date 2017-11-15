<?php
class Score{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		
	}
	
	function getParIndexvalue($data){
		$fdata =$total =array();
		$golf_course_id = (isset($data['golf_course_id']) && $data['golf_course_id']!='')?$data['golf_course_id']:'0';
		$hole_number = (isset($data['hole_number']) && $data['hole_number']!='')?$data['hole_number']:'0';
$event_id= (isset($data['event_id']) && $data['event_id']!='')?$data['event_id']:'0';
$my_player_id= (isset($data['user_id']) && $data['user_id']!='')?$data['user_id']:'0';
$order_player_ids= (isset($data['player_ids']) && is_array($data['player_ids']) && count($data['player_ids'])>0)?$data['player_ids']:array();
		if($golf_course_id >0 && $hole_number > 0){
		 
		$sqlQuery='SELECT hole_index_'.$hole_number.' as hole_index,par_value_'.$hole_number.' as par_value FROM golf_hole_index WHERE golf_course_id="'.$golf_course_id.'"';  
		$golfdatta  = $this->db->FetchRow($sqlQuery);

$is_spot_type = 0;
					$sqlQueryh="SELECT type FROM event_is_spot_tbl WHERE event_id='".$event_id."' and hole_number =".$hole_number."";  
					$spotdata=$this->db->FetchSingleValue($sqlQueryh);
$golfdatta['is_spot_type']  = ($spotdata)?$spotdata:0;
$teamdata=$this->checkTeamData($event_id);

$golfdatta['is_team']=0;$first_team_id=0;
$golfdatta['is_delegated']='0';

if($my_player_id > 0){
	$sqlQueryh="SELECT count(event_list_id) as c FROM event_player_list WHERE event_id='".$event_id."' and player_id != '".$my_player_id."' and scorere_id='".$my_player_id."' and delegate_status=0";  
	$del_data=$this->db->FetchSingleValue($sqlQueryh);
	$golfdatta['is_delegated'] = ($del_data > 0) ? '1' : '0';
}
//$golfdatta['is_delegated']='0';
if(count($teamdata) > 0){
$golfdatta['is_team']=1;
$first_team_id=$teamdata[2]['first_team_id'];
    $golfdatta['teamdata']=$teamdata;//(isset($teamdata['current_standing']) && count($teamdata['current_standing'])>0)?$teamdata['current_standing']:array();
}
/* $queryString = " select t.player_id,t.handicap_value,t.score_entry_".$hole_number." as hole_num_".$hole_number.", p.no_of_putt_". */
if($golfdatta['is_team']==1){
/*$sqlp="SELECT t.player_id,g.full_name as player_name,t.format_id,t.no_of_holes_played,ep.team_id FROM event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list ep ON ep.event_id=t.event_id and ep.player_id=t.player_id where t.event_id='".$event_id."' group by t.player_id  order by ep.team_id ASC";//,t.player_id ASC*/
$sqlp = " select p.team_id,t.player_id,g.full_name as player_name,t.format_id,t.no_of_holes_played from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$event_id."' group by p.player_id order by p.team_id asc, t.player_id ASC ";

                      
}else{
$sqlp="SELECT t.player_id,g.full_name as player_name,t.format_id,t.no_of_holes_played,ep.team_id FROM event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list ep ON ep.event_id=t.event_id and ep.player_id=t.player_id where t.event_id='".$event_id."' group by t.player_id";
}


							$sqlresultty  = $this->db->FetchQuery($sqlp);
$player_counter=0;//print_r($sqlresultty);
if(count($sqlresultty) > 0){
foreach($sqlresultty as $x=>$k){
$player_counter++;    
$k['is_handicap_gain']='';	
$lastscore=array();

$holestrstr = '';
for($cbv=1;$cbv<=18;$cbv++) {
	$holestrstr[] = "score_entry_{$cbv} as pl_hole_{$cbv}";
}

$holestrstr = is_array($holestrstr) && count($holestrstr)>0 ? ",".implode(",",$holestrstr) : "";

$sql3='SELECT hole_number,handicap_value,handicap_value_3_4,calculated_handicap'.$holestrstr.' FROM event_score_calc  WHERE player_id="'.$k['player_id'].'" and event_id="'.$event_id.'"'; 
$handicapvalue= $this->db->FetchRow($sql3);

$k['played_hole_number'] = array();
$tnarr = array();

for($cbv=1;$cbv<=18;$cbv++) {
	if($handicapvalue['pl_hole_'.$cbv] > 0) {
		$tnarr[] = $cbv;
	}
}
$k['played_hole_number'] = $tnarr;


if($k['format_id']=="3" || $k['format_id']=="4" || $k['format_id']=="6" || $k['format_id']=="7" || $k['format_id']=="10" || $k['format_id']=="11" || $k['format_id']=="12" || $k['format_id']=="13" || $k['format_id']=="14"){
						
						$lastscore=$this->getLastScore($event_id,$k['format_id'],$hole_number,0,$k['player_id'],0,$golf_course_id);	
						if($k['format_id']=="4" || $k['format_id']=="7"){									
							$k['is_handicap_gain']=($handicapvalue['handicap_value_3_4'] >=$golfdatta['hole_index'])?1:0;	
							}elseif($k['format_id']=="10" || $k['format_id']=="11" || $k['format_id']=="12" || $k['format_id']=="13" || $k['format_id']=="14"){									
							$k['is_handicap_gain']=($handicapvalue['calculated_handicap'] >=$golfdatta['hole_index'])?1:0;
							}else{
							$k['is_handicap_gain']=($handicapvalue['handicap_value'] >=$golfdatta['hole_index'])?1:0;
						}
					}
$k['player_handicap'] = $handicapvalue['calculated_handicap'];

 if($k['format_id']=="10" || $k['format_id']=="11" || $k['format_id']=="12" || $k['format_id']=="13" || $k['format_id']=="14"){                                       
        if($golfdatta['is_team'] > 0){ //echo "<br>".$first_team_id."----".$k['team_id'];
            if($first_team_id==$k['team_id']){
                $k['player_color_code']=$this->setColorForPlayer(1,'Team A',0);
            }else{
                $k['player_color_code']=$this->setColorForPlayer(1,'Team B',0);
            }
        }else{
            $k['player_color_code']=$this->setColorForPlayer(0,'',$player_counter);
        } 
 }

unset($k['format_id']);	

$sql = "select temp_id,score_entry_{$hole_number},par_{$hole_number} from event_score_calc_temp where event_id ='".$event_id."' and player_id='".$k['player_id']."' and trim(score_entry_{$hole_number}) !=''";
$gtempdata = $this->db->FetchRow($sql);

if(isset($gtempdata["temp_id"]) && $gtempdata["temp_id"] > 0 && trim($gtempdata["score_entry_{$hole_number}"])!='') {
	$golfdattaspot11 = json_decode($gtempdata["score_entry_{$hole_number}"],true);
	$golfdattaspot = $golfdattaspot11;
	$golfdattaspot['closest_feet'] = $golfdattaspot['closest_feet'].'.'.$golfdattaspot['closest_inch'];
	$golfdatta['par_value'] = $gtempdata["par_{$hole_number}"];
	$golfdattaspot['hole_num_'.$hole_number] = $golfdattaspot['score'];
	$k["is_temporary"] = 1;
	$myholenumber = $hole_number;
}
else {
$queryString = " select t.hole_number as myholenumber,t.player_id,t.handicap_value,t.score_entry_".$hole_number." as hole_num_".$hole_number.", p.no_of_putt_".$hole_number." as no_of_putt,f.fairway_".$hole_number." as fairway,s.sand_".$hole_number." as sand,c.closest_feet_".$hole_number." as closest_feet from event_score_calc t LEFT JOIN event_score_calc_no_of_putt p ON p.event_id =t.event_id and p.player_id=t.player_id LEFT JOIN event_score_calc_sand s ON s.event_id =t.event_id and s.player_id=t.player_id LEFT JOIN event_score_calc_fairway f ON f.event_id =t.event_id and f.player_id=t.player_id LEFT JOIN event_score_calc_closest_feet c ON c.event_id =t.event_id and c.player_id=t.player_id where t.event_id ='".$event_id."' and t.player_id='".$k['player_id']."'"; 
$golfdattaspot = $this->db->FetchRow($queryString);
$k["is_temporary"] = (isset($golfdattaspot["hole_num_".$hole_number]) && $golfdattaspot["hole_num_".$hole_number]>0) ? 0 : 1;
$myholenumber = (isset($golfdattaspot["myholenumber"]) ? $golfdattaspot["myholenumber"] : $hole_number);
//$k["is_temporary"] = 0;
}

	
$clo = (isset($golfdattaspot['closest_feet']) && $golfdattaspot['closest_feet'] !='')?$golfdattaspot['closest_feet']:'';
$feet='-';$inches='-';
if($clo!=""){
$closeset = explode('.', $clo);	
$feet = $closeset[0];
$inches = (substr($closeset[1],0,1)=='0') ? substr($closeset[1],1) : $closeset[1];
}
$k['no_of_putt']=isset($golfdattaspot['no_of_putt'])?$golfdattaspot['no_of_putt']:-1;

if($k["is_temporary"] == 1 && $k['no_of_putt'] == '-1') {
	$k['no_of_putt'] = '2';
}



$k['sand']=isset($golfdattaspot['sand'])?$golfdattaspot['sand']:-1;	
if($golfdatta['par_value']==3){
$k['fairway']=4;	
}else{
$k['fairway']=isset($golfdattaspot['fairway'])?$golfdattaspot['fairway']:0;	
if($golfdatta['par_value']!=3 && $k["is_temporary"] == 1) {
	$k['fairway']=2;
}
}



$k['closest_feet']=$feet;	
$k['closest_inch']=$inches;	
$k['score_value']=(isset($golfdattaspot['hole_num_'.$hole_number]) && $golfdattaspot['hole_num_'.$hole_number] > 0)?$golfdattaspot['hole_num_'.$hole_number]:$golfdatta['par_value'];	
// $k['current_hole_number']=$handicapvalue['hole_number'];

//$k['current_hole_number']=$hole_number ;
$k['current_hole_number']=$myholenumber ;
if($lastscore!=""){
$k['last_score'][]=$lastscore;		
}else{
	$k['last_score']=array();
}

//$total[$x]=$k;	
$total[$k['player_id']]=$k;	
}
}
			$fdata['status'] = '1';
			$fdata['data'] = $golfdatta;
			
			if(isset($order_player_ids) && is_array($order_player_ids) && count($order_player_ids)>0) {
				$new_total = array();
				foreach($order_player_ids as $c) {
					$new_total[] = $total[$c];
				}
				$fdata['total'] = $new_total;
			}
			else {
				$fdata['total'] = array_values($total);
			}
			

			$fdata['message'] = 'Success';
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field Can Not Be Empty';
		}
		return $fdata ;
		
		
		
	}
        
        function checkTeamData($eventId){
            $sqlQueryh="SELECT format_id,admin_id,total_hole_num FROM event_table WHERE event_id='".$eventId."'";  
	    $event_data=$this->db->FetchQuery($sqlQueryh);
            $stroke_play_id=$event_data[0]['format_id'];
            $event_admin_id=$event_data[0]['admin_id'];
            $total_hole_num=$event_data[0]['total_hole_num'];        
            $is_team_game=false;$team_data=array();
                if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){
                //check is team 
                $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' group by p.player_id order by t.team_profile_id asc";  
                $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
                $team_name_arr=array();$team_id=array();$player_idArr=array();
                if(count($sqlresult1) >0){
                            foreach($sqlresult1 as $i=>$e){
                                    $player_idArr[]=$e['player_id'];
                                    if(!in_array($e['team_id'],$team_id)){
                                    $team_id[]=$e['team_id'];
									$team_name_arr[$e['team_id']] = $e['team_display_name'];
                                    }
                            }
                }						
                $uniqueteam=array_unique($team_id);
                $game_type = (count($player_idArr)=="4")?'team':'';				
                $is_team_game = ($game_type == 'team') ? true : false;
                $currentScoreListArray['is_team'] =($is_team_game)?"1":"0";
                    if($is_team_game){
                       $queryString = " select p.event_admin_id,p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name ";
                       $queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' group by p.player_id order by p.team_id asc,p.player_id ASC ";
                       //echo $queryString;
                       $teamrec = $this->db->FetchQuery($queryString);	
                       foreach($teamrec as $t=>$row){
                            if($uniqueteam[0]==$row['team_id']){
                                $teamplayerarray1[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                            }else{
                                $teamplayerarray2[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                            } 
                       }
                       $teamarray[]=array('team_id'=>$uniqueteam[0],'team_name'=>(isset($team_name_arr[$uniqueteam[0]]) ? $team_name_arr[$uniqueteam[0]] : 'Team A'),'player_list'=>$teamplayerarray1);
                       $teamarray[]=array('team_id'=>$uniqueteam[1],'team_name'=>(isset($team_name_arr[$uniqueteam[1]]) ? $team_name_arr[$uniqueteam[1]] : 'Team B'),'player_list'=>$teamplayerarray2);
                       //$event_admin_id=$teamrec[0]['event_admin_id'];
                       $standingdata=$this->getStandingForNewGameFormat($total_hole_num,$stroke_play_id,$eventId,$event_admin_id);
//print_r($standingdata['current']);
						if($stroke_play_id == 11) {
							$sz = $standingdata['current'];
							unset($standingdata['current']);
							$standingdata['current'][0] = $sz;
						}
						
                       $teamarray[]=array('current_standing'=>$standingdata['current'],'first_team_id'=>$uniqueteam[0]);
//print_r($teamarray); die;
                       $team_data =$teamarray;
                    }
                }
                return $team_data;
        }
	
	function getScoreBoard($data,$event_id=0,$admin_id=0){
		$event_id = (isset($data['event_id']) && $data['event_id']!='')?$data['event_id']:$event_id;
		$admin_id = (isset($data['admin_id']) && $data['admin_id']!='')?$data['admin_id']:$admin_id;
		$resultfinal=array();
		$invalid=0;
		if($event_id >0 && $admin_id > 0){
		$queryString = " select admin_id,golf_course_id,is_started,format_id,DATE(event_start_date_time) as event_start_date_time,event_start_time,total_hole_num,hole_start_from from event_table where event_id =".$event_id;
					$result  = $this->db->FetchRow($queryString);
					$admin_id = $result['admin_id'];
					$golf_course_id = $result['golf_course_id'];
					$is_started = $result['is_started'];
					$stroke_play_id = $result['format_id'];
					$event_start_date_time = $result['event_start_date_time'];
					$event_start_time = $result['event_start_time'];
					$total_hole_num = $result['total_hole_num'];
					$hole_start_from = $result['hole_start_from'];
					$counter=($hole_start_from==10)?10:1;
					$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
$total = array();
					if($stroke_play_id=="11"){
						$sql="SELECT hole_number,winner,event_id,score_value,color,back_to_9_score FROM `event_score_autopress` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$scoreArr=json_decode($e['score_value']);
								$backto9scoreArr=json_decode($e['back_to_9_score']);
								$result1['hole_number'] = $e['hole_number'];
								$result1['score_count'] = count($scoreArr);
								$result1['score_value'] = json_decode($e['score_value']);
								$result1['back_to_9_score'] =(count($backto9scoreArr)>0)?json_decode($e['back_to_9_score']):array();
								$result1['winner'] = $e['winner'];
								$result1['color'] = $e['color'];
								//$result1['color_code'] = $e['color'];
								$resultfinal[$e['hole_number']]=$result1;
								
							}
						}
					}elseif($stroke_play_id=="10"){
						$sql="SELECT hole_number,winner,event_id,score_value,color FROM `event_score_matchplay` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['score_value'] = $e['score_value'];
								//$result1['winner'] = ($e['score_value']!='AS') ? $e['winner'] : "0";
								$result1['winner'] = ($e['score_value']!='AS') ? $e['winner'] : "0";
								$result1['color'] =$e['color'];
								$resultfinal[$e['hole_number']]=$result1;								
							}
						}
					}elseif($stroke_play_id=="12"){
                                           // echo $counter; echo "<br>".$total_hole_num;
						for($a=$counter;$a<=$total_hole_num;$a++){
							$result1=array();
							$sql="SELECT s.hole_number,s.player_id,s.score_value,s.total,u.full_name FROM `event_score_4_2_0` s LEFT JOIN golf_users u ON u.user_id = s.player_id where s.event_id='".$event_id."' and s.hole_number='".$a."' order by s.hole_number asc";
							$sqlresult1  = $this->db->FetchQuery($sql);
							if(count($sqlresult1) >0){
									$result1[$a]=$sqlresult1;
                                                                        $resultfinal[]=$result1;
							}else{
								//break;
							}
                                                        $result1=array();
                                                        $sqlp="SELECT total_score,player_id FROM event_score_calc where event_id='".$event_id."' group by player_id ";
                                                        $sqlresultty  = $this->db->FetchQuery($sqlp);
                                                        if(count($sqlresultty) > 0){
                                                        foreach($sqlresultty as $x=>$k){
                                                        $total[$x]=$k;	
                                                        }
                                                        }
						}
                                                
					}elseif($stroke_play_id=="13"){
						$sql="SELECT hole_number,winner,event_id,score_value,color_code FROM `event_score_vegas` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['score_value'] = $e['score_value'];
								$result1['winner'] = ($e['score_value']!='AS') ? $e['winner'] : "0";
                                $result1['color'] = (isset($e['color_code']) && trim($e['color_code'])!='') ? $e['color_code'] : $this->getColorCode("black");
								$resultfinal[$e['hole_number']]=$result1;								
							}
						}
					}elseif($stroke_play_id=="14"){
						$sql="SELECT hole_number,2_point,1_point,winner,event_id,score_value,color_code FROM `event_score_2_1` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['2_point'] = $e['2_point'];
								$result1['1_point'] = $e['1_point'];
								$result1['score_value'] = $e['score_value'];
								$result1['winner'] = ($e['score_value']!='AS') ? $e['winner'] : "0";
                                $result1['color'] = (isset($e['color_code']) && trim($e['color_code'])!='') ? $e['color_code'] : $this->getColorCode("black");
								$resultfinal[$e['hole_number']]=$result1;
								
							}
						}
					}else{
						$invalid=1;
					}
					//print_r($resultfinal); die;
					//$result1['score_value']=json_decode($result['score_value']);
					if($invalid==0){
					$fdata['status'] = '1';
					$fdata['data'] = $resultfinal;
$fdata['total'] = $total;
					$fdata['message'] = 'success.';
					}else{
					$fdata['status'] = '0';
			        $fdata['message'] = 'Invalid Game Format.';	
					}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field Can Not Be Empty';
		}
		return $fdata;
	}
	function getScoreCardData($data) {
		$fdata =array();
		$event_id = (isset($data['event_id']) && $data['event_id']!='')?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']!='')?$data['admin_id']:'0';
		$delegated_status = (isset($data['delegated_status']) && $data['delegated_status']=='1')?'1':'0';
		if($event_id >0 && $admin_id > 0){			
			$sqlQuery="SELECT e.event_id,e.format_id,e.event_name,e.golf_course_id,g.golf_course_name,e.total_hole_num,e.hole_start_from,e.is_spot,e.no_of_player FROM event_table e left join golf_course g ON g.golf_course_id=e.golf_course_id WHERE e.event_id='".$event_id."'";  
			$sqlresult  = $this->db->FetchRow($sqlQuery);
			$is_spot_type = 0;
			if($sqlresult['is_spot'] > 0){
			$sqlQueryh="SELECT type FROM event_is_spot_tbl WHERE event_id='".$event_id."' and hole_number =".$sqlresult['hole_start_from']."";  
			$is_spot_type  = $this->db->FetchSingleValue($sqlQueryh);
			}
			
			$sqlQueryh="SELECT start_from_hole FROM event_score_calc WHERE event_id='".$event_id."' and player_id = '".$admin_id."'";  
			$start_from_hole  = $this->db->FetchSingleValue($sqlQueryh);
			
            $first_team_id=0;
            if(is_array($sqlresult) && count($sqlresult)>0){
				/*$sqlQuery1="SELECT p.event_admin_id,p.team_id,p.player_id,p.is_accepted,u.full_name,s.calculated_handicap as self_handicap,s.score_entry_1,s.score_entry_2,s.score_entry_3,s.score_entry_4,s.score_entry_5,s.score_entry_6,s.score_entry_7,s.score_entry_8,s.score_entry_9,s.score_entry_10,s.score_entry_11,s.score_entry_12,s.score_entry_13,s.score_entry_14,s.score_entry_15,s.score_entry_16,s.score_entry_17,s.score_entry_18 FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id LEFT JOIN event_score_calc s ON s.player_id=p.player_id and s.event_id=p.event_id WHERE e.event_id='".$event_id."' and (p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."') and p.is_accepted ='1' "; */
				$teamdata=$this->checkTeamData($event_id);
				if(count($teamdata) > 0){
					$first_team_id=$teamdata[2]['first_team_id'];
					
					$player_team_id = $this->db->FetchSingleValue("SELECT team_id FROM event_player_list WHERE event_id='".$event_id."' and player_id =".$admin_id."");
					
					/*$sqlQuery1="SELECT p.event_admin_id,p.team_id,p.player_id,p.is_accepted,u.full_name,s.calculated_handicap as self_handicap,s.score_entry_1,s.score_entry_2,s.score_entry_3,s.score_entry_4,s.score_entry_5,s.score_entry_6,s.score_entry_7,s.score_entry_8,s.score_entry_9,s.score_entry_10,s.score_entry_11,s.score_entry_12,s.score_entry_13,s.score_entry_14,s.score_entry_15,s.score_entry_16,s.score_entry_17,s.score_entry_18 FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id LEFT JOIN event_score_calc s ON s.player_id=p.player_id and s.event_id=p.event_id WHERE e.event_id='".$event_id."' and (p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."') and p.is_accepted ='1' order by p.team_id ASC,p.player_id ASC"; */
					$sqlQuery1 = " select p.delegate_status,p.event_admin_id,p.team_id,p.player_id,p.is_accepted,g.full_name,s.calculated_handicap as self_handicap,s.hole_number as last_played,s.player_color,s.score_entry_1,s.score_entry_2,s.score_entry_3,s.score_entry_4,s.score_entry_5,s.score_entry_6,s.score_entry_7,s.score_entry_8,s.score_entry_9,s.score_entry_10,s.score_entry_11,s.score_entry_12,s.score_entry_13,s.score_entry_14,s.score_entry_15,s.score_entry_16,s.score_entry_17,s.score_entry_18,(select count(1) from event_score_calc where event_id='".$event_id."') as total_players from event_score_calc s left join golf_users g ON g.user_id=s.player_id left join event_player_list p ON p.player_id = s.player_id and p.event_id =s.event_id where s.event_id ='".$event_id."' group by p.player_id order by case when p.team_id = '{$player_team_id}' then 1 else 2 end,case when p.player_id = '{$admin_id}' then 1 else 2 end ";//  and p.is_accepted ='1' and (p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."')
				}
				else{
					$sqlQuery1="SELECT p.delegate_status,p.event_admin_id,p.team_id,p.player_id,p.is_accepted,u.full_name,s.calculated_handicap as self_handicap,s.hole_number as last_played,s.player_color,s.score_entry_1,s.score_entry_2,s.score_entry_3,s.score_entry_4,s.score_entry_5,s.score_entry_6,s.score_entry_7,s.score_entry_8,s.score_entry_9,s.score_entry_10,s.score_entry_11,s.score_entry_12,s.score_entry_13,s.score_entry_14,s.score_entry_15,s.score_entry_16,s.score_entry_17,s.score_entry_18,(select count(1) from event_score_calc where event_id='".$event_id."') as total_players FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id LEFT JOIN event_score_calc s ON s.player_id=p.player_id and s.event_id=p.event_id WHERE e.event_id='".$event_id."' and (p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."') and p.is_accepted ='1' order by case when p.player_id = '{$admin_id}' then 1 else 2 end ";     //(p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."')
				} //echo $sqlQuery1;die;
				$sqlresult1  = $this->db->FetchQuery($sqlQuery1);
				
				$sql2='SELECT hole_index_'.$sqlresult['hole_start_from'].' as hole_index,par_value_'.$sqlresult['hole_start_from'].' as par_value FROM golf_hole_index WHERE golf_course_id="'.$sqlresult['golf_course_id'].'"';  
				$holenumb  = $this->db->FetchRow($sql2);
				$players =$player_admin =array();
				if(count($sqlresult1) >0){ //print_r($sqlresult1);die;
				 $player_counter=0;
					foreach($sqlresult1 as $i=>$e){
						$fdata['total_players'] = $e['total_players'];
						$player_counter++;
						$nam = explode(' ',$e['full_name']);
						$namf = (isset($nam[0]) && $nam[0] !='')?$nam[0]:'';
						$naml = (isset($nam[1]) && $nam[1] !='')?$nam[1]:'';
						if(in_array($last, $e)){
							$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 2):substr($namf, 0, 2);
							$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
						}
						else{
							$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 1):substr($namf, 0, 2);
							$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
						}
						$e['is_handicap_gain']='';
						for($j=1;$j<=18;$j++){
							if($e['score_entry_'.$j] > 0 ){
								$e['played_hole_number'][]=$j;	
							}
							unset($e['score_entry_'.$j]);
						}
						
						//$e['last_hole_played'] = (isset($e['played_hole_number']) && count($e['played_hole_number'])>0)?end($e['played_hole_number']):0;
						$e['last_hole_played'] = $e['last_played'];
	$e['played_hole_number'] = (count($e['played_hole_number'])>0)?$e['played_hole_number']:array();
						if($sqlresult['format_id']=="3" || $sqlresult['format_id']=="4" || $sqlresult['format_id']=="6" || $sqlresult['format_id']=="7"  || $sqlresult['format_id']=="10" || $sqlresult['format_id']=="11" || $sqlresult['format_id']=="12" || $sqlresult['format_id']=="13" || $sqlresult['format_id']=="14"){
							$sql3='SELECT handicap_value,handicap_value_3_4,calculated_handicap FROM event_score_calc WHERE player_id="'.$e['player_id'].'" and event_id="'.$event_id.'"'; 
							$handicapvalue= $this->db->FetchRow($sql3);
							if($sqlresult['format_id']=="4" || $sqlresult['format_id']=="7"){
								$e['self_handicap']=$handicapvalue['handicap_value_3_4'];	
								$e['is_handicap_gain']=($handicapvalue['handicap_value_3_4'] >=$holenumb['hole_index'])?1:0;
								}elseif($sqlresult['format_id']=="10" || $sqlresult['format_id']=="11" || $sqlresult['format_id']=="12" || $sqlresult['format_id']=="13" || $sqlresult['format_id']=="14"){									
								$e['is_handicap_gain']=($handicapvalue['calculated_handicap'] >=$holenumb['hole_index'])?1:0;
								}else{
								$e['is_handicap_gain']=($handicapvalue['handicap_value'] >=$holenumb['hole_index'])?1:0;	
							}
						}
						$e['short_name'] = $last;
						if($sqlresult['format_id']=="10" || $sqlresult['format_id']=="11" || $sqlresult['format_id']=="12" || $sqlresult['format_id']=="13" || $sqlresult['format_id']=="14"){
							if(count($teamdata) > 0){
								if($first_team_id==$e['team_id']){
									$e['player_color_code']=$this->setColorForPlayer(1,'Team A',0);
								}
								else{
									$e['player_color_code']=$this->setColorForPlayer(1,'Team B',0);
								}
							}
							else{ //echo $player_counter.'<br/>';
								if($e['player_id'] == $e['event_admin_id']){
									$player_counter1 = 1;$player_counter--;
								}
								else {
									$player_counter1 = $player_counter+1;
									$player_counter = $player_counter1;
									$player_counter1 = ($player_counter1>count($sqlresult1)) ? count($sqlresult1) : $player_counter1;
								}

								$e['player_color_code']=$this->setColorForPlayer(0,'',$player_counter1);
							}
							if(trim($e['player_color'])!='') {
							$e['player_color_code'] = $e['player_color'];
							}
							unset($e['player_color']);
							/*if(count($teamdata) > 0){
							$player_admin=array();
							$players[] = $e;
							}else{
							if($e['player_id'] == $e['event_admin_id']){
							$player_admin[] = $e;
							}else{
							$players[] = $e;
							} 
							}*/
						}
						//if($e['player_id'] == $e['event_admin_id'] && $e['delegate_status']!='1'){
						if($e['player_id'] == $admin_id && $e['delegate_status']!='1'){
							$player_admin[] = $e;
						}
						else{
							$players[] = $e;
						}
						/**/
						$d= (isset($e['team_id']) && $e['team_id'] >0)?1:0;
						//$player[] = $e;
					}
					//die;
					$player = array_merge($player_admin,$players);
					$fdata['status'] = '1';
					$fdata['event_id'] = $sqlresult['event_id'];
					$fdata['event_name'] = $sqlresult['event_name'];
					$fdata['format_id'] = $sqlresult['format_id'];
					$fdata['golf_course_id'] = $sqlresult['golf_course_id'];
					$fdata['golf_course_name'] = $sqlresult['golf_course_name'];
					$fdata['hole_start_from'] = $start_from_hole>0 ? $start_from_hole:$sqlresult['hole_start_from'];
					$fdata['total_hole_num'] = $sqlresult['total_hole_num'];
					$fdata['is_team'] = $d;
					$fdata['is_spot_type'] = $is_spot_type;
					$fdata['par_value'] = $holenumb['par_value'];
					$fdata['hole_index'] = $holenumb['hole_index'];
					$fdata['is_4plus_game'] = ($sqlresult['no_of_player'] == '4+') ? '1' : '0';
					$fdata['status'] = '1';
					$fdata['data'] = $player;
					$fdata['message'] = 'success';
					
					if($delegated_status == '1') {
						$sql = "update event_player_list set delegate_status='1' where event_id='{$event_id}' and scorere_id='{$admin_id}'";
						$this->db->FetchQuery($sql);
					}
					
				}
				else{
					$fdata['status'] = '0';
					$fdata['message'] = 'Friend List Empty';
				}
			}
			else{
				$fdata['status'] = '0';
				$fdata['message'] = 'Data not found';	
			}
		}	
		else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field Can Not Be Empty';
		}
		return $fdata ;
	}
	
	function enterScoreTemp($data,$eventId,$admin_id,$strokeId,$par,$holeId){
		$playerId = (isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:'0';
		
		$json_data = json_encode($data);

		
		if($eventId >0 && $playerId>0 && $holeId >0){
			$sql = "select temp_id from event_score_calc_temp where event_id = '{$eventId}' and player_id = '{$playerId}'";
			$temp_id = $this->db->FetchSingleValue($sql);
			
			$hole_str = "score_entry_{$holeId}";
			$par_str = "par_{$holeId}";
			
			if($temp_id > 0) {
				$sql = "UPDATE event_score_calc_temp SET `last_hole_entered` = '{$holeId}', `{$hole_str}` = '{$json_data}', `{$par_str}` = '{$par}' where temp_id = '{$temp_id}'";
			}
			else {
				$sql = "INSERT INTO event_score_calc_temp (`event_id`, `player_id`, `last_hole_entered`, `{$hole_str}`, `{$par_str}`) VALUES ('{$eventId}','{$playerId}','{$holeId}','{$json_data}','{$par}')";
			}
			$this->db->FetchQuery($sql);
			$fdata['status'] = '1';
			$fdata['message'] = 'Success.';
		}
		else {
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Fields not found.';
		}
		return $fdata ; 
	}
	
	
	
	
	
	
	
	
	
	
	function enterScore($data,$eventId,$admin_id,$strokeId,$par,$holeId){
		//$this->submit_player_score_temp();die;
		/* $eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']>0)?$data['admin_id']:'0';
		$strokeId = (isset($data['stroke_id']) && $data['stroke_id']!='')?$data['stroke_id']:'0';
		$par = (isset($data['par']) && $data['par']!='')?$data['par']:'3';
		$holeId = (isset($data['hole_number']) && $data['hole_number']>0)?$data['hole_number']:'0'; */

		$playerId = (isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:'0';
		$score = (isset($data['score']) && $data['score']>0)?$data['score']:'0';
		$no_of_putt = (isset($data['no_of_putt']) && $data['no_of_putt']>=0)?$data['no_of_putt']:'-1';
		$fairway = (isset($data['fairway']) && $data['fairway']>0)?$data['fairway']:'0';
		$sand = (isset($data['sand']) && $data['sand']>=0)?$data['sand']:'-1';
		$closest_feet = (isset($data['closest_feet']) && $data['closest_feet']!='')?$data['closest_feet']:'-1';
		$closest_inch = (isset($data['closest_inch']) && $data['closest_inch']!='')?$data['closest_inch']:'0';
//echo $eventId.'__'.$admin_id.'__'.$strokeId.'__'.$par.'__'.$holeId; die;
		
		if($eventId >0 && $playerId>0 && $holeId >0){
			
			if($score <= 0){
				$fdata['status'] = '0';
				$fdata['message'] = 'Score allowed will be greater than 0.';
			}elseif($score > 20){
				$fdata['status'] = '0';
				$fdata['message'] = 'Score allowed will be less than or equal to 20';
				
			}
			else{
				
				$startArray = array();
				$queryString = " select is_submit_score from event_player_list where player_id='".$playerId."' and event_id =".$eventId;
				$is_submit_score_row  = $this->db->FetchRow($queryString);
				$is_submit_score = isset($is_submit_score_row['is_submit_score']) ? $is_submit_score_row['is_submit_score'] : 0;
				
				$queryString = " select start_from_hole from event_score_calc where player_id='".$playerId."' and event_id =".$eventId;
				$is_submit_score_row  = $this->db->FetchRow($queryString);
				
				$start_from_hole = (isset($is_submit_score_row['start_from_hole']) && $is_submit_score_row['start_from_hole']>0) ? $is_submit_score_row['start_from_hole'] : $holeId;
				if($is_submit_score=="1" && $admin_id!=$playerId){
					$fdata['status'] = '0';
					$fdata['message'] = 'After Score submitted, you can not enter score.';
				}else{
				$queryString = " select admin_id,golf_course_id,is_started,format_id,DATE(event_start_date_time) as event_start_date_time,event_start_time,total_hole_num,hole_start_from from event_table where event_id =".$eventId;
					$result  = $this->db->FetchRow($queryString);
					$admin_id = $result['admin_id'];
					$golf_course_id = $result['golf_course_id'];
					$is_started = $result['is_started'];
					$stroke_play_id = $result['format_id'];
					$event_start_date_time = $result['event_start_date_time'];
					$event_start_time = $result['event_start_time'];
					$total_hole_num = $result['total_hole_num'];
					$hole_start_from = $result['hole_start_from'];
				
				
				if($is_started=="3"){
					$checkupdatescor=1;
$this->updatePar($eventId,$par,$holeId,$playerId);
					if($checkupdatescor==1){
						//$queryString = "select hole_number from event_score_calc where event_id = ".$eventId." and player_id = ".$playerId;
						//$lastHoleNumber  = $this->db->FetchSingleValue($queryString);
						
						$queryString = "select hole_number,start_from_hole from event_score_calc where event_id = ".$eventId." and player_id = ".$playerId;
						$xarr  = $this->db->FetchRow($queryString);
						$lastHoleNumber  = $xarr['hole_number'];
						$start_hole  = $xarr['start_from_hole'];
						
						$hqueryString = " select handicap_value  from golf_course_user_handicap where golf_course_user_handicap.golf_course_id=  ".$golf_course_id." and participant_id = ".$playerId." and event_id = ".$eventId;
						$userHandicapValue  = $this->db->FetchSingleValue($hqueryString);
						$totalPar = 0;
						
						$pqueryString = " select par_value_".$holeId."  from golf_hole_index where golf_course_id = ".$golf_course_id;
						$totalPar  = $this->db->FetchSingleValue($pqueryString);
						
						if($totalPar==""){
							$totalPar=0;	
						}
						$queryString = "update event_score_calc set start_from_hole = ".$holeId." where event_id = ".$eventId." and player_id = ".$playerId." and hole_number = 0";
						$queryResult  = $this->db->FetchQuery($queryString);
						
						// moved last hole played to updateGrossScore
						
                        $querys = "update event_player_list set is_submit_score= 1,last_score_enter_on='".date('Y-m-d H:i:s')."' where event_id = ".$eventId." and player_id = ".$playerId."";
						$this->db->FetchQuery($querys );

						$queryStringu = "update golf_users set latest_event_id = ".$eventId.",format_id=".$stroke_play_id." where user_id = ".$playerId;
						$this->db->FetchQuery($queryStringu);
						
						$total_hole_num1=$total_hole_num;
						if($hole_start_from==10) {$total_hole_num1=18;}
						
						$is_admin = ($admin_id == $playerId) ? 1 : 0;
						
						if($stroke_play_id==10){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						//if($admin_id == $playerId) {
							//$this->updatematchplayScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
						//}
					    	
						}elseif($stroke_play_id==11){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	//if($admin_id == $playerId) {
								//$this->updateautopressScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							//}
						}elseif($stroke_play_id==12){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	if($admin_id == $playerId) {
								//$this->update420Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							}
						}elseif($stroke_play_id==13){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	//if($admin_id == $playerId) {
								//$this->updateVegasScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							//}
						}elseif($stroke_play_id==14){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					        //if($admin_id == $playerId) {
								//$this->update21Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
								//}
						}if(1==1){
							if($stroke_play_id<10){
							$this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin);
							$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
							}
							$this->update34NetStrokePlayScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
							$this->updateMoreNetHandicapValue($eventId,$golf_course_id,$playerId,$stroke_play_id,$holeId);
							$this->updateGrossStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
							$this->updateNetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
							$this->update34NetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
							$this->updatePeoriaScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
									
						}
$this->updatePosition($eventId,$stroke_play_id,$golf_course_id);
$this->updatePositionGross($eventId,$stroke_play_id,$golf_course_id);
						//$this->updateNetScoreAccordingtoCalculatedhandicap($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updatePositionAccordingtohole($eventId,$holeId,$stroke_play_id,$playerId,$golf_course_id,$total_hole_num1,$hole_start_from);
						
                        $this->updateNoOFHolesPlayed($eventId,$strokeId,$playerId,$total_hole_num1,$hole_start_from);
						$this->updateNoOFPutt($eventId,$no_of_putt,$holeId,$playerId,$par,$score);
						$this->updateFairways($eventId,$fairway,$holeId,$playerId);
						$this->updateSands($eventId,$sand,$holeId,$playerId);

						$this->updateClosestToPinFeet($eventId,$closest_feet,$closest_inch,$holeId,$playerId);
						//$this->updateClosestToPinInches($eventId,$closest_inch,$holeId,$playerId);
						//$lastscore=$this->getLastScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id);
						$leaderboardview=$this->getLeaderboardScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id);

						if($stroke_play_id!=10 && $stroke_play_id!=11 && $stroke_play_id!=12 && $stroke_play_id!=13 && $stroke_play_id!=14){
							$queryString = "delete from temp_event_score_entry where temp_event_score_entry.event_id = ". $eventId;
							$queryResult  = $this->db->FetchQuery($queryString);
						$queryString = "insert into temp_event_score_entry";
							$queryString .= " select * from event_score_calc ";
							$queryString .= " where event_id = ".$eventId." order by gross_score desc";
							// $queryResult = $this->db->FetchQuery($queryString);						
						}
					}
					
					
					if($stroke_play_id==10 && $stroke_play_id==11 || $stroke_play_id==12 || $stroke_play_id==13  || $stroke_play_id==14){ 
					$sql="select no_of_holes_played as hole_number from event_score_calc where event_id = ".$eventId." and player_id =".$playerId;	
					}else{
					$sql="select no_of_holes_played as hole_number from temp_event_score_entry where event_id = ".$eventId." and player_id =".$playerId;	
					}
					$tot_hole_played  = $this->db->FetchSingleValue($sql);
					
					$sql = "update event_score_calc_temp set score_entry_{$holeId}='',par_{$holeId}=0  where event_id = '{$eventId}' and player_id = '{$playerId}'";
					$this->db->FetchQuery($sql);
					
					$hole_msg='';					
					if($tot_hole_played == 9 && $total_hole_num==9){$hole_msg = "ok";}
					elseif($tot_hole_played == 18 && $total_hole_num==18){$hole_msg = "ok";}
					if($holeId < 18){
					$hole_number = $holeId+1;
					}else{
					$hole_number = '1';
					}
					$sqlQuery='SELECT hole_index_'.$hole_number.' as hole_index,par_value_'.$hole_number.' as par_value FROM golf_hole_index WHERE golf_course_id="'.$golf_course_id.'"';  
					$golfdatta  = $this->db->FetchRow($sqlQuery);
			        $is_spot_type = 0;
					$sqlQueryh="SELECT type FROM event_is_spot_tbl WHERE event_id='".$eventId."' and hole_number =".$hole_number."";  
					$golfdatta['is_spot_type']  = $this->db->FetchSingleValue($sqlQueryh);

$queryStringa = "update event_player_list set is_score_enterer='1' where event_id = ".$eventId." and player_id = ".$playerId;
				$this->db->FetchQuery($queryStringa );
$total =array();
$sql2='SELECT total_score,player_id,no_of_holes_played,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 FROM event_score_calc WHERE event_id="'.$eventId.'" group by player_id'; 
					$sqlresultty= $this->db->FetchQuery($sql2);
					//print_r($sqlresultty); die;				
					
					if(count($sqlresultty) > 0){
					
							foreach($sqlresultty as $x=>$k){	
for($i=1;$i<=18;$i++){
							
								$total[$x]['total_score']=$k['total_score'];	
								$total[$x]['player_id']=$k['player_id'];	
								$total[$x]['no_of_holes_played']=$k['no_of_holes_played'];	
								$total[$x]['hole_start_from']=$start_hole;	
								if($k['score_entry_'.$i] > 0 ){
									$total[$x]['played_hole_number'][]=$i;	
								}else{
									$total['played_hole_number']=array();	
								}
								
							}
						}
					}
					
					

					$fdata['status'] = '1';
					//$fdata['last_score_data']=$lastscore;
					$fdata['leader_board_view']=($leaderboardview > 0)?"1":"0";					
					$fdata['hole_data'] = $golfdatta;
$fdata['total'] = $total;
					$fdata['message'] = 'success.';
					
					
					
				}elseif($is_started=="4"){
					$fdata['status'] = '0';
					$fdata['message'] = 'Event closed';
				}elseif($is_started=="2"){
					$fdata['status'] = '0';
					$fdata['message'] = 'Event deleted.';
				}else{
					$fdata['status'] = '0';
					$fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date_time)).' '.date("h:i",strtotime($event_start_time))."";
				}

				}
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Feld not found.';
		}
		return $fdata ; 
	}
	
	function enterScoreFormatData($data,$eventId,$admin_id,$strokeId,$par,$holeId){
		
		/* $eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']>0)?$data['admin_id']:'0';
		$strokeId = (isset($data['stroke_id']) && $data['stroke_id']!='')?$data['stroke_id']:'0';
		$par = (isset($data['par']) && $data['par']!='')?$data['par']:'3';
		$holeId = (isset($data['hole_number']) && $data['hole_number']>0)?$data['hole_number']:'0'; */

		$playerId = (isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:'0';
		$score = (isset($data['score']) && $data['score']>0)?$data['score']:'0';
		$no_of_putt = (isset($data['no_of_putt']) && $data['no_of_putt']>0)?$data['no_of_putt']:'0';
		$fairway = (isset($data['fairway']) && $data['fairway']>0)?$data['fairway']:'0';
		$sand = (isset($data['sand']) && $data['sand']>0)?$data['sand']:'0';
		$closest_feet = (isset($data['closest_feet']) && $data['closest_feet']!='')?$data['closest_feet']:'0';
		$closest_inch = (isset($data['closest_inch']) && $data['closest_inch']!='')?$data['closest_inch']:'0';
//echo $eventId.'__'.$admin_id.'__'.$strokeId.'__'.$par.'__'.$holeId; die;
		
		if($eventId >0 && $playerId>0 && $holeId >0){
			
			if($score <= 0){
				$fdata['status'] = '0';
				$fdata['message'] = 'Score allowed will be greater than 0.';
			}elseif($score > 20){
				$fdata['status'] = '0';
				$fdata['message'] = 'Score allowed will be less than or equal to 20';
				
			}
			else{
				
				$startArray = array();
				$queryString = " select is_submit_score from event_player_list where player_id='".$playerId."' and event_id =".$eventId;
				$is_submit_score_row  = $this->db->FetchRow($queryString);
				$is_submit_score = isset($is_submit_score_row['is_submit_score']) ? $is_submit_score_row['is_submit_score'] : 0;
				
				$queryString = " select start_from_hole from event_score_calc where player_id='".$playerId."' and event_id =".$eventId;
				$is_submit_score_row  = $this->db->FetchRow($queryString);
				
				$start_from_hole = isset($is_submit_score_row['start_from_hole']) ? $is_submit_score_row['start_from_hole'] : $holeId;
				if($is_submit_score=="1" && $admin_id!=$playerId){
					$fdata['status'] = '0';
					$fdata['message'] = 'After Score submitted, you can not enter score.';
				}else{
				$queryString = " select admin_id,golf_course_id,is_started,format_id,DATE(event_start_date_time) as event_start_date_time,event_start_time,total_hole_num,hole_start_from from event_table where event_id =".$eventId;
					$result  = $this->db->FetchRow($queryString);
					$admin_id = $result['admin_id'];
					$golf_course_id = $result['golf_course_id'];
					$is_started = $result['is_started'];
					$stroke_play_id = $result['format_id'];
					$event_start_date_time = $result['event_start_date_time'];
					$event_start_time = $result['event_start_time'];
					$total_hole_num = $result['total_hole_num'];
					$hole_start_from = $result['hole_start_from'];
				if($admin_id != $playerId) { return true;}
				
				if($is_started=="3"){
					$checkupdatescor=1;
$this->updatePar($eventId,$par,$holeId,$playerId);
					if($checkupdatescor==1){
						$queryString = "select hole_number from event_score_calc where event_id = ".$eventId." and player_id = ".$playerId;
						$lastHoleNumber  = $this->db->FetchSingleValue($queryString);
						
						$hqueryString = " select handicap_value  from golf_course_user_handicap where golf_course_user_handicap.golf_course_id=  ".$golf_course_id." and participant_id = ".$playerId." and event_id = ".$eventId;
						$userHandicapValue  = $this->db->FetchSingleValue($hqueryString);
						$totalPar = 0;
						
						$pqueryString = " select par_value_".$holeId."  from golf_hole_index where golf_course_id = ".$golf_course_id;
						$totalPar  = $this->db->FetchSingleValue($pqueryString);
						
						if($totalPar==""){
							$totalPar=0;	
						}
						$queryString = "update event_score_calc set start_from_hole = ".$holeId." where event_id = ".$eventId." and player_id = ".$playerId." and hole_number = 0";
						$queryResult  = $this->db->FetchQuery($queryString);
						
						$hoqueryString = "update event_score_calc set hole_number = ".$holeId." where event_id = ".$eventId." and player_id = ".$playerId;
						$queryResult  = $this->db->FetchQuery($hoqueryString);
						
                        $querys = "update event_player_list set is_submit_score= 1 where event_id = ".$eventId." and player_id = ".$playerId."";
						$this->db->FetchQuery($querys );

						$queryStringu = "update golf_users set latest_event_id = ".$eventId.",format_id=".$stroke_play_id." where user_id = ".$playerId;
						$this->db->FetchQuery($queryStringu);
						
						$total_hole_num1=$total_hole_num;
						if($hole_start_from==10) {$total_hole_num1=18;}
						if($stroke_play_id==10){
                                                
						if($admin_id == $playerId) {
							$this->updatematchplayScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
						}
					    	
						}elseif($stroke_play_id==11){
                                                
					    	if($admin_id == $playerId) {
								$this->updateautopressScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							}
						}elseif($stroke_play_id==12){
                                                
					    	if($admin_id == $playerId) {
								$this->update420Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							}
						}elseif($stroke_play_id==13){
                                                
					    	if($admin_id == $playerId) {
								$this->updateVegasScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
							}
						}elseif($stroke_play_id==14){
                                                
					        if($admin_id == $playerId) {
								$this->update21Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$start_from_hole);
								}
						}

					}
					
					
					
					

					$fdata['status'] = '1';
					//$fdata['last_score_data']=$lastscore;
					$fdata['leader_board_view']=($leaderboardview > 0)?"1":"0";					
					$fdata['hole_data'] = $golfdatta;
$fdata['total'] = $total;
					$fdata['message'] = 'success.';
					
					
					
				}elseif($is_started=="4"){
					$fdata['status'] = '0';
					$fdata['message'] = 'Event closed';
				}elseif($is_started=="2"){
					$fdata['status'] = '0';
					$fdata['message'] = 'Event deleted.';
				}else{
					$fdata['status'] = '0';
					$fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date_time)).' '.date("h:i",strtotime($event_start_time))."";
				}

				}
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Feld not found.';
		}
		return $fdata ; 
	}
	
	
	function updatePositionAccordingtohole($eventId,$holeId,$stroke_play_id,$playerId,$golf_course_id,$total_hole_num,$hole_start_from){
		if($stroke_play_id=="3"){
			$fieldname="net_";
		}elseif($stroke_play_id=="4"){
			$fieldname="net_stableford_3_4_v_";
		}elseif($stroke_play_id=="5"){
			$fieldname="gross_stableford_"; $total_field_name="gross_stableford";
		}elseif($stroke_play_id=="6"){
			$fieldname="net_stableford_"; $total_field_name="net_stableford";
		}elseif($stroke_play_id=="7"){
			$fieldname="net_stableford_3_4_";
		}else{
			$fieldname="score_entry_";
		}
		$sqlQuery='SELECT * FROM golf_hole_index WHERE golf_course_id="'.$golf_course_id.'"';  
		$golfdatta  = $this->db->FetchRow($sqlQuery);
		$sqlQuery1="SELECT * from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
		$scoredata  = $this->db->FetchRow($sqlQuery1);
		//echo $total_hole_num.'==&& =='.$hole_start_from;
		$startfrom=($total_hole_num==18 && $hole_start_from==10)?10:1;
		
		for($i=$startfrom;$i<=$total_hole_num;$i++){
			//echo "<br>".$i;
		 $parvalue=$golfdatta['par_value_'.$i];
		 $score_value=$scoredata[$fieldname.$i];
		 
		 if($score_value <= 0) {
		     continue;
		 }
		 
		 if($parvalue==$score_value){
			 $position=0;
		 }else{
			 $position=($score_value-$parvalue);
		 }
		 $sqlQuery2="SELECT sum(position) as total from event_score_calc_position where event_id='".$eventId."' and player_id ='".$playerId."' and hole_number <= '".($i-1)."'";
		 $sumposition  = $this->db->FetchSingleValue($sqlQuery2);
		 $calculated_position=($position + $sumposition);
			$queryString = "select id";
			$queryString .= " from event_score_calc_position where event_id = ".$eventId;
			$queryString .= "  and hole_number ='".$i."' and player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc_position set calculated_position=".$calculated_position.",position = ".$position." where event_id = ".$eventId." and hole_number ='".$i."' and player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
			}
			else
			{
			   $queryString = " insert into event_score_calc_position(event_id,player_id,hole_number,position,calculated_position) values(".$eventId.",".$playerId.",".$i.",".$position.",".$calculated_position.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			}
		}
	}
	
	function getLastScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id){
		        $queryString = "select start_from_hole";
			$queryString .= " from event_score_calc where event_id = ".$eventId;
			$queryString .= "  and player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$start_from_hole = $this->db->FetchSingleValue($queryString);
			$prevhole=($holeId-1);
			$prevhole = ($prevhole <= 0) ? 18 : $prevhole;
			$queryResult='';
                        if($holeId >= 1){//$start_from_hole
				if($stroke_play_id==10){
				$queryString = " select score_value,winner,color from event_score_matchplay where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
				}elseif($stroke_play_id==11){
				$queryString = " select score_value,back_to_9_score,winner,color from event_score_autopress where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
$queryResult['score_value']=json_decode($queryResult['score_value']);
								$backto9scoreArr=json_decode($queryResult['back_to_9_score']);
								$queryResult['back_to_9_score'] =(count($backto9scoreArr)>0)?json_decode($queryResult['back_to_9_score']):array();
								$queryResult['score_value'] =(is_array($queryResult['score_value']) && count($queryResult['score_value'])>0)?$queryResult['score_value']:array();
				}elseif($stroke_play_id==12){
				$queryString = " select total as score_value,total from event_score_4_2_0 where hole_number='".$prevhole."' and player_id='".$playerId."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
				}elseif($stroke_play_id==13){
				$queryString = " select score_value,winner from event_score_vegas where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
				}elseif($stroke_play_id==14){
				$queryString = " select 2_point,1_point,score_value,winner from event_score_2_1 where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
				}else{
					
				}
			}
			return $queryResult;
	}
	
	function getLeaderboardScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id){
				$queryResult=array();
		    	if($stroke_play_id==10){
				$queryString = " select * from event_score_matchplay where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				}elseif($stroke_play_id==11){
				$queryString = " select * from event_score_autopress where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				}elseif($stroke_play_id==12){
				$queryString = " select * from event_score_4_2_0 where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				}elseif($stroke_play_id==13){
				$queryString = " select * from event_score_vegas where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				}elseif($stroke_play_id==14){
				$queryString = " select * from event_score_2_1 where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				}else{
					
				}
			return count($queryResult);
	}
	
	function updatematchplayScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
		    $counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
		    $queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else
			{
			   $queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			   $event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
				$sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by t.team_profile_id asc";  
					 $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
					 $team_id=array();
					if(count($sqlresult1) >0){
                                            if(count($sqlresult1) ==2){
                                            $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_score_calc e left join event_player_list p ON p.event_id=e.event_id and p.player_id=e.player_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by e.is_admin desc";  
					    $sqlresult1  = $this->db->FetchQuery($sqlQuery1);    
                                            }
						foreach($sqlresult1 as $i=>$e){
							/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."' order by is_admin desc";
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($j=$hole_start_from;$j<=($hole_start_from+17);$j++){
								$i = ($j<=18) ? $j : ($j-18);
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
                                                                $playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
							}
							$player_id[]=$e['player_id'];
							$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
							if(!in_array($e['team_id'],$team_id)){
							$team_id[]=$e['team_id'];
							}
						}
					}						
					$uniqueteam=array_unique($team_id);
					//print_r($player);die;
				$game_type = (count($player_id)=="4")?'team':'';				
				$is_team_game = ($game_type == 'team') ? true : false;				
				$start_value_first = $start_value_second = '0';				
				$color_team_a = $this->getColorCode("red");	$color_team_b = $this->getColorCode("blue");$color_team_both = "";				
				$color_bg_a = $this->getColorCode("red");	$color_bg_b = $this->getColorCode("blue");	$color_bg_both = $this->getColorCode("black");				
				$resultstr = $finalstr = '';
				$result_arr = $final_result_arr = array();
				$required_player_score=0;
				
				if($is_team_game){
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
					$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();
					$score_b =  isset($player_id[2]) ? $player[$player_id[2]] : array();
					$score_b2 =  isset($player_id[3]) ? $player[$player_id[3]] : array();
                                        
                                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
					$grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
					$grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
					$grossscore_b2 =  isset($player_id[3]) ? $playergross[$player_id[3]] : array();
                                        
					if(count($grossscore_a)>0 && count($grossscore_a2)>0 && count($grossscore_b)>0 && count($grossscore_b2)>0) {
					$required_player_score=1;	
					}
				}else{
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
					$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
                                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
                                        $grossscore_b = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
					if(count($grossscore_a)>0 && count($grossscore_b)>0) {
					$required_player_score=1;	
					}
				}
//echo $required_player_score;die;
				if($required_player_score==1) {
					$queryString = " delete from event_score_matchplay where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
					$final_result = array();
					$pervious_winner_score = 0;
					$pervious_winner_name = '';
					$pervious_winner_class = '';
//print_r($score_a2);die;

					foreach($score_a as $a=>$b) {
						$winner_name = $hole_winner_class = $bgclass = '';
						$team_a_sum = $team_b_sum = $winning_score = 0;
						$a1_val = $b;
						$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
						$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
						$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;

                                                        $grossa1_val=isset($grossscore_a[$a]) ? $grossscore_a[$a] : 0;
                                                        $grossa2_val=isset($grossscore_a2[$a]) ? $grossscore_a2[$a] : 0;  
                                                        $grossb1_val=isset($grossscore_b[$a]) ? $grossscore_b[$a] : 0; 
                                                        $grossb2_val=isset($grossscore_b2[$a]) ? $grossscore_b2[$a] : 0;       
						$last_index = ($a==1) ? 0 : ($a-2);
						$current_index = ($a==1) ? 0 : ($a-1);
						$tpasum = $tpbsum = 0;
						if($is_team_game) {
							if($grossa2_val>0 && $grossa1_val>0) {
								$team_a_sum = ($a2_val<$a1_val) ? intval($a2_val) : intval($a1_val);
								$tpasum = ($a2_val<$a1_val) ? intval($a1_val) : intval($a2_val);
							}
							if($grossb2_val>0 && $grossb1_val>0) {
								$team_b_sum = ($b2_val<$b1_val) ? intval($b2_val) : intval($b1_val);
								$tpbsum = ($b2_val<$b1_val) ? intval($b1_val) : intval($b2_val);
							}
							if($tpasum!=$tpbsum && $team_a_sum == $team_b_sum) {
								//$team_a_sum = $tpasum;
								//$team_b_sum = $tpbsum;
							}
						}
						else {
							if($grossa1_val>0) {
								$team_a_sum = intval($a1_val);
							}
							if($grossb1_val>0) {
								$team_b_sum = intval($b1_val);
							}
						}
//echo "<br>".$team_a_sum.'======='.$team_b_sum."<br>";//die;
						if($team_a_sum>0 || $team_b_sum>0){
							if($team_a_sum < $team_b_sum) {
								// winner :: TEAM A
								$winner_name = $hole_winner_name = 'TEAM A';
								$hole_winner_class = $color_team_a;
								$winning_score = 1;
								$current_winner_class = $color_bg_a;
								if($current_index>=0) {
									if($pervious_winner_name!='' && $pervious_winner_name!=$winner_name) {
										//$temp_score = $pervious_winner_score - $winning_score;
                                                                                $temp_score = ($pervious_winner_score > 0) ? ($pervious_winner_score - $winning_score) : $winning_score;
										/*if($temp_score>0) {
											$winner_name = $pervious_winner_name;
											$hole_winner_class = ($pervious_winner_name == 'TEAM A') ? $color_team_a : $color_team_b;
											$winning_score = $temp_score;
										}*/
                                                                                if($temp_score>0) {
                                                                                        $winner_name = $pervious_winner_name;
                                                                                        if($pervious_winner_name == 'TEAM B' && $temp_score >= 1) {
                                                                                                $hole_winner_class = ($pervious_winner_score > 0) ? $color_team_b : $color_team_a;
                                                                                                $current_winner_class = ($pervious_winner_score > 0) ? $color_bg_b : $color_bg_a;
                                                                                        }
                                                                                        else {
                                                                                                if($temp_score > 1) {
                                                                                                        $hole_winner_class = $color_team_b;
                                                                                                        $current_winner_class = $color_bg_b;
                                                                                                }
                                                                                                else {
                                                                                                        $hole_winner_class = $color_team_a;
                                                                                                        $current_winner_class = $color_bg_a;
                                                                                                }
                                                                                        }
                                                                                        //$hole_winner_class = ($pervious_winner_name == 'TEAM A' && $temp_score > 1) ? $color_team_a : $color_team_b;
                                                                                        $winner_name = ($hole_winner_class == $color_team_a) ? 'TEAM A' : 'TEAM B';
                                                                                        $winning_score = $temp_score;
                                                                                }
										elseif($temp_score<=0) {
											$winning_score = 0;											
										}
									}
									else {
										$winning_score = $pervious_winner_score + $winning_score;
									}
								}
								$final_result[$a] = array('winner'=>$winner_name,'hole_winner'=>$hole_winner_name,'current_winner_class'=>$current_winner_class,'winner_class'=>$hole_winner_class,'score'=>intval($winning_score));
							}
							elseif($team_b_sum < $team_a_sum) {
								// winner :: TEAM B
								$winner_name = $hole_winner_name = 'TEAM B';
								$hole_winner_class = $color_team_b;
								$winning_score = 1;
								$current_winner_class = $color_bg_b;								
								if($current_index>=0) {
									if($pervious_winner_name!='' && $pervious_winner_name!=$winner_name) {
                                                                                $temp_score = ($pervious_winner_score > 0) ? ($pervious_winner_score - $winning_score) : $winning_score;
                                                                                if($temp_score>0) {
                                                                                        $winner_name = $pervious_winner_name;
                                                                                        if($pervious_winner_name == 'TEAM A' && $temp_score >= 1) {
                                                                                                $hole_winner_class = ($pervious_winner_score > 0) ? $color_team_a : $color_team_b;
                                                                                                $current_winner_class = ($pervious_winner_score > 0) ? $color_bg_a : $color_bg_b;
                                                                                        }
                                                                                        else {
                                                                                                if($temp_score > 1) {
                                                                                                        $hole_winner_class = $color_team_a;
                                                                                                        $current_winner_class = $color_bg_a;
                                                                                                }
                                                                                                else {
                                                                                                        $hole_winner_class = $color_team_b;
                                                                                                        $current_winner_class = $color_bg_b;
                                                                                                }
                                                                                        }
                                                                                        $winner_name = ($hole_winner_class == $color_team_a) ? 'TEAM A' : 'TEAM B';
                                                                                        $winning_score = $temp_score;
                                                                                }
                                                                                elseif($temp_score<=0) {

                                                                                        $winning_score = 0;
                                                                                }
										/*$temp_score = $pervious_winner_score - $winning_score;										
										if($temp_score>0) {
											$winner_name = $pervious_winner_name;
											$hole_winner_class = ($pervious_winner_name == 'TEAM A') ? $color_team_a : $color_team_b;
											$winning_score = $temp_score;
										}
										elseif($temp_score<=0) {											
											$winning_score = 0;
										}*/
									}
									else {
										$winning_score = $pervious_winner_score + $winning_score;
									}
								}								
								$final_result[$a] = array('winner'=>$winner_name,'hole_winner'=>$hole_winner_name,'current_winner_class'=>$current_winner_class,'winner_class'=>$hole_winner_class,'score'=>intval($winning_score));
							}
							elseif($team_b_sum == $team_a_sum) {
								// winner :: All Square
								//$winner_name = $hole_winner_name = 'AS';
								
								$winner_name = $hole_winner_name = ($pervious_winner_name=='AS') ? 'AS' : (($pervious_winner_score>=1) ? $pervious_winner_name : 'AS');
								$hole_winner_class = ($pervious_winner_name=='AS') ? $color_team_both : (($pervious_winner_score>=1) ? $pervious_winner_class : $color_team_both);
								$winning_score = ($pervious_winner_name=='AS') ? 0 : (($pervious_winner_score>=1) ? $pervious_winner_score : 0);
								$current_winner_class = ($pervious_winner_name=='AS') ? $color_team_both : (($pervious_winner_score>=1) ? $pervious_winner_class_bg : $color_bg_both);
								$final_result[$a] = array('winner'=>$winner_name,'hole_winner'=>$hole_winner_name,'current_winner_class'=>$current_winner_class,'winner_class'=>$hole_winner_class,'score'=>$winning_score);
							}//echo '<pre>'; print_r($final_result); echo '</pre>';die;
							//print_r($uniqueteam);
							//echo $a.' == > '.$scoreval.'<br/>';
						if($winner_name == 'AS'){
							$winner_team_id=0;
						}else{
							if($is_team_game){
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
							}else{
								$winner_team_id=($winner_name=="TEAM A")?$player_id[0]:$player_id[1];
							}
							if($winning_score == 'AS') {
								$winner_team_id=0;
							}
						}
						//$scoreval=($winner_name=='AS')?"HALVED":($winning_score>0 ? ($winning_score.'UP') : 'AS');
						//$bgcolor=($scoreval=="HALVED")?$this->getColorCode("black"):$hole_winner_class;
$scoreval=($winner_name=='AS')?"AS":($winning_score>0 ? ($winning_score.'UP') : 'AS');
$bgcolor=($scoreval=="AS")?$this->getColorCode("black"):$hole_winner_class;
$winner_team_id=($bgcolor==$this->getColorCode("black"))?0:$winner_team_id;
						$queryString = " insert into event_score_matchplay(event_score_calc_id,hole_number,event_id,winner,score_value,color) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner_team_id.",'".$scoreval."','".$bgcolor."')";
						$queryResult  = $this->db->FetchQuery($queryString);
							
							//if($winner_name!='AS') {
					$pervious_winner_name = $winner_name;
					$pervious_winner_class = $hole_winner_class;
					$pervious_winner_class_bg = $current_winner_class;
				//}
				$pervious_winner_score = $winning_score;
						}						
					}//die;
				}
			}
	}
	
	
		function updateautopressScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
			$counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;

			$queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0) {
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else {
				$queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
				$queryResult  = $this->db->FetchQuery($queryString);
				$event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
				$team_id=array();
				$sqlQuery1="SELECT p.team_id,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join event_score_calc c on c.event_id=p.event_id and c.player_id=p.player_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1' group by p.player_id order by c.is_admin desc,p.team_id asc";  
				$sqlresult1  = $this->db->FetchQuery($sqlQuery1);//print_r($sqlresult1);die;
				if(count($sqlresult1) >0){
					if(count($sqlresult1) ==2) {
						$sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_score_calc e left join event_player_list p ON p.event_id=e.event_id and p.player_id=e.player_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by e.is_admin desc";
						$sqlresult1  = $this->db->FetchQuery($sqlQuery1);  //print_r($sqlresult1);die;  
					}
					
					foreach($sqlresult1 as $i=>$e) {
						$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."' order by is_admin desc";
						
						$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
						
						for($j=$hole_start_from;$j<=($hole_start_from+17);$j++) {
							$i = ($j<=18) ? $j : ($j-18);
							$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
							$playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
						}
						$player_id[]=$e['player_id'];
						$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
						
						if($e['team_id']>0 && !in_array($e['team_id'],$team_id)) {
							$team_id[]=$e['team_id'];
						}
					}
				}
				
				$uniqueteam=array_unique($team_id);
				$game_type = (count($player_id)=="4")?'team':'';
				$is_team_game = ($game_type == 'team') ? true : false;
				
				if($is_team_game) {
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
					$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();			
					$score_b = isset($player_id[2]) ? $player[$player_id[2]] : array();
					$score_b2 = isset($player_id[3]) ? $player[$player_id[3]] : array();
					$grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
					$grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
					$grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
					$grossscore_b2 =  isset($player_id[3]) ? $playergross[$player_id[3]] : array();
				}
				else {
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
					$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
					$grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
					$grossscore_b = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
				}
				//print_r($player_id);
				//print_r($score_a);print_r($score_a2);
				//print_r($score_b);print_r($score_b2);
				//die;
				
				$zero_point = 0; $one_point = 1; $two_point = 2; $three_point = 3; $four_point = 4;	
				$start_value_first = $start_value_second = '0';	
				$color_team_a = $this->getColorCode("red");
				$color_team_b = $this->getColorCode("blue");
				$color_team_both = $this->getColorCode("black");
				$color_display_a =$this->getColorCode("red");
				$color_display_b = $this->getColorCode("blue");
				$color_display_both = $this->getColorCode("black");
				$resultstr = $finalstr = '';
				$result_arr = $final_result_arr = array();
				$scorebackto9A = $scorebackto9B = array();
				
				if(count($score_a)>0 && count($score_b)>0) {
					$queryString = " delete from event_score_autopress where event_id=".$eventId;
					$queryResult  = $this->db->FetchQuery($queryString);
					$add_new_zero = $remove_last_zero = false;
					$end_final_result = array();
					$jxn=1;
					
					foreach($score_a as $a=>$b) {
						$resultstr = $finalstr = '';
						$final_result_arr = $temp_result_arr = array();
						
						if($is_team_game) {
							
							if($grossscore_a[$a] <= 0 || $grossscore_a2[$a] <= 0 || $grossscore_b[$a] <= 0 || $grossscore_b2[$a] <= 0) {
								continue;
							}
							
							$a_val=($score_a[$a] <= $score_a2[$a]) ? $score_a[$a] : $score_a2[$a];
							$b_val=($score_b[$a] <= $score_b2[$a]) ? $score_b[$a] : $score_b2[$a];

							$grossa_val=$a_val;
							$grossb_val=$b_val;
						}
						else {
							
							if($grossscore_a[$a] <= 0 || $grossscore_b[$a] <= 0) {
								continue;
							}
							
							$a_val = $b;
							$b_val = isset($score_b[$a]) ? $score_b[$a] : 0;

							$grossa_val=$a_val;
							$grossb_val=$b_val;
						}	
						
						if($jxn > 9){
							$scorebackto9A[$a]=$a_val;
							$scorebackto9B[$a]=$b_val;				
						}
						
						$current_index = $jxn;
						$last_index = ($current_index==1) ? $current_index : ($current_index-1);
						
						
						if(is_numeric($grossa_val) && is_numeric($grossb_val)) {
							$bgclass = $winner_text = '';
							if($a_val < $b_val) {
								$bgclass = $color_team_a;
								$winner_text = "A";
								$winner_text_main = "A";
								$color_class = $color_display_a;
							}
							elseif($a_val > $b_val) {
								$bgclass = $color_team_b;
								$winner_text = 'B';
								$winner_text_main = 'B';
								$color_class = $color_display_b;
							}
							elseif($a_val == $b_val) {
								$bgclass = $color_team_both;
								$winner_text = 'AS';
								$winner_text_main = 'AS';
								$color_class = $color_display_both;
							}
							
							if($current_index == 1) {
								$score = ($winner_text!='AS') ? "1UP" : "0";
								$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
								$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>($score == '0' ? 'AS' : $score));
								$end_final_result = $final_result_arr;
							}
							elseif($current_index == 2) { 
								$exp = explode("_",$result_arr[1]['result']);
								$result_score = $exp[0];
								$result_winner = $exp[1];
								$result_winner_class = $color_display_both;
								
								if($result_winner == 'A') {
									$result_winner_class = $color_display_a;
								}
								elseif($result_winner == 'B') {
									$result_winner_class = $color_display_b;
								}
								
								if($winner_text == 'AS' && ($winner_text == $result_winner)) {
									$score = "0";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>'AS');
								}
								elseif($winner_text == 'AS' && ($winner_text != $result_winner)) {
									$score = "0";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$final_result_arr[$jxn][] = array('winner'=>$result_winner,'color'=>$result_winner_class,'score'=>"1UP");
								}
								elseif($winner_text != 'AS' && 'AS' == $result_winner) {
									$score = "1UP";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
								}
								elseif($winner_text != 'AS' && ($winner_text == $result_winner)) {
									$score = "2";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
									$final_result_arr[$jxn][] = array('winner'=>'NU','color'=>$color_display_both,'score'=>"0");
								}
								elseif($winner_text != 'AS' && ($winner_text != $result_winner)) {
									$score = "1";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									//$final_result_arr[$jxn][] = array('winner'=>$result_winner,'color'=>$result_winner_class,'score'=>"1");
									//$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
									$final_result_arr[$jxn][] = array('winner'=>'AS','color'=>$color_display_both,'score'=>'AS');
								}
								$end_final_result = $final_result_arr;
							}
							elseif($current_index > 2) {
								$rarr = $end_final_result[$last_index];
								$rarr_len = count($rarr);
								if(is_array($rarr) && count($rarr) > 0) {
									$tmp_arr = array();
									
									$score = ($winner_text == 'AS') ? "0" : "1";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$is_added = false;
									foreach($rarr as $m=>$n) {
										if($winner_text != 'AS' && $n['winner'] == 'AS') {
											$tmp_arr[] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>"1UP");
											$is_added = true;
											break;
										}
										else {
											$is_added = false;
											if($winner_text == 'AS') {
												$is_added = true;
												$ps = $n['score'];
												$pn = $n['winner'];
												$pc = $n['color'];
												$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
											}
											elseif($winner_text != 'AS' && $n['winner'] == $winner_text) {
												$ps = $n['score']+1;
												$pn = $n['winner'];
												$pc = $n['color'];
												$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
											}
											elseif($winner_text != 'AS' && $n['winner'] != $winner_text) {
												$ps = ($n['winner'] == 'NU') ? (($n['score']==0 && $rarr_len==1) ? '1UP' : '1') : (($n['score']==0) ? ($n['score']+1) : ($n['score']-1));
												$ps = ($ps <= 0) ? (($rarr_len==1) ? 'AS' : '0') : $ps;
												
												if($ps == 'AS') {
													$pn = 'AS';
													$pc = $color_display_both;
												}
												else {
													$pn = ($ps == '0') ? "NU" : (($n['score']==0) ? $winner_text_main : $n['winner']);
													$pc = ($ps == '0') ? $color_display_both : (($n['score']==0) ? $color_class : $n['color']);
												}
												$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
											}
										}
									}
									if($is_added === false) {
										$end = end($tmp_arr);
										if($end['score'] == '2') {
											$tmp_arr[] = array('winner'=>'NU','color'=>$color_display_both,'score'=>'0');
										}
									}
									$final_result_arr[$jxn] = $tmp_arr;
									$end_final_result = $final_result_arr;
								}
							}
							
							// save into database
							//print_r($end_final_result);
							if($winner_text_main=="A") {
								$winner=($is_team_game) ? $uniqueteam[0] : $player_id[0];
							}
							elseif($winner_text_main=="B"){
								$winner=($is_team_game) ? $uniqueteam[1] : $player_id[1];
							}
							else{
								$winner=0;
							}
							//print_r($end_final_result);//die;
							$avalues = array_values($end_final_result);
							$scoreval=json_encode($avalues[0]);
							$bgcolor=$color_class;						
							
							$queryString = " insert into event_score_autopress(event_score_calc_id,hole_number,event_id,winner,score_value,back_to_9_score,color) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner.",'".$scoreval."','[]','".$bgcolor."')"; 
							$queryResult  = $this->db->FetchQuery($queryString);		
						}
						$jxn++;
					}
					//die;
					// clculate back9
					$jxn = 1;
					if(count($scorebackto9A) > 0 && count($scorebackto9B) > 0) {
						foreach($scorebackto9A as $a=>$b) {
							$resultstr = $finalstr = '';
							$final_result_arr = $temp_result_arr = array();
							
							$grossa_val = $a_val = $b;
							$grossb_val = $b_val = isset($scorebackto9B[$a]) ? $scorebackto9B[$a] : 0;
							
							$current_index = $jxn;
							$last_index = ($current_index==1) ? $current_index : ($current_index-1);
							
							
							if(is_numeric($grossa_val) && is_numeric($grossb_val)) {
								$bgclass = $winner_text = '';
								if($a_val < $b_val) {
									$bgclass = $color_team_a;
									$winner_text = "A";
									$winner_text_main = "A";
									$color_class = $color_display_a;
								}
								elseif($a_val > $b_val) {
									$bgclass = $color_team_b;
									$winner_text = 'B';
									$winner_text_main = 'B';
									$color_class = $color_display_b;
								}
								elseif($a_val == $b_val) {
									$bgclass = $color_team_both;
									$winner_text = 'AS';
									$winner_text_main = 'AS';
									$color_class = $color_display_both;
								}
								
								if($current_index == 1) {
									$score = ($winner_text!='AS') ? "1UP" : "0";
									$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
									$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>($score == '0' ? 'AS' : $score));
									$end_final_result = $final_result_arr;
								}
								elseif($current_index == 2) {
									$exp = explode("_",$result_arr[1]['result']);
									$result_score = $exp[0];
									$result_winner = $exp[1];
									$result_winner_class = $color_display_both;
									
									if($result_winner == 'A') {
										$result_winner_class = $color_display_a;
									}
									elseif($result_winner == 'B') {
										$result_winner_class = $color_display_b;
									}
									
									if($winner_text == 'AS' && ($winner_text == $result_winner)) {
										$score = "0";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>'AS');
									}
									elseif($winner_text == 'AS' && ($winner_text != $result_winner)) {
										$score = "0";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										$final_result_arr[$jxn][] = array('winner'=>$result_winner,'color'=>$result_winner_class,'score'=>"1UP");
									}
									elseif($winner_text != 'AS' && 'AS' == $result_winner) {
										$score = "1UP";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
									}
									elseif($winner_text != 'AS' && ($winner_text == $result_winner)) {
										$score = "2";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
										$final_result_arr[$jxn][] = array('winner'=>'NU','color'=>$color_display_both,'score'=>"0");
									}
									elseif($winner_text != 'AS' && ($winner_text != $result_winner)) {
										$score = "1";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										//$final_result_arr[$jxn][] = array('winner'=>$result_winner,'color'=>$result_winner_class,'score'=>"1");
										//$final_result_arr[$jxn][] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>$score);
										$final_result_arr[$jxn][] = array('winner'=>'AS','color'=>$color_display_both,'score'=>'AS');
									}
									$end_final_result = $final_result_arr;
								}
								elseif($current_index > 2) {
									$rarr = $end_final_result[$last_index];
									$rarr_len = count($rarr);
									if(is_array($rarr) && count($rarr) > 0) {
										$tmp_arr = array();
										
										$score = ($winner_text == 'AS') ? "0" : "1";
										$result_arr[$jxn] = array('result'=>$score.'_'.$winner_text,'a_score'=>$a_val,'b_score'=>$b_val);
										$is_added = false;
										foreach($rarr as $m=>$n) {
											if($winner_text != 'AS' && $n['winner'] == 'AS') {
												$tmp_arr[] = array('winner'=>$winner_text_main,'color'=>$color_class,'score'=>"1UP");
												$is_added = true;
												break;
											}
											else {
												$is_added = false;
												if($winner_text == 'AS') {
													$is_added = true;
													$ps = $n['score'];
													$pn = $n['winner'];
													$pc = $n['color'];
													$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
												}
												elseif($winner_text != 'AS' && $n['winner'] == $winner_text) {
													$ps = $n['score']+1;
													$pn = $n['winner'];
													$pc = $n['color'];
													$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
												}
												elseif($winner_text != 'AS' && $n['winner'] != $winner_text) {
													$ps = ($n['winner'] == 'NU') ? (($n['score']==0 && $rarr_len==1) ? '1UP' : '1') : (($n['score']==0) ? ($n['score']+1) : ($n['score']-1));
													$ps = ($ps <= 0) ? (($rarr_len==1) ? 'AS' : '0') : $ps;
													
													if($ps == 'AS') {
														$pn = 'AS';
														$pc = $color_display_both;
													}
													else {
														$pn = ($ps == '0') ? "NU" : (($n['score']==0) ? $winner_text_main : $n['winner']);
														$pc = ($ps == '0') ? $color_display_both : (($n['score']==0) ? $color_class : $n['color']);
													}
													$tmp_arr[] = array('winner'=>$pn,'color'=>$pc,'score'=>$ps);
												}
											}
										}
										if($is_added === false) {
											$end = end($tmp_arr);
											if($end['score'] == '2') {
												$tmp_arr[] = array('winner'=>'NU','color'=>$color_display_both,'score'=>'0');
											}
										}
										$final_result_arr[$jxn] = $tmp_arr;
										$end_final_result = $final_result_arr;
									}
								}
								
								// save into database
								//print_r($end_final_result);
								if($winner_text_main=="A") {
									$winner=($is_team_game) ? $uniqueteam[0] : $player_id[0];
								}
								elseif($winner_text_main=="B"){
									$winner=($is_team_game) ? $uniqueteam[1] : $player_id[1];
								}
								else{
									$winner=0;
								}
								$avalues = array_values($end_final_result);
								$scoreval=json_encode($avalues[0]);
								$bgcolor=$color_class;						
								
								$queryString = " update event_score_autopress set back_to_9_score='".$scoreval."' where hole_number='".$a."' and event_id='".$eventId."'";
								$queryResult  = $this->db->FetchQuery($queryString);		
							}
							$jxn++;
						}
					}
				}
			}
			//die;
		}
		
		function updateautopressScoreold($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
		$counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
		    
		     $queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else
			{
			   $queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			   $event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
				 $team_id=array();
				echo $sqlQuery1="SELECT p.team_id,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1'";  
			$sqlresult1  = $this->db->FetchQuery($sqlQuery1);//print_r($sqlresult1);die;
			if(count($sqlresult1) >0){
                            if(count($sqlresult1) ==2){
                                            $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_score_calc e left join event_player_list p ON p.event_id=e.event_id and p.player_id=e.player_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by e.is_admin desc";  
					    $sqlresult1  = $this->db->FetchQuery($sqlQuery1);  //print_r($sqlresult1);die;  
                                            }
				foreach($sqlresult1 as $i=>$e){
					/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
				        $sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."' order by is_admin desc";
					$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
					for($j=$hole_start_from;$j<=($hole_start_from+17);$j++){
								$i = ($j<=18) ? $j : ($j-18);
						$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
                                                $playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
					}
					$player_id[]=$e['player_id'];
					$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
					if(!in_array($e['team_id'],$team_id)){
							$team_id[]=$e['team_id'];
							}
					//$player[$e['player_id']] = 
				}
			}
				$uniqueteam=array_unique($team_id);
					//print_r($player);die;
				$game_type = (count($player_id)=="4")?'team':'';				
				$is_team_game = ($game_type == 'team') ? true : false;	
				if($is_team_game){
			$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
			$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();			
			$score_b = isset($player_id[2]) ? $player[$player_id[2]] : array();
			$score_b2 = isset($player_id[3]) ? $player[$player_id[3]] : array();
                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
                        $grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
                        $grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
                        $grossscore_b2 =  isset($player_id[3]) ? $playergross[$player_id[3]] : array();
				}else{
				$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
			$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
                        $grossscore_b = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
				}
			//print_r($score_a);//print_r($score_a2);
			//print_r($score_b);//print_r($score_b2);
			//die;
			$zero_point = 0;$one_point = 1;$two_point = 2;$three_point = 3;$four_point = 4;	
			$start_value_first = $start_value_second = '0';	
			$color_team_a = $this->getColorCode("red");$color_team_b = $this->getColorCode("blue");$color_team_c = $this->getColorCode("green");
			$color_team_both = $this->getColorCode("black");$color_display_a =$this->getColorCode("red"); $color_display_b = $this->getColorCode("blue");	$color_display_c = $this->getColorCode("green");
			$color_display_both = $this->getColorCode("black");$resultstr = $finalstr = '';$result_arr = $final_result_arr = array();$scorebackto9A=$scorebackto9B=array();
			if(count($score_a)>0 && count($score_b)>0) {
				$queryString = " delete from event_score_autopress where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$add_new_zero = $remove_last_zero = false;
				$end_final_result = array();
				$jxn=1;
				foreach($score_a as $a=>$b) {
					$resultstr = $finalstr = '';
					$final_result_arr = $temp_result_arr = array();
					//$a_val = $b;
					//$b_val = isset($score_b[$a]) ? $score_b[$a] : 0;			
					if($is_team_game){
						$a_val=($score_a[$a]+$score_a2[$a]);
						$b_val=($score_b[$a]+$score_b2[$a]);
                                                
                                                $grossa_val=($grossscore_a[$a]+$grossscore_a2[$a]);
						$grossb_val=($grossscore_b[$a]+$grossscore_b2[$a]);
					}else{
						$a_val = $b;
						$b_val = isset($score_b[$a]) ? $score_b[$a] : 0;
                                                
                                                $grossa_val=$grossscore_a[$a];
						$grossb_val=$grossscore_b[$a];
					}	
					if($jxn > 9){
			                    $scorebackto9A[$a]=$a_val;
                                            $scorebackto9B[$a]=$b_val;				
			                }
					$last_index = ($jxn==1) ? 0 : ($jxn-2);
					$current_index = ($jxn==1) ? 0 : ($jxn-1); $jxn++;
					if($grossa_val>0 && $grossb_val>0){
						$bgclass = $winner_text = '';
						if($a_val<$b_val) {
							$bgclass = $color_team_a;
							$winner_text = "A";
							$winner_text_main = "A";
							$color_class = $color_display_a;
						}
						elseif($a_val>$b_val) {
							$bgclass = $color_team_b;
							$winner_text = 'B';
							$winner_text_main = 'B';
							$color_class = $color_display_b;
						}
						elseif($a_val==$b_val) {
							$bgclass = $color_team_both;
							$winner_text = 'AS';
							$winner_text_main = 'AS';
							$color_class = $color_display_both;
						}
						if($current_index == 0) {
					$score = ($winner_text!='AS') ? "1UP" : "AS";
					$result_arr = array(($score=='AS' ? 0 : $score).'_'.$winner_text);
					$final_result_arr[] = array('color'=>$color_class,'score'=>$score);//"<b class='{$color_class}'>{$score}</b>";
				}
				elseif($current_index >= 1) { //print_r($result_arr);die;
					foreach($result_arr as $res_key=>$res_val) {
						$exp = explode('_',$res_val);
						$result_winner_text = (isset($exp[1]) && trim($exp[1])!='') ? strtoupper($exp[1]) : $winner_text_main;
						$result_winner_score = $exp[0];
						
						if(count($result_arr) > 1 && count($temp_result_arr)>0 && $res_key == count($result_arr)-1) {
							$end_arr = end($temp_result_arr);
							$exp22 = explode('_',$end_arr);
							$ewtext = strtoupper($exp22[1]);
							$ewscore22 = $exp22[0];
							if($ewscore22 <= '0' && $result_winner_score!=$winner_text_main) {
								continue;
							}
						}
						if($current_index == 1 && $result_winner_text != $winner_text_main) {
							$new_score = '1UP';
							$final_result_arr[]=array('color'=>$bgclass,'score'=>$new_score);// array("<b class='colorblack'>{$new_score}</b>");
							//$temp_result_arr = array();
							$temp_result_arr[] = $new_score.'_'.$winner_text_main;
							$temp_result_arr[] = '-1_'.$winner_text_main;
							$end_final_result = $final_result_arr;
							break;
						}
						elseif($current_index == 1 && $result_winner_text == $winner_text_main) {
							$new_score = 'AS';
							$final_result_arr[]=array('color'=>$this->getColorCode('black'),'score'=>$new_score);// array("<b class='colorblack'>{$new_score}</b>");
							//$temp_result_arr = array();
							$temp_result_arr[] = $new_score.'_'.$winner_text_main;
							$temp_result_arr[] = '-1_'.$winner_text_main;
							$end_final_result = $final_result_arr;
							break;
						}
						else {
							// add-subtract values according to winner
							if($winner_text_main == 'A') {
								if($result_winner_score != '1UP' && $result_winner_score != 'AS') {
									if(intval($result_winner_score)==0) {
										$new_score = 1;
										$ncolor = "red";
										$winner_text_display = $winner_text_main;
									}
									else {
										if($result_winner_text == $winner_text_main) {
											$new_score = (intval($result_winner_score)+1);
											$ncolor = "red";
											$winner_text_display = $winner_text_main;
										}
										else {
											$new_score = (intval($result_winner_score) == 0) ? 1 : (intval($result_winner_score)-1);
											$ncolor = (intval($result_winner_score) > 0) ? "blue" : "black";
											$winner_text_display = ($result_winner_score >= 1) ? $result_winner_text : $winner_text_main;
										}
									}
									
									$new_score = ($new_score <= 0) ? 0 : $new_score;
									$cl = ($new_score == 0) ? "black" : $ncolor;
									
									$final_result_arr[] = array('color'=>$this->getColorCode($cl),'score'=>$new_score);//"<b class='{$cl}'>{$new_score}</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text_display;
								}
								else {
									$new_score = 2;
									$final_result_arr[] = array('color'=>$this->getColorCode('red'),'score'=>$new_score);//"<b class='colorred'>{$new_score}</b>";
									$final_result_arr[] = array('color'=>$this->getColorCode('black'),'score'=>0);//"<b class='colorblack'>0</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text;
									$temp_result_arr[] = '0_'.$winner_text_main;
								}
								$end_final_result = $final_result_arr;
							}
							elseif($winner_text_main == 'B') {
								if($result_winner_score != '1UP' && $result_winner_score != 'AS') {
									if(intval($result_winner_score)==0) {
										$new_score = 1;
										$ncolor = "blue";
										$winner_text_display = $winner_text_main;
									}
									else {
										if($result_winner_text == $winner_text_main) {
											$new_score = (intval($result_winner_score)+1);
											$ncolor = "blue";
											$winner_text_display = $winner_text_main;
										}
										else {
											$new_score = (intval($result_winner_score) == 0) ? 1 : (intval($result_winner_score)-1);
											$ncolor = (intval($result_winner_score) > 0) ? "red" : "black";
											$winner_text_display = ($new_score > 0) ? $result_winner_text : $winner_text_main;
										}
									}
									
									$new_score = ($new_score <= 0) ? 0 : $new_score;
									$cl = ($new_score == 0) ? "black" : $ncolor;
									
									$final_result_arr[] =array('color'=>$this->getColorCode($cl),'score'=>$new_score);// "<b class='{$cl}'>{$new_score}</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text_display;
								}
								else {
									$new_score = 2;
									$final_result_arr[] = array('color'=>$this->getColorCode('blue'),'score'=>$new_score);//"<b class='colorblue'>{$new_score}</b>";
									$final_result_arr[] = array('color'=>$this->getColorCode('black'),'score'=>0);//"<b class='colorblack'>0</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text;
									$temp_result_arr[] = '0_'.$winner_text_main;
								}
								$end_final_result = $final_result_arr;
							}
							elseif($winner_text_main == 'AS') {
								$temp_result_arr[] = $res_val;
								$final_result_arr = $end_final_result;
							}
						}
					}
					
					if($winner_text_main !='AS' && count($temp_result_arr)>0) {
						$end_temp_result_arr = end($temp_result_arr);
						$exp = explode('_',$end_temp_result_arr);
						$ewtext = strtoupper($exp[1]);
						$ewscore = $exp[0];
						if($ewscore == '1') {
							$temp_result_arr[] = '-1_'.$winner_text_main;
						}
					}
					$result_arr = $temp_result_arr;
				}
						
						if($winner_text_main=="A"){
							$winner=($is_team_game)?$uniqueteam[0]:$player_id[0];
						}elseif($winner_text_main=="B"){
							$winner=($is_team_game)?$uniqueteam[1]:$player_id[1];
						}else{
							$winner=0;
						}
						//print_r($final_result_arr);
						$scoreval=json_encode($final_result_arr);//implode(' ',$final_result_arr);
						$bgcolor=$bgclass;						
						//echo "<br>".$scoreval; die;
						$colname = ($a>9) ? "score_value" : "score_value";
						$colname2 = ($a>9) ? "score_value" : "back_to_9_score";
						$queryString = " insert into event_score_autopress(event_score_calc_id,hole_number,event_id,winner,".$colname.",color) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner.",'".$scoreval."','".$bgcolor."')"; 
						$queryResult  = $this->db->FetchQuery($queryString);						
					}
				}
		    }
			
			if(count($scorebackto9A)>0 && count($scorebackto9B)>0) {
				$add_new_zero = $remove_last_zero = false;
				$end_final_result = array();
				$jxn=1;
				foreach($scorebackto9A as $a=>$b) {
					$resultstr = $finalstr = '';
					$final_result_arr = $temp_result_arr = array();
					$a_val = $scorebackto9A[$a];
			        $b_val = $scorebackto9B[$a];
			        $last_index = ($jxn==1) ? 0 : ($jxn-2);
					$current_index = ($jxn==1) ? 0 : ($jxn-1); $jxn++;
					if($a_val!='' && $b_val!=''){
						$bgclass = $winner_text = '';
						if($a_val<$b_val) {
							$bgclass = $color_team_a;$color_class = $color_display_a;
							$winner_text = "A";$winner_text_main = "A";							
						}elseif($a_val>$b_val) {
							$bgclass = $color_team_b;
							$winner_text = 'B';	$winner_text_main = 'B';
							$color_class = $color_display_b;
						}elseif($a_val==$b_val) {
							$bgclass = $color_team_both;
							$winner_text = 'AS';$winner_text_main = 'AS';
							$color_class = $color_display_both;
						}
						if($current_index == 0) {
					$score = ($winner_text!='AS') ? "1UP" : "0";
					$result_arr = array($score.'_'.$winner_text);
					$final_result_arr[] = array('color'=>$color_class,'score'=>$score);//"<b class='{$color_class}'>{$score}</b>";
				}
				elseif($current_index >= 1) {
					foreach($result_arr as $res_key=>$res_val) {
						$exp = explode('_',$res_val);
						$result_winner_text = (isset($exp[1]) && trim($exp[1])!='') ? strtoupper($exp[1]) : $winner_text_main;
						$result_winner_score = $exp[0];
						if(count($result_arr) > 1 && count($temp_result_arr)>0 && $res_key == count($result_arr)-1) {
							$end_arr = end($temp_result_arr);
							$exp22 = explode('_',$end_arr);
							$ewtext = strtoupper($exp22[1]);
							$ewscore22 = $exp22[0];
							if($ewscore22 <= '0' && $result_winner_score!=$winner_text_main) {
								continue;
							}
						}
						if($current_index == 1 && $result_winner_text != $winner_text_main) {
							$new_score = '1UP';
							$final_result_arr[]=array('color'=>$bgclass,'score'=>$new_score);// array("<b class='colorblack'>{$new_score}</b>");
							//$temp_result_arr = array();
							$temp_result_arr[] = $new_score.'_'.$winner_text_main;
							$temp_result_arr[] = '-1_'.$winner_text_main;
							$end_final_result = $final_result_arr;
							break;
						}
						elseif($current_index == 1 && $result_winner_text == $winner_text_main) {
							$new_score = 'AS';
							$final_result_arr[]=array('color'=>$this->getColorCode('black'),'score'=>$new_score);// array("<b class='colorblack'>{$new_score}</b>");
							//$temp_result_arr = array();
							$temp_result_arr[] = $new_score.'_'.$winner_text_main;
							$temp_result_arr[] = '-1_'.$winner_text_main;
							$end_final_result = $final_result_arr;
							break;
						}
						else {
							// add-subtract values according to winner
							if($winner_text_main == 'A') {
								if($result_winner_score != '1UP' && $result_winner_score != 'AS') {
									if(intval($result_winner_score)==0) {
										$new_score = 1;
										$ncolor = "red";
										$winner_text_display = $winner_text_main;
									}
									else {
										if($result_winner_text == $winner_text_main) {
											$new_score = (intval($result_winner_score)+1);
											$ncolor = "red";
											$winner_text_display = $winner_text_main;
										}
										else {
											$new_score = (intval($result_winner_score) == 0) ? 1 : (intval($result_winner_score)-1);
											$ncolor = (intval($result_winner_score) > 0) ? "blue" : "black";
											$winner_text_display = ($result_winner_score >= 1) ? $result_winner_text : $winner_text_main;
										}
									}									
									$new_score = ($new_score <= 0) ? 0 : $new_score;
									$cl = ($new_score == 0) ? "black" : $ncolor;									
									$final_result_arr[] = array('color'=>$this->getColorCode($cl),'score'=>$new_score);//"<b class='{$cl}'>{$new_score}</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text_display;
								}else {
									$new_score = 2;
									$final_result_arr[] = array('color'=>$this->getColorCode('red'),'score'=>$new_score);//"<b class='colorred'>{$new_score}</b>";
									$final_result_arr[] = array('color'=>$this->getColorCode('black'),'score'=>0);//"<b class='colorblack'>0</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text;
									$temp_result_arr[] = '0_'.$winner_text_main;
								}
								$end_final_result = $final_result_arr;
							}
							elseif($winner_text_main == 'B') {
								if($result_winner_score != '1UP' && $result_winner_score != 'AS') {
									if(intval($result_winner_score)==0) {
										$new_score = 1;
										$ncolor = "blue";
										$winner_text_display = $winner_text_main;
									}
									else {
										if($result_winner_text == $winner_text_main) {
											$new_score = (intval($result_winner_score)+1);
											$ncolor = "blue";
											$winner_text_display = $winner_text_main;
										}
										else {
											$new_score = (intval($result_winner_score) == 0) ? 1 : (intval($result_winner_score)-1);
											$ncolor = (intval($result_winner_score) > 0) ? "red" : "black";
											$winner_text_display = ($new_score > 0) ? $result_winner_text : $winner_text_main;
										}
									}
									$new_score = ($new_score <= 0) ? 0 : $new_score;
									$cl = ($new_score == 0) ? "black" : $ncolor;
									$final_result_arr[] =array('color'=>$this->getColorCode($cl),'score'=>$new_score);// "<b class='{$cl}'>{$new_score}</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text_display;
								}
								else {
									$new_score = 2;
									$final_result_arr[] = array('color'=>$this->getColorCode('blue'),'score'=>$new_score);//"<b class='colorblue'>{$new_score}</b>";
									$final_result_arr[] = array('color'=>$this->getColorCode('black'),'score'=>0);//"<b class='colorblack'>0</b>";
									$temp_result_arr[] = $new_score.'_'.$winner_text;
									$temp_result_arr[] = '0_'.$winner_text_main;
								}
								$end_final_result = $final_result_arr;
							}
							elseif($winner_text_main == 'AS') {
								$temp_result_arr[] = $res_val;
								$final_result_arr = $end_final_result;
							}
						}
					}
					
					if($winner_text_main !='AS' && count($temp_result_arr)>0) {
						$end_temp_result_arr = end($temp_result_arr);
						$exp = explode('_',$end_temp_result_arr);
						$ewtext = strtoupper($exp[1]);
						$ewscore = $exp[0];
						if($ewscore == '1') {
							$temp_result_arr[] = '-1_'.$winner_text_main;
						}
					}
					$result_arr = $temp_result_arr;
				}						
						if($winner_text_main=="A"){
							$winner=($is_team_game)?$uniqueteam[0]:$player_id[0];
						}elseif($winner_text_main=="B"){
							$winner=($is_team_game)?$uniqueteam[1]:$player_id[1];
						}else{
							$winner=0;
						}
						$scoreval=json_encode($final_result_arr);//implode(' ',$final_result_arr);
						$bgcolor=$bgclass;
						$queryString = " update event_score_autopress set back_to_9_score='".$scoreval."' where hole_number='".$a."' and event_id='".$eventId."'";
						$queryResult  = $this->db->FetchQuery($queryString);						
					}
				}
		    }

		}
	}	
	
	
	function update21Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
		$counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
			
		    $queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else
			{
			   $queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			   $event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
			 	    $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1' order by t.team_profile_id asc";  
					 $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
					 $team_id=array();
					if(count($sqlresult1) >0){
						foreach($sqlresult1 as $i=>$e){
							/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($j=$hole_start_from;$j<=($hole_start_from+17);$j++){
								$i = ($j<=18) ? $j : ($j-18);
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
                                                                $playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
							}
							$player_id[]=$e['player_id'];
							$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
							if(!in_array($e['team_id'],$team_id)){
							$team_id[]=$e['team_id'];
							}
						}
					}
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
					$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();
					$score_b =  isset($player_id[2]) ? $player[$player_id[2]] : array();
					$score_b2 =  isset($player_id[3]) ? $player[$player_id[3]] : array();
                                        
                                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
					$grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
					$grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
					$grossscore_b2 =  isset($player_id[3]) ? $playergross[$player_id[3]] : array();
                                        
					$uniqueteam=array_unique($team_id);
					$start_value_first = $start_value_second = '0';	
					$color_team_a = "bgred";
					$color_team_b = "bgblue";
					$color_team_both = "bgblack";					
					$color_display_a = "colorred";
					$color_display_b = "colorblue";
					$color_display_both = "";					
					$resultstr = $finalstr = '';
					$result_arr = $final_result_arr = array();
					if(count($score_a)>0 && count($score_a2)>0 && count($score_b)>0 && count($score_b2)>0) {
						$queryString = " delete from event_score_2_1 where event_id=".$eventId;
						$queryResult  = $this->db->FetchQuery($queryString);
						$final_result = array();
						$pervious_winner_score = 0;
						$pervious_winner_name = '';
						foreach($score_a as $a=>$b) {
							$winner_name = $bgclass_two = $bgclass_one = '';
							$team_a_min = $team_a_max = $team_b_min = $team_b_max = $winning_score = 0;
							$winning_score = $pervious_winner_score;
							$two_point = $one_point = 0;
							$two_point_winner = $one_point_winner = '';
							$a1_val = $b;
							$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
							$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
							$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;
                                                        
                                                        $grossa1_val=isset($grossscore_a[$a]) ? $grossscore_a[$a] : 0;
                                                        $grossa2_val=isset($grossscore_a2[$a]) ? $grossscore_a2[$a] : 0;  
                                                        $grossb1_val=isset($grossscore_b[$a]) ? $grossscore_b[$a] : 0; 
                                                        $grossb2_val=isset($grossscore_b2[$a]) ? $grossscore_b2[$a] : 0;       
							if($grossa1_val>0 && $grossa2_val>0 && $grossb1_val>0 && $grossb2_val>0){							
								$last_index = ($a==1) ? 0 : ($a-2);
								$current_index = ($a==1) ? 0 : ($a-1);								
								$team_a_min = ($a2_val<$a1_val) ? intval($a2_val) : intval($a1_val);
								$team_a_max = ($a2_val>$a1_val) ? intval($a2_val) : intval($a1_val);
								$team_b_min = ($b2_val<$b1_val) ? intval($b2_val) : intval($b1_val);
								$team_b_max = ($b2_val>$b1_val) ? intval($b2_val) : intval($b1_val);
								//echo 'Hole '.$a.' :: a min : '.$team_a_min.' | a max : '.$team_a_max.' || b min : '.$team_b_min.' | b max : '.$team_b_max.'<br/>';
								// calculate two point
								if($team_a_min < $team_b_min) {
									$two_point_winner = $winner_name = 'TEAM A';
									$bgclass_two = $color_team_a;
									if($pervious_winner_name!='' && $pervious_winner_name!='AS' && $pervious_winner_name != $two_point_winner) {
										$winning_scoreorg = (($pervious_winner_score - 2));
										$winning_score = intval(abs($pervious_winner_score - 2));
										$winner_name = ($winning_scoreorg > 0) ? $pervious_winner_name : $two_point_winner;
										//$winner_name = ($winning_score==0) ? 'AS' : $winner_name;
										$bgclass_two = ($winning_scoreorg > 0) ? $color_team_b : $color_team_a;
										$bgclass_two = ($winning_score == 0) ? $color_team_both : $bgclass_two;
										
									}
									else {
										$winning_score = intval(abs($pervious_winner_score + 2));
									}
								}
								elseif($team_b_min < $team_a_min) {
									$two_point_winner = $winner_name = 'TEAM B';
									$bgclass_two = $color_team_b;
									if($pervious_winner_name!='' && $pervious_winner_name!='AS' && $pervious_winner_name != $two_point_winner) {
										$winning_scoreorg = (($pervious_winner_score - 2));
										$winning_score = intval(abs($pervious_winner_score - 2));
										$winner_name = ($winning_scoreorg > 0) ? $pervious_winner_name : $two_point_winner;
										//$winner_name = ($winning_score==0) ? 'AS' : $winner_name;
										$bgclass_two = ($winning_scoreorg > 0) ? $color_team_b : $color_team_a;
										$bgclass_two = ($winning_score == 0) ? $color_team_both : $bgclass_two;
									}
									else {
										$winning_score = intval(abs($pervious_winner_score + 2));
									}
								}
								else {
									$two_point_winner = '-';
									$bgclass_two = $color_team_both;
									$winner_name = $pervious_winner_name;
									$winning_score = intval($pervious_winner_score);
								}
								$pervious_winner_name = $winner_name;
								$pervious_winner_class_bg = $bgclass_two;
								// calculate one point
								if($team_a_max < $team_b_max) {
									$one_point_winner = 'TEAM A';
									$bgclass_one = $color_team_a;
									if($pervious_winner_name!='' && $pervious_winner_name!='AS' && $pervious_winner_name != $one_point_winner) {
										$t_score = intval(($winning_score - 1));
										$winning_score = intval(abs($winning_score - 1));
										$winner_name = ($t_score > 0) ? $pervious_winner_name : $one_point_winner;
										//$winner_name = ($winning_score==0) ? 'AS' : $winner_name;
										$bgclass_one = ($t_score > 0) ? $pervious_winner_class_bg : $bgclass_one;
										$bgclass_one = ($winning_score == 0) ? $color_team_both : $bgclass_one;
									}
									else { 
										$winning_score = intval(abs($winning_score + 1));
										$winner_name = $one_point_winner;
									}
								}
								elseif($team_b_max < $team_a_max) {
									$one_point_winner = $winner_name = 'TEAM B';
									$bgclass_one = $color_team_b;
									if($pervious_winner_name!='' && $pervious_winner_name!='AS' && $pervious_winner_name != $one_point_winner) { 
										$t_score = intval(($winning_score - 1));
										$winning_score = intval(abs($winning_score - 1));
										$winner_name = ($t_score > 0) ? $pervious_winner_name : $one_point_winner;
										//$winner_name = ($winning_score==0) ? 'AS' : $winner_name;
										$bgclass_one = ($t_score > 0) ? $pervious_winner_class_bg : $bgclass_one;
										$bgclass_one = ($winning_score == 0) ? $color_team_both : $bgclass_one;
									}
									else {
										$winning_score = intval(abs($winning_score + 1));
										$winner_name = $one_point_winner;
									}
								}
								else {
									$one_point_winner = '-';
									$bgclass_one = $color_team_both;
									//$winner_name = ($current_index>0) ? $pervious_winner_name : $winner_name;
									$winner_name = $pervious_winner_name;
									$winning_score = intval($winning_score);
								}
								if($two_point_winner == $one_point_winner) {
									//$winner_name = 'AS';
									//$winning_score = $pervious_winner_score;
								}
								$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
								$color_code = '';
								//print_r($final_result);
								if($winner_name == 'AS' || $winner_name == '' || $winning_score == 0){
									$winner_team_id=0;
									$color_code = $this->getColorCode('black'); 
								}else{
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
								$color_code = ($winner_name=="TEAM A")?$this->getColorCode('red'):$this->getColorCode('blue'); 
								}
								if($two_point_winner=="TEAM A"){
								  $two_point_team_winner=$uniqueteam[0];	
								}elseif($two_point_winner=="TEAM B"){
								  $two_point_team_winner=$uniqueteam[1];	
								}else{
								   $two_point_team_winner=0;	
								}
								if($one_point_winner=="TEAM A"){
								  $one_point_team_winner=$uniqueteam[0];	
								}elseif($one_point_winner=="TEAM B"){
								  $one_point_team_winner=$uniqueteam[1];	
								}else{
								   $one_point_team_winner=0;	
								}								
								 $queryString = " insert into event_score_2_1(event_score_calc_id,hole_number,event_id,2_point,1_point,winner,score_value,color_code) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$two_point_team_winner.",".$one_point_team_winner.",".$winner_team_id.",".$final_result[$a]['score'].",'".$color_code."')";
								 //echo $queryString.'<br/>';
								$queryResult  = $this->db->FetchQuery($queryString);
								
								//if($winner_name!='AS') {
									$pervious_winner_name = $winner_name;
								//}
								$pervious_winner_score = $winning_score;
							}							
						}//die;
					}		
	    }	
	}
	
	function updateVegasScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
		$counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
			
		    
		    $queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else
			{
			   $queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			   $event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
			 	    $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1' order by t.team_profile_id asc";  
					 $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
					 $team_id=array();
					if(count($sqlresult1) >0){
						foreach($sqlresult1 as $i=>$e){
							/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($j=$hole_start_from;$j<=($hole_start_from+17);$j++){
								$i = ($j<=18) ? $j : ($j-18);
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
                                                                $playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
							}
							$player_id[]=$e['player_id'];
							$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
							if(!in_array($e['team_id'],$team_id)){
							$team_id[]=$e['team_id'];
							}
						}
					}
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
					$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();
					$score_b =  isset($player_id[2]) ? $player[$player_id[2]] : array();
					$score_b2 =  isset($player_id[3]) ? $player[$player_id[3]] : array();	
                                        
                                        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
					$grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
					$grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
					$grossscore_b2 =  isset($player_id[3]) ? $playergross[$player_id[3]] : array();
                                        
					$uniqueteam=array_unique($team_id);
					$start_value_first = $start_value_second = '0';
					$color_team_a = "bgred";
					$color_team_b = "bgblue";
					$color_team_both = "bgblack";
					$color_display_a = "colorred";
					$color_display_b = "colorblue";
					$color_display_both = "";
					$resultstr = $finalstr = '';
					$result_arr = $final_result_arr = array();
					if(count($score_a)>0 && count($score_a2)>0 && count($score_b)>0 && count($score_b2)>0) {
						$queryString = " delete from event_score_vegas where event_id=".$eventId;
						$queryResult  = $this->db->FetchQuery($queryString);
						$final_result = array();
						$curr_hole_score = $pervious_winner_score = 0;
						$curr_hole_winner = $pervious_winner_name = '';
						foreach($score_a as $a=>$b) {
							$winner_name = $bgclass = '';
							$team_a_sum = $team_b_sum = $winning_score = 0;
							$a1_val = $b;
							$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
							$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
							$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;
                                                        
                                                        $grossa1_val=isset($grossscore_a[$a]) ? $grossscore_a[$a] : 0;
                                                        $grossa2_val=isset($grossscore_a2[$a]) ? $grossscore_a2[$a] : 0;  
                                                        $grossb1_val=isset($grossscore_b[$a]) ? $grossscore_b[$a] : 0; 
                                                        $grossb2_val=isset($grossscore_b2[$a]) ? $grossscore_b2[$a] : 0;       
							if($grossa1_val>0 && $grossa2_val>0 && $grossb1_val>0 && $grossb2_val>0){							
								$last_index = ($a==1) ? 0 : ($a-2);
								$current_index = ($a==1) ? 0 : ($a-1);
								$team_a_sum = ($a2_val<$a1_val) ? intval($a2_val.$a1_val) : intval($a1_val.$a2_val);
								$team_b_sum = ($b2_val<$b1_val) ? intval($b2_val.$b1_val) : intval($b1_val.$b2_val);
								if($team_a_sum < $team_b_sum) {
									// winner :: TEAM A
									$curr_hole_winner = $winner_name = 'TEAM A';
									$bgclass = $color_team_a;
									$curr_hole_score = $winning_score = abs($team_a_sum - $team_b_sum);
									if($current_index>=0) {
										if($pervious_winner_name!=$winner_name) {
											$temp_score = $pervious_winner_score - $winning_score;
											if($temp_score<0) {
												$winner_name = 'TEAM A';
												$bgclass = $color_team_a;
												$winning_score = abs($temp_score);
											}
											if($temp_score>0) {
												$winner_name = $pervious_winner_name;
												$bgclass = ($pervious_winner_name == 'TEAM A') ? $color_team_a : $color_team_b;
												$winning_score = abs($temp_score);
											}
											elseif($temp_score==0) {
												$winner_name = 'AS';
												$bgclass = $color_team_both;
												$winning_score = 0;
												$pervious_winner_score = 0;
											}
										}
										else {
											$winning_score = $pervious_winner_score + $winning_score;
										}
									}
									$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
								}
								elseif($team_b_sum < $team_a_sum) {
									// winner :: TEAM B
									$curr_hole_winner = $winner_name = 'TEAM B';
									$bgclass = $color_team_b;
									$curr_hole_score = $winning_score = abs($team_a_sum - $team_b_sum);					
									if($current_index>=0) {
										if($pervious_winner_name!=$winner_name) {
											$temp_score = $pervious_winner_score - $winning_score;							
											if($temp_score<0) {
												$winner_name = 'TEAM B';
												$bgclass = $color_team_b;
												$winning_score = abs($temp_score);
											}
											if($temp_score>0) {
												$winner_name = $pervious_winner_name;
												$bgclass = ($pervious_winner_name == 'TEAM A') ? $color_team_a : $color_team_b;
												$winning_score = abs($temp_score);
											}
											elseif($temp_score==0) {
												$winner_name = 'AS';
												$bgclass = $color_team_both;
												$winning_score = 0;
												//$pervious_winner_score = 0;
											}
										}
										else {
											$winning_score = $pervious_winner_score + $winning_score;
										}
									}
									
									$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
								}
								elseif($team_b_sum == $team_a_sum) {
									// winner :: All Square
									$curr_hole_winner = $winner_name = 'AS';
									$bgclass = $color_team_both;
									$winning_score = $pervious_winner_score;
									$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
									$curr_hole_score = 0;
								}
								//if($winner_name == 'AS' || $winning_score == 0){
								if($winner_name == 'AS' && $winning_score == 0){
									$winner_team_id=0;
									$color_code = $this->getColorCode('black');
								}
								elseif($winner_name == 'AS' && $winning_score > 0){
									$winner_team_id=($pervious_winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
									$color_code = ($pervious_winner_name=="TEAM A")?$this->getColorCode('red'):$this->getColorCode('blue'); 
								}
								elseif($winning_score == 0){
									$winner_team_id=0;
									$color_code = $this->getColorCode('black');
								}else{
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
								$color_code = ($winner_name=="TEAM A")?$this->getColorCode('red'):$this->getColorCode('blue'); 
								}
								$db_hole_score = $db_hole_winner = 0;
								
								if($curr_hole_winner == 'AS') {
									$db_hole_score = $db_hole_winner = 0;
								}
								elseif($curr_hole_winner == 'TEAM A') {
									$db_hole_winner = $uniqueteam[0];
									$db_hole_score = $curr_hole_score;
								}
								elseif($curr_hole_winner == 'TEAM B') {
									$db_hole_winner = $uniqueteam[1];
									$db_hole_score = $curr_hole_score;
								}
								
								
								$queryString = " insert into event_score_vegas(event_score_calc_id,hole_number,event_id,winner,score_value,color_code,hole_score_value,hole_winner) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner_team_id.",".$winning_score.",'".$color_code."','".$db_hole_score."','".$db_hole_winner."')";
								$queryResult  = $this->db->FetchQuery($queryString);
								if($winner_name!='AS') {
									$pervious_winner_name = $winner_name;
								}
								$pervious_winner_score = $winning_score;
							}
							
						}
					}		
	    }	
	}
	
	function update420Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from){
		    $counter=($hole_start_from==10)?10:1;
			$total_hole_num=($hole_start_from==10)?18:$total_hole_num;
		    $queryString = "select event_score_calc_id";
			$queryString .= " from event_score_calc where event_score_calc.event_id = ".$eventId;
			$queryString .= " and event_score_calc.player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore>0)
			{
				$queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score."";
				$queryString .= " where event_score_calc.event_id = ".$eventId;
				$queryString .= " and event_score_calc.player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$getadminString1 = "select event_score_calc_id from event_score_calc where event_id='".$eventId."' and player_id ='".$playerId."'";
				$event_score_calc_id = $this->db->FetchSingleValue($getadminString1);
			}
			else
			{
			   $queryString = " insert into event_score_calc(event_id,player_id,format_id,hole_number,no_of_holes_played,score_entry_".$holeId.") values(".$eventId.",".$playerId.",".$stroke_play_id.",".$holeId.",".$holeId.",".$score.")";
			   $queryResult  = $this->db->FetchQuery($queryString);
			   $event_score_calc_id = $this->db->LastInsertId();
			}
			if($event_score_calc_id > 0){
				$sqlQuery1="SELECT p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1'";  
			$sqlresult1  = $this->db->FetchQuery($sqlQuery1);
			if(count($sqlresult1) >0){
				foreach($sqlresult1 as $i=>$e){
					/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
					for($j=$hole_start_from;$j<=($hole_start_from+17);$j++){
								$i = ($j<=18) ? $j : ($j-18);
						$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
                                                $playergross[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
					}
					$player_id[]=$e['player_id'];
					$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
					//$player[$e['player_id']] = 
				}
			}
			
	$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
	$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
	$score_c =isset($player_id[2]) ? $player[$player_id[2]] : array();
        
        $grossscore_a = isset($player_id[0]) ? $playergross[$player_id[0]] : array();
	$grossscore_a2 = isset($player_id[1]) ? $playergross[$player_id[1]] : array();
	$grossscore_b =  isset($player_id[2]) ? $playergross[$player_id[2]] : array();
	//print_r($score_a);print_r($score_b);print_r($score_c);
	$zero_point = 0;$one_point = 1;$two_point = 2;$three_point = 3;$four_point = 4;	
	$start_value_first = $start_value_second = '0';	
	$color_team_a = "bgred";$color_team_b = "bgblue";$color_team_c = "bggreen";
	$color_team_both = "bgblack";$color_display_a = "colorred";	$color_display_b = "colorblue";	$color_display_c = "colorgreen";
	$color_display_both = "";$resultstr = $finalstr = '';$result_arr = $final_result_arr = array();
	if(count($score_a)>0 && count($score_b)>0 && count($score_c)>0) {
		$queryString = " delete from event_score_4_2_0 where event_id=".$eventId;
		$queryResult  = $this->db->FetchQuery($queryString);
		$player_a_total = $player_b_total = $player_c_total = array();
		$final_result = array();$pervious_winner_score = 0;	$pervious_winner_name = '';
		foreach($score_a as $a=>$b) {
			$winner_name = $bgclass = '';
			$a_val = intval($b);
			$b_val = isset($score_b[$a]) ? intval($score_b[$a]) : 0;
			$c_val = isset($score_c[$a]) ? intval($score_c[$a]) : 0;
			$grossa1_val=isset($grossscore_a[$a]) ? $grossscore_a[$a] : 0;
                        $grossa2_val=isset($grossscore_a2[$a]) ? $grossscore_a2[$a] : 0;  
                        $grossb1_val=isset($grossscore_b[$a]) ? $grossscore_b[$a] : 0;       
				if($grossa1_val>0 && $grossa2_val>0 && $grossb1_val>0){
				$last_index = ($a==1) ? 0 : ($a-2);
				$current_index = ($a==1) ? 0 : ($a-1);
				if($a_val == $b_val && $b_val == $c_val) {
					$player_a_total[$a] = $zero_point;
					$player_b_total[$a] = $zero_point;
					$player_c_total[$a] = $zero_point;
				}
				// when player a wins
				elseif($a_val < $b_val && $a_val < $c_val) { 
					
					// if player b win 2 point
					if($b_val < $c_val) {
						$player_a_total[$a] = $four_point;
						$player_b_total[$a] = $two_point;
						$player_c_total[$a] = $zero_point;
					}
					elseif($c_val < $b_val) {
						$player_b_total[$a] = $zero_point;
						$player_c_total[$a] = $two_point;
						$player_a_total[$a] = $four_point;
					}
					else {
						$player_b_total[$a] = $zero_point;
						$player_c_total[$a] = $zero_point;
						$player_a_total[$a] = $three_point;
					}
				}
				// when player a lose
				elseif($a_val > $b_val && $a_val > $c_val) {
					if($b_val < $c_val) {
						$player_b_total[$a] = $four_point;
						$player_c_total[$a] = $two_point;
						$player_a_total[$a] = $zero_point;
					}
					elseif($c_val < $b_val) {
						$player_b_total[$a] = $two_point;
						$player_c_total[$a] = $four_point;
						$player_a_total[$a] = $zero_point;
					}
					else {
						$player_b_total[$a] = $three_point;
						$player_c_total[$a] = $three_point;
						$player_a_total[$a] = $zero_point;
					}
				}
				// when player b wins
				elseif($b_val < $a_val && $b_val < $c_val) { 
					
					
					// if player a win 2 point
					if($a_val < $c_val) {
						$player_b_total[$a] = $four_point;
						$player_a_total[$a] = $two_point;
						$player_c_total[$a] = $zero_point;
					}
					elseif($c_val < $a_val) {
						$player_a_total[$a] = $zero_point;
						$player_c_total[$a] = $two_point;
						$player_b_total[$a] = $four_point;
					}
					else {
						$player_a_total[$a] = $zero_point;
						$player_c_total[$a] = $zero_point;
						$player_b_total[$a] = $three_point;
					}
				}
				// when player b lose
				elseif($b_val > $a_val && $b_val > $c_val) {
					
					if($a_val < $c_val) {
						$player_a_total[$a] = $four_point;
						$player_c_total[$a] = $two_point;
						$player_b_total[$a] = $zero_point;
					}
					elseif($c_val < $a_val) {
						$player_a_total[$a] = $two_point;
						$player_c_total[$a] = $four_point;
						$player_b_total[$a] = $zero_point;
					}
					else {
						$player_a_total[$a] = $three_point;
						$player_c_total[$a] = $three_point;
						$player_b_total[$a] = $zero_point;
					}
				}
				// when player c wins
				elseif($c_val < $b_val && $c_val < $a_val) { 
					
					
					// if player a win 2 point
					if($a_val < $b_val) {
						$player_c_total[$a] = $four_point;
						$player_a_total[$a] = $two_point;
						$player_b_total[$a] = $zero_point;
					}
					elseif($b_val < $a_val) {
						$player_a_total[$a] = $zero_point;
						$player_b_total[$a] = $two_point;
						$player_c_total[$a] = $four_point;
					}
					else {
						$player_a_total[$a] = $zero_point;
						$player_b_total[$a] = $zero_point;
						$player_c_total[$a] = $three_point;
					}
				}
				// when player c lose
				elseif($c_val > $a_val && $c_val > $b_val) {
					
					if($a_val < $b_val) {
						$player_a_total[$a] = $four_point;
						$player_b_total[$a] = $two_point;
						$player_c_total[$a] = $zero_point;
					}
					elseif($b_val < $a_val) {
						$player_a_total[$a] = $two_point;
						$player_b_total[$a] = $four_point;
						$player_c_total[$a] = $zero_point;
					}
					else {
						$player_b_total[$a] = $three_point;
						$player_a_total[$a] = $three_point;
						$player_c_total[$a] = $zero_point;
					}
				}
				
				$sum_a = array_sum($player_a_total);
				$sum_b = array_sum($player_b_total);
				$sum_c = array_sum($player_c_total);
				$min_score = min(array($sum_a,$sum_b,$sum_c));
				if($min_score == $sum_a) {
					$sum_a = 0;
					$sum_b = abs($sum_b-$min_score);
					$sum_c = abs($sum_c-$min_score);
				}
				elseif($min_score == $sum_b) {
					$sum_b = 0;
					$sum_a = abs($sum_a-$min_score);
					$sum_c = abs($sum_c-$min_score);
				}
				elseif($min_score == $sum_c) {
					$sum_c = 0;
					$sum_a = abs($sum_a-$min_score);
					$sum_b = abs($sum_b-$min_score);
				}
				$queryString = " insert into event_score_4_2_0(event_score_calc_id,hole_number,event_id,player_id,score_value,total) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$player_id[0].",".$player_a_total[$a].",".$sum_a.")";
		        $queryResult  = $this->db->FetchQuery($queryString);
				$queryString = " insert into event_score_4_2_0(event_score_calc_id,hole_number,event_id,player_id,score_value,total) values(".$event_score_calc_Arr[1].",".$a.",".$eventId.",".$player_id[1].",".$player_b_total[$a].",".$sum_b.")";
		        $queryResult  = $this->db->FetchQuery($queryString);
				$queryString = " insert into event_score_4_2_0(event_score_calc_id,hole_number,event_id,player_id,score_value,total) values(".$event_score_calc_Arr[2].",".$a.",".$eventId.",".$player_id[2].",".$player_c_total[$a].",".$sum_c.")";
		        $queryResult  = $this->db->FetchQuery($queryString);
			}
		}		
		$sum_a = array_sum($player_a_total);
		$sum_b = array_sum($player_b_total);
		$sum_c = array_sum($player_c_total);
		$winner_class_a = $winner_class_b = $winner_class_c = $winner_name = $winner_class = '';
		if($sum_a > $sum_b && $sum_a > $sum_c) {
			$winner_class_a = $color_team_a;
			$winner_class_b = $winner_class_c = '';
			$winner_name = 'Player A';
			$winner_class = $color_team_a;
		}
		elseif($sum_a > $sum_b && $sum_a == $sum_c) {
			$winner_class_a = $color_team_a;
			$winner_class_b = $winner_class_c = '';
			$winner_name = 'Player A and Player C';
			$winner_class = $color_team_a;
		}
		elseif($sum_a == $sum_b && $sum_a > $sum_c) {
			$winner_class_a = $color_team_a;
			$winner_class_b = $winner_class_c = '';
			$winner_name = 'Player A and Player B';
			$winner_class = $color_team_a;
		}
		elseif($sum_b > $sum_a && $sum_b > $sum_c) {
			$winner_class_b = $color_team_b;
			$winner_class_a = $winner_class_c = '';
			$winner_name = 'Player B';
			$winner_class = $color_team_b;
		}
		elseif($sum_b > $sum_a && $sum_b == $sum_c) {
			$winner_class_b = $color_team_b;
			$winner_class_a = $winner_class_c = '';
			$winner_name = 'Player B and Player C';
			$winner_class = $color_team_b;
		}
		elseif($sum_b ==  $sum_a && $sum_b > $sum_c) {
			$winner_class_b = $color_team_b;
			$winner_class_a = $winner_class_c = '';
			$winner_name = 'Player A and Player B';
			$winner_class = $color_team_b;
		}
		elseif($sum_c > $sum_a && $sum_c > $sum_b) {
			$winner_class_c = $color_team_c;
			$winner_class_a = $winner_class_b = '';
			$winner_name = 'Player C';
			$winner_class = $color_team_c;
		}
		elseif($sum_c > $sum_a && $sum_c == $sum_b) {
			$winner_class_c = $color_team_c;
			$winner_class_a = $winner_class_b = '';
			$winner_name = 'Player B and Player C';
			$winner_class = $color_team_c;
		}
		elseif($sum_c == $sum_a && $sum_c > $sum_b) {
			$winner_class_c = $color_team_c;
			$winner_class_a = $winner_class_b = '';
			$winner_name = 'Player A and Player C';
			$winner_class = $color_team_c;
		}
		elseif($sum_c == $sum_a && $sum_b == $sum_c) {
			$winner_class_c = $color_team_c;
			$winner_class_a = $winner_class_b = '';
			$winner_name = 'AS';
			$winner_class = $color_team_both;
		}
		
		$winner_class_a = $winner_class_b = $winner_class_c = '';
		$min_score = min(array($sum_a,$sum_b,$sum_c));
		if($min_score == $sum_a) {
			$sum_a = 0;
			$sum_b = abs($sum_b-$min_score);
			$sum_c = abs($sum_c-$min_score);
		}
		elseif($min_score == $sum_b) {
			$sum_b = 0;
			$sum_a = abs($sum_a-$min_score);
			$sum_c = abs($sum_c-$min_score);
		}
		elseif($min_score == $sum_c) {
			$sum_c = 0;
			$sum_a = abs($sum_a-$min_score);
			$sum_b = abs($sum_b-$min_score);
		}
		$queryString = " update event_score_calc set total_score='".$sum_a."' where event_id=".$eventId." and player_id=".$player_id[0]."";
		//$queryResult  = $this->db->FetchQuery($queryString);
		$queryString = " update event_score_calc set total_score='".$sum_b."' where event_id=".$eventId." and player_id=".$player_id[1]."";
		//$queryResult  = $this->db->FetchQuery($queryString);
		$queryString = " update event_score_calc set total_score='".$sum_c."' where event_id=".$eventId." and player_id=".$player_id[2]."";
		//$queryResult  = $this->db->FetchQuery($queryString);
		}		
		}
	}
	
function updatePar($eventId,$par,$holeId,$playerId)
		{
			$queryString = "update event_score_calc set ";
				$queryString .= " par_".$holeId." = ".$par."";
				$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			
			$queryResult = $this->db->FetchQuery($queryString);
			
		} 

	function updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id,$is_admin=0)
		{
			$queryString = "select ";
			$queryString .= " score_entry_".$holeId;
			$queryString .= " from event_score_calc where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult  = $this->db->FetchQuery($queryString);
			$lastScore = $this->db->FetchSingleValue($queryString);
			
			if($lastScore>0)
			{
				$diffValue = $score - $lastScore;
				$queryString = "update event_score_calc set ";
				$queryString .= " is_admin = ".$is_admin.",";
				$queryString .= " score_entry_".$holeId." = ".$score.",";
				$queryString .= " total_score = (total_score + ".$diffValue. "),"; 
				$queryString .= "gross_score = (gross_score + ".$diffValue.")"; 
				$queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
			}
			else
			{
			   $queryString = "update event_score_calc set ";
			   $queryString .= " is_admin = ".$is_admin.",";
				$queryString .= " score_entry_".$holeId." = ".$score.",";
				$queryString .= " par_total = par_total + ".$totalPar.",";
				$queryString .= " total_score = (total_score + ".$score. "),"; 
				$queryString .= "gross_score = ((gross_score + ".$score.") - ".$totalPar.")"; 
				$queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
				
				// update last hole played value
				$hoqueryString = "update event_score_calc set hole_number = ".$holeId." where event_id = ".$eventId." and player_id = ".$playerId;
				$queryResult  = $this->db->FetchQuery($hoqueryString);
			}
			$queryResult = $this->db->FetchQuery($queryString);
			
		}            
        
		function updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " net_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_".$holeId." = ".$score.",";
			$queryString .= " net_score  = (net_score + ".$diffValue.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
		}
		else
		{
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_".$holeId." = ".$score;
			$queryString .= " , net_score  = ((net_score + ".$score.") - ".$totalPar.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
		}
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_".$holeId." = (net_".$holeId." - 1),";
			$queryString .= " net_score  = (net_score  - 1) "; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			//$queryString .= " and handicap_value >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id.")";
			$queryString .= " and calculated_handicap >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id.")";
			$queryResult = $this->db->FetchQuery($queryString);
	}
	
		function updateNetScoreAccordingtoCalculatedhandicap($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " net_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_".$holeId." = ".$score.",";
			$queryString .= " net_score  = (net_score + ".$diffValue.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
		}
		else
		{
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_".$holeId." = ".$score;
			$queryString .= " , net_score  = ((net_score + ".$score.") - ".$totalPar.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
		}
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_".$holeId." = (net_".$holeId." - 1),";
			$queryString .= " net_score  = (net_score  - 1) "; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and calculated_handicap >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id.")";
			$queryResult = $this->db->FetchQuery($queryString);
	}
		
		function update34NetStrokePlayScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " net_stableford_3_4_v_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_stableford_3_4_v_".$holeId." = ".$score.",";
			$queryString .= " 3_4_v_total  = (3_4_v_total + ".$diffValue.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
			
		}
		else
		{
			$queryString = "update event_score_calc set "; 
			$queryString .= " net_stableford_3_4_v_".$holeId." = ".$score.",";
			$queryString .= " 3_4_v_total  = ((3_4_v_total + ".$score.") - ".$totalPar.")"; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
		 
		}
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_3_4_v_".$holeId." = (net_stableford_3_4_v_".$holeId." - 1),";
			$queryString .= " 3_4_v_total  = (3_4_v_total  - 1) "; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and handicap_value_3_4 >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id.")";
			$queryResult = $this->db->FetchQuery($queryString);
	}
       
	 function updateMoreNetHandicapValue($eventId,$golf_course_id,$playerId,$format_id,$hole_no)
	{
		$fieldValue="";
		if($format_id=="4" || $format_id=="7")
		{
			$fieldValue="handicap_value_3_4";
		}
		else
		{ $fieldValue="calculated_handicap";
			if($format_id=="3" || $format_id=="6"){
			$fieldValue="calculated_handicap";	
			}
		}
		if($fieldValue!="")
		{
			$indexValueArray = array();
			$queryString = "select "; 
			$queryString .=" hole_index_".$hole_no;
			$queryString .=" from golf_hole_index";
			$queryString .=" where golf_course_id = ".$golf_course_id;
			$hole_index = $this->db->FetchSingleValue($queryString);
			 
			$sqlQuery = "select ".$fieldValue." as handicap_value from event_score_calc where ".$fieldValue." > 18 and event_id =".$eventId;
			$sqlQuery .= " and player_id = ".$playerId;	
			$handicap_value = $this->db->FetchSingleValue($sqlQuery);
			$handicap_value = ($handicap_value>0) ? $handicap_value : 0;
			//echo 'player : '.$playerId.' :: hole index : '.$hole_index.' | handicap value : '.$handicap_value.'\n';//die;
			$maxCount = $handicap_value - 18;
			//echo 'player : '.$playerId.' :: hole index : '.$hole_index.' | maxCount : '.$maxCount.'\n';//die;
			if($maxCount > 0)
			{
				if($hole_index <= $maxCount)
				{
					$queryString = "update event_score_calc set  ";
					$queryString .= " net_".$hole_no." = (net_".$hole_no." - 1),";
					$queryString .= " net_score  = (net_score  - 1) "; 
					$queryString .= " where event_id = ".$eventId;
					$queryString .= " and player_id = ".$playerId; //echo $queryString;
					$queryResult1 = $this->db->FetchQuery($queryString);
					if($format_id=="4" || $format_id=="7")
					{
						$queryString = "update event_score_calc set  ";
						$queryString .= " net_stableford_3_4_v_".$hole_no." = (net_stableford_3_4_v_".$hole_no." - 1),";
						$queryString .= " 3_4_v_total  = (3_4_v_total  - 1) "; 
						$queryString .= " where event_id = ".$eventId;
						$queryString .= " and player_id = ".$playerId;
						$queryResult = $this->db->FetchQuery($queryString);
						
					}
				}
			}
		}
	}

	function updateGrossStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		//echo $eventId.'__'.$strokeId.'__'.$holeId.'__'.$score.'__'.$playerId.'__'.$totalPar.'__'.$golf_course_id; die;
		$queryString = "select ";
		$queryString .= " score_entry_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " gross_stableford_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ( (score_entry_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " gross_stableford_".$holeId." = ((-1)*((score_entry_".$holeId." - ".$totalPar. " - 2)))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((score_entry_".$holeId." - ". $totalPar.") < 2)"; 
			$queryResult = $this->db->FetchQuery($queryString);
			
		}
		else
		{ 
			$queryString = "update event_score_calc set  ";
			$queryString .= " gross_stableford_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ( (score_entry_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " gross_stableford_".$holeId." = ((-1)*((score_entry_".$holeId." - ".$totalPar. " - 2)))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((score_entry_".$holeId." - ". $totalPar.") < 2)"; 
			$queryResult = $this->db->FetchQuery($queryString);
			
		}
		
			$queryString = "select (gross_stableford_1 + gross_stableford_2 + gross_stableford_3 + gross_stableford_4 + gross_stableford_5 + gross_stableford_6 + gross_stableford_7 + gross_stableford_8 + gross_stableford_9 + gross_stableford_10 + gross_stableford_11 + gross_stableford_12 + gross_stableford_13 + gross_stableford_14 + gross_stableford_15 + gross_stableford_16 + gross_stableford_17 + gross_stableford_18) as total_gross_stableford ";
			$queryString .= " from event_score_calc where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$gross_stableford_score = $this->db->FetchSingleValue($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " gross_stableford = ".$gross_stableford_score;
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
				
	}    
		
		
		function updateNetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " net_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
		
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_".$holeId." = ((-1)*((net_".$holeId." - ".$totalPar. ") -2))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_".$holeId." - ". $totalPar.") < 2)";   
			$queryResult = $this->db->FetchQuery($queryString);
		}
		else
		{
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_".$holeId." = ((-1)*((net_".$holeId." - ".$totalPar. ") -2))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_".$holeId." - ". $totalPar.") < 2)";
			$queryResult = $this->db->FetchQuery($queryString);			
			
		}
		
			$queryString = "select (net_stableford_1 + net_stableford_2 + net_stableford_3 + net_stableford_4 + net_stableford_5 + net_stableford_6 + net_stableford_7 + net_stableford_8 + net_stableford_9 + net_stableford_10 + net_stableford_11 + net_stableford_12 + net_stableford_13 + net_stableford_14 + net_stableford_15 + net_stableford_16 + net_stableford_17 + net_stableford_18) as total_net_stableford ";
			$queryString .= " from event_score_calc where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$stableford_score = $this->db->FetchSingleValue($queryString);	
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford = ".$stableford_score;
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);	
			
	}
	function update34NetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " net_stableford_3_4_v_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_3_4_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_stableford_3_4_v_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_3_4_".$holeId." = ((-1)*((net_stableford_3_4_v_".$holeId." - ".$totalPar. ") -2))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_stableford_3_4_v_".$holeId." - ". $totalPar.") < 2)";
			$queryResult = $this->db->FetchQuery($queryString);			
			
		}
		else
		{
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_3_4_".$holeId." = 0";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_stableford_3_4_v_".$holeId." - ". $totalPar.") >= 2)";
			$queryResult = $this->db->FetchQuery($queryString);
			
			$queryString = "update event_score_calc set  ";
			$queryString .= " net_stableford_3_4_".$holeId." = ((-1)*((net_stableford_3_4_v_".$holeId." - ".$totalPar. ") -2))";
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryString .= " and ((net_stableford_3_4_v_".$holeId." - ". $totalPar.") < 2)";   
			$queryResult = $this->db->FetchQuery($queryString);
			
			}
			 $queryString = "select (net_stableford_3_4_1 + net_stableford_3_4_2 + net_stableford_3_4_3 + net_stableford_3_4_4 + net_stableford_3_4_5 + net_stableford_3_4_6 + net_stableford_3_4_7 + net_stableford_3_4_8 + net_stableford_3_4_9 + net_stableford_3_4_10 + net_stableford_3_4_11 + net_stableford_3_4_12 + net_stableford_3_4_13 + net_stableford_3_4_14 + net_stableford_3_4_15 + net_stableford_3_4_16 + net_stableford_3_4_17 + net_stableford_3_4_18) as total_net_stableford ";
			$queryString .= " from event_score_calc where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$stableford_score = $this->db->FetchSingleValue($queryString); 
		
			$queryString = "update event_score_calc set  ";
			$queryString .= " 3_4_total = ".$stableford_score;
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$queryResult = $this->db->FetchQuery($queryString);
	}
	
	function updatePeoriaScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
	{
		$queryString = "select ";
		$queryString .= " score_entry_".$holeId;
		$queryString .= " from event_score_calc where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString); 
		
		if($lastScore>0)
		{
			$diffValue = $score - $lastScore;
		}
		else
		{
			
			$queryString = "update event_score_calc set ";
			$queryString .= " par_total = par_total + ".$totalPar;
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$this->db->FetchQuery($queryString); 
		}
	}	
	
	function updatePosition($eventId,$stroke_play_id,$golf_course_id){
		$position = 0;
		$colName = "";
		$conditionString = "";
		
		switch($stroke_play_id)
		{
			case "2":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "3":
				$rankingbyholeno="max";
				$colName =" net_score ,";$colName1="net_score";
				$conditionString =" a.net_score < b.net_score ) as rank ";
			break;	
			case "4":					
				$rankingbyholeno="max";
				$colName =" 3_4_v_total ,";$colName1="3_4_v_total";
				$conditionString =" a.3_4_v_total < b.3_4_v_total ) as rank ";
			break;	
			case "5":
				$rankingbyholeno="min";
				$colName =" gross_stableford ,";$colName1="gross_stableford";
				$conditionString =" a.gross_stableford > b.gross_stableford ) as rank ";
			break;
			case "6":
				$rankingbyholeno="min";
				$colName =" net_stableford ,";$colName1="net_stableford";
				$conditionString =" a.net_stableford > b.net_stableford ) as rank ";
			break;
			case "7":
				$rankingbyholeno="min";
				$colName =" 3_4_total ,";$colName1="3_4_total";
				$conditionString =" a.3_4_total > b.3_4_total ) as rank ";
			break;
			case "8":
				$rankingbyholeno="max";
				$colName =" gross_score ,";$colName1="gross_score";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "9":
				$rankingbyholeno="max";
				$colName =" gross_score ,";$colName1="gross_score";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
case "10":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "11":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "12":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "13":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
			case "14":
				$rankingbyholeno="max";$colName1="gross_score";
				$colName =" gross_score ,";
				$conditionString =" a.gross_score < b.gross_score ) as rank ";
			break;
		}
		
		$queryString = "select event_score_calc_id, ";
		$queryString .= $colName;
		$queryString .= " 1 + (select count( * ) from event_score_calc a";
		$queryString .= " where a.hole_number >0 and a.event_id = ".$eventId;
		$queryString .= " and ";
		$queryString .= $conditionString;
		$queryString .= " from event_score_calc b where b.hole_number >0 and event_id =  ".$eventId."";
		 $recordSetPosition= $this->db->FetchQuery($queryString); 
		foreach($recordSetPosition as $i=>$rowValues2)
		{
			$position++;
			$queryUpd = "update event_score_calc set current_position='".$rowValues2['rank']."'";
			$queryUpd .= " where event_score_calc_id='".$rowValues2['event_score_calc_id']."'";
			$this->db->FetchQuery($queryString); 
		}
		//If multiple player have same current positon
		$dupCount=0;
		$orderby=($rankingbyholeno=="max")?"DESC":"ASC";
					 $orderby2=($rankingbyholeno=="max")?"ASC":"DESC";
	$sql="select event_score_calc_id,player_id,hole_number,event_score_calc.current_position";
			$sql .=" from event_score_calc";
			$sql .=" inner join(select current_position from event_score_calc";
			$sql .=" where event_id=".$eventId ." group by current_position";
			$sql .=" having count(player_id) >1)temp on ";
			$sql .=" event_score_calc.current_position= temp.current_position";
			$sql .=" where hole_number > 0";
			$sql .=" and event_score_calc.event_id=".$eventId;
			$sql .=" order by hole_number ".$orderby."";
		$re= $this->db->FetchQuery($sql); 	
		if(count($re) > 0){
			foreach($re as $i=>$row){
				if($dupCount==0){
				}else{
			$queryUpd = "update event_score_calc set current_position=current_position + ".$dupCount;
				    $queryUpd .= " where event_score_calc_id='".$row['event_score_calc_id']."'";
				$this->db->FetchQuery($queryUpd); 
				}
				$dupCount++;
			}
		}
		
		//If player have score total 0
		
		$this->finalizePosition($eventId,$colName,$orderby2);
		$sql1="select max(current_position) as maxrank from ";
		$sql1 .=" event_score_calc where hole_number > 0  and event_id=".$eventId."";
		$maxrank_val= $this->db->FetchSingleValue($sql1);
		$maxrank=($maxrank_val + 1);
		$queryUpd = "update event_score_calc set lb_display_string ='T' , current_position=".$maxrank;
		$queryUpd .= " where hole_number= 0  and event_id=".$eventId."";
		$this->db->FetchQuery($queryUpd);			
		//find duplicate 0 order by holenum more up)   
		//less->max hole win & more -> less hole win        (current_position + count)

	}
	
	function updatePositionGross($eventId,$stroke_play_id,$golf_course_id){
		$position = 0;
		$colName = "";
		$conditionString = "";
		
		$rankingbyholeno="max";$colName1="gross_score";
		$colName =" gross_score ,";
		$conditionString =" a.gross_score < b.gross_score ) as rank ";
		
		$queryString = "select event_score_calc_id, ";
		$queryString .= $colName;
		$queryString .= " 1 + (select count( * ) from event_score_calc a";
		$queryString .= " where a.hole_number >0 and a.event_id = ".$eventId;
		$queryString .= " and ";
		$queryString .= $conditionString;
		$queryString .= " from event_score_calc b where b.hole_number >0 and event_id =  ".$eventId."";
		 $recordSetPosition= $this->db->FetchQuery($queryString); 
		foreach($recordSetPosition as $i=>$rowValues2) {
			$position++;
			$queryUpd = "update event_score_calc set current_position_gross='".$rowValues2['rank']."'";
			$queryUpd .= " where event_score_calc_id='".$rowValues2['event_score_calc_id']."'";
			$this->db->FetchQuery($queryString); 
		}
		//If multiple player have same current positon
		$dupCount=0;
		$orderby=($rankingbyholeno=="max")?"DESC":"ASC";
		$orderby2=($rankingbyholeno=="max")?"ASC":"DESC";
		$sql="select event_score_calc_id,player_id,hole_number,event_score_calc.current_position_gross";
		$sql .=" from event_score_calc";
		$sql .=" inner join(select current_position_gross from event_score_calc";
		$sql .=" where event_id=".$eventId ." group by current_position_gross";
		$sql .=" having count(player_id) >1)temp on ";
		$sql .=" event_score_calc.current_position_gross= temp.current_position_gross";
		$sql .=" where hole_number > 0";
		$sql .=" and event_score_calc.event_id=".$eventId;
		$sql .=" order by hole_number ".$orderby."";
		$re= $this->db->FetchQuery($sql); 	
		if(count($re) > 0){
			foreach($re as $i=>$row){
				if($dupCount==0){
				}else{
			$queryUpd = "update event_score_calc set current_position_gross=current_position_gross + ".$dupCount;
				    $queryUpd .= " where event_score_calc_id='".$row['event_score_calc_id']."'";
				$this->db->FetchQuery($queryUpd); 
				}
				$dupCount++;
			}
		}
		
		//If player have score total 0
		
		$this->finalizePositionGross($eventId,$colName,$orderby2);
		$sql1="select max(current_position_gross) as maxrank from ";
		$sql1 .=" event_score_calc where hole_number > 0  and event_id=".$eventId."";
		$maxrank_val= $this->db->FetchSingleValue($sql1);
		$maxrank=($maxrank_val + 1);
		$queryUpd = "update event_score_calc set lb_display_string ='T' , current_position_gross=".$maxrank;
		$queryUpd .= " where hole_number= 0  and event_id=".$eventId."";
		$this->db->FetchQuery($queryUpd);
		//find duplicate 0 order by holenum more up)   
		//less->max hole win & more -> less hole win        (current_position_gross + count)
	}
	
	function updateNoOFHolesPlayed($eventId,$stroke_play_id,$playerId,$total_num_hole,$hole_start_from=1){
		$fieldname="score_entry_"; 
		$qryString ="";
		for($ctr = ($hole_start_from-1); $ctr < $total_num_hole;  $ctr++)
		{
			$ctrV = $ctr+1;
			$qryString .= $fieldname.$ctrV." as hole_num_".$ctrV;
			if($ctr != $total_num_hole-1)
			{
				$qryString .= ",";
			}
		}
		$queryString = " select player_id, "; 
		$queryString .= $qryString;
		$queryString .= " from event_score_calc where event_id ='".$eventId."' and player_id='".$playerId."'";
		$rowValues = $this->db->FetchRow($queryString);
		
		$no_of_holes_played=0;
		for($counter = $hole_start_from; $counter <= $total_num_hole;  $counter++)
		{
				if($rowValues['hole_num_'.$counter] !=0){
					$no_of_holes_played++;
				}
		}
		$sqlu="update event_score_calc set no_of_holes_played='".$no_of_holes_played."' where event_id ='".$eventId."' and player_id='".$playerId."'";
		$this->db->FetchQuery($sqlu);
	} 

	function finalizePosition($eventId,$colName1,$orderby){
		$idArray = array();
		$scoreArray = array();
		$holeNumberArray = array();
		
		$colName = trim(str_replace(",","",$colName1));
		$queryString = "select event_score_calc_id, ".$colName.", hole_number";
		$queryString .= " from event_score_calc";
		$queryString .= " where hole_number >0";
		$queryString .= " and event_id = ".$eventId;
		$queryString .= " order by ".$colName." ".$orderby;
		
		$res = $this->db->FetchQuery($queryString);
		
		if(count($res) > 0)
		{
			foreach($res as $i=>$row)
			{
				$idArray[] = (int)$row['event_score_calc_id'];
				$scoreArray[] = (int)$row[$colName];
				$holeNumberArray[] = (int)$row['hole_number'];
			}
		}
		$postion =0;
		$skipCount=0;
		for($counter=0; $counter <count($scoreArray);$counter++) 
		{
	//	echo $counter."</br>"; 	
		if($counter>0)
		{
			$postion = $postion + 1;
			if($scoreArray[$counter] == $scoreArray[$counter -1])
			{
				$skipCount = $skipCount +1;
				$postion = $postion - 1;
				$queryUpd = "update event_score_calc set current_position= ".($postion);
				$queryUpd .= ", lb_display_string ='T'";
				$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
				$this->db->FetchQuery($queryUpd);
				
				$queryUpd = "update event_score_calc set ";
				$queryUpd .= " lb_display_string ='T'";
				$queryUpd .= " where event_score_calc_id='".$idArray[$counter -1]."'";
				$this->db->FetchQuery($queryUpd);
			}
			else
			{
				if($counter == 1){
				$queryUpd = "update event_score_calc set ";
				$queryUpd .= " lb_display_string =''";
				$queryUpd .= " where event_score_calc_id='".$idArray[$counter -1]."'";
				$this->db->FetchQuery($queryUpd);
				}
				$postion = $postion + $skipCount;
				$skipCount=0;
				$queryUpd = "update event_score_calc set current_position= ".$postion;
				$queryUpd .= " ,lb_display_string =''";
				$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
				$this->db->FetchQuery($queryUpd);
			}
		}
		else if($counter == 0)
		{
			$postion = 1;
			$skipCount=0;
			$queryUpd = "update event_score_calc set current_position= ".$postion;
			$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
			$this->db->FetchQuery($queryUpd);
		}
			//echo "esid = >". $idArray[$counter]. " score => ". $scoreArray[$counter]." rank => ". $postion."</br>";
			
		}
			$queryUpd = "update event_score_calc set lb_display_string =''";
			$queryUpd .= " where hole_number =0";
			$queryUpd .= " and event_id = ".$eventId;
			$this->db->FetchQuery($queryUpd);
	}	
	
	function finalizePositionGross($eventId,$colName1,$orderby){
		$idArray = array();
		$scoreArray = array();
		$holeNumberArray = array();
		
		$colName = trim(str_replace(",","",$colName1));
		$queryString = "select event_score_calc_id, ".$colName.", hole_number";
		$queryString .= " from event_score_calc";
		$queryString .= " where hole_number >0";
		$queryString .= " and event_id = ".$eventId;
		$queryString .= " order by ".$colName." ".$orderby;
		
		$res = $this->db->FetchQuery($queryString);
		
		if(count($res) > 0) {
			foreach($res as $i=>$row) {
				$idArray[] = (int)$row['event_score_calc_id'];
				$scoreArray[] = (int)$row[$colName];
				$holeNumberArray[] = (int)$row['hole_number'];
			}
		}
		$postion =0;
		$skipCount=0;
		for($counter=0; $counter <count($scoreArray);$counter++) {
			if($counter>0) {
				$postion = $postion + 1;
				if($scoreArray[$counter] == $scoreArray[$counter -1]) {
					$skipCount = $skipCount +1;
					$postion = $postion - 1;
					$queryUpd = "update event_score_calc set current_position_gross= ".($postion);
					$queryUpd .= ", lb_display_string_gross ='T'";
					$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
					$this->db->FetchQuery($queryUpd);
					
					$queryUpd = "update event_score_calc set ";
					$queryUpd .= " lb_display_string_gross ='T'";
					$queryUpd .= " where event_score_calc_id='".$idArray[$counter -1]."'";
					$this->db->FetchQuery($queryUpd);
				}
				else {
					if($counter == 1){
					$queryUpd = "update event_score_calc set ";
					$queryUpd .= " lb_display_string_gross =''";
					$queryUpd .= " where event_score_calc_id='".$idArray[$counter -1]."'";
					$this->db->FetchQuery($queryUpd);
					}
					$postion = $postion + $skipCount;
					$skipCount=0;
					$queryUpd = "update event_score_calc set current_position_gross= ".$postion;
					$queryUpd .= " ,lb_display_string_gross =''";
					$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
					$this->db->FetchQuery($queryUpd);
				}
			}
			else if($counter == 0) {
				$postion = 1;
				$skipCount=0;
				$queryUpd = "update event_score_calc set current_position_gross= ".$postion;
				$queryUpd .= " where event_score_calc_id='".$idArray[$counter]."'";
				$this->db->FetchQuery($queryUpd);
			}
		}
		$queryUpd = "update event_score_calc set lb_display_string_gross =''";
		$queryUpd .= " where hole_number =0";
		$queryUpd .= " and event_id = ".$eventId;
		$this->db->FetchQuery($queryUpd);
		}	
	
	function updateNoOFPutt($eventId,$no_of_putt,$holeId,$playerId,$parval=3,$scoreval)
		{
			$score_a = array($holeId=>$scoreval);
			$score_b = array($holeId=>$parval);
			$score_c = array($holeId=>$no_of_putt);
			
			$yes_gir_total = $no_gir_total = 0;
	
	if(count($score_a)>0 && count($score_b)>0) {
		
		$db_putt_str = ''; $db_putt_arr = array();
		$db_par_str = ''; $db_par_arr = array();
		$db_gir_str = ''; $db_gir_arr = array();
		$db_score_str = ''; $db_score_arr = array();
		
		for($xyz=1;$xyz<=18;$xyz++) {
			$db_putt_arr[] = "no_of_putt_{$xyz}";
			$db_gir_arr[] = "gir_{$xyz}";
			$db_par_arr[] = "par_value_{$xyz}";
			$db_score_arr[] = "score_entry_{$xyz}";
		}
		
		// get all gir values
		$sql1 = "SELECT ".implode(",",$db_gir_arr).",".implode(",",$db_putt_arr)." FROM event_score_calc_no_of_putt where event_id='{$eventId}' and player_id='{$playerId}' limit 1";
		$all_girs_arr = $this->db->FetchQuery($sql1);
		
		
		// get all par values
		$sql1 = "SELECT ".implode(",",$db_par_arr)." FROM golf_hole_index where golf_course_id=(select golf_course_id from event_table where event_id='{$eventId}') limit 1";
		$all_par_arr = $this->db->FetchQuery($sql1);
		
		// get all score values
		$sql1 = "SELECT ".implode(",",$db_score_arr)." FROM `event_score_calc` where event_id='{$eventId}' and player_id='{$playerId}' limit 1";
		$all_scores_arr = $this->db->FetchQuery($sql1);
		//print_r($all_girs_arr);
		//print_r($all_par_arr);
		//print_r($all_scores_arr);
		$all_putting_count=0;
		$end_final_result = $is_gir_arr = $par_3_arr = $par_4_arr = $par_5_arr = $allputs = array();
		if(isset($all_scores_arr[0]) && is_array($all_scores_arr[0]) && count($all_scores_arr[0])>0) {
			$myindex = 1; $is_gir_hole_arr = $is_not_gir_hole_arr = array();
			foreach($all_scores_arr[0] as $a=>$b) {
				$hole_score_value = $b;
				$is_gir = isset($all_girs_arr[0]["gir_{$myindex}"]) ? $all_girs_arr[0]["gir_{$myindex}"] : 0;
				$putt_score = isset($all_girs_arr[0]["no_of_putt_{$myindex}"]) ? $all_girs_arr[0]["no_of_putt_{$myindex}"] : 0;
				if($hole_score_value>0) {
					if($is_gir==1) { // is_gir
						$yes_gir_total++;
						$is_gir_arr[] = $putt_score;
						$is_gir_hole_arr[] = $myindex;
					}
					elseif($is_gir==2) { // not gir
						$no_gir_total++;
						$is_not_gir_hole_arr[] = $myindex;
					}else{
}
					
					$allputs[$myindex] = $putt_score;
					
					// get par value
					$par_val = isset($all_par_arr[0]["par_value_{$myindex}"]) ? $all_par_arr[0]["par_value_{$myindex}"] : 0;
					if($par_val==3) {
						$par_3_arr[] = $hole_score_value;
					}
					elseif($par_val==4) {
						$par_4_arr[] = $hole_score_value;
					}
					elseif($par_val==5) {
						$par_5_arr[] = $hole_score_value;
					}
					$all_putting_count++;
				}
				$myindex++;
			}
		}
		
		
		//die;
		
		foreach($score_a as $a=>$b) {
			$resultstr = $finalstr = '';
			$final_result_arr = $temp_result_arr = array();
			$score = $b;
			$par = isset($score_b[$a]) ? $score_b[$a] : 0;
			$putting = isset($score_c[$a]) ? $score_c[$a] : '-1';
			if($putting != '-1'){
				$allputs[$a] = $putting;
			}
			
			$last_index = ($a==1) ? 0 : ($a-2);
			$current_index = ($a==1) ? 0 : ($a-1);
			$is_gir = 2;
			$is_gir_str = '';
$all_putting_count++;
			if($score>0 && $par>0 && $putting>=0){
				if((($par-$score)+$putting)>=2) {
					
					$is_gir = 1;
					$is_gir_arr[] = $putting;
					if($par==3) {
						$par_3_arr[] = $score;
					}
					elseif($par==4) {
						$par_4_arr[] = $score;
					}
					elseif($par==5) {
						$par_5_arr[] = $score;
					}
				}
				$end_final_result[$a] = array('par'=>$par,'gross_score'=>$score,'putting'=>$putting,'is_gir'=>$is_gir);
				if($is_gir==1) {
					if(!in_array($a,$is_gir_hole_arr)){$yes_gir_total++;}
				}
				elseif($is_gir==2) {
					if(!in_array($a,$is_not_gir_hole_arr)){$no_gir_total++;}
				}
				else{
					
				}
			}
			else {
				$end_final_result[$a] = array('par'=>$par,'gross_score'=>$score,'putting'=>'-1','is_gir'=>0);
			}
		}
		$par = (count($is_gir_arr)>0) ? round((array_sum($is_gir_arr)/count($is_gir_arr)),2) : 0.00;
		$perGir = ($par>0) ? $par : 0;
		$all_putting = array_values($allputs);
		//$all_putting_count = count($end_final_result);
		$all_putting_count = count($all_putting);
		$putting = round((array_sum($all_putting)/$all_putting_count),2);
		$perHole = ($putting>0) ? $putting : 0;
		// insert putting and gir
		
		
		// insert par3 avg
		$par3_avg = (count($par_3_arr)>0) ? round((array_sum($par_3_arr)/count($par_3_arr)),2) : 0;
		$par3_avg = ($par3_avg>0) ? $par3_avg : 0;
		
		
		// insert par4 avg
		$par4_avg = (count($par_4_arr)>0) ? round((array_sum($par_4_arr)/count($par_4_arr)),2) : 0;
		$par4_avg = ($par4_avg>0) ? $par4_avg : 0;
		
		//print_r($par_5_arr);
		// insert par5 avg
		$par5_avg = (count($par_5_arr)>0) ? round((array_sum($par_5_arr)/count($par_5_arr)),2) : 0;
		$par5_avg = ($par5_avg>0) ? $par5_avg : 0;
		
		
	}
		$queryString = "update event_score_calc_no_of_putt set no_of_putt_".$holeId." = ".$end_final_result[$holeId]["putting"].", gir_".$holeId."=".$end_final_result[$holeId]["is_gir"].",per_gir=".$perGir.",per_hole=".$perHole.",per_3_average=".$par3_avg.",per_4_average=".$par4_avg.",per_5_average=".$par5_avg.",gir_yes=".$yes_gir_total.",gir_no=".$no_gir_total." where event_id = ".$eventId." and player_id = ".$playerId;
		$queryResult = $this->db->FetchQuery($queryString); 
		
		for($xyz=1;$xyz<=18;$xyz++) {
			$db_putt_arr[] = "no_of_putt_{$xyz}";
			$db_gir_arr[] = "gir_{$xyz}";
			$db_par_arr[] = "par_value_{$xyz}";
			$db_score_arr[] = "score_entry_{$xyz}";
		}
		
		// get all gir values
		$sql1 = "SELECT ".implode(",",$db_gir_arr)." FROM event_score_calc_no_of_putt where event_id='{$eventId}' and player_id='{$playerId}' limit 1";
		$all_girs_arr = $this->db->FetchQuery($sql1);
		
		$yes_gir_total = $no_gir_total = 0;
		for($i=1;$i<=18;$i++) {
			$myindex = $i;
			$is_gir = isset($all_girs_arr[0]["gir_{$myindex}"]) ? $all_girs_arr[0]["gir_{$myindex}"] : 0;
			
			if($is_gir == 1) {
				$yes_gir_total++;
			}
			else if($is_gir == 2) {
				$no_gir_total++;
			}
		}
		
		
		
		
		
		$queryString = "update event_score_calc_no_of_putt set gir_yes=".$yes_gir_total.",gir_no=".$no_gir_total." where event_id = ".$eventId." and player_id = ".$playerId;
		$queryResult = $this->db->FetchQuery($queryString); 
		
}
function updateFairways($eventId,$fairway,$holeId,$playerId)
	{
		$queryString = "select fairway_id from event_score_calc_fairway where event_id = ".$eventId;
		$queryString .= " and player_id = ".$playerId;
		$lastScore = $this->db->FetchSingleValue($queryString);
	
		if($lastScore>0)
		{
			$queryString = "update event_score_calc_fairway set ";
			$queryString .= " fairway_".$holeId." = ".$fairway.""; 
			$queryString .= " where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
		}
		else
		{
			$queryString = " insert into event_score_calc_fairway(event_id,player_id,fairway_".$holeId.") values(".$eventId.",".$playerId.",".$fairway.")";
		}
			$queryResult = $this->db->FetchQuery($queryString);
	}	


function updateSands($eventId,$sand,$holeId,$playerId)
		{
			$queryString = "select sand_id from event_score_calc_sand where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$lastScore = $this->db->FetchSingleValue($queryString);
			
			if($lastScore>0)
			{
		
				$queryString = "update event_score_calc_sand set ";
				$queryString .= " sand_".$holeId." = ".$sand.""; 
				$queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
			}
			else
			{
			   $queryString = " insert into event_score_calc_sand(event_id,player_id,sand_".$holeId.") values(".$eventId.",".$playerId.",".$sand.")";
			}
			$queryResult = $this->db->FetchQuery($queryString);
			
		}	
function updateClosestToPinFeet($eventId,$closest_feet,$closest_inches,$holeId,$playerId)
		{
			$closest_inches = trim($closest_inches);
			if($closest_feet >= 0 && $closest_feet < 150) {
				$closest_inches1 = (strlen($closest_inches) ==1 ) ? ("0".$closest_inches) : $closest_inches;
				$spot_value = $closest_feet.'.'.$closest_inches1; 
			}
			elseif($closest_feet >= 150) {
				$spot_value = $closest_feet; 
			}
			else {
				$spot_value = '-1.0'; 
			}
			
			$queryString = "select closest_feet_id from event_score_calc_closest_feet where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$lastScore = $this->db->FetchSingleValue($queryString);
			if($lastScore > 0)
			{ 
				$queryString = "update event_score_calc_closest_feet set ";
				$queryString .= " closest_feet_".$holeId." = '".$spot_value."'"; 
				$queryString .= " where event_id = ".$eventId;
				 $queryString .= " and player_id = ".$playerId;
			}
			else
			{ 
			   $queryString = " insert into event_score_calc_closest_feet(event_id,player_id,closest_feet_".$holeId.") values(".$eventId.",".$playerId.",'".$spot_value."')";
			}
			$queryResult = $this->db->FetchQuery($queryString);
			//echo $queryString;die;
			$this->updateClosestToPinInches($eventId,$closest_inches,$closest_feet,$holeId,$playerId);
		}

function updateClosestToPinInches($eventId,$closest_inches,$closest_feet,$holeId,$playerId)
		{
			$queryString = "select closest_inch_id from event_score_calc_closest_inch where event_id = ".$eventId." and player_id = ".$playerId; 
			$lastScore = $this->db->FetchSingleValue($queryString);
			
			if($closest_feet >= 0 && $closest_feet < 150) {
				$total = ($closest_feet*12)+$closest_inches ;
			}
			elseif($closest_feet >= 150) {
				$total = $closest_feet*36;
			}
			else {
				$total = 0; 
			}
			
			//$total = ($closest_feet*12)+$closest_inches ;
			if($lastScore > 0)
			{
				
				$queryString = "update event_score_calc_closest_inch set ";
				$queryString .= " closest_inch_".$holeId." = '".$total."'"; 
				 $queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
			}
			else
			{
			   $queryString = " insert into event_score_calc_closest_inch(event_id,player_id,closest_inch_".$holeId.") values(".$eventId.",".$playerId.",'".$total."')";
			}
			$queryResult = $this->db->FetchQuery($queryString);
			
		}
function getLeaderBoard($data){//print_r($data);die;

			 $playerScoreListArray =$fdata= array();
			$eventId=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"0";
			$format_id=(isset($data['format_id']) && $data['format_id'] > 0)?$data['format_id']:"0";
			$is_spot_type=(isset($data['is_spot_type']) && $data['is_spot_type'] > 0)?$data['is_spot_type']:"0";
		$is_spot_hole_number=(isset($data['is_spot_hole_number']) && $data['is_spot_hole_number'] > 0)?$data['is_spot_hole_number']:"0";
            $type=(isset($data['type']) && $data['type'] > 0)?$data['type']:"0";
			$original_format = 0;
            if($eventId!=""){
           			    $queryString = "select total_hole_num, format_id, is_started, DATE(event_start_date_time) as event_start_date,event_start_time,golf_course_name,event_name,is_spot from event_list_view where event_id ='".$eventId."'";
						$result = $this->db->FetchRow($queryString);
					
						
						if(count($result) > 0){
							$total_hole_num = $result['total_hole_num'];
						$stroke_play_id = ($format_id >0)?$format_id:$result['format_id'];
						$original_format = $result['format_id'];
						$is_started = $result['is_started'];
						$event_start_date = $result['event_start_date'];
						$event_start_time = $result['event_start_time'];
						$golf_course_name = $result['golf_course_name'];
						$event_name = $result['event_name'];
						
				$is_spot_data=array();		
				if($is_spot_hole_number > 0){
					$a = "select type,hole_number from event_is_spot_tbl where event_id ='".$eventId."' and hole_number='".$is_spot_hole_number."' ";
					$b = $this->db->FetchQuery($a);					
					if(count($b)>0){
						foreach($b as $i=>$c){
							$is_spot_data[] = $c['type'];
						}													
					}
				}
				//$currentScoreListArray['is_spot_type'] = (count($is_spot_data > 0))?implode(',',array_unique($is_spot_data)):"0";
				$currentScoreListArray['is_spot_type'] = $is_spot_type;
				if($currentScoreListArray['is_spot_type']==3){
					$spot_order="DESC";
				}else{
					$spot_order="ASC";
				}
						$currentScoreListArray['event_name'] =$event_name;
						$currentScoreListArray['event_start_date'] =$event_start_date;
						$currentScoreListArray['golf_course_name'] =$golf_course_name;
						$currentScoreListArray['total_hole_num'] =$total_hole_num;
						
							if($is_started=="3" || $is_started=="4"){
								$qryString ="";
								if($stroke_play_id=="2"){
								$currentScoreListArray['format_name']="Gross Strokeplay";
									$fieldname="score_entry_"; $total_field_name="gross_score";
								}elseif($stroke_play_id=="3"){
								$currentScoreListArray['format_name']="Net Strokeplay";
									$fieldname="net_";  $total_field_name="net_score";
								}elseif($stroke_play_id=="4"){
								$currentScoreListArray['format_name']="3/4th Net Strokeplay";
									$fieldname="net_stableford_3_4_v_"; $total_field_name="3_4_v_total";
								}elseif($stroke_play_id=="5"){
								$currentScoreListArray['format_name']="Gross stableford";
									$fieldname="gross_stableford_"; $total_field_name="gross_stableford";
								}elseif($stroke_play_id=="6"){
								$currentScoreListArray['format_name']="Net stableford";
									$fieldname="net_stableford_"; $total_field_name="net_stableford";
								}elseif($stroke_play_id=="7"){
								$currentScoreListArray['format_name']="3/4 Net stableford";
									$fieldname="net_stableford_3_4_"; $total_field_name="3_4_total";

								}elseif($stroke_play_id=="8"){
									$queryPeoriaString = "select is_calc_peoria as calc_peoria from event_table where event_id ='".$eventId."' limit 1";
									$calc_peoria = $this->db->FetchSingleValue($queryPeoriaString);
									$currentScoreListArray['format_name']="Peoria";
									$fieldname="score_entry_";
									if($calc_peoria==1){
									$total_field_name="prioria_value";//"prioria_value";
									}else{
									$total_field_name="gross_score";	
									}
								}elseif($stroke_play_id=="9"){
									$queryPeoriaString = "select is_calc_double_peoria as calc_peoria from event_table where event_id ='".$eventId."'";
									$calc_peoria = $this->db->FetchSingleValue($queryPeoriaString);
									
									$currentScoreListArray['format_name']="Double Peoria";
									$fieldname="score_entry_";
									if($calc_peoria==1){
									$total_field_name="double_prioria_value";//"prioria_value";
									}else{
									$total_field_name="gross_score";	
									}
									$fieldname="score_entry_"; 
								}else{
									$currentScoreListArray['format_name']="Gross Strokeplay";
									$fieldname="score_entry_"; $total_field_name="gross_score";
								}
								$player_hole_score=array();
								
								//$orderstr = ($spot_order == 'ASC') ?  "order by if((full_feet > -1.0) ,0,1)" : ('order by full_feet '.$spot_order);
								$ordfval = ($spot_order == 'ASC') ? '-full_feet' : 'full_feet';
								$ordfval = ($spot_order == 'ASC') ? 'full_feet' : 'full_feet';
								//$orderstr = ('order by  '.$ordfval.' '.$spot_order);
								$orderstr = ('order by  '.$ordfval.' desc');
								
								$defval = ($spot_order == 'ASC') ? '1000000' : '-1';
								//$orderstr = ('order by if((full_feet == -1) ,2,1) '.$spot_order);
								
								if($stroke_play_id=="4" || $stroke_play_id=="7"){
								
									if($is_spot_hole_number >0){
										
										$queryString = "SELECT t.score_entry_".$is_spot_hole_number.",f.closest_feet_".$is_spot_hole_number." as feet,IF(floor(i.closest_inch_".$is_spot_hole_number.") = '-1', {$defval}, floor(i.closest_inch_".$is_spot_hole_number.")) as full_feet,f.player_id,t.handicap_value_3_4 as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id ".$orderstr."";
}else{
										$queryString = " select t.player_id,t.handicap_value_3_4 as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played, "; 
								    $queryString .= "t.".$total_field_name." as total";
								    $queryString .= " from event_score_calc
 t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.current_position asc,t.no_of_holes_played desc";
									}
								}elseif($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){									
							       if($is_spot_hole_number >0){

									$queryString = "SELECT t.score_entry_".$is_spot_hole_number.",f.closest_feet_".$is_spot_hole_number." as feet,IF(floor(i.closest_inch_".$is_spot_hole_number.") = '-1', {$defval}, floor(i.closest_inch_".$is_spot_hole_number.")) as full_feet,f.player_id,t.calculated_handicap as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id ".$orderstr."";

									}else{
									$queryString = "select t.player_id,t.calculated_handicap as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played, "; 
									$queryString .= "t.".$total_field_name." as total";
									$queryString .= " from event_score_calc
									t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.current_position asc,t.no_of_holes_played desc";
									}
							}else{
								
								$cp_str = ($stroke_play_id=="2" && $original_format != $stroke_play_id) ? "current_position_gross" : "current_position";
								$lb_str = ($stroke_play_id=="2" && $original_format != $stroke_play_id) ? "lb_display_string_gross" : "lb_display_string";
								
									if($is_spot_hole_number >0){

									$queryString = "SELECT t.score_entry_".$is_spot_hole_number.",f.closest_feet_".$is_spot_hole_number." as feet,IF(floor(i.closest_inch_".$is_spot_hole_number.") = '-1', {$defval}, floor(i.closest_inch_".$is_spot_hole_number.")) as full_feet,f.player_id,t.handicap_value,g.full_name,CONCAT(t.".$lb_str.",t.".$cp_str.") as current_position,t.no_of_holes_played as no_of_hole_played,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id ".$orderstr."";

									}else{
									$queryString = "select t.player_id,t.handicap_value,g.full_name,CONCAT(t.".$lb_str.",t.".$cp_str.") as current_position,t.no_of_holes_played as no_of_hole_played, "; 
									$queryString .= "t.".$total_field_name." as total";
									$queryString .= " from event_score_calc
									t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.{$cp_str} asc,t.no_of_holes_played desc";
									}
								} //echo $queryString;die;
								$recordSetPlayerScore = $this->db->FetchQuery($queryString); //echo '<pre>';print_r($recordSetPlayerScore);die;
								foreach($recordSetPlayerScore as $i=>$rowValues)
									{
										$spoArr = explode('.',$rowValues['feet']);
										$rowValues['feet'] = is_numeric($spoArr[0]) ? $spoArr[0] : '-1';
										$rowValues['inches'] = (isset($spoArr[1]))?$spoArr[1]:'';
										if($rowValues['total']!='-' && $rowValues['feet'] == '-1') {
											$rowValues['inches'] = '';
										}
										$totalscore=$rowValues['total'];
										$rowValues['full_name']=ucfirst($rowValues['full_name']);
										if($stroke_play_id=="2" || $stroke_play_id=="3" || $stroke_play_id=="4" || $stroke_play_id=="8" || $stroke_play_id=="9"){
											if($rowValues['no_of_hole_played'] >0){
												if($totalscore==0){
												$rowValues['total'] ="Even";
											}elseif($totalscore > 0){
												$rowValues['total'] ="+".$totalscore;//"+".(int)$totalscore;
											}else{
												$rowValues['total'] =$totalscore;
											}
											}else{
												$rowValues['total'] ='-';
											}
										}
										if($is_spot_hole_number <= 0){
										if(($rowValues['no_of_hole_played']==0) || ($rowValues['current_position']==999))
                                        {
                                             $rowValues['current_position'] ="-";
                                        }
										
                                        else
                                        {
$rowValues['current_position'] =$rowValues['current_position'];
                                        }
										}
                                        $player_hole_score[] = $rowValues;
										
										
									}
									
									if($is_spot_hole_number > 0) {
										$player_hole_score_temp = $player_hole_score;
										$player_hole_score_temp1 = $player_hole_score_temp;
										$player_hole_score = array();
										//print_r($player_hole_score_temp);die;
										foreach($player_hole_score_temp as $current_index=>$hdata) {
											$last_index = $current_index - 1;
											$current_row_no = $current_index + 1;
											$arr_t = array();
											$arr_t = $hdata;
											//echo 'before position : '.$arr_t['current_position'];
											$spot_hole_score = $arr_t['score_entry_'.$is_spot_hole_number];
											if($arr_t['feet'] == '-1' && ($arr_t['inches'] == '' || $arr_t['inches']=='00' || $arr_t['inches']=='0')) {
											    $arr_t['inches'] = '';
											}
											if($current_index == 0) {
												if($spot_hole_score <= 0) {
													$arr_t['current_position'] = '-';
												}
												else {
													if(is_numeric($arr_t['full_feet']) && ($arr_t['full_feet'] > -1.0 && $arr_t['full_feet']>0)) {
														$arr_t['current_position'] = ($arr_t['current_position']=='0') ? '-' : $current_row_no;
													}
													else {
														$arr_t['current_position'] = '-';
													}
												}
											}
											else {
												if($spot_hole_score <= 0) {
													$arr_t['current_position'] = '-';
												}
												else {
													if(is_numeric($arr_t['full_feet']) && ($arr_t['full_feet'] > -1.0 && $arr_t['full_feet']>0)) {
														$last_pos = $player_hole_score[$last_index]['current_position'];
														
														$last_pos_without_t = str_replace('T','',$player_hole_score[$last_index]['current_position']);
														
														$last_score = $player_hole_score[$last_index]['full_feet'];
														
														if($last_score == $arr_t['full_feet']) {
															$player_hole_score[$last_index]['current_position'] = 'T'.$last_pos_without_t;
															$arr_t['current_position'] = 'T'.$last_pos_without_t;
														}
														else {
															$arr_t['current_position'] = $current_row_no;
														}
													}
													else {
														$arr_t['current_position'] = '-';
													}
												}
											}
											
											
											/*
											if($current_index == 0) {
												$arr_t['current_position'] = ($arr_t['current_position'] == 0) ? '-' : $current_row_no;
											}
											else {
												$last_pos = $player_hole_score[$last_index]['current_position'];
												$last_pos_without_t = str_replace('T','',$player_hole_score[$last_index]['current_position']);
												$last_score = $player_hole_score[$last_index]['full_feet'];
												//echo 'last_pos : '.$last_pos.' ___ last score : '.$last_score.'<br/>';
												if($last_score != $hdata['full_feet']) {
													$arr_t['current_position'] = ($arr_t['current_position'] == 0) ? '-' : $current_row_no;
												}
												elseif($last_score == $hdata['full_feet']) {
													if(substr($last_pos,0,1) != 'T') {
														$player_hole_score[$last_index]['current_position'] = ($arr_t['current_position'] == 0) ? '-' : 'T'.$last_pos_without_t;
													}
													$arr_t['current_position'] = ($arr_t['current_position'] == 0) ? '-' : 'T'.$last_pos_without_t;
												}
												echo '<br/> after position : '.$arr_t['current_position'];
											}
											*/
											$player_hole_score[$current_index] = $arr_t;
										}
										//print_r($player_hole_score); die;
										//$player_hole_score = array_values($player_hole_score);
									}
									
								$currentScoreListArray['player_score'] = $player_hole_score;			
							if($type == 1){
								$fdata = $player_hole_score;
							}else{
							   $fdata['status'] = '1';
							   $fdata['data'] = $currentScoreListArray;
							   $fdata['message'] = 'Leaderboard Data';
							}
							}elseif($is_started=="2"){
								if($type == 1){
									$fdata['message'] = 'Event deleted.';
								}else{
									$fdata['status'] = '0';
									$fdata['message'] = 'Event deleted.';
								}
								
							
							}else{
								if($type == 1){
									 $fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date)).' '.date("h:i",strtotime($event_start_time))."";
								}else{
									$fdata['status'] = '0';
									 $fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date)).' '.date("h:i",strtotime($event_start_time))."";
								}
							
							   }
						}else{
							if($type == 1){
									 $fdata['message'] = "Event not exists in database.";
								}else{
									$fdata['status'] = '0';
									 $fdata['message'] = "Event not exists in database.";
								}
							
						}
			}else{
				if($type == 1){
									  $fdata['message'] = "Required fields not found.";
								}else{
									$fdata['status'] = '0';
									 $fdata['message'] = "Required fields not found.";
								}
			}
return $fdata ; 			
		}
		
		function getLeaderBoardData($data){
		
		$fdata =$ldata=$is_spot= array();
		$event_id = (isset($data['event_id']) && $data['event_id'] >0 )?$data['event_id']:0;
		$format_id = (isset($data['format_id']) && $data['format_id'] >0 )?$data['format_id']:0;
		if($event_id <= 0){
			$fdata['status'] = '0';
			$fdata['status'] = 'Required Field Empty ';
		}else{
			
			$queryString = "select event_id,format_name,format_id,golf_course_name,event_name,is_spot from event_list_view where event_id ='".$event_id."'";
			$result = $this->db->FetchRow($queryString);
				$x=$p=array();		
			if(count($result) > 0){
				if($result['is_spot'] == 1){
					$a = "select type,hole_number from event_is_spot_tbl where event_id ='".$event_id."' order by type ASC,hole_number ASC ";
					$b = $this->db->FetchQuery($a);
					
					if(count($b)>0){
						foreach($b as $i=>$c){
							if($c['type'] == 1){
								$c['dislay_data'] = 'Closest To Pin # '.$c['hole_number'].'';
							}elseif($c['type'] == 2){
								$c['dislay_data'] = 'Straight Drive # '.$c['hole_number'].'';
							}else{
								$c['dislay_data'] = 'Long Drive # '.$c['hole_number'].'';
							}
							$c['is_spot_type'] = $c['type'];
							$p['hole_number'] = $result['format_id'];
							$p['dislay_data'] = $result['format_name'];
							$p['is_spot_type'] = '0';
							
							$is_spot[] = $c;
						}
					//$is_spot[]  =$p;
					//$is_spot[]  =$x;
						//$is_spot['format_name'][] = $result['format_name'];
						$ldata['is_spot'] =$is_spot;
							
					}else{
$ldata['is_spot'] =array();
}
					
				}else{
				$ldata['is_spot'] =$result['format_id'];
				}
				
				$ldata['format_id'] = $result['format_id'];
				$ldata['event_id'] = $result['event_id'];
$ldata['is_spot_value'] = $result['is_spot'];
				$ldata['format_name'] = $result['format_name'];
				$ldata['golf_course_name'] = $result['golf_course_name'];
				$ldata['event_name'] = $result['event_name'];
				$ldata['leader_board']=$this->getLeaderBoard(array('event_id'=>$result['event_id'],'type'=>1));
				
				$fdata['status'] = '1';
				$fdata['data'] = $ldata;
				$fdata['status'] = 'Success';
				
			}else{
				$fdata['status'] = '0';
				$fdata['status'] = 'Event Not exist';
			}
		}
		return $fdata ;
                
                
	}
        
        
    function getStandingForNewGameFormat($total_num_hole,$stroke_play_id,$eventId,$event_admin_id){
        $scoredata=$this->getScoreBoard(array(),$eventId,$event_admin_id);
        $counter=0;
        $score_data=array();$current_standing=array();

        /*if(isset($scoredata['data']) && count($scoredata['data'])>0){
           foreach($scoredata['data'] as $key=>$value){
           $counter++;
           if(count($scoredata['data'])==$counter){
               $current_standing=$scoredata['data'][$key];
           }
        }
        //$score_data=$scoredata['data'];

        $counter=($counter+1);
        }
        for($i=$counter;$i<=$total_num_hole;$i++){
        $score_data[]=array('hole_number'=>$i,'score_value'=>'','winner'=>'0','color'=>'');
        }*/
       //print_r($scoredata);die;
$cnt=0;$score_data_hole=array();
$pqueryString = " select hole_number  from event_score_calc where event_id = ".$eventId;
	$currenthole  = $this->db->FetchSingleValue($pqueryString);
           //echo $total_num_hole.'---------'.$currenthole;die;
        //for($i=1;$i<=$total_num_hole;$i++){
        for($i=1;$i<=18;$i++){
			if($i==10 && $total_num_hole==9) {
				$cnt=0;
			}
            if($stroke_play_id==12){
//print_r($scoredata['data'][$cnt][$currenthole]);
                if(isset($scoredata['data'][$cnt][$currenthole]) && count($scoredata['data'][$cnt][$currenthole])>0){
                     $current_standing=$scoredata['data'][$cnt];    
                }
            }
			elseif($stroke_play_id==10) {
				if(isset($scoredata['data'][$i]['hole_number']) && $scoredata['data'][$i]['hole_number'] == $i) {
					$score_data_hole[$i]=$scoredata['data'][$i];
					if($currenthole==$i){
						$current_standing[0]=$score_data_hole[$i];
					}
				}
				
			}
			elseif($stroke_play_id==13) {
				if(isset($scoredata['data'][$i]['hole_number']) && $scoredata['data'][$i]['hole_number'] == $i) {
					$score_data_hole[$i]=$scoredata['data'][$i];
					if($currenthole==$i){
						$current_standing[0]=$score_data_hole[$i];
					}
				}
				
			}
			elseif($stroke_play_id==14) {
				if(isset($scoredata['data'][$i]['hole_number']) && $scoredata['data'][$i]['hole_number'] == $i) {
					$score_data_hole[$i]=$scoredata['data'][$i];
					if($currenthole==$i){
						$current_standing[0]=$score_data_hole[$i];
					}
				}
				
			}
			elseif($stroke_play_id==11) {
				if(isset($scoredata['data'][$i]['hole_number']) && $scoredata['data'][$i]['hole_number'] == $i) {
					$score_data_hole[$i]=$scoredata['data'][$i];
					if($currenthole==$i){
						$current_standing=$score_data_hole[$i];
					}
				}
				
			}
			else{
                if(isset($scoredata['data'][$cnt][$i]) && count($scoredata['data'][$cnt][$i])>0){
                //$h=$scoredata['data'][$cnt]['hole_number'];
                $h=$i;
                $score_data_hole[$h]=$scoredata['data'][$cnt][$i];
                    if($currenthole==$h){
                    //print_r($score_data_hole[$h]);
                        $current_standing[]=$score_data_hole[$h];    
                    }
                }
            }
if(isset($score_data_hole[$i]) && count($score_data_hole[$i]) > 0){
$score_data[]=$score_data_hole[$i];
}else{
 $score_data[]=array('hole_number'=>$i,'score_value'=>'','winner'=>'0','color'=>'');
}
          $cnt++;  
       
        }
//print_r($score_data);die;
        if($stroke_play_id==12){
            if(count($current_standing) > 0){
            foreach($current_standing as $key=>$val){
                $current_standing1=$val; 
            }
            //print_r($current_standing1);
            foreach($current_standing1 as $key1=>$val1){
                if($event_admin_id==$val1['player_id']){
                $current_standingadmin[]=$val1;
                }else{
                $current_standingplayer[]=$val1;    
                }
            }
            $current_standing1=  array_merge($current_standingadmin,$current_standingplayer);
            $current_standing=array();
            $current_standing[]['last']=$current_standing1;
            }
        }
//print_r($score_data);
//print_r($current_standing);die;
        return array('standing'=>$score_data,'current'=>$current_standing);  
    }
	
	function getLatestFullScore($data){
		$fdata =  $currentScoreListArray = array();
		$eventId =  $data['event_id'];
		$player_id =  (isset($data['player_id']) && $data['player_id'] >0)?$data['player_id']:0;
		if($eventId > 0){
			$currentScoreListArray['event_id'] = $eventId;
			$queryString = "select golf_course_id,DATE(event_start_date_time) as event_start_date_time,event_start_time,admin_id,format_id,total_hole_num,is_started,golf_course_name,event_name,hole_start_from from event_list_view where event_id ='".$eventId."' ";
			
			$result = $this->db->FetchRow($queryString);
			
			$event_admin_id = isset($result['admin_id']) ? $result['admin_id'] : 0;
			
			if($event_admin_id > 0) {
				
				if(isset($result['format_id']) && $result['format_id']<10) {
					return $this->getLatestFullScoreOld($data);
				}
				
				$golf_course_id = $result['golf_course_id'];
				$event_start_date_time = $result['event_start_date_time'];
				$event_start_time = $result['event_start_time'];
				$stroke_play_id = $result['format_id'];
				$currentScoreListArray['event_stroke_play_id'] = $stroke_play_id;
				$currentScoreListArray['event_admin_id'] = $event_admin_id;
				$queryString = "select count(player_id) as total_player from event_player_list where event_id ='".$eventId."' limit 1";
				$total_player = $this->db->FetchSingleValue($queryString);
				$currentScoreListArray['total_player'] = $total_player;
				$total_num_hole = $result['total_hole_num'];
				$is_started = $result['is_started'];
				$golf_course_name = $result['golf_course_name'];
				$event_name = $result['event_name'];
				$hole_start_from = $result['hole_start_from'];
				$currentScoreListArray['total_num_hole'] = $total_num_hole;
				$currentScoreListArray['hole_start_from'] = $hole_start_from;
				$currentScoreListArray['golf_course_name'] = $golf_course_name;
				$currentScoreListArray['event_name'] = $event_name;
				
				if($is_started=="3" || $is_started=="4") {
					$queryString = "select max(hole_number) as hole_number from temp_event_score_entry where event_id ='".$eventId."' limit 1";
					$hole_number = $this->db->FetchSingleValue($queryString);
					$currentScoreListArray['hole_number'] = $hole_number;
					$qryString ="";
					$is_handicap_gain='';
					
					if($stroke_play_id=="2"){
						$fieldname="score_entry_"; $total_field_name="gross_score";
					}
					elseif($stroke_play_id=="3"){
						$fieldname="score_entry_"; $total_field_name="gross_score";$is_handicap_gain='1';
						//$fieldname="net_";  $total_field_name="net_score";$is_handicap_gain='1';
					}
					elseif($stroke_play_id=="4"){
						$fieldname="score_entry_"; $total_field_name="gross_score";$is_handicap_gain='1';
						//$fieldname="net_stableford_3_4_v_"; $total_field_name="3_4_v_total";$is_handicap_gain='1';
					}
					elseif($stroke_play_id=="5"){
						$fieldname="score_entry_"; $total_field_name="gross_stableford";
					}
					elseif($stroke_play_id=="6"){
						$fieldname="score_entry_"; $total_field_name="net_stableford";$is_handicap_gain='1';
					}
					elseif($stroke_play_id=="7"){
						$fieldname="score_entry_"; $total_field_name="3_4_total";$is_handicap_gain='1';
					}
					elseif($stroke_play_id=="8"){
						$fieldname="score_entry_"; $total_field_name="prioria_value";//"prioria_value";
					}
					elseif($stroke_play_id=="9"){
						$fieldname="score_entry_"; $total_field_name="double_prioria_value";//"double_prioria_value";
					}
					else{
						$fieldname="score_entry_"; $total_field_name="gross_score";
					}
					
					// new formats score data
					$is_team_game=false;
					if($stroke_play_id>="10" && $stroke_play_id<="14") {
						//check is team 
						$sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by t.team_profile_id asc,u.user_id asc";  
						
						$sqlresult1  = $this->db->FetchQuery($sqlQuery1);
						
						$team_id=$team_id_name=$player_idArr=array();
						if(count($sqlresult1) >0){
							
							foreach($sqlresult1 as $i=>$e) {
								if($player_id > 0 && $player_id!=$e['player_id']) {continue;}
								$player_idArr[]=$e['player_id'];
								if($e['team_id']>0 && !in_array($e['team_id'],$team_id)) {
									$team_id[]=$e['team_id'];
								}
								if(trim($e['team_display_name'])!='' && !in_array($e['team_display_name'],$team_id_name)) {
									$team_id_name[]=$e['team_display_name'];
								}
							}
									
							//print_r($team_id);
							//print_r($team_id_name);
							//print_r($player_idArr);
							$uniqueteam=array_unique($team_id);
							$team_id_name=array_unique($team_id_name);
							$is_team_game = (count($uniqueteam) > 0) ? true : false;
							$currentScoreListArray['is_team'] =($is_team_game) ? "1" : "0";
							
							
							$queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name,t.start_from_hole,t.no_of_holes_played ";
							$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' order by p.team_id asc,t.player_id ASC ";
							//echo $queryString;
							$teamrec = $this->db->FetchQuery($queryString); 
							$teamrec_count = count($teamrec);
							//$currentScoreListArray['no_of_holes_played'] = $teamrec[0]['no_of_holes_played'];
							$teamplayerarray1 = $teamplayerarray2 = array();
							//print_r($teamrec);die;
							foreach($teamrec as $t=>$row) {
								if($player_id > 0 && $player_id!=$row['player_id']) {continue;}
								
								$currentScoreListArray['no_of_holes_played'] = $row['no_of_holes_played'];
								
								if($is_team_game) {
									if($uniqueteam[0]==$row['team_id']) {
										$teamplayerarray1[]=array(
											'player_id'=>$row['player_id'],
											'name'=>$row['full_name'],
											'handicap_value'=>$row['handicap_value'],
											'color'=>$this->getColorCode('red')
										);
									}
									else{
										$teamplayerarray2[]=array(
											'player_id'=>$row['player_id'],
											'name'=>$row['full_name'],
											'handicap_value'=>$row['handicap_value'],
											'color'=>$this->getColorCode('blue'),
										);
									}
								}
								else {
									
									if($row['player_id'] == $event_admin_id) {
										$color_order = 1;
									}
									elseif($t==0 && $row['player_id'] != $event_admin_id) {
										$color_order = 2;
									}
									else {
										$color_order++;
										if((($t+1) == $teamrec_count) && $color_order < $teamrec_count) {
											$color_order = $teamrec_count;
										}
									}
									
									
									$color_order = ($color_order > $teamrec_count) ? $teamrec_count : $color_order;
									
									
									
									if($t == 0) {
										$teamplayerarray1[]=array(
											'player_id'=>$row['player_id'],
											'name'=>$row['full_name'],
											'handicap_value'=>$row['handicap_value'],
											'color'=>$this->setColorForPlayer(0,'',$color_order)
										);
									}
									else {
										if($teamrec_count == 2) {
											$teamplayerarray2[]=array(
												'player_id'=>$row['player_id'],
												'name'=>$row['full_name'],
												'handicap_value'=>$row['handicap_value'],
												'color'=>$this->setColorForPlayer(0,'',$color_order)
											);
										}
										else {
											$teamplayerarray1[]=array(
												'player_id'=>$row['player_id'],
												'name'=>$row['full_name'],
												'handicap_value'=>$row['handicap_value'],
												'color'=>$this->setColorForPlayer(0,'',$color_order)
											);
										}
									}
									$color_order = ($row['player_id'] == $event_admin_id && $color_order>1) ? ($color_order-1) : $color_order;
									
									
									
									// change order
									if($teamrec_count > 1) {
										if($teamrec_count == 2) {
											if($teamplayerarray2[0]['player_id'] == $event_admin_id) {
												$xz = $teamplayerarray1;
												$teamplayerarray1 = $teamplayerarray2;
												$teamplayerarray2 = $xz;
											}
										}
										else {
											$adm_arr = $oth_arr = array();
											foreach($teamplayerarray1 as $a=>$xz) {
												if($xz['player_id'] == $event_admin_id) {
													$adm_arr[0] = $xz;
												}
												else {
													$oth_arr[] = $xz;
												}
											}
											$teamplayerarray1 = array_merge($adm_arr,$oth_arr);
										}
									}
								}
								if(isset($row['start_from_hole']) && is_numeric($row['start_from_hole'])) {
									$currentScoreListArray['hole_start_from'] = $row['start_from_hole'];
								}
							}
							$team_a_name = (isset($team_id_name[0]) && trim($team_id_name[0])!='') ? $team_id_name[0] : '';
							$team_b_name = (isset($team_id_name[1]) && trim($team_id_name[1])!='') ? $team_id_name[1] : '';
							$teamarray['team_a']=array('team_name'=>$team_a_name,'player_list'=>$teamplayerarray1);
							$teamarray['team_b']=array('team_name'=>$team_b_name,'player_list'=>$teamplayerarray2);
							$currentScoreListArray['front_9_data'] =$teamarray;
							$currentScoreListArray['back_9_data'] =$teamarray;
							//print_r($currentScoreListArray);
							$standingdata=$this->getStandingForNewGameFormat($total_num_hole,$stroke_play_id,$eventId,$event_admin_id);
							//print_r($standingdata);die;
							$currentScoreListArray['current_standing'] = array();
							if(!isset($standingdata['current'][0])) {
								if(count($standingdata['current'])>0) {
									$currentScoreListArray['current_standing'][0]=$standingdata['current'];
								}
							}
							else {
								if(count($standingdata['current'])>0) {
									$currentScoreListArray['current_standing']=$standingdata['current'];
								}
							}
							if($stroke_play_id!=12){
								//$currentScoreListArray['standings']=$standingdata['standing'];
								foreach($standingdata['standing'] as $a=>$b) {
									if(!is_array($b['score_value']) && trim($b['score_value']) == '') {
										$b['score_value'] = '-';
									}
									if(trim($b['color']) == '') {
										$b['color'] = $this->getColorCode('black');
									}
									if($b['hole_number'] > 9) {
										$currentScoreListArray['back_9_data']["standings"][] = $b;
									}
									else {
										$currentScoreListArray['front_9_data']["standings"][] = $b;
									}
								}
							}
							else {
								if(isset($currentScoreListArray['current_standing'][0]['last']) && count($currentScoreListArray['current_standing'][0]['last'])>0) {
									$cs = $currentScoreListArray['current_standing'][0]['last'];
									$nz = array();
									foreach($teamplayerarray1 as $zc=>$bv) {
										foreach($cs as $y=>$q) {
											if($q['player_id'] == $bv['player_id']) {
												$nz[$bv['player_id']] = $q;
												break;
											}
										}
									}
									$currentScoreListArray['current_standing'][0]['last'] = array_values($nz);
								}
							}
							$player_hole_score=array();$player_hole_scoreadmin=array();								
							if($hole_start_from==10) {
								$total_num_hole=18;
							}
							$parqryString='';
							if ($total_num_hole) {
								for($ctr = ($hole_start_from-1); $ctr < $total_num_hole;  $ctr++) {
									$ctrV = $ctr+1;
									$parqryString .= 'par_value_'.$ctrV. ','.'hole_index_'.$ctrV;
									if($is_team_game) {
										$qryString .= "net_".$ctrV.",score_entry_".$ctrV.",min(t.".$fieldname.$ctrV.") as hole_num_".$ctrV;
									}
									else {
										$qryString .= "net_".$ctrV.",score_entry_".$ctrV.",t.".$fieldname.$ctrV." as hole_num_".$ctrV;
									}

									if($stroke_play_id=="5" || $stroke_play_id=="6" || $stroke_play_id=="7"){
										$qryString .= ",t.net_".$ctrV.", t.score_entry_".$ctrV." as gross_score_".$ctrV;											
									}
									if($ctr != $total_num_hole-1) {
										$qryString .= ","; $parqryString.=",";
									}
								}
								if($stroke_play_id=="5" || $stroke_play_id=="6" || $stroke_play_id=="7") {
									$qryString .= ", t.gross_score";	
								}
								$parqry=' select '.$parqryString.' from golf_hole_index where golf_course_id ='.$golf_course_id.''; 
								$rowparValues = $this->db->FetchRow($parqry);
								$parno=0;
								
								$pl = (isset($player_id) && $player_id > 0)?' AND t.player_id ='.$player_id.'':'';
								
								$scrnqryString_arr = array();
								
								for($i=1;$i<=18;$i++) {
									$scrnqryString_arr[] = 'score_entry_'.$i;
								}
								
								$scrnqryString = count($scrnqryString_arr)>0 ? implode(',',$scrnqryString_arr) : '';
								
								$parqry=' select '.$scrnqryString.' from event_score_calc t where t.event_id ='.$eventId.' '.$pl.' limit 1'; 
								$rowscrnValues = $this->db->FetchRow($parqry);
								
								for($ctr = $hole_start_from; $ctr <= $total_num_hole;  $ctr++) {
									$parno=$ctr;
									if($parno > 9) {
										$currentScoreListArray['back_9_data']["par_value"]['par_value_'.$parno] = ($rowscrnValues['score_entry_'.$parno]>0) ? $rowparValues['par_value_'.$parno] : '-';
										$currentScoreListArray['back_9_data']["hole_index"]['hole_index_'.$parno] = $rowparValues['hole_index_'.$parno];
									}
									else {
										$currentScoreListArray['front_9_data']["par_value"]['par_value_'.$parno] = ($rowscrnValues['score_entry_'.$parno]>0) ? $rowparValues['par_value_'.$parno] : '-';
										$currentScoreListArray['front_9_data']["hole_index"]['hole_index_'.$parno] = $rowparValues['hole_index_'.$parno];
									}
								}
								
								
								if($stroke_play_id=="4" || $stroke_play_id=="7") {
									$queryString = " select t.player_id,t.handicap_value_3_4 as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);
								}
								elseif($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14") {
									if($is_team_game){
										$queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
										$queryString .= $qryString; 
										$queryString .= ", t.".$total_field_name." as total";
										$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." group by t.player_id order by p.team_id asc ";
										//echo "<br>".$queryString;
									}
									else {
										$queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
										$queryString .= $qryString; 
										$queryString .= ", t.".$total_field_name." as total";
										$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." order by t.event_score_calc_id asc";
									}
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);										
								}
								else {
									$queryString = " select t.player_id,t.handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from  event_score_calc
									t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);
								}
								//print_r($recordSetPlayerScore);die;
								$event = new Events;
								$counter =0;$no_of_eagle=0;$no_of_birdies=0;$no_of_pars=0;$no_of_bogeys=0;$no_of_double_bogeys=0;
								$total_front_9_postion=0;$total_back_9_postion=0;$total_postion=0;$player_counter=0;
								foreach($recordSetPlayerScore as $i=>$rowValues) {
									$handi_counter=0; $player_counter++;
									for($ctr = $hole_start_from; $ctr <= $total_num_hole;  $ctr++){
										$handi_counter=$ctr;
										if($is_handicap_gain=="1"){
											if($stroke_play_id=="4" || $stroke_play_id=="7") {
												if($rowValues['handicap_value_3_4'] >=$rowparValues['hole_index_'.$handi_counter]){
													$rowValues['is_handicap_gain_'.$handi_counter]="Stroke Play";
												}
												else{
													$rowValues['is_handicap_gain_'.$handi_counter]="";	
												}
											}
											else {
												if($rowValues['handicap_value'] >=$rowparValues['hole_index_'.$handi_counter]){
													$rowValues['is_handicap_gain_'.$handi_counter]="Stroke Play";
												}
												else{
													$rowValues['is_handicap_gain_'.$handi_counter]="";	
												}	
											}
										}
										else {
											$rowValues['is_handicap_gain_'.$handi_counter]="";
										}	
										if(isset($player_id) && $player_id > 0) {
											$queryString = " select calculated_position from event_score_calc_position where event_id ='".$eventId."' and player_id='".$rowValues['player_id']."' and hole_number='".$handi_counter."'";
											$position = $this->db->FetchSingleValue($queryString);
											if($rowValues['score_entry_'.$handi_counter] > 0) {												
												if($position==0) {
													$rowValues['position_'.$handi_counter]='E';	
												}
												else{
													$rowValues['position_'.$handi_counter]=($position >0)?'+'.$position:$position;
												}
											}
											else {
												$rowValues['position_'.$handi_counter]='-';
											}
											if($handi_counter <=9){
												$total_front_9_postion+=$position;
											}
											else{
												$total_back_9_postion+=$position;	
											}
											$total_postion+=$position;

										}
										$color='#ffffff';
										if($rowValues['hole_num_'.$handi_counter] > 0) {
											$difference =  $rowValues['hole_num_'.$handi_counter] - $rowparValues['par_value_'.$handi_counter];
											if( $difference <= -2){
												$no_of_eagle = $no_of_eagle + 1;
												$color='#f4aa43';
											}
											else if( $difference == -1){
												$no_of_birdies = $no_of_birdies + 1;
												$color='#0a5c87';
											}
											else if( $difference == 0){
												$no_of_pars = $no_of_pars + 1;
												$color='#325604';
											}
											else if( $difference == 1){
												$no_of_bogeys = $no_of_bogeys + 1;
												$color='#939494';
											}
											else if( $difference >= 2){
												$no_of_double_bogeys = $no_of_double_bogeys + 1;
												$color='#000000';
											}
											else{
												
											}	
										}
										$nam = explode(' ',$rowValues['full_name']);
										$namf = (isset($nam[0]) && $nam[0] !='')?$nam[0]:'';
										$naml = (isset($nam[1]) && $nam[1] !='')?$nam[1]:'';
										if(in_array($last, $rowValues)) {
											$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 2):substr($namf, 0, 2);
											$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
										}
										else{
											$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 1):substr($namf, 0, 2);
											$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
										}
										$rowValues['hole_color_'.$handi_counter]=$color;				
										unset($rowValues['score_entry_'.$handi_counter]);
									}
									$rowValues['short_name'] = $last;			
									if($is_team_game) {
										//$rowValues['short_name']=($uniqueteam[0]==$rowValues['team_id'])?$team_id_name[0]:$team_id_name[1];
										//$rowValues['full_name']=($uniqueteam[0]==$rowValues['team_id'])?$team_id_name[0]:$team_id_name[1];
										$rowValues['player_color_code']=$this->setColorForPlayer(1,(($uniqueteam[0]==$rowValues['team_id'])?"Team A":"Team B"),0);
										//unset($rowValues['player_id']);
										unset($rowValues['handicap_value']);
										unset($rowValues['handicap_value_3_4']);
									}
									else { //echo count($recordSetPlayerScore).''.$player_counter.'<br/>';
										if($rowValues['player_id'] == $event_admin_id) {
											$player_counter1 = 1;$player_counter--;
										}
										else {
											$player_counter1 = $player_counter+1;
											$player_counter = $player_counter1;
											$player_counter1 = ($player_counter1 > count($recordSetPlayerScore)) ? count($recordSetPlayerScore) : $player_counter1;
										}
										$rowValues['player_color_code']=$this->setColorForPlayer(0,'',$player_counter1);						
									}
									if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){
										// do nothing
									}
									else{
										unset($rowValues['player_color_code']);  
									}
									if($event_admin_id==$rowValues['player_id']){
										//$player_hole_scoreadmin[$rowValues['player_id']] = $rowValues ;
										$player_hole_score[$rowValues['player_id']] = $rowValues ;
									}
									else{
										$player_hole_score[$rowValues['player_id']] = $rowValues ;                                            
									}
								}//die;
							} // temp
							//$player_hole_scorearray=array_merge($player_hole_scoreadmin,$player_hole_score);
							$player_hole_scorearray=$player_hole_score;
							//print_r($player_hole_scorearray);die;
							$all_score_for_lowest_arr = array();
							if(isset($currentScoreListArray['front_9_data']['team_a']['player_list']) && is_array($currentScoreListArray['front_9_data']['team_a']['player_list']) && count($currentScoreListArray['front_9_data']['team_a']['player_list'])>0) {
								$front_a_list = $front_a_list_org = $currentScoreListArray['front_9_data']['team_a']['player_list'];
								foreach($front_a_list as $a=>$b) {
									if(isset($player_hole_scorearray[$b['player_id']]) && is_array($player_hole_scorearray[$b['player_id']]) && count($player_hole_scorearray[$b['player_id']])>0) {
										$df = $player_hole_scorearray[$b['player_id']];
										$front_a_list_org[$a]['total'] = isset($df['total']) ? $df['total'] : 0;
										$front_a_list_org[$a]['team_id'] = isset($df['team_id']) ? $df['team_id'] : 0;
										$front_a_list_org[$a]['short_name'] = isset($df['short_name']) ? $df['short_name'] : 0;
										
										for($ii=1;$ii<=9;$ii++) {
											if(isset($df["hole_num_{$ii}"])) {
												$front_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['score'] = ($df["hole_num_{$ii}"]>0) ? $df["hole_num_{$ii}"] : '-';
												if(isset($df["net_{$ii}"])) {
													$all_score_for_lowest_arr[$ii]['team_a'][] = array('score'=>$df["net_{$ii}"],'team'=>'team_a','hole_type'=>'front_9','player_id'=>$b['player_id']);
												}
											}
											if(isset($df["is_handicap_gain_{$ii}"])) {
												$front_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['handicap_gain'] = $df["is_handicap_gain_{$ii}"];
											}
											if(isset($df["hole_color_{$ii}"])) {
												$front_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['color'] = $df["hole_color_{$ii}"];
											}
											$front_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['is_lowest'] = 0;
										}
									}
								}
								$currentScoreListArray['front_9_data']['team_a']['player_list'] = $front_a_list_org;
							}
							
							if(isset($currentScoreListArray['front_9_data']['team_b']['player_list']) && is_array($currentScoreListArray['front_9_data']['team_b']['player_list']) && count($currentScoreListArray['front_9_data']['team_b']['player_list'])>0) {
								$front_b_list = $front_b_list_org = $currentScoreListArray['front_9_data']['team_b']['player_list'];
								foreach($front_b_list as $a=>$b) {
									if(isset($player_hole_scorearray[$b['player_id']]) && is_array($player_hole_scorearray[$b['player_id']]) && count($player_hole_scorearray[$b['player_id']])>0) {
										$df = $player_hole_scorearray[$b['player_id']];
										$front_b_list_org[$a]['total'] = isset($df['total']) ? $df['total'] : 0;
										$front_b_list_org[$a]['team_id'] = isset($df['team_id']) ? $df['team_id'] : 0;
										$front_b_list_org[$a]['short_name'] = isset($df['short_name']) ? $df['short_name'] : 0;
										
										for($ii=1;$ii<=9;$ii++) {
											if(isset($df["hole_num_{$ii}"])) {
												$front_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['score'] = ($df["hole_num_{$ii}"]>0) ? $df["hole_num_{$ii}"] : '-';
												if(isset($df["net_{$ii}"])) {
													$all_score_for_lowest_arr[$ii]['team_b'][] = array('score'=>$df["net_{$ii}"],'team'=>'team_b','hole_type'=>'front_9','player_id'=>$b['player_id']);
												}
											}
											if(isset($df["is_handicap_gain_{$ii}"])) {
												$front_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['handicap_gain'] = $df["is_handicap_gain_{$ii}"];
											}
											if(isset($df["hole_color_{$ii}"])) {
												$front_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['color'] = $df["hole_color_{$ii}"];
											}
											$front_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['is_lowest'] = 0;
										}
									}
								}
								$currentScoreListArray['front_9_data']['team_b']['player_list'] = $front_b_list_org;
							}
							
							if(isset($currentScoreListArray['back_9_data']['team_a']['player_list']) && is_array($currentScoreListArray['back_9_data']['team_a']['player_list']) && count($currentScoreListArray['back_9_data']['team_a']['player_list'])>0) {
								$back_a_list = $back_a_list_org = $currentScoreListArray['back_9_data']['team_a']['player_list'];
								foreach($back_a_list as $a=>$b) {
									if(isset($player_hole_scorearray[$b['player_id']]) && is_array($player_hole_scorearray[$b['player_id']]) && count($player_hole_scorearray[$b['player_id']])>0) {
										$df = $player_hole_scorearray[$b['player_id']];
										$back_a_list_org[$a]['total'] = isset($df['total']) ? $df['total'] : 0;
										$back_a_list_org[$a]['team_id'] = isset($df['team_id']) ? $df['team_id'] : 0;
										$back_a_list_org[$a]['short_name'] = isset($df['short_name']) ? $df['short_name'] : 0;
										
										for($ii=10;$ii<=18;$ii++) {
											if(isset($df["hole_num_{$ii}"])) {
												$back_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['score'] = ($df["hole_num_{$ii}"]>0) ? $df["hole_num_{$ii}"] : '-';
												if(isset($df["net_{$ii}"])) {
													$all_score_for_lowest_arr[$ii]['team_a'][] = array('score'=>$df["net_{$ii}"],'team'=>'team_a','hole_type'=>'back_9','player_id'=>$b['player_id']);
												}
											}
											if(isset($df["is_handicap_gain_{$ii}"])) {
												$back_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['handicap_gain'] = $df["is_handicap_gain_{$ii}"];
											}
											if(isset($df["hole_color_{$ii}"])) {
												$back_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['color'] = $df["hole_color_{$ii}"];
											}
											$back_a_list_org[$a]['hole_score']["hole_num_{$ii}"]['is_lowest'] = 0;
										}
									}
								}
								$currentScoreListArray['back_9_data']['team_a']['player_list'] = $back_a_list_org;
							}
							
							if(isset($currentScoreListArray['back_9_data']['team_b']['player_list']) && is_array($currentScoreListArray['back_9_data']['team_b']['player_list']) && count($currentScoreListArray['back_9_data']['team_b']['player_list'])>0) {
								$back_b_list = $back_b_list_org = $currentScoreListArray['back_9_data']['team_b']['player_list'];
								foreach($back_b_list as $a=>$b) {
									if(isset($player_hole_scorearray[$b['player_id']]) && is_array($player_hole_scorearray[$b['player_id']]) && count($player_hole_scorearray[$b['player_id']])>0) {
										$df = $player_hole_scorearray[$b['player_id']];
										$back_b_list_org[$a]['total'] = isset($df['total']) ? $df['total'] : 0;
										$back_b_list_org[$a]['team_id'] = isset($df['team_id']) ? $df['team_id'] : 0;
										$back_b_list_org[$a]['short_name'] = isset($df['short_name']) ? $df['short_name'] : 0;
										
										for($ii=10;$ii<=18;$ii++) {
											if(isset($df["hole_num_{$ii}"])) {
												$back_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['score'] = ($df["hole_num_{$ii}"]>0) ? $df["hole_num_{$ii}"] : '-';
												if(isset($df["net_{$ii}"])) {
													$all_score_for_lowest_arr[$ii]['team_b'][] = array('score'=>$df["net_{$ii}"],'team'=>'team_b','hole_type'=>'back_9','player_id'=>$b['player_id']);
												}
											}
											if(isset($df["is_handicap_gain_{$ii}"])) {
												$back_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['handicap_gain'] = $df["is_handicap_gain_{$ii}"];
											}
											if(isset($df["hole_color_{$ii}"])) {
												$back_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['color'] = $df["hole_color_{$ii}"];
											}
											$back_b_list_org[$a]['hole_score']["hole_num_{$ii}"]['is_lowest'] = 0;
										}
									}
								}
								$currentScoreListArray['back_9_data']['team_b']['player_list'] = $back_b_list_org;
							}
							
							$currentScoreListArray['front_9_data']['team_a_count'] = 0;
							$currentScoreListArray['front_9_data']['team_b_count'] = 0;
							$currentScoreListArray['back_9_data']['team_a_count'] = 0;
							$currentScoreListArray['back_9_data']['team_b_count'] = 0;
							
							if(isset($currentScoreListArray['front_9_data']['team_a']['player_list']) && is_array($currentScoreListArray['front_9_data']['team_a']['player_list'])) {
								$currentScoreListArray['front_9_data']['team_a_count'] = count($currentScoreListArray['front_9_data']['team_a']['player_list']);
							}
							
							if(isset($currentScoreListArray['front_9_data']['team_b']['player_list']) && is_array($currentScoreListArray['front_9_data']['team_b']['player_list'])) {
								$currentScoreListArray['front_9_data']['team_b_count'] = count($currentScoreListArray['front_9_data']['team_b']['player_list']);
							}
							
							if(isset($currentScoreListArray['back_9_data']['team_a']['player_list']) && is_array($currentScoreListArray['back_9_data']['team_a']['player_list'])) {
								$currentScoreListArray['back_9_data']['team_a_count'] = count($currentScoreListArray['back_9_data']['team_a']['player_list']);
							}
							
							if(isset($currentScoreListArray['back_9_data']['team_b']['player_list']) && is_array($currentScoreListArray['back_9_data']['team_b']['player_list'])) {
								$currentScoreListArray['back_9_data']['team_b_count'] = count($currentScoreListArray['back_9_data']['team_b']['player_list']);
							}
							
							if(isset($currentScoreListArray['front_9_data'])) {
								$e = $currentScoreListArray['front_9_data'];
								unset($currentScoreListArray['front_9_data']);
								$currentScoreListArray['front_9_data'][0] = $e;
							}
							
							if(isset($currentScoreListArray['back_9_data'])) {
								$e = $currentScoreListArray['back_9_data'];
								unset($currentScoreListArray['back_9_data']);
								$currentScoreListArray['back_9_data'][0] = $e;
							}
							
							if(isset($currentScoreListArray['front_9_data']) && ($hole_start_from==10 && $currentScoreListArray['total_num_hole']==9)) {
								$currentScoreListArray['front_9_data'] = array();
							}
							
							if(isset($currentScoreListArray['back_9_data']) && ($hole_start_from==1 && $currentScoreListArray['total_num_hole']==9)) {
								$currentScoreListArray['back_9_data'] = array();
							}
							
							$currentScoreListArray['total_front_9_postion']= $total_front_9_postion; 
							$currentScoreListArray['total_back_9_postion']= $total_back_9_postion; 
							$currentScoreListArray['total_postion']= $total_postion; 				
							$currentScoreListArray['eagle_counter']= ($player_id>0) ? $no_of_eagle : 0; 
							$currentScoreListArray['birdie_counter']= ($player_id>0) ? $no_of_birdies : 0;  
							$currentScoreListArray['par_counter']= ($player_id>0) ? $no_of_pars : 0; 
							$currentScoreListArray['bogey_counter']= ($player_id>0) ? $no_of_bogeys : 0;  
							$currentScoreListArray['doublebogey_counter']= ($player_id>0) ? $no_of_double_bogeys : 0; 
							//$currentScoreListArray['player_hole_score']= $player_hole_scorearray; 
							$fdata['status'] = '1';
							//$fdata['data'] = $currentScoreListArray;
							$fdata['message'] = 'Full Score';
							
							// calculate lowest score
							//print_r($all_score_for_lowest_arr);die;
							// for matchplay and autopress
							if($stroke_play_id=="10" || $stroke_play_id=="11") {
								for($i=1;$i<=18;$i++) {
									if(isset($all_score_for_lowest_arr[$i]) && is_array($all_score_for_lowest_arr[$i]) && count($all_score_for_lowest_arr[$i])>0) {
										
										$hole_number = $i;
										
										// calculate team a
										if(count($all_score_for_lowest_arr[$i]['team_a']) > 1) {
											$sa = $all_score_for_lowest_arr[$i]['team_a'];
											
											$a1_score = $sa[0]['score'];
											$a2_score = $sa[1]['score'];
											
											if($a1_score < $a2_score) {
												$team_a_lowest_score = $sa[0]['score'];
												$team_a_lowest_player_id = $sa[0]['player_id'];
												$team_a_lowest_hole_type = $sa[0]['hole_type'];
											}
											elseif($a2_score < $a1_score) {
												$team_a_lowest_score = $sa[1]['score'];
												$team_a_lowest_player_id = $sa[1]['player_id'];
												$team_a_lowest_hole_type = $sa[1]['hole_type'];
											}
											else {
												$team_a_lowest_score = $sa[0]['score'];
												$team_a_lowest_player_id = $sa[0]['player_id'];
												$team_a_lowest_hole_type = $sa[0]['hole_type'];
											}
											
										}
										else {
											$sa = $all_score_for_lowest_arr[$i]['team_a'];
											$team_a_lowest_score = $sa[0]['score'];
											$team_a_lowest_player_id = $sa[0]['player_id'];
											$team_a_lowest_hole_type = $sa[0]['hole_type'];
										}
										
										// calculate team b
										if(count($all_score_for_lowest_arr[$i]['team_b']) > 1) {
											$sa = $all_score_for_lowest_arr[$i]['team_b'];
											
											$b1_score = $sa[0]['score'];
											$b2_score = $sa[1]['score'];
											
											if($b1_score < $b2_score) {
												$team_b_lowest_score = $sa[0]['score'];
												$team_b_lowest_player_id = $sa[0]['player_id'];
												$team_b_lowest_hole_type = $sa[0]['hole_type'];
											}
											elseif($b2_score < $b1_score) {
												$team_b_lowest_score = $sa[1]['score'];
												$team_b_lowest_player_id = $sa[1]['player_id'];
												$team_b_lowest_hole_type = $sa[1]['hole_type'];
											}
											else {
												$team_b_lowest_score = $sa[0]['score'];
												$team_b_lowest_player_id = $sa[0]['player_id'];
												$team_b_lowest_hole_type = $sa[0]['hole_type'];
											}
											
										}
										else {
											$sa = $all_score_for_lowest_arr[$i]['team_b'];
											$team_b_lowest_score = $sa[0]['score'];
											$team_b_lowest_player_id = $sa[0]['player_id'];
											$team_b_lowest_hole_type = $sa[0]['hole_type'];
										}
										
										// calculate between team a and team b
										if($team_a_lowest_score < $team_b_lowest_score) {
											$lowest_winner_team = 'team_a';
											$lowest_winner_player = $team_a_lowest_player_id;
											$lowest_winner_hole_type = $team_a_lowest_hole_type;
										}
										elseif($team_b_lowest_score < $team_a_lowest_score) {
											$lowest_winner_team = 'team_b';
											$lowest_winner_player = $team_b_lowest_player_id;
											$lowest_winner_hole_type = $team_b_lowest_hole_type;
										}
										else {
											// do nothing
											$lowest_winner_team = '';
											$lowest_winner_player = 0;
											$lowest_winner_hole_type = '';
										}
										
										// set lowest flag
										if(trim($lowest_winner_team)!='' && $lowest_winner_player>0 && trim($lowest_winner_hole_type)!='') {
											$scoredata = $currentScoreListArray["{$lowest_winner_hole_type}_data"][0][$lowest_winner_team]['player_list'];//print_r($currentScoreListArray);die;
											$scoredata1 = $scoredata;
											foreach($scoredata as $o=>$k) {
												if($k['player_id'] == $lowest_winner_player) {
													$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
													break;
												}
											}
											$currentScoreListArray["{$lowest_winner_hole_type}_data"][0][$lowest_winner_team]['player_list'] = $scoredata1;
										}
									}
								}
							}
							elseif($stroke_play_id=="12") {
								for($i=1;$i<=18;$i++) {
									if(isset($all_score_for_lowest_arr[$i]) && is_array($all_score_for_lowest_arr[$i]) && count($all_score_for_lowest_arr[$i])>0) {
										
										$hole_number = $i;
										
										// calculate 4-2-0 lowest
										$sa = $all_score_for_lowest_arr[$i]['team_a'];
										
										$a1_score = $sa[0]['score'];
										$a2_score = $sa[1]['score'];
										$a3_score = $sa[2]['score'];
										
										$a1_lowest_score = $a2_lowest_score = $a3_lowest_score = 0;
										$a1_lowest_player_id = $a2_lowest_player_id = $a3_lowest_player_id = 0;
										$a1_lowest_hole_type = $a2_lowest_hole_type = $a3_lowest_hole_type = '';
										
										if(($a1_score>=0 && $a2_score>=0 && $a3_score>=0) && ($a1_score!=$a2_score || $a2_score!=$a3_score)) {
											
											// calculate a1 lowest
											if($a1_score < $a2_score || $a1_score < $a3_score) {
												$a1_lowest_score = $sa[0]['score'];
												$a1_lowest_player_id = $sa[0]['player_id'];
												$a1_lowest_hole_type = $sa[0]['hole_type'];
											}
											elseif($a1_score == $a2_score && $a1_score < $a3_score) {
												$a1_lowest_score = $sa[0]['score'];
												$a1_lowest_player_id = $sa[0]['player_id'];
												$a1_lowest_hole_type = $sa[0]['hole_type'];
											}
											elseif($a1_score == $a3_score && $a1_score < $a2_score) {
												$a1_lowest_score = $sa[0]['score'];
												$a1_lowest_player_id = $sa[0]['player_id'];
												$a1_lowest_hole_type = $sa[0]['hole_type'];
											}
											
											// calculate a2 lowest
											if($a2_score < $a1_score || $a2_score < $a3_score) {
												$a2_lowest_score = $sa[1]['score'];
												$a2_lowest_player_id = $sa[1]['player_id'];
												$a2_lowest_hole_type = $sa[1]['hole_type'];
											}
											elseif($a2_score == $a1_score && $a2_score < $a3_score) {
												$a2_lowest_score = $sa[1]['score'];
												$a2_lowest_player_id = $sa[1]['player_id'];
												$a2_lowest_hole_type = $sa[1]['hole_type'];
											}
											elseif($a2_score == $a3_score && $a2_score < $a1_score) {
												$a2_lowest_score = $sa[1]['score'];
												$a2_lowest_player_id = $sa[1]['player_id'];
												$a2_lowest_hole_type = $sa[1]['hole_type'];
											}
											
											// calculate a3 lowest
											if($a3_score < $a1_score || $a3_score < $a2_score) {
												$a3_lowest_score = $sa[2]['score'];
												$a3_lowest_player_id = $sa[2]['player_id'];
												$a3_lowest_hole_type = $sa[2]['hole_type'];
											}
											elseif($a3_score == $a1_score && $a3_score < $a2_score) {
												$a3_lowest_score = $sa[2]['score'];
												$a3_lowest_player_id = $sa[2]['player_id'];
												$a3_lowest_hole_type = $sa[2]['hole_type'];
											}
											elseif($a3_score == $a2_score && $a3_score < $a1_score) {
												$a3_lowest_score = $sa[2]['score'];
												$a3_lowest_player_id = $sa[2]['player_id'];
												$a3_lowest_hole_type = $sa[2]['hole_type'];
											}
											
											// set lowest flag a1
											if($a1_lowest_score>=0 && $a1_lowest_player_id>0 && $a1_lowest_hole_type!='') {
												$scoredata = $currentScoreListArray["{$a1_lowest_hole_type}_data"][0]['team_a']['player_list'];
												$scoredata1 = $scoredata;
												foreach($scoredata as $o=>$k) {
													if($k['player_id'] == $a1_lowest_player_id) {
														$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
														break;
													}
												}
												$currentScoreListArray["{$a1_lowest_hole_type}_data"][0]['team_a']['player_list'] = $scoredata1;
											}
											
											// set lowest flag a2
											if($a2_lowest_score>=0 && $a2_lowest_player_id>0 && $a2_lowest_hole_type!='') {
												$scoredata = $currentScoreListArray["{$a2_lowest_hole_type}_data"][0]['team_a']['player_list'];
												$scoredata1 = $scoredata;
												foreach($scoredata as $o=>$k) {
													if($k['player_id'] == $a2_lowest_player_id) {
														$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
														break;
													}
												}
												$currentScoreListArray["{$a2_lowest_hole_type}_data"][0]['team_a']['player_list'] = $scoredata1;
											}
											
											// set lowest flag a3
											if($a3_lowest_score>=0 && $a3_lowest_player_id>0 && $a3_lowest_hole_type!='') {
												$scoredata = $currentScoreListArray["{$a3_lowest_hole_type}_data"][0]['team_a']['player_list'];
												$scoredata1 = $scoredata;
												foreach($scoredata as $o=>$k) {
													if($k['player_id'] == $a3_lowest_player_id) {
														$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
														break;
													}
												}
												$currentScoreListArray["{$a3_lowest_hole_type}_data"][0]['team_a']['player_list'] = $scoredata1;
											}
										}
									}
								}
							}
							elseif($stroke_play_id=="13" || $stroke_play_id=="14") {
								for($i=1;$i<=18;$i++) {
									if(isset($all_score_for_lowest_arr[$i]) && is_array($all_score_for_lowest_arr[$i]) && count($all_score_for_lowest_arr[$i])>0) {
										
										$hole_number = $i;
										
										// calculate team a
										$sa = $all_score_for_lowest_arr[$i]['team_a'];
										
										$a1_score = $sa[0]['score'];
										$a2_score = $sa[1]['score'];
										
										if($a1_score < $a2_score) {
											$team_a_lowest_score = $sa[0]['score'];
											$team_a_lowest_player_id = $sa[0]['player_id'];
											$team_a_lowest_hole_type = $sa[0]['hole_type'];
										}
										elseif($a2_score < $a1_score) {
											$team_a_lowest_score = $sa[1]['score'];
											$team_a_lowest_player_id = $sa[1]['player_id'];
											$team_a_lowest_hole_type = $sa[1]['hole_type'];
										}
										else {
											$team_a_lowest_score = $sa[0]['score'];
											$team_a_lowest_player_id = $sa[0]['player_id'];
											$team_a_lowest_hole_type = $sa[0]['hole_type'];
										}
										
										
										
										
										// calculate team b
										
										$sa = $all_score_for_lowest_arr[$i]['team_b'];
										
										$b1_score = $sa[0]['score'];
										$b2_score = $sa[1]['score'];
										
										if($b1_score < $b2_score) {
											$team_b_lowest_score = $sa[0]['score'];
											$team_b_lowest_player_id = $sa[0]['player_id'];
											$team_b_lowest_hole_type = $sa[0]['hole_type'];
										}
										elseif($b2_score < $b1_score) {
											$team_b_lowest_score = $sa[1]['score'];
											$team_b_lowest_player_id = $sa[1]['player_id'];
											$team_b_lowest_hole_type = $sa[1]['hole_type'];
										}
										else {
											$team_b_lowest_score = $sa[0]['score'];
											$team_b_lowest_player_id = $sa[0]['player_id'];
											$team_b_lowest_hole_type = $sa[0]['hole_type'];
										}
										
										// calculate between team a and team b
										if($team_a_lowest_score != $team_b_lowest_score) {
											// set lowest flag team a
											$scoredata = $currentScoreListArray["{$team_a_lowest_hole_type}_data"][0]['team_a']['player_list'];
											if(is_array($scoredata) && count($scoredata)>0) {
												$scoredata1 = $scoredata;
												foreach($scoredata as $o=>$k) {
													if($k['player_id'] == $team_a_lowest_player_id) {
														$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
														break;
													}
												}
												$currentScoreListArray["{$team_a_lowest_hole_type}_data"][0]['team_a']['player_list'] = $scoredata1;
											}
											
											// set lowest flag team b
											$scoredata = $currentScoreListArray["{$team_b_lowest_hole_type}_data"][0]['team_b']['player_list'];
											if(is_array($scoredata) && count($scoredata)>0) {
												$scoredata1 = $scoredata;
												
												foreach($scoredata as $o=>$k) {
													if($k['player_id'] == $team_b_lowest_player_id) {
														$scoredata1[$o]["hole_score"]["hole_num_{$hole_number}"]['is_lowest'] = 1;
														break;
													}
												}
												$currentScoreListArray["{$team_b_lowest_hole_type}_data"][0]['team_b']['player_list'] = $scoredata1;
											}
										}
									}
								}
							}
						}
					}
					$fdata['data'] = $currentScoreListArray;
				}
				elseif($is_started=="2") {
					$fdata['status'] = '0';
					$fdata['message'] = 'Event deleted.';							   
				}
				else {
					$fdata['status'] = '0';
					$fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date_time)).' '.date("h:i",strtotime($event_start_time))."";
				}
			}
			else {
				$fdata['status'] = '0';
				$fdata['message'] ='Event not exists in database.';
			}
		}
		else {
			$fdata['status'] = '0';
			$fdata['message'] ='Required fields not found.';
		}
		//die;
		return $fdata ;
	}
	
	
    
    function getLatestFullScoreOld($data){
          $fdata =  $currentScoreListArray = array();
            $eventId =  $data['event_id'];
			$player_id =  (isset($data['player_id']) && $data['player_id'] >0)?$data['player_id']:0;
             //  $stroke_play_id =  $data['stroke_play_id'];
			if($eventId > 0){
            $currentScoreListArray['event_id'] = $eventId;
			$queryString = "select golf_course_id,DATE(event_start_date_time) as event_start_date_time,format_id,event_start_time from event_table where event_id ='".$eventId."' ";
			$result = $this->db->FetchRow($queryString);
			$golf_course_id = $result['golf_course_id'];
			$event_start_date_time = $result['event_start_date_time'];
			$event_start_time = $result['event_start_time'];
			$stroke_play_id = $result['format_id'];
			$currentScoreListArray['event_stroke_play_id'] = $stroke_play_id;			
		    $queryString = "select event_admin_id from event_player_list where event_id ='".$eventId."' limit 1";
			$event_admin_id = $this->db->FetchSingleValue($queryString);
            if($event_admin_id > 0){
						$currentScoreListArray['event_admin_id'] = $event_admin_id;			
						$queryString = "select count(player_id) as total_player from event_player_list where event_id ='".$eventId."' limit 1";
						$total_player = $this->db->FetchSingleValue($queryString);						
						$currentScoreListArray['total_player'] = $total_player;			
						$queryString = "select total_hole_num,is_started,golf_course_name,event_name,hole_start_from from event_list_view where event_id ='".$eventId."' limit 1";
						$result = $this->db->FetchRow($queryString);
						$total_num_hole = $result['total_hole_num'];
						$is_started = $result['is_started'];
						$golf_course_name = $result['golf_course_name'];
						$event_name = $result['event_name'];
						$hole_start_from = $result['hole_start_from'];
						$currentScoreListArray['total_num_hole'] = $total_num_hole;
						$currentScoreListArray['hole_start_from'] = $hole_start_from;
						$currentScoreListArray['golf_course_name'] = $golf_course_name;
						$currentScoreListArray['event_name'] = $event_name;
							if($is_started=="3" || $is_started=="4"){
								  $queryString = "select max(hole_number) as hole_number from temp_event_score_entry where event_id ='".$eventId."' limit 1";
								 $hole_number = $this->db->FetchSingleValue($queryString);
									$currentScoreListArray['hole_number'] = $hole_number;
								$qryString ="";
								$is_handicap_gain='';
								if($stroke_play_id=="2"){
									$fieldname="score_entry_"; $total_field_name="gross_score";
								}elseif($stroke_play_id=="3"){
									$fieldname="score_entry_"; $total_field_name="gross_score";$is_handicap_gain='1';
									//$fieldname="net_";  $total_field_name="net_score";$is_handicap_gain='1';
								}elseif($stroke_play_id=="4"){
									$fieldname="score_entry_"; $total_field_name="gross_score";$is_handicap_gain='1';
									//$fieldname="net_stableford_3_4_v_"; $total_field_name="3_4_v_total";$is_handicap_gain='1';
								}elseif($stroke_play_id=="5"){
									$fieldname="gross_stableford_"; $total_field_name="gross_stableford";
								}elseif($stroke_play_id=="6"){
									$fieldname="net_stableford_"; $total_field_name="net_stableford";$is_handicap_gain='1';
								}elseif($stroke_play_id=="7"){
									$fieldname="net_stableford_3_4_"; $total_field_name="3_4_total";$is_handicap_gain='1';
								}elseif($stroke_play_id=="8"){
									$fieldname="score_entry_"; $total_field_name="prioria_value";//"prioria_value";
								}elseif($stroke_play_id=="9"){
									$fieldname="score_entry_"; $total_field_name="double_prioria_value";//"double_prioria_value";
								}else{
									$fieldname="score_entry_"; $total_field_name="gross_score";
								}
                                                                $is_team_game=false;
                                                                if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){
                                                                //check is team 
                                                                $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by t.team_profile_id asc,u.user_id asc";  
                                                                $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
                                                                $team_id=$team_id_name=array();$player_idArr=array();
                                                                if(count($sqlresult1) >0){
                                                                            foreach($sqlresult1 as $i=>$e){
                                                                                    $player_idArr[]=$e['player_id'];
                                                                                    if(!in_array($e['team_id'],$team_id)){
                                                                                    $team_id[]=$e['team_id'];
                                                                                    }
																					if(!in_array($e['team_display_name'],$team_id_name)){
                                                                                    $team_id_name[]=$e['team_display_name'];
                                                                                    }
                                                                            }
                                                                }						
                                                                $uniqueteam=array_unique($team_id);
                                                                $team_id_name=array_unique($team_id_name);
                                                                $game_type = (count($player_idArr)=="4")?'team':'';				
                                                                $is_team_game = ($game_type == 'team') ? true : false;
                                                                $currentScoreListArray['is_team'] =($is_team_game)?"1":"0";
                                                                    if($is_team_game){
                                                                       $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name,t.start_from_hole ";
								       $queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' order by p.team_id asc,t.player_id ASC ";
                                                                       //echo $queryString;
                                                                       $teamrec = $this->db->FetchQuery($queryString);	
                                                                       foreach($teamrec as $t=>$row){
                                                                            if($uniqueteam[0]==$row['team_id']){
                                                                                $teamplayerarray1[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                                                                            }else{
                                                                                $teamplayerarray2[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                                                                            }
																			$currentScoreListArray['hole_start_from']=$row['start_from_hole'];
                                                                       }
                                                                       $teamarray[]=array('team_name'=>$team_id_name[0],'player_list'=>$teamplayerarray1);
                                                                       $teamarray[]=array('team_name'=>$team_id_name[1],'player_list'=>$teamplayerarray2);
                                                                       $currentScoreListArray['team_data'] =$teamarray;
                                                                    }
                                                                    $standingdata=$this->getStandingForNewGameFormat($total_num_hole,$stroke_play_id,$eventId,$event_admin_id);//array();

                                                                    $currentScoreListArray['current_standing']=$standingdata['current'];
                                                                    if($stroke_play_id!=12){
                                                                    $currentScoreListArray['standings']=$standingdata['standing'];
                                                                    }
                                                                }
								$player_hole_score=array();$player_hole_scoreadmin=array();								
								if($hole_start_from==10) {
									$total_num_hole=18;
								}
								$parqryString='';
								if ($total_num_hole) {
									for($ctr = ($hole_start_from-1); $ctr < $total_num_hole;  $ctr++)
									{
										$ctrV = $ctr+1;
										$parqryString .= 'par_value_'.$ctrV. ','.'hole_index_'.$ctrV;
                                                                                if($is_team_game){
                                                                                    $qryString .= "t.score_entry_".$ctrV.",min(t.".$fieldname.$ctrV.") as hole_num_".$ctrV;
                                                                                }else{
                                                                                    $qryString .= "t.score_entry_".$ctrV.",t.".$fieldname.$ctrV." as hole_num_".$ctrV;
                                                                                }
										
										if($stroke_play_id=="5" || $stroke_play_id=="6" || $stroke_play_id=="7"){
										$qryString .= ", t.score_entry_".$ctrV." as gross_score_".$ctrV;											
										}
										if($ctr != $total_num_hole-1)
										{
											$qryString .= ",";$parqryString.=",";
										}
									}
									if($stroke_play_id=="5" || $stroke_play_id=="6" || $stroke_play_id=="7"){
									$qryString .= ", t.gross_score";	
									}
									$parqry=' select '.$parqryString.' from golf_hole_index where golf_course_id ='.$golf_course_id.''; 
								    $rowparValues = $this->db->FetchRow($parqry);
							     $parno=0;
								 
								 $pl = (isset($player_id) && $player_id > 0)?' AND t.player_id ='.$player_id.'':'';
								
								$scrnqryString_arr = array();
								
								for($i=1;$i<=18;$i++) {
									$scrnqryString_arr[] = 'score_entry_'.$i;
								}
								
								$scrnqryString = count($scrnqryString_arr)>0 ? implode(',',$scrnqryString_arr) : 't.*';
								
								$parqry=' select '.$scrnqryString.' from event_score_calc t where t.event_id ='.$eventId.' '.$pl.' limit 1'; 
								$rowscrnValues = $this->db->FetchRow($parqry);
								
								for($ctr = $hole_start_from; $ctr <= $total_num_hole;  $ctr++)
								{
									$parno=$ctr;
									$currentScoreListArray['par_value_'.$parno] = ($rowscrnValues['score_entry_'.$parno]>0) ? $rowparValues['par_value_'.$parno] : '0';
									//$currentScoreListArray['hole_color_'.$parno] = $this->getParColorCodeValue($rowparValues['par_value_'.$parno]);
									$currentScoreListArray['hole_index_'.$parno] = $rowparValues['hole_index_'.$parno];
								}
								   
									if($stroke_play_id=="4" || $stroke_play_id=="7"){
									$queryString = " select t.player_id,t.handicap_value_3_4 as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total,t.start_from_hole";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);
									}else if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){
                                                                        if($is_team_game){
                                                                        $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total,t.start_from_hole";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." group by p.team_id order by p.team_id asc ";
                                                                        //echo "<br>".$queryString;
									}else{
                                                                        $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total,t.start_from_hole";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." order by t.event_score_calc_id asc";
									}
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);										
									}else{
									$queryString = " select t.player_id,t.handicap_value,t.handicap_value_3_4,g.full_name,t.start_from_hole, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total,t.start_from_hole";
									$queryString .= " from  event_score_calc t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									//echo $queryString;
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);									
									}
									
                                    $event = new Events;
									$counter =0;$no_of_eagle=0;$no_of_birdies=0;$no_of_pars=0;$no_of_bogeys=0;$no_of_double_bogeys=0;
									$total_front_9_postion=0;$total_back_9_postion=0;$total_postion=0;$player_counter=0;
									//print_r($recordSetPlayerScore);
									foreach($recordSetPlayerScore as $i=>$rowValues){
										$handi_counter=0; $player_counter++;			
										for($ctr = $hole_start_from; $ctr <= $total_num_hole;  $ctr++){
											$handi_counter=$ctr;
											if($is_handicap_gain=="1"){		
												if($stroke_play_id=="4" || $stroke_play_id=="7"){									
												    if($rowValues['handicap_value_3_4'] >=$rowparValues['hole_index_'.$handi_counter]){
													$rowValues['is_handicap_gain_'.$handi_counter]="Stroke Play";
													}else{
													$rowValues['is_handicap_gain_'.$handi_counter]="";	
													}													
												}else{
													if($rowValues['handicap_value'] >=$rowparValues['hole_index_'.$handi_counter]){
													$rowValues['is_handicap_gain_'.$handi_counter]="Stroke Play";
													}else{
													$rowValues['is_handicap_gain_'.$handi_counter]="";	
													}	
												}
											}else{
												$rowValues['is_handicap_gain_'.$handi_counter]="";
											}	
											if(isset($player_id) && $player_id > 0){			
											$queryString = " select calculated_position from event_score_calc_position where event_id ='".$eventId."' and player_id='".$rowValues['player_id']."' and hole_number='".$handi_counter."'";
												$position = $this->db->FetchSingleValue($queryString);
                                                if($rowValues['hole_num_'.$handi_counter] > 0){												
												if($position==0){
												$rowValues['position_'.$handi_counter]='E';	
												}else{
													$rowValues['position_'.$handi_counter]=($position >0)?'+'.$position:$position;
												}		
												}else{
													$rowValues['position_'.$handi_counter]='-';
												}
												if($handi_counter <=9){
												$total_front_9_postion+=$position;
												}else{
												$total_back_9_postion+=$position;	
												}												
												$total_postion+=$position;
												
											}
											$color='#ffffff';
											$currentScoreListArray['hole_start_from']=$rowValues['start_from_hole'];
											if($rowValues['score_entry_'.$handi_counter] > 0){
												/*echo 'pos : '.$handi_counter.'<br/>';
												echo 'gs : '.$rowValues['score_entry_'.$handi_counter].'<br/>';
												echo 'par : '.$rowparValues['par_value_'.$handi_counter].'<br/>';die;*/
											$difference =  $rowValues['score_entry_'.$handi_counter] - $rowparValues['par_value_'.$handi_counter];
												if( $difference <= -2){
													$no_of_eagle = $no_of_eagle + 1;
													$color='#f4aa43';
												}else if( $difference == -1){
													$no_of_birdies = $no_of_birdies + 1;
													$color='#0a5c87';
												}else if( $difference == 0){
													$no_of_pars = $no_of_pars + 1;
													$color='#325604';
												}else if( $difference == 1){
													$no_of_bogeys = $no_of_bogeys + 1;
													$color='#939494';
												}else if( $difference >= 2){
													$no_of_double_bogeys = $no_of_double_bogeys + 1;
													$color='#000000';
												}else{}	
											}
                                        $nam = explode(' ',$rowValues['full_name']);
					$namf = (isset($nam[0]) && $nam[0] !='')?$nam[0]:'';
					$naml = (isset($nam[1]) && $nam[1] !='')?$nam[1]:'';
					if(in_array($last, $rowValues)){
						$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 2):substr($namf, 0, 2);
						$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
					}else{
						$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 1):substr($namf, 0, 2);
						$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
					}
											//$rowValues['hole_num_'.$handi_counter]='<span style="color:#ffffff">'.$rowValues['hole_num_'.$handi_counter].'</span>';
											$rowValues['hole_color_'.$handi_counter]=$color;
											unset($rowValues['score_entry_'.$handi_counter]);
										}
                                        $rowValues['short_name'] = $last;			
                                        if($is_team_game){
                                            $rowValues['short_name']=($uniqueteam[0]==$rowValues['team_id'])?$team_id_name[0]:$team_id_name[1];
                                            $rowValues['full_name']=($uniqueteam[0]==$rowValues['team_id'])?$team_id_name[0]:$team_id_name[1];
                                            $rowValues['player_color_code']=$this->setColorForPlayer(1,(($uniqueteam[0]==$rowValues['team_id'])?"Team A":"Team B"),0);
                                            unset($rowValues['player_id']);unset($rowValues['handicap_value']);unset($rowValues['handicap_value_3_4']);
                                        }else{
											if($rowValues['player_id'] == $event_admin_id) {
												$player_counter1 = 1;$player_counter--;
											}
											else {
												$player_counter1 = $player_counter+1;
												$player_counter = $player_counter1;
												$player_counter1 = ($player_counter1 > count($recordSetPlayerScore)) ? count($recordSetPlayerScore) : $player_counter1;
											}
                                            $rowValues['player_color_code']=$this->setColorForPlayer(0,'',$player_counter1);							
                                        }
                                        if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){

                                        }else{
                                          unset($rowValues['player_color_code']);  
                                        }
                                        if($event_admin_id==$rowValues['player_id']){
					     $player_hole_scoreadmin[] = $rowValues ;
                                        }else{
                                            $player_hole_score[] = $rowValues ;                                            
                                        }
                                                                                
									}
								}								
								//$no_of_eagle=0;$no_of_birdies=0;$no_of_pars=0;$no_of_bogeys=0;
							$player_hole_scorearray=array_merge($player_hole_scoreadmin,$player_hole_score);	
								$currentScoreListArray['total_front_9_postion']= $total_front_9_postion; 
							   $currentScoreListArray['total_back_9_postion']= $total_back_9_postion; 
							   $currentScoreListArray['total_postion']= $total_postion; 				
							   $currentScoreListArray['eagle_counter']= $no_of_eagle; 
							   $currentScoreListArray['birdie_counter']= $no_of_birdies; 
							   $currentScoreListArray['par_counter']= $no_of_pars; 
							   $currentScoreListArray['bogey_counter']= $no_of_bogeys; 
							   $currentScoreListArray['doublebogey_counter']= $no_of_double_bogeys; 
							   $currentScoreListArray['player_hole_score']= $player_hole_scorearray; 
							   $fdata['status'] = '1';
							   $fdata['data'] = $currentScoreListArray;
							   $fdata['message'] = 'Full Score';
							}elseif($is_started=="2"){
								 $fdata['status'] = '0';
							    $fdata['message'] = 'Event deleted.';							   
							}else{
								 $fdata['status'] = '0';
							    $fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date_time)).' '.date("h:i",strtotime($event_start_time))."";
							}
						}else{
							 $fdata['status'] = '0';
							 $fdata['message'] ='Event not exists in database.';
							
						}
				}else{
							$fdata['status'] = '0';
							 $fdata['message'] ='Required fields not found.';
							 
				
			}
			return $fdata ;
        }            
           
	function getParColorCodeValue($parvalue){
			$color='';
			if($parvalue=="3"){
				$color='red';//"#FF0000";//red
			}elseif($parvalue=="4"){
				$color='green'; //"#008000";//green
			}else{
				$color='blue';//"#000099";//blue
			}
			return $color;
		}
		
function submit_player_score($data){
		$eventId=isset($data['event_id'])?$data['event_id']:""; 
		$user_id=isset($data['user_id'])?$data['user_id']:"0"; 
		$fdata=array();
		
		if($eventId > 0 && $user_id > 0){
			
			 $queryString = "select player_id from event_player_list where event_id ='".$eventId."' and scorere_id =".$user_id."";
			$user_data  = $this->db->FetchQuery($queryString);

			if(isset($user_data[0]) && is_array($user_data[0]) && count($user_data[0])>0) {
$no_of_eagle=$no_of_birdies=$no_of_pars=$no_of_bogeys=$no_of_double_bogeys = 0;				
				foreach($user_data as $i=>$v){
				$sqlu="update event_player_list set is_submit_score='1',submit_score_date='".date('Y-m-d H:i:s')."' where event_id ='".$eventId."' and scorere_id =".$user_id." ";
				$this->db->FetchQuery($sqlu);
				for($i=1;$i<=18;$i++){
				
				$query = 'SELECT score_entry_'.$i.',par_'.$i.' FROM event_score_calc WHERE event_id='.$eventId.' AND player_id='.$user_id.''; 
				$getdata = $this->db->FetchRow($query);
				if($getdata['score_entry_'.$i] > 0){
											$difference =  $getdata['score_entry_'.$i] - $getdata['par_'.$i];
												if( $difference <= -2){
													$no_of_eagle = $no_of_eagle + 1;
													
												}else if( $difference == -1){
													$no_of_birdies = $no_of_birdies + 1;
													
												}else if( $difference == 0){
													$no_of_pars = $no_of_pars + 1;
													
												}else if( $difference == 1){
													$no_of_bogeys = $no_of_bogeys + 1;
												
												}else if( $difference >= 2){
													$no_of_double_bogeys = $no_of_double_bogeys + 1;
													
												}else{}	
											}
				$updata = "UPDATE event_score_calc SET no_of_eagle=".$no_of_eagle.",no_of_birdies=".$no_of_birdies.",no_of_pars=".$no_of_pars.",no_of_bogeys=".$no_of_bogeys.",no_of_double_bogeys=".$no_of_double_bogeys." where event_id=".$eventId." and player_id=".$v['player_id'].""; 
				$this->db->FetchQuery($updata);
				$plid = $v['player_id'];
				$sql22 = "delete from event_score_calc_temp where event_id = '{$eventId}' and player_id = '{$plid}'";
				$this->db->FetchQuery($sql22);
				
				
			}
				$xarr = array('event_id' => $eventId, 'player_id' => $plid, 'send_mail' => '1');
				$this->sendScorecardMail($xarr);
				
				}			
				
				
				
				$fdata['status'] = '1';	
				$fdata['message'] ="Score Submitted";
				
			}else{
				$fdata['status'] = '0';
				$fdata['message'] ="Event not found";	
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] ="Required field not found";
		}
		return $fdata;
	}
	
	/*function submit_player_score_temp($data=array()){
		ini_set('max_execution_time',0);
		$fdata=array();
		
		
			
			 $queryString = "select player_id,event_id from event_player_list where is_submit_score ='1'";
			$user_data  = $this->db->FetchQuery($queryString);

			if(isset($user_data[0]) && is_array($user_data[0]) && count($user_data[0])>0) {
$no_of_eagle=$no_of_birdies=$no_of_pars=$no_of_bogeys=$no_of_double_bogeys = 0;				
				foreach($user_data as $i=>$v){
				$eventId = $v['event_id'];
				$user_id = $v['player_id'];
				for($i=1;$i<=18;$i++){
				
				$query = 'SELECT score_entry_'.$i.',par_'.$i.' FROM event_score_calc WHERE event_id='.$eventId.' AND player_id='.$user_id.''; 
				$getdata = $this->db->FetchRow($query);
				if($getdata['score_entry_'.$i] > 0){
											$difference =  $getdata['score_entry_'.$i] - $getdata['par_'.$i];
												if( $difference <= -2){
													$no_of_eagle = $no_of_eagle + 1;
													
												}else if( $difference == -1){
													$no_of_birdies = $no_of_birdies + 1;
													
												}else if( $difference == 0){
													$no_of_pars = $no_of_pars + 1;
													
												}else if( $difference == 1){
													$no_of_bogeys = $no_of_bogeys + 1;
												
												}else if( $difference >= 2){
													$no_of_double_bogeys = $no_of_double_bogeys + 1;
													
												}else{}	
											}
				$updata = "UPDATE event_score_calc SET no_of_eagle=".$no_of_eagle.",no_of_birdies=".$no_of_birdies.",no_of_pars=".$no_of_pars.",no_of_bogeys=".$no_of_bogeys.",no_of_double_bogeys=".$no_of_double_bogeys." where event_id=".$eventId." and player_id=".$v['player_id'].""; 
				$this->db->FetchQuery($updata);
				$plid = $v['player_id'];
				$sql22 = "delete from event_score_calc_temp where event_id = '{$eventId}' and player_id = '{$plid}'";
				$this->db->FetchQuery($sql22);
				
				
			}
				
				}			
				
				
				
				$fdata['status'] = '1';	
				$fdata['message'] ="Score Submitted";	
			}else{
				$fdata['status'] = '0';
				$fdata['message'] ="Event not found";	
			}
		
		return $fdata;
	}
	
	function submit_player_score_temp($data=array()){
		ini_set('max_execution_time',0);
		$fdata=array();
		
		
			
			 $queryString = "select event_id,tee_id from event_table where event_id!='750' order by event_id limit 100000";
			$user_data  = $this->db->FetchQuery($queryString);

			foreach($user_data as $i=>$v){
				$eventId = $v['event_id'];
				$tee_id = $v['tee_id'];
				$arr = array();
				if(trim($tee_id)!='') {
					//[{"junior":"2","ladies":"2","men":"2"}]
					//[{"men":"1"},{"ladies":"1"},{"junior":"1"}]
					$jsn = json_decode($tee_id,true);
					//print_r($jsn);
						$arr[] = array('men' => $jsn[0]['men']);
						$arr[] = array('ladies' => $jsn[0]['ladies']);
						$arr[] = array('junior' => $jsn[0]['junior']);
					
				} //print_r($arr); print_r(json_decode('[{"men":"1"},{"ladies":"1"},{"junior":"1"}]',true));
				$updata = "UPDATE event_table SET tee_id='".json_encode($arr)."' where event_id=".$eventId.""; 
				$this->db->FetchQuery($updata);
				
				
				
			}
				
							
		return $fdata;
	}*/
	
	function end_player_score($data){
		$eventId=isset($data['event_id'])?$data['event_id']:""; 
		$user_id=isset($data['user_id'])?$data['user_id']:"0"; 
		$fdata=array();
		
		if($eventId > 0 && $user_id > 0 ){
		
			$queryString = "select player_id from event_player_list where event_id ='".$eventId."' and scorere_id =".$user_id."";
			$user_data  = $this->db->FetchQuery($queryString);
			
			if(isset($user_data[0]) && is_array($user_data[0]) && count($user_data[0])>0 ) {	
			
				
				foreach($user_data as $i=>$u){
					$tlb = array('event_player_list','event_score_4_2_0','event_score_calc','event_score_calc_closest_feet','event_score_calc_closest_inch','event_score_calc_fairway','event_score_calc_no_of_putt','event_score_calc_position','event_score_calc_sand','event_score_calc_position');
				foreach($tlb as $t){
				$sqlu="delete from ".$t." where event_id='".$eventId."' and player_id='".$u['player_id']."' ";
				$this->db->FetchQuery($sqlu);
				}
			}
			
			$queryString = "select golf_course_id,format_id from event_table where event_id ='".$eventId."'";
			$event_data  = $this->db->FetchRow($queryString);
			
			$this->updatePosition($eventId,$event_data['format_id'],$event_data['golf_course_id']);
			$this->updatePositionGross($eventId,$event_data['format_id'],$event_data['golf_course_id']);
			
				$quer = 'SELECT event_score_calc_id FROM event_score_calc WHERE event_id = '.$eventId.'';
				$isRow = $this->db->FetchQuery($quer);
				if(isset($isRow) && is_array($isRow) && count($isRow)>0){
					
				}else{
						$sqlu="update event_table set is_started='4' where event_id ='".$eventId."' ";
				$this->db->FetchQuery($sqlu);
				}
			
				$fdata['status'] = '1';	
				$fdata['message'] ="Event End Succesfully";	
			}else{
				$fdata['status'] = '0';
				$fdata['message'] ="Event not found";	
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] ="Required field not found";
		}
		return $fdata;
	}

	function getColorCode($color){
		if($color=="red"){
			$ccode='#FF0000';
		}elseif($color=="blue"){
			$ccode='#0b5a97';//'#0000FF';
		}elseif($color=="black"){
			$ccode='#000000';
		}elseif($color=="green"){
			$ccode='#325604';//'#0c9f32';
		}else{
			$ccode='';
		}
		return $ccode;
	}	
        
        function setColorForPlayer($is_team=0,$team_name='',$playercounter=1){
            if($is_team==1){
                $color=($team_name=="Team A")?$this->getColorCode('red'):$this->getColorCode('blue');
            }else{
                if($playercounter==1){
                   $color=$this->getColorCode('red'); 
                }elseif($playercounter==2){
                   $color=$this->getColorCode('blue'); 
                }else{
                   $color=$this->getColorCode('green');  
                }
            }
            return $color; 
        }
        
        
        

  function getIndividualHoleScore($data)
        {

		$eventId = (isset($data['event_id']) && $data['event_id'] >0)?$data['event_id']:'0';
		$hole_num = (isset($data['hole_num']) && $data['hole_num'] >0)?$data['hole_num']:'0';
		
		 $playerScoreListArray =$fdata= array();
            $playerScoreListArray['event_id'] = $eventId;
            if($eventId > 0 && $hole_num >0){
            $queryString = "select format_id,admin_id,total_hole_num,is_started,DATE(event_start_date_time) as event_start_date_time,event_start_time from event_table where event_id ='".$eventId."'";
			$result = $this->db->FetchRow($queryString);
            $stroke_play_id = $result['format_id'];
			 $event_start_date_time = $result['event_start_date_time'];
			  $event_start_time = $result['event_start_time']; 
			  $event_admin_id = $result['admin_id']; 
			  $total_num_hole = $result['total_hole_num']; 
			  $is_started = $result['is_started']; 
			$playerScoreListArray['event_stroke_play_id'] = $stroke_play_id;
            if(is_array($result) > 0){
						
						$currentScoreListArray['event_admin_id'] = $event_admin_id;
			
					$queryString = "select count(player_id) as total_player from event_player_list where event_id ='".$eventId."' limit 1";
						 $total_player = $this->db->FetchSingleValue($queryString);
					
						$currentScoreListArray['total_player'] = $total_player;
			
						$currentScoreListArray['total_num_hole'] = $total_num_hole;
							if($is_started=="3" || $is_started=="3"){
								$queryString = "select max(hole_number) as hole_number from temp_event_score_entry where event_id ='".$eventId."' limit 1";
								$hole_number = $this->db->FetchSingleValue($queryString);
								
								$currentScoreListArray['hole_number'] = $hole_number;
								$qryString ="";
$fieldname="score_entry_"; $total_field_name="gross_score";
												 
								$player_hole_score=array();

								if ($total_num_hole) {
									$queryString = " select t.player_id,t.handicap_value,t.score_entry_".$hole_num." as hole_num_".$hole_num.", p.no_of_putt_".$hole_num." as no_of_putt,f.fairway_".$hole_num." as fairway,s.sand_".$hole_num." as sand,c.closest_feet_".$hole_num." as closest_feet from temp_event_score_entry t LEFT JOIN event_score_calc_no_of_putt p ON p.event_id =t.event_id and p.player_id=t.player_id LEFT JOIN event_score_calc_sand s ON s.event_id =t.event_id and s.player_id=t.player_id LEFT JOIN event_score_calc_fairway f ON f.event_id =t.event_id and f.player_id=t.player_id LEFT JOIN event_score_calc_closest_feet c ON c.event_id =t.event_id and c.player_id=t.player_id where t.event_id ='".$eventId."' group by p.player_id order by current_position asc"; 
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);
								
									foreach($recordSetPlayerScore as $i=>$rowValues)
									{
										$clo = (isset($rowValues['closest_feet']) && $rowValues['closest_feet'] !='')?$rowValues['closest_feet']:'';
									//if($clo != ''){
										$closeset = explode(',', $clo);
										$rowValues['feet'] = $closeset[0];
										$rowValues['inches'] = $closeset[1];
									//}
									unset($rowValues['closest_feet']);

									//	$rowValues["hole_num_".$hole_num]=($rowValues["hole_num_".$hole_num]=="0") ? '' :$rowValues["hole_num_".$hole_num];
										$rowValues["score"]=($rowValues["hole_num_".$hole_num]=="0") ? '' :$rowValues["hole_num_".$hole_num];
										
										$player_hole_score[] = $rowValues ;
									}
								}
								
								$currentScoreListArray['player_hole_score'] = $player_hole_score;			
							   // print_r($currentScoreListArray);
							   $fdata['status'] = '1';
							   $fdata['data'] = $currentScoreListArray;
							   $fdata['message'] = 'Hole Score';
							
							}elseif($is_started=="2"){
								$fdata['status'] = '0';
							    $fdata['message'] = 'Event deleted.';
							
							}else{
								$fdata['status'] = '0';
							    $fdata['message'] = "This event will begin at ".date("d M Y",strtotime($event_start_date_time)).' '.date("h:i",strtotime($event_start_time))."";
								
							}
						}else{
							$fdata['status'] = '0';
							$fdata['message'] = 'Event not exists in database.';
							
						}
			}else{
					$fdata['status'] = '0';
					$fdata['message'] = 'Required fields not found.';
				
			}
return $fdata;			
		}
		
		function getExpandableScoreView($data){
			$fdata =  $currentScoreListArray = array();
			$eventId =  $data['event_id'];
			if($eventId > 0) {
				$currentScoreListArray['event_id'] = $eventId;
				
				$colname=array();
				for($i=1;$i<=18;$i++) {
					$colname[] = "c.score_entry_{$i}";
				}
				
				$queryString = "select e.admin_id,e.format_id,e.total_hole_num,e.is_started,e.hole_start_from,c.start_from_hole,".implode(",",$colname).",c.player_id,p.team_id from event_table e left join event_score_calc c on e.event_id = c.event_id left join event_player_list p on c.event_id = p.event_id and c.player_id = p.player_id where e.event_id ='".$eventId."' group by c.player_id order by c.is_admin desc,p.team_id asc,c.player_id asc";
				$result = $this->db->FetchQuery($queryString);
				//print_r($result);die;
				
				$player_count = count($result);
				
				if($player_count) {
					$event_admin_id = isset($result[0]['admin_id']) ? $result[0]['admin_id'] : 0;
					$event_format_id = isset($result[0]['format_id']) ? $result[0]['format_id'] : 0;
					$start_from_hole = isset($result[0]['start_from_hole']) ? $result[0]['start_from_hole'] : 0;
					
					$is_team = ($player_count == 4) ? true : false;
					
					if($event_format_id > 0) {
						/*
						11 : autopress
						12 : 4-2-0
						13 : vegas
						14 : 2-1
						*/
						if($event_format_id == 11) {
							$sql = "SELECT * FROM event_score_autopress where event_id='{$eventId}' order by id asc";
							$autopress_data = $this->db->FetchQuery($sql); //print_r($autopress_data);die;
							
							if(is_array($autopress_data) && count($autopress_data)>0) {
								$hole_counter = 1;
								$final_data = array();
								for($j=$start_from_hole;$j<=($start_from_hole+17);$j++){
									$current_hole_number = $i = ($j<=18) ? $j : ($j-18);
									
									$has_score = false;
									
									// check if hole score exist
									if($is_team) {
										$a1_score = isset($result[0]["score_entry_{$i}"]) ? $result[0]["score_entry_{$i}"] : 0;
										$a2_score = isset($result[1]["score_entry_{$i}"]) ? $result[1]["score_entry_{$i}"] : 0;
										$b1_score = isset($result[2]["score_entry_{$i}"]) ? $result[2]["score_entry_{$i}"] : 0;
										$b2_score = isset($result[3]["score_entry_{$i}"]) ? $result[3]["score_entry_{$i}"] : 0;
										
										if($a1_score > 0 && $a2_score > 0 && $b1_score > 0 && $b2_score > 0) {
											$has_score = true;
										}
									}
									else {
										$a1_score = isset($result[0]["score_entry_{$i}"]) ? $result[0]["score_entry_{$i}"] : 0;
										$b1_score = isset($result[1]["score_entry_{$i}"]) ? $result[1]["score_entry_{$i}"] : 0;
										
										if($a1_score > 0 && $b1_score > 0) {
											$has_score = true;
										}
									}
									
									if($has_score) {
										foreach($autopress_data as $a=>$b) {
											if($current_hole_number == $b['hole_number']) {
												$ky = "hole_number_{$hole_counter}";
												$final_data[$ky]['hole_number'] = $current_hole_number;
												if(trim($b['score_value']) != '' && trim($b['score_value']) != '[]') {
													$final_data[$ky]["first_array"] = json_decode($b['score_value'],true);
												}
												else {
													$final_data[$ky]["first_array"] = array();
												}
												
												if(trim($b['back_to_9_score']) != '' && trim($b['back_to_9_score']) != '[]') {
													$final_data[$ky]["second_array"] = json_decode($b['back_to_9_score'],true);
												}
												else {
													$final_data[$ky]["second_array"] = array();
												}
												$final_data[$ky]['first_array_count'] = count($final_data[$ky]["first_array"]);
												$final_data[$ky]['second_array_count'] = count($final_data[$ky]["second_array"]);
												break;
											}
										}
									}
									$hole_counter++;
								}
								
								$final_data1 = array();
								foreach($final_data as $a=>$b) {
									$final_data1[] = $b;
								}
								//print_r($final_data1);die;
								$fdata['status'] = '1';
								$fdata['data'] = $final_data1;
								//$fdata['data']['total_holes_played'] = count($final_data1);
							}
							else {
								$fdata['status'] = '0';
								$fdata['message'] = 'Autopress Score Not Generated.';
							}
						}
						elseif($event_format_id == 12) {
							$sql = "SELECT * FROM event_score_4_2_0 where event_id='{$eventId}' order by id asc";
							$data_420 = $this->db->FetchQuery($sql); //print_r($result); print_r($data_420);die;
							
							if(is_array($data_420) && count($data_420)>0) {
								$hole_counter = 1;
								$final_data = array();
								for($j=$start_from_hole;$j<=($start_from_hole+17);$j++){
									$current_hole_number = $i = ($j<=18) ? $j : ($j-18);
									
									// check if hole score exist
									$a1_score = isset($result[0]["score_entry_{$i}"]) ? $result[0]["score_entry_{$i}"] : 0;
									$a2_score = isset($result[1]["score_entry_{$i}"]) ? $result[1]["score_entry_{$i}"] : 0;
									$a3_score = isset($result[2]["score_entry_{$i}"]) ? $result[2]["score_entry_{$i}"] : 0;
									
									$a1_id = isset($result[0]["player_id"]) ? $result[0]["player_id"] : 0;
									$a2_id = isset($result[1]["player_id"]) ? $result[1]["player_id"] : 0;
									$a3_id = isset($result[2]["player_id"]) ? $result[2]["player_id"] : 0;
									
									if($a1_score > 0 && $a2_score > 0 && $a3_score > 0) {
										$tmparr = $tmparr_agg = array();
										foreach($data_420 as $a=>$b) {
											$ky = "hole_number_{$hole_counter}";
											$final_data[$ky]['hole_number'] = $current_hole_number;
											
											if($current_hole_number == $b['hole_number']) {
												if($b['player_id'] == $a1_id) {
													$tmparr[$b['player_id']] = array('score'=>strval($b['score_value']), 'color'=>'#ff0000');
													$tmparr_agg[$b['player_id']] = array('score'=>strval($b['total']), 'color'=>'#ff0000');
												}
												if($b['player_id'] == $a2_id) {
													$tmparr[$b['player_id']] = array('score'=>strval($b['score_value']), 'color'=>'#58b3fa');
													$tmparr_agg[$b['player_id']] = array('score'=>strval($b['total']), 'color'=>'#58b3fa');
												}
												if($b['player_id'] == $a3_id) {
													$tmparr[$b['player_id']] = array('score'=>strval($b['score_value']), 'color'=>'#508f00');
													$tmparr_agg[$b['player_id']] = array('score'=>strval($b['total']), 'color'=>'#508f00');
												}
												
												
												//break;
											}
										}
										if(count($tmparr) == 3 && count($tmparr_agg) == 3){
											$f1 = $f2 = array();
											$f1[$a1_id] = $tmparr[$a1_id];
											$f1[$a2_id] = $tmparr[$a2_id];
											$f1[$a3_id] = $tmparr[$a3_id];
											
											$f2[$a1_id] = $tmparr_agg[$a1_id];
											$f2[$a2_id] = $tmparr_agg[$a2_id];
											$f2[$a3_id] = $tmparr_agg[$a3_id];
											
											$final_data[$ky]["first_array"] = array_values($f1);
											$final_data[$ky]["second_array"] = array_values($f2);
											$final_data[$ky]["first_array_count"] = count($f2);
											$final_data[$ky]["second_array_count"] = count($f2);
										}
									}
									$hole_counter++;
								}
								//print_r($final_data);die;
								$final_data1 = array();
								foreach($final_data as $a=>$b) {
									$final_data1[] = $b;
								}
								$fdata['status'] = '1';
								$fdata['data'] = $final_data1;
								//$fdata['data']['total_holes_played'] = count($final_data);
							}
							else {
								$fdata['status'] = '0';
								$fdata['message'] = '4-2-0 Score Not Generated.';
							}
						}
						elseif($event_format_id == 13) {
							$sql = "SELECT * FROM event_score_vegas where event_id='{$eventId}' order by id asc";
							$vegas_data = $this->db->FetchQuery($sql); //print_r($result); print_r($vegas_data);die;
							
							if(is_array($vegas_data) && count($vegas_data)>0) {
								$hole_counter = 1;
								$final_data = array();
								for($j=$start_from_hole;$j<=($start_from_hole+17);$j++){
									$current_hole_number = $i = ($j<=18) ? $j : ($j-18);
									
									$has_score = false;
									
									// check if hole score exist
									if($is_team) {
										$a1_score = isset($result[0]["score_entry_{$i}"]) ? $result[0]["score_entry_{$i}"] : 0;
										$a2_score = isset($result[1]["score_entry_{$i}"]) ? $result[1]["score_entry_{$i}"] : 0;
										$b1_score = isset($result[2]["score_entry_{$i}"]) ? $result[2]["score_entry_{$i}"] : 0;
										$b2_score = isset($result[3]["score_entry_{$i}"]) ? $result[3]["score_entry_{$i}"] : 0;
										$team_a_id = isset($result[0]["team_id"]) ? $result[0]["team_id"] : 0;
										$team_b_id = isset($result[2]["team_id"]) ? $result[2]["team_id"] : 0;
										if($a1_score > 0 && $a2_score > 0 && $b1_score > 0 && $b2_score > 0) {
											$has_score = true;
										}
									}
									
									if($has_score) {
										foreach($vegas_data as $a=>$b) {
											$ky = "hole_number_{$hole_counter}";
											$final_data[$ky]['hole_number'] = $current_hole_number;
											
											if($current_hole_number == $b['hole_number']) {
											
												// set second array
												if($b['winner'] == $team_a_id) {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#ff0000';
												}
												elseif($b['winner'] == $team_b_id) {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#58b3fa';
												}
												else {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#000000';
												}
												
												// set first array  
												if($b['hole_winner'] == $team_a_id) {
													$final_data[$ky]['hole_score'] = strval($b['hole_score_value']);
													$final_data[$ky]['hole_color'] = '#ff0000';
												}
												elseif($b['hole_winner'] == $team_b_id) {
													$final_data[$ky]['hole_score'] = strval($b['hole_score_value']);
													$final_data[$ky]['hole_color'] = '#58b3fa';
												}
												else {
													$final_data[$ky]['hole_score'] = strval($b['hole_score_value']);
													$final_data[$ky]['hole_color'] = '#000000';
												}
												break;
											}
										}
									}
									$hole_counter++;
								}
								$final_data1 = array();
								foreach($final_data as $a=>$b) {
									$final_data1[] = $b;
								}
								$fdata['status'] = '1';
								$fdata['data'] = $final_data1;
								//$fdata['data']['total_holes_played'] = count($final_data);
							}
							else {
								$fdata['status'] = '0';
								$fdata['message'] = 'Vegas Score Not Generated.';
							}
						}
						elseif($event_format_id == 14) {
							$sql = "SELECT * FROM event_score_2_1 where event_id='{$eventId}' order by id asc";
							$data_2_1 = $this->db->FetchQuery($sql); //print_r($result); print_r($data_2_1);die;
							
							if(is_array($data_2_1) && count($data_2_1)>0) {
								$hole_counter = 1;
								$final_data = array();
								for($j=$start_from_hole;$j<=($start_from_hole+17);$j++){
									$current_hole_number = $i = ($j<=18) ? $j : ($j-18);
									
									$has_score = false;
									
									if($is_team) {
										$a1_score = isset($result[0]["score_entry_{$i}"]) ? $result[0]["score_entry_{$i}"] : 0;
										$a2_score = isset($result[1]["score_entry_{$i}"]) ? $result[1]["score_entry_{$i}"] : 0;
										$b1_score = isset($result[2]["score_entry_{$i}"]) ? $result[2]["score_entry_{$i}"] : 0;
										$b2_score = isset($result[3]["score_entry_{$i}"]) ? $result[3]["score_entry_{$i}"] : 0;
										$team_a_id = isset($result[0]["team_id"]) ? $result[0]["team_id"] : 0;
										$team_b_id = isset($result[2]["team_id"]) ? $result[2]["team_id"] : 0;
										if($a1_score > 0 && $a2_score > 0 && $b1_score > 0 && $b2_score > 0) {
											$has_score = true;
										}
									}
									
									if($has_score) {
										foreach($data_2_1 as $a=>$b) {
											$ky = "hole_number_{$hole_counter}";
											$final_data[$ky]['hole_number'] = $current_hole_number;
											
											if($current_hole_number == $b['hole_number']) {
												
												// set first array
												if($b['2_point'] == $b['1_point'] && $b['2_point'] == $team_a_id) {
													$final_data[$ky]['hole_score'] = "3";
													$final_data[$ky]['hole_color'] = '#ff0000';
												}
												elseif($b['2_point'] == $b['1_point'] && $b['2_point'] == $team_b_id) {
													$final_data[$ky]['hole_score'] = "3";
													$final_data[$ky]['hole_color'] = '#58b3fa';
												}
												elseif($b['2_point'] == $b['1_point'] && $b['2_point'] == 0) {
													$final_data[$ky]['hole_score'] = "0";
													$final_data[$ky]['hole_color'] = '#000000';
												}
												elseif($b['2_point'] == $team_a_id) {
													$final_data[$ky]['hole_score'] = "2";
													$final_data[$ky]['hole_color'] = '#ff0000';
													if($b['1_point'] == $team_b_id) {
														$final_data[$ky]['hole_score'] = "1";
													}
												}
												elseif($b['2_point'] == $team_b_id) {
													$final_data[$ky]['hole_score'] = "2";
													$final_data[$ky]['hole_color'] = '#58b3fa';
													if($b['1_point'] == $team_a_id) {
														$final_data[$ky]['hole_score'] = "1";
													}
												}
												elseif($b['2_point'] == 0) {
													if($b['1_point'] == $team_a_id) {
														$final_data[$ky]['hole_score'] = "1";
														$final_data[$ky]['hole_color'] = '#ff0000';
													}
													elseif($b['1_point'] == $team_b_id) {
														$final_data[$ky]['hole_score'] = "1";
														$final_data[$ky]['hole_color'] = '#58b3fa';
													}
													else {
														$final_data[$ky]['hole_score'] = "0";
														$final_data[$ky]['hole_color'] = '#000000';
													}
												}
												
												
												// set second array  
												if($b['winner'] == $team_a_id) {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#ff0000';
												}
												elseif($b['winner'] == $team_b_id) {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#58b3fa';
												}
												else {
													$final_data[$ky]['agg_score'] = strval($b['score_value']);
													$final_data[$ky]['agg_color'] = '#000000';
												}
												break;
											}
										}
									}
									$hole_counter++;
								}
								//print_r($final_data);die;
								$final_data1 = array();
								foreach($final_data as $a=>$b) {
									$final_data1[] = $b;
								}
								$fdata['status'] = '1';
								$fdata['data'] = $final_data1;
								//$fdata['data']['total_holes_played'] = count($final_data);
							}
							else {
								$fdata['status'] = '0';
								$fdata['message'] = '2-1 Score Not Generated.';
							}
						}
						else {
							$fdata['status'] = '0';
							$fdata['message'] = 'Score Not Generated For This Game Format.';
						}
					}
					else {
						$fdata['status'] = '0';
						$fdata['message'] = 'Invalid Game Format.';
					}
				}
				else {
					$fdata['status'] = '0';
					$fdata['message'] = 'Event Not Exists.';
				}
			}
			else {
				$fdata['status'] = '0';
				$fdata['message'] = 'Required fields not found.';
			}
			return $fdata;
		}
		
	public function saveScoreCard($data){
		$fdata =array();
		$eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']>0)?$data['admin_id']:'0';
		$strokeId = (isset($data['stroke_id']) && $data['stroke_id']!='')?$data['stroke_id']:'0';
		$par = (isset($data['par']) && $data['par']!='')?$data['par']:'3';
		$holeId = (isset($data['hole_number']) && $data['hole_number']>0)?$data['hole_number']:'0';
		
		$player_score = (isset($data['player_score']) && count($data['player_score']) && is_array($data['player_score']))?$data['player_score']:array();
		$event_admin_id = 0;
		
		$queryString = "select admin_id from event_table where event_id ='".$eventId."' limit 1";
		$event_admin_id = $this->db->FetchSingleValue($queryString);
		
		if(count($player_score)>0){
			foreach($player_score as $i=>$p){
				$fdata = $this->enterScore($p,$eventId,$admin_id,$strokeId,$par,$holeId);
			}
			foreach($player_score as $i=>$p){
				if($p['player_id'] == $event_admin_id) {
					$fdata1 = $this->enterScoreFormatData($p,$eventId,$admin_id,$strokeId,$par,$holeId);
				}
			}
			
			//die;
		}//die;
			return $fdata;
		
	}
	
	public function saveScoreCardTemp($data){
		$fdata =array();
		$eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$admin_id = 0;
		$strokeId = 0;
		$par = (isset($data['par']) && $data['par']!='')?$data['par']:'3';
		$holeId = (isset($data['hole_number']) && $data['hole_number']>0)?$data['hole_number']:'0';
		
		$player_score = (isset($data['player_score']) && count($data['player_score']) && is_array($data['player_score']))?$data['player_score']:array();
		
		if(count($player_score)>0){
			foreach($player_score as $i=>$p){
				
				$fdata = $this->enterScoreTemp($p,$eventId,$admin_id,$strokeId,$par,$holeId);
			
			}
		}
			return $fdata;
		
	}
	
	
	
	public function sendScorecardMail($data) {
		$fdata =array();
		$eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$player_id = (isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:'0';
		$send_mail_to_user = (isset($data['send_mail']) && $data['send_mail']==1)?'1':'0';
		if($eventId > 0 && $player_id > 0) {
			$sql = "SELECT e.golf_course_name,e.event_name,e.event_start_date_time,e.event_start_time,e.total_hole_num,e.hole_start_from,e.format_name,e.is_started,e.format_id,e.admin_id,e.golf_course_id FROM event_list_view e where e.event_id = '{$eventId}'";
			$event_data = $this->db->FetchRow($sql);
			
			$sql = "SELECT u.user_name,u.display_name FROM golf_users u where u.user_id = '{$player_id}'";
			$user_data = $this->db->FetchRow($sql);
			
			if(isset($event_data['golf_course_id']) && $event_data['golf_course_id'] > 0) {
				$img_path = __BASE_URI__."uploads/scorecard/images/";
				$game_holes = $event_data['total_hole_num'];
				$start_from_hole = $event_data['hole_start_from'];
				$event_name = $event_data['event_name'];
				$golf_course_name = $event_data['golf_course_name'];
				$golf_course_id = $event_data['golf_course_id'];
				$event_date = $event_data['event_start_date_time'];
				
				$s_total = $s_eagle = $s_birdie = $s_par = $s_bogey = $s_double_bogey = 0;
				
				$parqryString_arr = $score_string_arr = $putt_string_arr = $fairway_string_arr = $sand_string_arr = array();
				
				for($i = 1; $i<= 18; $i++) {
					$parqryString_arr[] = "hole_index_{$i},par_value_{$i}";
					$score_string_arr[] = "score_entry_{$i}";
					$putt_string_arr[] = "no_of_putt_{$i},gir_{$i}";
					$fairway_string_arr[] = "fairway_{$i}";
					$sand_string_arr[] = "sand_{$i}";
				}
				$parqryString = implode(',',$parqryString_arr);
				$scrqryString = implode(',',$score_string_arr);
				$puttqryString = implode(',',$putt_string_arr);
				$fairwayqryString = implode(',',$fairway_string_arr);
				$sandqryString = implode(',',$sand_string_arr);
				
				$parqry=' select '.$parqryString.' from golf_hole_index where golf_course_id ='.$golf_course_id.''; 
				$rowparValues = $this->db->FetchRow($parqry);
				
				$parqry=' select '.$scrqryString.',total_score,gross_score from event_score_calc where event_id = "'.$eventId.'" and player_id = "'.$player_id.'"'; 
				$row_score = $this->db->FetchRow($parqry);//print_r($row_score);
				
				$parqry=' select '.$puttqryString.',per_gir,per_hole,gir_yes,gir_no from event_score_calc_no_of_putt where event_id = "'.$eventId.'" and player_id = "'.$player_id.'"'; 
				$row_putts = $this->db->FetchRow($parqry);
				
				$parqry=' select '.$fairwayqryString.' from event_score_calc_fairway where event_id = "'.$eventId.'" and player_id = "'.$player_id.'"'; 
				$row_fairways = $this->db->FetchRow($parqry);
				
				$parqry=' select '.$sandqryString.' from event_score_calc_sand where event_id = "'.$eventId.'" and player_id = "'.$player_id.'"'; 
				$row_sand = $this->db->FetchRow($parqry);
				
				$html = '';
				$html = '<html><head><title>newsletter</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>.table-grid{border-top:1px solid #ccc;}.table-grid td,.table-grid th{border-bottom: 1px solid #ccc;text-align: center;border-right: 1px solid #ccc;}.table-grid td:nth-child(even),.table-grid th:nth-child(even){background:#f7f7f7;}.table-grid td:last-child,.table-grid th:last-child{border-right:none;}</style></head>';
				
				$html .= '<body bgcolor="#fff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"><table id="Table_01" width="830" border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="font-family:arial;font-size:14px;color:#000;border:1px solid #ccc;margin:auto"><tr><td colspan="'.$game_holes.'" align="center" bgcolor="#0b5a97"><img src="'.$img_path.'banner.jpg" width="214" height="207" alt=""></td></tr><tr><td colspan="'.$game_holes.'" height="30"></td></tr><tr><td colspan="'.$game_holes.'" style="padding:6px 9px;font-family:arial;font-size:19px;color:#000;line-height:22px;"><span style="font-weight:bold;">'.$event_name.'</span>, '.$golf_course_name.' (<em style="color:#696969;">'.date('F d, Y',strtotime($event_date)).'</em>)</td></tr><tr><td colspan="'.$game_holes.'" height="30"></td></tr><tr><td colspan="'.$game_holes.'" height="35" bgcolor="#0056a5" style="padding:0 6px;color:#fff;font-size:15px;font-weight:normal;text-transform:uppercase;"><b>Scorecard</b></td></tr><tr><td colspan="'.$game_holes.'" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="9" class="table-grid"><tr style="font-weight:bold;"><th style="text-align:left;color:#464646;">Hole</th>';
				
				// hole numbers
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$html .= '<th>'.$i.'</th>';
				}
				$html .= '<th>TOT</th></tr><tr><td style="text-align:left;color:#464646;">Par</td>';
				
				// par values
				$par_total = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $rowparValues["par_value_{$i}"];
					$par_total+=$x;
					$html .= '<td>'.$x.'</td>';
				}
				
				// index values
				$html .= '<td style="color:#464646;">'.$par_total.'</td></tr><tr><td style="text-align:left;color:#464646;">Index</td>';
				
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $rowparValues["hole_index_{$i}"];
					$html .= '<td>'.$x.'</td>';
				}
				
				// hole score
				$html .= '<td style="color:#464646;">--</td></tr><tr><td bgcolor="#e9eaeb" style="text-align:left;">Gross Score</td>';
				
				$s_par3 = $s_par4 = $s_par5 = array();
				
				$total_score = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$par = $rowparValues["par_value_{$i}"];
					$score = $row_score["score_entry_{$i}"];
					$total_score += $score;
					if($score > 0) {
						$difference = $score - $par;
						$bgcolor = '';
						
						if( $par == 3){ $s_par3[]=$score; }
						else if( $par == 4){ $s_par4[]=$score; }
						else if( $par == 5){ $s_par5[]=$score; }
						
						if( $difference <= -2){ $s_eagle++; $bgcolor='#f4aa43'; }
						else if( $difference == -1){ $s_birdie++; $bgcolor='#0a5c87'; }
						else if( $difference == 0){ $s_par++; $bgcolor='#325604'; }
						else if( $difference == 1){ $s_bogey++; $bgcolor='#939494'; }
						else if( $difference >= 2){ $s_double_bogey++; $bgcolor='#000000'; }
						$html .= '<td style="background:'.$bgcolor.';color:#fff;">'.$score.'</td>';
					}
					else {
						$html .= '<td style="background:#e9eaeb;color:#000;">0</td>';
					}
					
					
				}
				
				// total putts
				$html .= '<td style="color:#464646;">'.$total_score.'</td></tr><tr><td style="text-align:left;color:#464646;">No. of Putts</td>';
				$total_putts = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = isset($row_putts["no_of_putt_{$i}"]) ? $row_putts["no_of_putt_{$i}"] : '-';
					if($x>=0) {
						$total_putts += $x;
					}
					else {
						$x = '-';
					}
					
					$html .= '<td>'.($x>=0 ? $x : '-').'</td>';
				}
				
				// fairways
				$left_count = $hit_count = $right_count = $fcount = 0;
				$html .= '<td style="color:#464646;">'.$total_putts.'</td></tr><tr><td style="text-align:left;color:#464646;">Fairways (L/H/R)</td>';
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $row_fairways["fairway_{$i}"];
					
					if($x == 1) { $left_count++;$x = 'L'; }
					else if($x == 2) { $hit_count++;$x = 'H'; }
					else if($x == 3) { $right_count++;$x = 'R'; }
					else { $x = '-'; }
					
					$html .= '<td>'.$x.'</td>';
				}
				$fcount = $left_count + $hit_count + $right_count;
				
				// sand
				$html .= '<td style="color:#464646;">--</td></tr><tr><td style="text-align:left;color:#464646;">Sand</td>';
				$total_sands = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $row_sand["sand_{$i}"];
					if($x >= 0) {
						$total_sands += $x;
					}
					$html .= '<td>'.($x>=0 ? $x : '-').'</td>';
				}
				
				
				$html .= '<td style="color:#464646;">'.$total_sands.'</td></tr><tr><td colspan="'.($game_holes+2).'" height="40"></td></tr><tr bgcolor="#0056a5"><th colspan="'.($game_holes+2).'" height="35" style="text-align:left;color:#fff;font-size:15px;font-weight:normal;text-transform:uppercase;"><b>STATS</b></th></tr><tr><td width="16%" style="text-align:left;color:#464646;">GIR</td>';
				
				// gir :: cross.png | tick.png
				$total_girs = $total_yes_girs = $total_no_girs = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $row_putts["gir_{$i}"];
					$img = $img_path.'cross.png';
					if($x == 1) {
						$total_yes_girs++;
						$img = $img_path.'tick.png';
					}
					$total_girs++;
					$html .= '<td><img src="'.$img.'" width="15" height="15" alt="" /></td>';
				}
				
				$total_no_girs = $total_girs - $total_yes_girs;
				
				$html .= '<td width="8%" style="color:#464646;">'.($total_yes_girs.'/'.$total_girs).'</td></tr><tr><td style="text-align:left;color:#464646;">Sand Saves</td>';
				
				// sand saves :: cross.png | tick.png | -
				$total_sand = $total_yes_sand = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $row_sand["sand_{$i}"];
					
					$par = $rowparValues["par_value_{$i}"];
					$score = $row_score["score_entry_{$i}"];
					if($x >= 0) {
						$img = $img_path.'cross.png';
						if($score<=$par) {
							$total_yes_sand++;
							$img = $img_path.'tick.png';
						}
						$total_sand++;
						$html .= '<td><img src="'.$img.'" width="15" height="15" alt="" /></td>';
					}
					else {
						$html .= '<td>-</td>';
					}
				}
				
				$html .= '<td style="color:#464646;">'.($total_yes_sand.'/'.$total_sand).'</td></tr><tr><td style="text-align:left;color:#464646;">Recovery</td>';
				
				// Recovery :: cross.png | tick.png | -
				$total_recovery = $total_yes_recovery = 0;
				for($i=$start_from_hole; $i<=$game_holes; $i++){
					$x = $row_putts["gir_{$i}"];
					
					$par = $rowparValues["par_value_{$i}"];
					$score = $row_score["score_entry_{$i}"];
					if($x == 2) {
						$img = $img_path.'cross.png';
						if($score<=$par) {
							$total_yes_recovery++;
							$img = $img_path.'tick.png';
						}
						$total_recovery++;
						$html .= '<td><img src="'.$img.'" width="15" height="15" alt="" /></td>';
					}
					else {
						$html .= '<td>-</td>';
					}
				}
				
				//echo $s_eagle.'__'.$s_birdie.'__'.$s_par.'__'.$s_bogey.'__'.$s_double_bogey;die;
				
				$html .= '<td style="color:#464646;">'.($total_yes_recovery.'/'.$total_recovery).'</td></tr></table></td></tr><tr><td colspan="'.$game_holes.'">';
				
				$row_putts["per_gir"] = (is_numeric($row_putts["per_gir"])) ? $row_putts["per_gir"] : 0;
				$row_putts["per_hole"] = (is_numeric($row_putts["per_hole"])) ? $row_putts["per_hole"] : 0;
				
				$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="5" style="font-weight:bold;font-size:14px;text-transform:uppercase;"><tr><td align="center" width="50%" style="border-bottom:1px solid #ccc;border-right:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px 0  43px 0;padding-left:10px;font-size: 15px;line-height: 35px;text-align: center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;"><b>PUTTING</b></div><div style="display:table;width:100%;padding-bottom:20px;"><div style="display:inline-block;padding:10px;"><span style="font-size:25px;display:block;color:#000;">'.$row_putts["per_gir"].'</span> <br><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12px">Putts per GIR</span></div><div style="display:inline-block;padding:10px;"><span style="font-size:25px;display:block;color:#000;">'.$row_putts["per_hole"].'</span> <br><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12px">Putts per hole</span></div><div style="display:inline-block;padding:10px;"><span style="font-size:25px;display:block;color:#000;">'.$total_putts.'</span> <br><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12px">Putts per round</span></div></div></td>';
				
				$avg_eagle = ($total_score > 0) ? round(($s_eagle*100)/$total_score) : 0;
				$avg_birdie = ($total_score > 0) ? round(($s_birdie*100)/$total_score) : 0;
				$avg_par = ($total_score > 0) ? round(($s_par*100)/$total_score) : 0;
				$avg_bogey = ($total_score > 0) ? round(($s_bogey*100)/$total_score) : 0;
				$avg_double_bogey = ($total_score > 0) ? round(($s_double_bogey*100)/$total_score) : 0;
				
				$pre_range = ($game_holes == 9) ? "0|3|6|9" : "0|3|6|9|12|15|18";
				$pre_chds = ($game_holes == 9) ? "0,9" : "0,18";
				
				$html .= '<td align="center" width="50%" style="border-bottom:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px;padding-left:10px; font-size: 15px;line-height: 35px;text-align:center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;"><b>Scoring</b></div><img src="https://chart.googleapis.com/chart?chs=384x181&chco=f2a942|0056a5|008000|939494|000000&chtt=&cht=bvs&chd=s:Paz9&chxt=y,x&chd=t:'.$s_eagle.','.$s_birdie.','.$s_par.','.$s_bogey.','.$s_double_bogey.'&chxl=0:|'.$pre_range.'|1:|EAGLE%2B|BIRDIE|PAR|BOGEY|D.%20BOGEY%2B&chbh=50,20,26&chds='.$pre_chds.'" width="384" height="181" alt="" /></td></tr>';
				
				$avg_yes_gir = ($total_girs > 0) ? round(($total_yes_girs*100)/$total_girs) : 0;
				$avg_no_gir = ($total_girs > 0) ? round(($total_no_girs*100)/$total_girs) : 0;
				$perc_yes_gir = ($total_girs > 0) ? round(($total_yes_girs*100)/$total_girs,0) : 0;
				$perc_no_gir = ($total_girs > 0) ? round(($total_no_girs*100)/$total_girs,0) : 0;
				
				$html .= '<tr><td align="center" width="50%" style="border-bottom:1px solid #ccc;border-right:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px;padding-left:10px; font-size: 15px;line-height: 35px;text-align:center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;"><b>GIR</b></div><img src="https://chart.googleapis.com/chart?chc=corp&cht=pc&chd=s:eYY,ORVM&chco=c6e2ff|c6e2ff,006699|c6e2ff&chs=396x286&chdl=MISSED|HIT&chd=t:0|'.$avg_yes_gir.','.$avg_no_gir.'|0&chtt=&chl=dss|'.$perc_yes_gir.'%|'.$perc_no_gir.'%" width="396" height="286" alt=""></td>';
				
				$perc_left_count = ($fcount > 0) ? round(($left_count*100)/$fcount) : 0;
				$perc_hit_count = ($fcount > 0) ? round(($hit_count*100)/$fcount) : 0;
				$perc_right_count = ($fcount > 0) ? round(($right_count*100)/$fcount) : 0;
				
				//$html .= '<td align="center" width="50%" style="border-bottom:1px solid #ccc;"><img src="https://chart.googleapis.com/chart?cht=bhs&chco=3367cd,dc3812,fe9900&chs=396x286&chd=s:FOE,elo&chxt=x,y&chxl=1:||0:|0%|20%|40%|60%|80%|100%|&chtt=FAIRWAYS&chbh=100,30,90&chd=t:'.$perc_left_count.'|'.$perc_hit_count.'|'.$perc_right_count.'&chdl=LEFT%20MISSED|HIT|RIGHT%20MISSED&chds=0,100" width="396" height="286" alt=""></td></tr>';
				//$html .= '<td align="center" width="50%" style="border-bottom:1px solid #ccc;"><img src="https://chart.googleapis.com/chart?cht=p&chtt=FAIRWAYS&chs=496x340&chd=t:'.$perc_left_count.','.$perc_hit_count.','.$perc_right_count.'&chco=3367cd,dc3812,fe9900&chdl=LEFT%20MISSED|HIT|RIGHT%20MISSED&chl='.$perc_left_count.'%|'.$perc_hit_count.'%|'.$perc_right_count.'%" width="396" height="286" alt=""></td></tr>';
				
				$html .= '<td align="center" width="50%" style="border-bottom:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px 0  43px 0;padding-left:10px; font-size: 15px;line-height: 35px;text-align:center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;"><b>FAIRWAYS</b></div><div style="display:table;width:100%;padding-bottom:7px;"><div style="display:table-cell;"><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12px;margin:7px 0;display:inline-block;">Hit</span><br><span style="font-size:25px;display:block;color:#000;">'.$perc_hit_count.'%</span></div></div><img src="'.$img_path.'fairway.jpg" width="238" height="152" alt=""><div style="display:table;width:100%;padding-bottom:20px;"><div style="display:table-cell;width:50%;"><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12px;margin:7px 0;display:inline-block;">Left</span><br><span style="font-size:25px;display:block;color:#000;">'.$perc_left_count.'%</span></div><div style="display:table-cell;width:50%;"><span style="border-radius:20px;padding:5px 12px;background:#0056a5;color:#fff;font-size:12pxm;margin:7px 0;display:inline-block;">Right</span><br><span style="font-size:25px;display:block;color:#000;">'.$perc_right_count.'%</span></div></div></td></tr>';
				$perc_total_sand = ($total_sand > 0) ? round(($total_yes_sand*100)/$total_sand) : 0;
				$perc_total_recovery = ($total_recovery > 0) ? round(($total_yes_recovery*100)/$total_recovery) : 0;
				
				$html .= '<tr><td align="center" width="50%" style="border-bottom:1px solid #ccc;border-right:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px;padding-left:10px; font-size: 15px;line-height: 35px;text-align:center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;padding: 0px;vertical-align: top;"><b>Recovery</b></div><img src="https://chart.googleapis.com/chart?chs=396x268&chco=0055a5|c6e2ff&chtt=&cht=bvs&chd=s:Paz9&chxt=y,x&chd=t:'.$perc_total_sand.','.$perc_total_recovery.'&chxl=0:|0%|20%|40%|60%|80%|100%|1:|SAND SAVES|SCRAMBLE&chbh=100,50,100&chds=0,100" width="396" height="268" alt="" /></td>';
				
				$par3_avg = (count($s_par3) > 0) ? round(array_sum($s_par3)/count($s_par3),2) : 0;
				$par4_avg = (count($s_par4) > 0) ? round(array_sum($s_par4)/count($s_par4),2) : 0;
				$par5_avg = (count($s_par5) > 0) ? round(array_sum($s_par5)/count($s_par5),2) : 0;
				
				$maxval = max(array($par3_avg,$par4_avg,$par5_avg))+1;
				
				$divby = ($maxval>=4) ? 4 : $maxval;
				$xarr = array();
				$dif = $dif123 = round($maxval/$divby,1);
				for($i=0;$i<$divby;$i++) {
					$xarr[$i] = ($i==0) ? $dif : ($dif+$xarr[($i-1)]);
				}
				
				$xstr = '0|'.implode('|',$xarr);
				
				$html .= '<td align="center" width="50%" style="border-bottom:1px solid #ccc;padding: 0px;vertical-align: top;"><div style="margin:0px;padding-left:10px; font-size: 15px;line-height: 35px;text-align:center;background:#0056a5;color:#fff;font-weight:normal;text-transform:uppercase;"><b>Scores by par</b></div><img src="https://chart.googleapis.com/chart?chs=396x268&chco=0055a5|1E90FF|c6e2ff&chtt=&cht=bvs&chd=s:Paz9&chxt=y,x&chd=t:'.($par3_avg).','.($par4_avg).','.($par5_avg).'&chxl=0:|'.$xstr.'|1:|PAR 3s|PAR 4s|PAR 5s&chbh=80,50,30&chds=0,'.($maxval).'" width="396" height="268" alt="" /></td>';
				
				$html .= '</tr></table></td></tr><tr><td colspan="'.$game_holes.'" height="27">&nbsp;</td></tr><tr>';
				for($i=1;$i<=$game_holes;$i++) {
					$html .= '<td><img src="'.$img_path.'spacer.gif" width="62" height="1" alt=""></td>';
				}
				$html .= '</tr><tr><td colspan="'.$game_holes.'" height="30" bgcolor="#0056a5" style="padding:0 15px;color:#fff;font-size:14px;font-weight:normal;" align="center"><strong>PUTT2GETHER</strong> - Your own leaderboard app</td></tr></table></body></html>';
				
				//echo $html;die;
				if($send_mail_to_user == '1') {
					$subject = 'PUTT2GETHER SCORECARD';
					sendmail($user_data['user_name'], $user_data['display_name'], $subject, $html,'','',true);
					//sendmail('sachin@soms.in', $user_data['display_name'], $subject, $html,'','',true);
					//sendmail('abhinav@soms.in', $user_data['display_name'], $subject, $html,'','',true);
					$fdata['status'] = '1';
					$fdata['message'] = 'Mail Send Succesfully';
				}
				else {
					$fdata['status'] = '1';
					$fdata['message'] = $html;
				}
			}
			else {
				$fdata['status'] = '0';
				$fdata['message'] = 'Event not found.';
			}
		}
		else {
			$fdata['status'] = '0';
			$fdata['message'] = 'Required fields not found.';
		} //print_r($fdata);die;
		return $fdata;
	}
}
?>