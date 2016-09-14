<?php
usleep(500000);
require('config.php');
require_once('includes/functions.php');

// Bust cache. Mainly for IE
header('Cache-Control: no-cache, no-store');
header('Expires: '.date(DATE_RFC822));

$xml = null;

try {
	$xml = simplexml_load_file("http://".$ipAddress.":".$restPort."/p?auth=".$restPassword);
} catch(Exception $e) {
	http_response_code(500);
	echo $e->getMessage();
}

if($xml) {
	if(isset($_GET['json'])) {
		header('Content-Type: text/json');
		echo json_encode($xml);
	} else {
		$rowID = 0;
		foreach ( $xml->SongData as $playlistItem ) {
			// Skip already playing track
			if($rowID == 0) {
?>
			<input type="hidden" id="currentTrack" data-id="<?php echo $playlistItem->ID; ?>" data-track="<?php echo htmlspecialchars(str_replace('{}', '""', json_encode($playlistItem, false)));?>">
<?php
			} else {

?>
			<tr class="item tracktype-<?php echo strtolower($playlistItem->TrackType); ?>" data-id="<?php echo $playlistItem->ID; ?>" data-index="<?php echo($rowID-1); ?>">
				<td class="controls">
					<button class="playbutton" title="Play this item"><?php echo($rowID); ?></button>
				</td>
				<td class="plTData">
					<h2 class="plArtist"><?php echo htmlspecialchars($playlistItem->Artist); ?></h2>
					<h3 class="plTitle"><?php echo htmlspecialchars($playlistItem->Title); ?></h3>
				</td>
				<td class="controls">
					<button class="clearbutton" title="Remove this item">&times;</button>
				</td>
			</tr>
<?php
			}
			$rowID++;
		}
	}
}
?>
