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

<?php
// Seuls les médias dont le fichier est présent sont proposés : une URL vers un fichier absent
// lance une requête vouée à l'échec, que Jeedom écrit dans le log http.error si le dossier manque.
$thumbnailSrc = ($hasSnapshot == 1 && frigate::mediaFileExists($img)) ? $img : '/plugins/frigate/core/img/no-image.png';
$snapshotAvailable = $hasSnapshot == 1 && frigate::mediaFileExists($snapshot);
$clipAvailable = $hasClip == 1 && frigate::mediaFileExists($clip);
// Le bouton de description ouvre la vidéo, à défaut la capture ; sans média, il se limite à son infobulle.
$descriptionBtnClass = $clipAvailable ? 'video-btn' : ($snapshotAvailable ? 'snapshot-btn' : '');
// Titre de la fenêtre du média : du HTML rangé dans un attribut, puis relu par innerHTML (events.js).
// Les valeurs y sont échappées une fois pour le HTML, le tout une seconde fois pour l'attribut.
$titleHtml = "<i class='fas fa-minus-square'>&nbsp;</i>&nbsp;" . frigate::escapeHtml($label)
  . "<div class='percentage " . frigate::getPercentageClass($topScore) . "'>" . frigate::escapeHtml($topScore) . " %</div>"
  . "<br><i class='fas fa-video'>&nbsp;</i>&nbsp;" . frigate::escapeHtml($camera)
  . "<br><i class='fas fa-clock'>&nbsp;</i>&nbsp;" . frigate::escapeHtml($date) . " " . ($hasClip == 1 ? $formattedDuration : '');
?>

<div data-date="<?= frigate::escapeHtml($date) ?>" data-camera="<?= frigate::escapeHtml($camera) ?>" data-label="<?= frigate::escapeHtml($label) ?>" data-id="<?= frigate::escapeHtml($id) ?>"
  class="frigateEventContainer">
  <div class="frigateEvent">

    <!-- div img -->
    <div class="img-container" onmouseenter="if (typeof handleHover === 'function') handleHover(this)">
      <img class="imgSnap" src="<?= frigate::escapeHtml($thumbnailSrc) ?>" />
      <!-- Hidden video container idéal afficher les preview si hasclip est 0 ou que le param est 0 -->

      <?php
      if ($showClip && $clipAvailable) {
        echo '<div class="video-overlay">';
        echo '<video data-src="' . frigate::escapeHtml($clip) . '" autoplay="" muted="" loop=""></video>';
        echo '</div>';
      } else {
        if ($hasPreview) {
          echo '<div>';
          echo '<img class="video-overlay" src="' . frigate::escapeHtml($preview) . '" />';
          echo '</div>';
        }
      }


      ?>

      <button class="favorite-btn" onclick="toggleFavorite(this)" data-id="<?= frigate::escapeHtml($id) ?>">
        <i class="<?= $favoriteClass ?>"></i>
      </button>
      <?php if (!empty($filterText)): ?>
        <div class="filter"><?= $filterText; ?></div>
      <?php endif; ?>
    </div>

    <!-- div text -->
    <div class="eventText">
      <span class="inline-title"><?= frigate::escapeHtml($label) ?></span>
      <span class="inline-subtitle duration"> <?= $timeElapsed ?></span><br /><br />
      <i class="fas fa-minus-square"></i>
      <span> <?= frigate::escapeHtml($label) ?>
        <div class="percentage <?= frigate::getPercentageClass($topScore) ?>"><?= frigate::escapeHtml($topScore) ?>%</div>
      </span><br>
      <?php if ($cameraFound): ?>
        <div style='display: flex;align-items: center;gap: 10px;'>
          <a class="container-text" onclick="gotoCamera('<?= $cameraId ?>')" title="{{Afficher la page de la caméra}}">
          <?php endif; ?>
          <i class="fas fa-video"></i><span> <?= frigate::escapeHtml($camera) ?></span>
          <?php if ($cameraFound): ?>
          </a>
          <?php $zonesFormatted = htmlspecialchars(str_replace('_', ' ', $zones)) ?>
          <?= $zones !== '' ? '<div class="zones"" title="' . $zonesFormatted . '">' . $zonesFormatted . '</div>' : '' ?>
        </div>
      <?php endif; ?>
      <i class="fas fa-clock"></i><span> <?= frigate::escapeHtml($date) ?> <?= $hasClip == 1 ? $formattedDuration : '' ?></span>
    </div>

    <!-- div buttons -->
    <?php $confirmDelete = config::byKey('event::confirmDelete', 'frigate', 1) == 1; ?>
    <div class="eventBtns" 
      data-eventid="<?= frigate::escapeHtml($id) ?>"
      data-confirmdelete="<?= $confirmDelete ? 1 : 0 ?>"
      <?= $snapshotAvailable ? 'data-snapshot="' . frigate::escapeHtml($snapshot) . '"' : '' ?>
      <?= $clipAvailable ? 'data-video="' . frigate::escapeHtml($clip) . '"' : '' ?>
      data-title="<?= frigate::escapeHtml($titleHtml) ?>"
	    data-description="<?= frigate::escapeHtml($description) ?>">

      <?php if ($snapshotAvailable): ?>
        <button class="hover-button snapshot-btn" title="{{Voir la capture}}">
          <i class="fas fa-camera"></i>
        </button>
      <?php endif; ?>
      <?php if ($clipAvailable): ?>
        <button class="hover-button video-btn" title="{{Voir la vidéo}}">
          <i class="fas fa-film"></i>
        </button>
      <?php endif; ?>
      <button class="hover-button" onclick="deleteEvent(<?= frigate::escapeHtml(json_encode((string) $id)) ?>)"
        title="{{Supprimer l'évènement sur votre serveur Frigate}}">
        <i class="fas fa-trash"></i>
      </button>
      <?php if ($description != ''): ?>
        <button class="hover-button <?= $descriptionBtnClass ?>" title="<?= frigate::escapeHtml($description) ?>">
          <i class="fas fas fa-comment"></i>
        </button>
      <?php endif; ?>
    </div>

  </div>
</div>