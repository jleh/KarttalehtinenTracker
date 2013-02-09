<?php
/**
 * Scripts for mobile commands.
 */
include("yhteys.php");

// Puts stop point on last recorded point
if($_GET['stop'] == 1){
    //Get last key
    $query = $yhteys->prepare("SELECT time FROM gps ORDER BY time DESC LIMIT 1");
    $query->execute();
    $result = $query->fetch();
    echo $result[0];

    //Set stop
    $query = $yhteys->prepare("UPDATE gps SET stop = 1 WHERE time = ?");
    $query->execute(array($result[0]));
}
?>