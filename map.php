<link rel="stylesheet" href="/styles/map.css">
<?php

// connect to Mongo
$m = new MongoClient();
$db = $m->oastats;
$collection = $db->requests;

/*
db.requests.aggregate(
    [
        { 
            $group : { _id : "$country" , downloads: { $sum : 1 } }
        }
    ]
)
*/

// Query builder
$arrQuery = array();

if(isset($_GET["d"])) {
  
  $reqD = urldecode($_GET["d"]);
  $arrMatch = array('$match' => array('dlc'=>$reqD) );
  array_push($arrQuery,$arrMatch);

} elseif (isset($_GET["a"])) {

  $reqA = urldecode($_GET["a"]);
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
/*  array('$group' => array(
    '_id'=>'$country',
    'downloads'=>array('$sum'=>1),
    )
  )
);
*/

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

<div id="map"></div>
<script>

var mapdata = <?php echo json_encode($cursor["result"]); ?>;

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    console.log(out);

    // or, if you wanted to avoid alerts...
/*
    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
*/
}

var width = 900,
    height = 450;

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
      // .style("fill", function(d, i) { if(i===250){alert(d.id);dump(d);} return color(d.color = d3.max(neighbors[i], function(n) { return countries[n].color; }) + 1 | 0); });
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
var_dump($cursor);

?>