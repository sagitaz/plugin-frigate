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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/frigate_events.class.php';

use Log;
use eqLogic;
use cmd;
use event;

class frigate extends eqLogic
{
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */


  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5()
  {
    frigate::getEvents();
    frigate::getStats();
  }


  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
   }
   */

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert()
  {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert()
  {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate()
  {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate()
  {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');
    $name = $this->getConfiguration('name');
    $height = $this->getConfiguration('height', 500);
    $bbox = $this->getConfiguration('bbox', 0);
    $timestamp = $this->getConfiguration('timestamp', 1);
    $zones = $this->getConfiguration('zones', 0);
    $mask = $this->getConfiguration('mask', 0);
    $motion = $this->getConfiguration('motion', 0);
    $regions = $this->getConfiguration('regions', 0);
    $quality = $this->getConfiguration('quality', 70);

    $img = "http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?bbox=" . $bbox . "&timestamp=" . $timestamp . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions;
    log::add(__CLASS__, 'debug', "URL = " . json_encode($img));

    $this->setConfiguration('img', $img);
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave()
  {
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove()
  {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove()
  {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*     * **********************Getteur Setteur*************************** */
  private static function getcURL($function, $url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, 'debug', "Error:" . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = json_decode($data, true);
    return $response;
  }
  public static function getStats()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    $resultURL = $url . ":" . $port . "/api/stats";

    $stats = self::getcURL("Stats", $resultURL);

    foreach ($stats as $stat) {

        self::majStatsCmds($stat);
      
    }
  }

  public static function getTimeline()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    $resultURL = $url . ":" . $port . "/api/timeline";

    $result = self::getcURL("Timeline", $resultURL);

    return $result;
  }
  public static function getEvents()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    $resultURL = $url . ":" . $port . "/api/events";

    $events = self::getcURL("Events", $resultURL);

    foreach ($events as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      if (!$frigate) {

        $frigate = new frigate_events();

        $frigate->setBox($event['box']);
        $frigate->setCamera($event['camera']);
        $frigate->setData($event['data']);
        $frigate->setStartTime($event['start_time']);
        $frigate->setEndTime($event['end_time']);
        $frigate->setFalsePositive($event['false_positive']);
        $frigate->setHasClip($event['has_clip']);
        $frigate->setHasSnapshot($event['has_snapshot']);
        $frigate->setEventId($event['id']);
        $frigate->setLabel($event['label']);
        $frigate->setPlusId($event['plus_id']);
        $frigate->setRetain($event['retain_indefinitely']);
        $frigate->setSubLabel($event['sub_label']);
        $frigate->setThumbnail($event['thumbnail']);
        $frigate->setTopScore($event['data']['top_score']);
        $frigate->setZones($event['zones']);
        $frigate->save();

        self::majEventsCmds($event);
        event::add("frigate::alert", $event);
      }
    }
  }
  public static function getEvents2()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');
    $events = frigate_events::all();

    foreach ($events as $event) {
      $img = "http://" . $url . ":" . $port . "/api/events/" . $event->getEventId() . "/thumbnail.jpg";
      $clip = "http://" . $url . ":" . $port . "/api/events/" . $event->getEventId() . "/clip.mp4";
      $snapshot = "http://" . $url . ":" . $port . "/api/events/" . $event->getEventId() . "/snapshot.jpg";
      $date = date("d-m-Y H:i:s", $event->getStartTime());
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);

      $result[] = array(
        "img" => $img,
        "camera" => $event->getCamera(),
        "label" => $event->getLabel(),
        "date" => $date,
        "duree" => $duree,
        "snapshot" => $snapshot,
        "clip" => $clip,
        "hasSnapshot" => $event->getHasSnapshot(),
        "hasClip" => $event->getHasClip(),
        "id" => $event->getEventId(),
        "top_score" => round($event->getTopScore() * 100, 0)
      );
    }

    usort($result, 'frigate::orderByDate');

    return $result;
  }

  private static function orderByDate($a, $b)
  {
    $dateA = new DateTime($a['date']);
    $dateB = new DateTime($b['date']);
    return $dateB <=> $dateA;
  }

  private static function orderByScore($a, $b)
  {
    return $b['top_score'] <=> $a['top_score'];
  }


  public static function generateEqEvents()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!is_object($frigate)) {
      $frigate = new frigate();
      $frigate->setName('Events');
      $frigate->setEqType_name("frigate");
      $frigate->setLogicalId("eqFrigateEvents");
      $frigate->save();
    }
  }

  public static function generateEqStats()
  {
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    if (!is_object($frigate)) {
      $frigate = new frigate();
      $frigate->setName('Statistiques');
      $frigate->setEqType_name("frigate");
      $frigate->setLogicalId("eqFrigateStats");
      $frigate->save();
    }
  }
  private static function createCmd($eqLogicId, $name, $subType, $unite, $logicalId, $genericType, $historized = 1)
  {
    $eqlogic = eqLogic::byId($eqLogicId);
    $cmd = $eqlogic->getCmd(NULL, $logicalId);

    if (!is_object($cmd)) {
      $cmd = new jeemateCmd();
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized($historized);
      $cmd->setEqLogic_id($eqlogic->getId());
      $cmd->setName($name);
      $cmd->setType("info");
      $cmd->setSubType($subType);
      $cmd->setUnite($unite);
      $cmd->setLogicalId($logicalId);
      $cmd->setGeneric_type($genericType);
      $cmd->save();
    }
    return $cmd;
  }

  public static function majEventsCmds($event)
  {

    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $eqlogicId = $frigate->getId();

    // creation des commandes

    $cmd = self::createCmd($eqlogicId, "caméra", "string", "", "info_camera", "GENERIC_INFO", 0);
    $cmd->event($event['camera']);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "label", "string", "", "info_label", "GENERIC_INFO", 0);
    $cmd->event($event['label']);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "clips", "binary", "", "info_clips", "GENERIC_INFO", 0);
    log::add(__CLASS__, 'debug', "has-clip: " . $event['has_clip']);
    $hasClip = ($event['has_clip'] == 1) ? 1 : 0;
    log::add(__CLASS__, 'debug', "hasClip: " . $hasClip);
    $cmd->event($hasClip);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "snapshot", "binary", "", "info_snapshot", "GENERIC_INFO", 0);
    log::add(__CLASS__, 'debug', "has-snapshot: " . $event['has_snapshot']);
    $hasSnapshot = ($event['has_snapshot'] == 1) ? 1 : 0;
    log::add(__CLASS__, 'debug', "hasSnapshot: " . $hasSnapshot);
    $cmd->event($hasSnapshot);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "top score", "numeric", "%", "info_topscore", "GENERIC_INFO", 0);
    $value = round($event['data']['top_score'] * 100, 0);
    $cmd->event($value);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "score", "numeric", "%", "info_score", "GENERIC_INFO", 0);
    $value = round($event['data']['score'] * 100, 0);
    $cmd->event($value);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "id", "string", "", "info_id", "GENERIC_INFO", 0);
    $cmd->event($event['id']);
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "timestamp", "numeric", "", "info_timestamp", "GENERIC_INFO", 0);
    $cmd2 = self::createCmd($eqlogicId, "durée", "numeric", "sc", "info_duree", "GENERIC_INFO", 0);
    $cmd->event($event['start_time']);
    $cmd->save();
    $value = round($event['end_time'] - $event['start_time'], 0);
    $cmd2->event($value);
    $cmd2->save();
  }


  public static function majStatsCmds($stats)
  {
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $eqlogicId = $frigate->getId();

    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      foreach ($cameraStats as $key => $value) {
        // Créez un nom de commande en combinant le nom de la caméra et la clé
        $cmdName = $cameraName . '_' . $key;
        // Créez ou récupérez la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "cameras_" . $key, "GENERIC_INFO", 0);
        // Enregistrez la valeur de l'événement
        $cmd->event($value);
        $cmd->save();
      }
    }
  }


}
class frigateCmd extends cmd
{
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array())
  {
  }

  /*     * **********************Getteur Setteur*************************** */
}
