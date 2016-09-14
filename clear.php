<?php
require('config.php');
require_once('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$status = array(
	'success' => false,
	'data' => null
);

try {
	$assistedStatus = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=ClearPlaylist");
	$status['success'] = $assistedStatus;
	$status['data'] = $assistedStatus;
} catch (Exception $e) {
	http_response_code(500);
	$status['error'] = $e->getMessage();
}
exit(json_encode($status));
