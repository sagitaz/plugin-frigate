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
		console.log('ENTER init');
      	
        $("#synchroConfiguration").click(function () {
          console.log('synchroConfiguration');
          app_config.show();
        })

        $("#sendConfiguration").click(function () {
            console.log('sendConfiguration');
			$.ajax({
				type: 'POST',
				url: 'plugins/frigate/core/ajax/frigate.ajax.php',
				data: {
					action: 'sendFrigateConfiguration',
					data: $("#frigateConfiguration").val()
				},
				dataType: 'json',
				global: false,
				error: function (request, status, error) {
					handleAjaxError(request, status, error, $('#div_confighomebridgeAlert'));
				},
				success: function (data) {
					if (data.result != false) {
                        console.log('envoi réussi !');
                        $('#div_configFrigateAlert').showAlert({
                          message: '{{Envoi de la configuration au serveur Frigate réussi.}}',
                          level: 'success'
                        });                      
                    } else {
                        console.log('Envoi de la configuration Frigate échoué ! (' + data.result.message + ')');
                        $('#div_configFrigateAlert').showAlert({
                          message: data.result.message,
                          level: 'danger'
                        });                      
                    }
				}
			});
        })
        
      	$("#sendConfigurationAndRestart").click(function () {
            console.log('frigateConfigurationAndRestart');
			$.ajax({
				type: 'POST',
				url: 'plugins/frigate/core/ajax/frigate.ajax.php',
				data: {
					action: 'sendFrigateConfigurationAndRestart',
					data: $("#frigateConfiguration").val()
				},
				dataType: 'json',
				global: false,
				error: function (request, status, error) {
					handleAjaxError(request, status, error, $('#div_confighomebridgeAlert'));
				},
				success: function (data) {
					if (data.result != false) {
                        console.log('envoi de la configuration Frigate avec redémarrage réussi !');
                        $('#div_configFrigateAlert').showAlert({
                          message: '{{Envoi de la configuration au serveur Frigate avec redémarrage réussi.}}',
                          level: 'success'
                        });                      
                    } else {
                        console.log('Envoi de la configuration Frigate échoué ! (' + data.result.message + ')');
                        $('#div_configFrigateAlert').showAlert({
                          message: data.result.message,
                          level: 'danger'
                        });                      
                    }
				}
			});
        })                                      
    },
    show: function () {
		console.log('ENTER show');
		$.ajax({
			type: 'POST',
			url: 'plugins/frigate/core/ajax/frigate.ajax.php',
			data: {
				action: 'getFrigateConfiguration',
				type: 'GET'
			},
			dataType: 'json',
			global: false,
			error: function (request, status, error) {
              handleAjaxError(request, status, error);
			},
			success: function (data) {
				if (data.result.status == 'success') {
                  $('#div_configFrigateAlert').showAlert({
                    message: '{{Configuration du serveur Frigate récupérée.}}',
                    level: 'success'
                  });
                  $("#frigateConfiguration").val(data.result.message);
                }
                else {
                  $('#div_configFrigateAlert').showAlert({
                    message: data.result.message,
                    level: 'danger'
                  });
                }
			}
		});
    }
};