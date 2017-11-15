<?php

class Friend{
	public $db,$data = array();
	
	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = _STATE_TBL_;
		
	}
	
	function getSuggessionFriendList($data){
		       $users =  new users();
				
				$AlertsArray=array();
				
				
				$search_key=(isset($data['searchkey']) && $data['searchkey'] !="")?$data['searchkey']:"";
				$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
				$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"0";
				if($user_id==""){
					$fdata['status'] = '0';
					$fdata['Suggestion List'] = '';
					$fdata['msg'] = 'Required field not found';
			
				}else{
					
					$event_players_arr = $event_rejected_players_arr = array();
					
					$my_data = $this->db->FetchRow("select user_id,country from user_profile where user_id='".$user_id."' ");
					
					if($event_id > 0) {
						$event_players_data = $this->db->FetchSingleValue("select group_concat(player_id) as player_id from event_player_list where event_id='".$event_id."' ");
						if(trim($event_players_data)!='') {
							$event_players_arr = explode(',',$event_players_data);
						}
						
						$event_players_data = $this->db->FetchSingleValue("select group_concat(player_id) as player_id from event_player_list where event_id='".$event_id."' and is_accepted='2'");
						if(trim($event_players_data)!='') {
							$event_rejected_players_arr = explode(',',$event_players_data);
						}
					}
					
					
					
					if(isset($my_data['user_id']) && $my_data['user_id'] > 0){
					$con=''; $con2='';
					if($search_key!=""){
						
						 $con=" and (u.full_name like '%".$search_key."%')";
						 $con2=" and (a.full_name like '%".$search_key."%')";
					}
					$suggestionArr=array();
					
					
					$count_myevent = $this->db->FetchQuery("select event_id,last_modified_date from event_player_list where   (event_admin_id='".$user_id."' or player_id='".$user_id."')  and is_submit_score='1' group by event_id order by last_modified_date desc ,event_id desc limit 5");
					$my_event_arr = array(); 
					
					if(count($count_myevent) > 0){
						foreach($count_myevent as $i=>$e){
							$my_event_arr[]=$e['event_id'];
						}
					}
					//$count_myevent=0;
					$app_query_str="";
					if(count($my_event_arr)==1){
					$app_query_str= " = '".$my_event_arr[0]."'";
					}else if(count($my_event_arr)>1){
					$my_event_arr_str=implode(",",$my_event_arr);
					$app_query_str= " in (".$my_event_arr_str.")";
					}
		 $queryString = "select * from ((SELECT distinct e.player_id as user_id,u.full_name,u.registered_mobile_number,u.user_name,e.event_id,e.last_modified_date,p.country,p.self_handicap,p.photo_url FROM `event_player_list` e  right join golf_users u on e.player_id=u.user_id left join user_profile p on p.user_id = u.user_id where u.is_active != 2 ".$con." and is_submit_score='1' and e.event_id ".$app_query_str."  order by e.last_modified_date desc ) union (select a.user_id as user_id,a.full_name,a.registered_mobile_number,a.user_name,a.user_level,'',p.country,p.self_handicap,p.photo_url from golf_users a join user_profile p on p.user_id = a.user_id where a.user_id!='".$user_id."' and a.is_active!='2' ".$con2."  group by a.user_id)) b where b.user_id!='".$user_id."' and b.country = '".$my_data['country']."' group by user_id order by
		  b.last_modified_date desc,full_name asc";
		  
		 //echo $queryString; die;
					$rsContentDetail = $this->db->FetchQuery($queryString);
//print_r($rsContentDetail);die;
					if(count($rsContentDetail)>0){
					foreach($rsContentDetail as $i=>$rowValues){
					
							$user_event_arr =array();
							$already_played=0;
							$already_added=0;
							if(count($count_myevent)>0) {

								if((is_array($my_event_arr) && count($my_event_arr)>0) && is_numeric($rowValues['event_id']) && $rowValues['event_id']>0  ) {
										if(in_array($rowValues['event_id'],$my_event_arr)){
										
											$already_played=1;
										}
										 
									}
							
							}
							
							if(is_array($event_players_arr) && count($event_players_arr)>0 && in_array($rowValues['user_id'],$event_players_arr)){
								$already_added=1;
							}
							
							if(is_array($event_rejected_players_arr) && count($event_rejected_players_arr)>0 && in_array($rowValues['user_id'],$event_rejected_players_arr)){
								$already_added=0;//$already_added=1;
							}
							
							if(isset($rowValues["event_id"])){unset($rowValues["event_id"]);}
							$rowValues["played"] = $already_played;
							$rowValues["added"] = $already_added;
							$purl = (isset($rowValues['photo_url']) && $rowValues['photo_url'] != ''  && file_exists(UPLOADS_PROFILE_PATH.'thumb/'.$rowValues['photo_url']))?$rowValues['photo_url']:'';
							if($purl != ''){
								$thumb_url=DISPLAY_PROFILE_PATH.'thumb/'.$purl;	
								$photo_url=DISPLAY_PROFILE_PATH.$purl;
							}
							else{
								$thumb_url=DISPLAY_PROFILE_PATH.'thumb/noimage.png';		
								$photo_url=DISPLAY_PROFILE_PATH.'thumb/noimage.png';
							}
							//$arr = $users->getUserProfileDetail(array("user_id"=>$rowValues["user_id"]));
							//print_r($arr); //die;
							$rowValues['profile_image'] = $photo_url;
							$rowValues['thumb_image'] = $thumb_url;

							
							$suggestionArr[]=$rowValues; 
						}
					//die;
					foreach($suggestionArr as $tmparr){
						
						if($tmparr["played"]==1 ){
						
						
						$final_arr["bookmark"][]=$tmparr;

							}else{
							$final_arr["not_bookmark"][]=$tmparr;
								}
						}	
						
						$fd=array();
						if(is_array($final_arr['bookmark']) && count($final_arr['bookmark'])>0){
						
						if(is_array($final_arr['not_bookmark']) && count($final_arr['not_bookmark'])>0){
						$fd=array_merge($final_arr['bookmark'],$final_arr['not_bookmark']);
						}else{
							$fd=$final_arr['bookmark'];
							}
						
						}else{
							$fd=$final_arr['not_bookmark'];
							}
					$fdata['status'] = '1';
					$fdata['Suggestion List'] = $fd;
					$fdata['msg'] = 'Suggestion Listing';
					}else{
						$fdata['status'] = '0';
					$fdata['msg'] = 'No Data Found';
					}												
				}
				else{
					
					$fdata['status'] = '0';
					$fdata['Suggestion List'] = '';
					$fdata['msg'] = 'User Not Exist';
				}
				}
	
				return $fdata ; 
		}
function getDelegateUserList($data){
		$fdata = array();
		$eventId=isset($data['event_id'])?$data['event_id']:"";
		$user_id=isset($data['user_id'])?$data['user_id']:"";
		$player_id=(isset($data['player_id']) && $data['player_id']>0)?$data['player_id']:0; // 1= delegatelist, 2= assign delegated list
		$ParticipentArray=array();

		if($eventId==''){
			$fdata['status'] = '0';
			$fdata['message'] = 'Required field can not be blank.';
		}else{	
$is_singl = "SELECT e.is_singlescreen,p.scorere_id FROM event_table e LEFT JOIN event_player_list p ON p.event_id = e.event_id  WHERE e.event_id=".$eventId." AND p.scorere_id = ".$user_id." ";		
$is_scorer_value = $this->db->FetchRow($is_singl);
if(isset($is_scorer_value) && is_array($is_scorer_value) && count($is_scorer_value) > 0){
	if($is_scorer_value['is_singlescreen'] == 1){
	$con = ($player_id > 0)?' AND e.player_id != '.$user_id.'':' and e.player_id='.$is_scorer_value['scorere_id'].'';	
	}else{
	$con = ($player_id > 0)?' AND e.player_id != '.$user_id.'':' and e.scorere_id='.$is_scorer_value['scorere_id'].'';
	}
	 $queryString = "select (select count(*) from event_player_list where event_id ='".$eventId."' and scorere_id=e.player_id) as scorer_count,e.player_id,e.scorere_id,e.event_id,p.display_name as player_name,u.self_handicap as handicap_value,sc.calculated_handicap as handicap_value_score,s.display_name as scorer_name from event_player_list e left join event_score_calc sc on e.event_id = sc.event_id and e.player_id = sc.player_id left join golf_users p on p.user_id=e.player_id left join golf_users s on s.user_id=e.scorere_id left join user_profile u ON u.user_id = e.scorere_id where e.event_id ='".$eventId."' and e.is_accepted =1 ".$con."";

			$queryResult = $this->db->FetchQuery($queryString);
			
			if(count($queryResult) > 0){
				foreach($queryResult as $i=>$d){
					
					if(trim($d['handicap_value_score'])!='' && $d['handicap_value_score']>=0) {
						$d['handicap_value'] = $d['handicap_value_score'];
					}
					unset($d['handicap_value_score']);
					if($player_id >0){
						if($d['scorer_count'] <=3){
							$list[] = $d ;
						}
					}
					else {
						$list[] = $d ;
					}
				}
				
					$fdata['status'] = '1';
					$fdata['data'] = $list;
					$fdata['message'] = 'Delegate User List';
			}else{
$fdata['status'] = '0';
				$fdata['message'] = 'FriendList Empty';
}
}else{
				$fdata['status'] = '0';
				$fdata['message'] = 'You can not Delegate';
			}

			
		}
			return $fdata ;
	}
	
	function makeDelegate($data){
		$user_id=isset($data['user_id'])?$data['user_id']:"";
		$delegate_player=isset($data['delegate_player'])?$data['delegate_player']:"";
		$event_id=isset($data['event_id'])?$data['event_id']:"";
		$fdata =array();
		if($user_id==""  || $event_id==""){
			$fdata['status'] ='0';
			$fdata['message'] ='Required Field can not blank.';
		}
		else {
			$sql = "update event_player_list set delegate_status='0' where event_id='{$event_id}' and scorere_id='{$user_id}'";
			$this->db->FetchQuery($sql);
			$k = array();
			if(count($delegate_player)>0){
				foreach($delegate_player as $i=>$p){
					if(isset($p['delegated_to'])) {
						$k[] = $p['delegated_to'];
					}
				}
				$count_delegate = array_count_values($k); //print_r($k);print_r($count_delegate);die;
				
				$has_err = array();
				
				foreach($count_delegate as $d=>$c){
					$queryString = "select count(*) as total,u.full_name from event_player_list p LEFT JOIN golf_users u ON u.user_id = p.scorere_id where p.event_id='".trim($event_id)."' and p.scorere_id = ".trim($d); 
					$total_scorere = $this->db->FetchRow($queryString);
					$total_scor = $c + $total_scorere['total'];
					if($total_scor > 4) {
						$has_err[] =$total_scorere['full_name'].' can not make delegate more than 4.';
						
					}
				}
				
				if(count($has_err) > 0) {
					$fdata['status'] ='0';
					$fdata['message'] =implode("\n",$has_err);
				}
				else {
					$send_noti = array();
					foreach($delegate_player as $d=>$c){
						if(isset($c['player_id']) && $c['player_id'] >0){
							$userArray = array();
							$is_query = "SELECT e.is_singlescreen FROM event_table
							e LEFT JOIN event_player_list p ON p.event_id = e.event_id  WHERE e.event_id=".$event_id." AND p.scorere_id = ".$user_id." ";		
							$is_sin_query1 = $this->db->FetchSingleValue($is_query);

							if(isset($is_sin_query1) && $is_sin_query1 >0 && $total_scor <= 4){
								$con = ($is_sin_query1 == 1)?' ':' And player_id="'.$c['player_id'].'"';
								$queryString = "update event_player_list set";
								$queryString .= " scorere_id='".$c['delegated_to']."'";			
								$queryString .= " where event_id='".$event_id."' ".$con."";
								$this->db->FetchQuery($queryString);
								$fdata['status'] ='1';
								$fdata['message'] ='Delegate created.';

								$sql = "update event_player_list set delegate_status='0' where event_id='{$event_id}' and scorere_id='".$c['delegated_to']."'";
								$this->db->FetchQuery($sql);
								
								if(!isset($send_noti[$c['delegated_to']])) {
									$send_noti[$c['delegated_to']] = array('total' => 1, 'from_user' => $c['player_id']);
								}
								else {
									$send_noti[$c['delegated_to']]['total'] = $send_noti[$c['delegated_to']]['total']+1;
								}
								
								//$notif_mth = new createNotification();
								//$notif_mth->generatePushNotification($event_id,5,$user_id,$c['delegated_to']);
							}
							else {
								//$fdata['status'] ='0';
								//$fdata['message'] ='You dont have permission';
							}
						}
						else{
							//$fdata['status'] ='0';
							//$fdata['message'] ='Player Id Not Found';
						}
					}
					
					if(count($send_noti)>0) {
						
						$queryString = "select full_name from golf_users";
						$queryString .= " where user_id =".$user_id;
						$full_name = $this->db->FetchSingleValue($queryString);
						
						foreach($send_noti as $a=>$b) {
							if($b['total'] > 0) {
								$msg = '';
								
								if($b['total'] == 1) {
									
									if($b['from_user'] == $a) { // delegate user to user
										$queryString = "select full_name from golf_users";
										$queryString .= " where user_id =".$b['from_user'];
										$other_name = $this->db->FetchSingleValue($queryString);
										$msg = $full_name." has delegated your score entry to you. Tap here to enter your score.";
									}
									elseif($b['from_user'] != $user_id) { // other user
										$queryString = "select full_name from golf_users";
										$queryString .= " where user_id =".$b['from_user'];
										$other_name = $this->db->FetchSingleValue($queryString);
										$msg = $full_name." has delegated ".$other_name."'s score entry to you. Tap here to enter ".$other_name."'s score.";
									}
									else { // his own score
										$msg = $full_name." has delegated his score entry to you. Tap here to enter his/her score.";
									}
								}
								else { // multiple user
									$msg = $full_name." has delegated ".$b['total']." players score entry to you. Tap here to enter their scores.";
								}
								
								$notif_mth = new createNotification();
								$notif_mth->generatePushNotification($event_id,5,$user_id,$a,0,$msg);
							}
						}
					}
				}
			}
			else{
				$fdata['status'] ='0';
				$fdata['message'] ='You can not make delegate yourself.';
			}
		}
		return $fdata;
	}		
}
?>