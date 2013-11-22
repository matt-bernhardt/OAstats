<?php

$arrQuery = array();

// collect possible query parameters
if(isset($_GET["d"])) {
  $reqD = urldecode($_GET["d"]);
  $arrMatch = array( '$match' => array( 'dlc' => $reqD ) );
  array_push($arrQuery,$arrMatch);
}
if(isset($_GET["a"])) {
  $reqA = urldecode($_GET["a"]);
  $arrMatch = array( '$match' => array( 'author' => $reqA ) );
  array_push($arrQuery,$arrMatch);
}

// Project step
$arrProject = array('$project' => array(
    'time'=>array(
      '$substr' => array('$time',0,10)
      )
    )
  );
array_push($arrQuery,$arrProject);

// Group step
$arrGroup =   array('$group' => array(
    '_id'=>'$time', 
    'downloads'=>array('$sum'=>1)
    )
  );
array_push($arrQuery,$arrGroup);

// Sort step
$arrSort = array(
    '$sort' => array(
        '_id' => 1
      )
  );
array_push($arrQuery,$arrSort);

// connect to Mongo
$m = new MongoClient();
$db = $m->oastats;
$collection = $db->requests;

$cursor = $collection->aggregate($arrQuery);

/* 
Basic time series query:
db.requests.aggregate(
    [
        { $project: { time : { $substr : [ "$time", 0, 10] } } },
        { $group: { _id : "$time" , downloads: { $sum : 1 } } },
        { $sort: { _id : 1 } }
    ]
)

$cursor = $collection->aggregate(
	array('$project' => array(
		'time'=>array(
			'$substr' => array('$time',0,10)
      )
		)
	),
	array('$group' => array(
		'_id'=>'$time', 
		'downloads'=>array('$sum'=>1)
		)
	),
	array('$sort'=>array('_id'=>1))
);

*/

echo(json_encode($cursor["result"]));

?>