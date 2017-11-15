<?php
$remoteLocalServer = 1;
if ( $remoteLocalServer == 1 ) 
{
//   define("DB_HOST", "instapartyz.com");
   define("DB_HOST", "localhost");
   define('DB_NAME', 'insta_partyz_db');
   define('DB_USER', 'insta');
   define('DB_PASSWORD', 'Ff@45722');
//define('DB_PASSWORD', 'Ff@45126&^%');
   $dirPath = "instaapi";
}
else 
{
    define( "DB_HOST", "localhost" );
    define( "DB_USER", "root" );
    define( "DB_PASS", "" );
    define( "DB_NAME", "insta" );
    $dirPath = "instaapi";
}
define( "DEBUG_FLAG_ON",false);
$pathString = "http://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/" . $dirPath . "/";
?>