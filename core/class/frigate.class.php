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
use mqtt2;

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

  // Fonction exécutée à l'installation du plugin
  public static function setConfig()
  {
    // Configuration par défaut
    config::save('URL', '', 'frigate');
    config::save('port', '5000', 'frigate');
    config::save('topic', 'frigate', 'frigate');
    config::save('recovery_days', '7', 'frigate');
    config::save('remove_days', '7', 'frigate');
    config::save('datas_weight', '500', 'frigate');
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
    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cron::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cron5::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cron10::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cron15::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
      }
    }
  }
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');

    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cron30::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
      }
    }
  }
  // Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!empty($frigate)) {
      $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();
      if (config::byKey('functionality::cronHourly::enable', 'frigate', 0) == 1) {
        if ($execute == "1") {
          self::getEvents();
          self::getStats();
        }
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

    if ($this->getLogicalId() != 'eqFrigateStats' && $this->getLogicalId() != 'eqFrigateEvents') {
      $name = $this->getConfiguration('name');
      $bbox = $this->getConfiguration('bbox', 0);
      $timestamp = $this->getConfiguration('timestamp', 1);
      $zones = $this->getConfiguration('zones', 0);
      $mask = $this->getConfiguration('mask', 0);
      $motion = $this->getConfiguration('motion', 0);
      $regions = $this->getConfiguration('regions', 0);
      $quality = $this->getConfiguration('quality', 70);

      $img = htmlspecialchars("http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?bbox=" . $bbox . "&timestamp=" . $timestamp . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions);
      $this->setConfiguration('img', $img);
    }
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave()
  {
    /*
    $camera = $this->getConfiguration('name');
    $audio = $this->getConfiguration('audio', -1);
    if ($audio != -1) {
      self::publish_camera_message($camera, 'audio/set', ($audio == "1") ? 'ON' : 'OFF');
    }
    $birdeye = $this->getConfiguration('birdeye', -1);
    if ($birdeye != -1) {
      self::publish_camera_message($camera, 'birdeye/set', ($birdeye == "1") ? 'ON' : 'OFF');
    }
    $detect = $this->getConfiguration('detect', -1);
    if ($detect != -1) {
      self::publish_camera_message($camera, 'detect/set', ($detect == "1") ? 'ON' : 'OFF');
    }
    $improve_constrast = $this->getConfiguration('improve_constrast', -1);
    if ($improve_constrast != -1) {
      self::publish_camera_message($camera, 'improve_constrast/set', ($improve_constrast == "1") ? 'ON' : 'OFF');
    }
    /* TODO : $motion_contour_area : integer (10s)
    $motion_contour_area = $this->getConfiguration('detect', -1);
    if ($motion_contour_area != -1) {
      self::publish_camera_message($camera, 'motion_contour_area/set', ($motion_contour_area == "1") ? 'ON' : 'OFF');
    }
    */
    /* TODO : $motion_threshold : integer (30s)
    $motion_threshold = $this->getConfiguration('motion_threshold', -1);
    if ($motion_threshold != -1) {
      self::publish_camera_message($camera, 'motion_threshold/set', ($motion_threshold == "1") ? 'ON' : 'OFF');
    }*/
    /*

    $ptz_autotracker = $this->getConfiguration('ptz_autotracker', -1);
    if ($ptz_autotracker != -1) {
      self::publish_camera_message($camera, 'ptz_autotracker/set', ($ptz_autotracker == "1") ? 'ON' : 'OFF');
    }
    $recordings = $this->getConfiguration('recordings', -1);
    if ($recordings != -1) {
      self::publish_camera_message($camera, 'recordings/set', ($recordings == "1") ? 'ON' : 'OFF');
    }
    $snapshots = $this->getConfiguration('snapshots', -1);
    if ($snapshots != -1) {
      self::publish_camera_message($camera, 'snapshots/set', ($snapshots == "1") ? 'ON' : 'OFF');
    }
    */
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
  private static function getTopic()
  {
    return config::byKey('topic', 'frigate');
  }

  public static function publish_camera_message(string $camera, string $subTopic, string $payload)
  {
    self::publish_message("{$camera}/{$subTopic}", $payload);
  }

  public static function publish_message(string $subTopic, string $payload)
  {
    // log::add(__CLASS__, 'debug', "publish_message : " . self::getTopic() . "/{$subTopic} avec payload : {$payload}");
    mqtt2::publish(self::getTopic() . "/{$subTopic}", $payload);
  }

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

  public static function checkDatasWeight()
  {
    $recoveryDays = config::byKey('recovery_days', 'frigate'); // default 7
    $removeDays = config::byKey('remove_days', 'frigate'); // default 7
    $datasWeight = config::byKey('datas_weight', 'frigate'); // default 500 MB
    $folderIsFull = false;
    // Calculer la taille du dossier en octets
    $size = 0;
    $dir = dirname(__FILE__, 3) . "/data/";
    if (is_dir($dir)) {
      $size = 0;
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) {
          $size += $file->getSize();
        }
      }
    }
    // taille du dossier en MB
    $folderSizeInMB = round($size / 1024 / 1024, 2);
    // taille disponible du dossier en MB
    $folderAvailableSizeInMB = round(disk_free_space($dir) / 1024 / 1024, 2);
    // On verifie qu'il y a suffisament de place sur Jeedom
    if ($folderAvailableSizeInMB <= $datasWeight) {
      $folderIsFull = true;
      log::add(__CLASS__, 'debug', "Dossier Jeedom plein, taille : " . $folderSizeInMB . " MB");
    } else {
      log::add(__CLASS__, 'debug', "Dossier Jeedom OK, taille : " . $folderSizeInMB . " MB");
      // On verifie que le dossier n'est pas plein
      if ($folderSizeInMB <= $datasWeight) {
        log::add(__CLASS__, 'debug', "Dossier data Frigate OK, taille : " . $folderSizeInMB . " MB");
      } else {
        log::add(__CLASS__, 'debug', "Dossier data Frigate plein, taille : " . $folderSizeInMB . " MB");
        $folderIsFull = true;
      }
    }
    // Si dossier plein alors on reduit le nombre de jours de recuperation automatiquement et de suppression
    if ($folderIsFull) {
      log::add(__CLASS__, 'debug', "Dossier plein, taille : " . $folderSizeInMB . " MB, on reduit le nombre de jours de récupération automatiquement");
      message::add('frigate', __("Dossier plein, taille : " . $folderSizeInMB . " MB, on reduit le nombre de jours de récupération automatiquement", __FILE__), null, null);
      // on reduit le nombre de jours de recuperation automatiquement
      $recoveryDays = $recoveryDays - 1;
      config::save('recovery_days', $recoveryDays, 'frigate');
      // on reduit le nombre de jours de suppression automatiquement
      $removeDays = $removeDays - 1;
      config::save('remove_days', $removeDays, 'frigate');
      log::add(__CLASS__, 'debug', "Duree de recuperation : " . $recoveryDays . " jours, Duree de suppression : " . $removeDays . " jours");
    }
    return true;
  }
  public static function getEvents($mqtt = false, $events = array(), $type = 'end')
  {
    if (!$mqtt) {
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
      // Traiter les evenements du plus ancien au plus recent
      $events = array_reverse($events);
    }

    // Verifier la taille disponible dans le dossier data et agir en conserquence
    self::checkDatasWeight();

    // Nombre de jours a filtrer et enregistrer en DB
    $recoveryDays = config::byKey('recovery_days', 'frigate');
    if (empty($recoveryDays)) {
      $recoveryDays = 7;
    }
    // Nombre de jours a garder en DB
    $removeDays = config::byKey('remove_days', 'frigate');
    if (empty($removeDays)) {
      $removeDays = 7;
    } else if ($removeDays < $recoveryDays) {
      $removeDays = $recoveryDays;
      log::add(__CLASS__, 'warning', "le nombre de jours de suppression ne peut pas être plus petit que le nombre de jours de récupération. removeDays est donc égale à recoveryDays.");
    }

    $filteredRemoveEvents = array_filter($events, function ($event) use ($removeDays) {
      return $event['start_time'] >= time() - $removeDays * 86400;
    });
    $filteredRemoveEvents = array_values($filteredRemoveEvents);

    if (!$mqtt) {
      self::cleanDbEvents($filteredRemoveEvents);
    }


    $filteredRecoveryEvents = array_filter($events, function ($event) use ($recoveryDays) {
      return $event['start_time'] >= time() - $recoveryDays * 86400;
    });
    $filteredRecoveryEvents = array_values($filteredRecoveryEvents);

    foreach ($filteredRecoveryEvents as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      $img = self::saveURL($event['id'], null, $event['camera'], 1);
      if ($img == "error") {
        $img = "null";
      }

      if ($event['has_snapshot'] == "true") {
        $snapshot = self::saveURL($event['id'], "snapshot", $event['camera']);
        $hasSnapshot = 1;
        if ($snapshot == "error") {
          $snapshot = "null";
          $hasSnapshot = 0;
        }
      } else {
        $snapshot = "null";
        $hasSnapshot = 0;
      }

      if ($event['has_clip'] == "true") {
        $clip = self::saveURL($event['id'], "clip", $event['camera']);
        $hasClip = 1;
        if ($clip == "error") {
          $clip = "null";
          $hasClip = 0;
        }
      } else {
        $clip = "null";
        $hasClip = 0;
      }
      $endTime = $event['end_time'];
      if (empty($event['end_time'])) {
        log::add(__CLASS__, 'debug', "Event without end_time, force 0 : " . json_encode($event));
        $endTime = 0;
      }

      if (!$frigate) {
        $frigate = new frigate_events();
        $frigate->setBox($event['box']);
        $frigate->setCamera($event['camera']);
        $frigate->setData($event['data']);
        $frigate->setLasted($img);
        $frigate->setHasClip($hasClip);
        $frigate->setClip($clip);
        $frigate->setHasSnapshot($hasSnapshot);
        $frigate->setSnapshot($snapshot);
        $frigate->setStartTime($event['start_time']);
        $frigate->setEndTime($endTime);
        $frigate->setFalsePositive($event['false_positive']);
        $frigate->setEventId($event['id']);
        $frigate->setLabel($event['label']);
        $frigate->setPlusId($event['plus_id']);
        $frigate->setRetain($event['retain_indefinitely']);
        $frigate->setSubLabel($event['sub_label']);
        $frigate->setThumbnail($img);
        if (!$mqtt) {
          $frigate->setTopScore(round($event['data']['top_score'] * 100, 0));
          $frigate->setScore(round($event['data']['score'] * 100, 0));
        } else {
          $frigate->setTopScore(round($event['top_score'] * 100, 0));
          $frigate->setScore(round($event['score'] * 100, 0));
        }
        $frigate->setZones($event['zones']);
        $frigate->setType($type);
        $frigate->setIsFavorite(0);
        $frigate->save();
        self::majEventsCmds($frigate);
      } else {
        //log::add(__CLASS__, 'debug', "Event : " . json_encode($frigate[0]->getEventId()));
        if ($frigate[0]->getEndTime() == 0) {
          log::add(__CLASS__, 'debug', "Mise à jour de l'évènement avec le nouveau end time.");
          $frigate[0]->setEndTime($event['end_time']);
          $frigate[0]->setType($type);
          $frigate[0]->save();
          self::majEventsCmds($frigate[0]);
        }
      }
    }
  }

  public static function cleanDbEvents($events)
  {
    $ids = array();
    foreach ($events as $event) {
      $ids[] = $event['id'];
    }

    $inDbEvents = frigate_events::all();

    foreach ($inDbEvents as $inDbEvent) {
      if (!in_array($inDbEvent->getEventId(), $ids)) {
        // Verifier si le fichier est un favoris
        $isFavorite = $inDbEvent->getIsFavorite();
        if ($isFavorite == 1) {
          log::add(__CLASS__, 'debug', "Evènement " . $inDbEvent->getEventId() . " est un favoris, il ne doit pas être supprimé de la DB.");
        } else {
          log::add(__CLASS__, 'debug', "delete in DB : " . $inDbEvent->getEventId());
          $inDbEvent->remove();
          log::add(__CLASS__, 'debug', "Events delete in DB");
          // Recherche si clip et snapshot existent dans le dossier de sauvegarde
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
  }

  public static function deleteEvent($id, $all = false)
  {
    log::add(__CLASS__, 'debug', "Delete ID : " . $id);
    $frigate = frigate_events::byEventId($id);
    $isFavorite = $frigate[0]->getIsFavorite();
    if ($isFavorite == 1) {
      log::add(__CLASS__, 'debug', "Evènement " . $frigate[0]->getEventId() . " est un favoris, il ne doit pas être supprimé de la DB.");
      message::add('frigate', __("L'évènement est un favoris, il ne peut pas être supprimé de la DB.", __FILE__), null, null);
      return "Error 01";
    }
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return "Error 02";
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return "Error 03";
    }

    $resultURL = $url . ":" . $port . "/api/events/" . $id;

    if ($all) {
      self::deletecURL($resultURL);
    }

    $events = frigate_events::byEventId($id);
    foreach ($events as $event) {
      $event->remove();
    }

    return "OK";
  }
  public static function showEvents()
  {
    $result = array();
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
        "top_score" => $event->getTopScore(),
        "type" => $event->getType(),
        "isFavorite" => $event->getIsFavorite()
      );
    }

    if (isset($result)) {
      usort($result, 'frigate::orderByDate');
    }

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

    $exist = 0;
    $addToName = "";
    $create = 1;
    $resultURL = $url . ":" . $port . "/api/stats";
    $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;
    log::add(__CLASS__, 'debug', "Liste de cameras : " . json_encode($stats['cameras']));

    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      $eqlogics = eqLogic::byObjectId("41");
      foreach ($eqlogics as $eqlogic) {
        $name = $eqlogic->getname();
        if ($name === $cameraName) {
          $exist = 1;
          break;
        }
      }
      if ($exist) {
        log::add(__CLASS__, 'debug', "L'équipement : " . json_encode($cameraName) . "existe dans la pièce : " . jeeObject::byId($defaultRoom)->getName());
        $addToName = " by frigate plugin";
      }
      // Recherche équipement caméra
      $frigate = eqLogic::byLogicalId("eqFrigateCamera_" . $cameraName, "frigate");
      if (!is_object($frigate)) {
        $n++;
        $frigate = new frigate();
        $frigate->setName($cameraName . $addToName);
        $frigate->setEqType_name("frigate");
        $frigate->setConfiguration("name", $cameraName);
        if ($defaultRoom) $frigate->setObject_id($defaultRoom);
        $frigate->setIsEnable(1);
        $frigate->setIsVisible(1);
        log::add(__CLASS__, 'debug', "L'équipement : " . json_encode($cameraName . $addToName) . " est créé.");
      } else {
        log::add(__CLASS__, 'debug', "L'équipement : " . json_encode($cameraName) . " n'est pas créé.");
      }
      $frigate->setLogicalId("eqFrigateCamera_" . $cameraName);
      $frigate->save();
    }
    return $n;
  }

  public static function restartFrigate()
  {
    log::add(__CLASS__, 'info', "restartFrigate");
    self::publish_message('restart', '');
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
  private static function createCmd($eqLogicId, $name, $subType, $unite, $logicalId, $genericType, $isVisible = 1, $infoCmd = null, $historized = 1, $type = "info")
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
      $cmd->setIsVisible($isVisible);
      $cmd->setIsHistorized($historized);
      $cmd->setUnite($unite);
      if (is_object($infoCmd) && $type == 'action') {
        $cmd->setValue($infoCmd->getId());
        $cmd->setTemplate('dashboard', 'core::toggle');
        $cmd->setTemplate('mobile', 'core::toggle');
      }
      $cmd->save();
    }
    return $cmd;
  }
  public static function createAndRefreshURLcmd($eqlogicId, $url)
  {
    $cmd = self::createCmd($eqlogicId, "URL", "string", "", "info_url", "GENERIC_INFO");
    $cmd->save();
    $cmd->event($url);
    $cmd->save();
  }

  public static function createMQTTcmds($eqlogicId)
  {
    $infoCmd = self::createCmd($eqlogicId, "detect Etat", "binary", "", "info_detect", "JEEMATE_CAMERA_DETECT_STATE", 0);
    $infoCmd->save();
    // commande action
    $cmd = self::createCmd($eqlogicId, "detect off", "other", "", "action_stop_detect", "JEEMATE_CAMERA_DETECT_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "detect on", "other", "", "action_start_detect", "JEEMATE_CAMERA_DETECT_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "detect toggle", "other", "", "action_toggle_detect", "JEEMATE_CAMERA_DETECT_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();


    $infoCmd = self::createCmd($eqlogicId, "recordings Etat", "binary", "", "info_recordings", "JEEMATE_CAMERA_NVR_STATE", 0);
    $infoCmd->save();
    // commande action
    $cmd = self::createCmd($eqlogicId, "recordings off", "other", "", "action_stop_recordings", "JEEMATE_CAMERA_NVR_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "recordings on", "other", "", "action_start_recordings", "JEEMATE_CAMERA_NVR_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "recordings toggle", "other", "", "action_toggle_recordings", "JEEMATE_CAMERA_NVR_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();


    $infoCmd = self::createCmd($eqlogicId, "snapshots Etat", "binary", "", "info_snapshots", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    // commande action
    $cmd = self::createCmd($eqlogicId, "snapshots off", "other", "", "action_stop_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "snapshots on", "other", "", "action_start_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "snapshots toggle", "other", "", "action_toggle_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();
  }

  public static function setCmdsCron()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    // Création des commandes Crons pour l'equipement général
    // commande infos
    $infoCmd = self::createCmd($frigate->getId(), "Cron etat", "binary", "", "info_Cron", "LIGHT_STATE", 0);
    if ($infoCmd->execCmd() == "" || $infoCmd->execCmd() == null) {
      $infoCmd->event(1);
    }
    $infoCmd->save();
    // commandes actions
    $cmd = self::createCmd($frigate->getId(), "Cron off", "other", "", "action_stopCron", "LIGHT_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($frigate->getId(), "Cron on", "other", "", "action_startCron", "LIGHT_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
  }

  public static function majEventsCmds($event)
  {
    $eqlogicIds = [];
    $cameraActionsExist = false;
    // Maj des commandes de l'équipement events général
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (is_object($frigate)) {
      $eqlogicIds[] = $frigate->getId();
    }
    // Recherche et création equipement caméra    
    $cameraName = $event->getCamera();
    $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $cameraName, "frigate");
    if (is_object($eqCamera)) {
      $eqlogicIds[] = $eqCamera->getId();
    }


    $cameraActions = $eqCamera->getConfiguration('actions');
    $cameraActionsExist = (empty($cameraActions));
    if (!$cameraActionsExist) {
      self::executeActionNewEvent($eqCamera->getId(), $event);
    } else {
      self::executeActionNewEvent($frigate->getId(), $event);
    }

    foreach ($eqlogicIds as $eqlogicId) {
      // Creation des commandes infos
      $cmd = self::createCmd($eqlogicId, "caméra", "string", "", "info_camera", "GENERIC_INFO");
      $cmd->event($event->getCamera());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "label", "string", "", "info_label", "JEEMATE_CAMERA_DETECT_TYPE_STATE");
      $cmd->event($event->getLabel());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "clip disponible", "binary", "", "info_clips", "GENERIC_INFO");
      $cmd->event($event->getHasClip());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "snapshot disponible", "binary", "", "info_snapshot", "GENERIC_INFO");
      $cmd->event($event->getHasSnapshot());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "top score", "numeric", "%", "info_topscore", "GENERIC_INFO");
      $cmd->event($event->getTopScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "score", "numeric", "%", "info_score", "GENERIC_INFO");
      $cmd->event($event->getScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "id", "string", "", "info_id", "GENERIC_INFO");
      $cmd->event($event->getEventId());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "timestamp", "numeric", "", "info_timestamp", "GENERIC_INFO");
      $cmd2 = self::createCmd($eqlogicId, "durée", "numeric", "sc", "info_duree", "GENERIC_INFO");
      $cmd->event($event->getStartTime());
      $cmd->save();
      if ($event->getEndTime() != NULL) {
        $value = round($event->getEndTime() - $event->getStartTime(), 0);
      } else {
        $value = 0;
      }
      $cmd2->event($value);
      $cmd2->save();
    }
  }

  public static function majStatsCmds($stats, $mqtt = false)
  {
    // Statistiques pour chaque eqLogic caméras
    // Mise à jour des statistiques des caméras
    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      //log::add(__CLASS__, 'debug', "Camera : " . json_encode($cameraName));
      // Recherche equipement caméra
      $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $cameraName, "frigate");
      if (is_object($eqCamera)) {
        $eqlogicCameraId = $eqCamera->getId();
        if ($mqtt) {
          self::createMQTTcmds($eqlogicCameraId);
        }
        foreach ($cameraStats as $key => $value) {
          // Créez ou récupérez la commande
          $cmd = self::createCmd($eqlogicCameraId, $key, "numeric", "", "cameras_" . $key, "GENERIC_INFO");
          // Enregistrez la valeur de l'événement
          $cmd->event($value);
          $cmd->save();
        }
      } else {
        log::add(__CLASS__, 'debug', "L'équipement camera " . $cameraName . " n'existe pas.");
      }
    }

    // Statistiques pour eqLogic statistiques générales
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $eqlogicId = $frigate->getId();

    // Création de la commande restart Frigate
    $cmd = self::createCmd($eqlogicId, "redémarrer frigate", "other", "", "action_restart", "GENERIC_ACTION", 1, "", 0, "action");
    $cmd->save();

    // Mise à jour des statistiques des détecteurs
    foreach ($stats['detectors'] as $detectorName => $detectorStats) {
      foreach ($detectorStats as $key => $value) {
        // Créez un nom de commande en combinant le nom du détecteur et la clé
        $cmdName = $detectorName . '_' . $key;
        // Créez ou récupérez la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "detectors_" . $key, "GENERIC_INFO");
        // Enregistrez la valeur de l'événement
        $cmd->event($value);
        $cmd->save();

        if ($detectorName === "pid") {
          $cmdCpu = self::createCmd($eqlogicId, $detectorName . '_cpu', "numeric", "", "detectors_cpu", "GENERIC_INFO");
          $cmdCpu->event($stats['cpu_usages'][$value]['cpu']);
          $cmdCpu->save();
          $cmdMem = self::createCmd($eqlogicId, $detectorName . '_memory', "numeric", "", "detectors_memory", "GENERIC_INFO");
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
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "gpu_" . $key, "GENERIC_INFO");
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
    $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "GENERIC_INFO");
    // Enregistrez la valeur de l'événement
    $cmd->event($version);
    $cmd->save();
  }

  private static function executeActionNewEvent($eqLogicId, $event)
  {
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }
    // Liste des events et de leurs variables
    $eventId = $event->getEventId();
    $hasClip = $event->getHasClip();
    $hasSnapshot = $event->getHasSnapshot();
    $topScore = $event->getTopScore();
    $clip = $urlJeedom . $event->getClip();
    $snapshot = $urlJeedom . $event->getSnapshot();
    $clipPath = "/var/www/html" . $event->getClip();
    $snapshotPath = "/var/www/html" . $event->getSnapshot();
    $camera = $event->getCamera();
    $label = $event->getLabel();
    $zones = $event->getZones();
    $score = $event->getScore();
    $type = $event->getType();
    if ($event->getEndTime() != NULL) {
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);
      $end = date("d-m-Y H:i:s", $event->getEndTime());
    } else {
      $duree = 0;
      $end = date("d-m-Y H:i:s", $event->getStartTime());
    }
    $start = date("d-m-Y H:i:s", $event->getStartTime());
    $time = date("H:i");

    $eqLogic = eqLogic::byId($eqLogicId);
    $actions = $eqLogic->getConfiguration('actions');
    foreach ($actions[0] as $action) {
      $id = str_replace("#", "", $action['cmd']);
      $cmd = cmd::byId($id);
      $cmdLabelName = ($action['cmdLabelName'] == '') ? "all" : $action['cmdLabelName'];
      $cmdTypeName = ($action['cmdTypeName'] == "") ? "end" : $action['cmdTypeName'];
      $options = $action['options'];
      $enable = $action['options']['enable'];
      if ($enable) {
        $options = str_replace(
          ['#time#', '#event_id#', '#camera#', '#score#', '#has_clip#', '#has_snapshot#', '#top_score#', '#zones#', '#snapshot#', '#snapshot_path#', '#clip#', '#clip_path#', '#label#', '#start#', '#end#', '#duree#', '#type#'],
          [$time, $eventId, $camera, $score, $hasClip, $hasSnapshot, $topScore, $zones, $snapshot, $snapshotPath, $clip, $clipPath, $label, $start, $end, $duree, $type],
          $options
        );

        // Exécuter l'action seulement si $start est compris entre l'heure actuelle et -3h
        if ($event->getStartTime() > time() - 10800) {
          // Exécuter l'action seulement si le label correspond
          if ($cmdLabelName == "all" || $cmdLabelName == $label) {
            // Exécuter l'action seulement si le type correspond
            if ($cmdTypeName == "end" || $cmdTypeName == $type) {
              // Chercher les variables spécifiques dans les options
              $optionsJson = json_encode($action['options']);
              if (strpos($optionsJson, '#clip#') !== false || strpos($optionsJson, '#clip_path#') !== false) {
                log::add(__CLASS__, 'debug', "ACTION CLIP");
                if ($hasClip == 1) {
                  log::add(__CLASS__, 'debug', "EXECUTE ACTION CLIP : " . $optionsJson);
                  $cmd->execCmd($options);
                }
              } elseif (strpos($optionsJson, '#snapshot#') !== false || strpos($optionsJson, '#snapshot_path#') !== false) {
                log::add(__CLASS__, 'debug', "ACTION SNAPSHOT");
                if ($hasSnapshot == 1) {
                  log::add(__CLASS__, 'debug', "EXECUTE ACTION SNAPSHOT : " . $optionsJson);
                  $cmd->execCmd($options);
                }
              } else {
                log::add(__CLASS__, 'debug', "AUCUNE VARIABLE");
                $cmd->execCmd($options);
              }
            }
          }
        } else {
          log::add(__CLASS__, 'debug', "Heure dépassée : " . $start);
        }
      } else {
        log::add(__CLASS__, 'debug', "Commande désactivée");
      }
    }
  }

  public static function saveURL($eventId = null, $type = null, $camera, $thumbnail = 0, $latest = 0, $img = "")
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
    $path = "/data/" . $camera . "/" . $eventId . "_" . $type . "." . $format;
    if ($thumbnail == 1) {
      $lien = "http://" . $url . ":" . $port . "/api/events/" . $eventId . "/thumbnail.jpg";
      $path = "/data/" . $camera . "/" . $eventId . "_thumbnail.jpg";
    }
    if ($latest == 1) {
      $lien = $img;
      $path = "/data/" . $camera . "/latest.jpg";
    }

    // Vérifiez si le fichier existe déjà
    if (file_exists($path) && $latest == 0) {
      return $urlJeedom . str_replace("/var/www/html", "", $path);
    }

    // Obtenez le répertoire du chemin de destination
    $destinationDir = dirname(dirname(__FILE__, 3) . $path);

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
      $file = file_put_contents(dirname(__FILE__, 3) . $path, $content);
      if ($file !== false) {
        $result = "/plugins/frigate" . $path;
      } else {
        log::add(__CLASS__, 'debug', "Échec de l'enregistrement du fichier : " . $lien);
        $result = "error";
      }
    } else {
      log::add(__CLASS__, 'debug', "Échec du téléchargement du fichier : " . $lien);
      $result = "error";
    }

    return $result;
  }

  public static function preConfig_topic($value)
  {
    if (self::getTopic() != $value) {
      self::removeMQTTTopicRegistration();
    }
    return $value;
  }

  public static function postConfig_topic($value)
  {
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] === 'ok') {
      self::deamon_start();
    }
  }

  public static function removeMQTTTopicRegistration()
  {
    $topic = self::getTopic();
    log::add(__CLASS__, 'info', "Arrêt de l'écoute du topic Frigate sur mqtt2:'{$topic}'");
    mqtt2::removePluginTopic($topic);
  }

  public static function deamon_start()
  {
    log::add(__CLASS__, 'info', 'deamon_start()');
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }
    // Enregistrement topic frigate
    mqtt2::addPluginTopic(__CLASS__, config::byKey('topic', 'frigate'));
    $mqttInfos = mqtt2::getFormatedInfos();
    log::add(__CLASS__, 'info', '[' . __FUNCTION__ . '] ' . __('Informations reçues de MQTT Manager', __FILE__) . ' : ' . json_encode($mqttInfos));

    return true;
  }

  public static function deamon_stop()
  {
    log::add(__CLASS__, 'info', __('Arrêt du démon Frigate', __FILE__));
    mqtt2::removePluginTopic(config::byKey('frigate', 'frigate'));
  }

  public static function deamon_info()
  {
    $return = array();
    $return['log'] = __CLASS__;
    $return['launchable'] = 'ok';
    $return['state'] = 'nok';

    if (self::isRunning()) {
      $return['state'] = 'ok';
    }

    if (!class_exists('mqtt2')) {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le plugin MQTT Manager n\'est pas installé', __FILE__);
    } else if (mqtt2::deamon_info()['state'] != 'ok') {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le démon MQTT Manager n\'est pas démarré', __FILE__);
    } else if (self::getTopic() == '') {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Topic mqtt pour Frigate non défini.', __FILE__);
    }

    return $return;
  }

  public static function isRunning()
  {
    return true;
  }

  public static function handleMqttMessage($_message)
  {
    //log::add(__CLASS__, 'debug', '***** handle Mqtt Message:' . json_encode($_message));

    if (isset($_message[self::getTopic()])) {
      foreach ($_message[self::getTopic()] as $key => $value) {
        log::add(__CLASS__, 'info', 'handle Mqtt Message pour : ' . $key . ' = ' . json_encode($value));
        if ($key == 'events') {
          log::add(__CLASS__, 'info', ' => Traitement mqtt events');
          $event[] = $value['after'];
          self::getEvents(true, $event, $value['type']);
        } elseif ($key == 'stats') {
          log::add(__CLASS__, 'info', ' => Traitement mqtt stats');
          self::majStatsCmds($value, true);
        } else {
          $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $key, "frigate");
          if (is_object($eqCamera)) {
            log::add(__CLASS__, 'info', ' => Traitement mqtt camera ' . $key);
            foreach ($value as $innerKey => $innerValue) {
              switch ($innerKey) {
                case 'motion':
                  // A venir
                  break;
                case 'audio':
                  break;
                case 'birdeye':
                  // A venir
                  break;
                case 'detect':
                  $infoCmd = self::createCmd($eqCamera->getId(), $innerKey . " Etat", "binary", "", "info_" . $innerKey, "JEEMATE_CAMERA_DETECT_STATE", 0);
                  $value = ($innerValue['state'] == 'ON') ? "1" : "0";
                  $infoCmd->event($value);
                  $infoCmd->save();
                  break;
                case 'improve_constrast':
                  // A venir
                  break;
                case 'motion_contour_area':
                  // A venir
                  break;
                case 'motion_threshold':
                  // A venir
                  break;
                case 'ptz_autotracker':
                  break;
                case 'recordings':
                  $infoCmd = self::createCmd($eqCamera->getId(), $innerKey . " Etat", "binary", "", "info_" . $innerKey, "JEEMATE_CAMERA_NVR_STATE", 0);
                  $value = ($innerValue['state'] == 'ON') ? "1" : "0";
                  $infoCmd->event($value);
                  $infoCmd->save();
                  break;
                case 'snapshots':
                  $infoCmd = self::createCmd($eqCamera->getId(), $innerKey . " Etat", "binary", "", "info_" . $innerKey, "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
                  $value = ($innerValue['state'] == 'ON') ? "1" : "0";
                  $infoCmd->event($value);
                  $infoCmd->save();
                  break;
                default:
                  break;
              }
            }
          }
        }
      }
    }
  }

  public static function setFavorite($eventId, $isFav)
  {
    $events = frigate_events::byEventId($eventId);
    foreach ($events as $event) {
      $event->setIsFavorite($isFav);
      $event->save();
    }
    $state = $event->getIsFavorite();
    return $state;
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
    $camera = $frigate->getConfiguration('name');

    //  CRON START
    if ($this->getLogicalId() == 'action_startCron') {
      $frigate->getCmd(null, 'info_Cron')->event(1);
      log::add(__CLASS__, 'debug', "Cron activé");
    }
    //  CRON STOP
    else if ($this->getLogicalId() == 'action_stopCron') {
      $frigate->getCmd(null, 'info_Cron')->event(0);
      log::add(__CLASS__, 'debug', "Cron désactivé");
    }
    //  RESTART
    else if ($this->getLogicalId() == 'action_restart') {
      frigate::restartFrigate();
    }
    //  AUDIO START
    else if ($this->getLogicalId() == 'action_start_audio') {
      frigate::publish_camera_message($camera, 'audio/set', 'ON');
    }
    //  AUDIO STOP
    else if ($this->getLogicalId() == 'action_stop_audio') {
      frigate::publish_camera_message($camera, 'audio/set', 'OFF');
    }
    //  AUDIO TOGGLE
    else if ($this->getLogicalId() == 'action_toggle_audio') {
      $audio = $frigate->getCmd(null, 'info_audio')->execCmd();
      if ($audio == 1) {
        frigate::publish_camera_message($camera, 'audio/set', 'OFF');
      } else {
        frigate::publish_camera_message($camera, 'audio/set', 'ON');
      }
    }
    //  DETECT START
    else if ($this->getLogicalId() == 'action_start_detect') {
      frigate::publish_camera_message($camera, 'detect/set', 'ON');
    }
    //  DETECT STOP
    else if ($this->getLogicalId() == 'action_stop_detect') {
      frigate::publish_camera_message($camera, 'detect/set', 'OFF');
    }
    //  DETECT TOGGLE
    else if ($this->getLogicalId() == 'action_toggle_detect') {
      $detect = $frigate->getCmd(null, 'info_detect')->execCmd();
      if ($detect == 1) {
        frigate::publish_camera_message($camera, 'detect/set', 'OFF');
      } else {
        frigate::publish_camera_message($camera, 'detect/set', 'ON');
      }
    }
    //  PTZ AUTO TRACKER ON
    else if ($this->getLogicalId() == 'action_start_ptz_autotracker') {
      frigate::publish_camera_message($camera, 'ptz_autotracker/set', 'ON');
    }
    //  PTZ AUTO TRACKER OFF
    else if ($this->getLogicalId() == 'action_stop_ptz_autotracker') {
      frigate::publish_camera_message($camera, 'ptz_autotracker/set', 'OFF');
    }
    //  PTZ AUTO TRACKER TOGGLE
    else if ($this->getLogicalId() == 'action_toggle_ptz_autotracker') {
      $ptz_autotracker = $frigate->getCmd(null, 'info_ptz_autotracker')->execCmd();
      if ($ptz_autotracker == 1) {
        frigate::publish_camera_message($camera, 'ptz_autotracker/set', 'OFF');
      } else {
        frigate::publish_camera_message($camera, 'ptz_autotracker/set', 'ON');
      }
    }
    //  RECORDINGS ON
    else if ($this->getLogicalId() == 'action_start_recordings') {
      frigate::publish_camera_message($camera, 'recordings/set', 'ON');
    }
    //  RECORDINGS OFF
    else if ($this->getLogicalId() == 'action_stop_recordings') {
      frigate::publish_camera_message($camera, 'recordings/set', 'OFF');
    }
    //  RECORDINGS TOGGLE
    else if ($this->getLogicalId() == 'action_toggle_recordings') {
      $recordings = $frigate->getCmd(null, 'info_recordings')->execCmd();
      if ($recordings == 1) {
        frigate::publish_camera_message($camera, 'recordings/set', 'OFF');
      } else {
        frigate::publish_camera_message($camera, 'recordings/set', 'ON');
      }
    }
    //  SNAPSHOTS ON
    else if ($this->getLogicalId() == 'action_start_snapshots') {
      frigate::publish_camera_message($camera, 'snapshots/set', 'ON');
    }
    //  SNAPSHOTS OFF
    else if ($this->getLogicalId() == 'action_stop_snapshots') {
      frigate::publish_camera_message($camera, 'snapshots/set', 'OFF');
    }
    //  SNAPSHOTS TOGGLE
    else if ($this->getLogicalId() == 'action_toggle_snapshots') {
      $snapshots = $frigate->getCmd(null, 'info_snapshots')->execCmd();
      if ($snapshots == 1) {
        frigate::publish_camera_message($camera, 'snapshots/set', 'OFF');
      } else {
        frigate::publish_camera_message($camera, 'snapshots/set', 'ON');
      }
    }
  }

  /*     * **********************Getteur Setteur*************************** */
}
