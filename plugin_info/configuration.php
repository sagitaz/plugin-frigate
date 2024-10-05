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
<div class="col-md-6 frigate-info">
    <div class="frigate-heading">
        <h3 class="frigate-title"> {{Génèrale}}
        </h3>
    </div>
    <div class="frigate-body">
        <fieldset>
            <div class="form-group">
                <div class="form-group">
                    <label class="col-lg-7 control-label">{{Version Plugin}}
                        <sup><i class="fas fa-question-circle tooltips" title="{{Version du Plugin (A indiquer sur Community)}}"></i></sup>
                    </label>
                    <div class="col-xs-5">
                        <input class="configKey form-control input-xs" data-l1key="pluginVersion" readonly />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-7 control-label">{{Exclure du backup}}
                        <sup><i class="fas fa-question-circle tooltips" title="{{Exclure les clips et snapshot des sauvegardes Jeedom}}"></i></sup>
                    </label>
                    <div class="col-xs-5">
                        <input type="checkbox" class="configKey form-control" data-l1key="excludeBackup" />
                    </div>
                </div>
                <label class="col-lg-7 control-label">{{Pièce par défaut pour les équipements}}</label>
                <div class="col-xs-5">
                    <select id="sel_object" class="configKey form-control  input-xs" data-l1key="parentObject">
                        <option value="">{{Aucun}}</option>
                        <?php
                        $options = '';
                        foreach ((jeeObject::buildTree(null, false)) as $object) {
                            $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                        }
                        echo $options;
                        ?>
                    </select>
                </div>
            </div>
            <br>
            <br>
            <br>
        </fieldset>
    </div>
</div>


<div class="col-md-6 frigate-info">
    <div class="frigate-heading">
        <h3 class="frigate-title"> {{Paramétrage Frigate}}
        </h3>
    </div>
    <div class="frigate-body">
        <fieldset>
            <div class="form-group panel-primary">
                <label class="col-lg-7 control-label">{{URL Frigate}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{URL de Frigate}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="URL" placeholder="{{Exemple: 192.168.1.20}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Port}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{port de Frigate (5000 par défaut)}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="port" type="number" placeholder="{{5000 par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Adresse externe}}
                    <sup><i class="fas fa-question-circle tooltips inputPassword" title=" {{ne sert que pour le bouton vers votre serveur Frigate}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="URLexterne" type="text" placeholder="{{Adresse externe de votre serveur}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Topic MQTT}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Topic MQTT de Frigate}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="topic" placeholder="frigate" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Preset}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de preset à importer}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input type="number" class="configKey form-control input-xs" data-l1key="presetMax" placeholder="5" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Pause action}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Pause à effectuer sur l'action PTZ (0 à 10)}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input type="number" class="configKey form-control input-xs" data-l1key="pausePTZ" placeholder="10" />
                </div>
            </div>
        </fieldset>
    </div>
</div>


<div class="col-md-6 frigate-info">
    <div class="frigate-heading">
        <h3 class="frigate-title"> {{Gestion des évènements}}
        </h3>
    </div>
    <div class="frigate-body">
        <fieldset>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Récupération des évènements}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à récupérer}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="recovery_days" type="number" placeholder="{{7 jours par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Suppression des évènements}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à garder}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="remove_days" type="number" placeholder="{{7 jours par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Taille des dossiers}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Taille maximum des données (en Mo)}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="datas_weight" type="number" placeholder="{{500 Mo par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Durée de rafraîchissement}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Rafraichissement des captures des caméras (en secondes)}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="refresh_snapshot" type="number" placeholder="{{5 secondes par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Vidéos en vignette dans la page des évènements}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Affichage de la vidéo en miniature d'un évènement au passage de la souris sur sa capture}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input type="checkbox" class="configKey form-control" data-l1key="event::displayVideo" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Confirmation avant suppression d'un évènement}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Affichage d'une alerte de confirmation lors de la demande de suppression d'un évènement}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input type="checkbox" class="configKey form-control" data-l1key="event::confirmDelete" />
                </div>
            </div>
        </fieldset>
    </div>
</div>


<div class="col-md-6 frigate-info">
    <div class="frigate-heading">
        <h3 class="frigate-title"> {{Paramétrage par défaut d'un évènement créé manuellement}}
        </h3>
    </div>
    <div class="frigate-body">
        <fieldset>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Label}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Label par défaut d'un évènement créé manuellement}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="defaultLabel" placeholder="{{'manuel' par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Enregistrer une vidéo}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Enregistrement d une vidéo par défaut pour un évènement manuel}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input type="checkbox" class="configKey form-control" data-l1key="defaultVideo" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Durée de la vidéo}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Durée par défaut de la vidéo d un évènement manuel}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="defaultDuration" type="number" placeholder="{{40 secondes par défaut}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-7 control-label">{{Score}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Score par défaut d un évènement manuel, entre 0 et 100%}}"></i></sup>
                </label>
                <div class="col-xs-5">
                    <input class="configKey form-control input-xs" data-l1key="defaultScore" type="number" placeholder="{{0 par défaut, de 0 à 100%}}" />
                </div>
            </div>
        </fieldset>
    </div>
</div>

<style>
    .frigate-info {
        border-color: #ddd;
    }

    .frigate-heading {
        padding: 10px 15px;
        border-bottom: 1px solid transparent;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        color: rgb(var(--defaultBkg-color)) !important;
        background-color: grey !important;
    }

    .frigate-body {
        padding: 15px;
    }

    .frigate-title {
        margin-top: 0;
        margin-bottom: 0;
        font-size: 16px;
        color: inherit;
    }
</style>