<?php
// GeoJSON server

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-type: application/json");

// Debug parameter
// TODO: Implement event id handling
$event = 5;

$featureType = $_GET['featureType'];

if($featureType == 'route'){
    printRoute($event);
}
else if($featureType == 'currentLocation'){
    printCurrentLocation($event);
}
else if($featureType == 'routeAfterTime'){
    printRouteAfter($_GET['timestamp'], $event);
}

function printRoute($event){
    include('yhteys.php');
    $kysely = $yhteys->prepare("SELECT * FROM gps WHERE event = ? ORDER BY time ASC");
    $kysely->execute(array($event));

    $geometry = array();
    while($rivi = $kysely->fetch()){
        // Debug route splitting
        //if($rivi[stop] == 1)       
        //    break;
        // TODO: Implement route splitting with stop points
       $geometry[] = array($rivi[long], $rivi[lat]);
    }

    // Route feature
    $feature = array(
        "type" => "Feature",
        "geometry" => array(
            "type" => "LineString",
            "coordinates" => $geometry)
    );

    $output = array(
        "type" => "FeatureCollection",
        "features" => array($feature)
    );

    echo json_encode($output);
}

// TODO: Implement real query
function printCurrentLocation($event){
    include('yhteys.php');
    $kysely = $yhteys->prepare("SELECT * FROM gps WHERE event = ? ORDER BY time ASC");
    $kysely->execute(array($event));

    $points = array();
    $pointCoords = array(0, 0); // Point of current location
    $time = 0;
    while($rivi = $kysely->fetch()){
        $pointCoords[0] = $rivi[long];
        $pointCoords[1] = $rivi[lat];
        $time = $rivi[time];

        $points[] = array("time" => $rivi[time],
                          "lat" => $rivi[lat],
                          "lon" => $rivi[long],
                          "sat" => $rivi[satellites],
                          "stop" => $rivi[stop]);
       if($rivi[stop] == 1)       
           break;
    }

    // Current location feature
    $currentLocation = array(
        "type" => "Feature",
        "geometry" => array(
            "type" => "Point",
            "coordinates" => $pointCoords
        ),
        "properties" => array("time" => $time)
    );

    $output = array(
        "type" => "FeatureCollection",
        "features" => array( $currentLocation)
    );

    echo json_encode($output);
}

// Gets route after timestamp
function printRouteAfter($time, $event){
    include('yhteys.php');
    $kysely = $yhteys->prepare("SELECT * FROM gps WHERE time >= ? AND event = ? ORDER BY time ASC");
    $kysely->execute(array($time, $event));
    
    $geometry = array();
    $timestamp = 0;
    $lastPoint; // Store last point of route for current location
    while($rivi = $kysely->fetch()){
        // Debug route splitting
        //if($rivi[stop] == 1)       
        //    break;
       $geometry[] = array($rivi[long], $rivi[lat]);
       $timestamp = $rivi[time];
       $lastPoint[0] = $rivi[long];
       $lastPoint[1] = $rivi[lat];
    }

    // Route feature
    $feature = array(
        "type" => "Feature",
        "geometry" => array(
            "type" => "LineString",
            "coordinates" => $geometry),
        "properties" => array(
            "time" =>  $timestamp
        )
    );
    
    // Last point feature
    $point = array(
        "type" => "Feature",
        "geometry" => array(
            "type" => "Point",
            "coordinates" => $lastPoint
        ),
        "properties" => array("time" => $timestamp)
    );

    $output = array(
        "type" => "FeatureCollection",
        "features" => array($feature, $point)
    );

    echo json_encode($output);
}
?>
