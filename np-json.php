<?php
sleep(1); // Wait for RadioDJ to update current playing track
require('config.php');
require_once('includes/functions.php');

header('Content-Type: text/json');
// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$data = array(
	'artist' => '-',
	'title' => '-',
	'secondsRemaining' => 0,
	'isplaying' => false,
	'timeRemaining' => '00:00:00',
	'error' => null
);

try {
	$xml = simplexml_load_file("http://".$ipAddress.":".$restPort."/np?auth=".$restPassword."");
} catch(Exception $ex) {
	$xml = null;
	http_response_code(500);
	$data['error'] = $ex->getMessage();
}

if( !$xml || (isset($xml->Remaining) && 0 === (int)$xml->Remaining) ) {
	exit(json_encode($data));
}

$secondsRemaining = (float)$xml->Remaining;
$secondsRemaining = $secondsRemaining;

if( $secondsRemaining === 0 ){
	$secondsRemaining = 5;
}

$data = array(
	'artist' => htmlspecialchars($xml->Artist),
	'title' => htmlspecialchars($xml->Title),
	'secondsRemaining' => $secondsRemaining,
	'isplaying' => true,
	'timeRemaining' => gmdate("H:i:s", round($xml->Remaining)),
	'data' => $xml,
);
exit(json_encode($data));
?>
