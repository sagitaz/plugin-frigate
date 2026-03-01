<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

?>

<script language="javascript">
  window.onload = function() {
    const el = document.getElementById("div_mainContainer");
    const savedScroll = parseFloat(localStorage.getItem("frigateScrollTop"));
    if (el && savedScroll !== null) {
      // Repositionnement de la liste à sa position Y 
      el.scrollTo(0, document.getElementById("frigateEventList").getBoundingClientRect().top + savedScroll);
      localStorage.removeItem("frigateScrollTop");
    }
  }
</script>

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
    '2j' => '- 2 jours',
    '1s' => '- 1 semaine'
  ];

  // events filters (template)
  include 'event.filters.template2.php';

  echo '<div id="frigateEventList" class="col-lg-12">';
  foreach ($events as $event) {
    // event variables
    $id = $event['eventId'];
    $camera = $event['camera'];
    $label = $event['label'];
    $type = $event['type'];
    $date = $event['date'];
    $timeElapsed = frigate::timeElapsedString($date);
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
    $formattedDuration = '<div class=\'duration\'>' . frigate::formatDuration($duree) . '</div>';
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

<?php include_file('desktop', 'events', 'css', 'frigate'); ?>
<?php include_file('desktop', 'events', 'js', 'frigate'); ?>