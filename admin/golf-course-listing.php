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
		<li><a href="#">Golf Courses</a></li>
		<li class="active">Golf Courses List</li>
		</ol>

    <div class="container">

				<!--panel-->
				<div class="panel panel-default" >
				<div class="panel-heading"><span>Golf Courses	</span><span class="pull-right"><a class="btn btn-primary" href="add-golf-course.php" style="
    margin-top: -8px;
"><i class="fa fa-plus-circle"></i>Add Golf Course</a></span></div>
				<div class="panel-body ">
<?php
$query="select G.golf_course_id,G.golf_course_name,G.creation_date,G.is_active,C.city_name,CTY.country_name from golf_course as G LEFT JOIN city as C ON(G.city_id=C.city_id) LEFT JOIN country as CTY ON(C.country_id=CTY.country_id) order by golf_course_id desc";
$golf_data=$db->FetchQuery($query);
?>
<table id="viewapplication" class="table table-striped table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th>Sr. No</th>
<th>Name</th>
<th>City</th>
<th>Country</th>
<th>Created Date</th>
<th> Action  </th>
</tr>
<tbody>
<?php
if(count($golf_data)>0):
$count=0;
foreach($golf_data as $list):
?>
<tr id="row_Id<?php echo $list["golf_course_id"]; ?>">
<td><?php echo ++$count; ?></td>
<td><?php echo $list["golf_course_name"]; ?></td>
<td><?php echo $list["city_name"]; ?></td>
<td><?php echo $list["country_name"]; ?></td>
<td><?php echo date("d  M Y",strtotime($list["creation_date"])); ?></td>
<td>
<div class="btn-group btn-group-sm btn-custom">
	<?php if($list["is_active"]==1): ?>
	<a class="btn btn-success" id="status<?php  echo $list["golf_course_id"]; ?>" onclick="changeStatus('<?php echo $list["golf_course_id"]; ?>','2')" > Active </a>
	<?php else: ?>
	<a class="btn btn-warning" id="status<?php  echo $list["golf_course_id"]; ?>" onclick="changeStatus('<?php echo $list["golf_course_id"]; ?>','1')" > In-active </a>
	<?php endif; ?>
<a class="btn btn-info" href="edit-golf-course.php?golf_course_id=<?php echo $list["golf_course_id"]; ?>" target="_blank"> Edit</a>
</button-->
</div>
</td>
</tr>
<?php endforeach;
else: ?>
<tr><td>There are no records to display.<td></tr>
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
    { 'bSortable': false, 'aTargets': [ 4 ] }
  ]
});

$(document).ready(function(){
	$("#viewapplication_filter input").attr("placeholder","Search By Golf Course Name,City And Country");
	$("#viewapplication_paginate").removeClass('dataTables_paginate paging_simple_numbers');

});
function fixValue(row_Id)
{
  $("#deletButton").attr("onclick",'deleteOne('+row_Id+')');
}
function deleteOne(row_Id)
{
  $("#cancelButton").attr("disabled","disabled");
  $("#deletButton").attr("disabled","disabled");
  $("#myModalLabel").html("<i class='fa fa-cog fa-spin'></i>Please wait while data is deleting.");
  var data={};
    data['row_Id']=row_Id;
    data['action']="delete";
  $.post("../services/process_register.php",data,function(data){
    switch(data.status)
    {
      case 1:{
        $("#myModalLabel").html(data.message);
        $("#row_Id"+row_Id).hide();
        setTimeout(function(){
          $("#cancelButton").removeAttr("disabled");
          $("#deletButton").removeAttr("disabled");
          $("#myModalLabel").html('Delete Alert');
          $("#myModal").modal('hide');
        },3000);
       }break;
      case 0:{
        $("#myModalLabel").html(data.message);
        setTimeout(function(){
          $("#cancelButton").removeAttr("disabled");
          $("#deletButton").removeAttr("disabled");
          $("#myModalLabel").html('Delete Alert');
          $("#myModal").modal('hide');
        },3000);
       }break;
    }
  },'json');
}

function changeStatus(golf_id,action){
	if(action!=""){
		$("#status"+golf_id).attr("disabled","disabled");
		$.post("services/process_golf_status.php",{action:action,golf_id:golf_id},function(data){
			if(data.status==1){
				$("#status"+golf_id).attr("onclick",data.function);
				$("#status"+golf_id).attr("class",data.class);
				$("#status"+golf_id).html(data.text);
				$("#status"+golf_id).removeAttr("disabled");
			}
			else{
				alert("Unable to process.");
			}
		},'json');
	}
}
</script>
</body>
</html>
