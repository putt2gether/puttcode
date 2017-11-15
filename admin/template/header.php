<?php require_once(dirname(__FILE__).'../../config/db_config.php');
$db=new DatabaseConnectionClass();
session_start();
if(!isset($_SESSION['a_user_id']) && !isset($_SESSION['a_user_name']) && !isset($_SESSION['a_user_level'])):
header("location:index.html");
endif;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="css/scrollbar.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/eagle.gallery.min.js"></script>
<script src="js/form.js"></script>
<!--script src="https://malsup.github.com/jquery.form.js"></script-->
<link rel="stylesheet" href="css/eagle.gallery.css" />
</head>
<body>
<header>
  <nav class="navbar navbar-light">
    <!--logo-->
    <a class="logo navbar-brand"><img src="images/logo1.png" alt="logo"></a>
    <!--user logout-->
    <!-- Single button -->
    <div class="btn-group pull-right user-menu logout">
      <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="location.assign('logout.php')"> <i class="fa fa-lock"></i> <small> <b>LOGOUT</b> <span>Click to logout</span> </small> </button>
    </div>
    <div class="btn-group pull-right user-menu">
      <button type="button" class="btn admin" > <img src="images/avatar.png" alt="logo" class="users"> <small> <b><?php echo $_SESSION['a_user_name']; ?></b> </small> </button>
    </div>
    <a class="btn btn-menu pull-right collapsed" role="button"  data-toggle="collapse" href="#sidebar" aria-expanded="false" aria-controls="collapseExample"><i class="fa fa-bars"></i> <span >Menu </span></a> </nav>
</header>
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog dialog-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel_2">Message</h4>
      </div>
      <div class="modal-body" id="result"> Are you sure, you want to delete this information. </div>
      <div class="modal-footer">
        <button type="button"  id="cancelButton" class="btn btn-primary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
