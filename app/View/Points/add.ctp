<h1>Add point</h1>

<?php
echo $this->Form->create('Point');
echo $this->Form->input('lat');
echo $this->Form->input('lng');
echo $this->Form->input('time');
echo $this->Form->input('event');
echo $this->Form->end('Save');
?>