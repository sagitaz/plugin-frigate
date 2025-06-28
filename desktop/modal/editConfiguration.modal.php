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
  .bound-config {
    width: 100%;
    margin: 0px;
    padding: 0px;
  }
  textarea {
    width: 100%;
    margin: 0px;
    padding: 10px;
    height: 800px;
    font-size: 14px;
  }
  .alert-info .info-item:not(:last-of-type) {
    margin-bottom: 15px;
  }
  .alert-info .info-item span {
      padding: 5px;
  }
</style>
    <div id='div_configFrigateAlert' style="display: none;"></div>
    <div class="alert alert-danger">
        {{La modification de la configuration du serveur Frigate est à vos risques et périls ! Aucun support ne sera donné !}}
    </div>
    <div class="alert alert-info">
        <div class="info-item">
            <span class="alert-success">{{Récupérer la configuration}}</span> : {{Recharge le fichier de configuration depuis le serveur Frigate}}
        </div>
        <div class="info-item">
            <span class="alert-success">{{Télécharger la configuration}}</span> : {{Télécharger le fichier de configuration depuis le serveur Frigate}}
        </div>
        <div class="info-item">
            <span class="alert-warning">{{Envoyer la configuration}}</span> : {{Envoie et remplace le fichier de configuration du serveur Frigate}}
        </div>
        <div class="info-item">
            <span class="alert-warning">{{Envoyer la configuration et redémarrer Frigate}}</span> : {{Envoie et remplace le fichier de configuration Frigate puis redémarre Frigate}}
        </div>
    </div>
<div>
<button id="synchroConfiguration" class="btn btn-success pull-left" title="{{Recharge le fichier de configuration depuis le serveur Frigate}}"><i class="fas fa-cloud-download-alt"></i> {{Récupérer la configuration}}</button>
<button id="downloadConfiguration" class="btn btn-success pull-left" title="{{Télécharger le fichier de configuration depuis le serveur Frigate}}"><i class="fas fa-save"></i> {{Télécharger la configuration}}</button>
<button id="sendConfigurationAndRestart" class="btn btn-warning pull-right" style="margin-right: 0 !important;" title="{{Envoie et remplace le fichier de configuration du serveur Frigate puis redémarre Frigate}}"><i class="fas fa-redo"></i> {{Envoyer la configuration et redémarrer Frigate}}</button>
<button id="sendConfiguration" class="btn btn-warning pull-right" title="{{Envoie et remplace le fichier de configuration Frigate}}"><i class="fas fa-cloud-upload-alt"></i> {{Envoyer la configuration}}</button>
<br/>
<br/>
<div id="div_yamlAlert" class="alert" style="text-align: center"></div>
</div>
<div class="bound-config">
    <textarea id="frigateConfiguration" class="boxsizingborder" spellcheck="false"></textarea>
</div>

<?php include_file('desktop', 'editConfiguration', 'js', 'frigate');?>

<script>
    console.log('script init');
	if (window["app_config"] != undefined) {
        window["app_config"].init();
        window["app_config"].show();
    }
</script>

<!-- js-yaml 4.1.0 https://github.com/nodeca/js-yaml -->
<?php include_file('desktop', 'yaml', 'js', 'frigate'); ?>