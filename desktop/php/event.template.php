<!-- event template -->
<style>
  .img-container {
    position: relative;
    display: inline-block;
    width: 100%;
    height: auto;
  }

  .imgSnap {
    width: 100%;
    height: auto;
    border-radius: 0;
  }

  .video-overlay {
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    --width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 8;
    overflow: hidden;
    border-radius: 10px;
    --scale: 1.1;
  }

  .video-overlay video {
    --width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .img-container:hover .video-overlay {
    display: block;
  }
</style>

<div data-date="<?= $date ?>" data-camera="<?= $camera ?>" data-label="<?= $label ?>" data-id="<?= $id ?>"
  class="frigateEventContainer">
  <div class="frigateEvent">

    <!-- div img -->
    <div class="img-container">
      <img class="imgSnap" src="<?= $hasSnapshot == 1 ? $img : '/plugins/frigate/data/no-image.png' ?>" />
      <!-- Hidden video container -->
      <?php if ($hasClip == 1): ?>
        <div class="video-overlay">
          <video src="<?= $clip ?>" autoplay muted loop></video>
        </div>
      <?php endif; ?>
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
        <button class="hover-button snapshot-btn" title="Voir la capture">
          <i class="fas fa-camera"></i>
        </button>
      <?php endif; ?>
      <?php if ($hasClip == 1): ?>
        <button class="hover-button video-btn" title="Voir la vidéo">
          <i class="fas fa-film"></i>
        </button>
      <?php endif; ?>
      <button class="hover-button" onclick="deleteEvent('<?= $id ?>')"
        title="Supprimer l'évènement sur votre serveur Frigate">
        <i class="fas fa-trash"></i>
      </button>
    </div>

  </div>
</div>
