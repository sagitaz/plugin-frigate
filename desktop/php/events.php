<?php

use frigate;
use log;

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

?>


<div class="col-lg-12">
  <br><br>
  <div class="input-group" style="margin-bottom:20px">
    <span class="input-group-btn">
      <a class="btn roundedLeft" id="gotoHome"><i class="fa fa-arrow-circle-left"></i> {{retour}} </a>
      <a class="btn btn-danger roundedRight" id="deleteAll"><i class="fa fa-trash"></i> {{supprimer tous les évènements visibles}} </a>
  	  <a class="btn btn-warning roundedRight" id="createEvent"><i class="fas fa-plus-circle"></i> {{créer un nouvel évènement}} </a>
    </span>
  </div>

  <?php

  // functions
  function getPercentageClass($score) 
  {
    $score = (int) $score;
    if ($score === 100) return 'percentage-100';
    if ($score >= 90) return 'percentage-99';
    if ($score >= 80) return 'percentage-89';
    if ($score >= 70) return 'percentage-79';
    if ($score >= 60) return 'percentage-69';
    if ($score >= 50) return 'percentage-59';
    if ($score >= 40) return 'percentage-49';
    if ($score >= 30) return 'percentage-39';
    if ($score >= 20) return 'percentage-29';
    if ($score >= 10) return 'percentage-19';
    if ($score > 0) return 'percentage-9';
    
    return 'percentage-0';
  }
  
  function formatDuration($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $formattedDuration = '';
    if ($hours > 0) {
      $formattedDuration .= $hours . 'h';
      $formattedDuration .= ' ' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'mn';
      //$formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } elseif ($minutes > 0) {
      $formattedDuration .= $minutes . 'mn';
      $formattedDuration .= ' ' . str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
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

    if (!$full)
      $string = array_slice($string, 0, 1);
    return $string ? 'il y a ' . implode(', ', $string) : 'à l\'instant';
  }

  $events = frigate::showEvents();

  // cameras variables
  $selectedCameras = isset($_GET['cameras']) ? explode(',', $_GET['cameras']) : [];
  $cameras = array_unique(array_column($events, 'camera'));

  // labels variables
  $selectedLabels = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
  $labels = array_unique(array_column($events, 'label'));

  // filters variables
  $selectedTimeFilter = isset($_GET['delai']) ? $_GET['delai'] : '';
  $timeFilters = [
    '' => 'Toutes les dates',
    '1h' => 'Moins d\'une heure',
    '2h' => 'Moins de deux heures',
    '6h' => 'Moins de six heures',
    '12h' => 'Moins de douze heures',
    '1j' => 'Moins d\'un jour',
    '2j' => 'Moins de deux jours',
    '1s' => 'Moins d\'une semaine'
  ];

  // events filters (template)
  include 'event.filters.template.php';

  echo '<div class="frigateEventList col-lg-12">';
  foreach ($events as $event) {
    // event variables
    $id = $event['id'];
    $camera = $event['camera'];
    $label = $event['label'];
    $type = $event['type'];
    $date = $event['date'];
    $timeElapsed = timeElapsedString($date);
    $percentage = $event['percentage'] ?? 0;
    $duration = $event['duration'] ?? 0;
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
    $zones = $event['zones'];

    // event creation (template)
    include 'event.template.php';
  }
  echo '</div>';

  // event modal (template)
  include 'event.modal.template.php';
  ?>

</div>

<?php include_file('desktop', 'events', 'css', 'frigate'); ?>
<?php include_file('desktop', 'events', 'js', 'frigate'); ?>
