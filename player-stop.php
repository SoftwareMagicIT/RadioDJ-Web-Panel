<?php
require('config.php');
require_once('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$status = array(
	'success' => null,
	'changed' => false
);

try {
	$stopResponse = simplexml_load_file("http://".$ipAddress.":".$restPort."/opt?auth=".$restPassword."&command=StopPlayer");
	$status['success'] = (string)$response;
	$status['changed'] = $response == '200';
	$status['data'] = print_r($response, true);
} catch (Exception $e) {
	http_response_code(500);
	$status['error'] = $e->getMessage();
}

exit(json_encode($status));
