<?php
require('config.php');
require_once('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$status = array(
	'autodj' => false,
	'assisted' => false,
);

if(empty($restPassword)) {
	http_response_code(401);
	$status['error'] = "REST web panel is not configured. Please edit connection details in config.php";
	exit(json_encode($status));
}

try {
	//Check Auto DJ
	$autoDJStatus = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=StatusAutoDJ");

	// First check if auth password is correct
	if( 401 == (int)$autoDJStatus ) {
		http_response_code((int)$autoDJStatus);
		$status['error'] = "Connection to REST server works but password is incorrect";
		exit(json_encode($status));
	}

	$status['autodj'] = $autoDJStatus == "True";

	//Check Assisted
	$assistedStatus = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=StatusAssisted");
	$status['assisted'] = $assistedStatus == "True";
} catch(Exception $ex) {
	http_response_code(500);
	$status['error'] = $ex->getMessage();
}
echo json_encode($status);

?>
