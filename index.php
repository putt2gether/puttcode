<?php
if(isset($_GET['method'])){
	$jsonData = file_get_contents('php://input');
	$method = strtolower($_GET['method']);
	$parser = (isset($_REQUEST['parser']) ? $_REQUEST['parser'] : 'json');
	 $data=json_decode($jsonData,true); 
	$request_url = isset($_SERVER['REQUEST_URI']) ? ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) : "";
	
	if(isset($data['version']) && trim($data['version'])=='2') {
		$new_api_path = dirname(__FILE__).'/api_v2/api/index.php';
		require_once($new_api_path);
		exit;
	}
	else {
		$old_api_path = dirname(__FILE__).'/puttdemo/index.php';
		//$old_api_path = dirname(__FILE__).'/api_v2/api/index.php';
		require_once($old_api_path);
		exit;
	}
}
else{
	echo json_encode(array('status' => 21, 'msg' => 'Method Not Specified'));
	exit;
}
?>