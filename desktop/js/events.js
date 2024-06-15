/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* Permet la réorganisation des commandes dans l'équipement */

function showMedia(mediaType, src, hasVideo, hasSnapshot) {
  var mediaModal = document.getElementById('mediaModal');
  var videoContainer = document.querySelector('.video-container');
  var imageContainer = document.querySelector('.image-container');
  var videoPlayer = document.getElementById('videoPlayer');
  var videoSource = document.getElementById('videoSource');
  var snapshotImage = document.getElementById('snapshotImage');
  var showVideoBtn = document.getElementById('showVideo');
  var showImageBtn = document.getElementById('showImage');

  if (mediaType === 'video') {
    videoSource.src = src;
    videoPlayer.load();
    videoContainer.classList.add('active');
    imageContainer.classList.remove('active');
  } 
  else if (mediaType === 'snapshot') {
    snapshotImage.src = src;
    imageContainer.classList.add('active');
    videoContainer.classList.remove('active');
  }

  showVideoBtn.classList.toggle('hidden-btn', !hasVideo);
  showImageBtn.classList.toggle('hidden-btn', !hasSnapshot);

  mediaModal.style.display = 'block';
}

document.querySelectorAll('.snapshot-btn').forEach(function(button) {
  button.addEventListener('click', function() {
    var eventBtns = this.parentElement;
    var snapshotSrc = eventBtns.getAttribute('data-snapshot');
    var videoSrc = eventBtns.getAttribute('data-video');
    var hasVideo = !!videoSrc;
    var hasSnapshot = !!snapshotSrc;

    showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot);

    document.getElementById('showVideo').onclick = function() {
      showMedia('video', videoSrc, hasVideo, hasSnapshot);
    };
    document.getElementById('showImage').onclick = function() {
      showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot);
    };
  });
});

document.querySelectorAll('.video-btn').forEach(function(button) {
  button.addEventListener('click', function() {
    var eventBtns = this.parentElement;
    var videoSrc = eventBtns.getAttribute('data-video');
    var snapshotSrc = eventBtns.getAttribute('data-snapshot');
    var hasVideo = !!videoSrc;
    var hasSnapshot = !!snapshotSrc;

    showMedia('video', videoSrc, hasVideo, hasSnapshot);

    document.getElementById('showVideo').onclick = function() {
      showMedia('video', videoSrc, hasVideo, hasSnapshot);
    };
    document.getElementById('showImage').onclick = function() {
      showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot);
    };
  });
});


document.querySelector('.close').addEventListener('click', function() {
  var mediaModal = document.getElementById('mediaModal');
  mediaModal.style.display = 'none';
});

window.addEventListener('click', function(event) {
  var mediaModal = document.getElementById('mediaModal');
  if (event.target == mediaModal) {
    mediaModal.style.display = 'none';
  }
});

document.getElementById('gotoHome').addEventListener('click', function () {
    jeedomUtils.loadPage("index.php?v=d&m=frigate&p=frigate");
});

function deleteEvent(eventId) {
  	$.ajax({
        type: "POST",
        url: "plugins/frigate/core/ajax/frigate.ajax.php",
        data: {
            action: "deleteEvent",
            eventId : eventId
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            } else {
                $('#div_alert').showAlert({
                    message: '{{Suppression de l\'évènement réussi.}}',
                    level: 'success'
                });
                window.location.reload(true);
            }
        }
    })
};

function filterEvents() {
	console.log('filterEvents');
    const selectedCameras = Array.from(document.querySelectorAll('.cameraFilter:checked')).map(function(checkbox) {
      return checkbox.value;
    });
    const selectedLabels = Array.from(document.querySelectorAll('.labelFilter:checked')).map(function(checkbox) {
      return checkbox.value;
    });
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const timeFilter = document.querySelector('input[name="timeFilter"]:checked').value;
    const now = new Date();
    let timeLimit;

    switch (timeFilter) {
      case '1h':
        timeLimit = new Date(now.getTime() - (1 * 60 * 60 * 1000));
        break;
      case '2h':
        timeLimit = new Date(now.getTime() - (2 * 60 * 60 * 1000));
        break;
      case '6h':
        timeLimit = new Date(now.getTime() - (6 * 60 * 60 * 1000));
        break;
      case '12h':
        timeLimit = new Date(now.getTime() - (12 * 60 * 60 * 1000));
        break;
      case '1d':
        timeLimit = new Date(now.getTime() - (24 * 60 * 60 * 1000));
        break;
      case '2d':
        timeLimit = new Date(now.getTime() - (2 * 24 * 60 * 60 * 1000));
        break;
      case '1w':
        timeLimit = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
        break;
      default:
        timeLimit = null;
        break;
    }
    const events = document.querySelectorAll('.frigateEventContainer');

    events.forEach(function(event) {
      const camera = event.getAttribute('data-camera');
      const label = event.getAttribute('data-label');
      const date = moment(event.getAttribute('data-date'), 'DD-MM-YYYY HH:mm:ss').toDate();
      
      const matchesCamera = selectedCameras.includes(camera);
      const matchesLabel = selectedLabels.includes(label);
      const matchesDate = (!startDate || date >= new Date(startDate)) && (!endDate || date <= new Date(endDate));
      const matchesTime = !timeLimit || date >= timeLimit;

      if (matchesCamera && matchesLabel && matchesDate && matchesTime) {
        event.classList.remove('eventHidden');
      } 
      else {
        event.classList.add('eventHidden');
      }
    });

    if (selectedCameras.length === 0 && selectedLabels.length === 0 && !startDate && !endDate && !timeFilter) {
      events.forEach(function(event) {
        event.classList.add('eventHidden');
      });
    }
}

document.querySelectorAll('.cameraFilter, .labelFilter').forEach(function (checkbox) {
  checkbox.addEventListener('change', filterEvents);
});

document.getElementById('selectAllCameras').addEventListener('click', function () {
  document.querySelectorAll('.cameraFilter').forEach(function (checkbox) {
    checkbox.checked = true;
  });
  filterEvents();
});

document.getElementById('deselectAllCameras').addEventListener('click', function () {
  document.querySelectorAll('.cameraFilter').forEach(function (checkbox) {
    checkbox.checked = false;
  });
  filterEvents();
});

document.getElementById('selectAllLabels').addEventListener('click', function () {
  document.querySelectorAll('.labelFilter').forEach(function (checkbox) {
    checkbox.checked = true;
  });
  filterEvents();
});

document.getElementById('deselectAllLabels').addEventListener('click', function () {
  document.querySelectorAll('.labelFilter').forEach(function (checkbox) {
    checkbox.checked = false;
  });
  filterEvents();
});

document.getElementById('startDate').addEventListener('change', filterEvents);
document.getElementById('endDate').addEventListener('change', filterEvents);
document.querySelectorAll('input[name="timeFilter"]').forEach(function(radio) {
  radio.addEventListener('change', filterEvents);
});