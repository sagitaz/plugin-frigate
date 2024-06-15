<?php

use frigate;
use log;

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

?>


<div class="col-lg-12"><br>
	<br>
	<div class="input-group" style="margin-bottom:20px">
		<span class="input-group-btn">
			<a class="btn geoAction rounded" id="gotoHome"><i class="fa fa-arrow-circle-left"></i> retour </a>
		</span>
	</div>
	<?php
	$events = frigate::showEvents();

	echo '<div class="col-sm-9" style="margin-bottom:5px">';
  	echo '<a class="btn btn-info button-xs" id="selectAllCameras" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
    echo '<a class="btn btn-info button-xs" id="deselectAllCameras" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
	$cameras = array_unique(array_column($events, 'camera'));
    foreach ($cameras as $camera) {
        echo '<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr cameraFilter" value="' . $camera . '" checked> ' . ucfirst($camera) . '</label>';
    }
	echo '</div>';

	echo '<div class="col-sm-9" style="margin-bottom:20px">';
  	echo '<a class="btn btn-info button-xs" id="selectAllLabels" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
    echo '<a class="btn btn-info button-xs" id="deselectAllLabels" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
	$labels = array_unique(array_column($events, 'label'));
    foreach ($labels as $label) {
        echo '<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr labelFilter" value="' . $label . '" checked> ' . ucfirst($label) . '</label>';
    }
	echo '</div>';

	echo '<div>';
	foreach ($events as $event) {
		//div globale start
		echo '<div data-camera="' . $event['camera'] .  '" data-label="' . $event['label'] .  '" class="frigateEventContainer col-lg-4 ">';
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
		echo '<button class="hover-button" onclick="deleteEvent(this)" id="' . $event['id'] . '" title="Supprimer l\'event sur votre serveur frigate">';
		echo '<i class="fas fa-trash"></i>';
		echo '</button>';
		echo '</div>';
		// div globale end
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';

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

    .eventHidden {
      display: none;
    }

</style>

<?php include_file('desktop', 'events', 'js', 'frigate'); ?>