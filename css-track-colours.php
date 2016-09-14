<?php
include_once "includes/functions.php";

$CSSoutput = '';
if(isset($_FILES['file']) && UPLOAD_ERR_OK == $_FILES['file']['error']) {
	$track_colours = loadTrackColoursFromConfig($_FILES['file']['tmp_name']);
	foreach($track_colours as $type => $colour) {
		$CSSoutput .= "#playlistRows .tracktype-{$type} { background: {$colour}; }\n";
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Convert RadioDJ track colours to CSS</title>
		<meta name="viewport" content="width=device-width">
		<style media="screen">
			html, body {
				font-family: "Open Sans", "Helvetica Neue", Arial, sans-serif;
				font-size: 1.6em;
				font-weight: lighter;
			}
			b, strong {
				font-weight: normal;
			}
			.main {
				margin: 1em auto 3em;
				max-width: 650px;
				width: 100%;
			}
			section::after {
				content: '';
				clear: both;
				display: block;
			}
			form > p {
				margin: 0 auto 1em;
				display: block;
			}
			form > p::after {
				content: '';
				display: block;
				clear: both;
			}
			label {
				display: block;
				margin: 1em auto 0.3em;
				clear: both;
			}
			input, textarea, button {
				font-size: inherit;
				font-weight: inherit;
				margin: 0;
			}

			input[type=file]{
				padding:0;
				border: 0 none;
				width: 100%;
				position: relative;
			}
			input[type=file]::-webkit-file-upload-button {
				visibility: hidden;
			}
			input[type=file]::before {
				content: 'Select the file';
				display: block;
				background: #f9f9f9;
				background: linear-gradient(#f9f9f9, #e3e3e3);
				border: 1px solid #999;
				box-shadow: 0 2px 2px rgba(0,0,0,.5);
				padding: 5px 8px;
				outline: none;
				white-space: nowrap;
				-webkit-user-select: none;
				cursor: pointer;
				font-weight: normal;
				width: 100%;
				position: absolute;
				top: 0;
				left: 0;
			}
			input[type=file]:hover::before,
			input[type=file]:focus::before {
				border-color: black;
				background: linear-gradient(#f3f3f3, #eee);
			}
			input[type=file]:active::before {
				background: linear-gradient(#e3e3e3, #f9f9f9);
			}

			button[type=submit]{
				padding: 0.5em 1em;
				background-color: #3879D9;
				border: 0 none;
				color: #fff;
				box-shadow: 0 2px 2px rgba(0,0,0,.5);
				cursor: pointer;
				float: right;
			}
			button[type=submit]:hover,
			button[type=submit]:focus {
				box-shadow: 0 2px 3px rgba(0,0,0,.8);
				background-color: #2158ab;
			}
			#output {
				font-family: monospace;
				font-size: 16px;
				width: 100%;
				color: #555;
			}
			form .note {
				font-size: 0.5em;
				color: #555;
			}
		</style>
	</head>
	<body>
		<section class="main">
			<form class="" action="?convert" method="post" enctype="multipart/form-data">
				<p>
					<label for="file">Select <b>settings_titles.xml</b> file</label>
					<input type="file" id="file" name="file" accept=".xml,text/xml" />
					<span class="note">The file is located in RadioDJ folder</span>
				</p>
				<p>
					<button type="submit">Generate CSS</button>
				</p>
				<?php if(!empty($CSSoutput)) { ?>
				<p>
					<label for="output">Here is your CSS:</label>
					<textarea id="output" rows="17" cols="40" onfocus="return this.select();" spellcheck="off" readonly><?php echo htmlspecialchars($CSSoutput); ?></textarea>
					<span class="note">Now you can copy this CSS into <b>assets/style.css</b></span>
				</p>
				<?php } ?>
			</form>
		</section>
	</body>
</html>
