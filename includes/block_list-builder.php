<div id="list-builder">
<?php
// connect to Mongo
$m = new MongoClient();
$db = $m->oastats;
$collection = $db->requests;

if ($reqD!="") {
	echo "<p>DLC List Builder</p>";
} else if ($reqA!="") {
	echo "<p>Author List Builder</p>";
} else {
	echo "<p>General List Builder</p>";
}

$cursor = $collection->aggregate(
	array('$group' => array( '_id'=>'$dlc')),
	array('$sort'=>array('_id'=>1))
);

?>
	<form method="get">
	<select id="filter" name="filter" multiple="true">
	<?php
		foreach($cursor["result"] as $document) {
			echo "<option value=\"".$document["_id"]."\">".$document["_id"]."</option>";
		}
	?>
	</select>
	<input type="submit">
	</form>
</div>
<script type="text/javascript" src="/scripts/list-builder.js"></script>