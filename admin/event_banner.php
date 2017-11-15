<?php ob_start(); ?>
<?php include('template/header.php');
$event_id=isset($_GET['event_id'])?trim($_GET['event_id']):0;
$banner_id=isset($_GET['banner_id'])?trim($_GET['banner_id']):0;

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

$event_query="SELECT * FROM "._EVENT_LIST_VIEW_." order by event_id desc";
$event_data = $db->FetchQuery($event_query);

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
    <!--li><a href="#">Home</a></li-->
    <li class="active">Add Banner</li>
  </ol>
  <div class="container">

      <!--panel-->
      <div class="panel panel-default" ng-controller="addNewDomain">
         <div class="panel-heading"><h3><?php echo $has_event ? ($event_name.' >> Add/Edit Banner') : 'Add/Edit Banner'; ?></h3></div>
  <form class="eventBanner-form" name="eventBanner_form" autocomplete="off" id="eventBanner_form" method="post" action="services/process_banner.php" enctype="multipart/form-data">
  <div class="panel-body">
  <div class="row">
  <div class="col-sm-12"><div class="form-group">
  <label>* Event </label>
  <select class="form-control" name='event_id' id='event_id'>
  <option value='0'>-No Event-</option>
  <?php
  foreach($event_data as $a=>$b){
  $sel = '';
  if($b['event_id'] == $event_id) {$sel = 'selected';}
  ?>
  <option <?php echo $sel;?> value='<?php echo $b['event_id']?>'> <?php echo 'Event : '.$b['event_name'].' | Golf Course : '.$b['golf_course_name'].' | Date : '.date('d-M-y',strtotime($b['event_start_date_time'])).' '.date('h:i a',strtotime($b['event_start_time']));?></option>
  <?php }?>
  </select>
  <span id="event_error" class="all_errors"></span>
  </div>
  </div>
  
  </div>
  <div class="row">
  <div class="col-sm-6"><div class="form-group">
  <label>* Banner Title </label>
  <input type="text" name="banner_title" required="required" class="form-control" placeholder="eg. Banner Title" value="">
  <span id="title_error" class="all_errors"></span>
  </div>
  </div>
  <div class="col-sm-6"><div class="form-group">
  <label>* Banner Link (with http://) </label>
  <input type="text" name="image_href" class="form-control" placeholder="eg. http://website.com" value="">
  <span id="link_error" class="all_errors"></span>
  </div>
  </div>
  </div>
  <div class="row">
  <div class="col-sm-12"><div class="form-group form-check">
  <label class="form-check-label">* Banner Type </label>&nbsp;&nbsp;&nbsp;
  <?php foreach($banner_types as $a=>$b){?>
  <br/><label class="radio-inline"><input type="radio" class='event_banner_types' name="btype" value="<?php echo $a?>"> <?php echo $b?></label>
  <?php } ?>
  <span id="location_error2" class="all_errors"></span>
  </div>
  </div>
  </div>
  <div class="row">
  
  <div class="col-sm-12"><div class="form-group form-check">
  <label class="form-check-label">* Status </label>&nbsp;&nbsp;&nbsp;
  <br/><label class="radio-inline"><input type="radio" checked value="1" name="is_active">Active</label>
  <br/><label class="radio-inline"><input type="radio" value="0" name="is_active">In-Active</label>
  <span id="status_error" class="all_errors"></span>
  </div>
  </div>
  </div>
  <div class="row">
  <div class="col-sm-6"><div class="form-group">
  <label>* Banner Image </label>
  <input type="file" name="banner_image" required="required" class="form-control">
  <span id="image_error" class="all_errors"></span>
  </div>
  </div>
  
  </div>
  <!--small id="result" class="all_errors"></small-->
  <input type="hidden" name="banner_id" value="<?php echo $banner_id; ?>">
  <input type="hidden" name="action" value="add">
  <button type="submit" id="banner_FormButton" class="btn btn-lg btn-block btn-primary">
  Save</button>
  </div>
  </form>
      <!--end panel body-->
      </div>
      <!--end panel-->
  </div>
</div>
<!--end left bar-->
<!-- jQuery first, then Bootstrap JS. -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
<script src="js/scrollbar.js"></script>
<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script>


$('#eventBanner_form').ajaxForm({
  beforeSubmit: validate,
  dataType:  'json',
 success:function(data){
   //console.log(data);
   if(data.status==0){
     $("#banner_FormButton").removeAttr("disabled");
     $.each(data, function( index, value ) {
       $("#"+index).css("color","red").html(value).show();
      });
   }
   else{
     $("#banner_FormButton").removeAttr("disabled");
     $(".alert").remove();
     $("#eventBanner_form").prepend("<div class='alert alert-success'><strong>Success!</strong> "+data.message+"</div>");
     $("#eventBanner_form")[0].reset();
     setTimeout(function(){$(".alert").remove();},2000);
     location.assign("banner-listing.php?event_id=<?php echo $event_id; ?>");
   }
 }
});

function validate(){
	var err = false;
	
	var bt = $('.event_banner_types:checked').val();
	var event_id = $('#event_id').val();
	
	if(bt == undefined || bt == '') {
		alert('Please Select Banner Types');
		err = true;
	}
	else if((bt < '7' && bt != '1') && event_id == '0') {
		alert('Please Select Event');
		err = true;
	}
	
	if(err == true){
		return false;
	}
	else {
		$(".all_errors").html("").hide();
		$("#banner_FormButton").attr("disabled","disabled");
		$("#eventBanner_form").prepend("<div class='alert alert-info'>Processing....</div>");
		return true;
	}
}
</script>
</body>
</html>
