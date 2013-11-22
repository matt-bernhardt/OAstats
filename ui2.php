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

// connect to Mongo
$m = new MongoClient();
$db = $m->oastats;
$collection = $db->requests;

?><!doctype html>
<html lang="en">
	<head>
		<title>Prototype</title>
		<link rel="stylesheet" href="/styles/reset.css">
		<link rel="stylesheet" href="/styles/styles.css">
    <link rel="stylesheet" href="/styles/data.css">
    <link rel="stylesheet" href="/styles/time.css">
    <link rel="stylesheet" href="/styles/map.css">
		<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
		<script src="http://d3js.org/d3.geo.projection.v0.min.js" charset="utf-8"></script>
		<script src="http://d3js.org/topojson.v1.min.js" charset="utf-8"></script>
	</head>
	<body>
		<div id="page">
			<div id="masthead">
				<h1>OA Statistics Alt Mockup</h1>
			</div>
			<div id="breadcrumb">
				<p>
					<span class="semantic">You are here: </span>
					<span class="level"><a href="ui2.php">Home</a></span>
					<?php if($strBreadcrumb!="") { ?>
						<span class="semantic">in subsection </span>
						<span class="level"><?php echo $strBreadcrumb; ?></span>
					<?php }; ?>
				</p>
			</div>
			<div id="list-builder">
				<p>This will be the list builder UI pattern.</p>
			</div>
			<div id="data">
<?php

/*
This needs to translate the MongoDB query into PHP syntax:
db.requests.aggregate( [ 
	{ $group : { _id : { dlc : "$dlc" , handle : "$handle" }, downloads : { $sum : 1 } } } , 
	{ $group : { _id : "$_id.dlc" , size : { $sum : 1 } , downloads : { $sum: "$downloads"} } } ,
	{ $sort : { _id : 1 } } 
] )

	array('$match'=>array('author'=>'http://example.com/author/1195')),
	array('$group'=>array('_id'=>'$handle','downloads'=>array('$sum'=>1)))

*/
// Query builder

$arrQuery = array();

if($reqD!="") {
	$strGroup = "Author";
	$charNext = "a";

	$arrMatch = array('$match' => array('dlc'=>$reqD) );
	array_push($arrQuery,$arrMatch);

	$arrGroup = array('$group' => array(
			'_id'=>array(
				'author'=>'$author',
				'handle'=>'$handle'
			),'downloads'=>array('$sum'=>1)
			)
		);
	array_push($arrQuery,$arrGroup);

	$arrGroup = array('$group' => array(
			'_id'=>'$_id.author', 
			'size'=>array('$sum'=>1),
			'downloads'=>array('$sum'=>'$downloads')
			)
		);
	array_push($arrQuery,$arrGroup);

} elseif ($reqA!="") {
	$strGroup = "Paper";
	$charNext = "";

	$arrMatch = array('$match' => array('author'=>$reqA) );
	array_push($arrQuery,$arrMatch);

	$arrGroup = array('$group' => array(
			'_id'=>'$handle',
			'downloads'=>array('$sum'=>1)
			)
		);
	array_push($arrQuery,$arrGroup);

} else {
	$strGroup = "Group";
	$charNext = "d";

	$arrGroup = array('$group' => array(
			'_id'=>array(
				'dlc'=>'$dlc',
				'handle'=>'$handle'
			),'downloads'=>array('$sum'=>1)
			)
		);
	array_push($arrQuery,$arrGroup);

	$arrGroup = array('$group' => array(
			'_id'=>'$_id.dlc', 
			'size'=>array('$sum'=>1),
			'downloads'=>array('$sum'=>'$downloads')
			)
		);
	array_push($arrQuery,$arrGroup);

}

$arrSort = array('$sort'=>array('_id'=>1));
array_push($arrQuery,$arrSort);

$cursor = $collection->aggregate($arrQuery);

?>
				<table>
					<thead>
						<tr>
							<th scope="col"><?php echo $strGroup; ?></th>
							<?php if(!isset($reqA)) { ?><th scope="col">Items</th><?php } ?>
							<th scope="col">Downloads</th>
						</tr>
					</thead>
					<tbody>
				<?php
				foreach($cursor["result"] as $document) {
				?>
					<tr>
						<td><a href="?<?php echo $charNext; ?>=<?php echo urlencode($document["_id"]); ?>"><?php echo $document["_id"]; ?></a></td>
						<?php if(!isset($reqA)) { ?><td><?php echo $document["size"]; ?></td><?php } ?>
						<td><?php echo $document["downloads"]; ?></td>
					</tr>
				<?php
				}
				?>
				</table>
			</div>

			<div id="time">
				<div id="chart"></div>
			</div>

<script>

var margin = {top: 20, right: 10, bottom: 30, left: 30},
    width = 920 - margin.left - margin.right,
    height = 580 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y-%m-%d").parse;

var x = d3.time.scale()
    .range([0, width]);

var y = d3.scale.linear()
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");

var line = d3.svg.line()
    .x(function(d) { return x(d._id); })
    .y(function(d) { return y(d.downloads); });

var svgT = d3.select("#chart").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.json("data/json-time.php?<?php echo $_SERVER["QUERY_STRING"]; ?>", function(error, data) {
  data.forEach(function(d) {
    d._id = parseDate(d._id);
    d.downloads = +d.downloads;
  });

  x.domain(d3.extent(data, function(d) { return d._id; }));
  // y.domain(d3.extent(data, function(d) { return d.downloads; }));
  y.domain([0, d3.max(data, function(d) { return d.downloads; })]);

  svgT.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  svgT.append("g")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "end")
      .text("Downloads");

  svgT.append("path")
      .datum(data)
      .attr("class", "line")
      .attr("d", line);
});

</script>

			<div id="map">
<?php

/*
db.requests.aggregate(
    [
        { 
            $group : { _id : "$country" , downloads: { $sum : 1 } }
        }
    ]
)
*/

// Mongo query
$arrQuery = array();

if($reqD!="") {
	$arrMatch = array('$match' => array('dlc'=>$reqD) );
	array_push($arrQuery,$arrMatch);
} elseif ($reqA!="") {
	$arrMatch = array('$match' => array('author'=>$reqA) );
	array_push($arrQuery,$arrMatch);
} else {

}

$arrGroup = array('$group' => array(
    '_id'=>'$country',
    'downloads'=>array('$sum'=>1),
    )
  );
array_push($arrQuery,$arrGroup);

$cursor = $collection->aggregate($arrQuery);

// Augment resultset - calculate hi/low, and add ISO_3166-1 country codes
$lo = 99999999;
$hi = 0;
$i = 0;
foreach($cursor["result"] as $document) {
  if ( $document["downloads"] > $hi ) { $hi = $document["downloads"]; }
  if ( $document["downloads"] < $lo ) { $lo = $document["downloads"]; }
  switch($document["_id"]) {
    case "CA":
      $cursor["result"][$i]["code"] = 124;
      break;
    case "DE":
      $cursor["result"][$i]["code"] = 276;
      break;
    case "ES":
      $cursor["result"][$i]["code"] = 724;
      break;
    case "FI":
      $cursor["result"][$i]["code"] = 246;
      break;
    case "FR":
      $cursor["result"][$i]["code"] = 250;
      break;
    case "GB":
      $cursor["result"][$i]["code"] = 826;
      break;
    case "IT":
      $cursor["result"][$i]["code"] = 380;
      break;
    case "MX":
      $cursor["result"][$i]["code"] = 484;
      break;
    case "NO":
      $cursor["result"][$i]["code"] = 578;
      break;
    case "SE":
      $cursor["result"][$i]["code"] = 752;
      break;
    case "US":
      $cursor["result"][$i]["code"] = 840;
      break;
  }
  $i++;
}

?>

<script>

var mapdata = <?php echo json_encode($cursor["result"]); ?>;

var width = 900, height = 450;

var color = d3.scale.category10();

var projection = d3.geo.equirectangular()
    .scale(143)
    .translate([width / 2, height / 2])
    .precision(.1);

var downloadScale = d3.scale.linear()
  .domain([0,<?php echo $hi; ?>])
  .range([0,5]);

var path = d3.geo.path()
    .projection(projection);

var graticule = d3.geo.graticule();

var svg = d3.select("#map").append("svg")
    .attr("width", width)
    .attr("height", height);

svg.append("defs").append("path")
    .datum({type: "Sphere"})
    .attr("id", "sphere")
    .attr("d", path);

svg.append("use")
    .attr("class", "stroke")
    .attr("xlink:href", "#sphere");

svg.append("use")
    .attr("class", "fill")
    .attr("xlink:href", "#sphere");

svg.append("path")
    .datum(graticule)
    .attr("class", "graticule")
    .attr("d", path);

d3.json("data/world-50m.json", function(error, world) {
  var countries = topojson.feature(world, world.objects.countries).features,
      neighbors = topojson.neighbors(world.objects.countries.geometries);

  svg.selectAll(".country")
      .data(countries)
    .enter().insert("path", ".graticule")
      .attr("class", "country")
      .attr("d", path)
      .attr("class", function(d, i) { 
        for(j=0;j<mapdata.length;j++){
          if(mapdata[j]["code"]===d.id) {
            return "dl_"+Math.floor(downloadScale(mapdata[j]["downloads"]));
          }
        }
        // d.id - from the topojson file - is what we use to look up and then quantize downloads
        return "dnull d"+d.id
      } );
      // .style("fill", function(d, i) { if(i===250){alert('hi');} return color(d.color = d3.max(neighbors[i], function(n) { return countries[n].color; }) + 1 | 0); });
        // "#ffd407");

  svg.insert("path", ".graticule")
      .datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
      .attr("class", "boundary")
      .attr("d", path);
});

d3.select(self.frameElement).style("height", height + "px");

</script>
<?php
// Debugging
echo "<p>Data range from ".$lo." to ".$hi."</p>";
var_dump($cursor["result"]);

?>
			</div>




		</div>
	</body>
</html>