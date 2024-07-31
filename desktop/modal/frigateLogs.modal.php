<?php
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
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
?>

<style>
  .container {
    display: flex;
  	flex-direction: column;
	height: 100%;
  }
  .bound-config {
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 100%;
    margin: 0px;
    padding: 0px;
    height: 100px;
  }
  #logs {
    flex: 1;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
</style>

<div class="container">
  <div id='div_configFrigateAlert' style="display: none;"></div>
  <div class="header">
    <button id="frigateLogsBtn" class="btn btn-success pull-center" title="{{Afficher les logs Frigate}}"><i class="far fa-file-alt"></i> {{Logs Frigate}}</button>
    <button id="go2rtcLogsBtn" class="btn btn-success pull-center" title="{{Afficher les logs go2rtc}}"><i class="far fa-file-alt"></i> {{Logs go2rtc}}</button>
    <button id="nginxLogsBtn" class="btn btn-success pull-center" title="{{Afficher les logs nginx}}"><i class="far fa-file-alt"></i> {{Logs nginx}}</button>
    <button id="downloadConfiguration" class="btn btn-success pull-right" title="Télécharger les logs affichés"><i class="fas fa-save"></i> {{Télécharger les logs}}</button>
    <br/><br/>
    <div id='div_logsAlert' class="alert"></div>
  </div>
  <div class="bound-config">
    <div id="logs"></div>
  </div>
</div>

<?php include_file('desktop', 'frigateLogs', 'js', 'frigate');?>

<script>
	if (window["app_config"] != undefined) {
        window["app_config"].init();
        window["app_config"].show();
  }
</script>