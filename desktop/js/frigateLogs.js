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
        this.textLogs = '';
      	const $divLogsAlert = $("#div_logsAlert");
        const $divFrigateLogsAlert = $('#div_frigateLogsAlert');
        const $divFrigateAlert = $("#div_logsAlert");

        this.showAlert = function(message, level) {
            $('#div_configFrigateAlert').showAlert({
                message: message,
                level: level
            });
        }

        // Gestion du titre des logs
        this.updateTitle = function(isValid, message) {
            if (isValid) {
                $divFrigateAlert.removeClass('alert-danger alert-warning').addClass('alert-info');
            } else {
                $divFrigateAlert.removeClass('alert-warning alert-info').addClass('alert-danger');
            }
            $divFrigateAlert.html(message);
        }

        // Gestion de la coloration des logs
      	this.processLogs = function(logs) {
            logs = logs.split('\n');
            
            let result = '';
            logs.forEach(log => {
                let className;
                if (log.includes('ERROR')) {
                    className = 'danger';
                } else if (log.includes('INFO')) {
                    className = 'info';
                } else if (log.includes('WARNING')) {
                    className = 'warning';
                }
                
                result += `<p class="${className}">${log}</p>\n`;
            });

            return result;
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
                  if (data.state === 'ok') {
                    try {
                      const parsedResult = JSON.parse(data.result);
                      if (parsedResult.success !== undefined) {
                        if (parsedResult.success) {
                          successCallback(parsedResult.result);
                        } else {
                          app_config.showAlert(parsedResult.message, 'danger');
                        }
                      } else {
                        successCallback(data.result);
                      }
                    }
                    catch (error) {
                      successCallback(data.result);
                    }
                  } else {
                    app_config.showAlert(data.result, 'danger');
                  }
                }
            });
        };

        // Gestion des boutons
        $("#frigateLogsBtn").click(() => {
          	this.show();
        });

        $("#go2rtcLogsBtn").click(() => {
            this.ajaxRequest('logs', { type: 'GET', service: 'go2rtc' }, (logs) => {
                app_config.textLogs = logs;
              	app_config.showAlert('{{Logs go2rtc récupérés.}}', 'success');
            	app_config.updateTitle(true, '{{Logs go2rtc}}');
              	$("#logs").html(app_config.processLogs(logs));
            });
        });

        $("#nginxLogsBtn").click(() => {          
          	this.ajaxRequest('logs', { type: 'GET', service: 'nginx' }, (logs) => {
                app_config.textLogs = logs;
              	app_config.showAlert('{{Logs nginx récupérés.}}', 'success');
            	app_config.updateTitle(true, '{{Logs nginx}}');
              	$("#logs").html(app_config.processLogs(logs));
            });
        });
      
        $("#downloadConfiguration").click(() => {
            this.ajaxRequest('logs', { type: 'GET' }, () => {
              	const now = new Date();
              	const title = $divFrigateAlert.html().replace(/\s+/g, '');
                const fileName = `${title}_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}_${String(now.getHours()).padStart(2, '0')}h${String(now.getMinutes()).padStart(2, '0')}.log`;
                const blob = new Blob([app_config.textLogs], { type: "text/plain;charset=utf-8" });
                saveAs(blob, fileName);
            });
        });      
    },
    show: function () {
      	this.ajaxRequest('logs', { type: 'GET', service: 'frigate' }, (logs) => {
            app_config.textLogs = logs;
            app_config.showAlert('{{Logs Frigate récupérés.}}', 'success');
          	app_config.updateTitle(true, '{{Logs Frigate}}');
          	$("#logs").html(app_config.processLogs(logs));
        });
    }
};
