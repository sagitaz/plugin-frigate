<?php

use frigate;
use log;

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

?>
<br>
<div class="col-lg-12">
	<?php
	$events = frigate::getEvents2();
	$events = array_reverse($events);

	foreach ($events as $event) {
		echo '<div class="col-lg-3" style="padding:5px;">';
		echo '<div class="col-lg-10" style="background-color: rgb(var(--defaultBkg-color)); border-radius: 10px; display: flex;">';
		echo '<div style="flex: 0 0 auto; margin-right: 10px;">';
		echo '<img src="' . $event['img'] . '" height="125" style="border-radius:10px;"/>';
		echo '</div>';
		echo '<div style="flex: 1 1 auto;">';
		echo '<h4>' . $event['label'] . '</h4><br>';
		echo '<span>' . $event['camera'] . '</span><br>';
		echo '<span>' . $event['date'] . ' (' . $event['duree'] . ' sc)</span>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
	?>

</div>