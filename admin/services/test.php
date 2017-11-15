<?php
require_once(dirname(__FILE__).'../../config/db_config.php');
$db = $database;
$query="select * from golf_hole_index";
$golf_index_data=$db->FetchQuery($query);
$golf_index_data=$golf_index_data[0];
echo count($golf_index_data);
 ?>
