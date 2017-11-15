<?php
define('UPLOADS_PATH',BASE_PATH.'images/');
define('DISPLAY_PATH',__BASE_URI__.'images/');

define('UPLOADS_PROFILE_PATH',BASE_PATH.'uploads/profile/');
define('DISPLAY_PROFILE_PATH',__BASE_URI__.'uploads/profile/');

define('UPLOADS_EMOJI_PATH',BASE_PATH.'uploads/emoji/');
define('DISPLAY_EMOJI_PATH',__BASE_URI__.'uploads/emoji/');

define('UPLOADS_CIRCLE_PATH',BASE_PATH.'uploads/circle/');
define('DISPLAY_CIRCLE_PATH',__BASE_URI__.'uploads/circle/');

define('UPLOADS_GROUP_PATH',BASE_PATH.'uploads/group/');
define('DISPLAY_GROUP_PATH',__BASE_URI__.'uploads/group/');

define("_USER_TYPE_USER_",1);
define("_USER_TYPE_ADMIN_",2);

$_user_types[_USER_TYPE_USER_] = array('title'=>'Normal User');
$_user_types[_USER_TYPE_ADMIN_] = array('title'=>'Admin');

define("_CREATE_EVENT_",1);
define("_SENT_INVITE_",2);
define("_SENT_INVITE_RESPONSE_",3);
define("_CREATE_EVENT_RESPONSE_",4);

$_group_types[_CREATE_EVENT_] = array('title'=>'Create Event');
$_group_types[_SENT_INVITE_] = array('title'=>'Sent Invite');

define("_INVITE_ACCEPT_",1);
define("_INVITE_REJECT_",2);

define('_SENT_INVITE_MESSAGE_',' has invited you to his grid.');
define('_INVITE_ACCEPT_MESSAGE_',' has accepted your grid request.');
define('_INVITE_REJECT_MESSAGE_',' has rejected your grid request.');
?>