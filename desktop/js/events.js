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
            }
        }
    })
};