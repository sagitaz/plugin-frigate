<!-- event modal template -->
<div id="mediaModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div>
        <div id="mediaTitle"></div>
        <br />
        <div id="mediaDescription" class="limited-text"></div>
      </div>
      <div class="button-container">
        <button id="showVideo" class="hidden-btn custom-button"><i class="fas fa-video"></i>&nbsp;Voir la vidéo</button>
        <button id="showImage" class="hidden-btn custom-button"><i class="fas fa-camera"></i>&nbsp;Voir la capture</button><br />
        <span class="close">&times;</span>
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