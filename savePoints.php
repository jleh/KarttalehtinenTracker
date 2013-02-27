<?php
/**
 * Saves points from KLTracker client. Points come from JSON format
 */

include 'old/service/yhteys.php';

$pointListJSON = str_replace('\"', '"', $_REQUEST['a']);
$pointList = json_decode($pointListJSON, true);

foreach ($pointList as $key => $value) {
    $time = $value['time']/1000;
    
    // Debug print
    // echo date('c', $time).' '.$value['lat'].' '.$value['lng'].'<br>';
    
    $query = $yhteys->prepare("INSERT INTO gps VALUES(?, ?, ?, 0, 0, 5)");
    $query->execute(array($value['time'], $value['lat'], $value['lng']));
}
echo '['.count($pointList).']';
?>
