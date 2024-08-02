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
    throw new Exception('{{401 - Accès non autorisé}}');
}

?>

<style>
    .disabledEq {
        opacity: 0.3 !important;
    }

    .hidden {
        visibility: hidden;
    }

    input#autre:checked~.hidden {
        visibility: visible;
    }

    .fa-times::before {
        content: "\f00d";
    }
</style>
<legend><i class="fas fa-building"></i>{{Configuration du nouvel évènement}}</legend>
<br>

<div id="event_editor">
    <br>
    <div>
        <label class="col-sm-2 control-label">{{Caméra}} :</label>
      
        <?php    
          $plugin = plugin::byId('frigate');
          $eqLogics = eqLogic::byType($plugin->getId());
          if (count($eqLogics) != 0) {
            echo '<label class="col-sm-8 control-label">{{Aucune caméra Frigate trouvée}}</label>';
          } else {
            // Liste des caméras
      		echo '<select class="eventAttr col-sm-8" data-l1key="camera">';
            echo '<div class="eqLogicThumbnailContainer">';
            foreach ($eqLogics as $eqLogic) {
              $parts = explode('][', trim($eqLogic->getHumanName(false, false), '[]'));
              $camera = $parts[1];
              if ($camera !== 'Events' && $camera !== 'Statistiques') {
                echo '<option value="' . $camera . '">' . $camera . '</option>';
              }
            }
            echo '</div>';
            echo '</select>';
          }
        ?>
    </div>
    <br><br>
    </div>
        <label class="col-sm-2 control-label">{{Label}} :</label>
    	<input class="eventAttr col-sm-8 form-control input-sm" data-l1key="label" placeholder="{{Saisissez un label d'évènement (ex: person)}}">
    </div>
    <br><br>
</div>
<div class="form-actions pull-right"><a class="btn btn-success eqLogicAction" onclick="createNewEvent()">
        <i class="fas fa-check-circle"></i> {{Sauvegarder}}</a></div>
</div>

<?php include_file('desktop', 'modal_event', 'js', 'frigate'); ?>