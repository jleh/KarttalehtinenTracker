<?php
echo $this->Html->css('trackerOL');
echo $this->Html->script(array('http://openlayers.org/api/OpenLayers.js',
                               'http://code.jquery.com/jquery.min.js', 
                               'MML', 'trackerOL'));
?>

<h1>Tracker</h1>

<div id="map"></div>