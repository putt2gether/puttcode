<?php ob_start(); ?>
<?php include('template/header.php');

$event_data = $row_header = array();

if(isset($_GET['id'])){
	$eventId = $_GET['id'];
	$par_str_arr = $index_str_arr = $score_str_arr = array();
	for($i=1;$i<=18;$i++){
		$par_str_arr[] = "g.par_value_{$i}";
		$index_str_arr[] = "g.hole_index_{$i}";
		$score_str_arr[] = "c.score_entry_{$i}";
	}
	
	$query_String = "SELECT u.user_name,u.display_name,u.full_name,e.event_name,e.golf_course_name,e.total_hole_num,e.stroke_play_id,e.admin_id,p.is_submit_score,p.is_accepted,c.event_score_calc_id,c.event_id,c.player_id,c.format_id,c.hole_number,c.start_from_hole,c.no_of_holes_played,c.handicap_value,".implode(",",$score_str_arr).",c.total_score,c.par_total,c.gross_score,".implode(",",$index_str_arr).",".implode(",",$par_str_arr)." FROM event_score_calc c inner join event_player_list p on c.event_id = p.event_id and c.player_id = p.player_id inner join golf_users u on c.player_id = u.user_id inner join event_list_view e on c.event_id = e.event_id inner join golf_hole_index g on e.golf_course_id = g.golf_course_id where c.event_id = '".$eventId."' group by c.player_id order by c.event_score_calc_id";
	
	$event_data=$db->FetchQuery($query_String);
	
	if(is_array($event_data) && count($event_data)>0){
		$row_header = $event_data[0];
	}
	else {
		header("location:events-listing.php");
	}
}
else {
	header("location:events-listing.php");
}

//echo '<pre>';print_r($event_data);die;

$admin_id = $row_header['admin_id'];
$event_id = $row_header['event_id'];
$stroke_play_id = $row_header['stroke_play_id'];
$event_name = $row_header['event_name'];
$golf_course_name = $row_header['golf_course_name'];
$city_name = '';//$row_header['city_name'];

?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php //include('template/sidebar.php'); ?>
<!--content body-->
	<div class="wrapper" style='padding-left:0px;'>
		<!--breadcrumb-->
		<ol class="breadcrumb" >
		<li><a href="events-listing.php">Events List</a></li>
		<li class="active">Enter User Score</li>
		</ol>
		<style>.form-control {
    box-shadow: none;
    border: 0px #C7C7C7 solid;
    font-size: 14px;
    height: 29px;
    padding-bottom: 0;
    padding: 0 5px;
    border-radius: 2px;
    background: transparent;
    text-align: center;
}
.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control, tr.disabled_tr {
    background-color: transparent;
    opacity: 1;
    color: #ddd;
	cursor: not-allowed;
}
.table-bordered>thead>tr>th,.table-bordered>tbody>tr>td {
    font-size: 75%;
	vertical-align: middle;
}
.table-bordered>thead {
	
}
</style>
		<form id='form_send_score' name='form_send_score' action='insert_score.php' method='post'>
    <div class="container">
				<!--panel-->
				<div class="panel panel-default" >
				<div class="panel-heading"><h3 class="panel-title"><?php echo ucwords($event_name); ?></h3><h3 class="panel-title"><?php echo ucwords($golf_course_name).'-'.ucwords($city_name); ?></h3></div>
				<div class="panel-body ">
					<input type="hidden" name="update_score" value="1" />
					<input type="hidden" name="stroke_play_id" value="<?php echo $stroke_play_id; ?>" />
					<input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>" />
					<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
					<div class="flip-scroll">
	<table class="table table-bordered table-striped table-condensed flip-content" style="font-size:medium;">
	<thead class="bordered-palegreen">
	<tr>
	<th>HOLE</th><?php for($i=1;$i<=18;$i++){?> <th style="text-align: center;"><?php echo '&nbsp'.$i.'&nbsp'; ?></th> <?php } ?><th style="text-align: center;">TOTAL</th><th style="text-align: center;">Submit Score</th>
	</tr>
	<tr>
	<th>INDEX</th><?php for($i=1;$i<=18;$i++){?> <th style="text-align: center;"><?php echo '&nbsp'.$row_header["hole_index_{$i}"].'&nbsp'; ?></th> <?php } ?><th style="text-align: center;"> </th><th style="text-align: center;"> </th>
	</tr>
	<tr>
	<th>PAR</th><?php $d_par_sum = array(); for($i=1;$i<=18;$i++){?> <th style="text-align: center;"><?php $d_par_sum[] = $row_header["par_value_{$i}"]; echo '&nbsp'.$row_header["par_value_{$i}"].'&nbsp'; ?></th> <?php } ?><th style="text-align: center;"><?php echo array_sum($d_par_sum)?></th><th style="text-align: center;"> </th>
	</tr>
	</thead>
	<tbody>
	<?php if(is_array($event_data) && count($event_data)>0){
		foreach($event_data as $ind=>$values){ ?>
		<tr class='<?php echo ($values['is_submit_score']==1) ? "disabled_tr" : ""?>'>
		<td><input type="checkbox" class='inpplayercheck' name="player_id[<?php echo $values["player_id"]; ?>]" <?php echo ($values['is_submit_score']==1) ? "disabled" : ""?> value="<?php echo $values["event_score_calc_id"]; ?>" /><?php echo $values["full_name"].'&nbsp<span>('.$values["handicap_value"].')<span>' ?></td>
		<?php for($i=1;$i<=18;$i++){?>
		<td style="text-align: center;">
		<input type="hidden" class="form-control input-sm" name="last_score[<?php echo $values["player_id"]; ?>][<?php echo $i?>]" <?php echo ($values['is_submit_score']==1) ? "disabled" : ""?> value="<?php echo $values["score_entry_{$i}"]?>" />
		<input type="text" class="form-control input-sm" name="score[<?php echo $values["player_id"]; ?>][<?php echo $i?>]" <?php echo ($values['is_submit_score']==1) ? "disabled" : ""?> value="<?php echo $values["score_entry_{$i}"]?>" /></td>
		<?php } ?>
		<td style="text-align: center;"><?php echo $values["total_score"]?>
		</td>
		<td style="text-align: center;">
		<?php if($values['no_of_holes_played'] == $values['total_hole_num']){
			if($values['is_submit_score'] == 1){
				echo 'Submitted';
			}
			else {
				?>
				<a class="btn btn-warning" event_id="<?php echo $values["event_id"]; ?>" player_id="<?php echo $values["player_id"]; ?>" score_id="<?php echo $values["event_score_calc_id"]; ?>" action="0" onclick="end_round(this)">Submit</a>
				<?php
			}
		}else {echo ' - ';}?>
		</td>
		</tr>
	<?php	}?>
<tr><td colspan="21"><input type="submit" id="golf_FormButton" class="btn btn-lg btn-block btn-success" value='Update Score' /><div class='outp'></div></td></tr>
<?php	}
	else{?>
		<tr><td colspan="21">Sorry, there are no players to display.</td></tr>
		<?php } ?>
	</tbody>
	</table>
	</div>
	
  </div>
        <!--end panel-->
		</div>
    <!--end container-->
	</div>
	</form>
</div>
<!--end left bar-->
<!-- jQuery first, then Bootstrap JS. -->
<script src="js/jquery-1.7.1.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/2.2.2/js/bootstrap.min.js" ></script>

<script src="js/jquery.form.js"></script>
<script>
$(document).ready(function(e) { //alert(_default_enq_path);
    var enq_opt = { 
		url:($('#form_send_score').attr('action')),
		type:"post",
		dataType:  'json',
		beforeSubmit: validateScore,
		success: responseScore
		 }; 
	$("#form_send_score").ajaxForm(enq_opt);
});
function validateScore(formData, jqForm, options) {
	var chk = $('.inpplayercheck:checked').length;
	if(chk <= 0) {
		alert('Please Select Atleast One Player');
		return false;
	}
	else {
		var ov=$("input[type=submit]",jqForm).val();
		$("input[type=submit]",jqForm).attr("disabled",true).attr("ov",ov).val("Submitting Score, Please Wait...");
		return true;
	}
}

function responseScore(data, statusText, xhr, form) { //alert(data);$('.outp').html('<pre>'+data);//alert(data);
	var ov=$("input[type=submit]",form).attr("ov");
	var stat=data.stat;
	var mess=data.mess;
	if(stat=='1' || stat==1) {
		$("input[type=submit]",form).val(mess);
		window.setTimeout(function1, 1000);
	}
	else {
		$("input[type=submit]",form).attr("disabled",false).val(ov);
		alert(mess);
	}
}

function function1() {
	window.location.href=window.location.href;
}

function end_round(obj) {
	if(confirm('Are You Sure, you want to submit score?')){
		$(obj).text('Wait..').attr('disabled','disabled');
		var enq_opt = { 
			url:'insert_score.php',
			type:"post",
			dataType:  'json',
			data:  'submit_score=1&event_id='+($(obj).attr('event_id'))+'&player_id='+($(obj).attr('player_id')),
			success: function(data) { //alert(data);$('.outp').html('<pre>'+data);//alert(data);
				var stat=data.stat;
				var mess=data.mess;
				if(stat=='1' || stat==1) {
					$(obj).text('Submitted').attr('disabled','disabled');
					window.setTimeout(function1, 1000);
				}
				else {
					$(obj).text('Submit').removeAttr('disabled');
					alert(mess);
				}
			}
			 }; 
		$.ajax(enq_opt);
	}
}
</script>
</body>
</html>