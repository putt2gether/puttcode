<?php ob_start(); ?>
<?php include('template/header.php') ?>
<!--left bar-->
<div class="content">
<!--sidebar menu-->
<?php include('template/sidebar.php') ?>
<!--content body-->
<?php
$golf_course_id=isset($_REQUEST["golf_course_id"]) ? trim($_REQUEST['golf_course_id']) : 0;
$golf_query="select G.golf_course_name,G.number_of_holes,G.city_id,G.latitude,G.longitude,C.country_id,C.state_id from golf_course as G LEFT JOIN city as C ON(G.city_id=C.city_id) where G.golf_course_id='".$golf_course_id."'";
$golf_data=$db->FetchQuery($golf_query);
if(count($golf_data)>0 && count($golf_data)==1){?>
<div class="wrapper">
  <!--breadcrumb-->
  <ol class="breadcrumb" >
    <!--li><a href="#">Home</a></li-->
    <li class="active">Edit Golf Course</li>
  </ol>
  <div class="container">

      <!--panel-->
<div class="panel panel-default" ng-controller="addNewDomain">
<div class="panel-heading"> <i class="fa fa-plus-circle"></i></div>
  <?php
  //index hole number
  $hole_index_query="select * from golf_hole_index where golf_course_id='".$golf_course_id."'";
  $golf_index_data=$db->FetchQuery($hole_index_query);
  //++++++++++++++++++
  $golf_data=$golf_data[0];
  $location_query="select country_id,state_id from city where city_id='".$golf_data['city_id']."'";
  $location_data=$db->FetchQuery($location_query);
  //print_r($golf_data);
  $golf_index_data=$golf_index_data[0];


  $hole_index_data=array();
  $par_value_data=array();
  $tee_value_data=array();
  for($i=1;$i<=18;$i++){
    $hole_index_data[$i]=$golf_index_data["hole_index_".$i];
    $par_value_data[$i]=$golf_index_data["par_value_".$i];
  }

$tee_data=json_decode($golf_index_data["tee_value1"]);
foreach($tee_data->Men as $key=>$value){
  $tee_value_data[]=$key;
}
  $country_data=$db->FetchQuery("select country_id,country_name from country order by country_name");
  ?>
  <form class="adminAgent-form" autocomplete="off" id="adminAgent_Form" method="post" novalidate="novalidate" action="services/process_edit_golf.php">
  <div class="panel-body">
  <div class="row">
  <div class="col-sm-12"><div class="form-group">

  <label>* Golf Course Name </label>
  <input type="text" name="golf_course_name" required="required" class="form-control" placeholder="eg. 88 Country Club" value="<?php echo $golf_data["golf_course_name"]; ?>">
  <input type="hidden" name="number_of_holes" value="<?php echo $golf_data["number_of_holes"]; ?>">
  <span id="name_error" class="all_errors"></span>
  </div>
  </div>
  </div>
  <div class="row">
  <div class="col-sm-6"><div class="form-group">
  <label>* Select Country</label>
  <select name="country" class="form-control" onchange="getstate(this.value)">
  <option value="">select country</option>
  <?php foreach($country_data as $country): ?>
  <option value="<?php echo $country['country_id'] ?>" <?php if($golf_data["country_id"]==$country['country_id']){?> selected="selected" <?php } ?>><?php echo $country['country_name'] ?></option>
  <?php endforeach; ?>
  <option value="Other">Other</option>
  </select>
  <span id="country_error" class="all_errors"></span>
  </div>
  <div class="row">
  <div class="col-sm-6" id="country-list-other" style="display:none"><div class="form-group">
  <label>* Country Name</label>
  <input type="text" name="country" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
  <div class="col-sm-6" id="country-code-other" style="display:none"><div class="form-group">
  <label>* Country Code</label>
  <input type="number" name="country_code" required="required" disabled="disabled" class="form-control">
  <span id="country_code_error" class="all_errors"></span>
  </div>
  </div>
  </div>
  </div>
  <div class="col-sm-6"><div class="form-group">
  <label>* Select State</label>
  <select name="state" class="form-control" id="state-list"  onchange="getcity(this.value)" disabled="disabled">
  <option value="">select state</option>
  </select>
  <span id="state_error" class="all_errors"></span>
  </div>
  <div class="row">
  <div class="col-sm-6" id="state-list-other" style="display:none"><div class="form-group">
  <label>* State Name</label>
  <input type="text" name="state" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
  </div>
  </div>
  </div>
  <!---OTHER FIELDS--->
  <!--div class="row">
  <div class="col-sm-6" id="country-list-other" style="display:none"><div class="form-group">
  <label>Country Name</label>
  <input type="text" name="country" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
  <div class="col-sm-6" id="state-list-other" style="display:none"><div class="form-group">
  <label>State Name</label>
  <input type="text" name="state" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
</div-->
  <!---END OTHER FIELDS--->
  <div class="row">
  <div class="col-sm-6"><div class="form-group">
  <label>* Select city</label>
  <select name="city" class="form-control" id="city-list" onchange="addcity(this.value)" disabled="disabled">
  <option value="">select city</option>
  </select>
  <span id="city_error" class="all_errors"></span>
  </div>
  <div class="row">
  <div class="col-sm-6" id="city-list-other" style="display:none"><div class="form-group">
  <label>* City Name</label>
  <input type="text" name="city" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
  </div>
  </div>
  </div>
  <!---OTHER FIELDS--->
  <!--div class="row">
  <div class="col-sm-6" id="city-list-other" style="display:none"><div class="form-group">
  <label>City Name</label>
  <input type="text" name="city" required="required" disabled="disabled" class="form-control">
  </div>
  </div>
</div-->
  <!---END OTHER FIELDS--->
  <div class="row">
  <div class="col-sm-6"><div class="form-group">
  <label>* Latitude</label>
  <input type="text" name="latitude" required="required" class="form-control"  value="<?php echo $golf_data["latitude"]; ?>" placeholder="eg. 37.566535">
  <span id="lat_error" class="all_errors"></span>
  </div>
  </div>
  <div class="col-sm-6"><div class="form-group">
  <label>* Longitude</label>
  <input type="text" name="longitude" required="required" class="form-control" value="<?php echo $golf_data["longitude"]; ?>" placeholder="eg. 26.9779692">
  <span id="long_error" class="all_errors"></span>
  </div>
  </div>
  </div>
  <div class="row">
  <div class="col-sm-12"><div class="form-group">
  <label>* Hole Numbers</label>
<small id="hole_index_error" class="all_errors"></small>
<table class="table table-striped">
<tr><td><label>Hole Number </label></td><td><label>Index Value</label></td><td><label>Par Value</label></td></tr>
<?php for($i=1;$i<=18;$i++){ ?>
  <tr>
  <td><label>Hole Number <?php echo $i; ?></label></td>
  <td>
    <select class="hole" name="hole_index[]">
      <option value="">Select</option>
      <?php for($j=1;$j<=18;$j++){ ?>
        <option value="<?php echo $j; ?>" <?php if($hole_index_data[$i]==$j){?> selected="selected" <?php } ?>><?php echo $j; ?></option>
      <?php } ?>
    </select>
  </td>
  <td>
  <select name="par_value[]">
    <option value="">Select</option>
    <?php for($k=3;$k<=5;$k++){ ?>
      <option value="<?php echo $k; ?>" <?php if($par_value_data[$i]==$k){?> selected="selected" <?php } ?>><?php echo $k; ?></option>
    <?php } ?>
  </select>
  </td>
  </tr>
<?php } ?>
</table>
</div></div></div>
  <div class="row">
    <div class="col-sm-12"><div class="form-group">
    <label>* Tee Values</label>
  <small id="tee_value_error" class="all_errors"></small>
  <table class="table table-striped">
    <tr><td><label>Tee Order </label></td><td><label>Tee Value</label></td></tr>
    <tr><td><label>1. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[0])){ if($tee_value_data[0]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    </td></tr>
    <tr><td><label>2. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[1])){ if($tee_value_data[1]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    </td></tr>
    <tr><td><label>3. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[2])){ if($tee_value_data[2]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    <tr><td><label>4. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[3])){ if($tee_value_data[3]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    <tr><td><label>5. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[4])){ if($tee_value_data[4]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    <tr><td><label>6. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[5])){ if($tee_value_data[5]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    <tr><td><label>7. </label></td><td><select class="tee" name="tee_value[]">
    <option value="Select">Select</option>
    <option value="Black" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Black"){ ?> selected="selected" <?php }} ?>>Black</option>
    <option value="Blue" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Blue"){ ?> selected="selected" <?php }} ?>>Blue</option>
    <option value="Red" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Red"){ ?> selected="selected" <?php }} ?>>Red</option>
    <option value="Yellow" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Yellow"){ ?> selected="selected" <?php }} ?>>Yellow</option>
    <option value="White" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="White"){ ?> selected="selected" <?php }} ?>>White</option>
    <option value="Green" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Green"){ ?> selected="selected" <?php }} ?>>Green</option>
    <option value="Gold" <?php if(isset($tee_value_data[6])){ if($tee_value_data[6]=="Gold"){ ?> selected="selected" <?php }} ?>>Gold</option>
    </select>
    </td></tr>
  </table>
</div></div></div>
  <input type="hidden" name="golf_course_id" value="<?php echo $golf_course_id; ?>">
  <small id="result" class="all_errors"></small>
  <button type="submit" id="golf_FormButton" class="btn btn-lg btn-block btn-primary">
  Save</button>
  </div>
  </form>
      <!--end panel body-->
      </div>
      <!--end panel-->
  </div>
</div>
<!--end left bar-->
<script>
$(document).ready(function(){
  $('select.hole').on('change', function(event ) {
   var prevValue = $(this).data('previous');
  $('select.hole').not(this).find('option[value="'+prevValue+'"]').removeAttr('disabled');
  $('select.hole').not(this).find('option[value="'+prevValue+'"]').show();
   var value = $(this).val();
  $(this).data('previous',value); $('select.hole').not(this).find('option[value="'+value+'"]').attr('disabled','disabled');
  $(this).data('previous',value); $('select.hole').not(this).find('option[value="'+value+'"]').hide();
  });
  $('select.tee').on('change', function(event ) {
   var prevValue = $(this).data('previous');
   $('select.tee').not(this).find('option[value="'+prevValue+'"]').removeAttr('disabled');
  $('select.tee').not(this).find('option[value="'+prevValue+'"]').show();
   var value = $(this).val();
   if(value!="Select"){
     $(this).data('previous',value); $('select.tee').not(this).find('option[value="'+value+'"]').attr('disabled','disabled');
     $(this).data('previous',value); $('select.tee').not(this).find('option[value="'+value+'"]').hide();
   }
});
});
</script>
<!-- jQuery first, then Bootstrap JS. -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
<script src="js/scrollbar.js"></script>
<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script>
getstate('<?php echo $golf_data["country_id"]; ?>');
getcity('<?php echo $golf_data["state_id"]; ?>');
function getstate(country_id){
  //console.log(country_id);
  if(country_id!=null){
    if(country_id!="Other"){
      $("#state-list").attr("disabled","disabled");
      $("#country-list-other").hide();
      $("#country-list-other input[type=text]").attr("disabled","disabled");
      $("#country-code-other input[type=number]").attr("disabled","disabled");
      $("#country-code-other").hide();
      $("#state-list-other").hide();
      $("#state-list-other input[type=text]").attr("disabled","disabled");
      $("#city-list-other").hide();
      $("#city-list-other input[type=text]").attr("disabled","disabled");

      var data={};
      data["action"]="findstate";
      data["country_id"]=country_id;
      data["state_id"]='<?php echo $golf_data["state_id"]; ?>';
      $.post("services/process_locations.php",data,function(data){
        $("#state-list").removeAttr("disabled");
        $("#state-list").html(data);
      });
    }
    else{
      $("#state-list").attr("disabled","disabled");
      $("#city-list").attr("disabled","disabled");

      $("#country-list-other").show();
      $("#country-list-other input[type=text]").removeAttr("disabled");
      $("#country-code-other").show();
      $("#country-code-other input[type=number]").removeAttr("disabled");
      $("#state-list-other").show();
      $("#state-list-other input[type=text]").removeAttr("disabled");
      $("#city-list-other").show();
      $("#city-list-other input[type=text]").removeAttr("disabled");
    }
  }
}

function getcity(state_id){
  if(state_id!=null){
    if(state_id!="Other"){
      $("#city-list").attr("disabled","disabled");
      $("#state-list-other").hide();
      $("#state-list-other input[type=text]").attr("disabled","disabled");
      $("#city-list-other").hide();
      $("#city-list-other input[type=text]").attr("disabled","disabled");

      var data={};
      data["action"]="findcity";
      data["state_id"]=state_id;
      data["city_id"]='<?php echo $golf_data["city_id"]; ?>';
      $.post("services/process_locations.php",data,function(data){
        $("#city-list").removeAttr("disabled");
        $("#city-list").html(data);
      });
    }
    else{
      $("#city-list").attr("disabled","disabled");

      $("#state-list-other").show();
      $("#state-list-other input[type=text]").removeAttr("disabled");
      $("#city-list-other").show();
      $("#city-list-other input[type=text]").removeAttr("disabled");
    }
  }
}

function addcity(city_id){
  if(city_id!=null){
    if(city_id!="Other"){
      $("#city-list-other").hide();
      $("#city-list-other input[type=text]").attr("disabled","disabled");
      $("#city-list").removeAttr("disabled");
      $("#city-list").show();
    }
    else{
      $("#city-list-other").show();
      $("#city-list-other input[type=text]").removeAttr("disabled");
    }
  }
}

$('#adminAgent_Form').ajaxForm({
  beforeSubmit: validate,
  dataType:  'json',
 success:function(data){
   //console.log(data);
   if(data.status==0){
     $("#golf_FormButton").removeAttr("disabled");
     $.each(data, function( index, value ) {
       $("#"+index).css("color","red").html(value).show();
      });
      //show model
      $("#submitModal").modal('show');
      //end
   }
   else{
     $("#golf_FormButton").removeAttr("disabled");
     $.each(data, function( index, value ) {
       $("#"+index).css("color","green").html(value).show();
      });
      //show model
      $("#submitModal").modal('show');
      //end
   }
 }
});

function validate(){
  $(".all_errors").html("").hide();
  $("#golf_FormButton").attr("disabled","disabled");
}
</script>
</body>
</html>
<?php }
else{
header("location:golf-course-listing.php");
} ?>
