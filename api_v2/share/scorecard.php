<?php
require_once(dirname(__FILE__)."/../configdb.php");

$id = isset($_GET['id']) ? $_GET['id'] : "";
if(trim($id)=='') {
	err_page();
}
$json = base64_decode($id);
$arr = json_decode($json,true);
if(!is_array($arr) || count($arr)==0) {
	err_page();
}
if(!isset($arr['type']) || !isset($arr['player_id']) || !isset($arr['event_id'])) {
	err_page();
}

$player_id = $arr['player_id'];
$type = strtolower($arr['type']);
$event_id = $arr['event_id'];

if($type!="facebook" && $type!="twitter") {
	err_page();
}
if((!is_numeric($player_id) || $player_id<=0) && (!is_numeric($event_id) || $event_id<=0)) {
	err_page();
}
$html=sendScoreCard($event_id,$player_id,true,true,true);

$sql = "SELECT v.golf_course_name,v.event_name,v.event_start_date_time as event_date,u.full_name FROM `event_player_list` p inner join `event_list_view` v on p.event_id = v.event_id inner join golf_users u on p.player_id = u.user_id and p.event_id='{$event_id}' and p.player_id = '{$player_id}' group by p.player_id";

$result = mysql_query($sql);

$row = mysql_fetch_array($result);

if(!isset($row['full_name'])) {err_page();}

$event_name = ucwords($row['event_name']);
$player_name = ucwords($row['full_name']);
$golf_course_name = ucwords($row['golf_course_name']);
$event_date = date("d M Y",strtotime($row['event_date']));

$page_title = "putt2gether: {$player_name}'s official scorecard";
$page_description = "Follow the official putt2gether scorecard of {$player_name} for {$event_name} played on {$event_date} at {$golf_course_name}.";
$page_image_path = "http://putt2gether.com/puttdemo/share/logo.jpg";
$page_image_path = "http://putt2gether.com/puttdemo/images/banner.jpg";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="title" content="<?php echo $page_title?>" />
<meta name="description" content="<?php echo $page_description?>" />
<meta charset="UTF-8">
<?php if($type=="facebook") {?>
<meta property="og:url"                content="http://putt2gether.com/puttdemo/share/scorecard.php?id=<?php echo $id?>" />
<meta property="og:type"               content="article" />
<meta property="og:title"              content="<?php echo $page_title?>" />
<meta property="og:description"        content="<?php echo $page_description?>" />
<meta property="og:image"              content="<?php echo $page_image_path?>" />
<?php }elseif($type=="twitter") {?>
<meta name="twitter:card" content="<?php echo $page_description?>" />
<meta name="twitter:site" content="@putt2gether" />
<meta name="twitter:title" content="<?php echo $page_title?>" />
<meta name="twitter:image" content="<?php echo $page_image_path?>" />
<meta name="twitter:url" content="http://putt2gether.com/puttdemo/share/scorecard.php?id=<?php echo $id?>" />
<?php }?>

<title><?php echo $page_title?></title>
</head>
<body style="margin:0px;">
<div style="width:830px; min-height:500px;margin:0px auto "><?php echo $html?></div>
</body>
</html>