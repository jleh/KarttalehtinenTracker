<?php
// Saves uploaded image and add information to DB

include('yhteys.php');

if($_REQUEST['mode'] == 'save'){
    $allowedExts = array("jpg", "jpeg", "gif", "png");
    $extension = end(explode(".", $_FILES["image"]["name"]));
    if ((($_FILES["image"]["type"] == "image/gif")
    || ($_FILES["image"]["type"] == "image/jpeg")
    || ($_FILES["image"]["type"] == "image/png")
    || ($_FILES["image"]["type"] == "image/pjpeg"))
    && ($_FILES["image"]["size"] < 1000000)
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

                $lat = $_REQUEST["lat"];
                $lon = $_REQUEST["lon"];
                $comment = $_REQUEST["comment"];
                $image = 'images/'.$_FILES["image"]["name"];

                $query = $yhteys->prepare("INSERT INTO gps_images (image_path, lat, lon, comment) VALUES (?, ?, ?, ?)");
                $query->execute(array($image, $lat, $lon, $comment));

                echo $lat . " " . $lon . '<br>';

                echo "Image saved";
            }
        }
    } else {
        echo "File not saved.";
    }
}
else if($_REQUEST['mode'] == 'get'){
    $query = $yhteys->prepare('SELECT * FROM gps_images');
    $query->execute();
    
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
    $query = $yhteys->prepare('SELECT * FROM gps_images WHERE time > FROM_UNIXTIME(?)');
    $query->execute(array($time));
    
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
?>
