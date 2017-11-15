<?php
class Stats{
	public $db,$data = array();
	function __construct(){
		global $database;
		$this->db = $database;
	}
	function  getStatsPiChart($data){
		$fdata =array();
		$player_id =   (isset($data['user_id']) && $data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;
		
		if($no_of_event<=0) {
			$no_of_event = 1500; // for overall events
		}
		
		$rowValues  =array();
  if($player_id  >0){
$event_list = '0';
		 $limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
		$quet1 = "select s.event_id,p.submit_score_date as stime from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$player_id." and p.is_submit_score='1' order by stime DESC".$limit.""; 
		$event_list  = $this->db->FetchQuery($quet1);
//print_r($event_list);die;
if(isset($event_list) && is_array($event_list) && count($event_list)>0){
		foreach($event_list as $e=>$p){

				$events[] = $p['event_id'];
			}
			$event_list = (isset($events) && count($events)>0)?implode(',',$events):0; 
			//echo $event_list;die;
		 if($no_of_event > 1){
			$eagle = "avg(round(s.no_of_eagle,2)) as no_of_eagle";
			$birdie = "avg(round(s.no_of_birdies,2)) as no_of_birdies";
			$pars =  "avg(round(s.no_of_pars,2)) as no_of_pars";
			$bogey = "avg(round(s.no_of_bogeys,2)) as no_of_bogeys";
			$double_bogeys = "avg(round(s.no_of_double_bogeys,2)) as no_of_double_bogeys";
			$gscore = "avg(round((s.total_score),2)) as gross_score";

		 }else{
			 	$eagle = "round(s.no_of_eagle,2) as no_of_eagle";
			$birdie = "round(s.no_of_birdies,2) as no_of_birdies";
			$pars =  "round(s.no_of_pars,2) as no_of_pars";
			$bogey = "round(s.no_of_bogeys,2) as no_of_bogeys";
			$double_bogeys = "round(s.no_of_double_bogeys,2) as no_of_double_bogeys";
			$gscore = "round((s.total_score),2) as gross_score";
		 } 
			 

			$query1  = "SELECT ".$eagle.",
			last_modified_date  as stime FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC"; 
			$avg_eagle  = $this->db->FetchSingleValue($query1);

			$query2  = "SELECT ".$birdie.",last_modified_date  as stime FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC ";
			$avg_birdies  = $this->db->FetchSingleValue($query2);

			$query3  = "SELECT ".$pars.",last_modified_date  as stime FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC ";
			$avg_pars  = $this->db->FetchSingleValue($query3);

			$query4  = "SELECT ".$bogey.",last_modified_date  as stime FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC";
			$avg_bogeys  = $this->db->FetchSingleValue($query4);

			$query5  = "SELECT ".$double_bogeys.",last_modified_date  as stime FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC";
			$avg_double_bogeys  = $this->db->FetchSingleValue($query5);
			
			$query6  = "SELECT s.total_score as gross_score,e.last_modified_date as stime,s.no_of_holes_played,e.total_hole_num,s.no_of_holes_played FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") and e.total_hole_num=18 and s.no_of_holes_played=18 order by stime DESC"; 
			 $avg_gross_score  = $this->db->FetchQuery($query6);
			
$query7  = "SELECT s.par_total,s.total_score,e.last_modified_date as stime,s.no_of_holes_played,e.total_hole_num,e.total_hole_num,s.no_of_holes_played FROM event_score_calc s inner join event_table e on s.event_id = e.event_id where s.player_id= ".$player_id." and s.event_id in(".$event_list.") order by stime DESC"; 
			$avg_par_score  = $this->db->FetchQuery($query7);
			
			$total_scores=$total_par=$total_gross_scores=$total_scores_all=$total_scores_all_gs=0;
			//print_r($avg_eagle);echo '<br/>';print_r($avg_birdies);echo '<br/>';print_r($avg_pars);echo '<br/>';print_r($avg_bogeys);echo '<br/>';print_r($avg_double_bogeys);die;
			$played_games = 0;
			foreach($avg_par_score as $i=>$v) {
				
				if($no_of_event == 1) {
					if($v['total_hole_num'] == 18 && $v['no_of_holes_played'] == 18) {
						$total_scores_all_gs=$v['total_score'];
					}
					$played_games = 1;
					$total_scores = ($v['total_score']-$v['par_total']);
					break;
				}
				else {
					if($v['total_hole_num'] == 18 && $v['no_of_holes_played'] == 18) {
						$played_games++;
						$total_scores+=($v['total_score']-$v['par_total']);
						$total_scores_all_gs+=$v['total_score'];
					}
				}
			} 
			
			if($total_scores < 0){
				$sign = '-';
			}
			elseif($total_scores > 0){
				$sign = '+';
			}
			else{
				$sign = '';
			}
			//echo $sign.'%%%%%';die;
			
			$no_of_event = $played_games;
			
			//echo 'no_of_event : '.$no_of_event.'_____ events_no : '.$events_no;die;
			$position_avg = $no_of_event>0 ? round(abs($total_scores)/$no_of_event) :0;
			$score_avg = $no_of_event>0 ? ($total_scores_all_gs/$no_of_event) :0;

			$total_avg = ($avg_eagle+$avg_birdies+$avg_pars+$avg_bogeys+$avg_double_bogeys);
			//echo '<br>'.($avg_eagle.'___'.$avg_birdies.'___'.$avg_pars.'___'.$avg_bogeys.'___'.$avg_double_bogeys);die;
			$rowValues['no_of_eagle'] = ($total_avg>0) ? round(($avg_eagle/$total_avg)*100) : '-';
			$rowValues['no_of_birdies'] = ($total_avg>0) ? round(($avg_birdies/$total_avg)*100) : '-';
			$rowValues['no_of_pars'] = ($total_avg>0) ? round(($avg_pars/$total_avg)*100) : '-';
			$rowValues['no_of_bogeys'] = ($total_avg>0) ? round(($avg_bogeys/$total_avg)*100) : '-';
			$rowValues['no_of_double_bogeys'] = ($total_avg>0) ? round(($avg_double_bogeys/$total_avg)*100) :'-';
			$rowValues['gross_score'] = ($score_avg > 0)?round($score_avg,0):'-';
			$rowValues['curent_position'] = ($position_avg >0)?$sign.$position_avg:'-';
}else{
			$rowValues['no_of_eagle'] = '-';
			$rowValues['no_of_birdies'] = '-';
			$rowValues['no_of_pars'] = '-';
			$rowValues['no_of_bogeys'] = '-';
			$rowValues['no_of_double_bogeys'] = '-';
			$rowValues['gross_score'] = '-';
			$rowValues['curent_position'] = '-';
	
}	
	}
		return $rowValues ;
	}
	function getScoreStats($data){
		$fdata = array();
		$admin_id = (isset($data['user_id']) &&$data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		$stats['avg_gross_score'] = '-';
$stats['avg_par3s'] = '-';
$stats['avg_par4s'] = '-';
$stats['avg_par5s'] = '-';
$stats['avg_out'] = '-';
$stats['avg_in'] = '-';
$stats["gscore_change"] = '-';
$stats["gscore_change_color"] = '#0b5a97'; // default blue color
$stats["par3_change"] = '-';
$stats["par3_change_color"] = '#0b5a97'; // default blue color
$stats["par4_change"] = '-';
$stats["par4_change_color"] = '#0b5a97'; // default blue color
$stats["par5_change"] = '-';
$stats["par5_change_color"] = '#0b5a97'; // default blue color
$stats["in_change"] = '-';
$stats["in_change_color"] = '#0b5a97'; // default blue color
$stats["out_change"] = '-';
$stats["out_change_color"] = '#0b5a97'; // default blue color
$stats['last_gross_score'] = '-';
$stats['last_par3s'] = '-';
$stats['last_par4s'] = '-';
$stats['last_par5s'] = '-';
$stats['last_out'] = '-';
$stats['last_in'] = '-';
		if($admin_id > 0){
			$limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
			$quet1 = "select s.event_id,e.no_of_player,e.is_singlescreen,e.total_hole_num,s.no_of_holes_played,p.submit_score_date as stime,p.scorere_id from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$admin_id ." and p.is_submit_score='1'  order by stime DESC ".$limit."";  
			$event_list  = $this->db->FetchQuery($quet1);

		if(is_array($event_list) && count($event_list)>0){
			
			foreach($event_list as $e=>$p){
				$events[] = $p['event_id'];
				    
            }
			
			$event_list = (isset($events) && count($events)>0)?implode(',',$events):0; 
		//echo '_______'.$event_list; die;
		if(isset($event_list) && $event_list > 0){
			$query = 'SELECT e.total_hole_num,c.no_of_holes_played,concat(date(e.event_start_date_time)," ", e.event_start_time) as start_time,pl.submit_score_date as stime ,p.event_id,c.total_score as gross_score,p.per_3_average as par_3s,p.per_4_average as par_4s,p.per_5_average as par_5s,p.gir_yes as gir_in,p.gir_no as gir_out,c.score_entry_1,c.score_entry_2,c.score_entry_3,c.score_entry_4,c.score_entry_5,c.score_entry_6,c.score_entry_7,c.score_entry_8,c.score_entry_9,c.score_entry_10,c.score_entry_11,c.score_entry_12,c.score_entry_13,c.score_entry_14,c.score_entry_15,c.score_entry_16,c.score_entry_17,c.score_entry_18,c.par_1,c.par_2,c.par_3,c.par_4,c.par_5,c.par_6,c.par_7,c.par_8,c.par_9,c.par_10,c.par_11,c.par_12,c.par_13,c.par_14,c.par_15,c.par_16,c.par_17,c.par_18 FROM event_score_calc c inner join event_player_list pl ON pl.player_id = c.player_id and pl.event_id=c.event_id  LEFT JOIN event_score_calc_no_of_putt p ON p.event_id = c.event_id AND p.player_id = '.$admin_id.' left join event_table e ON e.event_id=c.event_id WHERE c.player_id = '.$admin_id.' and c.event_id in('.$event_list.') ORDER BY stime DESC';
			$allEvnt = $this->db->FetchQuery($query);
			//print_r($allEvnt);die;
			if(count($allEvnt)>0){
$total_gross=$par3=$par4=$par5=$girout=$girin=$latesttotal_gross=$latestpar3=$latestpar4=$latestpar5=$latestgirout=$latestgirin=$latest_total_hol_played_par3=$latest_total_hol_played_par4=$latest_total_hol_played_par5=0;
//print_r($allEvnt);die;
if($allEvnt[0]['total_hole_num'] == 18 && $allEvnt[0]['no_of_holes_played'] == 18) {
	//$latesttotal_gross+=($allEvnt[0]['gross_score']);
}
			$latest_event_index = 0;
			foreach($allEvnt as $a=>$b) {
			    if($b['total_hole_num'] == 18 && $b['no_of_holes_played'] == 18) {
                	$latesttotal_gross+=($b['gross_score']);
                	$latest_event_index = $a;
                	break;
                }
			}
				
				

for($i=1;$i<=18;$i++){
       if($i <= 9){
          $latestgirin+= $allEvnt[$latest_event_index]['score_entry_'.$i];
       }else{
          $latestgirout+= $allEvnt[$latest_event_index]['score_entry_'.$i];
       }
if($allEvnt[$latest_event_index]['par_'.$i] == 3){
	 $latestpar3+= $allEvnt[$latest_event_index]['score_entry_'.$i];
	$latest_total_hol_played_par3+= 1;
	}elseif($allEvnt[$latest_event_index]['par_'.$i] == 4){
	 $latestpar4+= $allEvnt[$latest_event_index]['score_entry_'.$i];
	$latest_total_hol_played_par4+= 1;
	}elseif($allEvnt[$latest_event_index]['par_'.$i] == 5){
	 $latestpar5+= $allEvnt[$latest_event_index]['score_entry_'.$i];
	$latest_total_hol_played_par5+= 1;
	}

    } 

//echo $latest_total_hol_played_par5.'__'.$latest_total_hol_played_par4.'__'.$latest_total_hol_played_par3; die;
//echo $latestpar3.'__'.$latestpar4.'__'.$latestpar5; die;
$no_events= count($allEvnt);

$front=$back=$total_hol_played_par3=$total_hol_played_par4=$total_hol_played_par5=0;
//$no_of_event = ($no_events > $no_of_event)?$no_of_event:$no_events;
if($no_of_event == 0){
$no_of_event =$no_events;
}elseif($no_events> $no_of_event){
$no_of_event =$no_of_event;
}else{
$no_of_event =$no_events;
}
$no_of_event_for_gross=0;

$stats['avg_gross_score'] = '-';
$stats['avg_par3s'] = '-';
$stats['avg_par4s'] = '-';
$stats['avg_par5s'] = '-';
$stats['avg_out'] = '-';
$stats['avg_in'] = '-';
$stats["gscore_change"] = '-';
$stats["gscore_change_color"] = '#0b5a97'; // default blue color
$stats["par3_change"] = '-';
$stats["par3_change_color"] = '#0b5a97'; // default blue color
$stats["par4_change"] = '-';
$stats["par4_change_color"] = '#0b5a97'; // default blue color
$stats["par5_change"] = '-';
$stats["par5_change_color"] = '#0b5a97'; // default blue color
$stats["in_change"] = '-';
$stats["in_change_color"] = '#0b5a97'; // default blue color
$stats["out_change"] = '-';
$stats["out_change_color"] = '#0b5a97'; // default blue color
$stats['last_gross_score'] = '-';
$stats['last_par3s'] = '-';
$stats['last_par4s'] = '-';
$stats['last_par5s'] = '-';
$stats['last_out'] = '-';
$stats['last_in'] = '-';
//echo $no_of_event ; die;
foreach($allEvnt as $i=>$s){
    
					if($s['gross_score'] >0){
for($i=1;$i<=18;$i++){
       if($i <= 9){
          $front+= $s['score_entry_'.$i];
       }else{
          $back+= $s['score_entry_'.$i];
       }
if($s['par_'.$i] == 3){
									 $par3+= $s['score_entry_'.$i];
									$total_hol_played_par3+= 1;
								}elseif($s['par_'.$i] == 4){
									 $par4+= $s['score_entry_'.$i];
									$total_hol_played_par4+= 1;
								}elseif($s['par_'.$i] == 5){
									 $par5+= $s['score_entry_'.$i];
									$total_hol_played_par5+= 1;
								}

    } 
	if($s['total_hole_num'] == 18 && $s['no_of_holes_played'] == 18) { $no_of_event_for_gross++;
						$total_gross+=($s['gross_score']);
	}
	//echo 'par 3  : '.$par3.' __ holes 3 : '.$total_hol_played_par3.'<br/>';
	//echo 'par 4  : '.$par4.' __ holes 4 : '.$total_hol_played_par4.'<br/>';
//	echo 'par 5  : '.$par5.' __ holes 5 : '.$total_hol_played_par5.'<br/>';
						
						$stats['avg_gross_score'] = ($total_gross >0 && $no_of_event>0)?$this->roundvalue($total_gross/$no_of_event_for_gross,2):'-';
						$stats['avg_par3s'] = ($par3 >0 && $no_of_event>0)?$this->roundvalue($par3/$total_hol_played_par3,2):'-';
						$stats['avg_par4s'] = ($par4 >0 && $no_of_event>0)?$this->roundvalue($par4/$total_hol_played_par4,2):'-';
						$stats['avg_par5s'] = ($par5 >0 && $no_of_event>0)?$this->roundvalue($par5/$total_hol_played_par5,2):'-';
						
$stats['avg_out'] = ($back >0 && $no_of_event>0)?$this->roundvalue($back/$no_of_event,2):'-';
$stats['avg_in'] = ($front >0 && $no_of_event>0)?$this->roundvalue($front/$no_of_event,2):'-';

					}	
				} //die;

				$stats['last_gross_score'] = ($latesttotal_gross >0)?$this->roundvalue($latesttotal_gross/1,2):'-';
		               $stats['last_par3s'] = ($latestpar3 >0)?$this->roundvalue($latestpar3/$latest_total_hol_played_par3,2):'-';
				$stats['last_par4s'] = ($latestpar4 >0)?$this->roundvalue($latestpar4/$latest_total_hol_played_par4,2):'-';
				$stats['last_par5s'] = ($latestpar5 >0)?$this->roundvalue($latestpar5/$latest_total_hol_played_par5,2):'-';
				$stats['last_out'] = ($latestgirout >0)?$this->roundvalue($latestgirout/1,2):'-';
				$stats['last_in'] = ($latestgirin >0)?$this->roundvalue($latestgirin/1,2):'-';

$gscore_change = ($stats['avg_gross_score']>0) ? ((abs($stats['last_gross_score']-$stats['avg_gross_score'] )/$stats['avg_gross_score'])*100) :0;
$gscore_change_color = ($stats['last_gross_score']>$stats['avg_gross_score'] ) ? 1 : (($stats['last_gross_score']==$stats['avg_gross_score'] ) ? 2  : 0) ;

$par3_change = ($stats['avg_par3s']>0) ? ((abs($stats['last_par3s']-$stats['avg_par3s'] )/$stats['avg_par3s'])*100) :0;
$par3_change_color = ($stats['last_par3s']>$stats['avg_par3s'] ) ? 1 : (($stats['last_par3s']==$stats['avg_par3s'] ) ? 2  : 0) ;

$par4_change = ($stats['avg_par4s']>0) ? ((abs($stats['last_par4s']-$stats['avg_par4s'] )/$stats['avg_par4s'])*100) :0;
$par4_change_color = ($stats['last_par4s']>$stats['avg_par4s'] ) ? 1 : (($stats['last_par4s']==$stats['avg_par4s'] ) ? 2  : 0) ;

$par5_change = ($stats['avg_par5s']>0) ? ((abs($stats['last_par5s']-$stats['avg_par5s'] )/$stats['avg_par5s'])*100) :0;
$par5_change_color = ($stats['last_par5s']>$stats['avg_par5s'] ) ? 1 : (($stats['last_par5s']==$stats['avg_par5s'] ) ? 2  : 0) ;

$in_change = ($stats['avg_in']>0) ? ((abs($stats['last_in']-$stats['avg_in'] )/$stats['avg_in'])*100) :0;
$in_change_color = ($stats['last_in']>$stats['avg_in'] ) ? 1 : (($stats['last_in']==$stats['avg_in'] ) ? 2  : 0) ;

$out_change = ($stats['avg_out']>0) ? ((abs($stats['last_out']-$stats['avg_out'] )/$stats['avg_out'])*100) :0;
$out_change_color = ($stats['last_out']>$stats['avg_out'] ) ? 1 : (($stats['last_out']==$stats['avg_out'] ) ? 2  : 0) ;

				$stats['gscore_change'] =$this->roundvalue($gscore_change,2).'%';
				$stats['gscore_change_color'] =($gscore_change_color== 1)?'#ff0000':(($gscore_change_color== 2) ? "#000000" : '#325604');

                                $stats['par3_change'] =$this->roundvalue($par3_change,2).'%';
				$stats['par3_change_color'] =($par3_change_color== 1)?'#ff0000':(($par3_change_color== 2) ? "#000000" : '#325604');

                                $stats['par4_change'] =$this->roundvalue($par4_change,2).'%';
				$stats['par4_change_color'] =($par4_change_color== 1)?'#ff0000':(($par4_change_color== 2) ? "#000000" : '#325604');

                                $stats['par5_change'] =$this->roundvalue($par5_change,2).'%';
				$stats['par5_change_color'] =($par5_change_color== 1)?'#ff0000':(($par5_change_color== 2) ? "#000000" : '#325604');

                                $stats['in_change'] =$this->roundvalue($in_change,2).'%';
				$stats['in_change_color'] =($in_change_color== 1)?'#ff0000':(($in_change_color== 2) ? "#000000" : '#325604');

                                $stats['out_change'] =$this->roundvalue($out_change,2).'%';
				$stats['out_change_color'] =($out_change_color== 1)?'#ff0000':(($out_change_color== 2) ? "#000000" : '#325604');
				
                                $fdata['status'] ='1';
				$fdata['data'] =$stats;
				$fdata['message'] ='Score Stats';
			}
		}else{
			
			$stats['avg_gross_score'] = '-';
			$stats['avg_par3s'] = '-';
			$stats['avg_par4s'] = '-';
			$stats['avg_par5s'] = '-';
			$stats['avg_out'] = '-';
			$stats['avg_in'] = '-';
			$stats['last_gross_score'] = '-';
			$stats['last_par3s'] = '-';
			$stats['last_par4s'] = '-';
			$stats['last_par5s'] = '-';
			$stats['last_out'] = '-';
			$stats['last_in'] = '-';
			$stats["gscore_change"] = '-';
			$stats["gscore_change_color"] = '#0b5a97'; // default blue color
			$stats["par3_change"] = '-';
			$stats["par3_change_color"] = '#0b5a97'; // default blue color
			$stats["par4_change"] = '-';
			$stats["par4_change_color"] = '#0b5a97'; // default blue color
			$stats["par5_change"] = '-';
			$stats["par5_change_color"] = '#0b5a97'; // default blue color
			$stats["in_change"] = '-';
			$stats["in_change_color"] = '#0b5a97'; // default blue color
			$stats["out_change"] = '-';
			$stats["out_change_color"] = '#0b5a97'; // default blue color
		}
		}else{
			$stats['avg_gross_score'] = '-';
			$stats['avg_par3s'] = '-';
			$stats['avg_par4s'] = '-';
			$stats['avg_par5s'] = '-';
			$stats['avg_out'] = '-';
			$stats['avg_in'] = '-';
			$stats['last_gross_score'] = '-';
			$stats['last_par3s'] = '-';
			$stats['last_par4s'] = '-';
			$stats['last_par5s'] = '-';
			$stats['last_out'] = '-';
			$stats['last_in'] = '-';
			$stats["gscore_change"] = '-';
			$stats["gscore_change_color"] = '#0b5a97'; // default blue color
			$stats["par3_change"] = '-';
			$stats["par3_change_color"] = '#0b5a97'; // default blue color
			$stats["par4_change"] = '-';
			$stats["par4_change_color"] = '#0b5a97'; // default blue color
			$stats["par5_change"] = '-';
			$stats["par5_change_color"] = '#0b5a97'; // default blue color
			$stats["in_change"] = '-';
			$stats["in_change_color"] = '#0b5a97'; // default blue color
			$stats["out_change"] = '-';
			$stats["out_change_color"] = '#0b5a97'; // default blue color
			
		}
		}
		return $stats;
	}
	
	function getGirPercentage($data){
		$fdata = array();
		
		$admin_id = (isset($data['user_id']) &&$data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		$gir['hit'] ='-'; 
		$gir['missed'] = '-'; 
		if($admin_id > 0){
			$limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
			$quet1 = "select e.is_singlescreen,e.no_of_player,e.total_hole_num,s.no_of_holes_played,s.event_id,p.submit_score_date as stime,p.scorere_id from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$admin_id ." and p.is_submit_score='1' order by stime DESC ".$limit."";  
			$event_list  = $this->db->FetchQuery($quet1);
			
			if(is_array($event_list) && count($event_list)>0){
				foreach($event_list as $e=>$p){
					if(($p['is_singlescreen'] == '2') || ($p['is_singlescreen'] == '1' && $p['scorere_id'] ==$admin_id)){
						//if($p['no_of_holes_played'] == $p['total_hole_num']){
							$events[] = $p['event_id'];
						//}
					}
				}
				$event_list = (isset($events) && count($events)>0)?implode(',',$events):0; 
			
			if(isset($event_list) && $event_list !=0){
				$query = 'SELECT p.event_id,p.gir_yes as gir_in,p.gir_no as gir_out,no_of_holes_played FROM event_score_calc c LEFT JOIN event_score_calc_no_of_putt p ON p.event_id = c.event_id WHERE c.player_id = '.$admin_id.' AND p.player_id = '.$admin_id.' and c.event_id in('.$event_list.')ORDER BY p.no_of_putt_id DESC';
				$allgirEvnt = $this->db->FetchQuery($query);
				if(count($allgirEvnt)>0){
					//print_r($allgirEvnt);
					$gir_yes =$gir_no=$total_played_hole= 0;
					foreach($allgirEvnt as $i=>$s){
							$gir_yes+=$s['gir_in'];
							$gir_no+=$s['gir_out'];
							
							$staaa = $s['gir_in']+$s['gir_out'];
							
							//$total_played_hole+=$s['no_of_holes_played'];
							$total_played_hole+=$staaa;
					}
				//echo $gir_yes.'__'.$gir_no; die;
//echo $total_played_hole; die;
					$yespercent = (isset($total_played_hole) && $total_played_hole >0)?$gir_yes*100/$total_played_hole:0;
					$nopercent =  (isset($total_played_hole) && $total_played_hole >0)?$gir_no*100/$total_played_hole:0;
			if((isset($yespercent) && $yespercent == 0) && (isset($nopercent ) && $nopercent == 0)){

$gir['hit'] ='-'; 
$gir['missed'] = '-'; 
}else{

					//$gir['hit'] = $this->roundvalue(number_format( $yespercent * 100)); 
					//$gir['missed'] = $this->roundvalue(number_format( $nopercent * 100));
					
					$gir['hit'] = $this->roundvalue($yespercent); 
					$gir['missed'] = $this->roundvalue($nopercent);

					if($gir['hit']+$gir['missed']>100) {
						$diff = (($gir['hit']+$gir['missed'])-100)/2;
						$gir['hit'] = $gir['hit']-$diff;
						$gir['missed'] = $gir['missed']-$diff;
						
					}
					
}
}
			}else{
				$gir['hit'] ='-'; 
					$gir['missed'] = '-'; 
				
			}
		}else{
			$gir['hit'] ='-'; 
					$gir['missed'] = '-'; 
			
		}
		$stats = $gir ;
		}
		return $stats;
	}
	
	function getFairwayPercentage($data){
		$fdata = array();
		$admin_id = (isset($data['user_id']) &&$data['user_id'] >0)?$data['user_id']:58;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		
		if($admin_id > 0){
			$limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
			$quet1 = "select s.event_id,e.is_singlescreen,e.no_of_player,e.total_hole_num,s.no_of_holes_played,p.submit_score_date as stime,p.scorere_id from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$admin_id ." and p.is_submit_score='1' order by stime DESC ".$limit."";  
			$event_list  = $this->db->FetchQuery($quet1);
			if(is_array($event_list) && count($event_list)>0){
				foreach($event_list as $e=>$p){
					if(($p['is_singlescreen'] == '2') || ($p['is_singlescreen'] == '1' && $p['scorere_id'] ==$admin_id)){
						//if($p['total_hole_num'] == $p['no_of_holes_played']){
							$events[] = $p['event_id'];
						//}
					}
				}
				$event_list = (isset($events) && count($events)>0)?implode(',',$events):0;  
		
			if(isset($event_list) && $event_list >0){
	$query = 'SELECT c.no_of_holes_played,f.event_id,c.no_of_holes_played,f.fairway_1,f.fairway_2,f.fairway_3,f.fairway_4,f.fairway_5,f.fairway_6,f.fairway_7,f.fairway_8,f.fairway_9,f.fairway_10,f.fairway_11,f.fairway_12,f.fairway_12,f.fairway_13,f.fairway_14,f.fairway_15,f.fairway_16,f.fairway_17,f.fairway_18 FROM event_score_calc_fairway f left join event_score_calc c ON c.event_id = f.event_id WHERE c.player_id = "'.$admin_id.'" and f.player_id = "'.$admin_id.'" and c.event_id in('.$event_list .') ORDER BY f.fairway_id DESC ';
				$q11 = $this->db->FetchQuery($query);
				$leftpercent =$rightpercent=$centerpercent=array();
			
				if(count($q11)>0){
				$sum_hole_played=$fairway_left =$fairway_right=$fairway_center=$bydefaultfairway=$total_played_hole= 0;		
	
					foreach($q11 as $p=>$q1){
						$no_of_holes_played = $q1['no_of_holes_played']; 
						for($i=1;$i<=$no_of_holes_played ;$i++){
							if($q1['fairway_'.$i]==1){
								$fairway_left+=(1);
							}
							elseif($q1['fairway_'.$i]==3){
								$fairway_right+=(1);
							}
							elseif($q1['fairway_'.$i]==2){
								$fairway_center+=(1);
							}else{
								$bydefaultfairway+=(1);
							}
						}
						$sum_hole_played+=$no_of_holes_played;
					}
	 $sum_hole_played = $sum_hole_played-$bydefaultfairway ; 
					//echo $bydefaultfairway.'___'.$sum_hole_played.' : played hole<br/>';echo 	$fairway_left.'___'.(($fairway_left*100)/	$sum_hole_played).'<br/>';// echo 	$fairway_right.'___'.(($fairway_right*100)/	$sum_hole_played).'<br/>';//echo 	$fairway_center.'____'.(($fairway_center*100)/	$sum_hole_played).'<br/>';die;
				}
				//echo $fairway_left.'__'.$fairway_right.'__'.$fairway_center; die;
if(isset($fairway_left) && $fairway_left > 0){
$left =(($fairway_left*100)/$sum_hole_played); 
}else{
$left =0; 
}
if(isset($fairway_center) && $fairway_center> 0){
$center= (($fairway_center*100)/$sum_hole_played); 
}else{
$center=0; 
}
if(isset($fairway_right) && $fairway_right> 0){
$right = (($fairway_right*100)/$sum_hole_played); 
}else{
$right =0; 
}
$left = $this->roundvalue($left);
$right = $this->roundvalue($right);
$center = $this->roundvalue($center);
$total_sc =  ($left+$right+$center);
if($total_sc == 0){
    $fairway['left'] = '-'; 
				$fairway['right'] = '-';
				$fairway['hit']= '-';
}else{

$p_arr = array('1'=>$left,'2'=>$right,'3'=>$center);
$fairway_array = getCalculatedPercentage($p_arr,$total_sc,1);

                                $fairway['left'] = $fairway_array[1]; 
				$fairway['right'] = $fairway_array[2];
				$fairway['hit']= $fairway_array[3];
}



			
			
			}else{
				$fairway['left'] = '-'; 
				$fairway['right'] = '-';
				$fairway['hit']= '-';
			}
		}else{
			$fairway['left'] = '-'; 
				$fairway['right'] = '-';
				$fairway['hit']= '-';
			
		}
			$fdata['status'] ='1';
				$fdata['data'] =$fairway;
				$fdata['message'] ='Score Stats';
		}
		
		return $fairway;
	}
	
	function getPuttingStats($data){
		$fdata = array();
		
		$admin_id = (isset($data['user_id']) &&$data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		
		if($admin_id > 0){
			$limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
			$quet1 = "select e.is_singlescreen,e.no_of_player,e.total_hole_num,s.no_of_holes_played,s.event_id,p.submit_score_date as stime,p.scorere_id from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$admin_id ." and p.is_submit_score='1' order by stime DESC ".$limit."";  
			$event_list  = $this->db->FetchQuery($quet1);
			$no_of_holes_played = 0;
//print_r($event_list);die;
			if(is_array($event_list) && count($event_list)>0){
				foreach($event_list as $e=>$p){
					if(($p['is_singlescreen'] == '2') || ($p['is_singlescreen'] == '1' && $p['scorere_id'] ==$admin_id)){
						//if($p['total_hole_num'] == $p['no_of_holes_played']){
							$events[] = $p['event_id'];
							$no_of_holes_played+=$p['no_of_holes_played'];
						//}
					}
				}
				$event_list = (isset($events) && count($events)>0)?implode(',',$events):0; 
			//echo $no_of_holes_played;
			if(isset($event_list) && $event_list>0){
				$gir_str = array();
				for($b=1;$b<=18;$b++) {
					$gir_str[] = "gir_{$b}";
				}
				$gir_str = implode(",",$gir_str);
				$sql = 'SELECt e.total_hole_num,'.$gir_str.',p.no_of_putt_1,p.no_of_putt_2,p.no_of_putt_3,p.no_of_putt_4,p.no_of_putt_5,p.no_of_putt_6,p.no_of_putt_7,p.no_of_putt_8,p.no_of_putt_9,p.no_of_putt_10,p.no_of_putt_11,p.no_of_putt_12,p.no_of_putt_13,p.no_of_putt_14,p.no_of_putt_15,p.no_of_putt_16,p.no_of_putt_17,p.no_of_putt_18,p.per_hole,per_gir from event_score_calc_no_of_putt p LEFT JOIN event_table e ON e.event_id = p.event_id where p.player_id = "'.$admin_id.'" and p.event_id in('.$event_list.') group by p.event_id ORDER BY p.no_of_putt_id DESC';
				$putting = $this->db->FetchQuery($sql);
				$hole=$gir=$puttin=0;
				$put_gir_hole_sum = array();
$no_event =count($putting);//print_r($putting);die;
				//if(count($putting)>0){
				foreach($putting as $i=>$v){
					for($i=1;$i<=$v['total_hole_num'];$i++){
						if($v['no_of_putt_'.$i] != '-1') {
							$puttin+=$v['no_of_putt_'.$i];
						}
						
						
						if($v['gir_'.$i] == 1 && $v['no_of_putt_'.$i] != '-1') {
							$put_gir_hole_sum[]=$v['no_of_putt_'.$i];
						}
						
					}
					$hole+=$v['per_hole'];
					$gir+=$v['per_gir'];
				}
//$no_of_event  = ($no_event > $no_of_event)?$no_of_event:$no_event;
if($no_of_event == 0){
$no_of_event =$no_event ;
}elseif($no_event > $no_of_event){
$no_of_event =$no_of_event;
}else{
$no_of_event =$no_event ;
}
$hole = ($no_of_holes_played>0) ? ($puttin/$no_of_holes_played) : 0;
//$gir =($gir/$no_of_event);
$gir =(isset($put_gir_hole_sum) && count($put_gir_hole_sum)>0)?(array_sum($put_gir_hole_sum)/count($put_gir_hole_sum)):0;
$puttin = $no_of_event>0 ? ($puttin/$no_of_event) : 0;
//echo $gir; die;
//echo $hole.'__'.$gir.'__'.$puttin ; die;
if((isset($hole) && $hole == 0) && (isset($gir) && $gir ==0) && (isset($puttin) && $puttin == 0)){
$putt['per_hole_avg'] = '-';
				$putt['per_gir_avg'] = '-' ;
				$putt['per_round_avg'] = '-' ;
}else{
$putt['per_hole_avg'] = $this->roundvalue($hole,2);
				$putt['per_gir_avg'] = $this->roundvalue($gir,2) ;
				$putt['per_round_avg'] = $this->roundvalue($puttin,2) ;
}

				
				
			}else{
				$putt['per_hole_avg'] = '-';
				$putt['per_gir_avg'] = '-' ;
				$putt['per_round_avg'] = '-';	
			}
			}else{
			$putt['per_hole_avg'] = '-';
				$putt['per_gir_avg'] = '-' ;
				$putt['per_round_avg'] = '-';	
				
			}
			$fdata['status'] = '1';
				$fdata['data'] = $putt;
				$fdata['message'] = 'data';
		}
		return $putt;
	}
	
	function getRecoveryStats($data){
		$fdata= array();
		$admin_id = (isset($data['user_id']) && $data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		
		if($admin_id > 0){
			$limit = ($no_of_event > 0)?' limit '.$no_of_event.'':'';
			$quet1 = "select e.is_singlescreen,e.no_of_player,e.total_hole_num,s.no_of_holes_played,s.event_id,p.submit_score_date as stime,p.scorere_id from event_score_calc s inner join event_table e on s.event_id = e.event_id inner join event_player_list p ON p.player_id = s.player_id and p.event_id=s.event_id where s.player_id= ".$admin_id ." and p.is_submit_score='1' order by stime DESC ".$limit."";  
			$event_list  = $this->db->FetchQuery($quet1);
				
				if(is_array($event_list) && count($event_list)>0){
					foreach($event_list as $e=>$p){
						if(($p['is_singlescreen'] == '2') || ($p['is_singlescreen'] == '1' && $p['scorere_id'] ==$admin_id)){
							//if($p['total_hole_num'] == $p['no_of_holes_played']){
								$events[] = $p['event_id'];
							//}
						}
					}
					$event_list = (isset($events) && count($events)>0)?implode(',',$events):0; 
		
			if(isset($event_list) && ($event_list)>0){
				$query = 'Select e.no_of_holes_played,e.score_entry_1,score_entry_2,score_entry_3,score_entry_4,score_entry_5,score_entry_6,score_entry_7,score_entry_8,score_entry_9,score_entry_10,score_entry_11,score_entry_12,score_entry_13,score_entry_14,score_entry_15,score_entry_16,score_entry_17,score_entry_18,e.event_id,e.player_id,p.gir_no,p.gir_yes,e.no_of_holes_played,s.sand_1,s.sand_2,s.sand_3,s.sand_4,s.sand_5,s.sand_6,s.sand_7,s.sand_8,s.sand_9,s.sand_10,s.sand_11,sand_12,s.sand_13,s.sand_14,s.sand_15,s.sand_16,s.sand_17,s.sand_18,e.par_1,e.par_2,e.par_3,e.par_4,e.par_5,e.par_6,e.par_7,e.par_8,e.par_9,e.par_10,e.par_11,e.par_12,e.par_13,e.par_14,e.par_15,e.par_16,e.par_17,e.par_18,p.gir_1,gir_2,gir_3,gir_4,gir_5,gir_6,gir_7,gir_8,gir_9,gir_10,gir_11,gir_12,gir_13,gir_14,gir_15,gir_16,gir_17,gir_18 FROM event_score_calc e LEFT JOIN event_score_calc_no_of_putt p ON p.event_id=e.event_id LEFT JOIN event_score_calc_sand s ON s.event_id=e.event_id WHERE p.player_id = '.$admin_id.' AND e.player_id = '.$admin_id.' AND s.player_id = '.$admin_id.' and e.event_id in('.$event_list.') ORDER BY no_of_putt_id DESC'; 
				$q1= $this->db->FetchQuery($query);
			
			if(count($q1)>0){
				//print_r($q1 ); die;
				$scramble_yes=$scramble_no=$sand_no=$sand_yes=$no_of_hole_sand_yes=$no_of_hole_played_yes=0;
				foreach($q1 as $v=>$k){
					for($i=1;$i<=18;$i++){
						if($k['gir_'.$i] == 2){
							$no_of_hole_played_yes+=1;
							if($k['score_entry_'.$i]<=$k['par_'.$i]){
								$scramble_yes+=1;
							}
						}else{
							$scramble_no+=0;
						}
						
						if($k['sand_'.$i] >= 0){
							$no_of_hole_sand_yes+= 1;
							if($k['score_entry_'.$i] <= $k['par_'.$i]){
								$sand_yes+=1;
							}	
						}
					}				
				}
//echo $scramble_yes.'__'.$scramble_no.'__'.$sum_hole_played; die;
$scrm = (isset($scramble_yes) && $scramble_yes > 0)?$this->roundvalue(($scramble_yes/$no_of_hole_played_yes)*100):0;
$san =(isset($sand_yes) && $sand_yes > 0)?$this->roundvalue(($sand_yes/$no_of_hole_sand_yes)*100):0;

if($scrm == 0 &&  $san == 0){
$recovry['scrmbl_avg'] = '-';
$recovry['sand_avg'] = '-' ;
}else{
$recovry['scrmbl_avg'] = $scrm ;
$recovry['sand_avg'] = $san ;
}

				
			
				
				
			}else{
				$recovry['scrmbl_avg'] = '-' ;
$recovry['sand_avg'] = '-' ;
			}
		}else{
			$recovry['scrmbl_avg'] = '-' ;
$recovry['sand_avg'] = '-' ;
		}
	}else{
		
		$recovry['scrmbl_avg'] = '-' ;
$recovry['sand_avg'] = '-' ;
	}
	$fdata['status'] = '1';
				$fdata['data']=$recovry;
				$fdata['message'] = 'Recovery Stats data';
			
		}
		return $recovry;
	}
	
	function getStats($data){
		$user_id = (isset($data['user_id']) && $data['user_id'] >0)?$data['user_id']:0;
		$no_of_event = (isset($data['no_of_event']))?$data['no_of_event']:10;	
		if($user_id > 0){

$pichat['pichart'] = $this->getStatsPiChart($data);
$pichat['score_stats'] = $this->getScoreStats($data);
$pichat['gir_percentage'] = $this->getGirPercentage($data);
$pichat['fairway_percentage'] = $this->getFairwayPercentage($data);
$pichat['putting_stats'] = $this->getPuttingStats($data);
$pichat['recovery_stats'] = $this->getRecoveryStats($data);

$chk_Otp='select count(1) as c from push_notification_user_list where user_id="'.$user_id.'" and is_read_by_user="0"';  
			$noti_count  = $this->db->FetchSingleValue($chk_Otp);
			$pichat['notifications_count'] = ($noti_count > 0) ? '1' : '0';

		$fdata['status'] = '1';

		$fdata['data'] = $pichat;
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field Not Found';
		}
		return $fdata;
	}
	
	function roundvalue($value,$decimal_places=1) {
		$decimal_places = 1; //static
		if(trim($value)!='' && is_numeric($value)) {
			if($decimal_places=='' || $decimal_places<=0) {
				return round($value);
			}
			elseif($decimal_places>0) {
				$exp = explode(".",$value);
				$val_first = $exp[0];
				if(isset($exp[1]) && trim($exp[1])!='') {
					$exp[1] = trim($exp[1]);
					$len = strlen($exp[1]);
				}
				else {
					$len = 0;
				}
				if($len < $decimal_places) {
					$diff = $decimal_places - $len;
					$str='';
					for($i=0;$i<$diff;$i++) {
						$str.='0';
					}
					$new_val = $exp[0].'.'.$exp[1].$str;
				}
				else {
					$new_val = round($value,$decimal_places);
				}
				return $new_val;
			}
			else {
				return $value;
			}
		}
		else {
			return false;
		}
	}
	
}
?>