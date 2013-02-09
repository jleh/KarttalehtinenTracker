<?php
//Gives all points in JSON
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