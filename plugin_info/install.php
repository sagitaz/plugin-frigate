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
    Log::add(__CLASS__, 'info', 'Start Install');
    self::frigate_remove();
    $sql = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL);
    frigate::generateEqEvents();
    frigate::generateEqStats();
    frigate::setConfig();
    frigate::setCmdsCron();
    Log::add(__CLASS__, 'info', 'Finish Install');
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function frigate_update()
{
    Log::add(__CLASS__, 'info', 'Start Update');
    self::frigate_remove();
    $sql = file_get_contents(dirname(__FILE__) . '/install.sql');
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL);
    frigate::generateEqEvents();
    frigate::generateEqStats();
    frigate::setCmdsCron();
    Log::add(__CLASS__, 'info', 'Finish Update');
}

// Fonction exécutée automatiquement après la suppression du plugin
function frigate_remove()
{
    Log::add(__CLASS__, "info", "==> Début de la suppression de la database Frigate");
    $sql = "DROP TABLE IF EXISTS `frigate_events`;";
    DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
    Log::add(__CLASS__, "info", "==> Fin de la suppression de la database Frigate");
}
