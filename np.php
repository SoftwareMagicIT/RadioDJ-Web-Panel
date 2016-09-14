<?php
//sleep(1);
require('config.php');
require_once('includes/functions.php');

// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

try {
	$xml = simplexml_load_file("http://".$ipAddress.":".$restPort."/np?auth=".$restPassword."");
} catch (Exception $e) {
	echo '<div class="error">Error connecting to RadioDJ<pre>'.$ex->getMessage().'</pre></div>';
}

echo "/*\n".print_r($xml, true)."*/\n\n";

if( !$xml || (isset($xml->Remaining) && 0 === (int)$xml->Remaining) ) {
	echo "isplaying = false; resetNowPlaying();";
	exit();
}

$secondsRemaining = (int)$xml->Remaining;

if( $secondsRemaining === 0 ){
	$secondsRemaining = 5;
} else {
	//Override Refresh
	//$secondsRemaining="5";
}

echo "isplaying = true;";
echo "$('#nowPlayingArtist').html('".htmlspecialchars($xml->Artist)."');";
echo "$('#nowPlayingTitle').html('".htmlspecialchars($xml->Title)."');";
echo "document.getElementById('countdownNP').innerHTML = \"".gmdate("H:i:s", round($xml->Remaining))."\";";
//This defines when to check for now playing data again.
//echo "clearTimeout(nowPlayingMonitor)";
echo "nptime=setTimeout(nowPlayingMonitor, " . $secondsRemaining*1000 . ");";
echo "count();";
?>
