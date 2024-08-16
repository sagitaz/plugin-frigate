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

function showMedia(mediaType, src, hasVideo, hasSnapshot, title) {
  const mediaModal = document.getElementById('mediaModal');
  const videoContainer = document.querySelector('.video-container');
  const imageContainer = document.querySelector('.image-container');
  const videoPlayer = document.getElementById('videoPlayer');
  const videoSource = document.getElementById('videoSource');
  const snapshotImage = document.getElementById('snapshotImage');
  const showVideoBtn = document.getElementById('showVideo');
  const showImageBtn = document.getElementById('showImage');
  const mediaTitle = document.getElementById('mediaTitle');

  mediaTitle.innerHTML = title;


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

document.querySelectorAll('.snapshot-btn').forEach(function (button) {
  button.addEventListener('click', function () {
    const eventBtns = this.parentElement;
    const snapshotSrc = eventBtns.getAttribute('data-snapshot');
    const videoSrc = eventBtns.getAttribute('data-video');
    const title = eventBtns.getAttribute('data-title');
    const hasVideo = !!videoSrc;
    const hasSnapshot = !!snapshotSrc;

    showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot, title);

    document.getElementById('showVideo').onclick = function () {
      showMedia('video', videoSrc, hasVideo, hasSnapshot, title);
    };
    document.getElementById('showImage').onclick = function () {
      showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot, title);
    };
  });
});

document.querySelectorAll('.video-btn').forEach(function (button) {
  button.addEventListener('click', function () {
    const eventBtns = this.parentElement;
    const videoSrc = eventBtns.getAttribute('data-video');
    const snapshotSrc = eventBtns.getAttribute('data-snapshot');
    const title = eventBtns.getAttribute('data-title');
    const hasVideo = !!videoSrc;
    const hasSnapshot = !!snapshotSrc;

    showMedia('video', videoSrc, hasVideo, hasSnapshot, title);

    document.getElementById('showVideo').onclick = function () {
      showMedia('video', videoSrc, hasVideo, hasSnapshot, title);
    };
    document.getElementById('showImage').onclick = function () {
      showMedia('snapshot', snapshotSrc, hasVideo, hasSnapshot, title);
    };
  });
});


document.querySelector('.close').addEventListener('click', function () {
  var mediaModal = document.getElementById('mediaModal');
  mediaModal.style.display = 'none';
});

window.addEventListener('click', function (event) {
  var mediaModal = document.getElementById('mediaModal');
  if (event.target == mediaModal) {
    mediaModal.style.display = 'none';
  }
});

document.getElementById('gotoHome').addEventListener('click', function () {
  jeedomUtils.loadPage("index.php?v=d&m=frigate&p=frigate");
});


document.getElementById('deleteAll').addEventListener('click', function () {
  const visibleEvents = getVisibleEvents();
  jeeDialog.confirm('{{Êtes-vous sûr de vouloir supprimer ces évènements ?<br/>Cela les supprimera aussi de votre serveur Frigate ! Continuer ?}}', function (result) {
    if (result) {
      visibleEvents.forEach(function (event) {
        const eventId = event.getAttribute('data-id');
        console.log("suppression de : " + eventId);
        deleteAllEvents(eventId);
      });
    }
  });
});

document.getElementById('createEvent').addEventListener('click', function () {
  $('#md_modal').dialog({title: "{{Configuration d'un nouvel évènement}}", width: 600, height: 400})
    .load('index.php?v=d&plugin=frigate&modal=event.modal').dialog('open');
});

function deleteEvent(eventId) {
  jeeDialog.confirm('{{Êtes-vous sûr de vouloir supprimer cet évènement ?<br/>Cela le supprimera aussi de votre serveur Frigate ! Continuer ?}}', function (result) {
    if (result) {
      console.log("suppression de : " + eventId);
      deleteAllEvents(eventId);
    }
  });
}

function deleteAllEvents(eventId) {
  $.ajax({
    type: "POST",
    url: "plugins/frigate/core/ajax/frigate.ajax.php",
    data: {
      action: "deleteEvent",
      eventId: eventId
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
        return;
      } else if (data.result == 'Error 01') {
        $('#div_alert').showAlert({
          message: '{{L\'évènement est un favori. Suppression impossible. Veuillez d\'abord le supprimer de la liste des favoris.}}',
          level: 'warning'
        });
        return;
      } else if (data.result == 'Error 02') {
        $('#div_alert').showAlert({
          message: '{{L\'URL du serveur Frigate n\'est pas configurée.}}',
          level: 'warning'
        });
        return;
      } else if (data.result == 'Error 03') {
        $('#div_alert').showAlert({
          message: '{{Le port du serveur Frigate n\'est pas configuré.}}',
          level: 'warning'
        });
        return;
      } else if (data.result == 'OK') {
        $('#div_alert').showAlert({
          message: '{{Suppression de l\'évènement réussie.}}',
          level: 'success'
        });
        window.location.reload(true);
      }
    }
  })
}

function filterEvents() {
  console.log('filterEvents');
  const selectedCameras = Array.from(document.querySelectorAll('.cameraFilter:checked')).map(function (checkbox) {
    return checkbox.value;
  });
  const selectedLabels = Array.from(document.querySelectorAll('.labelFilter:checked')).map(function (checkbox) {
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
    case '1j':
      timeLimit = new Date(now.getTime() - (24 * 60 * 60 * 1000));
      break;
    case '2j':
      timeLimit = new Date(now.getTime() - (2 * 24 * 60 * 60 * 1000));
      break;
    case '1s':
      timeLimit = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
      break;
    default:
      timeLimit = null;
      break;
  }
  const events = document.querySelectorAll('.frigateEventContainer');

  events.forEach(function (event) {
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
    events.forEach(function (event) {
      event.classList.add('eventHidden');
    });
  }

}

function getVisibleEvents() {
  return Array.from(document.querySelectorAll('.frigateEventContainer:not(.eventHidden)'));
}

function gotoCamera(cameraId) {
  jeedomUtils.loadPage("index.php?v=d&m=frigate&p=frigate&id=" + cameraId);
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
document.querySelectorAll('input[name="timeFilter"]').forEach(function (radio) {
  radio.addEventListener('change', filterEvents);
});

function toggleFavorite(button) {
  const eventId = button.getAttribute('data-id');
  var icon = button.querySelector('i');
  if (icon.classList.contains('fas')) {
    icon.classList.remove('fas');
    icon.classList.add('far');
    setFavorite(eventId, 0);
  } else {
    icon.classList.remove('far');
    icon.classList.add('fas');
    setFavorite(eventId, 1);
  }
}

function setFavorite(eventId, isFav) {
  $.ajax({
    type: "POST",
    url: "plugins/frigate/core/ajax/frigate.ajax.php",
    data: {
      action: "setFavorite",
      eventId: eventId,
      isFav: isFav
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.result == '0') {
        $('#div_alert').showAlert({
          message: '{{L\'évènement a été retiré de la liste des favoris.}}',
          level: 'success'
        });
        return;
      } else if (data.result == '1') {
        $('#div_alert').showAlert({
          message: '{{L\'évènement a été ajouté à la liste des favoris.}}',
          level: 'success'
        });
        return;
      }
    }
  })
}

$('body').off('frigate::events').on('frigate::events', function(_event, _options) {
  window.location.reload()
})

function loadAndPlayVideo(video) {
  const src = video.getAttribute('data-src');
  if (src) {
    video.setAttribute('src', src);
    video.load();
    video.play();
  }
}

function resetVideo(video) {
  video.pause();
  video.currentTime = 0;
  video.src = '';
}

function handleHover(event) {
  const video = event.querySelector('video[data-src]');
  if (video) {
    resetVideo(video);
    loadAndPlayVideo(video);
  }
}
  
filterEvents();
