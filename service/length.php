<?php
include("yhteys.php");
$query = $yhteys->prepare("SELECT * FROM gps ORDER BY time");
$query->execute();

$total = 0;
$rn = 0;
$vLat = 0; $vLon = 0;
$R = 6371;
$kerroin = 3.14159 / 180;

while($row = $query->fetch()){
        if($row[stop] == 1){
                $rn = 2;
                echo $total."<br>";
                $total = 0;
        }
        if($rn == 1){
                $dlat = ($row[lat]-$vLat) * $kerroin;
                $dlon = ($row[long]-$vLon) * $kerroin;
                $lat = $row[lat] * $kerroin;
                $lat2 = $vLat * $kerroin;

                $a = sin($dlat/2) * sin($dlat / 2) + sin($dlon / 2) * sin($dlon / 2) * cos($lat) * cos($lat2);
                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                $d = $R * $c;
                $total = $total + $d;
        }
        $vLat = $row[lat];
        $vLon = $row[long];
        $rn = 1;
//	echo "$total $row[stop] <br>";
}

//	echo $total;
?>