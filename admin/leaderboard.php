<?php ob_start(); ?>
<?php include('template/header.php'); ?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php include('template/sidebar.php'); ?>
<!--content body-->
<?php
$event_Id = isset($_GET['id'])?trim($_GET['id']):0;
$ch = curl_init('http://putt2gether.com/puttdemo/getleaderboard');
$data=array("event_id"=>$event_Id);
$data_string = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$result = curl_exec($ch);
curl_close($ch);
$result=json_decode($result);
if(isset($result->Error)){ ?>
	<div class="wrapper">
	<div class="container">
	<div class="flip-scroll">
	<table class="table table-bordered table-striped table-condensed flip-content" style="font-size:medium;">
	<thead class="flip-content bordered-palegreen">
	<tr><th><?php echo $result->Error->message; ?></th></tr>
	</thead>
	</table>
	</div>
	</div>
	</div>
<?php }
else{?>
	<div class="wrapper">
	<!--breadcrumb-->
	<ol class="breadcrumb" >
	<li><a href="events-listing.php">Events List</a></li>
	<li class="active">leaderboard</li>
	</ol>
	<div class="container">
	<!--panel-->
	<div class="panel panel-default" >
	<div class="panel-heading"><h3 class="panel-title">Leader Board</h3></div>
	<div class="panel-body ">
	<div class="flip-scroll">
		<table class="table table-bordered table-striped table-condensed flip-content" style="font-size:medium;">
		<thead class="flip-content bordered-palegreen">
		<tr><th>Golf Course Name </th><th>Event Name</th><th>Game Format</th><th>Total Hole Number</th><th>Event Start Date</th></tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $result->Leaderboard->golf_course_name; ?></td>
				<td><?php echo $result->Leaderboard->event_name; ?></td>
				<td><?php echo $result->Leaderboard->format_name; ?></td>
				<td><?php echo $result->Leaderboard->total_hole_num; ?></td>
				<td><?php echo $result->Leaderboard->event_start_date;?></td>
			</tr>
		</tbody>
		</table>
	<table class="table table-bordered table-striped table-condensed flip-content" style="font-size:medium;">
	<thead class="flip-content bordered-palegreen">
	<tr><th>RANK</th><th>	PLAYERS</th><th>HOLES PLAYED</th><th><?php echo strtoupper($result->Leaderboard->format_name); ?></th></tr>
	</thead>
	<tbody>
	<?php
		$players=$result->Leaderboard->{'0'}->player_score;
		foreach($players as $player){?>
			<tr>
				<td><?php echo $player->current_position; ?></td>
				<td><?php echo $player->full_name."&nbsp(".$player->handicap_value.")"; ?></td>
				<td><?php echo $player->no_of_hole_played; ?></td>
				<td><?php echo $player->total; ?></td>
			</tr>
	<?php } ?>
	</tbody>
	</table>
	</div>
	</div>
	<!--end panel-->
	</div>
	<!--end container-->
	</div>
	</div>
<?php } ?>
<!--end left bar-->
<!-- jQuery first, then Bootstrap JS. -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
<script src="js/scrollbar.js"></script>
<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
