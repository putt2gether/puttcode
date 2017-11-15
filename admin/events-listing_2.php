	<?php ob_start(); ?>
<?php include('template/header.php') ?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php include('template/sidebar.php') ?>
<!--content body-->
	<div class="wrapper">
		<!--breadcrumb-->
		<ol class="breadcrumb" >
		<li><a href="#">Events</a></li>
		<li class="active">Events List</li>
		</ol>

    <div class="container">

				<!--panel-->
				<div class="panel panel-default" >
				<div class="panel-heading"><span>Events</span><!--span class="pull-right"><i class="fa fa-plus-circle"></i><a href="add-golf-course.php">Add Golf Course</a></span--></div>
				<div class="panel-body ">
<?php
  $query="select EL.event_name,EL.golf_course_name,EL.total_hole_num,EL.event_start_date_time,EL.event_start_time,EL.format_name,EL.event_id,EL.is_active,EL.is_started,EL.admin_id,GU.display_name from "._EVENT_LIST_VIEW_." EL LEFT JOIN ".TABLE_GOLF_USERS." as GU ON(EL.admin_id=GU.user_id)";
if(trim($_SESSION["a_user_level"])!="SA"){
	$query.=" where EL.admin_id='".$_SESSION["a_user_id"]."'";
	$query.= " or event_id in (select event_id from event_player_list where player_id = ".$_SESSION['a_user_id']." and is_accepted='".intval(1)."')";
}
$event_data=$db->FetchQuery($query);
?>
<table id="viewapplication" class="table table-bordered table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th>Sr. No</th>
<th>Event</th>
<th>Golf Course</th>
<th>Event Admin</th>
<th>Number of Holes</th>
<th>Start Date</th>
<th>View</th>
<th>Format</th>
<?php if(trim($_SESSION["a_user_level"])=="SA"): ?>
<th>Status</th>
<?php endif; ?>
</tr>
<tbody>
<?php
if(count($event_data)>0):
$count=0;
foreach($event_data as $list):
?>
<tr id="row_Id<?php echo $list["event_id"]; ?>">
<td><?php echo ++$count; ?></td>
<td><?php echo $list["event_name"]; ?></td>
<td><?php echo $list["golf_course_name"]; ?></td>
<td><?php echo $list["display_name"]; ?></td>
<td class="text-center"><?php echo $list["total_hole_num"]; ?></td>
<td><?php echo date("d  M Y",strtotime($list["event_start_date_time"]))."&nbsp&nbsp".date("h:i A",strtotime($list["event_start_time"])); ?></td>
<?php if($list["admin_id"]==$_SESSION['a_user_id'] || trim($_SESSION["a_user_level"])=="SA"): ?>
<td><?php
switch($list['is_started']){
	case 3:{ ?>
		<div class="btn-group btn-group-sm btn-custom">
		<a href="scorecard.php?id=<?php echo $list['event_id'];?>" target="_blank" class="btn btn-warning">View Score</a>
		<a href="leaderboard.php?id=<?php echo $list['event_id'];?>" target="_blank" class="btn btn-primary">View Leader Board</a>
	</div>
							<!--a href="closeevent.php?id=<?php //echo $list['event_id'];?>">Close Event</a-->
	<?php } break;
	case 4:{
		echo 'Closed';
	} break;
	default:{
		echo '----';
	}
}
 ?></td>

<?php else: ?>
<td>Participant</td>
<?php endif; ?>
<td><?php echo $list["format_name"]; ?></td>
<?php if(trim($_SESSION["a_user_level"])=="SA"): ?>
<td>
<div class="btn-group btn-group-sm btn-custom">
<?php if($list["is_active"]==1): ?>
<a class="btn btn-success" id="status<?php  echo $list["event_id"]; ?>" event-id='<?php  echo $list["event_id"]; ?>' action='0' onclick="userStatus(this)"> Un-blocked </a>
<?php else: ?>
<a class="btn btn-warning" id="status<?php  echo $list["event_id"]; ?>" event-id='<?php  echo $list["event_id"]; ?>' action='1' onclick="userStatus(this)"> Blocked </a>
<?php endif; ?>
</div>
</td>
<?php endif; ?>
</tr>
<?php endforeach;
else: ?>
<tr><td colspan="<?php echo ($_SESSION['a_user_level']!='SA'?8:9); ?>">There are no records to display.<td></tr>
<?php endif; ?>
</tbody>
</thead>

</table>
        </div>
        <!--end panel-->
		</div>
    <!--end container-->

	</div>
</div>
//MODEL POPUP FOR DELETE
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog dialog-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete Alert</h4>
      </div>
      <div class="modal-body">
      Are you sure, you want to delete this agent.
      </div>
      <div class="modal-footer">
        <button type="button"  id="cancelButton" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        <button type="button" id="deletButton" class="btn btn-danger">Permanent Delete</button>
      </div>
    </div>
  </div>
</div>
<!--end left bar-->
<!-- jQuery first, then Bootstrap JS. -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
<script src="js/scrollbar.js"></script>
<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script>
$("#viewapplication").DataTable({
  "aoColumnDefs": [
    { 'bSortable': false, 'aTargets': [ 5 ] }
  ]
});

$(document).ready(function(){
	$("#viewapplication_filter input").attr("placeholder","Search By Event, Golf Course and Admin");
	$("#viewapplication_paginate").removeClass('dataTables_paginate paging_simple_numbers');

});

var userStatus=function(obj){
      var event_id = $(obj).attr('event-id');
      var flag = $(obj).attr('action');
			var action = "blockUnblockEvent";
      $("#status"+event_id).attr("disabled","disabled");
      $.post("services/process_eventBlockUnblock.php",{action:action,flag:flag,event_id:event_id},function(data){
        //console.log(data);
        if(data.status==1){
          $("#status"+event_id).attr("action",data.action);
          $("#status"+event_id).attr("class",data.class);
          $("#status"+event_id).html(data.text);
          $("#status"+event_id).removeAttr("disabled");
        }
        else{
          alert("Unable to process.");
					$("#status"+event_id).removeAttr("disabled");
        }
      },'json');
    }
</script>
</body>
</html>
