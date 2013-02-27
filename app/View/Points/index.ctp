<h1>Points</h1>

<?php
foreach($points as $point): ?>
    <?php echo $point['Point']['lat'] ?>
    <?php echo $point['Point']['lng'] ?>
<?php endforeach; ?>