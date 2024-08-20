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

function createNewEvent() {
  	const value = $('.eventAttr[data-l1key=label]').val();
    if (value === undefined || value === null || value.trim() === '') {
      jeeDialog.alert({
        title: 'Attention',
        message: 'Vous devez saisir un label'
      })
      return;
    }

    $.ajax({
        type: "POST",
        url: "plugins/frigate/core/ajax/frigate.ajax.php",
        data: {
            action: "createEvent",
            camera: $('.eventAttr[data-l1key=camera]').value(),
            label: $('.eventAttr[data-l1key=label]').value(),
            score: Math.min(100, $('.eventAttr[data-l1key=score]').value()),
            video: $('.eventAttr[data-l1key=video]').value(),
            duration: $('.eventAttr[data-l1key=duration]').value()            
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({
                message: '{{Création d\'évènement réussie}}',
                level: 'success'
            });

            window.location.reload(true);
        },
    });
}

function updateDurationDisplay() {
  if ($('#videoCheckbox').is(':checked')) {
    durationContainer.style.display = 'block';
  } else {
    durationContainer.style.display = 'none';
  }
}
  
$('#videoCheckbox').on('change', updateDurationDisplay);
updateDurationDisplay();

$('#md_modal').dialog('option', 'width', 600); 
$('#md_modal').dialog('option', 'height', 400);

$(document).ready(function() {
  $('.eventAttr[data-l1key=camera]').select2();
});
