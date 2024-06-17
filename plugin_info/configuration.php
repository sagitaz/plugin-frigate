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
        <div class="form-group">
            <label class="col-md-4 control-label">{{URL Frigate}}
                <sup><i class="fas fa-question-circle tooltips" title="{{URL de Frigate}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="URL" placeholder="Exemple: 192.168.1.20" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Port}}
                <sup><i class="fas fa-question-circle tooltips" title="{{port de Frigate (5000 par défaut)}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="port" type="number" placeholder="5000 par défaut" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Récupération des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à récupérer}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="recovery_days" type="number" placeholder="7 jours par défaut" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Suppression des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à garder}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="remove_days" type="number" placeholder="7 jours par défaut" />
            </div>
        </div>
    </fieldset>
</form>
