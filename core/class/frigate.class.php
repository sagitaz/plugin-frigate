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
    if (!config::byKey('URL', 'frigate')) {
      config::save('URL', '127.0.0.1', 'frigate');
    }
    if (!config::byKey('port', 'frigate')) {
      config::save('port', '5000', 'frigate');
    }
    if (!config::byKey('recovery_days', 'frigate')) {
      config::save('recovery_days', '7', 'frigate');
    }
    if (!config::byKey('remove_days', 'frigate')) {
      config::save('remove_days', '7', 'frigate');
    }
    if (!config::byKey('datas_weight', 'frigate')) {
      config::save('datas_weight', '500', 'frigate');
    }
    if (!config::byKey('refresh_snapshot', 'frigate')) {
      config::save('refresh_snapshot', '5', 'frigate');
    }
    if (!config::byKey('cron', 'frigate')) {
      config::save('cron', '5', 'frigate');
    }
    // seulement si mqtt2 est installé
    if (class_exists('mqtt2')) {
      if (!config::byKey('topic', 'frigate')) {
        config::save('topic', 'frigate', 'frigate');
      }
    }
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

    if ($this->getConfiguration('localApiKey') == '') {
      $this->setConfiguration('localApiKey', config::genKey());
    }

    if ($this->getConfiguration('rtsp') == '') {
      $this->setConfiguration('rtsp', 'rtsp://' . $url . ':8554/' . $this->getConfiguration('name'));
    }

    if ($this->getLogicalId() != 'eqFrigateStats' && $this->getLogicalId() != 'eqFrigateEvents') {
      $name = $this->getConfiguration('name');
      $bbox = $this->getConfiguration('bbox', 0);
      $timestamp = $this->getConfiguration('timestamp', 1);
      $zones = $this->getConfiguration('zones', 0);
      $mask = $this->getConfiguration('mask', 0);
      $motion = $this->getConfiguration('motion', 0);
      $regions = $this->getConfiguration('regions', 0);
      $quality = $this->getConfiguration('quality', 70);

      $img = $encoded_url = urlencode("http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?timestamp=" . $timestamp . "&bbox=" . $bbox . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions);
      $this->setConfiguration('img', $img);

      self::createCamerasCmds($this->getId());
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


  // Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard')
  {
    $type = "";
    $logicalId = $this->getLogicalId();
    if (strpos($logicalId, "eqFrigateCamera_") !== false) {
      $type = "camera";
    }

    if ($type == 'camera') {
      $replace = $this->preToHtml($_version);
      if (!is_array($replace)) {
        return $replace;
      }
      $version = jeedom::versionAlias($_version);

      $replace['#cameraEqlogicId#'] = $this->getLogicalId();
      $replace['#cameraName#'] = $this->getConfiguration("name");
      $replace['#imgUrl#'] = $this->getConfiguration("img");
      $replace['#refresh#'] = config::byKey('refresh_snapshot', 'frigate') * 1000;


      $replace['#actions#'] = '';
      // Commandes recording
      if (is_object($this->getCmd('action', 'action_start_recordings')) && is_object($this->getCmd('action', 'action_stop_recordings'))) {
        $on = $this->getCmd("action", 'action_start_recordings');
        $off = $this->getCmd("action", 'action_stop_recordings');
        $etat = $this->getCmd("info", 'info_recordings');
        if ($on->getIsVisible() == 1 && $off->getIsVisible() == 1) {
          if ($etat->execCmd() == 0) {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-video iconActionOff' . $this->getId() . '" title="recording ON" onclick="execAction(' . $on->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          } else {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-video iconAction' . $this->getId() . '" title="recording OFF" onclick="execAction(' . $off->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          }
        }
      }
      // Commandes snapshot
      if (is_object($this->getCmd('action', 'action_start_snapshots')) && is_object($this->getCmd('action', 'action_stop_snapshots'))) {
        $on = $this->getCmd("action", 'action_start_snapshots');
        $off = $this->getCmd("action", 'action_stop_snapshots');
        $etat = $this->getCmd("info", 'info_snapshots');
        if ($on->getIsVisible() == 1 && $off->getIsVisible() == 1) {
          if ($etat->execCmd() == 0) {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-camera iconActionOff' . $this->getId() . '" title="snapshot ON" onclick="execAction(' . $on->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          } else {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-camera iconAction' . $this->getId() . '" title="snapshot OFF" onclick="execAction(' . $off->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          }
        }
      }
      // commandes détections
      if (is_object($this->getCmd('action', 'action_start_detect')) && is_object($this->getCmd('action', 'action_stop_detect'))) {
        $on = $this->getCmd("action", 'action_start_detect');
        $off = $this->getCmd("action", 'action_stop_detect');
        $etat = $this->getCmd("info", 'info_detect');
        if ($on->getIsVisible() == 1 && $off->getIsVisible() == 1) {
          if ($etat->execCmd() == 0) {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-user-shield iconActionOff' . $this->getId() . '" title="detection ON" onclick="execAction(' . $on->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          } else {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-user-shield iconAction' . $this->getId() . '" title="detection OFF" onclick="execAction(' . $off->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          }
        }
      }
      // commantes motions
      $replace['#detectNow#'] = "";
      if (is_object($this->getCmd('info', 'info_detectNow'))) {
        $detectNow = $this->getCmd("info", 'info_detectNow');
        if ($detectNow->getIsVisible() == 1) {
          $value = $detectNow->execCmd();
          if ($value == 1) {
            $replace['#detectNow#'] = $replace['#detectNow#'] . '<div class="btn-detect">';
            $replace['#detectNow#'] = $replace['#detectNow#'] . '<i class="fas fa-user iconDetect' . $this->getId() . '"></i>';
            $replace['#detectNow#'] = $replace['#detectNow#'] . '</div>';
          } else {
            $replace['#detectNow#'] = $replace['#detectNow#'] . '<div class="btn-detect">';
            $replace['#detectNow#'] = $replace['#detectNow#'] . '<i class="fas fa-user-slash iconDetectOff' . $this->getId() . '"></i>';
            $replace['#detectNow#'] = $replace['#detectNow#'] . '</div>';
          }
        }
      }
      if (is_object($this->getCmd('action', 'action_start_motion')) && is_object($this->getCmd('action', 'action_stop_motion'))) {
        $on = $this->getCmd("action", 'action_start_motion');
        $off = $this->getCmd("action", 'action_stop_motion');
        $etat = $this->getCmd("info", 'info_motion');
        if ($on->getIsVisible() == 1 && $off->getIsVisible() == 1) {
          if ($etat->execCmd() == 0) {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-male iconActionOff' . $this->getId() . '" title="motion ON" onclick="execAction(' . $on->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          } else {
            $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
            $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-walking iconAction' . $this->getId() . '" title="motion OFF" onclick="execAction(' . $off->getId() . ')"></i>';
            $replace['#actions#'] = $replace['#actions#'] . '</div>';
          }
        }
      }
      // commandes PTZ down
      if (is_object($this->getCmd('action', 'action_ptz_down'))) {
        $down = $this->getCmd("action", 'action_ptz_down');
        if ($down->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-chevron-circle-down iconActionOff' . $this->getId() . '" title="PTZ DOWN" onclick="execAction(' . $down->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ up
      if (is_object($this->getCmd('action', 'action_ptz_up'))) {
        $up = $this->getCmd("action", 'action_ptz_up');
        if ($up->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-chevron-circle-up iconActionOff' . $this->getId() . '" title="PTZ UP" onclick="execAction(' . $up->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ left
      if (is_object($this->getCmd('action', 'action_ptz_left'))) {
        $left = $this->getCmd("action", 'action_ptz_left');
        if ($left->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-chevron-circle-left iconActionOff' . $this->getId() . '" title="PTZ LEFT" onclick="execAction(' . $left->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ right
      if (is_object($this->getCmd('action', 'action_ptz_right'))) {
        $right = $this->getCmd("action", 'action_ptz_right');
        if ($right->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-chevron-circle-right iconActionOff' . $this->getId() . '" title="PTZ RIGHT" onclick="execAction(' . $right->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ stop
      if (is_object($this->getCmd('action', 'action_ptz_stop'))) {
        $stop = $this->getCmd("action", 'action_ptz_stop');
        if ($stop->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-stop-circle iconActionOff' . $this->getId() . '" title="PTZ STOP" onclick="execAction(' . $stop->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ zoom in
      if (is_object($this->getCmd('action', 'action_ptz_zoom_in'))) {
        $zoom_in = $this->getCmd("action", 'action_ptz_zoom_in');
        if ($zoom_in->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-plus-circle iconActionOff' . $this->getId() . '" title="PTZ ZOOM IN" onclick="execAction(' . $zoom_in->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes PTZ zoom out
      if (is_object($this->getCmd('action', 'action_ptz_zoom_out'))) {
        $zoom_out = $this->getCmd("action", 'action_ptz_zoom_out');
        if ($zoom_out->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-minus-circle iconActionOff' . $this->getId() . '" title="PTZ ZOOM OUT" onclick="execAction(' . $zoom_out->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes make snapshot
      if (is_object($this->getCmd('action', 'action_make_event'))) {
        $make_snapshot = $this->getCmd("action", 'action_make_event');
        if ($make_snapshot->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-camera iconActionOff' . $this->getId() . '" title="Créer event" onclick="execAction(' . $make_snapshot->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }


      $html = $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'widgetCamera', 'frigate')));
      cache::set('widgetCamera' . $_version . $this->getId(), $html, 0);
      return $html;
    } else {
      return parent::toHtml($_version);
    }
  }


  /*     * **********************Getteur Setteur*************************** */
  private static function getTopic()
  {
    return config::byKey('topic', 'frigate');
  }

  public static function getUrlFrigate()
  {
    $url = config::byKey('URL', 'frigate');
    if ($url == "") {
      log::add(__CLASS__, 'debug', "Error: L'URL ne peut être vide.");
      return false;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'debug', "Error: Le port ne peut être vide");
      return false;
    }
    $urlFrigate = $url . ":" . $port;
    return $urlFrigate;
  }

  public static function addMessages()
  {
    message::add('frigate', __("Merci d'avoir installé le plugin. Pour toutes les demandes d'aide, veuillez contacter le support sur Discord ou sur Community.", __FILE__), null, null);
    $system = system::getOsVersion();
    if (version_compare($system, "11", "<")) {
      message::add('frigate', __("Attention, vous utilisez la version " . $system . " de Debian, aucun support n'est disponible. La version 11 de Debian est recommandée.", __FILE__), null, null);
    }
    $jeedom = jeedom::version();
    if (version_compare($jeedom, "4.4", "<")) {
      message::add('frigate', __("Attention, vous utilisez la version " . $jeedom . " de Jeedom. La version 4.4.x de Jeedom est recommandée.", __FILE__), null, null);
    }
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

  private static function getcURL($function, $url, $decodeJson = true, $post = false)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if ($post) {
      curl_setopt($ch, CURLOPT_POST, TRUE);
    }
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, 'debug', "Error:" . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = $decodeJson ? json_decode($data, true) : $data;
    log::add(__CLASS__, 'debug', $function . " : mise à jour.");
    return $response;
  }

  private static function postcURL($function, $url, $decodeJson = true)
  {
    return self::getcURL($function, $url, $decodeJson, true);
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
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";
    $stats = self::getcURL("Stats", $resultURL);
    self::majStatsCmds($stats);
  }

  public static function createEvent($camera, $label)
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/events/" . $camera . "/" . $label . "/create";
    $response = self::postcURL("CreateEvent", $resultURL);

    return $response;
  }

  public static function getLogs($service)
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/logs/" . $service;
    $logs = self::getcURL("Logs", $resultURL, false);

    return $logs;
  }

  public static function checkDatasWeight()
  {
    $recoveryDays = config::byKey('recovery_days', 'frigate'); // default 7
    $removeDays = config::byKey('remove_days', 'frigate'); // default 7
    $datasWeight = config::byKey('datas_weight', 'frigate'); // default 500 Mo
    $folderIsFull = false;
    // Calculer la taille du dossier en octets
    $dir = dirname(__FILE__, 3) . "/data/";
    $size = self::getFolderSize($dir);
    // taille du dossier en Mo
    $folderSizeInMB = round($size / 1024 / 1024, 2);
    // taille disponible du dossier en Mo
    $folderAvailableSizeInMB = round(disk_free_space($dir) / 1024 / 1024, 2);
    // On verifie qu'il y a suffisament de place sur Jeedom
    if ($folderAvailableSizeInMB <= $datasWeight) {
      $folderIsFull = true;
      log::add(__CLASS__, 'debug', "Dossier Jeedom plein, taille : " . $folderSizeInMB . " Mo");
    } else {
      log::add(__CLASS__, 'debug', "Dossier Jeedom OK, taille : " . $folderSizeInMB . " Mo");
      // On verifie que le dossier n'est pas plein
      if ($folderSizeInMB <= $datasWeight) {
        log::add(__CLASS__, 'debug', "Dossier data Frigate OK, taille : " . $folderSizeInMB . " Mo");
      } else {
        log::add(__CLASS__, 'debug', "Dossier data Frigate plein, taille : " . $folderSizeInMB . " Mo");
        $folderIsFull = true;
      }
    }
    // Si dossier plein alors on reduit le nombre de jours de recuperation automatiquement et de suppression
    if ($folderIsFull) {
      log::add(__CLASS__, 'debug', "Dossier plein, taille : " . $folderSizeInMB . " Mo, on réduit le nombre de jours de récupération automatiquement");
      message::add('frigate', __("Dossier plein, taille : " . $folderSizeInMB . " Mo, on réduit le nombre de jours de récupération automatiquement", __FILE__), null, null);
      // on reduit le nombre de jours de recuperation automatiquement
      $recoveryDays = $recoveryDays - 1;
      config::save('recovery_days', $recoveryDays, 'frigate');
      // on reduit le nombre de jours de suppression automatiquement
      $removeDays = $removeDays - 1;
      if ($removeDays < 1) {
        $removeDays = 1;
        log::add(__CLASS__, 'debug', "Vous êtes au nombre de jours minimum de suppression : " . $removeDays . " jours");
      } else {
        config::save('remove_days', $removeDays, 'frigate');
        log::add(__CLASS__, 'debug', "Durée de recuperation : " . $recoveryDays . " jours, Durée de suppression : " . $removeDays . " jours");
      }
    }
    return true;
  }

  public static function getFolderSize($folder)
  {
    $size = 0;
    // Parcourt récursivement tous les fichiers et dossiers
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) { // Exclure les sous-dossiers "snapshots" et "clips"
      if (strpos($file->getPath(), $folder . DIRECTORY_SEPARATOR . 'snapshots') === 0 || strpos($file->getPath(), $folder . DIRECTORY_SEPARATOR . 'clips') === 0) {
        continue;
      }
      $size += $file->getSize(); // Ajoute la taille du fichier
    }
    $size = round($size / 1024 / 1024, 2);
    return $size;
  }

  public static function deleteOldestFiles($folder, $tailleMax)
  {
    $favoris = array();
    $folderSize = self::getFolderSize($folder);

    while ($folderSize > $tailleMax) {
      log::add(__CLASS__, 'debug', "Le dossier data fait actuellement " . $folderSize . "Mo sur les " . $tailleMax . "Mo permis");
      $oldestFile = null;
      $oldestTime = time();

      // Parcourt récursivement tous les fichiers et dossiers
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        // Exclure les sous-dossiers "snapshots" et "clips"
        if (strpos($file->getPath(), $folder . DIRECTORY_SEPARATOR . 'snapshots') === 0 || strpos($file->getPath(), $folder . DIRECTORY_SEPARATOR . 'clips') === 0) {
          continue;
        }

        if ($file->isFile() && strpos($file->getPath(), $folder . DIRECTORY_SEPARATOR) === 0 && $file->getPath() !== $folder) {
          $id = self::extractID($file->getFilename());
          // Vérifie que le fichier n'est pas dans le tableau favoris
          if (in_array($id, $favoris)) {
            continue;
          }
          // Vérifie si c'est un fichier et s'il est plus ancien
          if ($file->getMTime() < $oldestTime) {
            $oldestFile = $file;
            $oldestTime = $file->getMTime();
          }
        }
      }

      if ($oldestFile !== null) {
        $id = self::extractID($oldestFile->getFilename());
        log::add(__CLASS__, 'debug', "delete in DB ID: " . $id);
        $delete = self::removeFile($id);
        if ($delete) {
          // Mettre à jour la taille du dossier
          $folderSize = self::getFolderSize($folder);
          continue; // Continue la boucle pour vérifier la taille à nouveau
        } else {
          $favoris[] = $id;
          continue;
        }
      } else {
        // Si aucun fichier n'est trouvé, sortir de la boucle pour éviter une boucle infinie
        break;
      }
    }
  }


  public static function removeFile($id)
  {
    $event = frigate_events::byEventId($id);
    if (!empty($event) && isset($event[0])) {
      // Verifier si le fichier est un favoris
      $isFavorite = $event[0]->getIsFavorite() ?? 0;
      if ($isFavorite == 1) {
        log::add(__CLASS__, 'debug', "Evènement " . $id . " est un favori, il ne doit pas être supprimé de la DB.");
        return false;
      } else {
        log::add(__CLASS__, 'debug', "delete in DB : " . $id);
        $event[0]->remove();
        log::add(__CLASS__, 'debug', "Events delete in DB");
        // Recherche si clip et snapshot existent dans le dossier de sauvegarde
        $clip = dirname(__FILE__, 3) . "/data/" . $event[0]->getCamera() . "/" . $id . "_clip.mp4";
        $snapshot = dirname(__FILE__, 3) . "/data/" . $event[0]->getCamera() . "/" . $id . "_snapshot.jpg";
        $thumbnail = dirname(__FILE__, 3) . "/data/" . $event[0]->getCamera() . "/" . $id . "_thumbnail.jpg";

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
        return true;
      }
    } else {
      return false;
    }
  }

  public static function extractID($filename)
  {
    // Utiliser une expression régulière pour extraire l'ID du nom de fichier
    if (preg_match('/^\d+\.\d+-[a-z0-9]+/', $filename, $matches)) {
      return $matches[0];
    }
    return null;
  }

  public static function getEvents($mqtt = false, $events = array(), $type = 'end')
  {
    if (!$mqtt) {
      $urlfrigate = self::getUrlFrigate();
      $resultURL = $urlfrigate . "/api/events";
      $events = self::getcURL("Events", $resultURL);
      // Traiter les evenements du plus ancien au plus recent
      $events = array_reverse($events);
    }

    // Nombre de jours a filtrer et enregistrer en DB
    $recoveryDays = config::byKey('recovery_days', 'frigate');
    if (empty($recoveryDays)) {
      $recoveryDays = 7;
    }

    $filteredRecoveryEvents = array_filter($events, function ($event) use ($recoveryDays) {
      return $event['start_time'] >= time() - $recoveryDays * 86400;
    });
    $filteredRecoveryEvents = array_values($filteredRecoveryEvents);

    foreach ($filteredRecoveryEvents as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      log::add(__CLASS__, 'debug', "New Events (type=" . $type . ") => " . json_encode($event));

      $infos = self::getEventinfos($mqtt, $event);

      if (!$frigate) {
        log::add(__CLASS__, 'debug', "Creating new frigate event for event ID: " . $event['id']);
        $frigate = new frigate_events();
        $frigate->setBox($event['box']);
        $frigate->setCamera($event['camera']);
        $frigate->setData($event['data']);
        $frigate->setLasted($infos["image"]);
        $frigate->setHasClip($infos["hasClip"]);
        $frigate->setClip($infos["clip"]);
        $frigate->setHasSnapshot($infos["hasSnapshot"]);
        $frigate->setSnapshot($infos["snapshot"]);
        $frigate->setStartTime($infos['startTime']);
        $frigate->setEndTime($infos["endTime"]);
        $frigate->setFalsePositive($event['false_positive']);
        $frigate->setEventId($event['id']);
        $frigate->setLabel($event['label']);
        $frigate->setPlusId($event['plus_id']);
        $frigate->setRetain($event['retain_indefinitely']);
        $frigate->setSubLabel($event['sub_label']);
        $frigate->setThumbnail($infos["thumbnail"]);
        $frigate->setTopScore($infos["topScore"]);
        $frigate->setScore($infos["score"]);
        $frigate->setZones($event['zones']);
        $frigate->setType($type);
        $frigate->setIsFavorite(0);
        $frigate->save();
        self::majEventsCmds($frigate);
        log::add(__CLASS__, 'debug', "Frigate event created and saved for event ID: " . $event['id']);
      } else {
        log::add(__CLASS__, 'debug', "Updating existing frigate event for event ID: " . $event['id']);

        if (is_array($frigate) && !empty($frigate)) {
          $frigate = $frigate[0];
        }

        $updated = false;

        $fieldsToUpdate = [
          'EndTime' => $infos["endTime"],
          'HasClip' => $infos["hasClip"],
          'Clip' => $infos["clip"],
          'HasSnapshot' => $infos["hasSnapshot"],
          'Snapshot' => $infos["snapshot"],
          'Box' => $event['box'],
          'Camera' => $event['camera'],
          'Data' => $event['data'],
          'FalsePositive' => $event['false_positive'],
          'Label' => $event['label'],
          'PlusId' => $event['plus_id'],
          'SubLabel' => $event['sub_label'],
          'Thumbnail' => $infos["thumbnail"],
          'Type' => $type,
          'TopScore' => $infos["topScore"],
          'Score' => $infos["score"]
        ];

        foreach ($fieldsToUpdate as $field => $value) {
          $getMethod = 'get' . $field;
          $setMethod = 'set' . $field;
          $currentValue = $frigate->$getMethod();

          // Si les deux valeurs sont des chaînes JSON, les décoder avant de les comparer
          if (is_string($currentValue) && is_string($value)) {
            $decodedCurrentValue = json_decode($currentValue, true);
            $decodedValue = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && $decodedCurrentValue === $decodedValue) {
              continue; // Les valeurs sont identiques après décodage
            }
          }

          if ((is_null($currentValue) || $currentValue === '' || $currentValue != $value) && !is_null($value) && $value !== '') {
            log::add(__CLASS__, 'debug', "Updating field '$field' for event ID: " . $event['id'] . ". Old Value: " . json_encode($currentValue) . ", New Value: " . json_encode($value));
            $frigate->$setMethod($value);
            $updated = true;
          }
        }

        if ($updated) {
          $frigate->save();
          self::majEventsCmds($frigate);
          log::add(__CLASS__, 'debug', "Frigate event updated and saved for event ID: " . $event['id']);
        } else {
          log::add(__CLASS__, 'debug', "No changes detected for event ID: " . $event['id']);
        }
      }
    }
  }

  public static function getEventinfos($mqtt, $event)
  {
    // Avant, on verifie la taille disponible dans le dossier data
    $tailleMax = config::byKey('datas_weight', 'frigate'); // default 500 Mo
    $dir = dirname(__FILE__, 3) . "/data";
    $size = self::getFolderSize($dir);
    //  log::add(__CLASS__, 'debug', "Le dossier data fait actuellement " . $size . "Mo sur les " . $tailleMax . "Mo permis");
    if ($size > $tailleMax) {
      self::deleteOldestFiles($dir, $tailleMax);
    }


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
      sleep(2);
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

    if (!$mqtt) {
      $newTopScore = round($event['data']['top_score'] * 100, 0);
      $newScore = round($event['data']['score'] * 100, 0);
    } else {
      $newTopScore = round($event['top_score'] * 100, 0);
      $newScore = round($event['score'] * 100, 0);
    }

    $infos = array(
      "image" => $img,
      "thumbnail" => $img,
      "snapshot" => $snapshot,
      "hasSnapshot" => $hasSnapshot,
      "clip" => $clip,
      "hasClip" => $hasClip,
      "startTime" => ceil($event['start_time']) > 0 ? ceil($event['start_time']) : $event['start_time'],
      "endTime" => ceil($endTime) > 0 ? ceil($endTime) : $endTime,
      "topScore" => $newTopScore,
      "score" => $newScore
    );

    return $infos;
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
        $isFavorite = $inDbEvent->getIsFavorite() ?? 0;
        if ($isFavorite == 1) {
          log::add(__CLASS__, 'debug', "Evènement " . $inDbEvent->getEventId() . " est un favori, il ne doit pas être supprimé de la DB.");
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
    if (!empty($frigate) && isset($frigate[0])) {
      $isFavorite = $frigate[0]->getIsFavorite() ?? 0;
      if ($isFavorite == 1) {
        log::add(__CLASS__, 'debug', "Evènement " . $frigate[0]->getEventId() . " est un favori, il ne doit pas être supprimé de la DB.");
        message::add('frigate', __("L'évènement est un favori, il ne peut pas être supprimé de la DB.", __FILE__), null, null);
        return "Error 01";
      }

      $urlfrigate = self::getUrlFrigate();
      $resultURL = $urlfrigate . "/api/events/" . $id;

      if ($all) {
        self::deletecURL($resultURL);
      }

      $events = frigate_events::byEventId($id);
      foreach ($events as $event) {
        $event->remove();
      }
      return "OK";
    } else {
      return "Error 02";
    }
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
        "isFavorite" => $event->getIsFavorite() ?? 0
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
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";

    $exist = 0;
    $addToName = "";
    $create = 1;
    $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;
    log::add(__CLASS__, 'debug', "Liste des caméras : " . json_encode($stats['cameras']));

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
        log::add(__CLASS__, 'debug', "L'équipement : " . json_encode($cameraName) . " existe dans la pièce : " . jeeObject::byId($defaultRoom)->getName());
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

  public static function createCamerasCmds($eqlogicId)
  {

    $cmd = self::createCmd($eqlogicId, "Créer un évenement via API", "other", "", "action_make_api_event", "", 1, null, 0, "action");
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "Créer une évenement", "other", "", "action_make_event", "", 1, null, 0, "action");
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

    $infoCmd = self::createCmd($eqlogicId, "détection en cours", "binary", "", "info_detectNow", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    $infoCmd = self::createCmd($eqlogicId, "motion Etat", "binary", "", "info_motion", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    // commande action
    $cmd = self::createCmd($eqlogicId, "motion off", "other", "", "action_stop_motion", "JEEMATE_CAMERA_SNAPSHOT_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "motion on", "other", "", "action_start_motion", "JEEMATE_CAMERA_SNAPSHOT_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "motion toggle", "other", "", "action_toggle_motion", "JEEMATE_CAMERA_SNAPSHOT_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();
  }

  public static function createPTZcmds($eqlogicId)
  {
    // commande action
    $cmd = self::createCmd($eqlogicId, "PTZ move left", "other", "", "action_ptz_left", "CAMERA_LEFT", 1, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ move right", "other", "", "action_ptz_right", "CAMERA_RIGHT", 1, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ move up", "other", "", "action_ptz_up", "CAMERA_UP", 0, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ move down", "other", "", "action_ptz_down", "CAMERA_DOWN", 1, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ move stop", "other", "", "action_ptz_stop", "CAMERA_STOP", 1, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ zoom in", "other", "", "action_ptz_zoom_in", "CAMERA_ZOOM", 0, "", 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "PTZ zoom out", "other", "", "action_ptz_zoom_out", "CAMERA_DEZOOM", 0, "", 0, "action");
    $cmd->save();

    return true;
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
    log::add(__CLASS__, 'debug', "maj events cmds ### 01#");
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
      log::add(__CLASS__, 'debug', "maj events cmds ### 02#");
    } else {
      self::executeActionNewEvent($frigate->getId(), $event);
      log::add(__CLASS__, 'debug', "maj events cmds ### 02bis#");
    }

    log::add(__CLASS__, 'debug', "maj events cmds ### 03#");
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


      $cmd = self::createCmd($eqlogicId, "URL snapshot", "string", "", "info_url_snapshot", "GENERIC_INFO");
      $cmd->event($event->getSnapshot());
      $cmd->save();


      $cmd = self::createCmd($eqlogicId, "URL clip", "string", "", "info_url_clip", "GENERIC_INFO");
      $cmd->event($event->getClip());
      $cmd->save();


      $cmd = self::createCmd($eqlogicId, "URL thumbnail", "string", "", "info_url_thumbnail", "GENERIC_INFO");
      $cmd->event($event->getThumbnail());
      $cmd->save();
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
          // Créer ou récupérer la commande
          $cmd = self::createCmd($eqlogicCameraId, $key, "numeric", "", "cameras_" . $key, "GENERIC_INFO");
          // Enregistrer la valeur de l'événement
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
        // Créer un nom de commande en combinant le nom du détecteur et la clé
        $cmdName = $detectorName . '_' . $key;
        // Créer ou récupérer la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "detectors_" . $key, "GENERIC_INFO");
        // Enregistrer la valeur de l'évènement
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
        // Créer un nom de commande en combinant le nom du GPU et la clé
        $cmdName = $gpuName . '_' . $key;
        // Créer ou récupérer la commande
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "gpu_" . $key, "GENERIC_INFO");
        // Enregistrer la valeur de l'événement
        $cmd->event($value);
        $cmd->save();
      }
    }

    // Créer ou récupérer la commande version Frigate
    $version = strstr($stats['service']['version'], '-', true);
    $latestVersion = $stats['service']['latest_version'];
    if (version_compare($version, $latestVersion, "<")) {
      message::add('frigate', __("Une nouvelle version de Frigate (" . $latestVersion . ") est disponible.", __FILE__), null, null);
    }
    $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "GENERIC_INFO");
    // Enregistrer la valeur de l'événement
    $cmd->event($version);
    $cmd->save();
  }

  private static function executeActionNewEvent($eqLogicId, $event)
  {
    log::add(__CLASS__, 'debug', "execute action new event ### 01");
    // Récupération des URLs externes et internes
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }

    // Initialisation des variables d'événement
    $eventId = $event->getEventId();
    $hasClip = $event->getHasClip();
    $hasSnapshot = $event->getHasSnapshot();
    $topScore = $event->getTopScore();
    $clip = $urlJeedom . $event->getClip();
    $snapshot = $urlJeedom . $event->getSnapshot();
    $thumbnail = $urlJeedom . $event->getThumbnail();
    $clipPath = "/var/www/html" . $event->getClip();
    $snapshotPath = "/var/www/html" . $event->getSnapshot();
    $thumbnailPath = "/var/www/html" . $event->getThumbnail();
    $camera = $event->getCamera();
    $label = $event->getLabel();
    $zones = $event->getZones();
    $score = $event->getScore();
    $type = $event->getType();
    $start = date("d-m-Y H:i:s", $event->getStartTime());
    $end = $event->getEndTime() ? date("d-m-Y H:i:s", $event->getEndTime()) : $start;
    $duree = $event->getEndTime() ? round($event->getEndTime() - $event->getStartTime(), 0) : 0;
    $time = date("H:i");

    $eqLogic = eqLogic::byId($eqLogicId);

    // Vérification de la condition d'exécution
    $conditionIf = $eqLogic->getConfiguration('conditionIf');
    if ($conditionIf && jeedom::evaluateExpression($conditionIf)) {
      log::add(__CLASS__, 'info', $eqLogic->getHumanName() . ' : actions non exécutées car ' . $conditionIf . ' est vrai.');
      return;
    }

    $actions = $eqLogic->getConfiguration('actions')[0];

    log::add(__CLASS__, 'warning', "execute action " . $eqLogic->getName() . " ### 01 " . json_encode($actions));

    foreach ($actions as $action) {
      $id = str_replace("#", "", $action['cmd']);
      $cmd = cmd::byId($id);
      $cmdLabelName = $action['cmdLabelName'] ?: "all";
      $cmdTypeName = $action['cmdTypeName'] ?: "end";
      $options = $action['options'];
      $enable = $action['options']['enable'] ?? false;

      if (!$enable) {
        log::add(__CLASS__, 'debug', "Commande désactivée");
        continue;
      }

      $options = str_replace(
        ['#time#', '#event_id#', '#camera#', '#score#', '#has_clip#', '#has_snapshot#', '#top_score#', '#zones#', '#snapshot#', '#snapshot_path#', '#clip#', '#clip_path#', '#thumbnail#', '#thumbnail_path#', '#label#', '#start#', '#end#', '#duree#', '#type#'],
        [$time, $eventId, $camera, $score, $hasClip, $hasSnapshot, $topScore, $zones, $snapshot, $snapshotPath, $clip, $clipPath, $thumbnail, $thumbnailPath, $label, $start, $end, $duree, $type],
        $options
      );

      // Vérifier les conditions de temps et de label/type
      // Exécuter l'action seulement si $start est compris entre l'heure actuelle et -3h
      if ($event->getStartTime() <= time() - 10800) {
        log::add(__CLASS__, 'debug', "Heure dépassée : " . $start);
        continue;
      }

      // Exécuter l'action seulement si le label correspond
      if ($cmdLabelName !== "all" && $cmdLabelName !== $label) {
        continue;
      }

      log::add(__CLASS__, 'debug', "execute action new event ### 02 = "  . $cmdLabelName);
      // Exécuter l'action seulement si le type correspond
      if ($cmdTypeName !== "end" && $cmdTypeName !== $type) {
        continue;
      }

      log::add(__CLASS__, 'debug', "execute action new event ### 03" . $cmdTypeName);
      // Exécuter l'action selon le contenu des options
      $optionsJson = json_encode($action['options']);
      if (strpos($optionsJson, '#clip#') !== false || strpos($optionsJson, '#clip_path#') !== false) {
        if ($hasClip == 1) {
          log::add(__CLASS__, 'debug', "ACTION CLIP : " . $optionsJson);
          $cmd->execCmd($options);
        }
      } elseif (strpos($optionsJson, '#snapshot#') !== false || strpos($optionsJson, '#snapshot_path#') !== false) {
        if ($hasSnapshot == 1) {
          log::add(__CLASS__, 'debug', "ACTION SNAPSHOT : " . $optionsJson);
          $cmd->execCmd($options);
        }
      } else {
        log::add(__CLASS__, 'debug', "AUCUNE VARIABLE");
        $cmd->execCmd($options);
      }
    }
  }

  public static function saveURL($eventId = null, $type = null, $camera, $mode = 0, $file = "")
  {
    // mode de fonctionnement : 0 = defaut, 1 = thumbnail, 2 = latest, 3 = snapshot, 4 = clip
    $result = "";
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }
    $urlfrigate = self::getUrlFrigate();
    $format = ($type == "snapshot") ? "jpg" : "mp4";
    $lien = "http://" . $urlfrigate . "/api/events/" . $eventId . "/" . $type . "." . $format;
    $path = "/data/" . $camera . "/" . $eventId . "_" . $type . "." . $format;
    if ($mode == 1) {
      $lien = "http://" . $urlfrigate . "/api/events/" . $eventId . "/thumbnail.jpg";
      $path = "/data/" . $camera . "/" . $eventId . "_thumbnail.jpg";
    } elseif ($mode == 2) {
      $lien = $file;
      $path = "/data/" . $camera . "/latest.jpg";
    } elseif ($mode == 3) {
      $lien = urldecode($file);
      $path = "/data/" . $camera . "/" . $eventId . "_snapshot.jpg";
    } elseif ($mode == 4) {
      log::add(__CLASS__, 'debug', "RTSP = " . $file);
      $path = "/data/" . $camera . "/" . $eventId . "_clip.mp4";
      $newpath = dirname(__FILE__, 3) . $path;
      log::add(__CLASS__, 'debug', "Path = " . $newpath);
      // clip creator
      $output = [];
      $return_var = 0;
      $cmd = 'ffmpeg -rtsp_transport tcp -loglevel fatal -i "' . $file . '" -c:v copy -bsf:a aac_adtstoasc -y -t 10 -movflags faststart ' . $newpath;
      exec($cmd, $output, $return_var);
      $result = "/plugins/frigate" . $path;
      log::add(__CLASS__, 'debug', "Commande exécutée : " . $cmd);
      log::add(__CLASS__, 'debug', "Sortie : " . implode("\n", $output));
      log::add(__CLASS__, 'debug', "Code de retour : " . $return_var);
      return $result;
    }

    // Vérifier si le fichier existe déjà
    if (file_exists($path) && $mode != 2) {
      return $urlJeedom . str_replace("/var/www/html", "", $path);
    }

    // Obtenir le répertoire du chemin de destination
    $destinationDir = dirname(dirname(__FILE__, 3) . $path);

    // Vérifier si le répertoire existe, sinon le créer
    if (!is_dir($destinationDir)) {
      if (!mkdir($destinationDir, 0755, true)) {
        log::add(__CLASS__, 'debug', "Échec de la création du répertoire.");
        return $result;
      }
    }

    // Télécharger l'image ou la vidéo
    $content = file_get_contents($lien);

    if ($content !== false) {
      // Enregistrer l'image ou la vidéo dans le dossier spécifié
      $file = file_put_contents(dirname(__FILE__, 3) . $path, $content);
      if ($file !== false) {
        $result = "/plugins/frigate" . $path;
        log::add(__CLASS__, 'debug', "Le fichier a été enregistré : " . $lien);
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

  public static function createSnapshot($eqLogic)
  {
    //$frigate = eqLogic::byId($eqLogicId);
    $camera = $eqLogic->getConfiguration('name');
    $file = $eqLogic->getConfiguration('img');
    $rtsp = $eqLogic->getConfiguration('rtsp');
    $timestamp = microtime(true);
    $formattedTimestamp = sprintf('%.6f', $timestamp);
    $startTime = time();
    $endTime = $startTime + 10;
    $uniqueId = self::createUniqueId($formattedTimestamp);
    // create snapshot
    $url = frigate::saveURL($uniqueId, null, $camera, 3, $file);
    // create clip
    $urlClip = frigate::saveURL($uniqueId, null, $camera, 4, $rtsp);
    // mise a jour des commandes
    $eqLogic->getCmd(null, 'info_url_snapshot')->event($url);
    $eqLogic->getCmd(null, 'info_url_clip')->event($urlClip);
    $eqLogic->getCmd(null, 'info_url_thumbnail')->event($url);
    $eqLogic->getCmd(null, 'info_timestamp')->event($startTime);
    $eqLogic->getCmd(null, 'info_label')->event("manuel");
    $eqLogic->getCmd(null, 'info_score')->event(0);
    $eqLogic->getCmd(null, 'info_topscore')->event(0);
    $eqLogic->getCmd(null, 'info_duree')->event(10);

    // Creation de l'evenement  dans la DB
    log::add(__CLASS__, 'debug', "Creating new frigate event for event ID: " . $uniqueId);
    $frigate = new frigate_events();
    $frigate->setCamera($camera);
    $frigate->setLasted($url);
    $frigate->setHasClip(1);
    $frigate->setClip($urlClip);
    $frigate->setHasSnapshot(1);
    $frigate->setSnapshot($url);
    $frigate->setStartTime($startTime);
    $frigate->setEndTime($endTime);
    $frigate->setEventId($timestamp);
    $frigate->setLabel("manuel");
    $frigate->setThumbnail($url);
    $frigate->setTopScore(0);
    $frigate->setScore(0);
    $frigate->setType("end");
    $frigate->setIsFavorite(0);
    $frigate->save();
  }

  public static function createUniqueId($timestamp)
  {
    // Generate a random string of 6 characters
    $randomStr = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
    // Combine the timestamp and random string
    $uniqueId = $timestamp . '-' . $randomStr;

    return $uniqueId;
  }

  public static function getConfig()
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config";
    $config = self::getcURL("Configuration", $resultURL);
    if ($config === false) {
      log::add(__CLASS__, 'debug', "Configuration non créée");
    } else {
      log::add(__CLASS__, 'debug', "Configuration : " . json_encode($config));
    }

    return $config;
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
    if (class_exists('mqtt2')) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] === 'ok') {
        self::deamon_start();
      }
    }
  }

  public static function removeMQTTTopicRegistration()
  {
    $topic = self::getTopic();
    if (class_exists('mqtt2')) {
      log::add(__CLASS__, 'info', "Arrêt de l'écoute du topic Frigate sur mqtt2:'{$topic}'");
      mqtt2::removePluginTopic($topic);
    }
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
    if (class_exists('mqtt2')) {
      log::add(__CLASS__, 'info', __('Arrêt du démon Frigate', __FILE__));
      mqtt2::removePluginTopic(config::byKey('frigate', 'frigate'));
    }
  }

  public static function deamon_info()
  {
    $return = [
      'log' => __CLASS__,
      'launchable' => 'ok',
      'state' => self::isRunning() ? 'ok' : 'nok'
    ];

    if (!class_exists('mqtt2')) {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le plugin MQTT Manager n\'est pas installé', __FILE__);
    } elseif (mqtt2::deamon_info()['state'] != 'ok') {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le démon MQTT Manager n\'est pas démarré', __FILE__);
    } elseif (self::getTopic() == '') {
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
    if (!isset($_message[self::getTopic()])) {
      return;
    }

    foreach ($_message[self::getTopic()] as $key => $value) {
      //    log::add(__CLASS__, 'info', 'handle Mqtt Message pour : ' . $key . ' = ' . json_encode($value));

      switch ($key) {
        case 'events':
          log::add(__CLASS__, 'info', ' => Traitement mqtt events');
          self::getEvents(true, [$value['after']], $value['type']);
          break;

        case 'stats':
          log::add(__CLASS__, 'info', ' => Traitement mqtt stats');
          self::majStatsCmds($value, true);
          break;

        default:
          $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $key, "frigate");
          if (!is_object($eqCamera)) {
            continue;
          }

          log::add(__CLASS__, 'info', ' => Traitement mqtt camera ' . $key);
          self::processCameraData($eqCamera, $key, $value);
          break;
      }
    }
  }

  private static function processCameraData($eqCamera, $key, $data)
  {
    foreach ($data as $innerKey => $innerValue) {
      if (in_array($innerKey, ['audio', 'birdeye', 'improve_constrast', 'motion_contour_area', 'motion_threshold', 'ptz_autotracker'])) {
        // A venir
        continue;
      }

      switch ($innerKey) {
        case 'motion':
          self::handleMotion($eqCamera, $key, $innerValue);
          break;

        case 'detect':
          self::updateCameraState($eqCamera, $innerKey, $innerValue['state'], "JEEMATE_CAMERA_DETECT_STATE");
          break;

        case 'recordings':
          self::updateCameraState($eqCamera, $innerKey, $innerValue['state'], "JEEMATE_CAMERA_NVR_STATE");
          break;

        case 'snapshots':
          self::updateCameraState($eqCamera, $innerKey, $innerValue['state'], "JEEMATE_CAMERA_SNAPSHOT_STATE");
          break;
      }
    }
  }

  private static function handleMotion($eqCamera, $key, $innerValue)
  {
    if (isset($innerValue['state']) && $innerValue['state']) {
      $state = ($innerValue['state'] == 'ON') ? "1" : "0";
      // log::add(__CLASS__, 'info', $key . ' => Valeur motion state : ' . $state);
      $infoCmd = self::createCmd($eqCamera->getId(), 'motion Etat', 'binary', '', 'info_motion', 'JEEMATE_CAMERA_DETECT_STATE', 0);
      $infoCmd->event($state);
      $infoCmd->save();
      $eqCamera->refreshWidget();
    }

    if (isset($innerValue) && !is_array($innerValue)) {
      $state = ($innerValue == 'ON') ? "1" : "0";
      //   log::add(__CLASS__, 'info', $key . ' => Valeur motion : ' . $state);
      $infoCmd = self::createCmd($eqCamera->getId(), 'détection en cours', 'binary', '', 'info_detectNow', 'JEEMATE_CAMERA_SNAPSHOT_STATE', 0);
      $infoCmd->event($state);
      $infoCmd->save();
      $eqCamera->refreshWidget();
    }
  }

  private static function updateCameraState($eqCamera, $type, $state, $jeemateState)
  {
    $stateValue = ($state == 'ON') ? "1" : "0";
    $infoCmd = self::createCmd($eqCamera->getId(), $type . " Etat", "binary", "", "info_" . $type, $jeemateState, 0);
    $infoCmd->event($stateValue);
    $infoCmd->save();
    $eqCamera->refreshWidget();
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


  public static function deleteLatestFile()
  {
    $folder = dirname(__FILE__, 3) . "/data/";
    $fileName = "latest.jpg";
    // Parcourt récursivement tous les fichiers et dossiers
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
      if ($file->isFile() && $file->getFilename() === $fileName) {
        // Supprime le fichier
        unlink($file->getPathname());
        log::add(__CLASS__, 'debug', "Deleted file: " . $file->getPathname());
      }
    }
  }

  public static function backupExclude()
  {
    // retourne le répertoire de sauvegarde des snapshots et des vidéos des events à ne pas enregistrer dans le backup Jeedom
    return ['data'];
  }


  public static function getFrigateConfiguration()
  {
    log::add(__CLASS__, 'info', "getFrigateConfiguration");

    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config/raw";

    $ch = curl_init($resultURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    $curlResponse = curl_exec($ch);

    if ($curlResponse === false) {
      $error = 'Erreur cURL : ' . curl_error($ch);
      curl_close($ch);
      log::add(__CLASS__, 'error', 'getFrigateConfiguration :: ' . $error);
      $response = array(
        'status' => 'error',
        'message' => $error
      );

      return $response;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
      $error = 'Erreur : Impossible de récupérer la configuration. Code de statut : ' . $httpCode;
      curl_close($ch);
      log::add(__CLASS__, 'error', 'getFrigateConfiguration :: ' . $error);
      $response = array(
        'status' => 'error',
        'message' => $error
      );

      return $response;
    }

    curl_close($ch);

    //log::add(__CLASS__, 'info', "getFrigateConfiguration:: save config file");
    //$file = file_put_contents(dirname(__FILE__, 3) . '/data/config.yaml', $curlResponse);

    $response = array(
      'status' => 'success',
      'message' => $curlResponse
    );

    return $response;
  }

  public static function sendFrigateConfiguration($frigateConfiguration, $restart = false)
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config/save" . ($restart ? '?save_option=restart' : '');

    $ch = curl_init($resultURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-yaml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $frigateConfiguration);

    log::add(__CLASS__, 'debug', 'sendFrigateConfiguration :: Data : ' . $frigateConfiguration);
    $curlResponse = curl_exec($ch);

    if ($curlResponse === false) {
      $error = 'Erreur cURL : ' . curl_error($ch);
      curl_close($ch);
      log::add(__CLASS__, 'error', 'sendFrigateConfiguration :: ' . $error);
      $response = array(
        'status' => 'error',
        'message' => $error
      );

      return $response;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
      $error = 'Erreur : Impossible de sauvegarder la configuration. Code de statut : ' . $httpCode;
      curl_close($ch);
      log::add(__CLASS__, 'error', 'sendFrigateConfiguration :: ' . $error);
      $response = array(
        'status' => 'error',
        'message' => $error
      );

      return $response;
    }

    curl_close($ch);
    $response = array(
      'status' => 'success',
      'message' => $curlResponse
    );
    return $response;
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
    $file = $frigate->getConfiguration('img');
    $logicalId = $this->getLogicalId();

    switch ($logicalId) {
      case 'action_startCron':
        $this->updateCronStatus($frigate, 1, "Cron activé");
        break;
      case 'action_stopCron':
        $this->updateCronStatus($frigate, 0, "Cron désactivé");
        break;
      case 'action_restart':
        frigate::restartFrigate();
        break;
      case 'action_start_audio':
      case 'action_stop_audio':
        $this->publishCameraMessage($camera, 'audio/set', $logicalId === 'action_start_audio' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_audio':
        $this->toggleCameraSetting($frigate, $camera, 'info_audio', 'audio/set');
        break;
      case 'action_start_detect':
      case 'action_stop_detect':
        $this->publishCameraMessage($camera, 'detect/set', $logicalId === 'action_start_detect' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_detect':
        $this->toggleCameraSetting($frigate, $camera, 'info_detect', 'detect/set');
        break;
      case 'action_start_ptz_autotracker':
      case 'action_stop_ptz_autotracker':
        $this->publishCameraMessage($camera, 'ptz_autotracker/set', $logicalId === 'action_start_ptz_autotracker' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_ptz_autotracker':
        $this->toggleCameraSetting($frigate, $camera, 'info_ptz_autotracker', 'ptz_autotracker/set');
        break;
      case 'action_start_recordings':
      case 'action_stop_recordings':
        $this->publishCameraMessage($camera, 'recordings/set', $logicalId === 'action_start_recordings' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_recordings':
        $this->toggleCameraSetting($frigate, $camera, 'info_recordings', 'recordings/set');
        break;
      case 'action_start_snapshots':
      case 'action_stop_snapshots':
        $this->publishCameraMessage($camera, 'snapshots/set', $logicalId === 'action_start_snapshots' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_snapshots':
        $this->toggleCameraSetting($frigate, $camera, 'info_snapshots', 'snapshots/set');
        break;
      case 'action_start_motion':
      case 'action_stop_motion':
        $this->publishCameraMessage($camera, 'motion/set', $logicalId === 'action_start_motion' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_motion':
        $this->toggleCameraSetting($frigate, $camera, 'info_motion', 'motion/set');
        break;
      case 'action_ptz_left':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_LEFT');
        break;
      case 'action_ptz_right':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_RIGHT');
        break;
      case 'action_ptz_up':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_UP');
        break;
      case 'action_ptz_down':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_DOWN');
        break;
      case 'action_ptz_stop':
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_zoom_in':
        $this->publishCameraMessage($camera, 'ptz', 'ZOOM_IN');
        break;
      case 'action_ptz_zoom_out':
        $this->publishCameraMessage($camera, 'ptz', 'ZOOM_OUT');
        break;
      case 'action_make_api_event':
        frigate::createEvent($camera, 'manuel');
        break;
      case 'action_make_event':
        frigate::createSnapshot($frigate);
        break;
      default:
        log::add(__CLASS__, 'error', "Unknown action: $logicalId");
    }
  }
  private function updateCronStatus($frigate, $status, $message)
  {
    $frigate->getCmd(null, 'info_Cron')->event($status);
    log::add(__CLASS__, 'debug', $message);
  }

  private function publishCameraMessage($camera, $topic, $message)
  {
    frigate::publish_camera_message($camera, $topic, $message);
  }

  private function toggleCameraSetting($frigate, $camera, $infoCmd, $setCmd)
  {
    $currentStatus = $frigate->getCmd(null, $infoCmd)->execCmd();
    $newStatus = $currentStatus == 1 ? 'OFF' : 'ON';
    frigate::publish_camera_message($camera, $setCmd, $newStatus);
  }

  /*     * **********************Getteur Setteur*************************** */
}
