<?php
class Events{
	public $db,$data = array();

	function __construct(){
		global $database;
		$this->db = $database;
		$this->table = _STATE_TBL_;

	}

	function geStrokePlayList($filter=array()) {
		$data = $filter;

	 	$no_of_player = (isset($data['no_of_player']) && $data['no_of_player'])?$data['no_of_player']:0;
		 $player_type = (isset($data['player_type']) && $data['player_type'])?$data['player_type']:2;
		$condi = '';
		$fdata = array();
		if(($no_of_player > 0) || $player_type > 0){

			if($no_of_player > 0){
				$condi = ' AND player_id = "'.$no_of_player.'"';
			}
			if($player_type > 0){
				$condi .= ' AND player_type = "'.$player_type.'"';
			}

			$sql = "select game_format_id from "._PLAYER_GAME_FORMAT_." where is_active= 1 ".$condi." "; //die;
			$queryResult1 = $this->db->FetchQuery($sql);

			$list =  isset($queryResult1[0]['game_format_id']) ? $queryResult1[0]['game_format_id'] : '';
			if($list!='') {
			$queryString= "SELECT upper(format_name) as format_name,format_id from "._GAME_FORMAT_TBL_." where format_id in(".$list.") group by format_id order by display_order ASC";
			 $queryResult = $this->db->FetchQuery($queryString)	;

			$StrokePlayListArray = array();

			if(count($queryResult) > 0)
			{
				foreach($queryResult as $i=>$rowValues )
				{
					$StrokePlayListArray[] = $rowValues ;
				}
				$fdata['status'] = '1';
				$fdata['data'] = $StrokePlayListArray;
				$fdata['message']="Stroke List";
			}
			else
			{
				$fdata['status'] = '0';
				$fdata['data'] = '';
				$fdata['message']="Stroke List Not Found";
			}
			}
			else {
				$fdata['status'] = '0';
				$fdata['data'] = '';
				$fdata['message']="Stroke List Not Found";
			}
		}
		else{
				$fdata['status'] = '0';
				$fdata['message']="Required Field Not Found";
			}

			return $fdata;
	}

	function geEventFormatList($data){
		if(isset($data['no_of_player']) && $data['no_of_player'] >0){
        $type = $data['no_of_player'];
		$queryString = "select event_format_id, upper(format_name) as format_name from "._EVENT_FORMAT_TBL_." where find_in_set('".$type."',no_of_player) and is_active != 1 order by event_format_id asc";
		$StrokePlayListArray = array();
		$queryResult = $this->db->FetchQuery($queryString)	;

		   if(count($queryResult) > 0)
            {
                foreach($queryResult as $i=>$rowValues)
                {
		            $StrokePlayListArray[] = $rowValues ;
	            }
				$fdata['status'] = '1';
				$fdata['data'] = $StrokePlayListArray;
				$fdata['message'] = 'Listing';
			}
            else
            {
				$fdata['status'] = '0';
				$fdata['message'] = 'List Empty';

            }
		}else{
			 {
				$fdata['status'] = '0';
				$fdata['message'] = 'Required Field not found';

            }
		}
			return $fdata ;
    }

	function geEventTypeList(){
		$fdata = array();
		$queryString = "select event_type_id, type from "._EVENT_TYPE_TBL_." where is_active != 1 order by event_type_id asc";
		$StrokePlayListArray = array();
		$queryResult = $this->db->FetchQuery($queryString)	;

	   if(count($queryResult) > 0)
		{
			foreach($queryResult as $i=>$rowValues)
			{
				$StrokePlayListArray[] = $rowValues ;
			}
			$fdata['status'] = '1';
			$fdata['EventTypeList'] = $StrokePlayListArray;
			$fdata['message']="Event Type List";
		}
		else
		{
			$fdata['status'] = '0';
			$fdata['EventTypeList'] = '';
			$fdata['message']="No Data Found";
		}
		return $fdata ;
	}

function isvalidEventData(&$eventInfoArray) {
	$return = false;
	$err = false;
	$no_of_player = isset($eventInfoArray['no_of_player'])?$eventInfoArray['no_of_player']:"1";
	$invited_group_list=isset($eventInfoArray['event_group_list'])?$eventInfoArray['event_group_list']:array();
	$event_admin_id = isset($eventInfoArray['event_admin_id'])?$eventInfoArray['event_admin_id']:"";
	$event_friend_list=isset($eventInfoArray['event_friend_list'])?$eventInfoArray['event_friend_list']:array();
	$invited_email_list=isset($eventInfoArray['invited_email_list'])?$eventInfoArray['invited_email_list']:array();
	$team_list=isset($eventInfoArray['team_list'])?$eventInfoArray['team_list']:array();

	$event_admin_email = '';
	$grpsql='select user_name from golf_users where user_id ="'.$event_admin_id.'" limit 1';
	$event_admin_email = $this->db->FetchSingleValue($grpsql);

	$group_count = 0; $group_members = $grpsqlresultssa = array();
	$team_member_count = 0; $team_members = array();
	$friend_count = 0; $friend_members = array();
	$email_count = 0; $email_members = array();
	// get group members
	if(isset($eventInfoArray['event_group_list']) && count($eventInfoArray['event_group_list']) > 0){
		for($i=1;$i<=count($eventInfoArray['event_group_list']);$i++) {
			$InvGroup = isset($invited_group["group"]) ? $invited_group["group"] : 0;
			if($InvGroup>0 && !in_array($InvGroup,$grpsqlresultssa)){
				$grpsqlresultssa[] =  $InvGroup;
			}
		}
		if(count($grpsqlresultssa) > 0) {
			$grpsql='select group_concat(grp_member_id) as total_members from '._GROUP_MEMBER_LIST_.' where group_id in ('.implode(',',$grpsqlresultssa).') limit 1';
			$group_members_str = $this->db->FetchSingleValue($grpsql);
			$group_members_old = explode(',',$group_members_str);
			foreach($group_members_old as $a) {
				if($event_admin_id!=$a) {
					$group_members[] = $a;
				}
			}

			$group_count = 	count($group_members) ;
		}
	}

	// get team members
	if(count($team_list)>0){
		$counter = 0;
		foreach($team_list as $teamNameId){
			$friendCounter = 1;
			foreach( $eventInfoArray['team_list'][$counter]['event_friend_list'][0] as $player_id){
				if($player_id<=0) {continue;}
				if(!in_array($player_id,$team_members) && $player_id!=$event_admin_id) {
					$team_members[] = $player_id;
				}
				$friendCounter++;
			}
			$counter++;
		}
		$team_member_count = count($team_members);
	}

	// get friend suggestion list
	$friendCounter = 1;
	if(count($event_friend_list)>0){
		foreach($event_friend_list as $particiantId){
			$friendUserId = "friend_id";
			if(!isset($particiantId[$friendUserId]) || $particiantId[$friendUserId]<=0) {
				continue;
			}
			$userId = $particiantId[$friendUserId];
			if(!in_array($userId,$friend_members) && $userId!=$event_admin_id) {
				$friend_members[] = $userId;
			}
			$friendCounter++;
		}
		$friend_count = count($friend_members);
	}

	// get email invites
	if(is_array($invited_email_list) && count($invited_email_list) > 0 ){
		$inviteCounter=1;
		foreach($invited_email_list as $nonregemail){

			$InvEmail = isset($nonregemail["email"]) ? $nonregemail["email"] :'';
			if($InvEmail!="" && !in_array($InvEmail,$email_members) && $InvEmail!=$event_admin_email){
				$email_members[] = $InvEmail;
			}
			$inviteCounter++;
		}
		$email_count = count($email_members);
	}
//echo $team_member_count+$friend_count+$email_count; die;
	/* echo $team_member_count ;
	 print_r($group_members);
	print_r($team_members);
	print_r($friend_members);
	print_r($email_members);die;
	*/
	// for 1 player
	if($no_of_player == '1') {
		if($group_count > 0) {
			$err = true;
			$return = 'Group is not allowed with one player game';
		}
		elseif($team_member_count > 0) {
			$err = true;
			$return = 'Team is not allowed with one player game';
		}
		elseif($friend_count > 0) {
			$err = true;
			$return = 'Multiple Players are not allowed with one player game';
		}
		elseif($email_count > 0) {
			$err = true;
			$return = 'Email Invites are not allowed with one player game';
		}
		else {
			$err = false;
			$return = true;
		}
		$eventInfoArray['invited_email_list'] = array();
		$eventInfoArray['event_friend_list'] = array();
		$eventInfoArray['event_is_team'] = 0;
		$eventInfoArray['event_team_num'] = 0;
		$eventInfoArray['event_friend_num'] = 0;
		$eventInfoArray['team_list'] = array();
	}
	// for 2,3,4 players
	elseif($no_of_player == '2' || $no_of_player == '3' || $no_of_player == '4') {
		$one_less = $no_of_player-1;
		$arr = array(1=>'one',2=>'two',3=>'three',4=>'four');
$pl = $no_of_player-1;
		if($group_count>0) {
			$err = true;
			$return = 'Group is not allowed with '.$arr[$no_of_player].' player game';
		}
		elseif($team_member_count > 0 && $no_of_player!=4) {
			$err = true;
			$return = 'Team is not allowed with '.$arr[$no_of_player].' player game';
		}
		elseif((($team_member_count+$friend_count+$email_count) < $one_less) ) {
//elseif((($team_member_count+$friend_count+$email_count) < $one_less) || ((($team_member_count+$email_count) < $one_less) ) ) {
			$err = true;
			$return = 'Minimum '.$pl.' players required with '.$arr[$no_of_player].' player game';
		}
		elseif(($friend_count+$email_count+$team_member_count) > $one_less) {
			$err = true;
			$return = 'Maximum '.$pl.' players allowed with '.$arr[$no_of_player].' player game';
		}
		else {
			$err = false;
			$return = true;
		}
		$eventInfoArray['event_group_list'] = array();
		if($no_of_player!=4) {
			$eventInfoArray['event_is_team'] = 0;
			$eventInfoArray['event_team_num'] = 0;
			$eventInfoArray['team_list'] = array();
		}
	}
	elseif($no_of_player == '4+') {
		$arr = array(1=>'one',2=>'two',3=>'three',4=>'four');
		/*if($team_member_count > 0) {
			$err = true;
			$return = 'Team is not allowed with 4+ player game';
		}
		elseif(($group_count+$friend_count+$email_count) < 4) {
			$err = true;
			$return = 'Minimum 3 players required with 4+ player game';
		}
		else {
			$err = false;
			$return = true;
		} */
        $err = false;
		$return = true;
		$eventInfoArray['event_is_team'] = 0;
		$eventInfoArray['event_team_num'] = 0;
		$eventInfoArray['team_list'] = array();
	}
	return $return;
}

function convertTeeFormat($tee){
	$d3=$this->object_to_array($tee);
    $d3=$d3[0];
	if(count($d3) > 1){
			$teedata=array();
			foreach($d3 as $key=>$val){
				$arr=array($key => $val);
				$teedata[]=(object)$arr;
			}
			$data1=(array)$teedata;
	}else{
		$data1=$tee;
	}
    return json_encode($data1);
}

function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;
    if(is_array($obj)) {
        $new = array();
        foreach($obj as $key => $val) {
            $new[$key] = $this->object_to_array($val);
        }
    }
    else $new = $obj;
    return $new;
}

function createEvents($data){
$fdata = array();
			$eventInfoArray =  $data ;
			//print_r($data);die;
			$st_err = $this->isvalidEventData($eventInfoArray);
			//var_dump($st_err);
			if($st_err !== true) {
				$fdata['status'] = '0';
				$fdata['message']=$st_err;
				return $fdata;
			}

			//print_r($eventInfoArray);die;
			$event_golf_course_id = isset($eventInfoArray['event_golf_course_id'])?$eventInfoArray['event_golf_course_id']:"";
			$event_name = isset($eventInfoArray['event_name'])?($eventInfoArray['event_name']):"";
			$no_of_player = isset($eventInfoArray['no_of_player'])?$eventInfoArray['no_of_player']:"1";
			$event_format_id = isset($eventInfoArray['event_format_id'])?$eventInfoArray['event_format_id']:"";
			$event_tee_id=isset($eventInfoArray['event_tee_id'])?$eventInfoArray['event_tee_id']:array();
			$event_tee_id = (count($event_tee_id)>0) ? $this->convertTeeFormat($event_tee_id) : '';
			$event_start_date = isset($eventInfoArray['event_start_date'])?$eventInfoArray['event_start_date']:"";
			$event_start_time = isset($eventInfoArray['event_start_time'])?$eventInfoArray['event_start_time']:"";
			$num_of_holes=isset($eventInfoArray['num_of_holes'])?$eventInfoArray['num_of_holes']:"";
			$event_is_individual = isset($eventInfoArray['event_is_individual'])?$eventInfoArray['event_is_individual']:"1";
			$select_holes=isset($eventInfoArray['select_holes'])?$eventInfoArray['select_holes']:"1";
			$event_admin_id = isset($eventInfoArray['event_admin_id'])?$eventInfoArray['event_admin_id']:"";
			$invited_email_list=isset($eventInfoArray['invited_email_list'])?$eventInfoArray['invited_email_list']:array();
			$invited_group_list=isset($eventInfoArray['event_group_list'])?$eventInfoArray['event_group_list']:array();
			$scorer_friend_list=isset($eventInfoArray['scorer_friend_list'])?$eventInfoArray['scorer_friend_list']:array();
			$event_friend_list=isset($eventInfoArray['event_friend_list'])?$eventInfoArray['event_friend_list']:array();
			$scrorer_listArr=isset($eventInfoArray['scrorer_list'])?$eventInfoArray['scrorer_list']:array();
			$scrorer_list=(count($scrorer_listArr) > 0)?$scrorer_listArr[0]:array();
			//print_r($scrorer_list);die;
			$team_list=isset($eventInfoArray['team_list'])?$eventInfoArray['team_list']:array();
			$temp_golf_courseArr=isset($eventInfoArray['temp_golf_course'])?$eventInfoArray['temp_golf_course']:array();
			$temp_golf_course=(count($temp_golf_courseArr) >0)?$temp_golf_courseArr[0]:array();
			$event_is_public = isset($eventInfoArray['event_is_public'])?$eventInfoArray['event_is_public']:"";
			$event_is_spot = isset($eventInfoArray['event_is_spot'])?$eventInfoArray['event_is_spot']:"0";
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$event_stroke_play_id = isset($eventInfoArray['event_stroke_play_id'])?$eventInfoArray['event_stroke_play_id']:"0";
			$event_end_date = isset($eventInfoArray['event_end_date'])?$eventInfoArray['event_end_date']:"0000-00-00";
			$event_end_time = isset($eventInfoArray['event_end_time'])?$eventInfoArray['event_end_time']:"00:00:00";
			$event_is_handicap = isset($eventInfoArray['event_is_handicap'])?$eventInfoArray['event_is_handicap']:"0";
			$event_is_team = isset($eventInfoArray['event_is_team'])?$eventInfoArray['event_is_team']:"";
			$event_team_num = isset($eventInfoArray['event_team_num'])?$eventInfoArray['event_team_num']:"";
			$event_friend_num = isset($eventInfoArray['event_friend_num'])?$eventInfoArray['event_friend_num']:"";
			$is_singlescreen = isset($eventInfoArray['is_singlescreen'])?$eventInfoArray['is_singlescreen']:"2"; // 1=single, 2=multiscreen
			$closest_pin = $long_drive = $straight_drive = array();
			
			if($event_is_team == 1) {
				$event_is_individual = '0';
			}

			if($event_is_spot == '1') {
				$closest_pin = isset($eventInfoArray['closest_pin'])?$eventInfoArray['closest_pin']:array();
				$long_drive = isset($eventInfoArray['long_drive'])?$eventInfoArray['long_drive']:array();
				$straight_drive = isset($eventInfoArray['straight_drive'])?$eventInfoArray['straight_drive']:array();
			}

			$gnu = 0;

			if($event_name!="" && $event_start_date!="" && $event_start_time!="" && $event_golf_course_id!="" && $event_format_id!="" && $event_is_public!="" && $event_tee_id!="" &&  $event_admin_id!="" && $event_is_individual>="0")
			{
		$sqlQuery ="insert into "._EVENT_TBL_." (";
                $sqlQuery .= " event_name,";
                $sqlQuery .= " golf_course_id,";
				$sqlQuery .= " total_hole_num,";
				$sqlQuery .= " hole_start_from,";
                $sqlQuery .= " format_id,";
                $sqlQuery .= " is_individual,";
                $sqlQuery .= " is_public,";
                $sqlQuery .= " admin_id,";
                $sqlQuery .= " tee_id,";
                $sqlQuery .= " is_handicap,";
                $sqlQuery .= " stroke_play_id,";
                $sqlQuery .= " event_start_date_time,";
                $sqlQuery .= " event_start_time,";
                $sqlQuery .= " event_end_date_time,";
                $sqlQuery .= " event_end_time,";
                $sqlQuery .= " is_active,";
                $sqlQuery .= " created_by,";
                $sqlQuery .= " creation_date,";
                $sqlQuery .= " ip_address,";
                $sqlQuery .= " is_spot,";
				 $sqlQuery .= " is_singlescreen,";
         $sqlQuery .= " no_of_player";
                $sqlQuery .= " ) values (";
                $sqlQuery .= '"'.$this->db->escape($event_name).'",';
                $sqlQuery .= " '".$event_golf_course_id."',";
				$sqlQuery .= " '".$num_of_holes."',";
				$sqlQuery .= " '".$select_holes."',";
                $sqlQuery .= " '".$event_format_id."',";
                $sqlQuery .= " '".$event_is_individual."',";
                $sqlQuery .= " '".$event_is_public."',";
                $sqlQuery .= " '".$event_admin_id."',";
                $sqlQuery .= " '".$event_tee_id."',";
                $sqlQuery .= " '".$event_is_handicap."',";
                $sqlQuery .= " '".$event_stroke_play_id."',";
                $sqlQuery .= " '".$event_start_date."',";
                $sqlQuery .= " '".$event_start_time."',";
                $sqlQuery .= " '".$event_end_date."',";
                $sqlQuery .= " '".$event_end_time."',";
                $sqlQuery .= " 1,";
                $sqlQuery .= " '".$event_admin_id."',";
                $sqlQuery .= " now(),";
                $sqlQuery .= " '".$ip_address."',";
                $sqlQuery .= " '".$event_is_spot."',";
				 $sqlQuery .= " '".$is_singlescreen."',";
              $sqlQuery .= " '".$no_of_player."'";
            $sqlQuery .= " )";
				$insertQuery = $this->db->FetchQuery($sqlQuery);
				$eventId = $this->db->LastInsertId();

if(count($closest_pin)>0){
				 foreach($closest_pin as $i=>$v ){
					$queryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",1,".$v.")";
					$this->db->FetchQuery($queryString);
				}
			}
			if(count($straight_drive)>0){
				 foreach($straight_drive as $i=>$s ){
					$squeryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",2,".$s.")";
					$this->db->FetchQuery($squeryString);
				}
			}
			if(count($long_drive)>0){
				 foreach($long_drive as $i=>$l ){
					$lqueryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",3,".$l.")";
					$this->db->FetchQuery($lqueryString);
				}
			}

				// admin details
				$getadminString1 = "select full_name from ".TABLE_GOLF_USERS." where user_id ='".$event_admin_id."'";
				$admin_name = $this->db->FetchSingleValue($getadminString1);
			 $scorere_id=0;
				/* if(isset($scrorer_list[$event_admin_id]) &&  $scrorer_list[$event_admin_id]>0 && $scrorer_list[$event_admin_id]!=$event_admin_id) {
					$scorere_id = (isset($scrorer_list[$event_admin_id]) && $scrorer_list[$event_admin_id]>0) ? $scrorer_list[$event_admin_id] : 0;
				} */

				if($is_singlescreen == 1) {
			if($event_format_id == 10 || $event_format_id==11 || $event_format_id==12 || $event_format_id==13 || $event_format_id==14){
			$is_accept = '1';
			$accepted_by = $event_admin_id;
			}else{
				$is_accept = '1';
				$accepted_by = $event_admin_id;

			}
	}	else{
		$is_accept = '0';
		$accepted_by = '0';
	}
				$sqlQuery1="insert into "._EVENT_PLAYER_LIST_TBL_." set event_id='".$eventId."',is_accepted='".$is_accept."',accepted_by='".$accepted_by."',event_admin_id='".$event_admin_id."',player_id='".$event_admin_id."',is_new='0',scorere_id='".$event_admin_id."'";
				$this->db->FetchQuery($sqlQuery1);

                if(is_array($invited_email_list) && count($invited_email_list) > 0 && $no_of_player > 1){

					$inviteCounter=1;
					$event_invite_email = array();
					$invitedcounter=0;

					foreach($invited_email_list as $nonregemail){

						$InvEmail = isset($nonregemail["email"]) ? $nonregemail["email"] :'';
						$InvName = isset($nonregemail["name"]) ? $nonregemail["name"] :'';
						$InvHandicap = isset($nonregemail["handicap"]) ? $nonregemail["handicap"] :'';
						$has_team_number = isset($nonregemail["team_number"]) ? $nonregemail["team_number"] :'0';

						if($InvEmail!=""){
							$sqlchk="select user_id,is_new from golf_users where user_name='".$InvEmail."'";
							$userValue = $this->db->FetchRow($sqlchk);


							if(!is_array($userValue) || count($userValue) <= 0) {

								$udata = array('email_id'=>$InvEmail, 'name'=>$InvName , 'handicap'=>$InvHandicap);
								$userId = createUser($udata);
								$eplayer = array('event_id'=>$eventId,'user_id'=>$userId);
								$neweventplayer = newEventPlayer($eplayer);
								$is_new=1;
							}
							else {
								$userId  = (isset($userValue['user_id']) && $userValue['user_id'] >0)?$userValue['user_id']:$userId;

							    $is_new  = (isset($userValue['is_new']) && $userValue['is_new'] <= 0 )?$userValue['is_new']:1;
							}
							$scorere_id = ($is_singlescreen == 1)?$event_admin_id:$userId;
							
							// add in team
							if($has_team_number > 0) {
								$counter_team = (($has_team_number-1) > 0) ? ($has_team_number-1) : 0;
								if(isset($eventInfoArray['team_list'][$counter_team]['event_friend_list'][0]) && is_array($eventInfoArray['team_list'][$counter_team]['event_friend_list'][0]) && count($eventInfoArray['team_list'][$counter_team]['event_friend_list'][0])>=0) {
									$cnt = count($eventInfoArray['team_list'][$counter_team]['event_friend_list'][0]);
									$cnt++;
									$eventInfoArray['team_list'][$counter_team]['event_friend_list'][0]["friend_id_{$cnt}_t"] = $userId;
								}
							}
							
							/* if(isset($scrorer_list[$userId]) &&  $scrorer_list[$userId]>0 && $scrorer_list[$userId]!=$userId) {
								$scorere_id = $scrorer_list[$userId];
							} */
							$allready_invite_chk1="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$userId."'";
							$allready_invite_chk_res1 = $this->db->FetchSingleValue($allready_invite_chk1);
							if($allready_invite_chk_res1 ==0 ){
							$sqlQuery1="insert into "._EVENT_PLAYER_LIST_TBL_." set temp_handicap='".$InvHandicap."',event_id='".$eventId."',is_accepted='".$is_accept."',accepted_by='".$accepted_by."',event_admin_id='".$event_admin_id."',player_id='".$userId."',is_new='".$is_new."',scorere_id='".$scorere_id."'";
							$this->db->FetchQuery($sqlQuery1);
							$event_invite_email[] = $InvEmail;
							}
						}

						$inviteCounter++;
					}
/*
					 if(count($event_invite_email)>0) {
						sendEventInviteMail($event_invite_email,$eventId);
					} */
				}

				if($no_of_player > 1 && count($invited_group_list) > 0){
					$ginviteCount=1;

					foreach($eventInfoArray['event_group_list'] as $invited_group)
                    {

                        $InvGroup = $invited_group["group"];
						if($InvGroup!="" && $InvGroup>0){
							//Check user if not registered then invitation mail send to email id
							$grpsql='select gm.user_id,gu.user_name from '._GROUP_MEMBER_LIST_.' as gm inner join '.TABLE_GOLF_USERS.' as gu on gu.user_id=gm.user_id where gm.group_id="'.$this->db->escape($InvGroup).'"';
							$grpsqlresult = $this->db->FetchQuery($grpsql);

							if(count($grpsqlresult) > 0){
								foreach($grpsqlresult as $i=>$rowValues6){
									$userId=$rowValues6['user_id'];
									$allready_invite_chk="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$rowValues6['user_id']."'";
									$allready_invite_chk_res = $this->db->FetchSingleValue($allready_invite_chk);
									if($allready_invite_chk_res ==0 ){
										$scorere_id=$userId;
										/* if(isset($scrorer_list[$userId]) &&  $scrorer_list[$userId]>0 && $scrorer_list[$userId]!=$userId) {
											$scorere_id = (isset($scrorer_list[$userId]) && $scrorer_list[$userId]>0) ? $scrorer_list[$userId] : 0;
										} */
										$sqlQuery1="insert into "._EVENT_PLAYER_LIST_TBL_." set group_id='".$InvGroup."',event_id='".$eventId."',event_admin_id='".$event_admin_id."',player_id='".$userId."',is_new='1',is_accepted='".$is_accept."',accepted_by='".$accepted_by."',scorere_id='".$scorere_id."'";
										$setEvn = $this->db->FetchQuery($sqlQuery1);
$admin_name = getUserNameById($event_admin_id);
$user_name = getUserNameById($userId);
$emails = getEmailById($userId);
										if($event_admin_id!=$userId){
											$notif_mth = new createNotification();
											$notif_mth->generatePushNotification($eventId,3,0,$userId);
											$subject="Invitation Received - You have received an event invitation";
											$message="Dear User,<br><br>You have received Putt2gether event invitation from ".$admin_name.".
									To view details download Putt2gether App from AppStore:.<br><br>Best Regards<br>Team Putt2gether!";
									sendmail($emails,$user_name, $subject, $message);	
											
										}
										// sachin 10-8-15
									}
								}
							}
						}

						$ginviteCount++;

					}
				}

			/*	if(count($temp_golf_course) > 0){
					$golf = new Golf ;
					$temp_golf_course['event_id'] = $eventId;
					$temp_golf_course['created_by'] = $event_admin_id;
					$newGolfId = $golf->createTemporaryGolfCourse($temp_golf_course);
					$event_golf_course_id = $newGolfId;
					if($event_golf_course_id>0) {
						$ssql = "update event_table set golf_course_id='{$event_golf_course_id}' where event_id='{$eventId}'";
						$this->db->FetchQuery($ssql);
					}
				} */

				date_default_timezone_set('asia/calcutta');
                if($event_is_team == 0)
                {
                    $event_friend_num = $event_friend_num;
                    $friendCounter = 1;

					if(count($event_friend_list)>0){
					$i  = 1;
						foreach($event_friend_list as $particiantId){
							$friendUserId = "friend_id";

							if(!isset($particiantId[$friendUserId]) || $particiantId[$friendUserId]<=0) {
								continue;
							}
							 $userId = $particiantId[$friendUserId];
							$sql_utest="select creation_date from ".TABLE_GOLF_USERS." where user_id='".$particiantId[$friendUserId]."'";
							$rowValuesUtest = $this->db->FetchSingleValue($sql_utest);

							$user_create_time=strtotime($rowValuesUtest);
							$is_new = 0;
							$sec_diff = abs(time()-$user_create_time);
							$min_diff = round($sec_diff / 60);
							if($min_diff<=10) {$is_new=1;}
							$scorere_id = ($is_singlescreen == 1)?$event_admin_id:$particiantId[$friendUserId];

							/*  if(isset($scrorer_list[$userId]) &&  $scrorer_list[$userId]>0 && $scrorer_list[$userId]!=$userId) {
								$scorere_id = (isset($scrorer_list[$userId]) && $scrorer_list[$userId]>0) ? $scrorer_list[$userId] : 0;
							}	 */
							$allready_invite_chk2="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$particiantId[$friendUserId]."'";
							$allready_invite_chk_res2 = $this->db->FetchSingleValue($allready_invite_chk2);
							if($allready_invite_chk_res2 ==0 ){
							$sqlQuery = "insert into "._EVENT_PLAYER_LIST_TBL_." (";
							$sqlQuery .= " event_id,";
							$sqlQuery .= " event_admin_id,";
							$sqlQuery .= " player_id,";
							$sqlQuery .= " is_individual,";
							$sqlQuery .= " creation_date,";
							$sqlQuery .= " ip_address,";
							$sqlQuery .= " is_new,";
							$sqlQuery .= " is_accepted,";
							$sqlQuery .= " accepted_by,";
							$sqlQuery .= " scorere_id";
							$sqlQuery .= " ) values (";
							$sqlQuery .= $eventId.", ";
							$sqlQuery .= " '".$event_admin_id."',";
							$sqlQuery .= " '".$particiantId[$friendUserId]."',";
							$sqlQuery .= " '".$event_is_individual."',";
							$sqlQuery .= " now(),";
							$sqlQuery .= " '".$ip_address."',";
							$sqlQuery .= " '".$is_new."',";
							$sqlQuery .= " '".$is_accept."',";
							$sqlQuery .= " '".$accepted_by."',";
							$sqlQuery .= " '".$scorere_id."'";
							$sqlQuery .= " )";
							$sql12 = $this->db->FetchQuery($sqlQuery);

							$friendCounter++;
							$notif_mth = new createNotification();
							$notif_mth->generatePushNotification($eventId,3,0,$particiantId[$friendUserId]);
							}
						$i++ ;
$event_friend_email = getEmailById($particiantId['friend_id']);
						sendEventInviteMail($event_friend_email,$eventId);
					
						}
					}
                }
				else{
					 if($event_is_team == 1){
						$teamCounter = 1;
						$counter = 0;
						if(count($team_list)>0){
						foreach( $team_list as $teamNameId){
							$teamNameString ="team_name_".$teamCounter;
							 $sqlQuery = "insert into  team_profile (";
							 $sqlQuery .= " event_id,";
							 $sqlQuery .= " team_admin_id,";
							 $sqlQuery .= " team_display_name,";
							 $sqlQuery .= " is_active,";
							 $sqlQuery .= " created_by ,";
							 $sqlQuery .= " creation_date,";
							 $sqlQuery .= " ip_address";
							 $sqlQuery .= " ) values (";
							 $sqlQuery .= $eventId.", ";
							 $sqlQuery .= " '".$event_admin_id."',";
							 $sqlQuery .= " '".$this->db->escape($teamNameId[$teamNameString])."',";
							 $sqlQuery .= " 1,";
							 $sqlQuery .= " '".$event_admin_id."',";
							 $sqlQuery .= " now(),";
							 $sqlQuery .= " '".$ip_address."'";
							 $sqlQuery .= " )";
							 $sqlQue = $this->db->FetchQuery($sqlQuery);
							 $teamId = $this->db->LastInsertId();

							$friendCounter = 1;
							$event_friend_num = $eventInfoArray['team_list'][$counter]['event_friend_num'];

							foreach( $eventInfoArray['team_list'][$counter]['event_friend_list'][0] as $particiantId)
							{
								$friendUserId = "friend_id_".$friendCounter;
								//$player_id=isset($particiantId[$friendUserId])?$particiantId[$friendUserId]:"0";
								$player_id=$particiantId;
//echo "<br>".$player_id." -team".$teamId;
								if($player_id<=0) {continue;}
								$sql_utest="select creation_date from golf_users where user_id='".$player_id."' ";
								 $rowValuesUtest = $this->db->FetchSingleValue($sql_utest);

								$user_create_time=strtotime($rowValuesUtest);
								$is_new = 0;
								$sec_diff = abs(time()-$user_create_time);
								$min_diff = round($sec_diff / 60);
								if($min_diff<=10) {$is_new=1;}
								$scorere_id = ($is_singlescreen == 1)?$event_admin_id:$player_id;
								/* if(isset($scrorer_list[$player_id]) &&  $scrorer_list[$player_id]>0 && $scrorer_list[$player_id]!=$player_id) {
									$scorere_id = $scrorer_list[$player_id];
								} */
								$allready_invite_chk3="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$player_id."'";
								$allready_invite_chk_res3 = $this->db->FetchSingleValue($allready_invite_chk3);
								if($allready_invite_chk_res3 ==0 ){
								$sqlQuery = "insert into event_player_list (";
								$sqlQuery .= " event_id,";
								$sqlQuery .= " event_admin_id,";
								$sqlQuery .= " player_id,";
								$sqlQuery .= " is_individual,";
								$sqlQuery .= " team_id,";
								$sqlQuery .= " creation_date,";
								$sqlQuery .= " ip_address,";
								$sqlQuery .= " is_accepted,";
								$sqlQuery .= " accepted_by,";
								$sqlQuery .= " is_new,";
								$sqlQuery .= " scorere_id";
								$sqlQuery .= " ) values (";
								$sqlQuery .= $eventId.", ";
								$sqlQuery .= " '".$event_admin_id."',";
								$sqlQuery .= " ".$player_id.",";
								$sqlQuery .= " '".$event_is_individual."',";
								$sqlQuery .= " '".$teamId."',";
								$sqlQuery .= " now(),";
								$sqlQuery .= " '".$ip_address."',";
								$sqlQuery .= " '".$is_accept."',";
								$sqlQuery .= " '".$accepted_by."',";
								$sqlQuery .= " '".$is_new."',";
								$sqlQuery .= " '".$scorere_id."'";
								$sqlQuery .= " )";
								$rowVal = $this->db->FetchQuery($sqlQuery);
								$notif_mth = new createNotification();
								$notif_mth->generatePushNotification($eventId,3,0,$player_id);
								$friendCounter++;
								}else{
$sql="update event_player_list set  team_id='".$teamId."' where event_list_id ='".$allready_invite_chk_res3."'";
$this->db->FetchQuery($sql);
                                                                }
							}
							$teamCounter++;
							$counter++;
						}
						}
					  }else{}
				    }

					$this->randomHoleSelectionForPeoria($eventId,$event_golf_course_id,$num_of_holes);
					$this->randomHoleSelectionForDoublePeoria($eventId,$event_golf_course_id,$num_of_holes);
     				$sdate=date("m/d/Y",strtotime($event_start_date));
					
					$sql_utest="select count(player_id) as c from event_player_list where event_id='".$eventId."'";
					$no_of_player = $this->db->FetchSingleValue($sql_utest);
					
					 $message="Your Event ".$event_name." on ".$sdate." for ".$no_of_player." participants has been created!";
					 $queryString = " select  ";
								$queryString .= " event_id, ";
								$queryString .= " admin_id, ";
								$queryString .= " golf_course_name,";
								$queryString .= " event_name,";
								$queryString .= " date(event_start_date_time) as event_date,";
								$queryString .= " event_start_time,";
								$queryString .= " total_hole_num,";
								$queryString .= " hole_start_from,";
								$queryString .= " format_name,";
								$queryString .= " stoke_name,";
								$queryString .= " tee_name, ";
								$queryString .= " is_started as game_status";
								$queryString .= " from event_list_view";
								$queryString .= " where event_id='".$eventId."'";

								$rowValues = $this->db->FetchRow($queryString);

								$queryString1 = "select full_name from golf_users where user_id ='".$rowValues['admin_id']."' ";
								$full_name = $this->db->FetchSingleValue($queryString1);

								$rowValues['admin_name']=$full_name;
								$rowValues['tee_name']=getEventTee($rowValues['tee_name']);

								//start auto accept event by admin

								$userId=$event_admin_id;
								if($eventId!="" && $userId!=""){

									$error='';
									$isPlayerExist = 0;
									$queryString3 = "select is_accepted from "._EVENT_PLAYER_LIST_TBL_." where event_id ='".$eventId."' and player_id ='".$userId."'";
									$is_accepted = $this->db->FetchSingleValue($queryString3);

									$sql1="select event_name,admin_id,DATE(event_start_date_time) as event_start_date,is_started from "._EVENT_TBL_." where event_id='".$eventId."' ";
									$result1 = $this->db->FetchRow($sql1);
									$event_name = $result1['event_name'];
									$admin_id = $result1['admin_id'];
									$event_start_date = $result1['event_start_date'];
									$is_started = $result1['is_started'];
									$status="1";

									if($status=="1"){
										if($is_started=="3")
											{
												$this->startEventForPlayer($eventId,$userId);
											}

										$sql2="update event_player_list set is_accepted='1',accepted_by='".$userId."',accepted_date=now()  where event_id ='".$eventId."' and player_id ='".$userId."'";
										$updSql = $this->db->FetchQuery($sql2);

									/*	 $queryString2 = " select  ";
										$queryString2 .= " event_id, ";
										$queryString2 .= " admin_id, ";
										$queryString2 .= " golf_course_name,";
										$queryString2 .= " event_name,";
										$queryString2 .= " date(event_start_date_time) as event_date,";
										$queryString2 .= " event_start_time,";
										$queryString2 .= " total_hole_num,";
										$queryString2 .= " format_name,";
										$queryString2 .= " stoke_name,";
										$queryString2 .= " tee_name, ";
										$queryString2 .= " is_started as game_status";
										$queryString2 .= " from event_list_view";
									$queryString2 .= " where event_id='".$eventId."'"; die;
										$rowValues3 = $this->db->FetchRow($queryString2);
								*/
										 $queryString5 = "select full_name from ".TABLE_GOLF_USERS." where user_id ='".$event_admin_id."'";
										$full_name = $this->db->FetchSingleValue($queryString5);
										$rowValues['admin_name']=$full_name;
									}
								}

								$eventArr=array("event_id"=>$eventId,"message"=>$message,"data"=>$rowValues);

								$fdata['status'] = '1';
								$fdata['Event'] = $eventArr;
								$fdata['message']="Event List";


			}else{
					$fdata['status'] = '0';
					$fdata['message']="Required field not found";
				}
				return $fdata ;
		}
		
		function addParticipantInEvent($data){
			$fdata = array();
			$eventInfoArray =  $data ;
			//print_r($data);die;
			

			//print_r($eventInfoArray);die;
			$event_id = isset($eventInfoArray['event_id'])?$eventInfoArray['event_id']:"0";
			$no_of_player = isset($eventInfoArray['no_of_player'])?$eventInfoArray['no_of_player']:"1";
			$event_admin_id = isset($eventInfoArray['event_admin_id'])?$eventInfoArray['event_admin_id']:"";
			$invited_email_list=isset($eventInfoArray['invited_email_list'])?$eventInfoArray['invited_email_list']:array();
			$invited_group_list=isset($eventInfoArray['event_group_list'])?$eventInfoArray['event_group_list']:array();
			$event_friend_list=isset($eventInfoArray['event_friend_list'])?$eventInfoArray['event_friend_list']:array();
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$event_friend_num = isset($eventInfoArray['event_friend_num'])?$eventInfoArray['event_friend_num']:"";
			
			

			$gnu = 0;

			if($event_id>"0") {
				$eventId = $event_id;
				// admin details
				$getadminString1 = "select full_name from ".TABLE_GOLF_USERS." where user_id ='".$event_admin_id."'";
				$admin_name = $this->db->FetchSingleValue($getadminString1);
				$scorere_id=0;
				
				$getadminString1 = "select * from event_table where event_id ='".$eventId."'";
				$event_data = $this->db->FetchRow($getadminString1);
				
				$is_singlescreen = 0;
				
				$event_is_individual = $event_data['is_individual'];
				
				if(isset($event_data['is_singlescreen']) && $event_data['is_singlescreen'] == 1) {
					$is_singlescreen = 1;
				}
				
				if($is_singlescreen == 1) {
					$is_accept = '1';
					$accepted_by = $event_admin_id;
				}
				else {
					$is_accept = '0';
					$accepted_by = '0';
				}
				
				if(is_array($invited_email_list) && count($invited_email_list) > 0){

					$inviteCounter=1;
					$event_invite_email = array();
					$invitedcounter=0;

					foreach($invited_email_list as $nonregemail){

						$InvEmail = isset($nonregemail["email"]) ? $nonregemail["email"] :'';
						$InvName = isset($nonregemail["name"]) ? $nonregemail["name"] :'';
						$InvHandicap = isset($nonregemail["handicap"]) ? $nonregemail["handicap"] :'';

						if($InvEmail!=""){
							$sqlchk="select user_id,is_new from golf_users where user_name='".$InvEmail."'";
							$userValue = $this->db->FetchRow($sqlchk);


							if(!is_array($userValue) || count($userValue) <= 0) {
								$udata = array('email_id'=>$InvEmail, 'name'=>$InvName , 'handicap'=>$InvHandicap);
								$userId = createUser($udata);
								$eplayer = array('event_id'=>$eventId,'user_id'=>$userId);
								$neweventplayer = newEventPlayer($eplayer);
								$is_new=1;
							}
							else {
								$userId  = (isset($userValue['user_id']) && $userValue['user_id'] >0)?$userValue['user_id']:$userId;
								$is_new  = (isset($userValue['is_new']) && $userValue['is_new'] <= 0 )?$userValue['is_new']:1;
							}
							$scorere_id = ($is_singlescreen == 1)?$event_admin_id:$userId;
							
							$allready_invite_chk1="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$userId."'";
							$allready_invite_chk_res1 = $this->db->FetchSingleValue($allready_invite_chk1);
							
							if($allready_invite_chk_res1 ==0 ){
								$sqlQuery1="insert into "._EVENT_PLAYER_LIST_TBL_." set event_id='".$eventId."',is_accepted='".$is_accept."',accepted_by='".$accepted_by."',event_admin_id='".$event_admin_id."',player_id='".$userId."',is_new='".$is_new."',scorere_id='".$scorere_id."'";
								$this->db->FetchQuery($sqlQuery1);
								$event_invite_email[] = $InvEmail;
							}
						}
						$inviteCounter++;
					}
				}

				if(count($invited_group_list) > 0){
					$ginviteCount=1;

					foreach($eventInfoArray['event_group_list'] as $invited_group)
                    {

                        $InvGroup = $invited_group["group"];
						if($InvGroup!="" && $InvGroup>0){
							//Check user if not registered then invitation mail send to email id
							$grpsql='select gm.user_id,gu.user_name,gu.display_name from '._GROUP_MEMBER_LIST_.' as gm inner join '.TABLE_GOLF_USERS.' as gu on gu.user_id=gm.user_id where gm.group_id="'.$this->db->escape($InvGroup).'"';
							$grpsqlresult = $this->db->FetchQuery($grpsql);

							if(count($grpsqlresult) > 0){
								foreach($grpsqlresult as $i=>$rowValues6){
									$userId=$rowValues6['user_id'];
									$allready_invite_chk="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$rowValues6['user_id']."'";
									$allready_invite_chk_res = $this->db->FetchSingleValue($allready_invite_chk);
									if($allready_invite_chk_res ==0 ){
										$scorere_id=($is_singlescreen == 1)?$event_admin_id:$userId;
										$sqlQuery1="insert into "._EVENT_PLAYER_LIST_TBL_." set group_id='".$InvGroup."',event_id='".$eventId."',event_admin_id='".$event_admin_id."',player_id='".$userId."',is_new='1',is_accepted='".$is_accept."',accepted_by='".$accepted_by."',scorere_id='".$scorere_id."'";
										$setEvn = $this->db->FetchQuery($sqlQuery1);
										
										$user_name = $rowValues6['display_name'];
										$emails = $rowValues6['user_name'];
										if($event_admin_id!=$userId){
											$subject="Invitation Received - You have received an event invitation";
											$message="Dear User,<br><br>You have received Putt2gether event invitation from ".$admin_name.".
											To view details download Putt2gether App from AppStore:.<br><br>Best Regards<br>Team Putt2gether!";
											sendmail($emails,$user_name, $subject, $message);
										}
										// sachin 10-8-15
									}
								}
							}
						}
						$ginviteCount++;
					}
				}

			

				date_default_timezone_set('asia/calcutta');
                
                    $event_friend_num = $event_friend_num;
                    $friendCounter = 1;

					if(count($event_friend_list)>0){
					$i  = 1;
						foreach($event_friend_list as $particiantId){
							$friendUserId = "friend_id";

							if(!isset($particiantId[$friendUserId]) || $particiantId[$friendUserId]<=0) {
								continue;
							}
							 $userId = $particiantId[$friendUserId];
							$sql_utest="select creation_date from ".TABLE_GOLF_USERS." where user_id='".$particiantId[$friendUserId]."'";
							$rowValuesUtest = $this->db->FetchSingleValue($sql_utest);

							$user_create_time=strtotime($rowValuesUtest);
							$is_new = 0;
							$sec_diff = abs(time()-$user_create_time);
							$min_diff = round($sec_diff / 60);
							if($min_diff<=10) {$is_new=1;}
							$scorere_id = ($is_singlescreen == 1)?$event_admin_id:$particiantId[$friendUserId];

							
							$allready_invite_chk2="select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where event_id='".$eventId."' and  player_id='".$particiantId[$friendUserId]."'";
							$allready_invite_chk_res2 = $this->db->FetchSingleValue($allready_invite_chk2);
							if($allready_invite_chk_res2 ==0 ){
							$sqlQuery = "insert into "._EVENT_PLAYER_LIST_TBL_." (";
							$sqlQuery .= " event_id,";
							$sqlQuery .= " event_admin_id,";
							$sqlQuery .= " player_id,";
							$sqlQuery .= " is_individual,";
							$sqlQuery .= " creation_date,";
							$sqlQuery .= " ip_address,";
							$sqlQuery .= " is_new,";
							$sqlQuery .= " is_accepted,";
							$sqlQuery .= " accepted_by,";
							$sqlQuery .= " scorere_id";
							$sqlQuery .= " ) values (";
							$sqlQuery .= $eventId.", ";
							$sqlQuery .= " '".$event_admin_id."',";
							$sqlQuery .= " '".$particiantId[$friendUserId]."',";
							$sqlQuery .= " '".$event_is_individual."',";
							$sqlQuery .= " now(),";
							$sqlQuery .= " '".$ip_address."',";
							$sqlQuery .= " '".$is_new."',";
							$sqlQuery .= " '".$is_accept."',";
							$sqlQuery .= " '".$accepted_by."',";
							$sqlQuery .= " '".$scorere_id."'";
							$sqlQuery .= " )";
							$sql12 = $this->db->FetchQuery($sqlQuery);

							$friendCounter++;
							$notif_mth = new createNotification();
							$notif_mth->generatePushNotification($eventId,3,0,$particiantId[$friendUserId]);
							}
						$i++ ;
						$event_friend_email = getEmailById($particiantId['friend_id']);
						sendEventInviteMail($event_friend_email,$eventId);
					
						}
					}
					
					$sdate=date("m/d/Y",strtotime($event_start_date));
					
					$sql_utest="select count(player_id) as c from event_player_list where event_id='".$eventId."'";
					$no_of_player = $this->db->FetchSingleValue($sql_utest);
					
					 $message="Your Event ".$event_name." on ".$sdate." for ".$no_of_player." participants has been updated!";
					 
								$rowValues = array();

								
								$full_name = $admin_name;

								$rowValues['admin_name']=$full_name;

								//start auto accept event by admin

								$userId=$event_admin_id;
								

								$eventArr=array("event_id"=>$eventId,"message"=>$message,"data"=>$rowValues);

								$fdata['status'] = '1';
								$fdata['Event'] = $eventArr;
								$fdata['message']="Event List";


			}else{
					$fdata['status'] = '0';
					$fdata['message']="Required field not found";
				}
				return $fdata ;
		}
		

function randomHoleSelectionForPeoria($eventId,$golf_course_id,$total_hole_num)
		{
				$randomArray1 = array(1,2,3,4,5,6,7,8,9);
				$randomArray2 = array(10,11,12,13,14,15,16,17,18);
				$randomArray = array();
				$count = 0;
				do
				{
					$randValue = rand(0,8);
					if(in_array($randomArray1[$randValue],$randomArray))
					{
						continue;
					}
					else
					{
						$randomArray[]=$randomArray1[$randValue];
						$count++;
					}
				}while($count<3);
				$count = 0;
				do
				{
					$randValue = rand(0,8);
					if(in_array($randomArray2[$randValue],$randomArray))
					{
						continue;
					}
					else
					{
						$randomArray[]=$randomArray2[$randValue];
						$count++;
					}
				}while($count<3);

				$sql="insert into event_par_peoria set prio_t = 1,golf_course_id='".$golf_course_id."',event_id='".$eventId."',num_hole='".$total_hole_num."'";
					$insData = $this->db->FetchQuery($sql);


				$queryString = "update event_par_peoria set ";
				for ($ctr = 0; $ctr < 6;$ctr++)
				{
					//echo $randomArray[$ctr]."<br>";
					if($ctr >0 && $ctr<6)
					{
							$queryString .= ",";
					}
					$queryString .= "is_used_in_peoria_".$randomArray[$ctr]."= 1";

					$queryParString =" select ";
					$queryParString .=" par_value_".$randomArray[$ctr]." as par_value";
					$queryParString .="  from golf_hole_index";
					$queryParString .=" where golf_course_id = ".$golf_course_id;
					$par_value = $this->db->FetchSingleValue($queryParString);

					$queryString1 = " insert into prioria_calc_tab(prio_t,event_id,hole_num,par_value) values(1,".$eventId.",".$randomArray[$ctr].",".$par_value.")";
 $this->db->FetchQuery($queryString1);

				}
				$queryString .= " where prio_t = 1 and golf_course_id='".$golf_course_id."' and event_id='".$eventId."'";
				return $this->db->FetchQuery($queryString);

		}

		function randomHoleSelectionForDoublePeoria($eventId,$golf_course_id,$total_hole_num)
		{
				$randomArray1 = array(1,2,3,4,5,6,7,8,9);
				$randomArray2 = array(10,11,12,13,14,15,16,17,18);
				$randomArray = array();
				$count = 0;
				do
				{
					$randValue = rand(0,8);
					if(in_array($randomArray1[$randValue],$randomArray))
					{

						continue;
					}
					else
					{
						$randomArray[]=$randomArray1[$randValue];
						$count++;
					}
				}while($count<6);
				$count = 0;
				do
				{
					$randValue = rand(0,8);
					if(in_array($randomArray2[$randValue],$randomArray))
					{
						continue;
					}
					else
					{
						$randomArray[]=$randomArray2[$randValue];
						$count++;
					}
				}while($count<6);

				$sql="insert into event_par_peoria set prio_t = 2,golf_course_id='".$golf_course_id."',event_id='".$eventId."',num_hole='".$total_hole_num."'";

					$this->db->FetchQuery($sql);

				$queryString = "update event_par_peoria set ";
				for ($ctr = 0; $ctr < 12;$ctr++)
				{
					//echo $randomArray[$ctr]."<br>";
					if($ctr >0 && $ctr<12)
					{
							$queryString .= ",";
					}
					$queryString .= "is_used_in_peoria_".$randomArray[$ctr]."= 1";

					$queryParString =" select ";
					$queryParString .=" par_value_".$randomArray[$ctr]." as par_value";
					$queryParString .="  from golf_hole_index";
					$queryParString .=" where golf_course_id = ".$golf_course_id;


					$par_value = $this->db->FetchSingleValue($queryParString);

					$queryString1 = " insert into prioria_calc_tab(prio_t,event_id,hole_num,par_value) values(2,".$eventId.",".$randomArray[$ctr].",".$par_value.")";
					$this->db->FetchQuery($queryString1);

				}
				$queryString .= " where prio_t = 2 and golf_course_id='".$golf_course_id."' and event_id='".$eventId."'";
				return $this->db->FetchQuery($queryString);

		}
		 function startEventForPlayer($eventId,$player_id)
            {

                $startArray = array();

				$queryString = " select golf_course_id,event_name, is_handicap,format_id,is_started";
				$queryString .= " from event_table";
				$queryString .= " where event_id =".$eventId;
				$result = $this->db->FetchRow($queryString);
				$golf_course_id = $result['golf_course_id'];
				$is_handicap = $result['is_handicap'];
				$event_name = $result['event_name'];
				$stroke_play_id = (isset($result['stroke_play_id']) && $result['stroke_play_id'] >0)?$result['stroke_play_id']:0;
				$is_started = $result['is_started'];

				$error=array();
				if($is_started=="4")
				{
					$error[]='Event closed.';
				}
				if($is_started=="2")
				{
					$error[]='Event deleted.';
				}
				$queryString = " select num_hole ";
				$queryString .= " from golf_hole_index";
				$queryString .= " where golf_course_id =".$golf_course_id;
				$queryResult1 = $this->db->FetchSingleValue($queryString);
				if($queryResult1==0)
				{
					$error[]='Golf Course must have par value & index value.';
				}

				$playerHandicap=0;
				$queryString = "select handicap_value from golf_course_user_handicap where  handicap_value > 0 ";
				$queryString .= " and event_id = ".$eventId;
				$queryString .= " and participant_id='".$player_id."'";

				$handicap_value = $this->db->FetchSingleValue($queryString);

				if($handicap_value > 0)
				{
					$handicap_value ;
				}
				else
				{
					$sqlA="select self_handicap from user_profile where user_id='".$player_id."' ";
					$hvalue = $this->db->FetchSingleValue($sqlA);

					$handicap_value=isset($hvalue)?$hvalue:"";
					if($handicap_value > 0)
					{

						$queryString = "insert into golf_course_user_handicap(";
						$queryString .= " event_id, golf_course_id,participant_id, handicap_value,ip_address)";
						$queryString .= " values (";
						$queryString .= "'".trim($eventId)."',";
						$queryString .= "'".trim($golf_course_id)."',";
						$queryString .= $player_id.",";
						$queryString .= "'".$handicap_value."',";
						$queryString .= "'".$_SERVER['REMOTE_ADDR'];
						$queryString .="')";
						$this->db->FetchQuery($queryString);

					}
					else
					{
						$playerHandicap++;
					}
				}

				$queryString = "select event_score_calc_id from event_score_calc where event_score_calc.event_id = ";
				$queryString .= $eventId;
				$queryString .= " and player_id =";
				$queryString .= $player_id;
				$event_score_calc_id = $this->db->FetchSingleValue($queryString);

				if(empty($event_score_calc_id))
				{
			$queryString = " insert into event_score_calc(event_id,player_id,current_position) values(".$eventId.",".$player_id.",999)";
					$queryResult = $this->db->FetchQuery($queryString);
					$event_score_calc_id = $this->db->LastInsertId();


 					$queryString = " select format_id from event_table where event_table.event_id = ".$eventId;
					$formatid = $this->db->FetchSingleValue($queryString);

					$queryString = " update event_score_calc set format_id= ".$formatid.",";
					$queryString .= " handicap_value =". $handicap_value.",";
					$queryString .= " handicap_value_3_4=calculatehandicap3_4(".$handicap_value.",.75)";
					$queryString .= " where event_score_calc_id =".$event_score_calc_id;
					$queryResult = $this->db->FetchQuery($queryString);

				}
				$queryString = "select temp_event_score_id from temp_event_score_entry where event_id = ";
				$queryString .= $eventId;
				$queryString .= " and player_id =";
				$queryString .= $player_id;
				$temp_event_score_id = $this->db->FetchSingleValue($queryString);

				if(empty($temp_event_score_id))
				{
					$queryString = " select * FROM event_score_calc where ";
					$queryString .= " event_id=".$eventId;
					 $queryString .= " and player_id=".$player_id;
					 $queryResult = $this->db->FetchQuery($queryString);

				}
        }


public function showholenumbers($filter=array()){
		$fdata = $error = array();
		$data = $filter;
		$golf_course_id = isset($data['golf_course_id']) ? trim($data['golf_course_id']) : "";
		$option = isset($data['option']) ? trim($data['option']) : "";
		if($golf_course_id==""){
			$error[]="Golf Course Id Is Missing.";
		}
		if($option!=""){
			if($option<1 || $option>2){
				$error[]="In Valid Action.";
			}
			else{
				switch($option){
			    case 1: { $from=1; $limit=9;} break; //for first nine
			    case 2: { $from=10; $limit=18;} break; //for last nine
			  }
			}
		}
		else{
			$from=1; $limit=18;
		}
		if(count($error)==0){
			$queryString="select * from "._GOLF_HOLE_INDEX_." where golf_course_id='".$golf_course_id."'";
			$golf_index_data=$this->db->FetchQuery($queryString);
			if(is_array($golf_index_data) && count($golf_index_data)==1 && isset($golf_index_data[0])){
				$golf_index_data=$golf_index_data[0];
				$par_value_data=array();
				$array_3=array();
				$array_4n5=array();
				for($i=$from;$i<=$limit;$i++){
					if($golf_index_data["par_value_".$i]!="" && $golf_index_data["par_value_".$i]!=0){
						$par_value_data[$i]=$golf_index_data["par_value_".$i];
					}
				}

				if(count($par_value_data)>0){
					foreach($par_value_data as $key=>$value){
							if(trim($value)==3){
							$array_3[]['hole'] = strval($key);
						}
						elseif(trim($value)==4 || trim($value)==5){
							$array_4n5[]['hole'] = strval($key);
						}
						$data=array("par3_holes"=>$array_3,"par4n5_holes"=>$array_4n5);
						$fdata = array();
						$fdata['status'] = '1';
						$fdata['data']=$data;
						//$fdata=$data;
					}
				}
				else{
					$error[]="Array Is Blank.";
				  $fdata['status'] = '0';
				  $fdata['data'] = implode(' ',$error);
				}
			}
			else{
				$error[]="Data Not Found.";
			  $fdata['status'] = '0';
			  $fdata['data']['message'] = implode(' ',$error);
			}
	 }
	 else{
		 $fdata['status'] = '0';
		 $fdata['data']['message'] = implode(' ',$error);
	 }
	 return $fdata;
	}

function  getStatsPiChartForScorer($data){

		$fdata =array();
		$event_id =   (isset($data['event_id']) && $data['event_id'] >0)?$data['event_id']:0;
		$player_id =   (isset($data['user_id']) && $data['user_id'] >0)?$data['user_id']:0;

		$event_id = $event_id;
		$player_id = $player_id;
		$sum_par3 = 0;
		$sum_par4 = 0;
		$sum_par5 = 0;
		$sum_first9 = 0;
		$sum_last9 = 0;
		$sum_par_first9 = 0;
		$sum_par_last9 = 0;

		$count_par3 = 0;
		$count_par4 = 0;
		$count_par5 = 0;
		$no_of_eagle  = 0;
		$no_of_birdies  = 0;
		$no_of_pars  = 0;
		$no_of_bogeys  = 0;
		$no_of_double_bogeys  = 0;
		$score_array = array();
		$total_sum = 0;


		$queryString = " select e.golf_course_id,e.is_started, e.event_start_date_time, e.total_hole_num, e.hole_start_from,f.is_submit_score from "._EVENT_TBL_." e LEFT JOIN "._EVENT_PLAYER_LIST_TBL_." f ON f.event_id=e.event_id where e.event_id =".$event_id." and player_id =".$player_id;

		$rowValues  = $this->db->FetchRow($queryString);

		$golf_course_id = (isset($rowValues['golf_course_id']) && $rowValues['golf_course_id'] >0)?$rowValues['golf_course_id']:0;

		$is_started = (isset($rowValues['is_started']) && $rowValues['is_started'] >0)?$rowValues['is_started']:0;
		$event_start_date_time = (isset($rowValues['event_start_date_time']) && $rowValues['event_start_date_time']!= '')?$rowValues['event_start_date_time']:'';
		$total_hole_num = (isset($rowValues['total_hole_num']) && $rowValues['total_hole_num'] >0)?$rowValues['total_hole_num']:0;
		$hole_start_from = (isset($rowValues['hole_start_from']) && $rowValues['hole_start_from'] >0)?$rowValues['hole_start_from']:0;
		$is_started = (isset($rowValues['is_submit_score']) && $rowValues['is_submit_score'] >0)?$rowValues['is_submit_score']:0;
		$col_span =$total_hole_num;

		if($total_hole_num==9 && $hole_start_from==10) {
			$total_hole_num=18;
		}

		if($is_started=="1")
		{

			$par_value_array = array();
			$hole_index_array = array();
			$par_sum = 0;
			$hole_sum = 0;
			$queryString = "select ";
			$queryString .= " golf_hole_index_id, ";

			$qarr=array(); $qarr1=array();

			for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
				$qarr[]=" par_value_{$i}";
				$qarr1[]=" hole_index_{$i}";
			}

			$queryString .= implode(",",$qarr);
			$queryString .= ",".implode(",",$qarr1);
			$queryString .= " from golf_hole_index";
			$queryString .= " where golf_course_id = ".$golf_course_id;

			$row_index  = $this->db->FetchRow($queryString);
			$golf_hole_index_id = $row_index['golf_hole_index_id'];

			$par_value_array=array();
			$hole_index_arr_all=array();
			for($i=$hole_start_from;$i<=$total_hole_num;$i++) {
				$par_value_array[$i] = $row_index["par_value_{$i}"];
				$hole_index_arr_all[$i] = $row_index["hole_index_{$i}"];
			}

foreach ($par_value_array as $key => $value)
			{
				$queryString1 = " select event_score_calc.score_entry_".$key." from event_score_calc where event_id =".$event_id." and player_id =".$player_id;
				 $gross_score  = $this->db->FetchSingleValue($queryString1);

				$score_array[$key] = $gross_score;
				$total_sum = $total_sum + $gross_score;
			if($value == 3)
				{

				 $sum_par3 = $sum_par3 + $gross_score;
					$count_par3++;
				}
				else if($value == 4)
				{
					$sum_par4 = $sum_par4 + $gross_score;
					$count_par4++;
				}
				else if($value == 5)
				{
					$sum_par5 = $sum_par5 + $gross_score;
					$count_par5++;
				}
				if($key <= 9)
				{
					$sum_first9 = $sum_first9 + $gross_score;
					$sum_par_first9 = $sum_par_first9 + $value;
				}
				else if($key > 9)
				{
					$sum_last9 = $sum_last9 + $gross_score;
					$sum_par_last9 = $sum_par_last9 + $value;
				}
				$difference =  $gross_score - $value;
				if( $difference <= -2)
				{
					$no_of_eagle = $no_of_eagle + 1;
				}
				else if( $difference == -1)
				{
					$no_of_birdies = $no_of_birdies + 1;
				}
				else if( $difference == 0)
				{
					$no_of_pars = $no_of_pars + 1;
				}
				else if( $difference == 1)
				{
					$no_of_bogeys = $no_of_bogeys + 1;
				}
				else if( $difference >= 2)
				{
					$no_of_double_bogeys = $no_of_double_bogeys + 1;
				}

			}

			$my_score = array_sum($score_array);

			 $no_of_eagle = ($no_of_eagle *100)/18 ;
			 $no_of_birdies = ($no_of_birdies*100)/18 ;
			 $no_of_pars = ($no_of_pars *100)/18 ;
			 $no_of_bogeys = ($no_of_bogeys *100)/18 ;

			$no_of_double_bogeys = ($no_of_double_bogeys *100)/18 ;

			//$stacks = array('eagle'=>$no_of_eagle,'birdie'=>$no_of_birdies,'par'=>$no_of_pars,'bogey'=>$no_of_bogeys,'doublebogey'=>$no_of_double_bogeys,'average'=> $my_score);

$stacks = array('eagle'=>ceil($no_of_eagle),'birdie'=>ceil($no_of_birdies),'par'=>ceil($no_of_pars),'bogey'=>ceil($no_of_bogeys),'doublebogey'=>ceil($no_of_double_bogeys),'average'=> ceil($my_score));
			$fdata['status'] ='1';
			$fdata['data'] =$stacks;
			$fdata['message'] ='stats Data';

		}else{
			$fdata['status'] ='0';
			$fdata['message'] ='Score Not Submitted';
		}
		return $stacks;
	}


function getEventInvitationList($data)

    {
        $fdata = array();
			$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
			if($user_id==""){
				$fdata['status'] = "0";
				$fdata['message'] = "Required field not found";

			}else{
				$totalunreadinvitation=0;
				$crdate_str = '';
				//$crdate_str = "and DATE(eve.event_start_date_time)>= '".date('Y-m-d')."'";
				
			$queryString = "select e.player_id,e.is_accepted,eve.event_id,eve.event_name,eve.event_display_number,e.is_score_enterer,DATE(eve.event_start_date_time) as start_date,eve.event_start_time,eve.is_started,g.golf_course_name,g.golf_course_id,eve.format_id,eve.admin_id,u.full_name as admin,e.creation_date,e.is_submit_score,e.read_status,e.add_player_type from "._EVENT_PLAYER_LIST_TBL_." e left join "._EVENT_TBL_." eve ON eve.event_id=e.event_id left join ".TABLE_GOLF_USERS." u ON u.user_id=eve.admin_id left join "._GOLF_COURSE_TBL_." g ON g.golf_course_id=eve.golf_course_id WHERE eve.is_started in (0,1,3) and  e.player_id='".$user_id."'  and e.is_accepted!='2' and e.is_submit_score='0' ".$crdate_str." order by start_date asc,eve.event_start_time asc, eve.event_id desc";

				  $queryResult = $this->db->FetchQuery($queryString);

				 if(count($queryResult)>0)
				{
					$total=0;//mysql_num_rows($queryResult);
					foreach($queryResult as $rowValues)
					{
						if(($rowValues['start_date'] < date("Y-m-d")) && $rowValues['is_started']!=3) {
							continue;
						}

	if(($rowValues['is_accepted'] == 1 && $rowValues['add_player_type'] == 1) || ($rowValues['is_accepted'] != 2 && $rowValues['add_player_type'] == 0)){

						$getformatequery="select format_name from game_format where format_id='".$rowValues['format_id']."' limit 1 ";
						$format_name = $this->db->FetchSingleValue($getformatequery);


						$queryString1 = " select city_id from golf_course where golf_course_id =".$rowValues['golf_course_id'];
						$city_id = $this->db->FetchSingleValue($queryString1);

						$locationQuery="select city_name from city where city_id='".$city_id."'";
						$city_name = $this->db->FetchSingleValue($locationQuery);
						
						$banner_data = $this->getAdvertisementBanner(array('type'=>'2','event_id'=>$rowValues['event_id']));
						
						if(isset($banner_data['data']) && is_array($banner_data['data']) && count($banner_data['data'])>0) {
							$rowValues['banner_image']=$banner_data['data'][0]['image_path'];
							$rowValues['banner_href']=$banner_data['data'][0]['image_href'];
						}
						else {
							$rowValues['banner_image']='';
							$rowValues['banner_href']='';
						}


						$rowValues['location']=$city_name;
						$rowValues['formate_name']=$format_name;
						$is_started="";
						$event_status=$rowValues['is_started'];
						if($rowValues['is_started']=="0" || $rowValues['is_started']=="1"){
							$is_started="Pending";
						}elseif($rowValues['is_started']=="3"){
							$is_started="Started";
						}else{
							$is_started="Closed";
						}
						$rowValues['event_start_time']=date("H:i", strtotime($rowValues['event_start_time']));
$rowValues['start_date']=date("d M", strtotime($rowValues['start_date']));
						$rowValues['is_started']=$is_started;



						if($rowValues['is_accepted']=="0" && $rowValues['read_status']=="0"){
							$totalunreadinvitation++;
						}
						if($rowValues['is_accepted']=="0"){
							$total++;
						}
						if($rowValues['is_accepted']=="1"){
							if($event_status == 3){

								if($rowValues['is_score_enterer'] == 1){

// 1= start event, 2= resume round, 3= event not started
									$rowValues['is_accepted']="2";
								}else{
									$rowValues['is_accepted']="1";
								}
							}else{
							$rowValues['is_accepted']="3";
							}
						}elseif($rowValues['is_accepted']=="2"){
							$rowValues['is_accepted']="Rejected";
						}else{
							$rowValues['is_accepted']="Pending";
						}
						if($rowValues['admin_id']==$user_id && $is_started=="Pending"){
							$rowValues['is_edit']="Edit";
}else{
$rowValues['is_edit']="Details";
}
						$DateWiseArray[] = $rowValues;
					}
					}
					unset($rowValues['is_score_enterer']);
$EventDateWiseArray=array('Invitation'=>$DateWiseArray,'total_pending_invitation'=>$total,'total_unread_pendinginvitation'=>$totalunreadinvitation);
					$fdata['status'] = "1";
				$fdata['data'] = $EventDateWiseArray;
				$fdata['message'] = "EventInvitationList";
				}else{

				$fdata['status'] = "0";
				$fdata['message'] = "No Request Found";
				}


			}
			return $fdata ;
        }


	function AcceptRejectEvent($data)
	{

		$eventId=isset($data['event_id'])?$data['event_id']:"0";
		$userId=isset($data['user_id'])?$data['user_id']:"0";
		$status=isset($data['status'])?$data['status']:"0";
//print_r($data);
		if($eventId > 0 && $userId > 0 && $status > 0){

			$error='';
			$isPlayerExist = 0;
			$queryString = "select is_accepted from event_player_list where event_id ='".$eventId."' and player_id ='".$userId."'";
			$is_accepted = $this->db->FetchSingleValue($queryString);

			if($is_accepted <= 0 )
			{

			$sql="select event_name,admin_id,DATE(event_start_date_time) as event_start_date,is_started from event_table where event_id='".$eventId."'";
			$result = $this->db->FetchRow($sql);
			$event_name = $result['event_name'];
			$admin_id = $result['admin_id'];
			$event_start_date = $result['event_start_date'];
			$is_started = $result['is_started'];

			if($status=="1"){

				if($is_started=="3"){
			$this->startEventForPlayer($eventId,$userId);
				}

				 $sql="update event_player_list set is_accepted='1',accepted_by='".$userId."',accepted_date=now()  where event_id ='".$eventId."' and player_id ='".$userId."'";
				$this->db->FetchRow($sql);

					$queryString = " select  ";
					$queryString .= " event_id, ";
					$queryString .= " admin_id, ";
					$queryString .= " golf_course_name,";
					$queryString .= " event_name,";
					$queryString .= " date(event_start_date_time) as event_date,";
					$queryString .= " event_start_time,";
					$queryString .= " total_hole_num,";
					$queryString .= " format_name,";
					$queryString .= " stoke_name,";
					$queryString .= " tee_name, ";
					$queryString .= " is_started as game_status";
					$queryString .= " from event_list_view";
					$queryString .= " where event_id='".$eventId."' ";
					$rowValues = $this->db->FetchRow($queryString);

					if($queryResult)
					{
						$queryString1 = "select full_name from golf_users where user_id ='".$rowValues['admin_id']."' ";
						$full_name = $this->db->FetchSingleValue($queryString1);
						$rowValues['admin_name']=$full_name;
						$rowValues['tee_name']=getEventTee($rowValues['tee_name']);

					}

				$fdate['status'] = "1";
				$fdate['data'] = $rowValues;
				$fdate['message'] = "You Have Accepted This Request";


					$alertsubject='<strong>'.$event_name.'</strong> Accepted By ';

					}
					elseif($status == '2'){
					 /*if($is_started=="3"){
							$error='1';
							$message="Event already started on date. \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));
							$fdate['status'] = "0";
							$fdate['message'] = $message;
					  }else*/if($is_accepted=="2"){
						    $error='1';
							$message="You have already rejected this event . \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));
							$fdate['status'] = "0";
							$fdate['message'] = $message;
					  }else{
					  		$sql="update event_player_list set is_accepted='2' where event_id ='".$eventId."' and player_id ='".$userId."'";
							$this->db->FetchQuery($sql);
						$fdate['status'] = "1";
				$fdate['message'] = "You Have Declined This Request";
						$alertsubject='<strong>'.$event_name.'</strong> Rejected By ';
						}
				   }
				   else{}
				   if($error==''){
				   ///////////////////////////Send Alert////////////////////////////
					$sql="select full_name from golf_users where user_id='".$userId."'";

					$player_name = $this->db->FetchSingleValue($sql);

					$alertmessage="Event Request ".$alertsubject." <strong>".$player_name."</strong>";

//SendAlert($userId,$admin_id,$alertsubject.$player_name,$alertmessage);
					///////////////////////////Send Alert////////////////////////////

				//   return formatData("Success",$message,$format);
				   }
			}elseif($is_accepted == 1) {
				$fdate['status'] = "0";
				$fdate['message'] = "You Already Accepted Request";

			}else{
				$fdate['status'] = "0";
				$fdate['message'] = "Invitation Request not found";

			}

		}
	   else{
		   $fdate['status'] = "0";
		   $fdate['message'] = "Required field not found";

	   }
//print_r($fdate);die;
	   return $fdate ;
	}

function getEventDetail($data){
		$fdata =array();
		$eventId =   (isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:0;
		$user_id = (isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:0;
$request_to_participate = (isset($data['request_to_participate']) && $data['request_to_participate'] > 0)?1:0;
		 
  if($eventId > 0 && $user_id > 0)
			{
if($request_to_participate <= 0){
 $q="select * from event_player_list where player_id ='".$user_id."' and event_id='".$eventId."' ";
 
						$resdata = $this->db->FetchQuery($q);
						if(count($resdata)>0){

						}else{
$q="select admin_id from event_table where event_id='".$eventId."'";						
						$user_id=$this->db->FetchSingleValue($q);
}
}

$con = ($request_to_participate <=0)?' AND p.player_id = '.$user_id.'':'';
$con1 = ($request_to_participate <=0)?' AND s.player_id = '.$user_id.'':'and s.player_id=p.player_id';
$con_group = ($con!='') ? "group by p.player_id" : "";
$con_group = "group by p.player_id";
		$queryString = "select e.*,g.golf_course_name,e.admin_id as event_admin_id,f.format_name,t.type,e.is_individual,p.is_score_enterer,p.scorere_id,p.is_submit_score,p.is_accepted,s.hole_number,s.no_of_holes_played,(select count(1) from event_player_list where event_id=".$eventId." and scorere_id=".$user_id.") as is_scorer from event_table e inner join game_format f on f.format_id = e.format_id inner join golf_course g ON g.golf_course_id = e.golf_course_id left join event_type t ON t.event_type_id = e.is_public inner JOIN event_player_list p ON p.event_id = e.event_id {$con} LEFT JOIN event_score_calc s ON s.event_id = e.event_id {$con1} where e.event_id ='".$eventId."' ".$con_group." ";
//AND p.player_id = ".$user_id."   
//echo $queryString;die;
$rowValues  = $this->db->FetchRow($queryString); 	
$TeeListArray = array();
if(isset($rowValues) && $rowValues != '' && count($rowValues) > 0)
				{
					if($rowValues['is_individual'] == 1){
						$rowValues['is_individual'] = 'Individual';
					}else{
						$rowValues['is_individual'] = 'Team';
					}
					if($rowValues['total_hole_num']==9)
					{
						if($rowValues['hole_start_from']==1){
							$rowValues['holes']="Front 9";
						}
						elseif($rowValues['hole_start_from']==10){
							$rowValues['holes']="Back 9";
						}
					}
					else
					{
						$rowValues['holes']="";
					}
					if($rowValues['is_public'] == 1){

$rowValues['type'] = 'Public';
}else{
$rowValues['type'] = 'Private';
}
//echo $user_id ; die;
if($user_id == $rowValues['admin_id']){
//echo 'dd'; die;
$rowValues['is_admin'] = '1';
}else{
$rowValues['is_admin'] = '0';
}

//  1= start event, 2= resume round, 3= event not started, 4= started event, 5= accept,6=Request to Participate, 7=Participation Request Pending, 8 = Score Board

					if($rowValues['is_started'] == 3 && $rowValues['is_accepted'] == 1){
						if($rowValues['no_of_holes_played'] > 0 && $rowValues['is_scorer'] > 0){
							$rowValues['start_round_status'] = '2';
						}
						elseif($rowValues['no_of_holes_played'] == 0 && $rowValues['is_scorer'] > 0){
							$rowValues['start_round_status'] = '9';
						}
						else{
							$rowValues['start_round_status'] = '8';
						}
					}
					else {
						if($rowValues['event_admin_id'] == $user_id ){
							$rowValues['start_round_status'] = '1';
						}
						else{
							if($rowValues['is_accepted'] == 1){
								$rowValues['start_round_status'] = '3';
							}
							else{
								$rowValues['start_round_status'] = '5';
							}
						}
					}
					if($request_to_participate==1){
						$quesry = 'SELECT event_list_id from event_player_list where event_id="'.$eventId.'" And player_id="'.$user_id.'" AND is_accepted != "2"'; 
						$event_list_id1= $this->db->FetchRow($quesry);
						if(is_array($event_list_id1) && count($event_list_id1) > 0){
							$rowValues['start_round_status'] = '7';
						}
						else{
							$rowValues['start_round_status'] = '6';
						}
					}


/* if($rowValues["no_of_holes_played"]>0){
								$rowValues["start_round_status"]=true;
}else{ $rowValues["start_round_status"]=false; } */

					$start_date=date("Y-M-d",strtotime($rowValues['event_start_date_time']));
					$start_time=date("H:i", strtotime($rowValues['event_start_time']));
					$rowValues['event_start_date_time']=$start_date.' '.$start_time;
					unset($rowValues['hole_start_from']);
					unset($rowValues['event_start_time']);
//unset($rowValues['type']);

if($rowValues['no_of_player'] == '4+'){
$rowValues['is_singlescreen'] = '0';
}else{
$rowValues['is_singlescreen'] =$rowValues['is_singlescreen'];
}
$rowValues['players_in_game'] = $rowValues['no_of_player'];
				if($rowValues['no_of_player'] == '4+'){
						$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted in (1,0)";
						$queryResult1  = $this->db->FetchQuery($queryString1);
						$rowValues['no_of_player']=count($queryResult1);
						
				}

					$rowValues['tee_id']=getEventTee($rowValues['tee_id']);//json_decode($rowValues['tee_id']);
				$queryString = "select CAST(hole_number AS CHAR(20)) as hole_number from event_is_spot_tbl where event_id ='".$eventId."' AND type = 1 ";
        			$closest_pin  = $this->db->FetchQuery($queryString);
					$queryString = "select CAST(hole_number AS CHAR(20)) as hole_number from event_is_spot_tbl where event_id ='".$eventId."' AND type = 3";
        			$long_drive  = $this->db->FetchQuery($queryString);
					$queryString = "select CAST(hole_number AS CHAR(20)) as hole_number from event_is_spot_tbl where event_id ='".$eventId."' AND type = 2 ";
        			$straight_drive  = $this->db->FetchQuery($queryString);

					$rowValues['closest_pin']=$closest_pin;
					$rowValues['long_drive']=$long_drive;
					$rowValues['straight_drive']=$straight_drive;
					$TeeListArray[] = $rowValues ;


					$fdata['status'] = '1';
					$fdata['data'] = $TeeListArray;
					$fdata['msg'] = 'Event Detail';

				}
				else
				{
					$fdata['status'] = '0';
					$fdata['msg'] = 'Event not exists in database';

				}
			}
		   else
		   {
				$fdata['status'] = '0';
				$fdata['msg'] = 'Required field not found';
           }

		return $fdata ;
    }

	function getScorerList($data){
		//print_r($data); die;
		$invited_email_list=isset($data['invited_email_list'])?$data['invited_email_list']:array();
		$invited_group_list=isset($data['event_group_list'])?$data['event_group_list']:array();
		$event_friend_list=isset($data['event_friend_list'])?$data['event_friend_list']:array();
		$team_list=isset($data['team_list'])?$data['team_list']:array();
		 $creation_date = date('Y-m-d H:i:s');
		$admin_id = $data['event_admin_id'];
		$team_members = array();
		$friend_members = array();
		$email_members = array();
		$group_members = array();
		$grpsqlresultssa = array();
		if(count($team_list)>0){
			$counter = 0;
			foreach($team_list as $teamNameId){
				$friendCounter = 1;
				foreach( $team_list[$counter]['event_friend_list'][0] as $player_id){
					if($player_id<=0) {continue;}
					if(!in_array($player_id,$team_members)) {
						$team_members[] = $player_id;
					}
					$friendCounter++;
				}
				$counter++;
			}
				$team_member_count = count($team_members);
		}
			if(isset($invited_group_list) && count($invited_group_list) > 0){
			foreach($invited_group_list as $i=>$invited_group) {
				$InvGroup = isset($invited_group["group"]) ? $invited_group["group"] : 0;
				if($InvGroup>0 && !in_array($InvGroup,$grpsqlresultssa)){
					$grpsqlresultssa[] =  $InvGroup;
				}
			}
			if(count($grpsqlresultssa) > 0) {
				$grpsql='select group_concat(grp_member_id) as total_members from '._GROUP_MEMBER_LIST_.' where group_id in ('.implode(',',$grpsqlresultssa).') limit 1';
				$group_members_str = $this->db->FetchSingleValue($grpsql);
				$group_members_old = explode(',',$group_members_str);
				foreach($group_members_old as $a) {
					$group_members[] = $a;

				}
			}
		}
		$friendCounter = 1;
		if(count($event_friend_list)>0){
			foreach($event_friend_list as $particiantId=>$f){

				$userId = $f['friend_id'];
					$friend_members[] = $userId;

				$friendCounter++;
			}

		}
	$val =array();
	// get email invites
		if(is_array($invited_email_list) && count($invited_email_list) > 0 ){
			$inviteCounter=1;
			foreach($invited_email_list as $nonregemail){

				$InvEmail = isset($nonregemail["email"]) ? $nonregemail["email"] :'';
				if($InvEmail!="" && !in_array($InvEmail,$email_members)){
					$email_members[] = $InvEmail;
				}
				$inviteCounter++;
			}

		}
	$friend = array_merge($group_members,$team_members,$friend_members);
	$friend_list = array_unique($friend);
	$del="DELETE FROM temp_scorer_list WHERE admin_id='".$admin_id."'";
			$this->db->FetchQuery($del);
	if(count($friend_list) > 0){
		foreach($friend_list as $f=>$id){
		 	$sqlchk="select g.user_id,g.authorization_key,g.display_name,u.self_handicap as handicap_value,u.photo_url from golf_users g left join user_profile u ON u.user_id=g.user_id  where g.user_id='".$id."' ";
			$rowValues  = $this->db->FetchRow($sqlchk);
			if($rowValues['user_id'] >0){
if($rowValues['authorization_key'] != ''){
$is_login = '1';
}else{
$is_login = '0';
}
					$sqlQuery1="insert into temp_scorer_list set admin_id='".$admin_id."',participant_id='".$rowValues['user_id']."',handicap_value='".$rowValues['handicap_value']."',user_name='".$rowValues['display_name']."',image_url='".$rowValues['photo_url']."',creation_date='".$creation_date."',is_facebook_login='".$is_login."'";
			$this->db->FetchQuery($sqlQuery1);
			}
	}
	  if(is_array($invited_email_list) && count($invited_email_list) > 0){

					$event_invite_email = array();
					foreach($invited_email_list as $nonregemail){

						$InvEmail = isset($nonregemail["email"]) ? $nonregemail["email"] :'';
						$InvName = isset($nonregemail["name"]) ? $nonregemail["name"] :'';
						$InvHandicap = isset($nonregemail["handicap"]) ? $nonregemail["handicap"] :'';

						if($InvEmail!=""){

							$sqlchk="select user_id from golf_users where user_name='".$InvEmail."'";
							$userValue = $this->db->FetchSingleValue($sqlchk);
							if($userValue < 0) {
							$userId  = (isset($userValue) && $userValue >0)?$userValue:0;
							}

								$sqlQuery1="insert into temp_scorer_list set admin_id='".$admin_id."',participant_id='".$userId."',handicap_value='".$InvHandicap."',user_name='".$InvName."',image_url='',creation_date='".$creation_date."',is_facebook_login='".$is_login."'";
						$this->db->FetchQuery($sqlQuery1);

						}

					}

				}
	}
	$sqlchk="select * from temp_scorer_list where admin_id='".$admin_id."'";
	$list = $this->db->FetchQuery($sqlchk);

	if(count($list) > 0){
		foreach($list as $i=>$l){
$img = ($l['is_facebook_login'] == 1)?$l['image_url']: __BASE_URI__."images/profile/".$l['image_url'];
			$p['photo_url']=($l['image_url']!="")?$img:"";
			$p['name']=$l['user_name'];
			$p['handicap_value']=$l['handicap_value'];
			$p['participant_id']=$l['participant_id'];
			$suggestionArr[]=$p;

		}

	}

	$fdata['status'] ='1';
	$fdata['data'] =$suggestionArr;
	$fdata['message'] ='Scorer List';
	return $fdata ;
}
	function editEvent($data){

		$event_admin_id = $data['event_admin_id'];
		$eventId = $data['event_id'];
		$event_golf_course_id=isset($data['event_golf_course_id'])?$data['event_golf_course_id']:0;
		$event_name=isset($data['event_name'])?($data['event_name']):'';
		$event_format_id=isset($data['event_format_id'])?$data['event_format_id']:array();
		$event_tee_id=isset($data['event_tee_id'])?$data['event_tee_id']:array();
		//$event_tee_id = (count($event_tee_id)>0) ? json_encode($event_tee_id) : '' ;
		$event_tee_id = (count($event_tee_id)>0) ? $this->convertTeeFormat($event_tee_id) : '';
		$event_start_date = isset($data['event_start_date'])?$data['event_start_date']:"";
		$event_start_time = isset($data['event_start_time'])?$data['event_start_time']:"";
		$event_is_spot = isset($data['is_spot'])?$data['is_spot']:"-1";
		$event_is_singlescreen = isset($data['is_singlescreen'])?$data['is_singlescreen']:"-1";
		$creation_date = date('Y-m-d H:i:s');
		$event_is_public = isset($data['event_is_public'])?$data['event_is_public']:"";
		$closest_pin = $long_drive = $straight_drive = 0;
		
		$ip_address = $_SERVER['REMOTE_ADDR'];

 //echo $eventId.'___'.$event_name.'___'.$event_start_date.'___'.$event_start_time.'___'.$event_golf_course_id.'___'.$event_format_id.'___'.$event_tee_id.'___'.$event_admin_id.'________'.$event_is_public; die;

		$fdata = array();
		if($eventId > 0 ) {
			$sql = "Select event_id FROM "._EVENT_TBL_." WHERE admin_id =".$event_admin_id." AND event_id = ".$eventId."  ";
			$eventdata = $this->db->FetchRow($sql);
			if(isset($eventdata) && $eventdata >0){
				
				$sql_str = array();
				
				if(trim($event_name)!='') {
					$sql_str[]="event_name='".$this->db->escape($event_name)."'";
				}
				
				if(trim($event_start_date)!='') {
					$sql_str[]="event_start_date_time='".$this->db->escape($event_start_date)."'";
				}
				
				if(trim($event_start_time)!='') {
					$sql_str[]="event_start_time='".$this->db->escape($event_start_time)."'";
				}
				
				if(trim($event_golf_course_id)!='' && $event_golf_course_id > 0) {
					$sql_str[]="golf_course_id='".$this->db->escape($event_golf_course_id)."'";
				}
				
				if(trim($event_format_id)!='' && $event_format_id > 0) {
					$sql_str[]="format_id='".$this->db->escape($event_format_id)."'";
				}
				
				if(trim($event_tee_id)!='') {
					$sql_str[]="tee_id='".$this->db->escape($event_tee_id)."'";
				}
				
				if(trim($event_is_spot)!='' && $event_is_spot >= 0) {
					$sql_str[]="is_spot='".$this->db->escape($event_is_spot)."'";
				}
				
				if(trim($event_is_singlescreen)!='' && $event_is_singlescreen >= 0) {
					$sql_str[]="is_singlescreen='".$this->db->escape($event_is_singlescreen)."'";
				}
				
				/*if(trim($closest_pin)!='' && $closest_pin > 0) {
					$sql_str[]="closest_pin='".$this->db->escape($closest_pin)."'";
				}
				
				if(trim($long_drive)!='' && $long_drive > 0) {
					$sql_str[]="long_drive='".$this->db->escape($long_drive)."'";
				}
				
				if(trim($straight_drive)!='' && $straight_drive > 0) {
					$sql_str[]="straight_drive='".$this->db->escape($straight_drive)."'";
				}*/
				
				if(count($sql_str) > 0) {
					$ssql = "update "._EVENT_TBL_." set ".implode(',',$sql_str)." where event_id='".$eventId."'";
					$this->db->FetchQuery($ssql);
					
					if($event_is_singlescreen == 2) {
						$ssql = "update event_player_list set scorere_id = player_id where event_id='".$eventId."'";
						$this->db->FetchQuery($ssql);
					}
					else {
						$ssql = "update event_player_list set scorere_id = event_admin_id,is_accepted=1 where event_id='".$eventId."'";
						$this->db->FetchQuery($ssql);
					}
					
					if($event_is_spot == '1') {
						$closest_pin = (isset($data['closest_pin']) && is_array($data['closest_pin']) && count($data['closest_pin'])>0)?array_values($data['closest_pin']):array();
						$long_drive = (isset($data['long_drive']) && is_array($data['long_drive']) && count($data['long_drive'])>0)?array_values($data['long_drive']):array();
						$straight_drive = (isset($data['straight_drive']) && is_array($data['straight_drive']) && count($data['straight_drive'])>0)?array_values($data['straight_drive']):array();
						
						if(count($closest_pin)>0){
							$sql = 'delete from event_is_spot_tbl where event_id = "'.$eventId.'" and type=1';
							$this->db->FetchQuery($sql);
							
							foreach($closest_pin as $i=>$v ){
								$queryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",1,".$v.")";
								$this->db->FetchQuery($queryString);
							}
						}
						
						if(count($straight_drive)>0){
							$sql = 'delete from event_is_spot_tbl where event_id = "'.$eventId.'" and type=2';
							$this->db->FetchQuery($sql);
							foreach($straight_drive as $i=>$s ){
								$squeryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",2,".$s.")";
								$this->db->FetchQuery($squeryString);
							}
						}
						
						if(count($long_drive)>0){
							$sql = 'delete from event_is_spot_tbl where event_id = "'.$eventId.'" and type=3';
							$this->db->FetchQuery($sql);
							foreach($long_drive as $i=>$l ){
								$lqueryString = " insert into event_is_spot_tbl(event_id,type,hole_number) values(".$eventId.",3,".$l.")";
								$this->db->FetchQuery($lqueryString);
							}
						}
						
					}
					elseif($event_is_spot == '0') {
						$sql = 'delete from event_is_spot_tbl where event_id = "'.$eventId.'" ';
						$this->db->FetchQuery($sql);
					}
					
					$fdata['status'] = '1';
					$fdata['message'] = 'Event Successfully updated';
				}
				else {
					$fdata['status'] = '0';
					$fdata['message'] = 'No Changes Found';
				}
			}
			else {
				$fdata['status'] = '0';
				$fdata['message'] = 'You have not permission to update this event';
			}
		}
		else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required Field not Found';

		}
		return $fdata;

	}

function getEventParticipentList($data){

		$eventId=isset($data['event_id'])?$data['event_id']:"";
		$ParticipentArray=array();
		$fdata = array();
		//$eventId = 3;
		$golf_course_id = 0;
		$handicap_value=0;

		if($eventId==''){
			$fdata['status'] = '0';
			$fdata['message'] = 'Required field can not be blank.';
		}
		else{
			$queryString = "select golf_course_id from "._EVENT_LIST_VIEW_." where event_id =".$eventId;
			$golf_course_id=$this->db->FetchSingleValue($queryString);

			$queryString = "select g.user_id as userId,g.full_name, g.user_name,g.registered_mobile_number,p.photo_url from golf_users g left join user_profile p on g.user_id=p.user_id where g.user_id in (select player_id from "._EVENT_PLAYER_LIST_TBL_." where event_id =".$eventId."  and add_player_type ='0' or (add_player_type ='1' and is_accepted='1' and event_id =".$eventId." ))";
			$recordSetParticipant=$this->db->FetchQuery($queryString);
			foreach($recordSetParticipant as $i=>$rowValues )
			{
				$queryString1= "select is_accepted,add_player_type from event_player_list where player_id =".trim($rowValues['userId'])." and event_id =".$eventId." ";
				$result=$this->db->FetchRow($queryString1);
				$is_accepted = $result['is_accepted'];
				$add_player_type = $result['add_player_type'];

				if($is_accepted == 1)
				{
				   $status="Accepted";
				}
				else if($is_accepted == 2)
				{
					$status="Rejected";
				}
				else
				{
					$status="Pending";
				}
				$rowValues['invitation_status']=$status;
				$rowValues['full_name']=ucfirst($rowValues['full_name']);
$rowValues['event_id']=$eventId;
				$rowValues['thumb_url']=($rowValues['photo_url']!="" && file_exists(BASE_PATH."/uploads/profile/thumb/".$rowValues['photo_url']))?(__BASE_URI__."uploads/profile/thumb/".$rowValues['photo_url']):__BASE_URI__."uploads/profile/noimage.png";
				$rowValues['photo_url']=($rowValues['photo_url']!="")?(__BASE_URI__."uploads/profile/".$rowValues['photo_url']):__BASE_URI__."uploads/profile/noimage.png";

				$rowValues['add_player_type']=$add_player_type;
				$queryString = "select handicap_value from golf_course_user_handicap where participant_id =".trim($rowValues['userId'])." and event_id =".$eventId." and golf_course_id = ". $golf_course_id. " ";
				$handicap_value =$this->db->FetchSingleValue($queryString);

				if($handicap_value > 0){
				}
				else{
					$sqlA="select self_handicap from user_profile where user_id='".trim($rowValues['userId'])."'";
					$handicap_value =$this->db->FetchSingleValue($sqlA);
					if($handicap_value > 0){
						$queryString = "insert into golf_course_user_handicap(";
						$queryString .= " event_id, golf_course_id,participant_id, handicap_value,ip_address)";
						$queryString .= " values (";
						$queryString .= "'".trim($eventId)."',";
						$queryString .= "'".trim($golf_course_id)."',";
						$queryString  .= trim($rowValues['userId']).",";
						$queryString .= "'".$handicap_value."',";
						$queryString  .= "'".$_SERVER['REMOTE_ADDR'];
						$queryString  .="')"; //echo $queryString;
						$this->db->FetchQuery($queryString);
					}
				}
							if(isset($handicap_value))
							{
							$rowValues['handicap_value']=$handicap_value;
							}
							else
							{
							$rowValues['handicap_value']=0;
							}


												$ParticipentArray[] = $rowValues ;
							}
							$fdata['status'] = '1';
							$fdata['data'] = $ParticipentArray;
							$fdata['message'] = 'Participent List';

			}
			return $fdata;
		}
function RequestToParticipate($data){

		$user_id_arr=$fdata=array();

		if(isset($data['user_id']) && (!is_array($data['user_id']) && trim($data['user_id'])!='')) {
			$user_id_arr=array(intval($data['user_id']));
		}
		elseif(isset($data['user_id']) && is_array($data['user_id']) && count($data['user_id'])>0) {
			$user_id_arr=$data['user_id'];
		}
		$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"";
		$add_player_type=(isset($data['type']) && $data['type'] > 0)?$data['type']:"0";

		$skip_arr=array(); $insert_arr=array(); $exist_arr=array();
		$errorMessage1=array(); $errorMessage2=array();
		if(count($user_id_arr)==0 || $event_id==""){
			$fdata['status'] = '0';
			$fdata['msg'] = 'Required field not found';


		}else{
			$sql="select admin_id,event_name,DATE(event_start_date_time) as event_start_date from "._EVENT_TBL_." where event_id='".$event_id."' ";
			$rowValues  = $this->db->FetchRow($sql);
			$admin_id=$rowValues['admin_id'];
			$event_name=$rowValues['event_name'];
			$event_start_date=$rowValues['event_start_date'];

			foreach($user_id_arr as $k=>$user_id) {
				$queryString = " select player_id from "._EVENT_PLAYER_LIST_TBL_." where player_id = ";

				$queryString .= "'".trim($user_id)."'";

				$queryString  .= " and event_id = ".$event_id." AND is_accepted !=2";
				$player_id  = $this->db->FetchRow($queryString);

				if ( count($player_id) && is_array($player_id) > 0 )

				{

					$errorMessage1[] = "Participant Name Already Exist. Please try with new Participant Name";

					$exist_arr[]=$user_id;

				}else{

					$sql="select full_name,creation_date from ".TABLE_GOLF_USERS." where user_id='".$user_id."' ";
					$rowValues  = $this->db->FetchRow($sql);
					$player_name=$rowValues['full_name'];
					date_default_timezone_set('asia/calcutta');
					$is_new = 0;

					if((bool)strtotime($rowValues['creation_date'])) {

						$user_create_time=strtotime($rowValues['creation_date']);

						$sec_diff = abs(time()-$user_create_time);

						$min_diff = round($sec_diff / 60); //echo '__'.$min_diff.'___';

						if($min_diff<=10) {$is_new=1;}//echo 'is new __'.$is_new.'___';
					}

$queryString = " select event_list_id from "._EVENT_PLAYER_LIST_TBL_." where player_id = '".trim($user_id)."' and event_id = ".$event_id."";
		 $isExistPlayer  = $this->db->FetchSingleValue($queryString);
if($isExistPlayer > 0){
		$queryString = "Update "._EVENT_PLAYER_LIST_TBL_." SET is_accepted = '0' WHERE event_id = '".$event_id."' AND player_id = '".$user_id."' ";
				$rowValues  = $this->db->FetchQuery($queryString);

					$alertmessage="<strong>".$player_name."</strong> request you to participate this event <strong>".$event_name."</strong> ";

					//SendAlert($user_id,$admin_id,'Request To Participate',$alertmessage);

					///////////////////////////Send Alert////////////////////////////

					

					$msg_type = ($add_player_type!=1) ? 3 : 7;
}else{
					$queryString = "insert into "._EVENT_PLAYER_LIST_TBL_."(";

					$queryString .= " event_id, event_admin_id, player_id,scorere_id,";

					$queryString .= " is_individual, is_accepted, is_active, ";

					$queryString .= " creation_date, ip_address,add_player_type,is_new) ";

					$queryString .= " values (";

					$queryString .= "'".trim($event_id)."',";

					$queryString  .= $admin_id.",";

					$queryString .= "'".$user_id."','".$user_id."',1,0,1,";

					$queryString  .="now(), ";

					$queryString  .= "'".$_SERVER['REMOTE_ADDR']."','".$add_player_type."','".$is_new."'";

					$queryString  .=")";

					///////////////////////////Send Alert////////////////////////////



					$alertmessage="<strong>".$player_name."</strong> request you to participate this event <strong>".$event_name."</strong> ";

					//SendAlert($user_id,$admin_id,'Request To Participate',$alertmessage);

					///////////////////////////Send Alert////////////////////////////

					$rowValues  = $this->db->FetchQuery($queryString);

					$msg_type = ($add_player_type!=1) ? 3 : 7;
$notif_mth = new createNotification();
					$notif_mth->generatePushNotification($event_id,$msg_type,$user_id,$user_id,'');
}
					$insert_arr[]=$user_id;
				}

			}

			if(count($insert_arr)>0) {

				$msg=count($insert_arr).' requests has been submitted to the organizers. They will revert to you soon.';
				$status='1';
				//sendPushNotification();
			}

			if(count($skip_arr)>0 || count($exist_arr)>0) {
				$err=array();
				if(count($skip_arr)>0){$err[]="You have already accepted an event on the same date. Do you want to continue?";$is_accept_status=true;}
				if(count($exist_arr)>0){$err[]=$errorMessage1[0];}
				if(count($err)>0){
					$msg=implode("\n",$err);
					$status='0';
					}
			}
			$fdata['status'] = $status;
			$fdata['msg'] = $msg;


		} //else close
		return $fdata;
	}
 function getEventRequestList($data)

        {
			$fdata = array();
           $user_id=(isset($data['admin_id']) && $data['admin_id'] > 0)?$data['admin_id']:"0";
			$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"0";
			if($user_id <=0 && $event_id<=0){
				$fdata['status'] ='0';
				$fdata['message'] ='Required field not found';

			}else{
				$totalunreadrequest=0;
		 $queryString = "select e.is_accepted,eve.event_id,eve.event_name,u.user_id,u.full_name as user,up.photo_url as photo_url,up.self_handicap as handicap,eve.golf_course_id from event_player_list e left join event_table eve ON eve.event_id=e.event_id left join golf_users u ON u.user_id=e.player_id left join user_profile up on u.user_id=up.user_id left join golf_course g ON g.golf_course_id=eve.golf_course_id WHERE eve.is_started in (0,1,3) and  e.event_admin_id ='".$user_id."' and e.event_id ='".$event_id."' and e.is_new='0' and e.is_accepted!='2' and e.add_player_type='1' and DATE(eve.event_start_date_time)>= '".date('Y-m-d')."' order by e.creation_date desc";
//eve.is_started in (0,1) and

				$EventDateWiseArray = array();
				$queryResult  = $this->db->FetchQuery($queryString);
//print_r($queryResult); die;
				if(count($queryResult)>0)
				{

				$total=0;//mysql_num_rows($queryResult);
					foreach($queryResult as $i=>$rowValues)
					{

				$queryString = "select handicap_value from golf_course_user_handicap where participant_id =".trim($rowValues['user_id'])." and event_id =".$rowValues['event_id']." and golf_course_id = ". $rowValues['golf_course_id']. " ";
				$handicap_value =$this->db->FetchSingleValue($queryString);

				if($handicap_value > 0){
				}else{
$sqlA="select self_handicap from user_profile where user_id='".trim($rowValues['user_id'])."'";
					$handicap_value =$this->db->FetchSingleValue($sqlA);
}

$rowValues['is_started']=$handicap_value ;
						$is_started="";
						if($rowValues['is_started']=="0" || $rowValues['is_started']=="1"){
							$is_started="Pending";
						}elseif($rowValues['is_started']=="3"){
							$is_started="Started";
						}else{
							$is_started="Closed";
						}

						$rowValues['is_started']=$is_started;
						if($rowValues['is_accepted']=="0" && $rowValues['read_status']=="0"){
							$totalunreadinvitation++;
						}
						if($rowValues['is_accepted']=="0"){
							$total++;
						}
						if($rowValues['is_accepted']=="1"){
							$rowValues['is_accepted']="Accepted";
						}elseif($rowValues['is_accepted']=="2"){
							$rowValues['is_accepted']="Rejected";
						}else{
							$rowValues['is_accepted']="Pending";
						}
						$rowValues['thumb_url']=($rowValues['photo_url']!="" && file_exists(UPLOADS_PROFILE_PATH."thumb/".$rowValues['photo_url']))?(DISPLAY_PROFILE_PATH."thumb/".$rowValues['photo_url']):DISPLAY_PROFILE_PATH."noimage.png";
						$rowValues['photo_url']=($rowValues['photo_url']!="" && file_exists(UPLOADS_PROFILE_PATH.$rowValues['photo_url']))?(DISPLAY_PROFILE_PATH.$rowValues['photo_url']):DISPLAY_PROFILE_PATH."noimage.png";

						$DateWiseArray[] = $rowValues;
					}
				$EventDateWiseArray=array('Request'=>$DateWiseArray,'total_pending_request'=>$total,'total_unread_pendingrequest'=>$totalunreadrequest);
					$fdata['status'] ='1';
				$fdata['data'] =$EventDateWiseArray;
				$fdata['message'] ='EventRequestList';
				} else{
					$fdata['status'] ='0';
				$fdata['message'] ='No Participant Request Found';
				}


			}
			return $fdata ;
        }
function GetEventAccordingToDate($data){

		$fdate = array();
		$current_date=(isset($data['current_date']) && $data['current_date']!='')?$data['current_date']:"";
		$user_id=(isset($data['user_id']) && $data['user_id']!='')?$data['user_id']:"";
		$golf_course_id=(isset($data['golf_course_id']) && $data['golf_course_id']!='')?$data['golf_course_id']:"0";

		if($golf_course_id > 0 && $current_date != ''){

		$queryString = "select e.event_id,e.is_public,e.event_name,e.event_display_number,DATE(e.event_start_date_time) as start_date,e.event_start_time,e.golf_course_name,e.admin_id,u.full_name as admin,e.golf_course_id from event_list_view e left join golf_users u ON u.user_id=e.admin_id WHERE e.event_start_date_time ='".date('Y-m-d H:i:s', strtotime($current_date))."' and e.golf_course_id='".$golf_course_id."' and e.is_started not in(3,2,4) and e.is_public='1' order by e.event_start_date_time asc"; 
			 $queryResult  = $this->db->FetchQuery($queryString);
			if(count($queryResult)>0){
				foreach($queryResult as $i=>$e){
					//check if participate then not display detail
					if($user_id > 0){
						$q="select count(1) as c from event_player_list where (player_id ='".$user_id."' and event_id='".$e['event_id']."') and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))"; 
						$resdata = $this->db->FetchSingleValue($q);//die;

if(isset($resdata) && $resdata > 0){
						}else{
							
						$event[] =$e;
						}
					}else{
					//$event[] =$e;
					}
				} 
				$fdata['status'] = '1';
				$fdata['data'] = $event;
				$fdata['message'] = 'Listing';
			}else{
				$fdata['status'] = '0';
			$fdata['message'] = 'No Event Found';
			}

		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required field not found';
		}
		return $fdata ;
	}

	function startEvent($data)
    {

		$startArray =$fdata= array();
		$eventId=(isset($data['event_id']) && $data['event_id'] >0)?$data['event_id']:0;

		if($eventId<=0){
			$error[]='Event Id not Exist.';
			$fdata['status'] = '0';
			$fdata['message'] = 'Event already started.';
		}
		$error=array();
		$queryString = " select golf_course_id,event_name, is_handicap,format_id,is_started,admin_id,stroke_play_id from event_table where event_id =".($eventId);
		$result  = $this->db->FetchRow($queryString);

		$golf_course_id = $result['golf_course_id'];
		$is_handicap = $result['is_handicap'];
		$event_name = $result['event_name'];
		//$stroke_play_id = $result['stroke_play_id'];
		$stroke_play_id = $result['format_id'];
		$is_started = $result['is_started'];
		$admin_id = $result['admin_id'];
		if($is_started=="3"){
			$error[]='Event already started.';
			$fdata['status'] = '0';
			$fdata['message'] = 'Event already started.';
		}
		if($is_started=="4"){
			$error[]='Event closed.';
			$fdata['status'] = '0';
			$fdata['message'] = 'Event closed.';

		}
		if($is_started=="2"){
			$error[]='Event deleted.';
			$fdata['status'] = '0';
			$fdata['message'] = 'Event deleted.';
		}

		$queryString = " select num_hole from golf_hole_index where golf_course_id =".($golf_course_id);
		$num_hole  = $this->db->FetchSingleValue($queryString);

		if($num_hole==0){
			$error[]='Golf Course must have par value & index value.';
			$fdata['status'] = '0';
			$fdata['message'] = 'Golf Course must have par value & index value.';
		}
		$playerHandicap=0;

		$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted in (1,0)";
		$queryResult1  = $this->db->FetchQuery($queryString1);

		if(count($queryResult1) > 0)
		{
			foreach($queryResult1 as $i=>$rowValues1)
			{
				$queryString = "select handicap_value from golf_course_user_handicap where  handicap_value > 0 and event_id = ".$eventId." and participant_id='".$rowValues1['player_id']."'";
				$queryResult  = $this->db->FetchQuery($queryString);

				if(count($queryResult) > 0){

				}else{
					
					//$sqlA="select temp_handicap from event_player_list where event_id='".$eventId."' and player_id='".$rowValues1['player_id']."'";
					//$handicap_value  = $this->db->FetchSingleValue($sqlA);
					
					//if($handicap_value <=0) {
						$sqlA="select self_handicap from user_profile where user_id='".$rowValues1['player_id']."' ";
						$handicap_value  = $this->db->FetchSingleValue($sqlA);
					//}
					
					if($handicap_value > 0){
						$queryString = "insert into golf_course_user_handicap(";
						$queryString .= " event_id, golf_course_id,participant_id, handicap_value,ip_address)";
						$queryString .= " values (";
						$queryString .= "'".trim(($eventId))."',";
						$queryString .= "'".trim(($golf_course_id))."',";
						$queryString  .= ($rowValues1['player_id']).",";
						$queryString .= "'".($handicap_value)."',";
						$queryString  .= "'".($_SERVER['REMOTE_ADDR']);
						$queryString  .="')";
						$handicap_value  = $this->db->FetchQuery($queryString);
					}else{
						$playerHandicap++;
					}
				}
			}
			if($playerHandicap > 0){
				//$error[]='Participent must have handicap value.';
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Participent not added.';
			$error[]='Participent not added.';
		}

		if($stroke_play_id=="8" || $stroke_play_id=="9"){
			$proriaCounter=0;
			if($stroke_play_id=="8"){
			$counter=6;
				for($c=1;$c<=18;$c++){
					$queryString = " select * from event_par_peoria where is_used_in_peoria_".$c."='1' and event_id ='".$eventId."' and prio_t = 1";
					$queryResult  = $this->db->FetchQuery($queryString);

					if(count($queryResult) > 0){
						$proriaCounter++;
					}
				}
			}else{
			$counter=12;
				for($c=1;$c<=18;$c++){
				$queryString = " select * from event_par_peoria where is_used_in_peoria_".$c."='1' and event_id ='".$eventId."' and prio_t = 2";
				$queryResult  = $this->db->FetchQuery($queryString);
				if(count($queryResult) > 0){
						$proriaCounter++;
					}
				}
			}

			if($proriaCounter < $counter){
				$fdata['status'] = '0';
				$fdata['message'] = "Peoria ".$counter." hole selection value not fill blank.";
				$error[]="Peoria ".$counter." hole selection value not fill blank.";
			}
		}
        /*if($stroke_play_id=="10" || $stroke_play_id=="11" || $stroke_play_id=="13" || $stroke_play_id=="14"){
			$sql="SELECT * FROM `team_profile` where event_id = ".$eventId."";
			$is_team=$this->db->FetchQuery($sql);
			if(count($is_team) > 0){
				$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted='1'";
				$totalplayerrow  = $this->db->FetchQuery($queryString1);
				$totalplayer=count($totalplayerrow);
				if($totalplayer==4){

				}else{
					if($totalplayer > 4){
						$fdata['status'] = '0';
						$fdata['message'] = "More than four Player can not participate this event.";
						$error[]="More than four Player can not participate this event.";
					}else{
						$fdata['status'] = '0';
						$fdata['message'] = "Team player not accepted this event.";
						$error[]="Team player not accepted this event.";
					}
				}
			}else{
				if($stroke_play_id=="10" || $stroke_play_id=="11"){
					$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted='1'";
				    $totalplayerrow  = $this->db->FetchQuery($queryString1);
				    $totalplayer=count($totalplayerrow);
					if($totalplayer==2){

					}else{
						if($totalplayer > 2){
							$fdata['status'] = '0';
							$fdata['message'] = "More than two Player can not participate this event.";
							$error[]="More than two Player can not participate this event.";
						}else{
							$fdata['status'] = '0';
							$fdata['message'] = "Player not accepted this event.";
							$error[]="Player not accepted this event.";
						}
					}
				}else{
					$fdata['status'] = '0';
				    $fdata['message'] = "This event game format is only for team.";
				    $error[]="This event game format is only for team.";
				}

			}
		}
		if($stroke_play_id=="12"){
					$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted='1'";
				    $totalplayerrow  = $this->db->FetchQuery($queryString1);
				    $totalplayer=count($totalplayerrow);
					if($totalplayer==3){

					}else{
						if($totalplayer > 3){
							$fdata['status'] = '0';
							$fdata['message'] = "More than three Player can not participate this event.";
							$error[]="More than three Player can not participate this event.";
						}else{
							$fdata['status'] = '0';
							$fdata['message'] = "Player not accepted this event.";
							$error[]="Player not accepted this event.";
						}
					}
		}*/
		if(count($error) > 0){


		}else
		{   // call create event

		 	$notification = new createNotification();
			$notification->generatePushNotification($eventId,2,0,0);
			//sendPushNotification(); 

			$queryString12 = "update event_table set is_started = 3, last_modified_date='".date('Y-m-d H:i:s')."' where event_id = ".$eventId;
$rowValues  = $this->db->FetchRow($queryString12);
			$queryString = "select admin_id,event_name from event_list_view WHERE event_id='".$eventId."'";
			$rowValues  = $this->db->FetchRow($queryString);

			$event_name=$rowValues['event_name'];
			$admin_id=$rowValues['admin_id'];

			$subject=$event_name;
			$message="Started";
			//$queryString1 = "select player_id from event_player_list where event_id = ".$eventId." and is_accepted='1'";
			$queryString1 = "select player_id,team_id from event_player_list where event_id = ".$eventId." and is_accepted='1' order by team_id asc,case when player_id = '{$admin_id}' then 1 else 2 end";
			$queryResult1  = $this->db->FetchQuery($queryString1);
			$queryStringw = "delete from event_score_calc where event_id =".$eventId."" ;
			$this->db->FetchQuery($queryStringw);
			if(count($queryResult1)) {
				$player_order = $last_team_id = 0;
				$team_order_arr = array();
				foreach($queryResult1 as $i=>$rowValues1) {
					if($rowValues1['team_id'] > 0){
						if($i == 0) {
							$team_order_arr[$rowValues1['team_id']] = 1;
						}
						else if(!isset($team_order_arr[$rowValues1['team_id']])) {
							$team_order_arr[$rowValues1['team_id']] = ($last_team_id == $rowValues1['team_id']) ? 1 : 2;
						}
						$last_team_id = $rowValues1['team_id'];
					}
				}
				
				$score_class = new Score();
				
				foreach($queryResult1 as $i=>$rowValues1) {
					$player_order = $i+1;
					$player_color = '';
					
					if($stroke_play_id < 10) { // old formats
						$player_color = $score_class->setColorForPlayer(0,'',2);
					}
					elseif($stroke_play_id == 12) { // 4-2-0
						$player_color = $score_class->setColorForPlayer(0,'',$player_order);
					}
					else {
						if($rowValues1['team_id'] > 0) {
							$player_color = $score_class->setColorForPlayer(0,'',$team_order_arr[$rowValues1['team_id']]);
						}
						else {
							$player_color = $score_class->setColorForPlayer(0,'',$player_order);
						}
					}
					
					$queryString = "INSERT INTO event_score_calc (event_id, player_id, player_order, player_color) VALUES (".$eventId.",".$rowValues1['player_id'].",".$player_order.",'".$player_color."')";
					$this->db->FetchQuery($queryString);
				}
				
				foreach($queryResult1 as $i=>$rowValues1) {
					$inlist = array('event_score_calc_no_of_putt','event_score_calc_fairway','event_score_calc_sand','event_score_calc_closest_feet','event_score_calc_closest_inch');
					
					foreach($inlist as $k){
						$queryString = "INSERT INTO ".$k."(`event_id`, `player_id`) VALUES (".$eventId.",".$rowValues1['player_id'].")";
						$this->db->FetchQuery($queryString);
					}
					//SendAlert($admin_id,$rowValues1['player_id'],$subject,$message,$eventId);
				}
			}



			$queryString = " select format_id from event_table where event_id = ".$eventId;
			$formatid  = $this->db->FetchSingleValue($queryString);
			
			$new_format_ids_arr = array(10,11,12,13,14);
			
			$new_format_game = (in_array($formatid,$new_format_ids_arr)) ? true : false;

			$queryString = " update event_score_calc set format_id= ".$formatid." where event_id =".$eventId;;
			$queryResult  = $this->db->FetchQuery($queryString);


			$queryString2 = "select handicap_value, participant_id from golf_course_user_handicap
			where golf_course_user_handicap.event_id = ".$eventId;
			$queryResult2  = $this->db->FetchQuery($queryString2);

			if(count($queryResult2) > 0)
			{
				foreach($queryResult2 as $i=>$rowValues2)
				{
					//$queryString3 = "update event_score_calc set  handicap_value =".($rowValues2['handicap_value']).",calculated_handicap =".($rowValues2['handicap_value'])." where player_id = ".$rowValues2['participant_id']." and event_score_calc.event_id = ".$eventId;
					//$queryResult  = $this->db->FetchQuery($queryString3);

					$hadicap_3_4_value=round( ( $rowValues2['handicap_value'] * .75 ) , 0 );

					$queryString4 = "update event_score_calc set handicap_value =".($rowValues2['handicap_value']).",calculated_handicap =".($rowValues2['handicap_value']).",handicap_value_3_4=".$hadicap_3_4_value." where event_score_calc.player_id = ".$rowValues2['participant_id']." and event_score_calc.event_id = ".$eventId;
					$queryResult  = $this->db->FetchQuery($queryString4);
				}
			}
			
			if($new_format_game){

				$sql="select min(handicap_value) as minvalue from event_score_calc where event_id = ".$eventId;
				$minvalue  = $this->db->FetchSingleValue($sql);
				$sql="select handicap_value,player_id from event_score_calc where handicap_value!='".$minvalue."' and event_id = ".$eventId;
				$queryRes  = $this->db->FetchQuery($sql);
				$jxq=0;
				if(count($queryRes) > 0){
					foreach($queryRes as $i=>$rowVal){
						$calculated_handicap= ($new_format_game) ? ($rowVal['handicap_value']-$minvalue) : $rowVal['handicap_value'];
						$queryString4 = "update event_score_calc set calculated_handicap=".$calculated_handicap." where event_score_calc.player_id = ".$rowVal['player_id']." and event_score_calc.event_id = ".$eventId;
						$queryResult  = $this->db->FetchQuery($queryString4);
						$jxq++;
					}
				}
				if($new_format_game) {
					$queryString4 = "update event_score_calc set calculated_handicap='0' where handicap_value='".$minvalue."' and event_score_calc.event_id = ".$eventId;
					$queryResult  = $this->db->FetchQuery($queryString4);
				}
				else if($jxq == 0) {
					$queryString4 = "update event_score_calc set calculated_handicap='{$minvalue}' where handicap_value='".$minvalue."' and event_score_calc.event_id = ".$eventId;
					$queryResult  = $this->db->FetchQuery($queryString4);
				}
			}

				$queryString = "delete from temp_event_score_entry where event_id = ".$eventId."";
				$queryResult  = $this->db->FetchQuery($queryString);

				$queryString = "insert into temp_event_score_entry select * FROM event_score_calc where event_id=".$eventId;
				$queryResult  = $this->db->FetchQuery($queryString);

				$queryString2 = "select event_id from event_score_calc where event_id=".$eventId;
				$queryResult2  = $this->db->FetchQuery($queryString2);

				if(count($queryResult2)>0)
				{
					foreach($queryResult2 as $i=>$rowValues)
					{
						if($rowValues == 1)
						{
							$startArray = "Start Sucessfully";
						}
						else if($rowValues == 0)
						{
							$startArray = "There is some error please contact Event Admin";
						}
					}

					if(count($contact_list) > 0){
						foreach($data['contact_list'][0] as $contact){
							if($contact!=""){

								$from = "8802929382";
								$no =rand();
								$msg = 'Your event '.$event_name.' has just started. To start your round use putt2gether app.'.$no;
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL,"http://203.212.70.200/smpp/sendsms");
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS,"username=scplhttp&password=scpl1234&to=".$contact."&from=".$from."&udh=&text=".$msg."&dlr-mask=19&dlr-url");
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								$server_output = curl_exec ($ch);
								curl_close ($ch);
							}
						}
					}
					$startArray = "Your event started successfully";

					$fdata['status'] = '1';
					$fdata['message'] = $startArray;

				}
				else
				{
					$fdata['status'] = '0';
					$fdata['message'] = 'Event Not Started Successfully';

				}
		}
		return $fdata ;

    }


	function UpComingEventList($data){
		$fdata=array();

		$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
		$golf_course_id=(isset($data['golf_course_id']) && $data['golf_course_id'] > 0)?$data['golf_course_id']:"";
		$current_date=(isset($data['current_date']) && $data['current_date']!='')?$data['current_date']:"";
		$myevent=(isset($data['myevent']) && $data['myevent']!='')?$data['myevent']:0;
		$today_date=date('Y-m-d');
		$month=date('m',strtotime($current_date));
		$weekStartDate = date('Y-m-d',strtotime("last Sunday", strtotime($current_date)));

		if($user_id=="" || $current_date==""){
			$fdata['status'] = '0';
			$fdata['message'] = 'Required field not found';

		}
		else{

			$condition=" and e.is_started not in(3,2,4)";

			if(is_numeric($golf_course_id) && $golf_course_id>0 ){
				$condition.=" and e.golf_course_id='".$golf_course_id."'";
			}

			$queryString = "select e.event_id,e.event_name,e.event_display_number,DATE(e.event_start_date_time) as start_date,e.event_start_time,e.golf_course_name,e.admin_id,u.full_name as admin,e.tee_name,e.is_public,e.is_individual,e.format_id,e.golf_course_id from event_list_view e left join golf_users u ON u.user_id=e.admin_id WHERE DATE(e.event_start_date_time)='".$current_date."' and date(e.event_start_date_time)>='".$today_date."' ".$condition." order by e.event_start_date_time asc";

			$EventDateWiseArray = array();
			$queryResult  = $this->db->FetchQuery($queryString);
				if(count($queryResult) > 0)
				{
					foreach($queryResult as $i=>$rowValues)
					{
							$getformatequery="select format_name from game_format where format_id='".$rowValues['format_id']."' limit 1";
							$even_formate_arr  = $this->db->FetchRow($getformatequery);

							$queryString1 = " select city_id";
							$queryString1 .= " from golf_course";
							$queryString1 .= " where golf_course_id =".$rowValues['golf_course_id'];
							$golf_course_city  = $this->db->FetchSingleValue($queryString1);

							$locationQuery="select city_name from city where city_id='".$golf_course_city."'";
							$city_name_arr  = $this->db->FetchSingleValue($locationQuery);


							//$location=$city_arr["city_name"];

							$rowValues['tee_name'] =getEventTee($rowValues['tee_name']);
							$rowValues['event_format_name']=($rowValues['is_individual']=="1")?"Individual":"Team";
							$rowValues['game_format_name']=$even_formate_arr["format_name"];
							$rowValues['location']=$city_name_arr;

						$rowValues['event_type']=($rowValues['is_public']=="1")?"Public":"Private";
						$queryStringA= "select player_id,is_accepted,add_player_type,is_submit_score from event_player_list where player_id='".$user_id."' and event_id =".$rowValues['event_id']." ";

						$event_player =$this->db->FetchRow($queryStringA);

						if(count($event_player) > 0){
                               $rowValues['is_participate']=true;
								  $rowValues['add_player_type']= $event_player['add_player_type'];
							       $rowValues['is_accepted']= $event_player['is_accepted'];

						}else{
									$rowValues['is_participate']=false;
						}
						if($rowValues['admin_id']==$user_id){
						$rowValues['is_participate']=true;
						}
						unset($rowValues['is_individual']);
						unset($rowValues['is_public']);
						$EventDateWiseArray[] = $rowValues ;
					}
				}

				$weekEndDate = date('Y-m-d',strtotime("+7 days", strtotime($weekStartDate)));

				$queryString = "select e.event_id,e.event_name,e.event_display_number,DATE(e.event_start_date_time) as start_date,e.event_start_time,e.golf_course_name,e.admin_id,u.full_name as admin,e.tee_name,e.is_public,e.is_individual,e.format_id,e.golf_course_id from event_list_view e left join golf_users u ON u.user_id=e.admin_id WHERE DATE(e.event_start_date_time) >= '".$today_date."' and DATE(e.event_start_date_time) <='".$weekEndDate."' ".$condition." order by e.event_start_date_time asc";
				$EventWeekWiseArray = array();
				$queryResult =$this->db->FetchQuery($queryString);

				if(count($queryResult) > 0)
				{
					foreach($queryResult as $i=>$rowValues)
					{
						$rowValues['tee_name'] =getEventTee($rowValues['tee_name']);

						$getformatequery="select format_name from game_format where format_id='".$rowValues['format_id']."' limit 1 ";
						$even_formate_arr  = $this->db->FetchRow($getformatequery);

						$queryString1 = " select city_id";
						$queryString1 .= " from golf_course";
						$queryString1 .= " where golf_course_id =".$rowValues['golf_course_id'];
						$golf_course_city  = $this->db->FetchSingleValue($queryString1);

						$locationQuery="select city_name from city where city_id='".$golf_course_city."'";
						$city_name_arr  = $this->db->FetchSingleValue($locationQuery);

						$rowValues['event_format_name']=($rowValues['is_individual']=="1")?"Individual":"Team";
						$rowValues['game_format_name']=$even_formate_arr["format_name"];
						$rowValues['location']=$city_name_arr;



						///$rowValues['format_name']=($rowValues['is_individual']=="1")?"Individual":"Team";
						$rowValues['event_type']=($rowValues['is_public']=="1")?"Public":"Private";
						$queryStringA= "select player_id from event_player_list where player_id='".$user_id."' and  is_accepted='1' and event_id =".$rowValues['event_id']." ";
						$rsContentDetailA  = $this->db->FetchRow($queryStringA);
						if(count($rsContentDetailA) > 0){
									$rowValues['is_participate']=true;
						}else{
									$rowValues['is_participate']=false;
						}
						if($rowValues['admin_id']==$user_id){
						$rowValues['is_participate']=true;
						}
						unset($rowValues['is_individual']);
						unset($rowValues['is_public']);
						$EventWeekWiseArray[] = $rowValues ;
					}
				}




				$queryString = "select e.event_id,e.event_name,e.event_display_number,DATE(e.event_start_date_time) as start_date,e.event_start_time,e.golf_course_name,e.admin_id,u.full_name as admin,e.tee_name,e.is_public,e.is_individual,e.format_id,e.golf_course_id from event_list_view e left join golf_users u ON u.user_id=e.admin_id WHERE MONTH(e.event_start_date_time)='".$month."' and date(e.event_start_date_time)>='".$today_date."' ".$condition." order by e.event_start_date_time asc";
				$EventMonthWiseArray = array();
				$queryResult  = $this->db->FetchQuery($queryString);
				if(count($queryResult)>0)
				{
					foreach($queryResult as $i=>$rowValues)
					{
						$rowValues['tee_name'] =getEventTee($rowValues['tee_name']);
						$getformatequery="select format_name from game_format where format_id='".$rowValues['format_id']."' ";
						$even_formate_arr  = $this->db->FetchRow($getformatequery);

						$queryString1 = " select city_id";
						$queryString1 .= " from golf_course";
						$queryString1 .= " where golf_course_id =".$rowValues['golf_course_id'];
						$golf_course_city  = $this->db->FetchSingleValue($queryString1);

						$locationQuery="select city_name from city where city_id='".$golf_course_city."'";
						$golf_city_name  = $this->db->FetchSingleValue($locationQuery);

						$rowValues['event_format_name']=($rowValues['is_individual']=="1")?"Individual":"Team";
						$rowValues['game_format_name']=$even_formate_arr["format_name"];
						$rowValues['location']=$golf_city_name;

						$rowValues['event_type']=($rowValues['is_public']=="1")?"Public":"Private";
						$queryStringA= "select player_id from event_player_list where player_id='".$user_id."' and  is_accepted='1' and event_id =".$rowValues['event_id']." ";
						$rsContentDetailA  = $this->db->FetchRow($queryStringA);
						if(count($rsContentDetailA) > 0){
									$rowValues['is_participate']=true;
						}else{
									$rowValues['is_participate']=false;
						}
						if($rowValues['admin_id']==$user_id){
						$rowValues['is_participate']=true;
						}
						unset($rowValues['is_individual']);
						unset($rowValues['is_public']);
						$EventMonthWiseArray[] = $rowValues ;
					}
				}

				$data=array('TodayEventList'=>$EventDateWiseArray,'WeekEventList'=>$EventWeekWiseArray,'MonthEventList'=>$EventMonthWiseArray);
				$fdata['status'] ='1';
				$fdata['data'] =$data;
				$fdata['message'] ='Upcoming Event List';

			}
			return $fdata ;
		}
/*
function startEventForPlayers($eventId,$player_id)
            {

                if (mysqli_connect_errno())
                {
                    printf("Connect failed: %s\n", mysqli_connect_error());
                    exit();
                }
                $format ="json";
                $startArray = array();
				$queryString = " select golf_course_id,event_name, is_handicap,format_id,is_started";
				$queryString .= " from event_table";
				$queryString .= " where event_id =".$eventId;
				$result = mysql_query($queryString) or die (mysql_error());
				list($golf_course_id,$is_handicap,$event_name,$stroke_play_id,$is_started) = mysql_fetch_row($result);
				$error=array();
				if($is_started=="4")
				{
					$error[]='Event closed.';
				}
				if($is_started=="2")
				{
					$error[]='Event deleted.';
				}
				$queryString = " select num_hole ";
				$queryString .= " from golf_hole_index";
				$queryString .= " where golf_course_id =".$golf_course_id;
				$queryResult = mysql_query($queryString) or die (mysql_error());
				if(mysql_num_rows($queryResult)==0)
				{
					$error[]='Golf Course must have par value & index value.';
				}
				$playerHandicap=0;
				$queryString = "select handicap_value from golf_course_user_handicap where  handicap_value > 0 ";
				$queryString .= " and event_id = ".$eventId;
				$queryString .= " and participant_id='".$player_id."'";
				$queryResult = mysql_query($queryString) or die(mysql_error());
				if(mysql_num_rows($queryResult) > 0)
				{
					list($handicap_value) = mysql_fetch_row($queryResult);
				}
				else
				{
					$sqlA="select self_handicap from user_profile where user_id='".$player_id."' limit 1";
					$resultA = mysql_query($sqlA) or die (mysql_error());
					$rowValuesA = mysql_fetch_assoc($resultA);
					$handicap_value=isset($rowValuesA['self_handicap'])?$rowValuesA['self_handicap']:"";
					if($handicap_value > 0)
					{
						$queryString = "insert into golf_course_user_handicap(";
						$queryString .= " event_id, golf_course_id,participant_id, handicap_value,ip_address)";
						$queryString .= " values (";
						$queryString .= "'".trim($eventId)."',";
						$queryString .= "'".trim($golf_course_id)."',";
						$queryString .= $player_id.",";
						$queryString .= "'".$handicap_value."',";
						$queryString .= "'".$_SERVER['REMOTE_ADDR'];
						$queryString .="')";
						mysql_query($queryString) or die("G Handicap Creation Failed:" . mysql_error());
					}
					else
					{
						$playerHandicap++;
					}
				}
				$queryString = "select event_score_calc_id from event_score_calc where event_score_calc.event_id = ";
				$queryString .= $eventId;
				$queryString .= " and player_id =";
				$queryString .= $player_id;
				$queryResult = mysql_query($queryString) or die(mysql_error());
				list($event_score_calc_id) = mysql_fetch_row($queryResult);
				if(empty($event_score_calc_id))
				{
					$queryString = " insert into event_score_calc(event_id,player_id,current_position) values(".$eventId.",".$player_id.",999)";
					$queryResult = mysql_query($queryString) or die(mysql_error());
					$event_score_calc_id = mysql_insert_id();

 					$queryString = " select format_id from event_table where event_table.event_id = ".$eventId;
					$result = mysql_query($queryString) or die (mysql_error());
					list($formatid) = mysql_fetch_row($result);
					$queryString = " update event_score_calc set format_id= ".$formatid.",";
					$queryString .= " handicap_value =". $handicap_value.",";
					$queryString .= " handicap_value_3_4=calculatehandicap3_4(".$handicap_value.",.75)";
					$queryString .= " where event_score_calc_id =".$event_score_calc_id;
					$queryResult = mysql_query($queryString) or die(mysql_error());
				}
				$queryString = "select temp_event_score_id from temp_event_score_entry where event_id = ";
				$queryString .= $eventId;
				$queryString .= " and player_id =";
				$queryString .= $player_id;
				$queryResult = mysql_query($queryString) or die(mysql_error());
				list($temp_event_score_id) = mysql_fetch_row($queryResult);
				if(empty($temp_event_score_id))
				{
					$queryString = " select * FROM event_score_calc where ";
					$queryString .= " event_id=".$eventId;
					$queryString .= " and player_id=".$player_id;
					$queryResult = mysql_query($queryString) or die(mysql_error());
				}
        }   */
	function AcceptRejectEventRequest($data)
	{
		$fdata = array();
           $eventId=isset($data['event_id'])?$data['event_id']:"";
           $player_id=isset($data['player_id'])?$data['player_id']:"";
		   $admin_id=isset($data['admin_id'])?$data['admin_id']:"";
		   $status=isset($data['status'])?$data['status']:"";  //1=accepeted, 2= rejected
            if($eventId!="" && $admin_id!="" && $player_id!=""){
			   $error='';
			   $isPlayerExist = 0;
			   $queryString = "select is_accepted from event_player_list where event_id ='".$eventId."' and player_id ='".$player_id."' and event_admin_id ='".$admin_id."'";
			   $queryResult  = $this->db->FetchRow($queryString);
				if(is_array($queryResult) && count($queryResult)>0)
				{
					$is_accepted = $queryResult['is_accepted'];
					$sql="select event_name,admin_id,DATE(event_start_date_time) as event_start_date,is_started from event_table where event_id='".$eventId."'";
					$result  = $this->db->FetchRow($sql);

					$event_name = $result['event_name'];
					$admin_id = $result['admin_id'];
					$event_start_date = $result['event_start_date'];
					$is_started = $result['is_started'];

					if($status=="1"){

					if($is_started=="3")
					{
						$fdata['status'] = "0";
						$fdata['message'] = "Event already started on date. \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));
						$this->startEventForPlayer($eventId,$player_id);
					}else{
					$sqlQuery="SELECT e.event_id FROM event_table e left join event_player_list p ON p.event_id=e.event_id WHERE p.player_id='".$player_id."' and DATE(e.event_start_date_time)='".$event_start_date."' and p.is_accepted='1' and p.is_submit_score='0' and p.event_id ='".$eventId."'";
				 $sqlresult  = $this->db->FetchSingleValue($sqlQuery);
					if($sqlresult > 0){
						$error='1';
						$fdata['status'] = "0";
						$fdata['message'] = "You have already accepted an event on the same date. \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));

					}
					else
					{
						 $sql="update event_player_list set is_accepted='1',accepted_by='".$admin_id."',accepted_date=now()  where event_id ='".$eventId."' and player_id ='".$player_id."'";
						$sqlresult  = $this->db->FetchQuery($sql);
						 $notification = new createNotification();
						$notification->generatePushNotification($eventId,8,$player_id,'');
						//sendPushNotification(); 

						$fdata['status'] = "1";
						$fdata['message'] = "Accepted";
						$alertsubject='<strong>'.$event_name.'</strong> Accepted By ';
					}
					}
				 }else{
					  if($is_started=="3"){
						  $error='1';
							$fdata['status'] = "0";
							$fdata['message'] = "Event already started on date. \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));
						}elseif($is_accepted=="2"){
							$error='1';
							$fdata['status'] = "0";
							$fdata['message'] = "You have already rejected this event . \n".$event_name. ' '. date("d M Y",strtotime($event_start_date));
					  }else{
					  		$sql="update event_player_list set is_accepted='2' where event_id ='".$eventId."' and player_id ='".$player_id."'";
							$sqlresult  = $this->db->FetchQuery($sql);
							$inlist = array('event_score_calc','event_score_calc_no_of_putt','event_score_calc_fairway','event_score_calc_sand','event_score_calc_closest_feet','event_score_calc_closest_inch');
							foreach($inlist as $k){
							  $queryString = "delete from ".$k." where event_id ='".$eventId."' and player_id ='".$player_id."'";
							  $this->db->FetchQuery($queryString);
							}
							$fdata['status'] = "1";
							$fdata['message'] = "Rejected";

					  $notification = new createNotification();
						$notification->generatePushNotification($eventId,8,$player_id,'');
						//sendPushNotification(); 

					  $alertsubject='<strong>'.$event_name.'</strong> Rejected By ';
						}
				   }

				   ///////////////////////////Send Alert////////////////////////////
					$sql="select full_name from golf_users where user_id='".$userId."'";
					$player_name  = $this->db->FetchSingleValue($sql);
					$alertmessage="Event Request ".$alertsubject." <strong>".$player_name."</strong>";
					//SendAlert($userId,$admin_id,$alertsubject.$player_name,$alertmessage);
					///////////////////////////Send Alert////////////////////////////

				}else{
					$fdata['status'] = "0";
					$fdata['message'] = "Request not found";

				}
		   }else{
			  $fdata['status'] = "0";
					$fdata['message'] = "Required field not found";

		   }
	return $fdata;
	}

/*function getEventPerYearMonth($data){
		$fdata = array();
		$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
		$golf_course_id=(isset($data['golf_course_id']) && $data['golf_course_id'] > 0)?$data['golf_course_id']:"";
		$month=(isset($data['month']) && $data['month']!='')?$data['month']:date('m');
		$year=(isset($data['year']) && $data['year']!='')?$data['year']:date('Y');
		if($golf_course_id > 0){

			$queryString = "select DATE(e.event_start_date_time) as start_date from event_table e WHERE MONTH(e.event_start_date_time)='".$month."' and YEAR(e.event_start_date_time)>='".$year."' and golf_course_id = ".$golf_course_id." and is_public='1' group by start_date order by e.event_start_date_time asc";
			$queryResult  = $this->db->FetchQuery($queryString);

			if(is_array($queryResult) && count($queryResult)>0){
				foreach($queryResult as $i=>$e){
					$q="select event_id,(select count(*) from event_player_list where player_id ='".$user_id."' and event_id=e.event_id  and is_accepted in (0,1)) as request_to_participate_event from event_table e where e.is_public='1' and e.is_started in (0,1) and e.golf_course_id='".$golf_course_id."' and DATE(e.event_start_date_time)='".$e['start_date']."'";
					$resdata = $this->db->FetchQuery($q);
					$request_to_participate=0;
					if(count($resdata)>0){
					  foreach($resdata as $gp=>$grow){
						$total_event++;
						if($grow['request_to_participate_event']==0){
							$request_to_participate++;
						}
					  }
					}
					$e = date('d',strtotime($e['start_date']));
					 //check total event on this date and user total participate event on this date

					if($request_to_participate > 0){
					$edata[] = $e;
					}
				}
				$fdata['status'] = '1';
				$fdata['data'] = $edata;
				$fdata['message'] = 'Success';
			}else{
				$fdata['status'] = '0';
				$fdata['message'] = 'No Event Found';
			}
		}else{
			$fdata['status'] = '0';
			$fdata['message'] = 'Required field not found';
		}
		return $fdata ;
	}*/
	function getEventPerYearMonth($data){
			$fdata = array(); $event=array();
$player_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
			$user_id=(isset($data['admin_id']) && $data['admin_id'] > 0)?$data['admin_id']:$player_id;
			$golf_course_id=(isset($data['golf_course_id']) && $data['golf_course_id'] > 0)?$data['golf_course_id']:"";
			$month=(isset($data['month']) && $data['month']!='')?$data['month']:date('m');
			$year=(isset($data['year']) && $data['year']!='')?$data['year']:date('Y');
$current_date=date("Y-m-d");
			if($golf_course_id > 0){

		$queryString = "select e.event_id,DATE(e.event_start_date_time) as start_date from event_table e WHERE  MONTH(e.event_start_date_time)='".$month."' and YEAR(e.event_start_date_time)>='".$year."' and golf_course_id = ".$golf_course_id." and e.is_started not in(2,3,4) and is_public='1' AND DATE(e.event_start_date_time) >= '".$current_date."' order by e.event_start_date_time asc";

				$queryResult  = $this->db->FetchQuery($queryString);
			if(count($queryResult)>0){
				foreach($queryResult as $i=>$e){
					//check if participate then not display detail
					if($user_id > 0){
					$q="select count(1) as c from event_player_list where (player_id ='".$user_id."' and event_id='".$e['event_id']."') and ((case when (add_player_type != '0') THEN is_accepted = 1 else (player_id>0 and is_accepted in (0,1)) END))"; 
						 $resdata = $this->db->FetchSingleValue($q);
if(isset($resdata) && $resdata>0){
						}else{
							
						$event[] =date('d',strtotime($e['start_date']));
						}
					}else{
					//$event[] =$e;
					}
				} 
				$fdata['status'] = '1';
				$fdata['data'] = $event;
				$fdata['message'] = 'Listing';
			}else{
					$fdata['status'] = '0';
                                        $fdata['data'] = $event;
					$fdata['message'] = 'No Event Found';
				}
			}else{
				$fdata['status'] = '0';
                                $fdata['data'] = $event;
				$fdata['message'] = 'Required field not found';
			}
			return $fdata ;
		}
		function MyEventHistory($data){
			$fdata= array();
			$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
			$flag=(isset($data['flag']) && $data['flag'] > 0)?$data['flag']:"";
			if($user_id==""){
				$fdata['status'] = '0';
				$fdata['message'] = 'Required field not found';
				$historyArray = $fdata;
			}
			else {
				$current_date=date("Y-m-d");
				$queryString = "select sc.no_of_holes_played,e.event_id,e.format_id,(select count(player_id) from event_player_list where event_id = e.event_id and is_accepted=1) as no_of_player_accepted,(select count(player_id) from event_player_list where event_id = e.event_id) as no_of_player,e.total_hole_num,e.is_started,e.event_name,DATE(e.event_start_date_time) as event_start_date,e.event_start_time,e.golf_course_name from event_list_view e left join event_player_list epl on epl.event_id=e.event_id and epl.player_id='{$user_id}' left join golf_users u ON u.user_id=epl.player_id  left join event_score_calc sc on sc.event_id = epl.event_id and sc.player_id =epl.player_id  WHERE 1=1 AND epl.is_submit_score='1'  /*and sc.no_of_holes_played = 18 and e.total_hole_num=18*/ order by epl.submit_score_date desc";
				$historyArray = $evn = array();
				$queryResult  = $this->db->FetchQuery($queryString);
				if(count($queryResult) > 0) {
					foreach($queryResult as $i=>$row) {
						$rowValues = array();
						$event_id = $row['event_id'];
						if($row['no_of_holes_played'] == 18 && $row['total_hole_num'] == 18) {
							$evn[] = $event_id;
						}
						
						
						$rowValues['gross_score']= 0;
						$rowValues['no_of_birdies']= 0;
						$rowValues['eagle']= 0;
						$rowValues['no_of_pars']= 0;
						$rowValues['event_id']= $row['event_id'];
						$rowValues['format_id']= $row['format_id'];
						$rowValues['event_name']= $row['event_name'];
						$rowValues['golf_course_name']= $row['golf_course_name'];
						$rowValues['format_name']= $rowValues['format_id'];
						$rowValues['no_of_player_accepted']= $row['no_of_player_accepted'];
						$rowValues['no_of_player']= ($row['no_of_player']>4) ? "4+" : strval($row['no_of_player']); $rowValues['no_of_player_ios']= ($row['no_of_player']>4) ? "5" : strval($row['no_of_player']);
						
						$rowValues['event_start_date']= date('d-M-y', strtotime($row['event_start_date']));
						
						$query1 = "select total_score,CONCAT(lb_display_string,current_position) as current_position from event_score_calc where player_id ='{$user_id}' and event_id ='{$event_id}' limit 1";
						$row_calc  = $this->db->FetchRow($query1);
						
						$row_calc['total_score'] = (isset($row_calc['total_score']) && is_numeric($row_calc['total_score']) && $row_calc['total_score']>0) ? $row_calc['total_score'] : 0;
						$row_calc['current_position'] = (isset($row_calc['current_position']) && trim($row_calc['current_position'])!='') ? $row_calc['current_position'] : '';
						
						if($row_calc['current_position'] == '1' || $row_calc['current_position'] == 'T1'){
							$rowValues['current_ranking']= 'Winner';
						}
						else {
							$rowValues['current_ranking']= $row_calc['current_position'];
						}
						
						$rowValues['current_position']= $row_calc['current_position'];
						$rowValues['total']= $row_calc['total_score'];
						
						$historyArray[$event_id] = $rowValues;
					}
					//print_r($evn);die;
					$mul_event = implode(',',$evn); 
					
					//echo $query1 = "select min(total_score) as gross_score,event_id from event_score_calc where player_id ='{$user_id}' and event_id in (".$mul_event.") group by event_id order by event_id asc limit 1";
					$query1 = "select (concat(total_score,'.',event_id)) as gross_score from event_score_calc where player_id ='{$user_id}' and event_id in (".$mul_event.")";
					$rowValues1  = $this->db->FetchQuery($query1);
					//print_r($rowValues1);die;
					$par_arr = array();
					foreach($rowValues1 as $a=>$b) {
						$par_arr[] = ($b['gross_score']);
					} //print_r($par_arr);die;
					$rowValues1['gross_score'] = min($par_arr);//die;
					
					
					
					$exp = trim($rowValues1['gross_score'])!='' ? explode('.',$rowValues1['gross_score']) : array();
					if(is_array($exp) && count($exp)==2) {
						$min_gross_score = $exp[0];
						$min_event_id = $exp[1];
						
						foreach($par_arr as $b=>$c) {
							$za = explode('.',$c);
							if($za[0] == $min_gross_score) {
								if($za[1] > $min_event_id) {
									$min_event_id = $za[1];
								}
							}
						}
						
						$rowValues1['gross_score'] = $min_gross_score;
						$rowValues1['event_id'] = $min_event_id;
					}
					//print_r($rowValues1);die;
					if(isset($rowValues1['event_id']) && is_numeric($rowValues1['event_id']) && is_numeric($rowValues1['gross_score'])) {
						$historyArray[$rowValues1['event_id']]['gross_score'] = 1;
					}
					
					$query3 = "select (concat(no_of_birdies,'.',event_id)) as no_of_birdies from event_score_calc where player_id ='{$user_id}' and event_id in (".$mul_event.")";
					$rowValues3 = $this->db->FetchQuery($query3);
					
					$par_arr = array();
					foreach($rowValues3 as $a=>$b) {
						$par_arr[] = ($b['no_of_birdies']);
					}
					$rowValues3['no_of_birdies'] = max($par_arr);//die;
					
					$exp = trim($rowValues3['no_of_birdies'])!='' ? explode('.',$rowValues3['no_of_birdies']) : array();
					if(is_array($exp) && count($exp)==2) {
						$rowValues3['no_of_birdies'] = $exp[0];
						$rowValues3['event_id'] = $exp[1];
					}
					if(isset($rowValues3['event_id']) && is_numeric($rowValues3['event_id']) && $rowValues3['no_of_birdies']>0) {
						$historyArray[$rowValues3['event_id']]['no_of_birdies'] = 1;
					}
					
					$query4 = "select concat(no_of_pars,'.',event_id) as no_of_pars from event_score_calc where player_id ='{$user_id}' and event_id in (".$mul_event.")"; //echo $query4;die;
					$rowValues4 = $this->db->FetchQuery($query4);
					
					$par_arr = array();
					foreach($rowValues4 as $a=>$b) {
						$par_arr[] = ($b['no_of_pars']);
					}
					$rowValues4['no_of_pars'] = max($par_arr);//die;
					
					$exp = trim($rowValues4['no_of_pars'])!='' ? explode('.',$rowValues4['no_of_pars']) : array();
					if(is_array($exp) && count($exp)==2) {
						$rowValues4['no_of_pars'] = $exp[0];
						$rowValues4['event_id'] = $exp[1];
					}
					if(isset($rowValues4['event_id']) && is_numeric($rowValues4['event_id']) && $rowValues4['no_of_pars']>0) {
						$historyArray[$rowValues4['event_id']]['no_of_pars'] = 1;
					}
					
					$query2 = "select group_concat(distinct event_id) as event_id from event_score_calc where
					player_id ='{$user_id}' and event_id in (".$mul_event.") and no_of_eagle != 0 group by event_id order by no_of_eagle desc "; 
					$rowValues2  = $this->db->FetchRow($query2);
					
					$eagle_events = (isset($rowValues2['event_id']) && trim($rowValues2['event_id'])!='') ? explode(",",$rowValues2['event_id']) : array();
					
					if(count($eagle_events)>0) {
						foreach($eagle_events as $a) {
							$historyArray[$a]['eagle'] = 1;
						}
					}
					$fdata['status'] = '1';
					$fdata['data'] = array_values($historyArray);
					$fdata['message'] = 'Event History List';
					$historyArray = $fdata;
				}
				else {
					$fdata['status'] = '0';
					$fdata['message'] = 'Event History Empty';
					$historyArray = $fdata;
				}
			}
			return $historyArray;
		}

function DeleteEventHistory($data){
				$fdata =array();
				$event_id=(isset($data['event_id']) && $data['event_id'] > 0)?$data['event_id']:"";
				$user_id=(isset($data['user_id']) && $data['user_id'] > 0)?$data['user_id']:"";
				if($event_id=="" && $user_id==""){
					$fdata['status'] = '0';
					$fdata['message'] = 'Required field not found';

				}else{
					 $queryString = "select admin_id,event_name from event_list_view WHERE admin_id='".$user_id."' and event_id='".$event_id."'";
					$historyArray = array();
					$rowValues  = $this->db->FetchRow($queryString);

					if(is_array($rowValues)){
						$event_name=$rowValues['event_name'];
						$sql="update event_table set is_started='2' where event_id='".$event_id."'";
						$this->db->FetchQuery($sql);

						$subject=$event_name;
						$message="Deleted";
						$queryString1 = "select player_id from event_player_list where event_id = ".$event_id." and is_accepted='1'";
						$queryResult1 = $this->db->FetchQuery($queryString1);
						if(count($queryResult1))
						{
							foreach($queryResult1 as $i=>$rowValues1)
							{
							//SendAlert($user_id,$rowValues1['player_id'],$subject,$message,$event_id);
							}
						}
						$fdata['status'] = '1';
						$fdata['message'] = 'Event Deleted.';

					}else{
						$fdata['status'] = '0';
						$fdata['message'] = 'Only Admin can delete this event.';
					}
				}
				return $fdata;
		}
function DashboardUpcomingEvent($data){
			$admin_id = (isset($data['admin_id']) && $data['admin_id'] >0)?$data['admin_id']:0;
			//$noof_event = (isset($data['no_of_event']) && $data['no_of_event'] >0)?$data['no_of_event']:1;
			$today_date=date('Y-m-d');
			if($admin_id > 0){
				
				// check if running event is exist or not
				$query = 'SELECT v.event_id,v.event_name,v.golf_course_name,v.event_start_date_time FROM event_list_view v left join event_player_list p ON p.event_id=v.event_id WHERE v.is_started = "3" and p.player_id="'.$admin_id .'" and p.is_submit_score = "0" and p.is_accepted="1" order by p.last_score_enter_on desc';
				$eventlist = $this->db->FetchRow($query);
				
				if(isset($eventlist['event_id']) && $eventlist['event_id']>0) {
					$eventlist['is_resume'] = '1';
				}
				else {
					$query = 'SELECT v.event_id,v.event_name,v.golf_course_name,v.event_start_date_time FROM event_list_view v left join event_player_list p ON p.event_id=v.event_id WHERE v.is_started not in(2,4) AND date(v.event_start_date_time)>="'.$today_date.'" and p.player_id="'.$admin_id .'" and p.is_submit_score = "0" and p.is_accepted!="2" order by v.event_start_date_time asc';
					$eventlist = $this->db->FetchRow($query);
					$eventlist['is_resume'] = '0';
				}
				
				if(is_array($eventlist)>0){
					$eventlist['start_date'] = date('d F Y',strtotime($eventlist['event_start_date_time']));
					unset($eventlist['event_start_date_time']);
					$fdata['status'] = '1';
					$fdata['data'] = $eventlist;
					$fdata['message'] = 'Upcoming Event List';
				}else{
					$fdata['status'] = '0';
					$fdata['message'] = 'Upcoming Event Not Found';
				}
				
				$chk_Otp='select count(1) as c from push_notification_user_list where user_id="'.$admin_id.'" and is_read_by_user="0"';  
			    $noti_count  = $this->db->FetchSingleValue($chk_Otp);
			    $fdata['notifications_count'] = ($noti_count > 0) ? '1' : '0';
				
			}else{
				$fdata['status'] = '0';
				$fdata['message'] = 'Required Field Not Found';
			}
			return $fdata;
		}
		
		function getAdvertisementBanner($data) { 
			
			$format ="json";
			$event_id = isset($data['event_id']) ? $data['event_id'] : "0";
			$type = isset($data['type']) ? $data['type'] : "";
			if($event_id>=0 && (is_numeric($type) && $type>0)) {
				$random = isset($data['random']) ? $data['random'] : "1";
				$limit = isset($data['limit']) ? $data['limit'] : "1";
				$order_str = ($random=='1') ? 'order by rand()' : 'order by id desc';
				$limit_str = ($limit>'1') ? "limit {$limit}" : 'limit 1';
				$return_arr = array();
				$event_str = ($event_id>0) ? "event_id = '{$event_id}' and" : '';
				$sql = "select id,event_id,type,title,image_path,image_href,is_active from golf_banners where {$event_str} type = '{$type}' and image_path is not null {$order_str} {$limit_str}";
				$result = $this->db->FetchQuery($sql);
				$numCount = count($result);

				if($numCount>0) {
					foreach($result as $row) {
						//$row['image_path']=str_replace("http://","https://",BASE_URL)."banners/".$row['image_path'];
						$row['image_path']=__BASE_URI__."uploads/banner/".$row['image_path'];
						$return_arr[] = $row;
					}
					return array("status"=>'1',"data"=>$return_arr);
				}
				else {
					return array("status"=>'0',"message"=>"No Banner Found");
				}
			}
			else {
				return array("status"=>'0',"message"=>"Required Fields Not Found");
			}
		}

}
?>