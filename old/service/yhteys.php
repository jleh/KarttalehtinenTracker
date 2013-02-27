<?php
try {
    $yhteys = new PDO("mysql:host=localhost;dbname=julehtinen_suunnnistus", "juleh_user", "gepsi");
} catch (PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
$yhteys->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>