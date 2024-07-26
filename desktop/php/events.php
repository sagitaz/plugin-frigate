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
      <a class="btn btn-danger roundedRight" id="deleteAll"><i class="fa fa-trash"></i> supprimer tous les évènements visibles </a>
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
    $id = $event['id'];
    $camera = $event['camera'];
    $label = $event['label'];
    $type = $event['type'];
    $date = $event['date'];
    $timeElapsed = timeElapsedString($date);
    $percentage = $event['percentage'];
    $duration = $event['duration'];
    $favoriteClass = $event['isFavorite'] ? 'fas fa-star' : 'far fa-star';
    $filterText = '';
    if ($type == 'new') {
      $filterText = 'Nouveau';
    } elseif ($type == 'update') {
      $filterText = 'En cours';
    }
    $cameraFound = false;
    $cameraId = 0;
    try {
      $frigateCamera = eqLogic::byLogicalId('eqFrigateCamera_' . $camera, 'frigate', false);
      if ($frigateCamera != false) {
        $cameraFound = true;
        $cameraId = $frigateCamera->getId();
      }
    } catch (Exception $e) {
      //echo "Erreur : " . $e->getMessage();
    }
	  $topScore = $event['top_score'];
    $duree = $event['duree'];
    $formattedDuration = '<div class=\'duration\'>' . formatDuration($duree) . '</div>';
    $formattedDurationTitle = '<div class=\'duration durationTitle\'>' . formatDuration($duree) . '</div>';
	  $img = $event['img'];
    $hasSnapshot = $event['hasSnapshot'];
    $snapshot = $event['snapshot'];
    $hasClip = $event['hasClip'];
    $clip = $event['clip'];
    
  	// event creation with a template
    include 'event.template.php';
  }
  echo '</div>';

  include 'event.modal.template.php';
  ?>

</div>

<?php include_file('desktop', 'events', 'css', 'frigate'); ?>
<?php include_file('desktop', 'events', 'js', 'frigate'); ?>