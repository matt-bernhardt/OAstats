<?php

$strBreadcrumb = "";
// collect possible query parameters
$reqD = "";
if(isset($_GET["d"])) {
	$reqD = urldecode($_GET["d"]);
	$strBreadcrumb = $reqD;
}
$reqA = "";
if(isset($_GET["a"])) {
	$reqA = urldecode($_GET["a"]);
	$strBreadcrumb = $reqA;
}

?><!doctype html>
<html lang="en">
	<head>
		<title>Prototype</title>
		<link rel="stylesheet" href="/styles/reset.css">
		<link rel="stylesheet" href="/styles/styles.css">
		<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
		<script src="http://d3js.org/d3.geo.projection.v0.min.js" charset="utf-8"></script>
		<script src="http://d3js.org/topojson.v1.min.js" charset="utf-8"></script>
		<script>
		$(function() {
			$( "#tabs" ).tabs({
				beforeLoad: function( event, ui ) {
					ui.jqXHR.error(function() {
						ui.panel.html(
						"Couldn't load this tab. We'll try to fix this as soon as possible. " +
						"If this wouldn't be a demo." );
					});
				}
			});
		});
		</script>
	</head>
	<body>
		<div id="page">
			<div id="masthead">
				<h1>OA Statistics Mockup</h1>
			</div>
			<div id="breadcrumb">
				<p>
					<span class="semantic">You are here: </span>
					<span class="level"><a href="ui.php">Home</a></span>
					<?php if($strBreadcrumb!="") { ?>
						<span class="semantic">in subsection </span>
						<span class="level"><?php echo $strBreadcrumb; ?></span>
					<?php }; ?>
				</p>
			</div>
			<div id="list-builder">
				<p>This will be the list builder UI pattern.</p>
			</div>
			<div id="tabs">
				<ul>
					<li><a href="data.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">Data</a></li>
					<li><a href="time.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">Timeline</a></li>
					<li><a href="map.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">Map</a></li>
				</ul>
			</div>
		</div>
	</body>
</html>