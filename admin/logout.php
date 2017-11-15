<?php
if (!isset($_SERVER['HTTP_REFERER'])) {die ('<h2>Direct File Access NOT allowed</h2>');}
else {
  ob_start();
  session_start();
  session_destroy();
  header("location:index.html");
}
?>
