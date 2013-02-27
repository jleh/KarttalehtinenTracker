<?php 

$lineString = array(
        "type" => "Feature",
        "geometry" => array(
            "type" => "LineString",
            "coordinates" => $message)
    );

$featureCollection = array(
        "type" => "FeatureCollection",
        "features" => array($lineString)
    );

echo json_encode($featureCollection); 
?>