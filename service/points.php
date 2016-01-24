<?php
//Gives all points in JSON

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-type: application/json");

include('yhteys.php');

$kysely = $yhteys->prepare("SELECT * FROM gps ORDER BY time ASC");
$kysely->execute();

$points = array();
while($rivi = $kysely->fetch()){
   $points[] = array("time" => $rivi[time],
                      "lat" => $rivi[lat],
                      "lon" => $rivi[long],
                      "sat" => $rivi[satellites],
                      "stop" => $rivi[stop]);
}

echo json_encode($points);
?>