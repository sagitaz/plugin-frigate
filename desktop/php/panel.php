<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

$allObject = jeeObject::buildTree(null, false);
$frigate_widgets = array();
if (init('object_id') == '') {
  foreach ($allObject as $object) {
    foreach ($object->getEqLogic(true, true, 'frigate') as $frigate) {
      if ($frigate->getLogicalId() != 'eqFrigateStats' && $frigate->getLogicalId() != 'eqFrigateEvents' && $frigate->getConfiguration("panel") == true) {
        $frigate_widgets[] = array('widget' => $frigate->toHtml('panel'));
      }
    }
  }
}

?>

<ul class="nav nav-tabs" role="tablist">
  <li role="presentation" class="active">
    <a href="#Cameras" aria-controls="Cameras" role="tab" data-toggle="tab" data-url="/get-cameras-content">Caméras</a>
  </li>
  <li role="presentation">
    <a href="#Events" aria-controls="Events" role="tab" data-toggle="tab" data-url="/get-events-content">Evènements</a>
  </li>
 <!-- <li role="presentation">
    <a href="#Health" aria-controls="Health" role="tab" data-toggle="tab" data-url="/get-health-content">Santé</a>
  </li> -->
  <!--<li role="presentation">
    <a href="#Snapshots" aria-controls="Snapshots" role="tab" data-toggle="tab" data-url="/get-snapshots-content">Captures</a>
  </li>-->
</ul>

<div class="tab-content" id="div_configuration" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
  <div role="tabpanel" class="tab-pane active" id="Cameras">
    <?php
    echo '<div class="col-lg-12" style="width: 100%;">';
    foreach ($frigate_widgets as $widget) {
      echo '<div class="col-lg-4" style="padding-top: 10px">';
      echo $widget['widget'];
      echo '</div>';
    }
    echo '</div>';
    ?>
  </div>


  <div role="tabpanel" class="tab-pane" id="Events">
    <div class="col-lg-12">
      <br><br>
      <div class="input-group" style="margin-bottom:20px">
        <span class="input-group-btn">
          <a class="btn btn-danger roundedLeft" id="deleteAll"><i class="fa fa-trash"></i> {{supprimer tous les évènements visibles}} </a>
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
        '' => 'Toutes',
        '1h' => '- 1h',
        '2h' => '- 2h',
        '6h' => '- 6h',
        '12h' => '- 12h',
        '1j' => '- 1 jour',
        '2j' => '- 2 jour',
        '1s' => '- 1 semaine'
      ];

      // events filters (template)
      include 'event.filters.template2.php';

      echo '<div class="frigateEventList col-lg-12">';
      foreach ($events as $event) {
        // event variables
        $id = $event['eventId'];
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
    	  $description = $event['description'];
        $duree = $event['duree'];
        $formattedDuration = '<div class=\'duration\'>' . formatDuration($duree) . '</div>';
        $formattedDurationTitle = '<div class=\'duration durationTitle\'>' . formatDuration($duree) . '</div>';
        $img = $event['img'];
        $hasSnapshot = $event['hasSnapshot'];
        $snapshot = $event['snapshot'];
        $hasClip = $event['hasClip'];
        $clip = $event['clip'];
        $preview = str_replace("snapshot.jpg", "preview.gif", $event["snapshot"]);
        $hasPreview = file_exists("/var/www/html" . $preview);
        $zones = $event['zones'];
        $showClip = 0;
        if (config::byKey('event::displayVideo', 'frigate', true) == true && $hasClip == 1) {
          $showClip = 1;
        }

        // event creation (template)
        include 'event.template.php';
      }
      echo '</div>';

      // event modal (template)
      include 'event.modal.template.php';
      ?>

    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="Health">
  </div>

  <div role="tabpanel" class="tab-pane" id="Snapshots">
    <?php
    // Définit le chemin du dossier
    $dir = dirname(__FILE__, 3) . "/data/snapshots/";

    // Initialise une variable pour le HTML
    $div = '<div class="row gallery-container" style="padding-top: 10px;">';

    // Vérifie si le répertoire existe
    if (is_dir($dir)) {
      // Scanne le répertoire et récupère tous les fichiers
      $files = array_diff(scandir($dir), array('..', '.')); // Exclut les dossiers parent et courant

      // Parcourt les fichiers dans l'ordre inverse
      foreach (array_reverse($files) as $val) {
        // Construit le chemin du fichier complet
        $file = str_replace("/var/www/html/", "", $dir . $val);
        $timestamp = explode('.', $val)[0];
        $timestamp = (int) $timestamp;
        $name = date('Y-m-d H:i:s', $timestamp);

        // Vérifie si c'est bien un fichier avant de l'afficher
        if (is_file($file)) {
          // Construit le contenu HTML pour chaque fichier
          $div .= '<div class="col-12 col-sm-6 col-md-4 col-lg-3 gallery-item">';
          $div .= '<div class="img-container">';
          $div .= '<img src="' . $file . '" alt="" class="img-fluid gallery-img" onclick="openModal(this)" />';
          $div .= '<div class="image-caption"><label>' . $name . '</label></div>';
          $div .= '<a class="btn btn-xs btn-danger delete-btn" id="' . $file . '" onclick="removeImg(this)"><i class="fas fa-trash"></i> {{supprimer}}</a>';
          $div .= '</div>';
          $div .= '</div>';
        }
      }
    }

    $div .= '</div>'; // Fermeture de la rangée
    // Affiche la div contenant toutes les images
    echo $div;
    ?>
  </div>

  <!-- Modal Structure -->
  <div id="modalSnap" class="modal" onclick="closeModal()">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalSnapImg">
    <div id="caption"></div>
  </div>


</div>

<?php include_file('desktop', 'events', 'css', 'frigate'); ?>
<?php include_file('desktop', 'events', 'js', 'frigate'); ?>
<?php include_file('desktop', 'panel', 'js', 'frigate'); ?>
<?php include_file('desktop', 'panel', 'css', 'frigate'); ?>