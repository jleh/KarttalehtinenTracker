<?php
// Returns points in GeoJSON format
header("Content-type: application/json");

$featureType = $_GET['featureType'];

if($featureType == 'route'){
    printRoute();
}
else if($featureType == 'currentLocation'){
    printCurrentLocation();
}

function printRoute(){
    include('yhteys.php');
    $kysely = $yhteys->prepare("SELECT * FROM gps ORDER BY time ASC");
    $kysely->execute();

    $points = array();
    $geometry = array();
    while($rivi = $kysely->fetch()){
        // TODO: Remove debug break and implement path splitting
        if($rivi[stop] == 1)       
            break;
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
function printCurrentLocation(){
    include('yhteys.php');
    $kysely = $yhteys->prepare("SELECT * FROM gps ORDER BY time ASC");
    $kysely->execute();

    $points = array();
    $geometry = array();
    $pointCoords = array(0, 0); // Point of current location
    while($rivi = $kysely->fetch()){
        $pointCoords[0] = $rivi[long];
        $pointCoords[1] = $rivi[lat];

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
        "properties" => array("prop0" => "Current")
    );

    $output = array(
        "type" => "FeatureCollection",
        "features" => array( $currentLocation)
    );

    echo json_encode($output);
}
?>
