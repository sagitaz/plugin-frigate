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

function openClip(url) {
    window.open(url.id);
}

function openSnapshot(url) {
    window.open(url.id);
}

document.getElementById('gotoHome').addEventListener('click', function () {
    jeedomUtils.loadPage("index.php?v=d&m=frigate&p=frigate");
});

function deleteEvent(url) {
    $.ajax({
        type: "POST",
        url: "plugins/frigate/core/ajax/frigate.ajax.php",
        data: {
            action: "deleteEvent",
            eventId : url.id
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
    var selectedCameras = Array.from(document.querySelectorAll('.cameraFilter:checked')).map(function(checkbox) {
      return checkbox.value;
    });
    var selectedLabels = Array.from(document.querySelectorAll('.labelFilter:checked')).map(function(checkbox) {
      return checkbox.value;
    });
    var events = document.querySelectorAll('.frigateEventContainer');

    events.forEach(function(event) {
      var camera = event.getAttribute('data-camera');
      var label = event.getAttribute('data-label');

      var matchesCamera = selectedCameras.includes(camera);
      var matchesLabel = selectedLabels.includes(label);

      if (matchesCamera && matchesLabel) {
        event.classList.remove('eventHidden');
      } else {
        event.classList.add('eventHidden');
      }
    });

    if (selectedCameras.length === 0 && selectedLabels.length === 0) {
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
