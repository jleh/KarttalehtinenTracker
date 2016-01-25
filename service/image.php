<?php
// Saves uploaded image and add information to DB

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-type: application/json");

include('yhteys.php');

$eventId = 5;

if($_REQUEST['mode'] == 'save'){
    $allowedExts = array("jpg", "jpeg", "gif", "png");
    $extension = end(explode(".", $_FILES["image"]["name"]));
    if ((($_FILES["image"]["type"] == "image/gif")
    || ($_FILES["image"]["type"] == "image/jpeg")
    || ($_FILES["image"]["type"] == "image/png")
    || ($_FILES["image"]["type"] == "image/pjpeg"))
    && ($_FILES["image"]["size"] < 10000000)
    && in_array($extension, $allowedExts)) {

        if($_FILES["image"]["error"] > 0){
            echo "Error" . $_FILES["image"]["error"];
        }
        else {
            echo "Upload: " . $_FILES["image"]["name"]."<br>";
            echo "Type: " . $_FILES["image"]["type"]."<br>";
            echo "Size: " . ($_FILES["image"]["size"] / 1024)."<br>";
            echo "Tmp file: " . $_FILES["image"]["tmp_name"]."<br>";

            $image_path = "../images/".$_FILES["image"]["name"];

            if(file_exists($image_path)){
                echo "Image not saved, already exist";
            } else {
                if(!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)){
                    echo 'Save failed';
                    return;
                }

                $exif = exif_read_data($image_path);

                $lon = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
                $lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);

                if ($lat == 0 || $lon == 0)
                    die("Coordinates not found");

                $comment = $_REQUEST["comment"];
                $image = 'images/'.$_FILES["image"]["name"];

                $query = $yhteys->prepare("INSERT INTO gps_images (image_path, lat, lon, comment, event) VALUES (?, ?, ?, ?, ?)");
                $query->execute(array($image, $lat, $lon, $comment, $eventId));

                echo "\n Coordinates: " . $lat . " " . $lon . '<br>';

                echo "Image saved";
            }
        }
    } else {
        echo "File not saved. " . $_FILES["image"]["size"];
    }
}
else if($_REQUEST['mode'] == 'get'){
    $query = $yhteys->prepare('SELECT * FROM gps_images WHERE event = ?');
    $query->execute(array($eventId));
    
    $images;
    while($row = $query->fetch()){
        $images[] = array("image" => $row['image_path'],
                          "lat" => $row['lat'],
                          "lon" => $row['lon'],
                          "comment" => $row['comment'],
                          "time" => strtotime($row['time']));
    }
    
    echo json_encode($images);
}
else if($_REQUEST['mode'] == 'getNew'){
    $time = $_REQUEST['time'];
    $query = $yhteys->prepare('SELECT * FROM gps_images WHERE time > FROM_UNIXTIME(?) AND event = ?');
    $query->execute(array($time, $eventId));
    
    $images = array();
    while($row = $query->fetch()){
        $images[] = array("image" => $row['image_path'],
                          "lat" => $row['lat'],
                          "lon" => $row['lon'],
                          "comment" => $row['comment'],
                          "time" => strtotime($row['time']));
    }
    
    echo json_encode($images);
}

function getGps($exifCoord, $hemi) {

    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

}

function gps2Num($coordPart) {

    $parts = explode('/', $coordPart);

    if (count($parts) <= 0)
        return 0;

    if (count($parts) == 1)
        return $parts[0];

    return floatval($parts[0]) / floatval($parts[1]);
}


?>
