<?php
require_once('includes/functions.php');
?>
<!DOCTYPE html>
<html>
<!-- For use with RadioDJ 1.7.5+ with REST Server Plugin -->
<head>
	<meta charset="utf-8" />
	<title>RallyPodium & Reporting Radio Control Panel</title>
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" type="text/css" href="assets/style.css">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/images/REST-panel-icon.png">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<!--
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.js"></script>
	-->
</head>

<body>
<div id="statusbar" class="noselect">
	<div class="notification" style="display:none;">
	</div>
	<div class="controls">
		<button id="play" title="Play or play next"><span>play</span></button>
		<button id="stop" title="Stop playback"><span>stop</span></button>
	</div>
	<div class="nowPlaying">
		<h2 class="title" id="nowPlayingTitle">-</h2>
		<h3 class="artist" id="nowPlayingArtist">-</h3>
	</div>
	<div class="npTimes" id="countdownNP">--:--:--</div>
</div>
<div id="optionsRow" class="noselect">
	<button class="button activated" id="autodj" title="Enable/Disable Automatic Playlist Generation">AUTODJ</button>
	<button class="button activated" id="assisted" title="Enable/Disable Playlist Auto Advance">AUTOMATED</button>
	<button class="button normal" id="clear" title="Clear The Current Playlist">CLEAR</button>
</div>
<div class="playlist-container noselect">
	<table id="playlistRows">
	</table>
</div>
<div class="log" id="thelog"></div>
<div class="overlay"></div>
<script>
(function($){
	$(document).ready(function() {
		$('#play').click(function(){
			playPlaylistTrack(0);
			$(this).blur();
		});

		$('#stop').click(function(){
			playerSTOP();
			$(this).blur();
		});

		$('#autodj').click(function(){
			toggleAutoDJ();
			$(this).blur();
		});

		$('#assisted').click(function(){
			toggleAssisted();
			$(this).blur();
		});

		$('#clear').click(function(){
			clearPlaylist();
			$(this).blur();
		});

		$('#playlistRows').on('click', '.playbutton', function(){
			$(this).parents('tr.item').remove();
			var trackRow = $(this).parents('tr.item')
			var index = trackRow.data('index');
			playPlaylistTrack(index);
			trackRow.remove();
		});

		$('#playlistRows').on('click', '.clearbutton', function(){
			var trackRow = $(this).parents('tr.item')
			var index = trackRow.data('index');
			dropPlaylistTrack(index);
			trackRow.remove();
		});

		if(window.console != undefined)
			console.log( "RadioDJ REST Web Interface Ready!" );

		// Prevent IE from caching AJAX requests
		$.ajaxSetup({ cache: false });

		var xhrFailed = false;
		$( document ).ajaxError(function(event, jqxhr, settings, thrownError) {
			// Stop all timers if error occurs
			stop();
			var message = thrownError;

			if( jqxhr.status >= 400 || jqxhr.status === 0 ) {
				message = jqxhr.responseJSON != undefined ? jqxhr.responseJSON.error : (jqxhr.responseText? jqxhr.responseText : jqxhr.status+" - "+thrownError);
			}

			$('.notification').html('<p class="error notice">We have encountered an error. See <a href="#thelog">log messages</a>.</p>').slideDown('fast');

			$(".log" ).append('<div class="notice error"><b>Error:</b> '+ message +'</div>');

			if(window.console != undefined)
				console.log(jqxhr, thrownError);
			xhrFailed = true;
		});

		// Check for connection status and init only if connection works
		$.ajax({
			url: "status-check.php",
			method: 'GET',
			success: function(){
				init();
			}
		});

	});

	var montime, nptime, playingTimer, rdjStatusTimeout;
	var isplaying = false;
	var timeRemaining = 0;
	var trackEndsAt = new Date();
	var trackID = 0;

	function init() {
		rdjStatus();
		nowPlayingMonitor();
		playlistMonitor();
	}

	function stop(){
		clearTimeout(montime);
		clearTimeout(nptime);
		clearTimeout(playingTimer);
		clearTimeout(rdjStatusTimeout);
		resetNowPlaying();
	}

	function nowPlayingMonitor() {
		$.get("np-json.php", function(data){
			$('#nowPlayingArtist').html(data.artist);
			$('#nowPlayingTitle').html(data.title);
			timeRemaining = data.secondsRemaining;
			trackEndsAt = new Date(new Date().valueOf()+(data.secondsRemaining*1000));
			if(data.secondsRemaining > 0) {
				nptime = setTimeout(nowPlayingMonitor, data.secondsRemaining*1000);
			}
			isplaying = data.isplaying;
			count();
			playlistMonitor();
		});
	}

	function playlistMonitor() {
		clearTimeout(montime);
		$.get("plmon.php", function(response){
			$('#playlistRows').html(response);
			montime = setTimeout(playlistMonitor, 5000);

			if(trackID != $('#currentTrack').data('id')) {
				nowPlayingMonitor();
			}

			trackID = $('#currentTrack').data('id');
		});
	}

	function toggleAutoDJ() {
		$.get("autodj-toggle.php", function(data) {
			if(data.autodj == true) {
				$('#autodj').text('AUTODJ').removeClass('deactivated').addClass('activated');
			} else {
				$('#autodj').text('MANUAL').removeClass('activated').addClass('deactivated');
			}
			nowPlayingMonitor();
		});
	}

	function toggleAssisted() {
		$.get("assisted-toggle.php", function(data) {
			if(data.assisted == true) {
				$('#assisted').text('ASSISTED').removeClass('activated').addClass('deactivated');
			} else {
				$('#assisted').text('AUTOMATED').removeClass('deactivated').addClass('activated');;
			}
			nowPlayingMonitor();
		});
	}

	function clearPlaylist() {
		$.get("clear.php", function(){
			$('#playlistRows .item').remove();
			playlistMonitor();
		});
	}

	function rdjStatus() {
		$.get("status-check.php", function(status) {

			clearTimeout(rdjStatusTimeout);
			rdjStatusTimeout = setTimeout(rdjStatus, 5000);

			if(status.autodj) {
				$('#autodj').text('AUTODJ').removeClass('deactivated').addClass('activated');
			} else {
				$('#autodj').text('MANUAL').removeClass('activated').addClass('deactivated');
			}

			if(status.assisted) {
				$('#assisted').text('ASSISTED').removeClass('activated').addClass('deactivated');
			} else {
				$('#assisted').text('AUTOMATED').removeClass('deactivated').addClass('activated');;
			}

		});
	}

	function playPlaylistTrack(playlistid) {
		clearTimeout(nptime);
		$.get("player-start.php?index="+playlistid, function(data){
			if(data.changed) {
				nowPlayingMonitor();
			}
		});
	}

	function dropPlaylistTrack(playlistid) {
		$.get("item-remove.php?index="+playlistid, function(data){
			if (data.success) {
				playlistMonitor();
			} else {
				alert('Could not remove track');
			}
		});
	}

	function playerSTOP() {
		$.get("player-stop.php", function(data){
			if(data.changed) {
				clearTimeout(nptime);
				clearTimeout(playingTimer);
				setTimeout(nowPlayingMonitor, 5000);
			}
		});
	}

	function setTimer(seconds) {
		var formated = '00:00:00';
		if(seconds >= 0) {
			var time = new Date();
			time.setHours(0);
			time.setMinutes(0);
			time.setSeconds(seconds);
			formated = time.toTimeString().split(" ")[0];
		}
		$('#countdownNP').text(formated);
	}

	function count() {

		// Remove duplicate playingTimer
		clearTimeout(playingTimer);

		var timeRemaining = (trackEndsAt - new Date())/1000;
		setTimer(timeRemaining);

		if(timeRemaining > 0) {
			playingTimer= setTimeout(count, 1000);
		}
	}

	function resetNowPlaying(){
		$('#countdownNP').text('--:--:--');
		$('#nowPlayingArtist').text('-');
		$('#nowPlayingTitle').text('-');
	}
})(jQuery);

</script>
</body>
</html>
