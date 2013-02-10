<?php
// Saves points from mobile GPS eg. from RGTracker
if($_GET['act'] == "s"){
    include("yhteys.php");
    $data = $_GET['d'];
    $arrayedData = explode("x", $data);

    $firstRow = explode(",", $arrayedData[0]);

    for($i = 1; $i < count($arrayedData); $i++){
            $row = explode(",", $arrayedData[$i]);
            for($j = 0; $j < 3; $j++)
                    $row[$j] = $firstRow[$j] + $row[$j];

            //Coordinates to float
            $row[1] = substr($row[1], 0, 2).".".substr($row[1], 2);
            $row[2] = substr($row[2], 0, 2).".".substr($row[2], 2);

            //Save data to database
            //Check duplicates and insert
            $query = $yhteys->prepare("SELECT COUNT(*) FROM gps WHERE time = ?");
            $query->execute(array($row[0]));
            $result = $query->fetch();	
            if($result[0] == 0) { // TODO: Get eventId from URL
                    $query = $yhteys->prepare("INSERT INTO gps VALUES(?, ?, ?, 0, 0, 2)");
                    $query->execute(array($row[0], $row[1], $row[2]));
            }
    }

    //Add first row to database
    //Convert coordinates
    $firstRow[1] = substr($firstRow[1], 0, 2).".".substr($firstRow[1], 2);
    $firstRow[2] = substr($firstRow[2], 0, 2).".".substr($firstRow[2], 2);

    $query = $yhteys->prepare("SELECT COUNT(*) FROM gps WHERE time = ?");
    $query->execute(array($firstRow[0]));
    $result = $query->fetch();

    if($result[0] == 0){
            $query = $yhteys->prepare("INSERT INTO gps VALUES(?, ?, ?, ?, 0, 2)");
            $query->execute($firstRow);
    }

    echo "ok";
}
?>