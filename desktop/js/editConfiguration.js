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

var app_config = {
  init: function () {
      const $frigateConfiguration = $("#frigateConfiguration");
      const $sendConfiguration = $("#sendConfiguration");
      const $sendConfigurationAndRestart = $("#sendConfigurationAndRestart");
      const $divYamlAlert = $("#div_yamlAlert");
      const $divConfigFrigateAlert = $('#div_configFrigateAlert');

      this.showAlert = function(message, level) {
          $('#div_configFrigateAlert').showAlert({
              message: message,
              level: level
          });
      }

      // Gestion des alertes de validité du champ de configuration
      function updateAlert(isValid, message) {
          if (isValid) {
              $sendConfiguration.removeClass('disabled');
              $sendConfigurationAndRestart.removeClass('disabled');
              $divYamlAlert.removeClass('alert-danger alert-warning').addClass('alert-success');
          } else {
              $sendConfiguration.addClass('disabled');
              $sendConfigurationAndRestart.addClass('disabled');
              $divYamlAlert.removeClass('alert-warning alert-success').addClass('alert-danger');
          }
          $divYamlAlert.html(message);
      }

      // Gestion des erreurs Ajax
      function handleAjaxError(request, status, error) {
          console.error(`Error: ${status} - ${error}`);
          app_config.showAlert(`Une erreur est survenue : ${status} - ${error}.`, 'danger');
      }

      // Gestion des appels Ajax
      this.ajaxRequest = function(action, data, successCallback) {
          $.ajax({
              type: 'POST',
              url: 'plugins/frigate/core/ajax/frigate.ajax.php',
              data: {
                  action: action,
                  ...data
              },
              dataType: 'json',
              global: false,
              error: handleAjaxError,
              success: function (data) {
                  if (data.result.status === 'success') {
                      successCallback(data);
                  } else {
                      app_config.showAlert(data.result.message, 'danger');
                  }
              }
          });
      };

      // Gestion des changements de contenu du champ de configuration
      $frigateConfiguration.on("change keyup paste load", function() {
          const yamlInput = $frigateConfiguration.val();
          try {
              jsyaml.load(yamlInput);
              updateAlert(true, '{{Fichier de configuration valide.}}');
          } catch (e) {
              updateAlert(false, `{{Fichier de configuration invalide}} : ${e.message}`);
          }
      });

      // Gestion des boutons
      $("#synchroConfiguration").click(() => {
          this.show();
      });

      $("#downloadConfiguration").click(() => {
          this.ajaxRequest('getFrigateConfiguration', { type: 'GET' }, (data) => {
              const now = new Date();
              const fileName = `configurationFrigate_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}_${String(now.getHours()).padStart(2, '0')}h${String(now.getMinutes()).padStart(2, '0')}.yaml`;
              const blob = new Blob([data.result.message], { type: "application/x-yaml" });
              saveAs(blob, fileName);
          });
      });

      $sendConfiguration.click(() => {
          this.ajaxRequest('sendFrigateConfiguration', { data: $frigateConfiguration.val() }, () => {
              app_config.showAlert('{{Envoi de la configuration au serveur Frigate réussi.}}', 'success');
          });
      });

      $sendConfigurationAndRestart.click(() => {
          this.ajaxRequest('sendFrigateConfigurationAndRestart', { data: $frigateConfiguration.val() }, () => {
              app_config.showAlert('{{Envoi de la configuration au serveur Frigate avec redémarrage réussi.}}', 'success');
          });
      });
  },
  show: function () {
      this.ajaxRequest('getFrigateConfiguration', { type: 'GET' }, (data) => {
          app_config.showAlert('{{Configuration du serveur Frigate récupérée.}}', 'success');
          $("#frigateConfiguration").val(data.result.message).change();
      });
  }
};


$('body').off('frigate::config').on('frigate::config', function(_event, _options) {
    app_config.show();
})