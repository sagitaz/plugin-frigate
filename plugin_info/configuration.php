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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Pièce par défaut pour les équipements}}</label>
            <div class="col-md-4">
                <select id="sel_object" class="configKey form-control" data-l1key="parentObject">
                    <option value="">{{Aucune}}</option>
                    <?php
                    foreach (jeeObject::all() as $object) {
                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <div class="form-group">
            <h5 class="col-sm-12"><b>{{Paramétrage Frigate}}</b></h5>
            <label class="col-md-4 control-label">{{URL Frigate}}
                <sup><i class="fas fa-question-circle tooltips" title="{{URL de Frigate}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="URL" placeholder="{{Exemple: 192.168.1.20}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Port}}
                <sup><i class="fas fa-question-circle tooltips" title="{{port de Frigate (5000 par défaut)}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="port" type="number" placeholder="{{5000 par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Adresse externe}}
                <sup><i class="fas fa-question-circle tooltips inputPassword" title=" {{ne sert que pour le bouton vers votre serveur Frigate}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="URLexterne" type="text" placeholder="{{Adresse externe de votre serveur}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Topic MQTT}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Topic MQTT de Frigate}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="topic" placeholder="frigate" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Preset}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de preset à importer}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input type="number" class="configKey form-control" data-l1key="presetMax" placeholder="5" />
            </div>
        </div>
    </fieldset>
    <fieldset>
        <div class="form-group">
            <h5 class="col-sm-12"><b>{{Gestion des évènements}}</b></h5>
            <label class="col-md-4 control-label">{{Récupération des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à récupérer}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="recovery_days" type="number" placeholder="{{7 jours par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Suppression des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à garder}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="remove_days" type="number" placeholder="{{7 jours par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Taille des dossiers}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Taille maximum des données (en Mo)}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="datas_weight" type="number" placeholder="{{500 Mo par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Durée de rafraîchissement}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Rafraichissement des captures des caméras (en secondes)}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="refresh_snapshot" type="number" placeholder="{{5 secondes par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Vidéos en vignette dans la page des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Affichage de la vidéo en miniature d'un évènement au passage de la souris sur sa capture}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input type="checkbox" class="configKey form-control" data-l1key="event::displayVideo" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Confirmation avant suppression d'un évènement}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Affichage d'une alerte de confirmation lors de la demande de suppression d'un évènement}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input type="checkbox" class="configKey form-control" data-l1key="event::confirmDelete" />
            </div>
        </div>
    </fieldset>
    <fieldset>
        <div class="form-group">
            <h5 class="col-sm-12"><b>{{Paramétrage par défaut d'un évènement créé manuellement}}</b></h5>
            <label class="col-md-4 control-label">{{Label}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Label par défaut d'un évènement créé manuellement}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="defaultLabel" placeholder="{{'manuel' par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Enregistrer une vidéo}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Enregistrement d une vidéo par défaut pour un évènement manuel}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input type="checkbox" class="configKey form-control" data-l1key="defaultVideo" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Durée de la vidéo}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Durée par défaut de la vidéo d un évènement manuel}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="defaultDuration" type="number" placeholder="{{40 secondes par défaut}}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Score}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Score par défaut d un évènement manuel, entre 0 et 100%}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="defaultScore" type="number" placeholder="{{0 par défaut, de 0 à 100%}}" />
            </div>
        </div>
    </fieldset>
</form>