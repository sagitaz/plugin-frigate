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
            <label class="col-md-4 control-label">{{URL Frigate}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Renseignez l'URL de frigate}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="URL" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Port}}
                <sup><i class="fas fa-question-circle tooltips" title="{{port (5000 par default)}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="port" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Récupération des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à récupèrer}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="recovery_days" placeholder="7 jours par default" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Suppréssion des évènements}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours à garder}}"></i></sup>
            </label>
            <div class="col-md-4">
                <input class="configKey form-control" data-l1key="remove_days" placeholder="7 jours par default" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{TTS Pitch}}
            </label>
            <div class="col-md-4">
                <select class="configKey" data-l1key="cron">
                    <option value="1">{{1 minute}}</option>
                    <option value="5" selected>{{5 minutes}}</option>
                    <option value="15">{{15 minutes}}</option>
                    <option value="30">{{30 minutes}}</option>
                    <option value="60">{{60 minutes}}</option>
                </select>
            </div>
        </div>
    </fieldset>
</form>