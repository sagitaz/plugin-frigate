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
use message;
use config;

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

  // Fonction exécuté à l'installation du plugin
  public static function setConfig()
  {
    // Configuration par défaut
    config::save('URL', '', 'frigate');
    config::save('port', '5000', 'frigate');
    config::save('recovery_days', '7', 'frigate');
    config::save('remove_days', '7', 'frigate');
    config::save('cron', '5', 'frigate');
  }
  // configuration par defaut des crons
  public static function setConfigCron()
  {
    // cron par défaut
    if (!config::byKey('functionality::cron::enable', 'frigate')) {
      config::save('functionality::cron::enable', 0, 'frigate');
    }
    if (!config::byKey('functionality::cron5::enable', 'frigate')) {
      config::save('functionality::cron5::enable', 1, 'frigate');
    }
    if (!config::byKey('functionality::cron10::enable', 'frigate')) {
      config::save('functionality::cron10::enable', 0, 'frigate');
    }
    if (!config::byKey('functionality::cron15::enable', 'frigate')) {
      config::save('functionality::cron15::enable', 0, 'frigate');
    }
    if (!config::byKey('functionality::cron30::enable', 'frigate')) {
      config::save('functionality::cron30::enable', 0, 'frigate');
    }
    if (!config::byKey('functionality::cronHourly::enable', 'frigate')) {
      config::save('functionality::cronHourly::enable', 0, 'frigate');
    }
  }
  // Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cron::enable', 'frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cron5::enable','frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cron10::enable','frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cron15::enable','frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cron30::enable','frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }
  // Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
    if (config::byKey('functionality::cronHourly::enable','frigate', '0 ') == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
  }


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
    $bbox = $this->getConfiguration('bbox', 0);
    $timestamp = $this->getConfiguration('timestamp', 1);
    $zones = $this->getConfiguration('zones', 0);
    $mask = $this->getConfiguration('mask', 0);
    $motion = $this->getConfiguration('motion', 0);
    $regions = $this->getConfiguration('regions', 0);
    $quality = $this->getConfiguration('quality', 70);

    $img = "http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?bbox=" . $bbox . "&timestamp=" . $timestamp . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions;
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
    log::add(__CLASS__, 'debug', $function . " : mise à jour.");
    return $response;
  }

  private static function deletecURL($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, 'debug', "Error:" . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = json_decode($data, true);
    log::add(__CLASS__, 'debug', "Delete : " . json_encode($response));
    return $response;
  }

  public static function getStats()
  {
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return;
    }

    $resultURL = $url . ":" . $port . "/api/stats";

    $stats = self::getcURL("Stats", $resultURL);
    self::majStatsCmds($stats);
  }
  public static function getEvents()
  {
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return;
    }
    $resultURL = $url . ":" . $port . "/api/events";
    $events = self::getcURL("Events", $resultURL);
    // traiter les evenemnts du plus ancien au plus recent
    $events = array_reverse($events);


    // Nombre de jours a filtrer et enregistrer en DB
    $recoveryDays = config::byKey('recovery_days', 'frigate');
    if (empty($recoveryDays)) {
      $recoveryDays = 7;
    }

    $filteredEvents = array_filter($events, function ($event) use ($recoveryDays) {
      return $event['start_time'] >= time() - $recoveryDays * 86400;
    });
    $filteredEvents = array_values($filteredEvents);

    foreach ($filteredEvents as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      $img = self::saveURL($event['id'], "snapshot", $event['camera'], 1);

      if ($event['has_snapshot'] == 1) {
        $snapshot = self::saveURL($event['id'], "snapshot", $event['camera']);
      } else {
        $snapshot = "null";
      }
      if ($event['has_clip'] == 1) {
        $clip = self::saveURL($event['id'], "clip", $event['camera']);
      } else {
        $clip = "null";
      }

      if (!$frigate) {
        $frigate = new frigate_events();
        $frigate->setBox($event['box']);
        $frigate->setCamera($event['camera']);
        $frigate->setData($event['data']);
        $frigate->setLasted($img);
        $frigate->setClip($clip);
        $frigate->setSnapshot($snapshot);
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
        $frigate->setThumbnail($img);
        $frigate->setTopScore(round($event['data']['top_score'] * 100, 0));
        $frigate->setScore(round($event['data']['score'] * 100, 0));
        $frigate->setZones($event['zones']);
        $frigate->save();

        self::majEventsCmds($frigate);
      }
    }
    // Nombre de jours a garder en DB
    $removeDays = config::byKey('remove_days', 'frigate');
    if (empty($removeDays)) {
      $removeDays = 7;
    } else if ($removeDays < $recoveryDays) {
      $removeDays = $recoveryDays;
      log::add(__CLASS__, 'warning', "le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. removeDays est donc égale à recoveryDays.");
    }

    $filteredEvents = array_filter($events, function ($event) use ($removeDays) {
      return $event['start_time'] >= time() - $removeDays * 86400;
    });
    $filteredEvents = array_values($filteredEvents);
    self::cleanDbEvents($filteredEvents);
  }

  public static function cleanDbEvents($events)
  {
    foreach ($events as $event) {
      $ids[] = $event['id'];
    }

    $inDbEvents = frigate_events::all();

    foreach ($inDbEvents as $inDbEvent) {
      if (!in_array($inDbEvent->getEventId(), $ids)) {
        log::add(__CLASS__, 'debug', "delete in DB : " . $inDbEvent->getEventId());
        $inDbEvent->remove();
        log::add(__CLASS__, 'debug', "Events delete in DB");
        // recherche si clip et snapshot existe dans le dossier de sauvegarde
        $clip = dirname(__FILE__, 3) . "/data/" . $inDbEvent->getCamera() . "/" . $inDbEvent->getEventId() . "_clip.mp4";
        $snapshot = dirname(__FILE__, 3) . "/data/" . $inDbEvent->getCamera() . "/" . $inDbEvent->getEventId() . "_snapshot.jpg";
        $thumbnail = dirname(__FILE__, 3) . "/data/" . $inDbEvent->getCamera() . "/" . $inDbEvent->getEventId() . "_thumbnail.jpg";

        if (file_exists($clip)) {
          unlink($clip);
          log::add(__CLASS__, 'debug', "MP4 clip delete");
        }
        if (file_exists($snapshot)) {
          unlink($snapshot);
          log::add(__CLASS__, 'debug', "JPG snapshot delete");
        }
        if (file_exists($thumbnail)) {
          unlink($thumbnail);
          log::add(__CLASS__, 'debug', "JPG thumbnail delete");
        }
      }
    }
  }

  public static function deleteEvent($id)
  {
    log::add(__CLASS__, 'debug', "Delete ID : " . $id);
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return;
    }

    $resultURL = $url . ":" . $port . "/api/events/" . $id;

    self::deletecURL($resultURL);

    $events = frigate_events::byEventId($id);
    foreach ($events as $event) {
      $event->remove();
    }
  }
  public static function showEvents()
  {
    $events = frigate_events::all();

    foreach ($events as $event) {
      $date = date("d-m-Y H:i:s", $event->getStartTime());
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);

      $result[] = array(
        "img" => $event->getLasted(),
        "camera" => $event->getCamera(),
        "label" => $event->getLabel(),
        "date" => $date,
        "duree" => $duree,
        "snapshot" => $event->getSnapshot(),
        "clip" => $event->getClip(),
        "hasSnapshot" => $event->getHasSnapshot(),
        "hasClip" => $event->getHasClip(),
        "id" => $event->getEventId(),
        "top_score" => $event->getTopScore()
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

  public static function generateEqCameras()
  {
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      $result = "URL";
      return $result;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      $result = "PORT";
      return $result;
    }

    $resultURL = $url . ":" . $port . "/api/stats";
    $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;
    log::add(__CLASS__, 'debug', "Liste de cameras : " . json_encode($stats['cameras']));
    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      // recherche equipement caméra
      $eqCamera = eqLogic::byTypeAndSearchConfiguration("frigate", $cameraName);
      $frigate = $eqCamera[0];
      if (!is_object($frigate)) {
        log::add(__CLASS__, 'debug', "L'équipement : " . json_encode($cameraName) . "est créé.");
        $n++;
        $frigate = new frigate();
        $frigate->setName($cameraName);
        $frigate->setEqType_name("frigate");
        $frigate->setLogicalId("eqFrigateCameras");
        $frigate->setConfiguration("name", $cameraName);
        if ($defaultRoom) $frigate->setObject_id($defaultRoom);
        $frigate->setIsEnable(1);
        $frigate->setIsVisible(1);
        $frigate->save();
      }
    }
    return $n;
  }
  public static function generateEqEvents()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    if (!is_object($frigate)) {
      $frigate = new frigate();
      $frigate->setName('Events');
      $frigate->setEqType_name("frigate");
      $frigate->setLogicalId("eqFrigateEvents");
      if ($defaultRoom) $frigate->setObject_id($defaultRoom);
      $frigate->setIsEnable(1);
      $frigate->setIsVisible(1);
      $frigate->save();
    }
  }

  public static function generateEqStats()
  {
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    if (!is_object($frigate)) {
      $frigate = new frigate();
      $frigate->setName('Statistiques');
      $frigate->setEqType_name("frigate");
      $frigate->setLogicalId("eqFrigateStats");
      if ($defaultRoom) $frigate->setObject_id($defaultRoom);
      $frigate->setIsEnable(1);
      $frigate->setIsVisible(1);
      $frigate->save();
    }
  }
  private static function createCmd($eqLogicId, $name, $subType, $unite, $logicalId, $genericType, $infoCmd = null, $historized = 1, $type = "info")
  {
    $cmd = cmd::byEqLogicIdCmdName($eqLogicId, $name);

    if (!is_object($cmd)) {
      $cmd = new frigateCmd();
      $cmd->setLogicalId($logicalId);
      $cmd->setEqLogic_id($eqLogicId);
      $cmd->setName($name);
      $cmd->setType($type);
      $cmd->setSubType($subType);
      $cmd->setGeneric_type($genericType);
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized($historized);
      $cmd->setUnite($unite);
      if (is_object($infoCmd) && $type == 'action') {
        $cmd->setValue($infoCmd->getId());
      }
      $cmd->save();
    }
    return $cmd;
  }

  public static function setCmdsCron()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    // Création des commandes Crons pour l'equipement général
    // commande infos
    $infoCmd = self::createCmd($frigate->getId(), "Etat cron", "binary", "", "info_Cron", "LIGHT_STATE", 0);
    $infoCmd->save();
    // commandes actions
    $cmd = self::createCmd($frigate->getId(), "Stop cron", "other", "", "action_stopCron", "LIGHT_ON", $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($frigate->getId(), "Start cron", "other", "", "action_startCron", "LIGHT_OFF", $infoCmd, 0, "action");
    $cmd->save();
  }

  public static function majEventsCmds($event)
  {
    $eqlogicIds = [];
    // maj des commandes de l'équipement events général
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    $eqlogicIds[] = $frigate->getId();
    // recherche et création equipement caméra    
    $cameraName = $event->getCamera();
    $frigate = frigate::byTypeAndSearhConfiguration('frigate', $cameraName);
    $eqCamera = $frigate[0];
    $eqlogicIds[] = $eqCamera->getId();
    self::executeActionNewEvent($eqCamera->getId(), $event);


    foreach ($eqlogicIds as $eqlogicId) {
      // creation des commandes infos
      $cmd = self::createCmd($eqlogicId, "caméra", "string", "", "info_camera", "GENERIC_INFO", 0);
      $cmd->event($event->getCamera());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "label", "string", "", "info_label", "GENERIC_INFO", 0);
      $cmd->event($event->getLabel());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "clip disponible", "binary", "", "info_clips", "GENERIC_INFO", 0);
      $cmd->event($event->getHasClip());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "snapshot disponible", "binary", "", "info_snapshot", "GENERIC_INFO", 0);
      $cmd->event($event->getHasSnapshot());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "top score", "numeric", "%", "info_topscore", "GENERIC_INFO", 0);
      $cmd->event($event->getTopScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "score", "numeric", "%", "info_score", "GENERIC_INFO", 0);
      $cmd->event($event->getScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "id", "string", "", "info_id", "GENERIC_INFO", 0);
      $cmd->event($event->getEventId());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "timestamp", "numeric", "", "info_timestamp", "GENERIC_INFO", 0);
      $cmd2 = self::createCmd($eqlogicId, "durée", "numeric", "sc", "info_duree", "GENERIC_INFO", 0);
      $cmd->event($event->getStartTime());
      $cmd->save();
      $value = round($event->getEndTime() - $event->getStartTime(), 0);
      $cmd2->event($value);
      $cmd2->save();
    }
  }


  public static function majStatsCmds($stats)
  {
    // Statistiques pour chaque eqLogic caméras
    // Mise à jour des statistiques des caméras
    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      log::add(__CLASS__, 'debug', "Camera : " . json_encode($cameraName));
      // recherche equipement caméra
      $eqCamera = eqLogic::byTypeAndSearchConfiguration("frigate", $cameraName);
      if (is_object($eqCamera[0])) {
        $eqlogicCameraId = $eqCamera[0]->getId();
        foreach ($cameraStats as $key => $value) {
          // Créez ou récupérez la commande
          $cmd = self::createCmd($eqlogicCameraId, $key, "numeric", "", "cameras_" . $key, "GENERIC_INFO", 0);
          // Enregistrez la valeur de l'événement
          $cmd->event($value);
          $cmd->save();
        }
      } else {
        log::add(__CLASS__, 'debug', "L'équipement camera n'existe pas.");
      }
    }


    // Statistiques pour eqLogic statistiques générales
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $eqlogicId = $frigate->getId();

    // Mise à jour des statistiques des détecteurs
    foreach ($stats['detectors'] as $detectorName => $detectorStats) {
      foreach ($detectorStats as $key => $value) {
        // Créez un nom de commande en combinant le nom du détecteur et la clé
        $cmdName = $detectorName . '_' . $key;
        // Créez ou récupérez la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "detectors_" . $key, "GENERIC_INFO", 0);
        // Enregistrez la valeur de l'événement
        $cmd->event($value);
        $cmd->save();

        if ($detectorName === "pid") {
          $cmdCpu = self::createCmd($eqlogicId, $detectorName . '_cpu', "numeric", "", "detectors_cpu", "GENERIC_INFO", 0);
          $cmdCpu->event($stats['cpu_usages'][$value]['cpu']);
          $cmdCpu->save();
          $cmdMem = self::createCmd($eqlogicId, $detectorName . '_memory', "numeric", "", "detectors_memory", "GENERIC_INFO", 0);
          $cmdMem->event($stats['cpu_usages'][$value]['mem']);
          $cmdMem->save();
        }
      }
    }

    // Mise à jour des usages GPU
    foreach ($stats['gpu_usages'] as $gpuName => $gpuStats) {
      foreach ($gpuStats as $key => $value) {
        // Créez un nom de commande en combinant le nom du GPU et la clé
        $cmdName = $gpuName . '_' . $key;
        // Créez ou récupérez la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "gpu_" . $key, "GENERIC_INFO", 0);
        // Enregistrez la valeur de l'événement
        $cmd->event($value);
        $cmd->save();
      }
    }

    // Créez ou récupérez la commande version Frigate
    $version = strstr($stats['service']['version'], '-', true);
    $latestVersion = $stats['service']['latest_version'];
    if (str_replace('.', '', $version) < str_replace('.', '', $latestVersion)) {
      message::add('frigate', __("Une nouvelle version de Frigate (" . $latestVersion . ") est disponible.", __FILE__), null, null);
    }
    $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "GENERIC_INFO", 0);
    // Enregistrez la valeur de l'événement
    $cmd->event($version);
    $cmd->save();
  }

  private static function executeActionNewEvent($eqLogicId, $event)
  {
    // liste des events et de leurs variables
    $hasClip = $event->getHasClip();
    $hasSnapshot = $event->getHasSnapshot();
    $topScore = $event->getTopScore();
    $clip = $event->getClip();
    $snapshot = $event->getSnapshot();
    $camera = $event->getCamera();
    $label = $event->getLabel();
    $duree = round($event->getEndTime() - $event->getStartTime(), 0);
    $zones = $event->getZones();
    $score = $event->getScore();
    $start = date("d-m-Y H:i:s", $event->getStartTime());
    $end = date("d-m-Y H:i:s", $event->getEndTime());

    $eqLogic = eqLogic::byId($eqLogicId);
    $actions = $eqLogic->getConfiguration('actions');
    foreach ($actions[0] as $action) {
      $id = str_replace("#", "", $action['cmd']);
      $cmd = cmd::byId($id);
      $options = $action['options'];
      $options = str_replace(['#camera#', '#score#', '#has_clip#', '#has_snapshot#', '#top_score#', '#zones#', '#snapshot#', '#clip#', '#label#', '#start#', '#end#', '#duree#'], [$camera, $score, $hasClip, $hasSnapshot, $topScore, $zones, $snapshot, $clip, $label, $start, $end, $duree], $options);
      // executer l'action que si $start est compris entre l'heure actuelle et -3h.
      if ($event->getStartTime > time() - 10800) {
        if (strpos(json_encode($action['options']), '#clip#') !== false) {
          log::add(__CLASS__, 'debug', "ACTION CLIP");
          if ($hasClip == 1) {
            log::add(__CLASS__, 'debug', "EXECUTE ACTION CLIP");
            $cmd->execCmd($options);
          }
        } else if (strpos(json_encode($action['options']), '#snapshot#') !== false) {
          log::add(__CLASS__, 'debug', "ACTION SNAPSHOT");
          if ($hasSnapshot == 1) {
            log::add(__CLASS__, 'debug', "EXECUTE ACTION SNAPSHOT");
            $cmd->execCmd($options);
          }
        } else {
          log::add(__CLASS__, 'debug', "AUCUNE VARIABLE");
          $cmd->execCmd($options);
        }
      } else {

        log::add(__CLASS__, 'debug', "Heure dépassée : " . $start);
      }
    }
  }

  public static function saveURL($eventId = null, $type = null, $camera, $thumbnail = 0, $latest = 0, $img = null)
  {
    $result = "";
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return;
    }
    $format = ($type == "snapshot") ? "jpg" : "mp4";
    $lien = "http://" . $url . ":" . $port . "/api/events/" . $eventId . "/" . $type . "." . $format;
    $path = dirname(__FILE__, 3) . "/data/" . $camera . "/" . $eventId . "_" . $type . "." . $format;
    if ($thumbnail == 1) {
      $lien = "http://" . $url . ":" . $port . "/api/events/" . $eventId . "/thumbnail.jpg";
      $path = dirname(__FILE__, 3) . "/data/" . $camera . "/" . $eventId . "_thumbnail.jpg";
    }
    if ($latest == 1) {
      $lien = $img;
      $path = dirname(__FILE__, 3) . "/data/" . $camera . "/latest.jpg";
    }

    // Vérifiez si le fichier existe déjà
    if (file_exists($path) && $latest == 0) {
      return $urlJeedom . str_replace("/var/www/html", "", $path);
    }

    // Obtenez le répertoire du chemin de destination
    $destinationDir = dirname($path);

    // Vérifiez si le répertoire existe, sinon créez-le
    if (!is_dir($destinationDir)) {
      if (!mkdir($destinationDir, 0755, true)) {
        log::add(__CLASS__, 'debug', "Échec de la création du répertoire.");
        return $result;
      }
    }

    // Téléchargez l'image ou la vidéo
    $content = file_get_contents($lien);

    if ($content !== false) {
      // Enregistrez l'image ou la vidéo dans le dossier spécifié
      $file = file_put_contents($path, $content);
      if ($file !== false) {
        // log::add(__CLASS__, 'debug', "Le fichier a été téléchargé et enregistré avec succès.");
        $result = $urlJeedom . str_replace("/var/www/html", "", $path);
      } else {
        log::add(__CLASS__, 'debug', "Échec de l'enregistrement du fichier : " . $lien);
      }
    } else {
      log::add(__CLASS__, 'debug', "Échec du téléchargement du fichier : " . $lien);
    }

    return $result;
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
    $frigate = $this->getEqLogic();
    if ($this->getLogicalId() == 'action_startCron') {
      $frigate->getCmd(null, 'info_Cron')->event(1);
      log::add(__CLASS__, 'debug', "Cron activé");
    } else if ($this->getLogicalId() == 'action_stopCron') {
      $frigate->getCmd(null, 'info_Cron')->event(0);
      log::add(__CLASS__, 'debug', "Cron désactivé");
    }
  }

  /*     * **********************Getteur Setteur*************************** */
}
