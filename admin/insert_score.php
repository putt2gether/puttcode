<?php ob_start();
//print_r($_POST);
if(isset($_POST['update_score']) && trim($_POST['update_score']) == '1') {
	if(isset($_POST['event_id']) && isset($_POST['player_id']) && is_array($_POST['player_id']) && count($_POST['player_id'])>0) {
		$stroke_play_id = $_POST['stroke_play_id'];
		$admin_id = $_POST['admin_id'];
		$event_id = $_POST['event_id'];
		$_POST['version'] = '2';
		$input_arr = array();
		
		foreach($_POST['player_id'] as $a=>$b) {
			$player_id = $a;
			$score_id = $b;
			
			if(isset($_POST['score'][$player_id]) && is_array($_POST['score'][$player_id]) && count($_POST['score'][$player_id])>0) {
				foreach($_POST['score'][$player_id] as $hole=>$score) {
					$last_value = isset($_POST['last_score'][$player_id][$hole]) ? $_POST['last_score'][$player_id][$hole] : 0;
					if($hole > 0 && $score > 0 && $last_value != $score) {
						$input_arr[] = array('hole_number'=>$hole, 'event_id'=>$event_id, 'stroke_play_id'=>$stroke_play_id, 'admin_id'=>$admin_id, 'player_id'=>$player_id, 'score'=>$score, 'player_group_list'=>array(array('score_1'=>$score, 'player_id_1'=>$player_id)));
					}
				}
			}
		}
		//print_r($input_arr);die;
		if(count($input_arr) >0 ) {
			
			$result = array();
			
			foreach($input_arr as $a=>$b) {
				$url = 'http://putt2gether.com/puttdemo/enterUserScore';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST,0);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($b));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				$result=curl_exec($ch); //print_r($result);
				curl_close ($ch);
			}
			
			echo json_encode(array('stat'=>1,'mess'=>'Score Updated Successfully'));exit;
			
		}
		else {
			echo json_encode(array('stat'=>0,'mess'=>'No Score Found'));exit;
		}
	}
	else {
		echo json_encode(array('stat'=>0,'mess'=>'No Player Found'));exit;
	}
}
elseif(isset($_POST['submit_score']) && trim($_POST['submit_score']) == '1') {
	//print_r($_POST);
	
	if(isset($_POST['event_id']) && $_POST['event_id']>0 && isset($_POST['player_id']) && $_POST['player_id']>0) {
		
		$b = array("event_id"=>$_POST['event_id'], "user_id"=>$_POST['player_id']);
		
		$url = 'http://putt2gether.com/puttdemo/submitscore';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST,0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($b));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$result=curl_exec($ch);
		curl_close ($ch);
		
		echo json_encode(array('stat'=>1,'mess'=>'Score Submitted Successfully'));exit;
	}
	else {
		echo json_encode(array('stat'=>0,'mess'=>'No Event/Player Found'));exit;
	}
}
else {
	echo json_encode(array('stat'=>0,'mess'=>'No Method Found'));exit;
}
?>