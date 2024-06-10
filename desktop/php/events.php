<?php

use frigate;
use log;

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

?>

<br>
<br>
<div class="input-group" style="display:inline-flex">
	<span class="input-group-btn">
		<a class="btn geoAction rounded" data-action="gotoHome"><i class="fa fa-arrow-circle-left"></i> retour </a>
	</span>
</div>
<br>
<br>
<div class="col-lg-12">
	<?php
	$events = frigate::getEvents2();
	foreach ($events as $event) {
		//div globale start
		echo '<div class="col-lg-4 ">';
		echo '<div class="col-lg-12 frigateEvent">';
		// div img
		echo '<div>';
		echo '<img class="imgSnap" src="' . $event['img'] . '"/>';
		echo '</div>';
		// div texte
		echo '<div class="eventText">';
		echo '<h4>' . $event['label'] . '</h4><br>';
		echo '<i class="fas fa-minus-square"></i><span>  ' . $event['label'] . ' (' . $event['top_score'] . ' %)</span><br>';
		echo '<i class="fas fa-video"></i><span>  ' . $event['camera'] . '</span><br>';
		echo '<i class="fas fa-clock"></i><span>  ' . $event['date'] . ' (' . $event['duree'] . ' sc)</span>';
		echo '</div>';
		// div buttons
		echo '<div class="eventBtn">';
		if ($event['hasSnapshot'] == 1) {
			echo '<button class="hover-button" onclick="openClip(this)" id="' . $event['snapshot'] . '">';
			echo '<i class="fas fa-image"></i>';
			echo '</button>';
		}
		if ($event['hasClip'] == 1) {
			echo '<button class="hover-button" onclick="openSnapshot(this)" id="' . $event['clip'] . '">';
			echo '<i class="fas fa-camera"></i>';
			echo '</button>';
		}
		echo '<button class="hover-button" onclick="deleteEvent()">';
		echo '<i class="fas fa-trash"></i>';
		echo '</button>';
		echo '</div>';
		// div globale end
		echo '</div>';
		echo '</div>';
	}
	?>

</div>


<style>
	.frigateEvent {
		display: flex;
		background-color: rgb(var(--defaultBkg-color));
		margin-bottom: 10px;
		border-radius: 10px;
	}

	.imgSnap {
		display: flex: 0 0 auto;
		position: relative;
		//   background-color: rgb(var(--defaultBkg-color));
		margin-left: -15px;
		height: 125px;
		border-bottom-left-radius: 10px;
		border-top-left-radius: 10px;

	}

	.eventText {
		display: flex: 1 1 auto;
		position: relative;
		margin-left: 20px;

	}

	.eventBtn {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		margin-left: auto;

	}

	.hover-button {
		background: none;
		border: none;
		color: rgb(var(--defaultText-color));
		font-size: 20px;
	}

	.hover-button:hover~.hover-image {
		display: block;
	}

	.hover-button-container {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		/* Align buttons to the right */
	}
</style>

<?php include_file('desktop', 'events', 'js', 'frigate'); ?>