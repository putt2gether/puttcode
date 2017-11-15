<?php include('template/header.php');
$event_id=isset($_GET['event_id'])?trim($_GET['event_id']):0;
if(is_numeric($event_id) && $event_id>0){
	$event_con = "WHERE b.event_id='".$event_id."'";
	$event_query="SELECT event_name FROM "._EVENT_LIST_VIEW_." WHERE event_id='".$event_id."'";
	$event_data = $db->FetchQuery($event_query);
	$event_name = isset($event_data[0]['event_name']) ? trim($event_data[0]['event_name']) : '';
	$has_event = true;
}
else {
	$event_con = '';
	$event_name = '';
	$has_event = false;
}

$query="SELECT b.*,e.event_name,e.golf_course_name,e.event_start_date_time,e.event_start_time,e.format_name FROM "._EVENT_BANNER_." b left join event_list_view e on b.event_id = e.event_id {$event_con} order by id desc";
$banner_data=$db->FetchQuery($query);


$banner_types[1] = 'Horizontal banner on Event List under Invite Section';
$banner_types[2] = 'Event Logo under Invite Section';
$banner_types[3] = 'Horizontal banner on Leaderboard';
$banner_types[4] = 'Horizontal banner on Scorecard';
$banner_types[5] = 'Full-screen banner after Submit score';
$banner_types[6] = 'Full-screen banner after Start Round';
$banner_types[7] = 'Horizontal banner on My Score (Without Event)';
$banner_types[8] = 'Horizontal banner on Notifications (Without Event)';
?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php include('template/sidebar.php') ?>
<!--content body-->
<div class="wrapper">
  <!--breadcrumb-->
  <ol class="breadcrumb" >
  <?php if($has_event){?>
  <li><a href="events-listing.php">Events</a></li>
  <?php }?>
  <li class="active">Banner List</li>
  </ol>

  <div class="container">

      <!--panel-->
      <div class="panel panel-default" >
      <div class="panel-heading"><span><h3><?php echo $has_event ? ($event_name.' >> Event Banner List') : 'Banner List'; ?></h3></span><span class="pull-right"><a class="btn btn-info" style="margin-top: -42px;" href="event_banner.php?event_id=<?php echo $event_id; ?>">Add Banner</a></span></div>
      <div class="panel-body ">
<table id="viewapplication" class="table table-bordered table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th>Sr. No</th>
<th>Event Name</th>
<th>Image</th>
<th>Title / For</th>
<th>Redirect to</th>
<th>Added on</th>
<th>Status</th>
<th>Action</th>
</tr>
<tbody>
<?php
if(count($banner_data)>0):
$count=0;
foreach($banner_data as $list):
?>
<tr id="row<?php echo $list["id"]; ?>">
<td><?php echo ++$count; ?></td>
<td><?php echo trim($list["event_name"]) == '' ? '---' : $list["event_name"];?></td>
<td><img src="<?php echo _DISPLAY_BANNER_PATH_.$list["image_path"];?>" style="max-width:100px;"></td>
<td><?php echo "<strong>".$list["title"]."</strong></br>"; 
echo $banner_types[$list["type"]];
?></td>
<td><?php echo $list["image_href"]; ?></td>
<td><?php echo date("d M Y h:i A",strtotime($list["create_date"])); ?></td>
<td>
<div class="btn-group btn-group-sm btn-custom">
<?php if($list["is_active"]==1): ?>
<a class="btn btn-success" id="status<?php  echo $list["id"]; ?>" banner-id='<?php  echo $list["id"]; ?>' action='0' onclick="bannerStatus(this)"> Active </a>
<?php else: ?>
<a class="btn btn-warning" id="status<?php  echo $list["id"]; ?>" banner-id='<?php  echo $list["id"]; ?>' action='1' onclick="bannerStatus(this)"> In-active </a>
<?php endif; ?>
</div>
</td>
<td>
<div class="btn-group btn-group-sm btn-custom">
  <a class="btn btn-danger" banner-id="<?php echo $list["id"];?>" onclick="deleteB(this)">Delete</a>
  <!--a class="btn btn-info" href="event_banner.php?event_id=<//?php echo $event_id; ?>&banner_id=<//?php echo $list["id"];?>">Edit</a-->
</div>
</td>
</tr>
<?php endforeach;
else: ?>
<tr><td colspan="7">There are no records to display, &nbsp;<a href="event_banner.php?event_id=<?php echo $event_id; ?>">Add New Banner<?php echo $has_event ? (' in '.$event_name) : ''; ?></a>.</td></tr>
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
$("#viewapplication_filter input").attr("placeholder","Search By Banner title");
$("#viewapplication_paginate").removeClass('dataTables_paginate paging_simple_numbers');

});
var deleteB=function(obj){
  if(confirm("Are you sure!")==true){
    var banner_id=$(obj).attr("banner-id");
    $(obj).attr("disabled","disabled");
    $.post("services/process_banner.php",{action:'del',banner_id:banner_id},function(data){
      if(data.status==1){
        console.log('deleted');
        $("#row"+banner_id).hide();
      }
      else{
        $(obj).removeAttr("disabled");
        alert("Unable to delete banner.");
      }
    },'json');
  }
}
var bannerStatus=function(obj){
    var banner_id = $(obj).attr('banner-id');
    var flag = $(obj).attr('action');
    var action = "status";
    $("#status"+banner_id).attr("disabled","disabled");
    $.post("services/process_banner.php",{action:action,flag:flag,banner_id:banner_id},function(data){
      if(data.status==1){
        $("#status"+banner_id).attr("action",data.action);
        $("#status"+banner_id).attr("class",data.class);
        $("#status"+banner_id).html(data.text);
        $("#status"+banner_id).removeAttr("disabled");
      }
      else{
        alert("Unable to process.");
        $("#status"+banner_id).removeAttr("disabled");
      }
    },'json');
  }
</script>
</body>
</html>
