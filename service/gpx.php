<?php
    //Gives track on GPX file
    header("Content-disposition: attachment; filename=ilveshiihto.gpx");
    header("content-type: application/xml");
    include('yhteys.php');

    echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?><gpx version="1.0" creator="Karttalehtinen" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.topografix.com/GPX/1/0" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">';

    echo "<trk><name>Ilveshiihto 2012</name><trkseg>";

    $kysely = $yhteys->prepare("SELECT * FROM gps ORDER BY time ASC");
    $kysely->execute();

    $points = array();
    while($rivi = $kysely->fetch()){
       $points[] = array("time" => $rivi[time],
                          "lat" => $rivi[lat],
                          "long" => $rivi[long],
                          "sat" => $rivi[satellites],
                          "stop" => $rivi[stop]);
         echo '<trkpt lat="'.$rivi[lat].'" lon="'.$rivi[long].'">';
         echo '<ele>4.46</ele><time>2009-10-17T18:37:26Z</time></trkpt>';
    }

    echo "</trkseg></trk></gpx>";
?>