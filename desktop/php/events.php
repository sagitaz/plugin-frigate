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
      <a class="btn roundedLeft" id="gotoHome"><i class="fa fa-arrow-circle-left"></i> retour </a>
      <a class="btn btn-danger roundedRight" id="deleteAll"><i class="fa fa-trash"></i> supprimer tous les events visibles </a>
    </span>
  </div>
  <?php

  function formatDuration($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $formattedDuration = '';
    if ($hours > 0) {
      $formattedDuration .= $hours . 'h';
      $formattedDuration .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'mn';
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } elseif ($minutes > 0) {
      $formattedDuration .= $minutes . 'mn';
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } else {
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    }

    return $formattedDuration;
  }

  function timeElapsedString($datetime, $full = false)
  {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
      'y' => 'année',
      'm' => 'mois',
      'w' => 'semaine',
      'd' => 'jour',
      'h' => 'heure',
      'i' => 'minute',
      's' => 'seconde',
    );

    foreach ($string as $k => &$v) {
      if ($diff->$k) {
        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
        unset($string[$k]);
      }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'il y a ' . implode(', ', $string) : 'à l\'instant';
  }

  $events = frigate::showEvents();

  echo '<div class="col-sm-10 flex-container">';
  echo '  <a class="btn btn-info button-xs" id="selectAllCameras" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
  echo '  <a class="btn btn-info button-xs" id="deselectAllCameras" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
  echo '  <div class="checkbox-container">';
  $selectedCameras = isset($_GET['cameras']) ? explode(',', $_GET['cameras']) : [];
  $cameras = array_unique(array_column($events, 'camera'));
  foreach ($cameras as $camera) {
    $isChecked = empty($selectedCameras) || in_array($camera, $selectedCameras);
    echo '    <label><input type="checkbox" class="eqLogicAttr cameraFilter" value="' . $camera . '" ' . ($isChecked ? 'checked' : '') . '>';
    echo '    <span class="custom-checkbox"></span> ' . ucfirst($camera) . '</label>';
  }
  echo '  </div>';
  echo '</div>';

  echo '<div class="col-sm-10 flex-container">';
  echo '  <a class="btn btn-info button-xs" id="selectAllLabels" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
  echo '  <a class="btn btn-info button-xs" id="deselectAllLabels" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
  echo '  <div class="checkbox-container">';
  $selectedLabels = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
  $labels = array_unique(array_column($events, 'label'));
  foreach ($labels as $label) {
    $isChecked = empty($selectedLabels) || in_array($label, $selectedLabels);
    echo '    <label><input type="checkbox" class="eqLogicAttr labelFilter" value="' . $label . '" ' . ($isChecked ? 'checked' : '') . '> ';
    echo '    <span class="custom-checkbox"></span> ' . ucfirst($label) . '</label>';
  }
  echo '  </div>';
  echo '</div>';

  echo '<div class="col-sm-12" style="margin-bottom:10px">';
  echo '<div class="col-sm-4 datetime-container">
        <label>Entre <input type="datetime-local" id="startDate"></label>
        <label>et <input type="datetime-local" id="endDate"></label>
        <label>Ou de </label>
    </div>';

  $selectedTimeFilter = isset($_GET['delai']) ? $_GET['delai'] : '';

  $timeFilters = [
    ''    => 'Toutes les dates',
    '1h'  => 'Moins d\'une heure',
    '2h'  => 'Moins de deux heures',
    '6h'  => 'Moins de six heures',
    '12h' => 'Moins de douze heures',
    '1j'  => 'Moins d\'un jour',
    '2j'  => 'Moins de deux jours',
    '1s'  => 'Moins d\'une semaine'
  ];

  echo '<div class="col-sm-8 radio-container">';
  foreach ($timeFilters as $value => $label) {
    $isChecked = $value === $selectedTimeFilter;
    echo '<label><input type="radio" name="timeFilter" value="' . $value . '" ' . ($isChecked ? 'checked' : '') . '>';
    echo '<span class="custom-radio"></span> ' . $label . '</label>';
  }
  echo '</div>';
  echo '</div>';

  echo '<div class="frigateEventList col-lg-12">';
  foreach ($events as $event) {
    //div globale start
    echo '<div data-date="' . $event['date'] .  '" data-camera="' . $event['camera'] . '" data-label="' . $event['label'] . '" data-id="' . $event['id'] . '" class="frigateEventContainer">';
    echo '<div class="frigateEvent">';
    // div img
    $favoriteClass = $event['isFavorite'] ? 'fas fa-star' : 'far fa-star';
    $type = $event['type'];
    $filterText = '';
    if ($type == 'new') {
      $filterText = 'Nouveau';
    } elseif ($type == 'update') {
      $filterText = 'En cours';
    }
    echo '<div class="img-container">';
    echo '<img class="imgSnap" src="' . $event['img'] . '"/>';
    echo '<button class="favorite-btn" onclick="toggleFavorite(this)" data-id="' . $event['id'] . '" >';
    echo '<i class="' . $favoriteClass . '"></i>';
    echo '</button>';
    if (!empty($filterText)) {
      echo '<div class="filter">' . $filterText . '</div>';
    }
    echo '</div>';
    // div texte
    echo '<div class="eventText">';
    $timeElapsed = timeElapsedString($event['date']);
    echo '<span class="inline-title">' . $event['label'] . '</span><span class="inline-subtitle duration"> ' . $timeElapsed . '</span><br/><br/>';
    echo '<i class="fas fa-minus-square"></i><span>  ' . $event['label'] . ' <div class="percentage" data-percentage="' . $event['top_score'] . '">' . $event['top_score'] . '%</div></span><br>';

    $cameraFound = false;
    $cameraId = 0;
    try {
      $attribut = 'name';
      $valeurRecherchee = $event['camera'];
      $frigateCamera = eqLogic::byLogicalId('eqFrigateCamera_' . $valeurRecherchee, 'frigate', false);
      if ($frigateCamera != false) {
        $cameraFound = true;
        $cameraId = $frigateCamera->getId();
      }
    } catch (Exception $e) {
      //echo "Erreur : " . $e->getMessage();
    }

    if ($cameraFound) {
      echo '<a onclick="gotoCamera(\'' . $cameraId . '\')" title="Afficher la page de la caméra">';
    }
    echo '<i class="fas fa-video"></i><span>  ' . $event['camera'] . '</span>';
    if ($cameraFound) {
      echo '</a>';
    }
    echo '<br>';

    $formattedDuration = '<div class=\'duration\'>' . formatDuration($event['duree']) . '</div>';
    $formattedDurationTitle = '<div class=\'duration durationTitle\'>' . formatDuration($event['duree']) . '</div>';

    echo '<i class="fas fa-clock"></i><span>  ' . $event['date'] . ' ' . $formattedDuration . '</span>';
    echo '</div>';
    // div buttons
    echo '<div class="eventBtns"';
    if ($event['hasSnapshot'] == 1) echo ' data-snapshot="' . $event['snapshot'] . '"';
    if ($event['hasClip'] == 1) echo ' data-video="' . $event['clip'] . '"';
    echo ' data-title="' . $event['label'] . ' <div class=\'percentage percentageTitle\' data-percentage=\'' . $event['top_score'] . '\'>' . $event['top_score'] . ' %</div> - ' . $event['camera'] . ' - ' . $event['date'] . ' ' . $formattedDurationTitle . '"';
    echo '>';
    if ($event['hasSnapshot'] == 1) {
      echo '<button class="hover-button snapshot-btn" title="Voir le snapshot">';
      echo '<i class="fas fa-camera"></i>';
      echo '</button>';
    }
    if ($event['hasClip'] == 1) {
      echo '<button class="hover-button video-btn" title="Voir le clip">';
      echo '<i class="fas fa-film"></i>';
      echo '</button>';
    }
    echo '<button class="hover-button" onclick="deleteEvent(\'' . $event['id'] . '\')" title="Supprimer l\'event sur votre serveur Frigate">';
    echo '<i class="fas fa-trash"></i>';
    echo '</button>';
    echo '</div>';
    // div globale end
    echo '</div>';
    echo '</div>';
  }
  echo '</div>';

  ?>

  <div id="mediaModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>

      <div class="modal-header">
        <h2 id="mediaTitle"></h2>
        <div class="button-container">
          <button id="showVideo" class="hidden-btn custom-button">Voir la vidéo</button>
          <button id="showImage" class="hidden-btn custom-button">Voir la snapshot</button>
        </div>
      </div>
      <div class="media-container">
        <div class="video-container active">
          <video id="videoPlayer" width="100%" controls autoplay>
            <source id="videoSource" src="" type="video/mp4">
            Votre navigateur ne supporte pas la balise vidéo.
          </video>
        </div>
        <div class="image-container">
          <img id="snapshotImage" src="" alt="Snapshot" width="100%">
        </div>
      </div>
    </div>
  </div>

</div>

<?php include_file('desktop', 'events', 'css', 'frigate'); ?>
<?php include_file('desktop', 'events', 'js', 'frigate'); ?>