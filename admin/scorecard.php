<?php ob_start(); ?>
<?php include('template/header.php');
if(isset($_GET['id'])){
	$eventId = $_GET['id'];
	$query_String = "SELECT golf_course_id,golf_course_name,format_id,stroke_play_id,event_name,total_hole_num FROM event_list_view where event_id ='".$eventId."'";
	$event_Data=$db->FetchQuery($query_String);
	if(is_array($event_Data) && count($event_Data)>0){
		$hole_Index="select hole_index_1, hole_index_2, hole_index_3, hole_index_4, hole_index_5, hole_index_6, hole_index_7, hole_index_8,
		hole_index_9, hole_index_10, hole_index_11, hole_index_12, hole_index_13, hole_index_14, hole_index_15, hole_index_16, hole_index_17,
		hole_index_18 from golf_hole_index where golf_course_id='".$event_Data[0]['golf_course_id']."'";
		$index_Data=$db->FetchQuery($hole_Index)[0];

		$par_Value="select par_value_1, par_value_2, par_value_3, par_value_4, par_value_5, par_value_6, par_value_7, par_value_8, par_value_9,
		par_value_10, par_value_11, par_value_12, par_value_13, par_value_14, par_value_15, par_value_16, par_value_17,
		par_value_18 from golf_hole_index where golf_course_id='".$event_Data[0]['golf_course_id']."'";
		$par_Data=$db->FetchQuery($par_Value)[0];

		$query_String="SELECT ESE.handicap_value,GU.full_name,";
		for($i=1;$i<=18;$i++){
			if($i==18){ $query_String.="ESE.score_entry_".$i; }
			else{ $query_String.="ESE.score_entry_".$i.","; }
		}
		$query_String.=" FROM temp_event_score_entry AS ESE LEFT JOIN golf_users AS GU ON(ESE.player_id=GU.user_id)  where ESE.event_id = '".$eventId."' order by current_position asc";
		$score_Data=$db->FetchQuery($query_String);
		$numberOfRecordSet=count($score_Data);
		if(isset($_GET["Page"])){
			$Page = $_GET["Page"];
			if(!$_GET["Page"]){
				$Page=1;
			}
		}
		else{
			$Page=1;
		}
		$Prev_Page = $Page-1;
		$Next_Page = $Page+1;
		$Page_Start = ((Number_of_Rows_Per_Page*$Page)-Number_of_Rows_Per_Page);
		if($numberOfRecordSet<=Number_of_Rows_Per_Page){
			$Num_Pages =1;
		}
		else if(($numberOfRecordSet % Number_of_Rows_Per_Page)==0){
			$Num_Pages =($numberOfRecordSet/Number_of_Rows_Per_Page) ;
		}
		else{
			$Num_Pages =($numberOfRecordSet/Number_of_Rows_Per_Page)+1;
			$Num_Pages = (int)$Num_Pages;
		}
		$query_String .="  limit $Page_Start ,". Number_of_Rows_Per_Page;
		$score_Data=$db->FetchQuery($query_String);
	}
	else{	header("location:events-listing.php"); }
}
else{ header("location:events-listing.php"); }
?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php include('template/sidebar.php'); ?>
<!--content body-->
	<div class="wrapper">
		<!--breadcrumb-->
		<ol class="breadcrumb" >
		<li><a href="events-listing.php">Events List</a></li>
		<li class="active">Score Card</li>
		</ol>
    <div class="container">
				<!--panel-->
				<div class="panel panel-default" >
				<div class="panel-heading"><h3 class="panel-title"><?php echo ucwords($event_Data[0]['event_name']); ?></h3><h3 class="panel-title"><?php echo ucwords($event_Data[0]['golf_course_name']).'-'.ucwords($event_Data[0]['city_name']); ?></h3></div>
				<div class="panel-body ">
					<table class="table" style="font-size:medium;">
						<tr>
							<th  style="color:wheat;text-align: center;background-color:<?php echo _PAR_; ?>">Par</th>
							<th style="color:wheat;text-align: center;background-color:<?php echo _BIRDIE_; ?>">Birdie</th>
							<th style="color:wheat;text-align: center;background-color:<?php echo _BOGEY_; ?>">Bogey</th>
							<th style="color:wheat;text-align: center;background-color:<?php echo _DOUBLE_BOGEY_; ?>">D.Bogey</th>
							<th style="color:wheat;text-align: center;background-color:<?php echo _EAGLE_; ?>">Eagle</th></tr>
					</table>
					<div class="flip-scroll">
	<table class="table table-bordered table-striped table-condensed flip-content" style="font-size:medium;">
	<thead class="flip-content bordered-palegreen">
	<tr>
	<th>HOLE</th><?php for($i=1;$i<=9;$i++){?> <th style="text-align: center;"><?php echo '&nbsp'.$i.'&nbsp'; ?></th> <?php } ?><th style="text-align: center;">FRONT 9</th>
	<?php for($i=10;$i<=18;$i++){?> <th style="text-align: center;"><?php echo $i; ?></th> <?php } ?><th style="text-align: center;">BACK 9</th><th style="text-align: center;">TOTAL</th>
	</tr>
	<tr>
	<th>INDEX</th><?php for($i=1;$i<=9;$i++){?> <th style="text-align: center;"><?php echo $index_Data['hole_index_'.$i]; ?></th> <?php } ?><th>&nbsp</th>
	<?php for($i=10;$i<=18;$i++){?> <th style="text-align: center;"><?php echo $index_Data['hole_index_'.$i]; ?></th> <?php } ?><th>&nbsp</th><th>&nbsp</th>
	</tr>
	<tr>
	<th>PAR</th><?php $sum_1=0; for($i=1;$i<=9;$i++){?> <th style="text-align: center;"><?php echo $par_Data['par_value_'.$i]; $sum_1=$sum_1+$par_Data['par_value_'.$i]; ?></th> <?php } ?><th style="text-align: center;"><?php echo $sum_1; ?></th>
	<?php $sum_2=0; for($i=10;$i<=18;$i++){?> <th style="text-align: center;"><?php echo $par_Data['par_value_'.$i];  $sum_2=$sum_2+$par_Data['par_value_'.$i]; ?></td> <?php } ?><th style="text-align: center;"><?php echo $sum_2; ?></th><th style="text-align: center;"><?php echo $sum_1+$sum_2; ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if(is_array($score_Data) && count($score_Data)>0){
		foreach($score_Data as $values){ ?>
		<tr>
		<th><?php echo $values["full_name"].'&nbsp<span>('.$values["handicap_value"].')<span>' ?></th>
			<?php $sum_1=0; for($i=1;$i<=9;$i++){
					$style=scoreBoxColor($values['score_entry_'.$i],$par_Data['par_value_'.$i]);
				?>
				<td style="<?php echo $style; ?>"><?php echo $values['score_entry_'.$i]; $sum_1=$sum_1+$values['score_entry_'.$i]; ?></td>
				<?php } ?><th style="text-align: center;"><?php echo $sum_1; ?></th>
		<?php $sum_2=0; for($i=10;$i<=18;$i++){
			$style=scoreBoxColor($values['score_entry_'.$i],$par_Data['par_value_'.$i]);
			?>
			<td style="<?php echo $style; ?>"><?php echo $values['score_entry_'.$i];  $sum_2=$sum_2+$values['score_entry_'.$i]; ?></td>
			<?php } ?><th style="text-align: center;"><?php echo $sum_2; ?></th><th style="text-align: center;"><?php echo $sum_1+$sum_2; ?></th>
		</tr>
	<?php	} }
	else{?>
		<tr><th colspan="22">Sorry, there are no players to display.</th></tr>
		<?php } ?>
	</tbody>
	</table>
	</div>
	<?php if(is_array($score_Data) && count($score_Data)>0){?>
	<ul class="pagination">
	<?php
	if($Prev_Page){
	echo '<li><a href='.$_SERVER["SCRIPT_NAME"].'?id='.$eventId.'&Page='.$Prev_Page.'>Previous</a></li>';
	}
	for($i=1; $i<=$Num_Pages; $i++){
	if($i != $Page){
		echo '<li><a href='.$_SERVER["SCRIPT_NAME"].'?id='.$eventId.'&Page='.$i.'>'.$i.'<span class="sr-only">(current)</span></a></li>';
	}
	else{
		echo '<li class="active"><a href="#">'.$i.'</a></li>';
	}
	}
	if($Page!=$Num_Pages){
	echo '<li><a href='.$_SERVER["SCRIPT_NAME"].'?id='.$eventId.'&Page='.$Next_Page.'>Next</a></li>';
	}
	?>
	</ul>
	<?php } ?>
  </div>
        <!--end panel-->
		</div>
    <!--end container-->
	</div>
</div>
<?php
function scoreBoxColor($score_entry,$par_value){
	if($score_entry==0){
		$style="text-align:center";
	}
	else if(($par_value-$score_entry)==0){
		$style="color:wheat;text-align:center;background-color:". _PAR_.";";
	}
	else if(($score_entry-$par_value)==-1){
		$style="color:wheat;text-align:center;background-color:". _BIRDIE_.";";
	}
	else if(($score_entry-$par_value)<=-2){
		$style="color:wheat;text-align:center;background-color:". _EAGLE_.";";
	}
	else if(($score_entry-$par_value)==1){
		$style="color:wheat;text-align:center;background-color:". _BOGEY_.";";
	}
	else if(($score_entry-$par_value)>=2){
		$style="color:wheat;text-align:center;background-color:". _DOUBLE_BOGEY_.";";
	}
	return $style;
}
?>
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
