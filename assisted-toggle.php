<?php
require('config.php');
require('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$status = array(
	'assisted' => null,
	'changed' => false
);

try {
	$assistedStatus = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=StatusAssisted");

	$status['assisted'] = $assistedStatus == "True";

	if($status['assisted'] === false) {
		$status['changed'] = file_get_contents("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=EnableAssisted&arg=1");
		$status['assisted'] = true;
	} else {
		$status['changed'] = file_get_contents("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=EnableAssisted&arg=0");
		$status['assisted'] = false;
	}
} catch (Exception $e) {
	http_response_code(500);
	$status['error'] = $e->getMessage();
}
exit(json_encode($status));
