<?php
require('config.php');
require('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$status = array(
	'autodj' => null,
	'changed' => false
);

try {
	$autodjStatus = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=StatusAutoDJ");

	$status['autodj'] = $autodjStatus == "True";

	if($status['autodj'] === false) {
		$status['changed'] = file_get_contents("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=EnableAutoDJ&arg=1");
		$status['autodj'] = true;
	} else {
		$status['changed'] = file_get_contents("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=EnableAutoDJ&arg=0");
		$status['autodj'] = false;
	}
} catch (Exception $e) {
	http_response_code(500);
	$status['error'] = $e->getMessage();
}
exit(json_encode($status));
