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
    <div class="img-container" onmouseenter="if (typeof handleHover === 'function') handleHover(this)">
      <img class="imgSnap" src="<?= $hasSnapshot == 1 ? $img : '/plugins/frigate/data/no-image.png' ?>" />
      <!-- Hidden video container idéal afficher les preview si hasclip est 0 ou que le param est 0 -->

      <?php
      if ($showClip) {
        echo '<div class="video-overlay">';
        echo '<video data-src="' . $clip . '" autoplay="" muted="" loop=""></video>';
        echo '</div>';
      } else {
        if ($hasPreview) {
          echo '<div>';
          echo '<img class="video-overlay" src="' . $preview . '" />';
          echo '</div>';
        }
      }


      ?>

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
        <div class="percentage <?= getPercentageClass($topScore) ?>"><?= $topScore ?>%</div>
      </span><br>
      <?php if ($cameraFound): ?>
        <div style='display: flex;align-items: center;gap: 10px;'>
          <a class="container-text" onclick="gotoCamera('<?= $cameraId ?>')" title="{{Afficher la page de la caméra}}">
          <?php endif; ?>
          <i class="fas fa-video"></i><span> <?= $camera ?></span>
          <?php if ($cameraFound): ?>
          </a>
          <?php $zonesFormatted = htmlspecialchars(str_replace('_', ' ', $zones)) ?>
          <?= $zones !== '' ? '<div class="zones"" title="' . $zonesFormatted . '">' . $zonesFormatted . '</div>' : '' ?>
        </div>
      <?php endif; ?>
      <i class="fas fa-clock"></i><span> <?= $date ?> <?= $hasClip == 1 ? $formattedDuration : '' ?></span>
    </div>

    <!-- div buttons -->
    <div class="eventBtns" 
      <?php echo 'data-eventid="' . $id . '"'; ?>
      <?php echo 'data-confirmdelete="' . (config::byKey('event::confirmDelete', 'frigate', 1) == 1 ? 'true' : 'false') . '"' ?>
      <?php if ($hasSnapshot == 1) echo 'data-snapshot="' . $snapshot . '"'; ?>
      <?php if ($hasClip == 1) echo 'data-video="' . $clip . '"'; ?>
      data-title="<i class='fas fa-minus-square'>&nbsp;</i>&nbsp;<?= $label ?><div class='percentage <?= getPercentageClass($topScore) ?>'><?= $topScore ?> %</div><br><i class='fas fa-video'>&nbsp;</i>&nbsp;<?= $camera ?><br><i class='fas fa-clock'>&nbsp;</i>&nbsp;<?= $date ?> <?= $hasClip == 1 ? $formattedDuration : '' ?>"
	    data-description="<?= $description ?>">
      <?php if ($hasSnapshot == 1): ?>
        <button class="hover-button snapshot-btn" title="{{Voir la capture}}">
          <i class="fas fa-camera"></i>
        </button>
      <?php endif; ?>
      <?php if ($hasClip == 1): ?>
        <button class="hover-button video-btn" title="{{Voir la vidéo}}">
          <i class="fas fa-film"></i>
        </button>
      <?php endif; ?>
      <button class="hover-button" onclick="deleteEvent('<?= $id ?>', <?= config::byKey('event::confirmDelete', 'frigate', 1) == 1 ? 'true' : 'false' ?>)"
        title="{{Supprimer l'évènement sur votre serveur Frigate}}">
        <i class="fas fa-trash"></i>
      </button>
      <?php if ($description != ''): ?>
        <button class="hover-button video-btn" title="<?= $description ?>">
          <i class="fas fas fa-comment"></i>
        </button>
      <?php endif; ?>
    </div>

  </div>
</div>