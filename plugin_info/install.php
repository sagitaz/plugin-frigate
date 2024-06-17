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

use Log;

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function frigate_install()
{
    Log::add("frigate", 'info', 'Start Install');
    $sql = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL);
    frigate::generateEqEvents();
    frigate::generateEqStats();
    frigate::setConfig();
    frigate::setConfigCron();
    frigate::setCmdsCron();
    Log::add("frigate", 'info', 'Finish Install');
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function frigate_update()
{
    Log::add("frigate", 'info', 'Start Update');
    $sql = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL);
    Log::add("update", 'debug', '==> Update DB');
    try {
        Log::add("update", 'debug', '===> Modify le champ topScore et créé le champ score dans frigate_events');
        // Modification de la colonne topScore
        $sql1 = "ALTER TABLE `jeedom`.`frigate_events` MODIFY `topScore` int(11) NULL;";
        DB::Prepare($sql1, array(), DB::FETCH_TYPE_ROW);

        // Création de la nouvelle colonne score
        $sql2 = "ALTER TABLE `jeedom`.`frigate_events` ADD COLUMN `score` int(11) NULL;";
        DB::Prepare($sql2, array(), DB::FETCH_TYPE_ROW);
    } catch (Exception $exception) {
        Log::add("update", 'error', $exception);
    }

    frigate::generateEqEvents();
    frigate::generateEqStats();
    frigate::setConfigCron();
    frigate::setCmdsCron();
    Log::add("frigate", 'info', 'Finish Update');
}

// Fonction exécutée automatiquement après la suppression du plugin
function frigate_remove()
{
    Log::add("frigate", "info", "==> Début de la suppression de la database Frigate");
    $sql = "DROP TABLE IF EXISTS `frigate_events`;";
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
    Log::add("frigate", "info", "==> Fin de la suppression de la database Frigate");
}
