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
		if($golf_course_id >0 && $hole_number > 0){
		 
		$sqlQuery='SELECT hole_index_'.$hole_number.' as hole_index,par_value_'.$hole_number.' as par_value FROM golf_hole_index WHERE golf_course_id="'.$golf_course_id.'"';  
		$golfdatta  = $this->db->FetchRow($sqlQuery);

$is_spot_type = 0;
					$sqlQueryh="SELECT type FROM event_is_spot_tbl WHERE event_id='".$event_id."' and hole_number =".$hole_number."";  
					$spotdata=$this->db->FetchSingleValue($sqlQueryh);
$golfdatta['is_spot_type']  = ($spotdata)?$spotdata:0;
$teamdata=$this->checkTeamData($event_id);
$golfdatta['is_team']=0;
if(count($teamdata) > 0){
$golfdatta['is_team']=1;
$first_team_id=$teamdata['first_team_id'];
    $golfdatta['teamdata']=$teamdata['current_standing'];
}
/* $queryString = " select t.player_id,t.handicap_value,t.score_entry_".$hole_number." as hole_num_".$hole_number.", p.no_of_putt_". */
$sqlp="SELECT t.player_id,g.full_name as player_name,t.format_id,t.no_of_holes_played,ep.team_id FROM event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list ep ON ep.event_id=t.event_id and ep.player_id=t.player_id where t.event_id='".$event_id."' group by t.player_id ";

							$sqlresultty  = $this->db->FetchQuery($sqlp);
$player_counter=0;
if(count($sqlresultty) > 0){
foreach($sqlresultty as $x=>$k){
$player_counter++;    
$k['is_handicap_gain']='';	
$lastscore=array();
$sql3='SELECT hole_number,handicap_value,handicap_value_3_4,calculated_handicap FROM event_score_calc  WHERE player_id="'.$k['player_id'].'" and event_id="'.$event_id.'"'; 
$handicapvalue= $this->db->FetchRow($sql3);
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
 if($k['format_id']=="10" || $k['format_id']=="11" || $k['format_id']=="12" || $k['format_id']=="13" || $k['format_id']=="14"){                                       
        if($golfdatta['is_team'] > 0){
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
$queryString = " select t.player_id,t.handicap_value,t.score_entry_".$hole_number." as hole_num_".$hole_number.", p.no_of_putt_".$hole_number." as no_of_putt,f.fairway_".$hole_number." as fairway,s.sand_".$hole_number." as sand,c.closest_feet_".$hole_number." as closest_feet from event_score_calc t LEFT JOIN event_score_calc_no_of_putt p ON p.event_id =t.event_id and p.player_id=t.player_id LEFT JOIN event_score_calc_sand s ON s.event_id =t.event_id and s.player_id=t.player_id LEFT JOIN event_score_calc_fairway f ON f.event_id =t.event_id and f.player_id=t.player_id LEFT JOIN event_score_calc_closest_feet c ON c.event_id =t.event_id and c.player_id=t.player_id where t.event_id ='".$event_id."' and p.player_id='".$k['player_id']."'"; 
$golfdattaspot = $this->db->FetchRow($queryString);
	
$clo = (isset($golfdattaspot['closest_feet']) && $golfdattaspot['closest_feet'] !='')?$golfdattaspot['closest_feet']:'';
$feet='';$inches='';
if($clo!=""){
$closeset = explode(',', $clo);	
$feet = $closeset[0];
$inches = $closeset[1];
}
$k['no_of_putt']=isset($golfdattaspot['no_of_putt'])?$golfdattaspot['no_of_putt']:0;	
$k['sand']=isset($golfdattaspot['sand'])?$golfdattaspot['sand']:0;	
if($golfdatta['par_value']==3){
$k['fairway']=4;	
}else{
$k['fairway']=isset($golfdattaspot['fairway'])?$golfdattaspot['fairway']:0;	
}
$k['closest_feet']=$feet;	
$k['closest_inch']=$inches;	
$k['score_value']=(isset($golfdattaspot['hole_num_'.$hole_number]) && $golfdattaspot['hole_num_'.$hole_number] > 0)?$golfdattaspot['hole_num_'.$hole_number]:$golfdatta['par_value'];	
// $k['current_hole_number']=$handicapvalue['hole_number'];

$k['current_hole_number']=$hole_number ;
if($lastscore!=""){
$k['last_score'][]=$lastscore;		
}else{
	$k['last_score']=array();
}

$total[$x]=$k;	
}
}
			$fdata['status'] = '1';
			$fdata['data'] = $golfdatta;
$fdata['total'] = $total;
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
                $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by t.team_profile_id asc";  
                $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
                $team_id=array();$player_idArr=array();
                if(count($sqlresult1) >0){
                            foreach($sqlresult1 as $i=>$e){
                                    $player_idArr[]=$e['player_id'];
                                    if(!in_array($e['team_id'],$team_id)){
                                    $team_id[]=$e['team_id'];
                                    }
                            }
                }						
                $uniqueteam=array_unique($team_id);
                $game_type = (count($player_idArr)=="4")?'team':'';				
                $is_team_game = ($game_type == 'team') ? true : false;
                $currentScoreListArray['is_team'] =($is_team_game)?"1":"0";
                    if($is_team_game){
                       $queryString = " select p.event_admin_id,p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name ";
                       $queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' order by p.team_id asc ";
                       //echo $queryString;
                       $teamrec = $this->db->FetchQuery($queryString);	
                       foreach($teamrec as $t=>$row){
                            if($uniqueteam[0]==$row['team_id']){
                                $teamplayerarray1[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                            }else{
                                $teamplayerarray2[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                            } 
                       }
                       $teamarray[]=array('team_id'=>$uniqueteam[0],'team_name'=>'Team A','player_list'=>$teamplayerarray1);
                       $teamarray[]=array('team_id'=>$uniqueteam[1],'team_name'=>'Team B','player_list'=>$teamplayerarray2);
                       //$event_admin_id=$teamrec[0]['event_admin_id'];
                       $standingdata=$this->getStandingForNewGameFormat($total_num_hole,$stroke_play_id,$eventId,$event_admin_id);
                       $teamarray[]=array('current_standing'=>$standingdata['current'],'first_team_id'=>$uniqueteam[0]);
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
								$resultfinal[]=$result1;
								
							}
						}
					}elseif($stroke_play_id=="10"){
						$sql="SELECT hole_number,winner,event_id,score_value,color FROM `event_score_matchplay` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['score_value'] = $e['score_value'];
								$result1['winner'] = $e['winner'];
								$result1['color'] =$e['color'];
								$resultfinal[]=$result1;								
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
						$sql="SELECT hole_number,winner,event_id,score_value FROM `event_score_vegas` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['score_value'] = $e['score_value'];
								$result1['winner'] = $e['winner'];
                                                                $result1['color'] = $this->getColorCode("black");
								$resultfinal[]=$result1;								
							}
						}
					}elseif($stroke_play_id=="14"){
						$sql="SELECT hole_number,2_point,1_point,winner,event_id,score_value FROM `event_score_2_1` where event_id='".$event_id."' order by hole_number asc";
						$sqlresult1  = $this->db->FetchQuery($sql);
						if(count($sqlresult1) >0){
							foreach($sqlresult1 as $i=>$e){
								$result1['hole_number'] = $e['hole_number'];
								$result1['2_point'] = $e['2_point'];
								$result1['1_point'] = $e['1_point'];
								$result1['score_value'] = $e['score_value'];
								$result1['winner'] = $e['winner'];
                                                                $result1['color'] = $this->getColorCode("black");
								$resultfinal[]=$result1;
								
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
	function getScoreCardData($data){
		$fdata =array();
		$event_id = (isset($data['event_id']) && $data['event_id']!='')?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']!='')?$data['admin_id']:'0';
		if($event_id >0 && $admin_id > 0){			
			$sqlQuery="SELECT e.event_id,e.format_id,e.event_name,e.golf_course_id,g.golf_course_name,e.total_hole_num,e.hole_start_from,e.is_spot FROM event_table e left join golf_course g ON g.golf_course_id=e.golf_course_id WHERE e.event_id='".$event_id."'";  
			$sqlresult  = $this->db->FetchRow($sqlQuery);
			$is_spot_type = 0;
			if($sqlresult['is_spot'] > 0){
			$sqlQueryh="SELECT type FROM event_is_spot_tbl WHERE event_id='".$event_id."' and hole_number =".$sqlresult['hole_start_from']."";  
			$is_spot_type  = $this->db->FetchSingleValue($sqlQueryh);
			}			
            if(is_array($sqlresult) && count($sqlresult)>0){
		$sqlQuery1="SELECT p.event_admin_id,p.team_id,p.player_id,p.is_accepted,u.full_name,s.calculated_handicap as self_handicap,s.score_entry_1,s.score_entry_2,s.score_entry_3,s.score_entry_4,s.score_entry_5,s.score_entry_6,s.score_entry_7,s.score_entry_8,s.score_entry_9,s.score_entry_10,s.score_entry_11,s.score_entry_12,s.score_entry_13,s.score_entry_14,s.score_entry_15,s.score_entry_16,s.score_entry_17,s.score_entry_18 FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id LEFT JOIN event_score_calc s ON s.player_id=p.player_id and s.event_id=p.event_id WHERE e.event_id='".$event_id."' and (p.scorere_id = '".$admin_id."' OR p.player_id='".$admin_id."') and p.is_accepted ='1' "; 
		$sqlresult1  = $this->db->FetchQuery($sqlQuery1);

			$sql2='SELECT hole_index_'.$sqlresult['hole_start_from'].' as hole_index,par_value_'.$sqlresult['hole_start_from'].' as par_value FROM golf_hole_index WHERE golf_course_id="'.$sqlresult['golf_course_id'].'"';  
			$holenumb  = $this->db->FetchRow($sql2);
$players =$player_admin =array();
			if(count($sqlresult1) >0){
             
				foreach($sqlresult1 as $i=>$e){
                                        $nam = explode(' ',$e['full_name']);
					$namf = (isset($nam[0]) && $nam[0] !='')?$nam[0]:'';
					$naml = (isset($nam[1]) && $nam[1] !='')?$nam[1]:'';
					if(in_array($last, $e)){
						$first = (isset($naml[0]) && $naml[0] !='')?substr($namf, 0, 2):substr($namf, 0, 2);
						$last = (isset($naml[0]) && $naml[0] !='')?$first.' '.$naml[0]:$first;
					}else{
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
								$e['last_hole_played'] = (isset($e['played_hole_number']) && count($e['played_hole_number'])>0)?end($e['played_hole_number']):0;
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

if($e['player_id'] == $e['event_admin_id']){
						$player_admin[] = $e;
					}else{
						$players[] = $e;
					}
$d= (isset($e['team_id']) && $e['team_id'] >0)?1:0;
					//$player[] = $e;
				}

$player = array_merge($player_admin,$players);
			$fdata['status'] = '1';
			$fdata['event_id'] = $sqlresult['event_id'];
			$fdata['event_name'] = $sqlresult['event_name'];
			$fdata['format_id'] = $sqlresult['format_id'];
			$fdata['golf_course_id'] = $sqlresult['golf_course_id'];
			$fdata['golf_course_name'] = $sqlresult['golf_course_name'];
			$fdata['hole_start_from'] = $sqlresult['hole_start_from'];
			$fdata['total_hole_num'] = $sqlresult['total_hole_num'];
$fdata['is_team'] = $d;
$fdata['is_spot_type'] = $is_spot_type;
$fdata['par_value'] = $holenumb['par_value'];
$fdata['hole_index'] = $holenumb['hole_index'];
			$fdata['status'] = '1';
			$fdata['data'] = $player;
			$fdata['message'] = 'success';	
			}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Friend List Empty';	
			}
		}else{
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

	function saveScoreCard($data){
		
		$eventId = (isset($data['event_id']) && $data['event_id']>0)?$data['event_id']:'0';
		$admin_id = (isset($data['admin_id']) && $data['admin_id']>0)?$data['admin_id']:'0';
		$playerId = (isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:'0';
		$holeId = (isset($data['hole_number']) && $data['hole_number']>0)?$data['hole_number']:'0';
		$score = (isset($data['score']) && $data['score']>0)?$data['score']:'0';
		$no_of_putt = (isset($data['no_of_putt']) && $data['no_of_putt']>0)?$data['no_of_putt']:'0';
		$fairway = (isset($data['fairway']) && $data['fairway']>0)?$data['fairway']:'0';
		$sand = (isset($data['sand']) && $data['sand']>0)?$data['sand']:'0';
		$closest_feet = (isset($data['closest_feet']) && $data['closest_feet']!='')?$data['closest_feet']:'0';
		$closest_inch = (isset($data['closest_inch']) && $data['closest_inch']!='')?$data['closest_inch']:'0';
		$strokeId = (isset($data['stroke_id']) && $data['stroke_id']!='')?$data['stroke_id']:'0';
$par = (isset($data['par']) && $data['par']!='')?$data['par']:'3';
		
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
				$is_submit_score  = $this->db->FetchSingleValue($queryString);
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
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	$this->updatematchplayScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from);
						}elseif($stroke_play_id==11){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	$this->updateautopressScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from);
						}elseif($stroke_play_id==12){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	$this->update420Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from);
						}elseif($stroke_play_id==13){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    	$this->updateVegasScore($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from);
						}elseif($stroke_play_id==14){
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					        $this->update21Score($eventId,$stroke_play_id,$holeId,$score,$playerId,$total_hole_num,$golf_course_id,$hole_start_from);
						}else{
                                                $this->updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
					    
						$this->update34NetStrokePlayScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateMoreNetHandicapValue($eventId,$golf_course_id,$playerId,$stroke_play_id,$holeId);
						$this->updateGrossStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updateNetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->update34NetStablefordScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updatePeoriaScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
						$this->updatePosition($eventId,$stroke_play_id,$golf_course_id);		
						}

						$this->updateNetScoreAccordingtoCalculatedhandicap($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id);
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
			$queryResult='';
                        if($holeId > 1){//$start_from_hole
				if($stroke_play_id==10){
				$queryString = " select score_value,winner,color from event_score_matchplay where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
				}elseif($stroke_play_id==11){
				$queryString = " select score_value,back_to_9_score,winner,color from event_score_autopress where hole_number='".$prevhole."' and event_id=".$eventId;
				$queryResult  = $this->db->FetchRow($queryString);
$queryResult['score_value']=json_decode($queryResult['score_value']);
								$backto9scoreArr=json_decode($queryResult['back_to_9_score']);
								$queryResult['back_to_9_score'] =(count($backto9scoreArr)>0)?json_decode($queryResult['back_to_9_score']):array();
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
						foreach($sqlresult1 as $i=>$e){
							/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($i=$counter;$i<=$total_hole_num;$i++){
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
							}
							$player_id[]=$e['player_id'];
							$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
							if(!in_array($e['team_id'],$team_id)){
							$team_id[]=$e['team_id'];
							}
						}
					}						
					$uniqueteam=array_unique($team_id);
					//print_r($player_id);
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
					if(count($score_a)>0 && count($score_a2)>0 && count($score_b)>0 && count($score_b2)>0) {
					$required_player_score=1;	
					}
				}else{
					$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
					$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
					if(count($score_a)>0 && count($score_b)>0) {
					$required_player_score=1;	
					}
				}
				if($required_player_score==1) {
					$queryString = " delete from event_score_matchplay where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
					$final_result = array();
					$pervious_winner_score = 0;
					$pervious_winner_name = '';
					$pervious_winner_class = '';
					foreach($score_a as $a=>$b) {
						$winner_name = $hole_winner_class = $bgclass = '';
						$team_a_sum = $team_b_sum = $winning_score = 0;
						$a1_val = $b;
						$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
						$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
						$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;
						$last_index = ($a==1) ? 0 : ($a-2);
						$current_index = ($a==1) ? 0 : ($a-1);
						$tpasum = $tpbsum = 0;
						if($is_team_game) {
							if($a2_val>0 && $a1_val>0) {
								$team_a_sum = ($a2_val<$a1_val) ? intval($a2_val) : intval($a1_val);
								$tpasum = ($a2_val<$a1_val) ? intval($a1_val) : intval($a2_val);
							}
							if($b2_val>0 && $b1_val>0) {
								$team_b_sum = ($b2_val<$b1_val) ? intval($b2_val) : intval($b1_val);
								$tpbsum = ($b2_val<$b1_val) ? intval($b1_val) : intval($b2_val);
							}
							if($tpasum!=$tpbsum && $team_a_sum == $team_b_sum) {
								$team_a_sum = $tpasum;
								$team_b_sum = $tpbsum;
							}
						}
						else {
							if($a1_val>0) {
								$team_a_sum = intval($a1_val);
							}
							if($b1_val>0) {
								$team_b_sum = intval($b1_val);
							}
						}
						if($team_a_sum>0 && $team_b_sum>0){
							if($team_a_sum < $team_b_sum) {
								// winner :: TEAM A
								$winner_name = $hole_winner_name = 'TEAM A';
								$hole_winner_class = $color_team_a;
								$winning_score = 1;
								$current_winner_class = $color_bg_a;
								if($current_index>0) {
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
								if($current_index>0) {
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
								
                                                                $winner_name = $hole_winner_name = ($pervious_winner_score>=1) ? $pervious_winner_name : 'AS';
                                                                $hole_winner_class = ($pervious_winner_score>=1) ? $pervious_winner_class : $color_team_both;
                                                                $winning_score = $pervious_winner_score;
                                                                $current_winner_class = ($pervious_winner_score>=1) ? $pervious_winner_class_bg : $color_bg_both;
								$final_result[$a] = array('winner'=>$winner_name,'hole_winner'=>$hole_winner_name,'current_winner_class'=>$current_winner_class,'winner_class'=>$hole_winner_class,'score'=>$winning_score);
							}//echo '<pre>'; print_r($final_result); echo '</pre>';
							
						if($winner_name == 'AS'){
							$winner_team_id=0;
						}else{
							if($is_team_game){
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
							}else{
								$winner_team_id=($winner_name=="TEAM A")?$player_id[0]:$player_id[1];
							}
						}
						//$scoreval=($winner_name=='AS')?"HALVED":($winning_score>0 ? ($winning_score.'UP') : 'AS');
						//$bgcolor=($scoreval=="HALVED")?$this->getColorCode("black"):$hole_winner_class;
$scoreval=($winner_name=='AS')?"AS":($winning_score>0 ? ($winning_score.'UP') : 'AS');
$bgcolor=($scoreval=="AS")?$this->getColorCode("black"):$hole_winner_class;
						$queryString = " insert into event_score_matchplay(event_score_calc_id,hole_number,event_id,winner,score_value,color) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner_team_id.",'".$scoreval."','".$bgcolor."')";
						$queryResult  = $this->db->FetchQuery($queryString);
							
							//if($winner_name!='AS') {
					$pervious_winner_name = $winner_name;
					$pervious_winner_class = $hole_winner_class;
					$pervious_winner_class_bg = $current_winner_class;
				//}
				$pervious_winner_score = $winning_score;
						}						
					}
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
				$sqlQuery1="SELECT p.team_id,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."'  and p.is_accepted='1'";  
			$sqlresult1  = $this->db->FetchQuery($sqlQuery1);
			if(count($sqlresult1) >0){
				foreach($sqlresult1 as $i=>$e){
					/*$sqlQuery1="SELECT event_score_calc_id,score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";*/
				        $sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
							
			        $sqlresult2  = $this->db->FetchQuery($sqlQuery1);
					for($i=$counter;$i<=$total_hole_num;$i++){
						$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
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
					//print_r($player_id);
				$game_type = (count($player_id)=="4")?'team':'';				
				$is_team_game = ($game_type == 'team') ? true : false;	
				if($is_team_game){
			$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
			$score_a2 = isset($player_id[1]) ? $player[$player_id[1]] : array();			
			$score_b = isset($player_id[2]) ? $player[$player_id[2]] : array();
			$score_b2 = isset($player_id[3]) ? $player[$player_id[3]] : array();
				}else{
				$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();			
			$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();							
				}
			//print_r($score_a);
			$zero_point = 0;$one_point = 1;$two_point = 2;$three_point = 3;$four_point = 4;	
			$start_value_first = $start_value_second = '0';	
			$color_team_a = $this->getColorCode("red");$color_team_b = $this->getColorCode("blue");$color_team_c = $this->getColorCode("green");
			$color_team_both = $this->getColorCode("black");$color_display_a =$this->getColorCode("red"); $color_display_b = $this->getColorCode("blue");	$color_display_c = $this->getColorCode("green");
			$color_display_both = "";$resultstr = $finalstr = '';$result_arr = $final_result_arr = array();$scorebackto9A=$scorebackto9B=array();
			if(count($score_a)>0 && count($score_b)>0) {
				$queryString = " delete from event_score_autopress where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);
				$add_new_zero = $remove_last_zero = false;
				$end_final_result = array();
				foreach($score_a as $a=>$b) {
					$resultstr = $finalstr = '';
					$final_result_arr = $temp_result_arr = array();
					//$a_val = $b;
					//$b_val = isset($score_b[$a]) ? $score_b[$a] : 0;			
					if($is_team_game){
						$a_val=($score_a[$a]+$score_a2[$a]);
						$b_val=($score_b[$a]+$score_b2[$a]);
					}else{
						$a_val = $b;
						$b_val = isset($score_b[$a]) ? $score_b[$a] : 0;
					}	
					if($a > 9){
			          $scorebackto9A[$a]=$a_val;
                      $scorebackto9B[$a]=$b_val;				
			        }
					$last_index = ($a==1) ? 0 : ($a-2);
					$current_index = ($a==1) ? 0 : ($a-1);
					if($a_val>0 && $b_val>0){
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
						$queryString = " insert into event_score_autopress(event_score_calc_id,hole_number,event_id,winner,score_value,color) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner.",'".$scoreval."','".$bgcolor."')"; 
						$queryResult  = $this->db->FetchQuery($queryString);						
					}
				}
		    }
			
			if(count($scorebackto9A)>0 && count($scorebackto9B)>0) {
				$add_new_zero = $remove_last_zero = false;
				$end_final_result = array();
				foreach($scorebackto9A as $a=>$b) {
					$resultstr = $finalstr = '';
					$final_result_arr = $temp_result_arr = array();
					$a_val = $scorebackto9A[$a];
			        $b_val = $scorebackto9B[$a];
			        $current_index = ($a==10) ?  $score_a[9]: ($a-1);
					if($a_val>0 && $b_val>0){
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
							$new_score = 'AS';
							$final_result_arr =array('color'=>$this->getColorCode('black'),'score'=>$new_score);
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
						$queryString = " update event_score_autopress set back_to_9_score='".$scoreval."' where hole_number='".$a."' and event_id='".$eventId."' and winner=".$winner."";
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
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($i=$counter;$i<=$total_hole_num;$i++){
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
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
							$two_point = $one_point = 0;
							$two_point_winner = $one_point_winner = '';
							$a1_val = $b;
							$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
							$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
							$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;							
							if($a1_val>0 && $a2_val>0 && $b1_val>0 && $b2_val>0){							
								$last_index = ($a==1) ? 0 : ($a-2);
								$current_index = ($a==1) ? 0 : ($a-1);								
								$team_a_min = ($a2_val<$a1_val) ? intval($a2_val) : intval($a1_val);
								$team_a_max = ($a2_val>$a1_val) ? intval($a2_val) : intval($a1_val);
								$team_b_min = ($b2_val<$b1_val) ? intval($b2_val) : intval($b1_val);
								$team_b_max = ($b2_val>$b1_val) ? intval($b2_val) : intval($b1_val);								
								// calculate two point
								if($team_a_min < $team_b_min) {
									$two_point_winner = $winner_name = 'TEAM A';
									$bgclass_two = $color_team_a;
									if($pervious_winner_name!='' && $pervious_winner_name != $two_point_winner) {
										$winning_score = intval(abs($pervious_winner_score - 2));
										$two_point_winner = $winner_name = ($pervious_winner_name != 'TEAM A') ? 'TEAM A' : 'TEAM B';
										$bgclass_two = ($pervious_winner_name == 'TEAM A') ? $color_team_a : $color_team_b;
									}
									else {
										$winning_score = intval(abs($pervious_winner_score + 2));
									}
								}
								elseif($team_b_min < $team_a_min) {
									$two_point_winner = $winner_name = 'TEAM B';
									$bgclass_two = $color_team_b;
									if($pervious_winner_name!='' && $pervious_winner_name != $two_point_winner) {
										$winning_score = intval(abs($pervious_winner_score - 2));
										$two_point_winner = $winner_name = ($pervious_winner_name != 'TEAM A') ? 'TEAM A' : 'TEAM B';
										$bgclass_two = ($pervious_winner_name != 'TEAM A') ? $color_team_a : $color_team_b;
									}
									else {
										$winning_score = intval(abs($pervious_winner_score + 2));
									}
								}
								else {
									$two_point_winner = '-';
									$bgclass_two = 'bgblack';
									$winner_name = $pervious_winner_name;
									$winning_score = intval($pervious_winner_score);
								}
								// calculate one point
								if($team_a_max < $team_b_max) {
									$one_point_winner = $winner_name = 'TEAM A';
									$bgclass_one = $color_team_a;
									if($pervious_winner_name!='' && $pervious_winner_name != $one_point_winner) {
										$t_score = intval(($winning_score - 1));
										$winning_score = intval(abs($winning_score - 1));
										$winner_name = ($pervious_winner_name == 'TEAM A' && $t_score > 0) ? 'TEAM A' : 'TEAM B';
										//$bgclass_one = ($pervious_winner_name == 'TEAM A' && $t_score > 0) ? $color_team_a : $color_team_b;
									}
									else { 
										$winning_score = intval(abs($winning_score + 1));
									}
								}
								elseif($team_b_max < $team_a_max) {
									$one_point_winner = $winner_name = 'TEAM B';
									$bgclass_one = $color_team_b;
									if($pervious_winner_name!='' && $pervious_winner_name != $one_point_winner) { 
										$t_score = intval(($winning_score - 1));
										$winning_score = intval(abs($winning_score - 1));
										$winner_name = ($pervious_winner_name == 'TEAM A' && $t_score > 0) ? 'TEAM A' : 'TEAM B';
										//$bgclass_one = ($pervious_winner_name == 'TEAM A' && $t_score > 0) ? $color_team_a : $color_team_b;
									}
									else {
										$winning_score = intval(abs($winning_score + 1));
									}
								}
								else {
									$one_point_winner = '-';
									$bgclass_one = 'bgblack';
									$winner_name = ($current_index>0) ? $pervious_winner_name : $winner_name;
									$winning_score = intval($winning_score);
								}
								if($two_point_winner == $one_point_winner) {
									//$winner_name = 'AS';
									//$winning_score = $pervious_winner_score;
								}
								$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
								if($winner_name == 'AS'){
									$winner_team_id=0;
								}else{
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
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
								$queryString = " insert into event_score_2_1(event_score_calc_id,hole_number,event_id,2_point,1_point,winner,score_value) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$two_point_team_winner.",".$one_point_team_winner.",".$winner_team_id.",".$winning_score.")";
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
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					
							$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
							for($i=$counter;$i<=$total_hole_num;$i++){
								$player[$e['player_id']][$i] =$sqlresult2[0]['net_'.$i];
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
						$pervious_winner_score = 0;
						$pervious_winner_name = '';
						foreach($score_a as $a=>$b) {
							$winner_name = $bgclass = '';
							$team_a_sum = $team_b_sum = $winning_score = 0;
							$a1_val = $b;
							$a2_val = isset($score_a2[$a]) ? $score_a2[$a] : 0;
							$b1_val = isset($score_b[$a]) ? $score_b[$a] : 0;
							$b2_val = isset($score_b2[$a]) ? $score_b2[$a] : 0;
							if($a1_val>0 && $a2_val>0 && $b1_val>0 && $b2_val>0){								
								$last_index = ($a==1) ? 0 : ($a-2);
								$current_index = ($a==1) ? 0 : ($a-1);
								$team_a_sum = ($a2_val<$a1_val) ? intval($a2_val.$a1_val) : intval($a1_val.$a2_val);
								$team_b_sum = ($b2_val<$b1_val) ? intval($b2_val.$b1_val) : intval($b1_val.$b2_val);
								if($team_a_sum < $team_b_sum) {
									// winner :: TEAM A
									$winner_name = 'TEAM A';
									$bgclass = $color_team_a;
									$winning_score = abs($team_a_sum - $team_b_sum);
									if($current_index>0) {
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
									$winner_name = 'TEAM B';
									$bgclass = $color_team_b;
									$winning_score = abs($team_a_sum - $team_b_sum);					
									if($current_index>0) {
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
									$winner_name = 'AS';
									$bgclass = $color_team_both;
									$winning_score = $pervious_winner_score;
									$final_result[$a] = array('winner'=>$winner_name,'score'=>intval($winning_score));
								}
								if($winner_name == 'AS'){
									$winner_team_id=0;
								}else{
								$winner_team_id=($winner_name=="TEAM A")?$uniqueteam[0]:$uniqueteam[1];
								}
								$queryString = " insert into event_score_vegas(event_score_calc_id,hole_number,event_id,winner,score_value) values(".$event_score_calc_Arr[0].",".$a.",".$eventId.",".$winner_team_id.",".$winning_score.")";
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
							$sqlQuery1="SELECT event_score_calc_id,net_1,net_2,net_3,net_4,net_5,net_6,net_7,net_8,net_9,net_10,net_11,net_12,net_13,net_14,net_15,net_16,net_17,net_18 from event_score_calc where event_id='".$eventId."' and player_id ='".$e['player_id']."'";
					$sqlresult2  = $this->db->FetchQuery($sqlQuery1);
					for($i=$counter;$i<=$total_hole_num;$i++){
						$player[$e['player_id']][$i] =$sqlresult2[0]['score_entry_'.$i];
					}
					$player_id[]=$e['player_id'];
					$event_score_calc_Arr[]=$sqlresult2[0]['event_score_calc_id'];
					//$player[$e['player_id']] = 
				}
			}
			
			$score_a = isset($player_id[0]) ? $player[$player_id[0]] : array();
			
	$score_b = isset($player_id[1]) ? $player[$player_id[1]] : array();
	$score_c =isset($player_id[2]) ? $player[$player_id[2]] : array();
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
			if($a_val>0 && $b_val>0 && $c_val>0){
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
		$queryResult  = $this->db->FetchQuery($queryString);
		$queryString = " update event_score_calc set total_score='".$sum_b."' where event_id=".$eventId." and player_id=".$player_id[1]."";
		$queryResult  = $this->db->FetchQuery($queryString);
		$queryString = " update event_score_calc set total_score='".$sum_c."' where event_id=".$eventId." and player_id=".$player_id[2]."";
		$queryResult  = $this->db->FetchQuery($queryString);
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

	function updateGrossScore($eventId,$strokeId,$holeId,$score,$playerId,$totalPar,$golf_course_id)
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
				$queryString .= " score_entry_".$holeId." = ".$score.",";
				$queryString .= " total_score = (total_score + ".$diffValue. "),"; 
				$queryString .= "gross_score = (gross_score + ".$diffValue.")"; 
				$queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
			}
			else
			{
			   $queryString = "update event_score_calc set ";
				$queryString .= " score_entry_".$holeId." = ".$score.",";
				$queryString .= " par_total = par_total + ".$totalPar.",";
				$queryString .= " total_score = (total_score + ".$score. "),"; 
				$queryString .= "gross_score = ((gross_score + ".$score.") - ".$totalPar.")"; 
				$queryString .= " where event_id = ".$eventId;
				$queryString .= " and player_id = ".$playerId;
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
			$queryString .= " and handicap_value >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id." and event_id = ".$eventId.")";
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
			$queryString .= " and calculated_handicap >= (select hole_index_".$holeId." from golf_hole_index where golf_course_id =".$golf_course_id." and event_id = ".$eventId.")";
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
		{
			if($format_id=="3" || $format_id=="6"){
			$fieldValue="handicap_value";	
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
			$handicap_value = $this->db->FetchSingleValue($queryString);
			
			$maxCount = $handicap_value - 18;
			if($maxCount > 0)
			{
				if($hole_index <= $maxCount)
				{
					$queryString = "update event_score_calc set  ";
					$queryString .= " net_".$hole_no." = (net_".$hole_no." - 1),";
					$queryString .= " net_score  = (net_score  - 1) "; 
					$queryString .= " where event_id = ".$eventId;
					$queryString .= " and player_id = ".$playerId;
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
			$myindex = 1;
			foreach($all_scores_arr[0] as $a=>$b) {
				$hole_score_value = $b;
				$is_gir = isset($all_girs_arr[0]["gir_{$myindex}"]) ? $all_girs_arr[0]["gir_{$myindex}"] : 0;
				$putt_score = isset($all_girs_arr[0]["no_of_putt_{$myindex}"]) ? $all_girs_arr[0]["no_of_putt_{$myindex}"] : 0;
				if($hole_score_value>0) {
					if($is_gir==1) { // is_gir
						$yes_gir_total++;
						$is_gir_arr[] = $putt_score;
					}
					elseif($is_gir==2) { // not gir
						$no_gir_total++;
					}else{
}
					
					$allputs[] = $putt_score;
					
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
			$putting = isset($score_c[$a]) ? $score_c[$a] : 0;
			$allputs[] = $putting;
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
					$yes_gir_total++;
				}
				elseif($is_gir==2) {
					$no_gir_total++;
				}else{
}
					
				
			}	
		}
		$par = (count($is_gir_arr)>0) ? round((array_sum($is_gir_arr)/count($is_gir_arr)),2) : 0.00;
		$perGir = ($par>0) ? $par : 0;
		$all_putting = $allputs;
		//$all_putting_count = count($end_final_result);
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
			$spot_value = $closest_feet.','.$closest_inches; 
			$queryString = "select closest_feet_id from event_score_calc_closest_feet where event_id = ".$eventId;
			$queryString .= " and player_id = ".$playerId;
			$lastScore = $this->db->FetchSingleValue($queryString);
			if(isset($lastScore))
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
			
			$this->updateClosestToPinInches($eventId,$closest_inches,$closest_feet,$holeId,$playerId);
		}

function updateClosestToPinInches($eventId,$closest_inches,$closest_feet,$holeId,$playerId)
		{
			$queryString = "select closest_inch_id from event_score_calc_closest_inch where event_id = ".$eventId." and player_id = ".$playerId; 
			$lastScore = $this->db->FetchSingleValue($queryString);
			$total = ($closest_feet*12)+$closest_inches ;
			if(isset($lastScore))
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
function getLeaderBoard($data){

			 $playerScoreListArray =$fdata= array();
			$eventId=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"0";
			$format_id=(isset($data['format_id']) && $data['format_id'] > 0)?$data['format_id']:"0";
			$is_spot_type=(isset($data['is_spot_type']) && $data['is_spot_type'] > 0)?$data['is_spot_type']:"0";
		$is_spot_hole_number=(isset($data['is_spot_hole_number']) && $data['is_spot_hole_number'] > 0)?$data['is_spot_hole_number']:"0";
            $type=(isset($data['type']) && $data['type'] > 0)?$data['type']:"0";
            if($eventId!=""){
           			    $queryString = "select total_hole_num, format_id, is_started, DATE(event_start_date_time) as event_start_date,event_start_time,golf_course_name,event_name,is_spot from event_list_view where event_id ='".$eventId."'";
						$result = $this->db->FetchRow($queryString);
					
						
						if(count($result) > 0){
							$total_hole_num = $result['total_hole_num'];
						$stroke_play_id = ($format_id >0)?$format_id:$result['format_id'];
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
				$currentScoreListArray['is_spot_type'] = (count($is_spot_data > 0))?implode(',',array_unique($is_spot_data)):"0";
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
								if($stroke_play_id=="4" || $stroke_play_id=="7"){
								
									if($is_spot_hole_number >0){
										
										$queryString = "SELECT f.closest_feet_".$is_spot_hole_number." as feet,f.player_id,t.handicap_value_3_4 as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id order by i.closest_inch_".$is_spot_hole_number." ".$spot_order."";
}else{
										$queryString = " select t.player_id,t.handicap_value_3_4 as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played, "; 
								    $queryString .= "t.".$total_field_name." as total";
								    $queryString .= " from event_score_calc
 t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.current_position asc,t.no_of_holes_played desc";
									}
								}elseif($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){									
							       if($is_spot_hole_number >0){

									$queryString = "SELECT f.closest_feet_".$is_spot_hole_number." as feet,f.player_id,t.calculated_handicap as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id order by i.closest_inch_".$is_spot_hole_number." ".$spot_order."";

									}else{
									$queryString = "select t.player_id,t.calculated_handicap as handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played, "; 
									$queryString .= "t.".$total_field_name." as total";
									$queryString .= " from event_score_calc
									t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.current_position asc,t.no_of_holes_played desc";
									}
							}else{
									if($is_spot_hole_number >0){

									$queryString = "SELECT f.closest_feet_".$is_spot_hole_number." as feet,f.player_id,t.handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.".$total_field_name." as total FROM `event_score_calc` t
left JOIN  `event_score_calc_closest_feet` f ON f.event_id = t.event_id and f.player_id=t.player_id
LEFT JOIN event_score_calc_closest_inch i ON i.event_id = t.event_id and i.player_id=t.player_id
LEFT JOIN golf_users g ON g.user_id = t.player_id where t.event_id = ".$eventId." group by t.player_id order by i.closest_inch_".$is_spot_hole_number." ".$spot_order."";

									}else{
									$queryString = "select t.player_id,t.handicap_value,g.full_name,CONCAT(t.lb_display_string,t.current_position) as current_position,t.no_of_holes_played as no_of_hole_played, "; 
									$queryString .= "t.".$total_field_name." as total";
									$queryString .= " from event_score_calc
									t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' order by t.current_position asc,t.no_of_holes_played desc";
									}
								}
								$recordSetPlayerScore = $this->db->FetchQuery($queryString);
								foreach($recordSetPlayerScore as $i=>$rowValues)
									{
										$spoArr = explode(',',$rowValues['feet']);
										$rowValues['feet'] = $spoArr[0];
										$rowValues['inches'] = (isset($spoArr[1]))?$spoArr[1]:'';
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

        if(isset($scoredata['data']) && count($scoredata['data'])>0){
           foreach($scoredata['data'] as $key=>$value){
           $counter++;
           if(count($scoredata['data'])==$counter){
               $current_standing=$scoredata['data'][$key];
           }
        }
        $score_data=$scoredata['data'];

        $counter=($counter+1);
        }
        for($i=$counter;$i<=$total_num_hole;$i++){
        $score_data[]=array('hole_number'=>$i,'score_value'=>'','winner'=>'0','color'=>'');
        }
        if($stroke_play_id==12){
           // echo $counter;
            if(count($current_standing) > 0){
            foreach($current_standing as $key=>$val){
                $current_standing1=$val;
            }
            $current_standing=array();
            $current_standing['last']=$current_standing1;
            }
        }
        return array('standing'=>$score_data,'current'=>$current_standing);  
    }
    
    function getLatestFullScore($data){          
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
                                                                $sqlQuery1="SELECT p.team_id,t.team_display_name,p.player_id,u.full_name,profile.self_handicap FROM event_table e left join event_player_list p ON p.event_id=e.event_id left join team_profile t on t.team_profile_id=p.team_id LEFT JOIN golf_users u ON u.user_id = p.player_id LEFT JOIN user_profile profile ON profile.user_id =u.user_id WHERE e.event_id='".$eventId."' and p.is_accepted='1' order by t.team_profile_id asc";  
                                                                $sqlresult1  = $this->db->FetchQuery($sqlQuery1);
                                                                $team_id=array();$player_idArr=array();
                                                                if(count($sqlresult1) >0){
                                                                            foreach($sqlresult1 as $i=>$e){
                                                                                    $player_idArr[]=$e['player_id'];
                                                                                    if(!in_array($e['team_id'],$team_id)){
                                                                                    $team_id[]=$e['team_id'];
                                                                                    }
                                                                            }
                                                                }						
                                                                $uniqueteam=array_unique($team_id);
                                                                $game_type = (count($player_idArr)=="4")?'team':'';				
                                                                $is_team_game = ($game_type == 'team') ? true : false;
                                                                $currentScoreListArray['is_team'] =($is_team_game)?"1":"0";
                                                                    if($is_team_game){
                                                                       $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name ";
								       $queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' order by p.team_id asc ";
                                                                       //echo $queryString;
                                                                       $teamrec = $this->db->FetchQuery($queryString);	
                                                                       foreach($teamrec as $t=>$row){
                                                                            if($uniqueteam[0]==$row['team_id']){
                                                                                $teamplayerarray1[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                                                                            }else{
                                                                                $teamplayerarray2[]=array('player_id'=>$row['player_id'],'name'=>$row['full_name'],'handicap_value'=>$row['handicap_value']);
                                                                            } 
                                                                       }
                                                                       $teamarray[]=array('team_name'=>'Team A','player_list'=>$teamplayerarray1);
                                                                       $teamarray[]=array('team_name'=>'Team B','player_list'=>$teamplayerarray2);
                                                                       $currentScoreListArray['team_data'] =$teamarray;
                                                                    }
                                                                    $standingdata=$this->getStandingForNewGameFormat($total_num_hole,$stroke_play_id,$eventId,$event_admin_id);//array();

                                                                    $currentScoreListArray['current_standing']=$standingdata['current'];
                                                                    if($stroke_play_id!=12){
                                                                    $currentScoreListArray['standings']=$standingdata['standing'];
                                                                    }
                                                                }
								$player_hole_score=array();								
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
                                                                                    $qryString .= "score_entry_".$ctrV.",min(t.".$fieldname.$ctrV.") as hole_num_".$ctrV;
                                                                                }else{
                                                                                    $qryString .= "score_entry_".$ctrV.",t.".$fieldname.$ctrV." as hole_num_".$ctrV;
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
								for($ctr = $hole_start_from; $ctr <= $total_num_hole;  $ctr++)
								{
									$parno=$ctr;
									$currentScoreListArray['par_value_'.$parno] = $rowparValues['par_value_'.$parno];
									//$currentScoreListArray['hole_color_'.$parno] = $this->getParColorCodeValue($rowparValues['par_value_'.$parno]);
									$currentScoreListArray['hole_index_'.$parno] = $rowparValues['hole_index_'.$parno];
								}
								   $pl = (isset($player_id) && $player_id > 0)?' AND t.player_id ='.$player_id.'':'';
									if($stroke_play_id=="4" || $stroke_play_id=="7"){
									$queryString = " select t.player_id,t.handicap_value_3_4 as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);
									}else if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){
                                                                        if($is_team_game){
                                                                        $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." group by p.team_id order by p.team_id asc ";
                                                                        //echo "<br>".$queryString;
									}else{
                                                                        $queryString = " select p.team_id,t.player_id,t.calculated_handicap as handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from event_score_calc t left join golf_users g ON g.user_id=t.player_id left Join event_player_list p ON p.player_id = t.player_id and p.event_id =t.event_id where t.event_id ='".$eventId."' ".$pl." order by t.event_score_calc_id asc";
									}
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);										
									}else{
									$queryString = " select t.player_id,t.handicap_value,t.handicap_value_3_4,g.full_name, ";
									$queryString .= $qryString; 
									$queryString .= ", t.".$total_field_name." as total";
									$queryString .= " from  event_score_calc
t left join golf_users g ON g.user_id=t.player_id where t.event_id ='".$eventId."' ".$pl." order by t.player_id asc";
									$recordSetPlayerScore = $this->db->FetchQuery($queryString);									
									}
									
                                    $event = new Events;
									$counter =0;$no_of_eagle=0;$no_of_birdies=0;$no_of_pars=0;$no_of_bogeys=0;$no_of_double_bogeys=0;
									$total_front_9_postion=0;$total_back_9_postion=0;$total_postion=0;$player_counter=0;
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
                                                if($rowValues['score_entry_'.$handi_counter] > 0){												
												if($position==0){
												$rowValues['position_'.$handi_counter]='Even';	
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
											if($rowValues['score_entry_'.$handi_counter] > 0){
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
											$rowValues['hole_color_'.$handi_counter]=$color;				
											unset($rowValues['score_entry_'.$handi_counter]);
										}
                                        $rowValues['short_name'] = $last;			
                                        if($is_team_game){
                                            $rowValues['short_name']=($uniqueteam[0]==$rowValues['team_id'])?"Team A":"Team B";
                                            $rowValues['full_name']=($uniqueteam[0]==$rowValues['team_id'])?"Team A":"Team B";
                                            $rowValues['player_color_code']=$this->setColorForPlayer(1,$rowValues['full_name'],0);
                                            unset($rowValues['player_id']);unset($rowValues['handicap_value']);unset($rowValues['handicap_value_3_4']);
                                        }else{
                                            $rowValues['player_color_code']=$this->setColorForPlayer(0,'',$player_counter);							
                                        }
                                        if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="12" || $stroke_play_id=="13" || $stroke_play_id=="14"){

                                        }else{
                                          unset($rowValues['player_color_code']);  
                                        }
										$player_hole_score[] = $rowValues ;
									}
								}								
								//$no_of_eagle=0;$no_of_birdies=0;$no_of_pars=0;$no_of_bogeys=0;
								
								$currentScoreListArray['total_front_9_postion']= $total_front_9_postion; 
							   $currentScoreListArray['total_back_9_postion']= $total_back_9_postion; 
							   $currentScoreListArray['total_postion']= $total_postion; 				
							   $currentScoreListArray['eagle_counter']= $no_of_eagle; 
							   $currentScoreListArray['birdie_counter']= $no_of_birdies; 
							   $currentScoreListArray['par_counter']= $no_of_pars; 
							   $currentScoreListArray['bogey_counter']= $no_of_bogeys; 
							   $currentScoreListArray['doublebogey_counter']= $no_of_double_bogeys; 
							   $currentScoreListArray['player_hole_score']= $player_hole_score; 
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
				$sqlu="update event_player_list set is_submit_score='1' where event_id ='".$eventId."' and scorere_id =".$user_id." ";
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
				
			}
				
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
			$ccode='#0c9f32';
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
	
}
?>