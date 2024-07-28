<!-- event template -->
<div data-date="<?= $date ?>" data-camera="<?= $camera ?>" data-label="<?= $label ?>" data-id="<?= $id ?>"
  class="frigateEventContainer">
  <div class="frigateEvent">

    <!-- div img -->
    <div class="img-container">
      <img class="imgSnap" src="<?= $img ?>" />
      <button class="favorite-btn" onclick="toggleFavorite(this)" data-id="<?= $id ?>">
        <i class="<?= $favoriteClass ?>"></i>
      </button>
      <?php if (!empty($filterText)): ?>
        <div class="filter"><?= $filterText; ?></div>
      <?php endif; ?>
    </div>

    <!-- div text -->
    <div class="eventText">
      <span class="inline-title"><?= $label ?></span>
      <span class="inline-subtitle duration"> <?= $timeElapsed ?></span><br /><br />
      <i class="fas fa-minus-square"></i>
      <span> <?= $label ?>
        <div class="percentage" data-percentage="<?= $topScore ?>"><?= $topScore ?>%</div>
      </span><br>
      <?php if ($cameraFound): ?>
        <a onclick="gotoCamera('<?= $cameraId ?>')" title="Afficher la page de la caméra">
        <?php endif; ?>
        <i class="fas fa-video"></i><span> <?= $camera ?></span>
        <?php if ($cameraFound): ?>
        </a>
      <?php endif; ?>
      <br>
      <i class="fas fa-clock"></i><span> <?= $date ?> <?= $formattedDuration ?></span>
    </div>

    <!-- div buttons -->
    <div class="eventBtns" <?php if ($hasSnapshot == 1)
      echo 'data-snapshot="' . $snapshot . '"'; ?> <?php if ($hasClip == 1)
               echo 'data-video="' . $clip . '"'; ?>
      data-title="<?= $label ?> <div class='percentage percentageTitle' data-percentage='<?= $topScore ?>'><?= $topScore ?> %</div> - <?= $camera ?> - <?= $date ?> <?= $formattedDurationTitle ?>">
      <?php if ($hasSnapshot == 1): ?>
        <button class="hover-button snapshot-btn" title="Voir le snapshot">
          <i class="fas fa-camera"></i>
        </button>
      <?php endif; ?>
      <?php if ($hasClip == 1): ?>
        <button class="hover-button video-btn" title="Voir le clip">
          <i class="fas fa-film"></i>
        </button>
      <?php endif; ?>
      <button class="hover-button" onclick="deleteEvent('<?= $id ?>')"
        title="Supprimer l'event sur votre serveur Frigate">
        <i class="fas fa-trash"></i>
      </button>
    </div>

  </div>
</div>