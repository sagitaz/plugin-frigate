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

use log;
use DB;

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function frigate_install()
{
    $pluginVersion = frigate::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'frigate');
    Log::add("frigate", 'info', 'Start Install');
    $sql = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL);

    frigate::setConfig();
    frigate::setConfigCron();
    frigate::addMessages();
    Log::add("frigate", 'info', 'Finish Install');
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function frigate_update()
{
    $pluginVersion = frigate::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'frigate');
    Log::add("frigate", 'info', 'Start Update');
    /*   Log::add("frigate", "info", "==> Début de la suppression de la database Frigate");
    $sql = "DROP TABLE IF EXISTS `frigate_events`;";
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
    Log::add("frigate", "info", "==> Fin de la suppression de la database Frigate"); */
    $sql1 = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql1, array(), DB::FETCH_TYPE_ALL);


    // Vérifier si la colonne 'type' existe déjà dans la table 'frigate_events'
    $sqlCheck = "SHOW COLUMNS FROM `jeedom`.`frigate_events` LIKE 'type';";
    $resultCheck = DB::Prepare($sqlCheck, array(), DB::FETCH_TYPE_ROW);
    if (empty($resultCheck)) {
        // Création de la nouvelle colonne 'type' si elle n'existe pas
        $sql2 = "ALTER TABLE `jeedom`.`frigate_events` ADD COLUMN `type` text DEFAULT NULL;";
        DB::Prepare($sql2, array(), DB::FETCH_TYPE_ROW);
    }
    // Vérifier si la colonne 'isFavorite' existe déjà dans la table 'frigate_events'
    $sqlCheck = "SHOW COLUMNS FROM `jeedom`.`frigate_events` LIKE 'isFavorite';";
    $resultCheck = DB::Prepare($sqlCheck, array(), DB::FETCH_TYPE_ROW);
    if (empty($resultCheck)) {
        // Création de la nouvelle colonne 'isFavorite' si elle n'existe pas
        $sql2 = "ALTER TABLE `jeedom`.`frigate_events` ADD COLUMN `isFavorite` tinyint(1) DEFAULT 0;";
        DB::Prepare($sql2, array(), DB::FETCH_TYPE_ROW);
    }
    // Mettre à jour les enregistrements où 'isFavorite' est NULL pour les définir à 0UPDATE `frigate_events`
    $sqlUpdate = "UPDATE `jeedom`.`frigate_events` SET `isFavorite` = 0 WHERE `isFavorite` != 1 AND `isFavorite` != 0;";
    DB::Prepare($sqlUpdate, array(), DB::FETCH_TYPE_ROW);

    // Vérifier si les colonnes recognition_* existent déjà dans la table 'frigate_events'
    $columns = ['recognition_type', 'recognition_description', 'recognition_name', 'recognition_plate', 'recognition_score'];
    foreach ($columns as $column) {
        $sqlCheck = "SHOW COLUMNS FROM `jeedom`.`frigate_events` LIKE '" . $column . "';";
        $resultCheck = DB::Prepare($sqlCheck, array(), DB::FETCH_TYPE_ROW);
        if (empty($resultCheck)) {
            // Création de la nouvelle colonne si elle n'existe pas
            if ($column == 'recognition_score') {
                $sql2 = "ALTER TABLE `jeedom`.`frigate_events` ADD COLUMN `" . $column . "` int(11) DEFAULT NULL;";
            } else {
                $sql2 = "ALTER TABLE `jeedom`.`frigate_events` ADD COLUMN `" . $column . "` text DEFAULT NULL;";
            }
            DB::Prepare($sql2, array(), DB::FETCH_TYPE_ROW);
        }
    }
    // Vérifier le type de la colonne data, si c'est text le passer en mediumtext
    $sqlCheck = "SHOW COLUMNS FROM `jeedom`.`frigate_events` LIKE 'data';";
    $resultCheck = DB::Prepare($sqlCheck, array(), DB::FETCH_TYPE_ROW);
    if (!empty($resultCheck) && isset($resultCheck['Type']) && $resultCheck['Type'] == 'text') {
        $sql2 = "ALTER TABLE `jeedom`.`frigate_events` MODIFY COLUMN `data` MEDIUMTEXT;";
        DB::Prepare($sql2, array(), DB::FETCH_TYPE_ROW);
    }

    frigate::setConfig();
    frigate::addMessages();
    frigate::deleteLatestFile();
    Log::add("frigate", 'info', 'Finish Update');
}

// Fonction exécutée automatiquement après la suppression du plugin
function frigate_remove()
{
    Log::add("frigate", "info", "==> Début de la suppression de la database Frigate");
    $sql = "DROP TABLE IF EXISTS `frigate_events`;";
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
    Log::add("frigate", "info", "==> Fin de la suppression de la database Frigate");

    Log::add("frigate", "info", "==> Désenregistrement du topic Frigate de MQTT2");
    frigate::removeMQTTTopicRegistration();
}
