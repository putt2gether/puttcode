<?php 
require_once(dirname(__FILE__).'/../config/db_config.php');
$output_array = array();
$output_header = "output";

if(isset($_GET['method'])){

	$jsonData = file_get_contents('php://input');
	$method = strtolower($_GET['method']);
	$parser = (isset($_REQUEST['parser']) ? $_REQUEST['parser'] : 'json');
	$data=json_decode($jsonData,true);
 	//$data=$_REQUEST;
	$request_url = isset($_SERVER['REQUEST_URI']) ? ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) : "";
	$user_id = isset($data['user_id']) ? trim($data['user_id']) : "";
	$access_token = isset($data['access_token']) ? trim($data['access_token']) : "";
	/* 
	if($access_token != '' && $user_id != ''){
		$output_array = isExistAccessToken($access_token,$user_id);
		display_output($output_header,$output_array);
	
	 */
	 $api_url = $request_url;
	 $user_agent = $_SERVER['HTTP_USER_AGENT'];
	 $ip_address = $_SERVER['REMOTE_ADDR'];
$sqlQuery="insert into log set method='".$method."',log_data='".addslashes($jsonData)."',log_date_time='".date("Y-m-d H:i:s")."',api_url='".$api_url."',user_agent='".$user_agent."',ip_address='".$ip_address."'";	
	 $database->FetchQuery($sqlQuery);
	if($method=="login"){
		$users = new users();
		$output_array = $users->login($data);
		display_output($output_header,$output_array);
	}
	if($method=="logout"){
		$users = new users();
		$output_array = $users->logout($data);
		display_output($output_header,$output_array);
	}
	if($method=="register"){
		$users = new users();
		$output_array = $users->register($data);
		display_output($output_header,$output_array);
	}
	if($method=="getcountrylist"){
		$country = new Country();
		$output_array = $country->getCountryList($data);                
		display_output($output_header,$output_array);
	}
	if($method=="getcitylist"){
		$city= new City();
		$output_array = $city->getCityList($data);
		display_data($output_header,$output_array);
	}
if($method=="getstatelist"){
		$state = new State();
		$output_array = $state->getStateList($data);
		display_data($output_header,$output_array);
	}
	if($method=="forgetpassword"){
		$users = new users();
		$output_array = $users->forgotPassword($data);
		display_data($output_header,$output_array);
	}
	if($method=="verifyotp"){
		$users = new users();
		$output_array = $users->verifyotp($data);
		display_data($output_header,$output_array);
	}
	if($method=="updatepassword"){
		$users = new users();
		$output_array = $users->updatePassword($data);
		display_data($output_header,$output_array);
	}
	if($method=="getnearestgolfcourse"){
		$golf = new Golf();
		$output_array = $golf->getGolfcourseNerabyDistance($data);
		display_output($output_header,$output_array);
	}
	if($method=="getgolfcoursetee"){
		$golf = new Golf();
		$output_array = $golf->getGolfCourseTee($data);
		display_data($output_header,$output_array);
	}
	if($method=="getgolfcourselist"){
		$golf = new Golf();
		$output_array = $golf->getCityGolfCourseList($data);
		display_output($output_header,$output_array);
	}
	if($method=="getstrokeplaylist"){
		$event = new Events(); 
		$output_array = $event->geStrokePlayList($data);
		display_data($output_header,$output_array);
	}
	
	if($method=="getteelist"){
		$tee = new Tee();
		$output_array = $tee->getTeeList($data);
		display_output($output_header,$output_array);
	}
	if($method=="getteecolor"){
		$tee = new Tee();
		$output_array = $tee->getTeeColorCode($data);
		display_output($output_header,$output_array);
	}
	if($method=="geteventformatlist"){
		$event = new Events();
		$output_array = $event->geEventFormatList($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventtypelist"){
		$event = new Events();
		$output_array = $event->geEventTypeList($data);
		display_output($output_header,$output_array);
	}
	if($method=="getuserdetail"){
		$users = new users();
		$output_array = $users->getUserProfileDetail($data);
		display_data($output_header,$output_array);
	}
	if($method=="getsuggessionfriendlist"){
		$friend = new Friend();
		$output_array = $friend->getSuggessionFriendList($data);
		display_data($output_header,$output_array);
	}
	
	if($method=="createevent"){
		$event = new Events(); 
		$output_array = $event->createEvents($data);
		display_data($output_header,$output_array);
	}
	if($method=="addparticipantinevent"){
		$event = new Events(); 
		$output_array = $event->addParticipantInEvent($data);
		display_data($output_header,$output_array);
	}
	if($method=="addnewgroup"){
		$groups = new Group(); 
		$output_array = $groups->createGroup($data);
		display_data($output_header,$output_array);
	}
	if($method=="addgroupmember"){
		$groups = new Group(); 
		$output_array = $groups->addGroupMember($data);
		display_data($output_header,$output_array);
	}
	
	if($method=="getgrouplist"){
		$groups = new Group(); 
		$output_array = $groups->getGroupListing($data);
		display_data($output_header,$output_array);
	}
	if($method=="getgroupmemberbygroup"){
		$groups = new Group(); 
		$output_array = $groups->getGroupMemberListing($data);
		display_data($output_header,$output_array);
	}
	if($method=="showholenumbers"){
		$event = new Events();
		$output_array = $event->showholenumbers($data);
		display_data($output_header,$output_array);
	}
	if($method=="sociallogin"){
		$users = new users();
		$output_array = $users->socialLogin($data);
		display_output($output_header,$output_array);
	}
	if($method=="updateuserhandicap"){
		$user = new users();
		$output_array = $user->updateUserHandicap($data);
		display_data($output_header,$output_array);
	}
	if($method=="updateprofile"){
		$user = new users();
		$output_array = $user->updateProfile($data);
		display_data($output_header,$output_array);
	}
	if($method=="getstatspichart"){
		$event = new Events();
		$output_array = $event->getStatsPiChart($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventinvitationlist"){
		$event = new Events();
		$output_array = $event->getEventInvitationList($data);
		display_data($output_header,$output_array);
	}
	if($method=="accepteventinvitation"){
		$event = new Events();
		$output_array = $event->AcceptRejectEvent($data);
		display_data($output_header,$output_array);
	}
	if($method=="requesttoparticipate"){
		$event = new Events();
		$output_array = $event->RequestToParticipate($data);
		display_data($output_header,$output_array);
	}
	if($method=="eventdetail"){
		$event = new Events();
		$output_array = $event->getEventDetail($data);
		display_data($output_header,$output_array);
	}
	if($method=="getstackscorecard"){
		$event = new Events();
		$output_array = $event->getStackScoreCard($data);
		display_data($output_header,$output_array);
	}
	if($method=="getscorerlist"){
		$event = new Events();
		$output_array = $event->getScorerList($data);
		display_data($output_header,$output_array);
	}
	if($method=="createtemporarygolfcourse"){
			$golf = new Golf();
		$output_array = $golf->createTemporaryGolfCourse($data);
		display_data($output_header,$output_array);
	}
	if($method=="editevent"){
		$event = new Events();
		$output_array = $event->editEvent($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventparticipentlist"){
		$event = new Events();
		$output_array = $event->getEventParticipentList($data);
		display_data($output_header,$output_array);
	}
	if($method=="getgroupdetail"){
		$groups = new Group(); 
		$output_array = $groups->getGroupDetails($data);
		display_data($output_header,$output_array);
	}
	if($method=="editgroup"){
		$groups = new Group(); 
		$output_array = $groups->editGroup($data);
		display_data($output_header,$output_array);
	}
	if($method=="exitgroup"){
		$groups = new Group(); 
		$output_array = $groups->exitGroup($data);
		display_data($output_header,$output_array);
	}
	if($method=="deletegroup"){
		$groups = new Group(); 
		$output_array = $groups->deleteGroup($data);
		display_data($output_header,$output_array);
	}
	if($method=="getrecentgolfcourselist"){
		$golf = new Golf();
		$output_array = $golf->getRecentGolfCourseList($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventrequestlist"){
		$event = new Events();
		$output_array = $event->getEventRequestList($data);
		display_data($output_header,$output_array);
	}
	if($method=="getplayereventstatus"){
		$user = new users();
		$output_array = $user->getPlayerEventStatus($data);
		display_data($output_header,$output_array);
		
	}
	if($method=="startevent"){
		$event = new Events();
		$output_array = $event->startEvent($data);
		display_data($output_header,$output_array);	
	}
	if($method=="getupcomingeventlist"){
		$event = new Events();
		$output_array = $event->UpComingEventList($data);
		display_data($output_header,$output_array);
	}
	if($method=="accepteventrequest"){
		$event = new Events();
		$output_array = $event->AcceptRejectEventRequest($data);
		display_data($output_header,$output_array);
	}
	if($method=="getscorecarddata"){
		$score = new Score();
		$output_array = $score->getScoreCardData($data);
		display_data($output_header,$output_array);
	}
	if($method=="getparindexvalue"){
		$score = new Score();
		$output_array = $score->getParIndexvalue($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventperdate"){
		$event = new Events();
		$output_array = $event->GetEventAccordingToDate($data);
		display_data($output_header,$output_array);
	}
	if($method=="savescorecard"){
		$score = new Score();
		$output_array = $score->saveScoreCard($data);
		display_data($output_header,$output_array);
	}
	if($method=="savescorecardtemp"){
		$score = new Score();
		$output_array = $score->saveScoreCardTemp($data);
		display_data($output_header,$output_array);
	}
	if($method=="geteventperyearmonth"){
		$event = new Events();
		$output_array = $event->getEventPerYearMonth($data);
		display_data($output_header,$output_array);	
	}
	if($method=="getusernotification"){
		$alerts = new Alerts();
		$output_array = $alerts->getUserAlertsNotification($data);
		display_data($output_header,$output_array);
	}
	if($method=="getscoreboard"){
		$score = new Score();
		$output_array = $score->getScoreBoard($data);
		display_data($output_header,$output_array);
	}
	if($method=="getleaderboard"){
		$score = new Score();
		$output_array = $score->getLeaderBoard($data);
		display_data($output_header,$output_array);
	}
	if($method=="getlatestfullscore"){
		$score = new Score();
		$output_array = $score->getLatestFullScore($data);
		display_data($output_header,$output_array);
	}
	if($method=="getexpandablescoreview"){
		$score = new Score();
		$output_array = $score->getExpandableScoreView($data);
		display_data($output_header,$output_array);
	}
	if($method=="getdelegateuserlist"){
		$friend = new Friend();
		$output_array = $friend->getDelegateUserList($data);
		display_data($output_header,$output_array);
	}
	if($method=="makedelegate"){
		$friend = new Friend();
		$output_array = $friend->makeDelegate($data);
		display_data($output_header,$output_array);
	}
	if($method=="submitscore"){
		$score = new Score();
		$output_array = $score->submit_player_score($data);
		display_data($output_header,$output_array);
	}
	if($method=="endscore"){
		$score = new Score();
		$output_array = $score->end_player_score($data);
		display_data($output_header,$output_array);
	}
	if($method=="getleaderboarddata"){
		$score = new Score();
		$output_array = $score->getLeaderBoardData($data);
		display_data($output_header,$output_array);
	}
	if($method=="myeventhistory"){
		$event = new Events();
		$output_array = $event->MyEventHistory($data);
		display_data($output_header,$output_array);
	}
	if($method=="deleteeventhistory"){
		$event = new Events();
		$output_array = $event->DeleteEventHistory($data);
		display_data($output_header,$output_array);
	}
	if($method=="getindividualholescore"){
		$score = new Score();
		$output_array = $score->getIndividualHoleScore($data);
		display_data($output_header,$output_array);
	}
	if($method=="getdashboardupcomingevent"){
		$event = new Events();
		$output_array = $event->DashboardUpcomingEvent($data);
		display_data($output_header,$output_array);
	}
	if($method=="getgroupsuggessionfriendlist"){
		$group = new Group();
		$output_array = $group->getGroupSuggessionFriendList($data);
		display_data($output_header,$output_array);
	}
	if($method=="getscorestats"){
		$stats = new Stats();
		$output_array = $stats->getScoreStats($data);
		display_data($output_header,$output_array);
	}
	if($method=="getgirstats"){
		$stats = new Stats();
		$output_array = $stats->getGirPercentage($data);
		display_data($output_header,$output_array);
	}
	if($method=="getfairwaystats"){
		$stats = new Stats();
		$output_array = $stats->getFairwayPercentage($data);
		display_data($output_header,$output_array);
	}
	if($method=="getputtingstats"){
		$stats = new Stats();
		$output_array = $stats->getPuttingStats($data);
		display_data($output_header,$output_array);
	}
	if($method=="getrecoverystats"){
		$stats = new Stats();
		$output_array = $stats->getRecoveryStats($data);
		display_data($output_header,$output_array);
	}
	if($method=="getstats"){
		$stats = new Stats();
		$output_array = $stats->getStats($data);
		display_data($output_header,$output_array);
	}
	if($method=="getcountrygolfcourselist"){
		$golf = new Golf();
		$output_array = $golf->getCountryGolfCourseList($data);
		display_data($output_header,$output_array);
	}
	if($method=="viewuserprofile"){
		$user = new Users();
		$output_array = $user->ViewUserProfile($data);
		display_data($output_header,$output_array);
	}
	if($method=="addmembertomultiplegroup"){
		$groups = new Group(); 
		$output_array = $groups->addSingleMemberToMultipleGroup($data);
		display_data($output_header,$output_array);
	}
	if($method=="privacypolicy"){
		$groups = new Privacy(); 
		$output_array = $groups->getPrivacyPolicy($data);
		display_data($output_header,$output_array);
	}
	if($method=="marknotificationisread"){
		$groups = new createNotification(); 
		$output_array = $groups->markNotificationIsRead($data);
		display_data($output_header,$output_array);
	}
	if($method=="getadvbanner"){
		$groups = new Events(); 
		$output_array = $groups->getAdvertisementBanner($data);
		display_data($output_header,$output_array);
	}
	if($method=="sendscorecardmail"){
		$score = new Score(); 
		$output_array = $score->sendScorecardMail($data);
		display_data($output_header,$output_array);
	}
	if($method=="createcharts"){//print_r($_GET);die;
		$score = new Score(); 
		$output_array = $score->get_mail_charts($_GET); print_r($output_array);die;
		display_data($output_header,$output_array);
	}
	else {
		$output_array = array('status' => '0', 'data' => array('message'=>'Invalid Method Type'));
		display_output($output_header,$output_array);
	}
	/* }
	else{ 
		$output_array = isExistAccessToken($access_token,$user_id);
		display_output($output_header,$output_array);
	} */
}
else {
	$output_array = array('status' => '0', 'data' => array('message'=>'Method Not Specified'));
	display_output($output_header,$output_array);
}
?>