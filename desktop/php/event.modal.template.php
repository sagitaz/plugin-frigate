<!-- event modal template -->
<div id="mediaModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>

      <div class="modal-header">
        <h2 id="mediaTitle"></h2>
        <div class="button-container">
          <button id="showVideo" class="hidden-btn custom-button">Voir la vidéo</button>
          <button id="showImage" class="hidden-btn custom-button">Voir la capture</button>
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