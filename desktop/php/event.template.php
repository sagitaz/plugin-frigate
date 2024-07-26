<!-- div template event -->
<div data-date="<?php echo $date ?>" data-camera="<?php echo $camera ?>" data-label="<?php echo $label ?>" data-id="<?php echo $id ?>" class="frigateEventContainer">
    <div class="frigateEvent">

        <!-- div img -->
        <div class="img-container">
            <img class="imgSnap" src="<?php echo $img ?>"/>
            <button class="favorite-btn" onclick="toggleFavorite(this)" data-id="<?php echo $id ?>">
                <i class="<?php echo $favoriteClass ?>"></i>
            </button>
            <?php if (!empty($filterText)): ?>
                <div class="filter"><?php echo $filterText; ?></div>
            <?php endif; ?>
        </div>

        <!-- div texte -->
        <div class="eventText">
            <span class="inline-title"><?php echo $label ?></span>
            <span class="inline-subtitle duration"> <?php echo $timeElapsed ?></span><br/><br/>
            <i class="fas fa-minus-square"></i>
            <span> <?php echo $label ?>
                <div class="percentage" data-percentage="<?php echo $topScore ?>"><?php echo $topScore ?>%</div>
            </span><br>
            <?php if ($cameraFound): ?>
                <a onclick="gotoCamera('<?php echo $cameraId ?>')" title="Afficher la page de la caméra">
            <?php endif; ?>
            <i class="fas fa-video"></i><span>  <?php echo $camera ?></span>
            <?php if ($cameraFound): ?>
                </a>
            <?php endif; ?>
            <br>
            <i class="fas fa-clock"></i><span> <?php echo $date ?> <?php echo $formattedDuration ?></span>
        </div>

        <!-- div buttons -->
        <div class="eventBtns" 
            <?php if ($hasSnapshot == 1) echo 'data-snapshot="' . $snapshot . '"'; ?>
            <?php if ($hasClip == 1) echo 'data-video="' . $clip . '"'; ?> 
            data-title="<?php echo $label ?> <div class='percentage percentageTitle' data-percentage='<?php echo $topScore ?>'><?php echo $topScore ?> %</div> - <?php echo $camera ?> - <?php echo $date ?> <?php echo $formattedDurationTitle ?>"
        >
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
            <button class="hover-button" onclick="deleteEvent('<?php echo $id ?>')" title="Supprimer l'event sur votre serveur Frigate">
                <i class="fas fa-trash"></i>
            </button>
        </div>

    </div>
</div>
