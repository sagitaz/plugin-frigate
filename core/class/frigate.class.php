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
    if (!config::byKey('event::displayVideo', 'frigate')) {
      config::save('event::displayVideo', true, 'frigate');
    }
    if (!config::byKey('event::confirmDelete', 'frigate')) {
      config::save('event::confirmDelete', true, 'frigate');
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

  private static function execCron($frequence)
  {
    log::add(__CLASS__, 'debug', "----------------------:fg-success:START CRON:/fg:----------------------------------");
    log::add(__CLASS__, 'debug', "| Exécution du cron : {$frequence}");
    log::add(__CLASS__, 'debug', "| Nettoyage du dossier data");
    self::cleanFolderData();
    log::add(__CLASS__, 'debug', "| Nettoyage des anciens fichiers");
    self::cleanAllOldestFiles();

    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (empty($frigate)) {
      return;
    }

    $execute = $frigate->getCmd(null, 'info_Cron')->execCmd();

    if (config::byKey($frequence, 'frigate', 0) == 1) {
      if ($execute == "1") {
        self::getEvents();
        self::getStats();
      }
    }
    log::add(__CLASS__, 'debug', "----------------------END CRON----------------------------------");
    return;
  }

  // Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron()
  {
    self::execCron('functionality::cron::enable');
  }
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5()
  {
    self::execCron('functionality::cron5::enable');
  }
  // Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10()
  {
    self::execCron('functionality::cron10::enable');
  }
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15()
  {
    self::execCron('functionality::cron15::enable');
  }
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30()
  {
    self::execCron('functionality::cron30::enable');
  }
  // Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly()
  {
    self::execCron('functionality::cronHourly::enable');
  }
  // Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily()
  {
    self::checkFriagetVersion();
    self::execCron('functionality::cronDaily::enable');
  }


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


  // Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
  // en lors de la création semi-automatique d'un post sur le forum community
  public static function getConfigForCommunity()
  {
    $system = system::getOsVersion();

    $CommunityInfo = "```\n";
    $CommunityInfo = $CommunityInfo . 'URL : ' . self::getUrlFrigate() . "\n";
    $CommunityInfo = $CommunityInfo . 'MQTT topic : ' . config::byKey('topic', 'frigate') . "\n";
    $CommunityInfo = $CommunityInfo . 'Debian : ' . $system . "\n";
    $CommunityInfo = $CommunityInfo . 'Frigate : ' . config::byKey('frigate_version', 'frigate') . "\n";
    $CommunityInfo = $CommunityInfo . "```";
    return $CommunityInfo;
  }


  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {}

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {}

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {}

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {}

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave()
  {

    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    if ($this->getConfiguration('localApiKey') == '') {
      $this->setConfiguration('localApiKey', config::genKey());
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

      $urlLatest = "http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?timestamp=" . $timestamp . "&bbox=" . $bbox . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions;
      $img = $encoded_url = urlencode($urlLatest);
      $this->setConfiguration('img', $img);
    }

    if ($this->getConfiguration('cameraStreamAccessUrl') == '') {
      $this->setConfiguration('cameraStreamAccessUrl', 'rtsp://' . $url . ':8554/' . $this->getConfiguration('name'));
    }

    $urlStream = "";
    $cmd = cmd::byEqLogicIdCmdName($this->getId(), "SNAPSHOT LIVE");
    if (is_object($cmd)) {
      $urlStream = $cmd->execCmd();
    }

    if ($this->getConfiguration('urlStream') == '' || $this->getConfiguration('urlStream') != $urlStream) {
      $urlJeedom = network::getNetworkAccess('external');
      if ($urlJeedom == "") {
        $urlJeedom = network::getNetworkAccess('internal');
      }
      $urlStream = "/plugins/frigate/core/ajax/frigate.proxy.php?url=" . $img;
      $this->setConfiguration('urlStream', $urlStream);
      if (is_object($cmd)) {
        $cmd->event($urlJeedom . $urlStream);
        $cmd->save();
      }
    }
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {}

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {}

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove()
  {
    $name = $this->getConfiguration('name');
    $events = frigate_events::all();
    log::add(__CLASS__, 'debug', "----------------------:fg-success:START REMOVE EQLOGIC:/fg:----------------------------------");
    foreach ($events as $event) {
      if ($event->getCamera() == $name) {
        $event->setIsFavorite(0);
        $event->save();
        $eventId = $event->getEventId();
        self::cleanDbEvent($eventId);
      }
    }
    log::add(__CLASS__, 'debug', "----------------------END REMOVE EQLOGIC----------------------------------");
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
      if (is_object($this->getCmd('action', 'action_make_api_event'))) {
        $make_snapshot = $this->getCmd("action", 'action_make_api_event');
        if ($make_snapshot->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-camera-retro iconActionOff' . $this->getId() . '" title="' . __("Créer un évènement", __FILE__) . '" onclick="execAction(' . $make_snapshot->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      // commandes create capture
      if (is_object($this->getCmd('action', 'action_create_snapshot'))) {
        $make_snapshot = $this->getCmd("action", 'action_create_snapshot');
        if ($make_snapshot->getIsVisible() == 1) {
          $replace['#actions#'] = $replace['#actions#'] . '<div class="btn-icon">';
          $replace['#actions#'] = $replace['#actions#'] . '<i class="fas fa-image iconActionOff' . $this->getId() . '" title="' . __("Créer une capture", __FILE__) . '" onclick="execAction(' . $make_snapshot->getId() . ')"></i>';
          $replace['#actions#'] = $replace['#actions#'] . '</div>';
        }
      }

      $html = template_replace($replace, getTemplate('core', $version, 'widgetCamera', __CLASS__));
      $html = translate::exec($html, 'plugins/frigate/core/template/' . $version . '/widgetCamera.html');
      $html = $this->postToHtml($_version, $html);
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
      log::add(__CLASS__, 'error', "| Erreur: L'URL ne peut être vide.");
      return false;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, 'error', "| Erreur: Le port ne peut être vide");
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
    log::add(__CLASS__, 'debug', "| publish_message : " . self::getTopic() . "/{$subTopic} avec payload : {$payload}");
    mqtt2::publish(self::getTopic() . "/{$subTopic}", $payload);
  }

  private static function getcURL($function, $url, $params = null, $decodeJson = true, $method = 'GET')
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
      if ($method !== 'DELETE') {
        $jsonParams = json_encode($params);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonParams);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($jsonParams)
        ]);
      }

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, 'error', "| Erreur getcURL (" . $method . "): " . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = $decodeJson ? json_decode($data, true) : $data;
    log::add(__CLASS__, 'debug', "| " . $function . " : requête " . $method . " exécutée.");
    return $response;
  }

  private static function postcURL($function, $url, $params = null, $decodeJson = true)
  {
    return self::getcURL($function, $url, $params, $decodeJson, 'POST');
  }

  private static function putcURL($function, $url, $params = null, $decodeJson = true)
  {
    return self::getcURL($function, $url, $params, $decodeJson, 'PUT');
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
      log::add(__CLASS__, 'error', "| Erreur: deletecURL" . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = json_decode($data, true);
    log::add(__CLASS__, 'debug', "| Suppression sur le serveur Frigate : " . json_encode($response));
    return $response;
  }

  public static function getStats()
  {
    log::add(__CLASS__, 'debug', "----------------------:fg-success:START STATS:/fg:----------------------------------");
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";
    $stats = self::getcURL("Stats", $resultURL);
    self::majStatsCmds($stats);
    log::add(__CLASS__, 'debug', "----------------------END STATS----------------------------------");
  }

  public static function createEvent($camera, $label, $video = 1, $duration = 20, $score = 30, $subLabel = '')
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/events/" . $camera . "/" . rawurlencode($label) . "/create";

    $score = max(0, min(100, floatval($score)));
    $score = $score / 100;
    $duration = floatval($duration);
    $includeRecording = ($video == 1);

    log::add(__CLASS__, 'debug', "----------------------:fg-success:START CREATE EVENT:/fg:----------------------------------");
    log::add(__CLASS__, 'debug', "| label : {$label}");
    log::add(__CLASS__, 'debug', "| score : {$score}");
    log::add(__CLASS__, 'debug', "| duration : {$duration}");
    log::add(__CLASS__, 'debug', "| video : {$video}");
    log::add(__CLASS__, 'debug', "| include_recording : " . ($includeRecording ? "true" : "false"));
    log::add(__CLASS__, 'debug', "| sub_label : {$subLabel}");

    $params = [
      'source_type' => 'api',
      'sub_label' => $subLabel,
      'score' => $score,
      'duration' => $duration,
      'include_recording' => $includeRecording
    ];
    $response = self::postcURL("CreateEvent", $resultURL, $params);

    log::add(__CLASS__, 'debug', "----------------------END CREATE EVENT----------------------------------");
    return $response;
  }

  // Méthodes de modification du fichier de configuration par API
  // Attention : Redémarrage Frigate nécessaire pour prise en compte
  // TODO : Ajouter des méthodes appelant cette méthode pour une modification de paramètres du fichier de configuration
  public static function saveConfig($config)
  {
    log::add(__CLASS__, 'debug', "----------------------:fg-success:START SAVE CONFIG:/fg:----------------------------------");
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config/set?{$config}";

    log::add(__CLASS__, 'debug', "| url : {$resultURL}");
    $response = self::putcURL("saveConfig", $resultURL); //, $params);

    event::add('frigate::config', array('message' => 'api_config_update', 'type' => 'config'));

    log::add(__CLASS__, 'debug', "----------------------END SAVE CONFIG----------------------------------");
    return $response;
  }

  public static function saveCameraConfig($camera, $config)
  {
    $response = self::saveConfig("cameras.{$camera}.{$config}");

    return $response;
  }

  // Méthodes de modification du fichier de configuration pour une caméra par API 
  // Attention : Redémarrage Frigate nécessaire pour prise en compte
  // TODO : Ajouter d'autres méthodes pour différents paramètres supplémentaires pour une caméra
  public static function enableCamera($camera, $enable)
  {
    $enabled = $enable == 1 ? 'true' : 'false';
    $response = self::saveCameraConfig($camera, "enabled={$enabled}");

    return $response;
  }

  public static function getLogs($service)
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/logs/" . $service;
    $logs = self::getcURL("Logs", $resultURL, null, false);

    return $logs;
  }

  public static function getEvent($id = null, $type = 'end')
  {
    if ($id == null) return;

    self::getEvents(false, array(), $type, $id);
  }

  public static function getEvents($mqtt = false, $events = array(), $type = 'end', $id = null, $recoveryDays = null)
  {
    if ($id !== null) {
      $urlFrigate = self::getUrlFrigate();
      $resultURL = "{$urlFrigate}/api/events/{$id}";
      $event = self::getcURL("ManualEvent", $resultURL);
      // Traiter un évènement
      $events = array($event);
    } else if (!$mqtt) {
      $urlFrigate = self::getUrlFrigate();
      $resultURL = "{$urlFrigate}/api/events";
      $events = self::getcURL("Events", $resultURL);
      // Traiter les evenements du plus ancien au plus recent
      $events = array_reverse($events);
    }
    if ($recoveryDays != 1) {
      // Nombre de jours a filtrer et enregistrer en DB
      $recoveryDays = config::byKey('recovery_days', 'frigate');
      if (empty($recoveryDays)) {
        $recoveryDays = 7;
      }
    }
    // vérification de la taille du dossier et nettoyage
    self::cleanFolderDataIfFull();

    $filteredRecoveryEvents = array_filter($events, function ($event) use ($recoveryDays) {
      return $event['start_time'] >= time() - $recoveryDays * 86400;
    });
    $filteredRecoveryEvents = array_values($filteredRecoveryEvents);

    foreach ($filteredRecoveryEvents as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      log::add(__CLASS__, 'debug', "----------------------:fg-success:START EVENT:/fg:----------------------------------");

      $infos = self::getEventinfos($mqtt, $event, false, $type);

      if (!$frigate) {
        log::add(__CLASS__, 'debug', "| Events (type=" . $type . ") => " . json_encode($event));
        $box = $event['data']['box'] ?? "null";

        $frigate = new frigate_events();
        $frigate->setBox($box);
        $frigate->setCamera($event['camera']);
        $frigate->setData($event['data']);
        $frigate->setLasted($infos["image"]);
        $frigate->setHasClip($infos["hasClip"]);
        $frigate->setClip($infos["clip"]);
        $frigate->setHasSnapshot($infos["hasSnapshot"]);
        $frigate->setSnapshot($infos["snapshot"]);
        $frigate->setStartTime($infos['startTime']);
        $frigate->setEndTime($infos["endTime"]);
        // $frigate->setFalsePositive($event['false_positive']);
        $frigate->setEventId($event['id']);
        $frigate->setLabel($infos['label']);
        $frigate->setPlusId($event['plus_id']);
        $frigate->setRetain($event['retain_indefinitely']);
        $frigate->setSubLabel($event['sub_label']);
        $frigate->setThumbnail($infos["thumbnail"]);
        $frigate->setTopScore($infos["topScore"]);
        $frigate->setScore($infos["score"]);
        $frigate->setZones($infos['zones']);
        $frigate->setType($type);
        $frigate->setIsFavorite(0);
        $frigate->save();
        self::majEventsCmds($frigate);

        log::add(__CLASS__, 'debug', "| Evénement Frigate créé et sauvegardé, event ID: " . $event['id']);
      } else {

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
          'Box' => $event['data']['box'] ?? null,
          'Camera' => $event['camera'],
          // 'FalsePositive' => $event['false_positive'],
          'Label' => $infos['label'],
          'PlusId' => $event['plus_id'],
          'SubLabel' => $event['sub_label'],
          'Thumbnail' => $infos["thumbnail"],
          'Type' => $type,
          'TopScore' => $infos["topScore"],
          'Score' => $infos["score"],
          'Zones' => $infos['zones']
        ];

        foreach ($fieldsToUpdate as $field => $value) {
          $getMethod = 'get' . $field;
          $setMethod = 'set' . $field;
          //$currentValue = is_string($frigate->$getMethod()) ? json_decode($frigate->$getMethod(), true) : $frigate->$getMethod();
          $currentValue = $frigate->$getMethod();
          //$newValue = is_string($value) ? json_decode($value, true) : $value;
          $newValue = $value;

          // soucis sur maj Box, "[]" != []
          if ($field == 'Box') {
            if ($value !== null) {
              $newValue = json_encode($value);
            }
            // log::add(__CLASS__, 'debug', "| BOX, ancienne valeur: " . $currentValue . ", nouvelle valeur: " . $newValue);
          }

          if ((is_null($currentValue) || $currentValue === '' || $currentValue != $newValue) && !is_null($newValue) && $newValue !== '') {
            log::add(__CLASS__, 'debug', "| Mise à jour du champ '$field' pour event ID: " . $event['id'] . ". ancienne valeur: " . json_encode($currentValue) . ", nouvelle valeur: " . json_encode($newValue));
            $frigate->$setMethod($newValue);
            $updated = true;
            if ($field == 'Type' && $newValue == 'end') {
              $infos = self::getEventinfos($mqtt, $event, true);
              $frigate->setSnapshot($infos["snapshot"]);
              $frigate->setClip($infos["clip"]);
              log::add(__CLASS__, 'debug', "| Mise à jour forcé des champs snapshot et clip pour event ID: " . $event['id']);
              $frigate->save();
            }
          }
        }

        if ($updated) {
          $frigate->setData($event['data']);
          log::add(__CLASS__, 'debug', "| Mise à jour du champ data pour event ID: " . $event['id']);
          $frigate->save();
          self::majEventsCmds($frigate);
          log::add(__CLASS__, 'debug', "| Evénement Frigate mis à jour et sauvegardé, event ID: " . $event['id']);
        } else {
          log::add(__CLASS__, 'debug', "| Pas de mise à jour pour event ID: " . $event['id']);
        }
      }
      log::add(__CLASS__, 'debug', "----------------------END EVENT----------------------------------");
    }
  }

  public static function getEventinfos($mqtt, $event, $force = false, $type = "end")
  {
    $dir = dirname(__FILE__, 3) . "/data/" . $event['camera'];
    // verifier si le fichier thumbnail existe avant de le telecharger
    if (!file_exists($dir . '/' . $event['id'] . '_thumbnail.jpg')) {
      log::add(__CLASS__, 'debug', "| Fichier non trouvé: " . $dir . '/' . $event['id'] . '_thumbnail.jpg, téléchargement');
      $img = self::saveURL($event['id'], null, $event['camera'], 1);
      if ($img == "error") {
        $img = "null";
      }
    } else {
      //log::add(__CLASS__, 'debug', "| File found: " . $dir . '/' . $event['id'] . '_thumbnail.jpg');
      $img = "/plugins/frigate/data/" . $event['camera'] . "/" . $event['id'] . '_thumbnail.jpg';
    }

    // verifier si le fichier snapshot existe avant de le telecharger
    if (!file_exists($dir . '/' . $event['id'] . '_snapshot.jpg') || $force) {
      log::add(__CLASS__, 'debug', "| Fichier non trouvé: " . $dir . '/' . $event['id'] . '_snapshot.jpg');
      if ($event['has_snapshot'] == "true") {
        log::add(__CLASS__, 'debug', "| Has Snapshot: true, téléchargement");
        $snapshot = self::saveURL($event['id'], "snapshot", $event['camera']);
        $hasSnapshot = 1;
        if ($snapshot == "error") {
          $snapshot = "null";
          $hasSnapshot = 0;
        }
      } else {
        log::add(__CLASS__, 'debug', "| Has Snapshot: false, téléchargement annulé");
        $snapshot = "null";
        $hasSnapshot = 0;
      }
    } else {
      //log::add(__CLASS__, 'debug', "| File found: " . $dir . '/' . $event['id'] . '_snapshot.jpg');
      $snapshot = "/plugins/frigate/data/" . $event['camera'] . "/" . $event['id'] . '_snapshot.jpg';
      $hasSnapshot = 1;
    }

    // verifier si le fichier clip existe avant de le telecharger
    if (!file_exists($dir . '/' . $event['id'] . '_clip.mp4') || $force) {
      log::add(__CLASS__, 'debug', "| Fichier non trouvé: " . $dir . '/' . $event['id'] . '_clip.mp4');
      if ($type == "end") {
        if ($event['has_clip'] == "true") {
          log::add(__CLASS__, 'debug', "| Has Clip: true, téléchargement");
          sleep(5);
          $clip = self::saveURL($event['id'], "clip", $event['camera']);
          $hasClip = 1;
          if ($clip == "error") {
            $clip = "null";
            $hasClip = 0;
          } else {
            $filePath = $dir . '/' . $event['id'] . '_clip.mp4';
            $duration = self::getVideoDuration($filePath);
            if ($duration !== false) {
              log::add(__CLASS__, 'debug', "| La durée de la video est de " . gmdate("H:i:s", $duration));
            } else {
              log::add(__CLASS__, 'debug', "| Impossible de recuperer la durée de la videofile");
            }
          }
        } else {
          log::add(__CLASS__, 'debug', "| Has Clip: false, téléchargement annulé");
          $clip = "null";
          $hasClip = 0;
        }
      } else {
        log::add(__CLASS__, 'debug', "| Pas de clip, le type n'est pas 'end' " . json_encode($event));
      }
    } else {
      $clip = "/plugins/frigate/data/" . $event['camera'] . "/" . $event['id'] . '_clip.mp4';
      $hasClip = 1;
      $filePath = $dir . '/' . $event['id'] . '_clip.mp4';
      $duration = self::getVideoDuration($filePath);
      if ($duration !== false) {
        log::add(__CLASS__, 'debug', "| La durée de la video est de " . gmdate("H:i:s", $duration));
      } else {
        log::add(__CLASS__, 'debug', "| Impossible de recuperer la durée de la videofile");
      }
    }

    // verifier le endtime
    $endTime = $event['end_time'];
    if (empty($event['end_time'])) {
      log::add(__CLASS__, 'debug', "| Evénement sans end_time, il est forcé à 0 : " . json_encode($event));
      $endTime = 0;
    }

    // calculer le score
    if (!$mqtt) {
      $newTopScore = round($event['data']['top_score'] * 100, 0);
      $newScore = round($event['data']['score'] * 100, 0);
    } else {
      $newTopScore = round($event['top_score'] * 100, 0);
      $newScore = round($event['score'] * 100, 0);
    }

    // calculer les zones
    $newZones = isset($event['zones'])
      && is_array($event['zones'])
      && !empty($event['zones'])
      ? implode(', ', $event['zones'])
      : null;

    // nettoyer le label
    $label = $event['label'];
    // Détecter si la chaîne est déjà en UTF-8
    /*  if (mb_detect_encoding($label, 'UTF-8', true) === 'UTF-8') {
      // Si la chaîne est déjà en UTF-8, on la décodera à partir de UTF-8
      $label = utf8_decode($label);
    } else {
      // Sinon, on la convertit de ISO-8859-1 à UTF-8
      $label = mb_convert_encoding($label, 'UTF-8', 'ISO-8859-1');
    } */
    // renvoyer les infos
    $infos = array(
      "image" => $img,
      "thumbnail" => $img,
      "snapshot" => $snapshot,
      "hasSnapshot" => $hasSnapshot,
      "clip" => $clip ?? "",
      "hasClip" => $hasClip ?? 0,
      "startTime" => ceil($event['start_time']) > 0 ? ceil($event['start_time']) : $event['start_time'],
      "endTime" => ceil($endTime) > 0 ? ceil($endTime) : $endTime,
      "topScore" => $newTopScore,
      "score" => $newScore,
      "zones" => $newZones,
      "label" => $label
    );

    return $infos;
  }

  public static function getVideoDuration($filePath)
  {
    $cmd = "ffmpeg -i " . escapeshellarg($filePath) . " 2>&1";
    $output = shell_exec($cmd);

    if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})\.(\d{2})/', $output, $matches)) {
      $hours = $matches[1];
      $minutes = $matches[2];
      $seconds = $matches[3];
      $duration = ($hours * 3600) + ($minutes * 60) + $seconds;

      return $duration; // Durée en secondes
    }

    return false; // En cas d'erreur
  }
  // Fonction de nettoyage du dossier data, suppression de tous les fichiers n'ayant pas d'event associé en DB Jeedom
  // Exécution en cronDaily
  public static function cleanFolderData()
  {
    // Nettoyage du dossier des images caméra
    $folder = dirname(__FILE__, 3) . "/data";

    if (file_exists($folder)) {
      // Parcourt récursivement tous les fichiers et dossiers
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile()) {
          $path = $file->getPathname();
          // Vérifiez que le fichier est dans un sous-dossier de /data
          if (strpos($path, $folder . DIRECTORY_SEPARATOR) === 0 && $path !== $folder . DIRECTORY_SEPARATOR . basename($path) && strpos($path, $folder . DIRECTORY_SEPARATOR . 'snapshots' . DIRECTORY_SEPARATOR) === false) {
            $id = self::extractID($file->getFilename());
            // Vérifier si l'id existe dans la base de données
            $frigate = frigate_events::byEventId($id);
            if (!$frigate) {
              log::add(__CLASS__, 'debug', "| Fichier " . $path . " non trouvé en database.");
              if (unlink($path)) {
                log::add(__CLASS__, 'debug', "| Suppresion reussie: " . $path);
              } else {
                log::add(__CLASS__, 'error', "| Suppresion echouée: " . $path);
              }
            }
          }
        }
      }
    } else {
      log::add(__CLASS__, 'error', "| Dossier inexistant: " . $folder);
    }
  }


  // nettoyer la DB de tous les fichiers dont la date de creation est supérieure au nombre de jours configurer
  // Exécution en cronDaily
  public static function cleanAllOldestFiles()
  {
    $days = config::byKey('remove_days', 'frigate', "7");
    $recoveryDays = config::byKey('recovery_days', 'frigate', "7");
    if (!is_numeric($days) || $days <= 0) {
      log::add(__CLASS__, 'error', "| Configuration invalide pour 'remove_days': " . $days . " Cela doit être un nombre positif.");
      return;
    }
    if ($days < $recoveryDays) {
      log::add(__CLASS__, 'warning', "| 'remove_days' doit être supérieur à 'recovery_days'");
      $days = $recoveryDays;
    }
    log::add(__CLASS__, 'info', "| Nettoyage des fichiers datant de plus de " . $days . " jours.");

    $events = frigate_events::getOldestNotFavorites($days);

    if (!empty($events)) {
      foreach ($events as $event) {
        $eventId = $event->getEventId();

        log::add(__CLASS__, 'info', "| Nettoyage de l'événement ID: " . $eventId);

        $result = self::cleanDbEvent($eventId);

        if ($result) {
          log::add(__CLASS__, 'info', "| Événement ID: " . $eventId . " nettoyé avec succès.");
        } else {
          log::add(__CLASS__, 'error', "| Échec du nettoyage de l'événement ID: " . $eventId);
        }
      }
    } else {
      log::add(__CLASS__, 'info', "| Aucun événement trouvé datant de plus de " . $days . " jours.");
    }
  }



  public static function cleanOldestFile()
  {
    $events = [];
    $events = frigate_events::getOldestNotFavorite();
    if (!empty($events)) {
      foreach ($events as $event) {
        self::cleanDbEvent($event->getEventId());
      }
    }
  }

  // Supprime le plus vieux si dossier plein
  public static function cleanFolderDataIfFull()
  {
    $maxSize = config::byKey('datas_weight', 'frigate');
    $size = self::getFolderSize();
    log::add(__CLASS__, 'debug', "| Taille du dossier : " . $size);
    log::add(__CLASS__, 'debug', "| Taille maximale du dossier : " . $maxSize);

    while ($size > $maxSize) {
      log::add(__CLASS__, 'debug', "| Le dossier est plein, nettoyage du fichier le plus ancien");
      self::cleanOldestFile();
      $size = self::getFolderSize();
      log::add(__CLASS__, 'debug', "| Nouvelle taille du dossier : " . $size);
    }

    if ($size <= $maxSize) {
      log::add(__CLASS__, 'debug', "| Le dossier n'est pas plein");
    }
  }


  // Fonction qui calcule la taille du dossier data
  public static function getFolderSize()
  {
    $folder = dirname(__FILE__, 3) . "/data";
    $size = 0;
    // Parcourt récursivement tous les fichiers et dossiers
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
      $size += $file->getSize(); // Ajoute la taille du fichier
    }
    $size = round($size / 1024 / 1024, 2);
    return $size;
  }

  public static function extractID($filename)
  {
    // Utiliser une expression régulière pour extraire l'ID du nom de fichier
    if (preg_match(
      '/^\d+\.\d+-[a-z0-9]+/',
      $filename,
      $matches
    )) {
      return $matches[0];
    }
    return null;
  }

  public static function cleanDbEvent($id)
  {
    $frigate = frigate_events::byEventId($id);
    if (!empty($frigate) && isset($frigate[0])) {
      $frigate = $frigate[0];
      // Vérifier si le fichier est un favori
      $isFavorite = $frigate->getIsFavorite() ?? 0;
      if (
        $isFavorite == 1
      ) {
        log::add(__CLASS__, 'debug', "| Événement " . $frigate->getEventId() . " est un favori, il ne doit pas être supprimé de la base de données.");
      } else {
        // Recherche si clip et snapshot existent dans le dossier de sauvegarde
        $clip = dirname(__FILE__, 3) . "/data/" . $frigate->getCamera() . "/" . $frigate->getEventId() . "_clip.mp4";
        $snapshot = dirname(__FILE__, 3) . "/data/" . $frigate->getCamera() . "/" . $frigate->getEventId() . "_snapshot.jpg";
        $thumbnail = dirname(__FILE__, 3) . "/data/" . $frigate->getCamera() . "/" . $frigate->getEventId() . "_thumbnail.jpg";

        if (file_exists($clip)) {
          unlink($clip);
          log::add(__CLASS__, 'debug', "| Clip MP4 supprimé pour l'événement " . $frigate->getEventId());
        }
        if (file_exists($snapshot)) {
          unlink($snapshot);
          log::add(__CLASS__, 'debug', "| Snapshot JPG supprimé pour l'événement " . $frigate->getEventId());
        }
        if (file_exists($thumbnail)) {
          unlink($thumbnail);
          log::add(__CLASS__, 'debug', "| Miniature JPG supprimée pour l'événement " . $frigate->getEventId());
        }

        $frigate->remove();
        log::add(__CLASS__, 'debug', "| Événement " . $frigate->getEventId() . " supprimé de la base de données.");
      }
      return true;
    } else {
      return false;
    }
  }


  public static function deleteEvent($id, $all = false)
  {
    $frigate = frigate_events::byEventId($id);
    if (!empty($frigate) && isset($frigate[0])) {
      $isFavorite = $frigate[0]->getIsFavorite() ?? 0;
      if ($isFavorite == 1) {
        log::add(__CLASS__, 'debug', "| Evènement " . $frigate[0]->getEventId() . " est un favori, il ne doit pas être supprimé de la DB.");
        message::add('frigate', __("L'évènement est un favori, il ne peut pas être supprimé de la DB.", __FILE__), null, null);
        return "Error 01";
      }

      $urlfrigate = self::getUrlFrigate();
      $resultURL = $urlfrigate . "/api/events/" . $id;

      if ($all) {
        self::deletecURL($resultURL);
        self::cleanDbEvent($id);
      } else {
        self::cleanDbEvent($id);
      }
      return "OK";
    } else {
      return "Error 02";
    }
  }
  public static function showEvents()
  {
    $result = [];
    $events = frigate_events::all();

    foreach ($events as $event) {
      $date = date("d-m-Y H:i:s", $event->getStartTime());
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);

      $result[] = array(
        "id" => $event->getId(),
        "img" => $event->getLasted(),
        "camera" => $event->getCamera(),
        "label" => $event->getLabel(),
        "box" => $event->getBox(),
        "date" => $date,
        "duree" => $duree,
        "startTime" => $event->getStartTime(),
        "endTime" => $event->getEndTime(),
        "snapshot" => $event->getSnapshot(),
        "clip" => $event->getClip(),
        "thumbnail" => $event->getthumbnail(),
        "hasSnapshot" => $event->getHasSnapshot(),
        "hasClip" => $event->getHasClip(),
        "eventId" => $event->getEventId(),
        "score" => $event->getScore(),
        "top_score" => $event->getTopScore(),
        "type" => $event->getType(),
        "isFavorite" => $event->getIsFavorite() ?? 0,
        "zones" => $event->getZones() ?? ''
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

  public static function generateAllEqs() {

    log::add(__CLASS__, 'debug', "----------------------:fg-success:CREATION DES EQUIPEMENTS:/fg:----------------------------------");
    frigate::generateEqEvents();
    frigate::generateEqStats();
    frigate::generateEqCameras();

    frigate::setCmdsCron();
    log::add(__CLASS__, 'debug', "----------------------:fg-success:FIN CREATION DES EQUIPEMENTS:/fg:----------------------------------");
  }
  public static function generateEqCameras()
  {

    log::add(__CLASS__, 'debug', "----------------------:fg-success:CREATION DES CAMERAS:/fg:----------------------------------");
    $urlfrigate = self::getUrlFrigate();
    //  $resultURL = $urlfrigate . "/api/stats";
    // décoder le yaml de configuration
    $configuration = self::yamlToJsonFromUrl("http://" . $urlfrigate . "/api/config/raw");
    // Décoder la chaîne JSON en tableau PHP
    $configurationArray = json_decode($configuration, true);
    log::add(__CLASS__, 'debug', "| Fichier de configuration : " . json_encode($configurationArray));
    $mqttCmds = isset($configurationArray['mqtt']['host']) && !empty($configurationArray['mqtt']['host']);
    $audioCmds = isset($configurationArray['audio']['enable']) && !empty($configurationArray['audio']['enable']);
    $exist = 0;
    $addToName = "";
    $create = 1;
    //  $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;

    foreach ($configurationArray['cameras'] as $cameraName => $cameraConfig) {
      $eqlogics = eqLogic::byObjectId($defaultRoom);
      foreach ($eqlogics as $eqlogic) {
        $name = $eqlogic->getname();
        if ($name === $cameraName) {
          $exist = 1;
          break;
        }
      }
      if ($exist) {
        log::add(__CLASS__, 'debug', "| L'équipement : " . json_encode($cameraName) . " existe dans la pièce : " . jeeObject::byId($defaultRoom)->getName());
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
        log::add(__CLASS__, 'debug', "| L'équipement : " . json_encode($cameraName . $addToName) . " est créé.");
      } else {
        log::add(__CLASS__, 'debug', "| L'équipement : " . json_encode($cameraName) . " n'est pas créé.");
      }
      $frigate->setLogicalId("eqFrigateCamera_" . $cameraName);
      $frigate->save();
      // commandes identique pour toutes les caméras
      log::add(__CLASS__, 'debug', "| Création des commandes génèrales pour : " . json_encode($cameraName));
      self::createCamerasCmds($frigate->getId());
      // commandes MQTT s'il est configuré
      if ($mqttCmds) {
        log::add(__CLASS__, 'debug', "| Création des commandes MQTT pour : " . json_encode($cameraName));
        self::createMqttCmds($frigate->getId());
        // commande PTZ si onvif est configuré
        if (isset($cameraConfig['onvif']['host']) && !empty($cameraConfig['onvif']['host']) && $cameraConfig['onvif']['host'] !== '0.0.0.0') {
          log::add(__CLASS__, 'debug', "| Création des commandes PTZ pour : " . json_encode($cameraName));
          self::createPTZcmds($frigate->getId());
        }
      }
      // commandes audio s'il est configuré
      if ($audioCmds) {
        log::add(__CLASS__, 'debug', "| Création des commandes audio pour : " . json_encode($cameraName));
        self::createAudioCmds($frigate->getId());
      }
    }
    message::add('frigate', 'Frigate : ' . $n . ' cameras créées, les commandes, évènements et statistiques sont mises à jour. Veuillez patienter...');
    // commandes de statisque
    self::getStats();
    // commandes des events
    self::getEvents(false, array(), 'end', null, 1);
    message::add('frigate', 'Mise à jour des commandes, évènements et statistiques terminé.');

    log::add(__CLASS__, 'debug', "----------------------END CREATION DES CAMERAS----------------------------------");
    return $n;
  }

  public static function restartFrigate()
  {
    log::add(__CLASS__, 'info', ":fg-warning:restartFrigate:/fg:");
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
    $cmd = self::createCmd($eqlogicId, "URL", "string", "", "info_url", "");
    $cmd->save();
    $cmd->event($url);
    $cmd->save();
  }

  public static function createAudioCmds($eqlogicId)
  {
    $infoCmd = self::createCmd($eqlogicId, "audio Etat", "binary", "", "info_audio", "JEEMATE_CAMERA_AUDIO_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    // commande action
    $cmd = self::createCmd($eqlogicId, "audio off", "other", "", "action_stop_audio", "JEEMATE_CAMERA_AUDIO_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "audio on", "other", "", "action_start_audio", "JEEMATE_CAMERA_AUDIO_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "audio toggle", "other", "", "action_toggle_audio", "JEEMATE_CAMERA_AUDIO_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();
  }
  public static function createCamerasCmds($eqlogicId)
  {
    $eqlogic = eqLogic::byId($eqlogicId);
    // Récupération des URLs externes et internes
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');
    $name = $eqlogic->getConfiguration('name');

    $cmd = self::createCmd($eqlogicId, "Créer un évènement", "message", "", "action_make_api_event", "", 1, null, 0, "action");
    $cmd->save();
    $infoCmd = self::createCmd($eqlogicId, "URL image", "string", "", "info_url_capture", "", 0, null, 0);
    $infoCmd->save();
    $cmd = self::createCmd($eqlogicId, "Capturer une image", "other", "", "action_create_snapshot", "", 1, $infoCmd, 0, "action");
    $cmd->save();

    // commande des liens rtsp et snapshot live
    $infoCmd = self::createCmd($eqlogicId, "RTSP", "string", "", "link_rtsp", "", 0, null, 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $link = $eqlogic->getConfiguration("cameraStreamAccessUrl");
      $infoCmd->event($link);
      $infoCmd->save();
    }
    $infoCmd = self::createCmd($eqlogicId, "SNAPSHOT LIVE", "string", "", "link_snapshot", "CAMERA_URL", 0, null, 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $link = $urlJeedom . "/plugins/frigate/core/ajax/frigate.proxy.php?url=http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg";
      $infoCmd->event($link);
      $infoCmd->save();
    }

    // commande action enable/disable camera
    $infoCmd = self::createCmd($eqlogicId, "(Config) Etat activation caméra", "binary", "", "enable_camera", "", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    $cmd = self::createCmd($eqlogicId, "(Config) Désactiver caméra", "other", "", "action_disable_camera", "", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "(Config) Activer caméra", "other", "", "action_enable_camera", "", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "(Config) Inverser activation caméra", "other", "", "action_toggle_camera", "", 0, $infoCmd, 0, "action");
    $cmd->save();
  }
  public static function createMQTTcmds($eqlogicId)
  {
    $infoCmd = self::createCmd($eqlogicId, "detect Etat", "binary", "", "info_detect", "JEEMATE_CAMERA_DETECT_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    // commande action
    $cmd = self::createCmd($eqlogicId, "detect off", "other", "", "action_stop_detect", "JEEMATE_CAMERA_DETECT_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "detect on", "other", "", "action_start_detect", "JEEMATE_CAMERA_DETECT_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "detect toggle", "other", "", "action_toggle_detect", "JEEMATE_CAMERA_DETECT_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();


    $infoCmd = self::createCmd($eqlogicId, "recordings Etat", "binary", "", "info_recordings", "JEEMATE_CAMERA_NVR_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    // commande action
    $cmd = self::createCmd($eqlogicId, "recordings off", "other", "", "action_stop_recordings", "JEEMATE_CAMERA_NVR_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "recordings on", "other", "", "action_start_recordings", "JEEMATE_CAMERA_NVR_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "recordings toggle", "other", "", "action_toggle_recordings", "JEEMATE_CAMERA_NVR_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();


    $infoCmd = self::createCmd($eqlogicId, "snapshots Etat", "binary", "", "info_snapshots", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    // commande action
    $cmd = self::createCmd($eqlogicId, "snapshots off", "other", "", "action_stop_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_OFF", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "snapshots on", "other", "", "action_start_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_ON", 1, $infoCmd, 0, "action");
    $cmd->save();
    $cmd = self::createCmd($eqlogicId, "snapshots toggle", "other", "", "action_toggle_snapshots", "JEEMATE_CAMERA_SNAPSHOT_SET_TOGGLE", 0, $infoCmd, 0, "action");
    $cmd->save();

    $infoCmd = self::createCmd($eqlogicId, "détection en cours", "binary", "", "info_detectNow", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
    $infoCmd = self::createCmd($eqlogicId, "motion Etat", "binary", "", "info_motion", "JEEMATE_CAMERA_SNAPSHOT_STATE", 0);
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
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
    $infoCmd->save();
    $value = $infoCmd->execCmd();
    if (!isset($value) || $value == null || $value == '') {
      $infoCmd->event(1);
      $infoCmd->save();
    }
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
      // Récupération de la configuration des actions de la caméra
      $cameraActions = $eqCamera->getConfiguration('actions')[0];
      // Vérifier si la liste d'actions est vide
      $cameraActionsExist = !empty($cameraActions);
    }



    if ($cameraActionsExist) {
      log::add(__CLASS__, 'debug', "| ACTION: Vérification des actions caméra.");

      // Vérifier si toutes les actions sont désactivées
      $allActionsDisabled = true;
      foreach ($cameraActions as $action) {
        // Vérifier si l'action est activée
        $enable = $action['options']['enable'] ?? false;

        if ($enable) {
          // Si au moins une action est activée, on met à jour $allActionsDisabled à false
          $allActionsDisabled = false;
          log::add(__CLASS__, 'debug', "| ACTION: Une action caméra est activée.");
          break;
        }
      }

      // Si toutes les actions sont désactivées, on met à jour $cameraActionsExist à false
      if ($allActionsDisabled) {
        $cameraActionsExist = false;
        log::add(__CLASS__, 'debug', "| ACTION: Toutes les actions caméra sont désactivées.");
      }
    } else {
      log::add(__CLASS__, 'debug', "| ACTION: Aucune action configurée.");
    }

    // Vérification des actions caméra existantes
    // Si la liste d'actions n'est pas vide et qu'au moins une action est activée
    if ($cameraActionsExist) {
      log::add(__CLASS__, 'debug', "| ACTION: Exécution des actions pour la caméra (ID: " . $eqCamera->getId() . ").");
      self::executeActionNewEvent($eqCamera->getId(), $event);
    } else {
      // Sinon, on exécute les actions suivantes
      log::add(__CLASS__, 'debug', "| ACTION: Aucune action caméra activée, exécution des actions pour l'équipement Events (ID: " . $frigate->getId() . ").");
      self::executeActionNewEvent($frigate->getId(), $event);
    }



    foreach ($eqlogicIds as $eqlogicId) {
      // Creation des commandes infos
      $cmd = self::createCmd($eqlogicId, "caméra", "string", "", "info_camera", "GENERIC_INFO", 0, null, 0);
      $cmd->event($event->getCamera());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "label", "string", "", "info_label", "JEEMATE_CAMERA_DETECT_TYPE_STATE", 0, null, 0);
      $cmd->event($event->getLabel());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "clip disponible", "binary", "", "info_clips", "");
      $cmd->event($event->getHasClip());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "snapshot disponible", "binary", "", "info_snapshot", "");
      $cmd->event($event->getHasSnapshot());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "top score", "numeric", "%", "info_topscore", "GENERIC_INFO");
      $cmd->event($event->getTopScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "score", "numeric", "%", "info_score", "");
      $cmd->event($event->getScore());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "zones", "string", "", "info_zones", "", 0, null, 0);
      $cmd->event($event->getZones());
      $cmd->save();

      $cmd = self::createCmd($eqlogicId, "id", "string", "", "info_id", "", 0, null, 0);
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


      $cmd = self::createCmd($eqlogicId, "URL snapshot", "string", "", "info_url_snapshot", "", 0, null, 0);
      $cmd->event($event->getSnapshot());
      $cmd->save();


      $cmd = self::createCmd($eqlogicId, "URL clip", "string", "", "info_url_clip", "", 0, null, 0);
      $cmd->event($event->getClip());
      $cmd->save();


      $cmd = self::createCmd($eqlogicId, "URL thumbnail", "string", "", "info_url_thumbnail", "", 0, null, 0);
      $cmd->event($event->getThumbnail());
      $cmd->save();
    }
  }

  public static function majStatsCmds($stats, $mqtt = false)
  {
    // Statistiques pour chaque eqLogic caméras
    // Mise à jour des statistiques des caméras
    foreach ($stats['cameras'] as $cameraName => $cameraStats) {
      // Recherche equipement caméra
      $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $cameraName, "frigate");
      if (is_object($eqCamera)) {
        $eqlogicCameraId = $eqCamera->getId();
        foreach ($cameraStats as $key => $value) {
          // Créer ou récupérer la commande
          $cmd = self::createCmd($eqlogicCameraId, $key, "numeric", "", "cameras_" . $key, "GENERIC_INFO");
          // Enregistrer la valeur de l'événement
          $cmd->event($value);
          $cmd->save();
          // Mise à jour de l'activité de la caméra en fonction de pid
          if ($key === 'pid') {
            $cameraEnabled = $value != 0;
            $cmd = $eqCamera->getCmd(null, 'enable_camera');
            if (is_object($cmd)) {
              $cmd->event($cameraEnabled);
            } else {
              log::add(__CLASS__, 'debug', "L'équipement camera " . $cameraName . " n'a pas de commande enable_camera.");
            }
          }
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

    $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "", 0, null, 0);
    // Enregistrer la valeur de l'événement
    $cmd->event($version);
    $cmd->save();
    if ($version != config::byKey('frigate_version', 'frigate')) {
      config::save('frigate_version', $version, 'frigate');
    }
  }

  private static function executeActionNewEvent($eqLogicId, $event)
  {
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
      log::add(__CLASS__, 'info', "| " . $eqLogic->getHumanName() . ' : actions non exécutées car ' . $conditionIf . ' est vrai.');
      return;
    }

    $actions = $eqLogic->getConfiguration('actions')[0];

    foreach ($actions as $action) {
      $cmd = $action['cmd'];
      $cmdLabelName = $action['cmdLabelName'] ?: "all";
      $cmdTypeName = $action['cmdTypeName'] ?: "end";
      $options = $action['options'];
      $enable = $action['options']['enable'] ?? false;

      if (!$enable) {
        log::add(__CLASS__, 'debug', "| Commande(s) désactivée(s)");
        continue;
      }

      $options = str_replace(
        ['#time#', '#event_id#', '#camera#', '#score#', '#has_clip#', '#has_snapshot#', '#top_score#', '#zones#', '#snapshot#', '#snapshot_path#', '#clip#', '#clip_path#', '#thumbnail#', '#thumbnail_path#', '#label#', '#start#', '#end#', '#duree#', '#type#'],
        [$time, $eventId, $camera, $score, $hasClip, $hasSnapshot, $topScore, $zones, $snapshot, $snapshotPath, $clip, $clipPath, $thumbnail, $thumbnailPath, $label, $start, $end, $duree, $type],
        $options
      );

      // Vérifie si le temps de début de l'événement est inférieur ou égal à trois heures avant le temps actuel
      if ($event->getStartTime() <= time() - 10800) {
        log::add(__CLASS__, 'debug', "| ACTION: Événement trop ancien (plus de 3 heures), il sera ignoré.");
        continue;
      }

      // Vérifie si le label de commande ne correspond pas au label attendu
      if ($cmdLabelName !== $label && $cmdLabelName !== "all") {
        log::add(__CLASS__, 'debug', "| ACTION: Label de commande ('{$cmdLabelName}') ne correspond pas au label attendu ('{$label}') et n'est pas 'all', l'action sera ignoré.");
        continue;
      }

      // Vérifie si le type de commande ne correspond pas au type attendu
      if ($cmdTypeName !== $type) {
        log::add(__CLASS__, 'debug', "| ACTION: Type de commande ('{$cmdTypeName}') ne correspond pas au type attendu ('{$type}'), l'action sera ignoré.");
        continue;
      }

      // Exécuter l'action selon le contenu des options
      $optionsJson = json_encode($action['options']);
      if (strpos($optionsJson, '#clip#') !== false || strpos($optionsJson, '#clip_path#') !== false) {
        if ($hasClip == 1) {
          log::add(__CLASS__, 'debug', "| ACTION CLIP : " . $optionsJson);
          scenarioExpression::createAndExec('action', $cmd, $options);
        }
      } elseif (strpos($optionsJson, '#snapshot#') !== false || strpos($optionsJson, '#snapshot_path#') !== false) {
        if ($hasSnapshot == 1) {
          log::add(__CLASS__, 'debug', "| ACTION SNAPSHOT : " . $optionsJson);
          scenarioExpression::createAndExec('action', $cmd, $options);
        }
      } else {
        log::add(__CLASS__, 'debug', "| ACTION : " . $optionsJson);
        scenarioExpression::createAndExec('action', $cmd, $options);
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
      $path = "/data/snapshots/" . $eventId . "_snapshot.jpg";
    } elseif ($mode == 4) {
      $path = "/data/" . $camera . "/" . $eventId . "_clip.mp4";
      $newpath = dirname(__FILE__, 3) . $path;
      // clip creator
      $output = [];
      $return_var = 0;
      $cmd = 'ffmpeg -rtsp_transport tcp -loglevel fatal -i "' . $file . '" -c:v copy -bsf:a aac_adtstoasc -y -t 10 -movflags faststart ' . $newpath;
      exec($cmd, $output, $return_var);
      $result = "/plugins/frigate" . $path;
      log::add(__CLASS__, 'debug', "| Commande exécutée : " . $cmd);
      log::add(__CLASS__, 'debug', "| Sortie : " . implode("\n", $output));
      log::add(__CLASS__, 'debug', "| Code de retour : " . $return_var);
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
        log::add(__CLASS__, 'debug', "| Échec de la création du répertoire.");
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
        log::add(__CLASS__, 'debug', "| Le fichier a été enregistré : " . $lien);
      } else {
        log::add(__CLASS__, 'debug', "| Échec de l'enregistrement du fichier : " . $lien);
        $result = "error";
      }
    } else {
      log::add(__CLASS__, 'debug', "| Échec du téléchargement du fichier : " . $lien);
      $result = "error";
    }

    return $result;
  }

  public static function createSnapshot($eqLogic)
  {
    log::add(__CLASS__, 'debug', "---------------------------------------------------");
    log::add(__CLASS__, 'debug', "| Créer snapshot");
    $camera = $eqLogic->getConfiguration('name');
    $file = $eqLogic->getConfiguration('img');
    $timestamp = microtime(true);
    $formattedTimestamp = sprintf('%.6f', $timestamp);
    $startTime = time();
    $endTime = $startTime;
    $uniqueId = self::createUniqueId($formattedTimestamp);
    // create snapshot
    $url = frigate::saveURL($uniqueId, null, $camera, 3, $file);
    $urlClip = "";
    // mise a jour des commandes
    log::add(__CLASS__, 'debug', "| Mise à jour de la commande.");
    $eqLogic->getCmd(null, 'info_url_capture')->event("/var/www/html" . $url);
    $eqLogic->getCmd(null, 'info_label')->event("capture");
    $eqLogic->getCmd(null, 'info_score')->event(0);
    $eqLogic->getCmd(null, 'info_topscore')->event(0);
    $eqLogic->getCmd(null, 'info_duree')->event(0);

    // Creation de l'evenement  dans la DB
    log::add(__CLASS__, 'debug', "| Creéation d'un nouveau évènement Frigate pour l'event ID: " . $uniqueId);
    $frigate = new frigate_events();
    $frigate->setCamera($camera);
    $frigate->setLasted($url);
    $frigate->setHasClip(0);
    $frigate->setClip($urlClip);
    $frigate->setHasSnapshot(1);
    $frigate->setSnapshot($url);
    $frigate->setStartTime($startTime);
    $frigate->setEndTime($endTime);
    $frigate->setEventId($timestamp);
    $frigate->setLabel("capture");
    $frigate->setThumbnail($url);
    $frigate->setTopScore(0);
    $frigate->setScore(0);
    $frigate->setType("end");
    $frigate->setIsFavorite(0);
    $frigate->save();
    log::add(__CLASS__, 'debug', "---------------------------------------------------");
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
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $eqlogicId = $frigate->getId();
    $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "", 0, null, 0);
    $version = $cmd->execCmd();
    if ($version != config::byKey('frigate_version', 'frigate')) {
      config::save('frigate_version', $version, 'frigate');
    }

    foreach ($_message[self::getTopic()] as $key => $value) {
      log::add(__CLASS__, 'info', 'handle Mqtt Message pour : :b:' . $key . ':/b:');
      log::add(__CLASS__, 'debug', 'handle Mqtt Message pour : :b:' . $key . ':/b: = ' . json_encode($value));

      switch ($key) {
        case 'events':
          if (version_compare($version, "0.14", "<")) {
            log::add(__CLASS__, 'info', ' => Traitement mqtt events <0.14');
            self::getEvents(true, [$value['after']], $value['type']);
            event::add('frigate::events', array('message' => 'mqtt_update', 'type' => 'event'));
          } else {
            log::add(__CLASS__, 'info', ' => Traitement mqtt events non exécuté, version >= 0.14, utilisation de reviews.');
          }
          break;

        case 'reviews':
          $eventId = $value['after']['data']['detections'][0];
          $eventType = $value['type'];
          log::add(__CLASS__, 'info', ' => Traitement mqtt manual event <=');
          self::getEvent($eventId, $eventType);
          event::add('frigate::events', array('message' => 'mqtt_update_manual', 'type' => 'event'));
          break;

        case 'stats':
          log::add(__CLASS__, 'info', ' => Traitement mqtt stats');
          self::majStatsCmds($value, true);
          break;

        default:
          $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $key, "frigate");
          if (!is_object($eqCamera)) {
            continue 2;
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
        log::add(__CLASS__, 'debug', "Fichier supprimé: " . $file->getPathname());
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



  private static function yamlToJsonFromUrl($yamlUrl)
  {
    // Télécharger le contenu YAML depuis l'URL
    $yamlContent = file_get_contents($yamlUrl);
    // Vérifier si le téléchargement a réussi
    if ($yamlContent === false) {
      log::add(__CLASS__, 'error', "yamlToJsonFromUrl : Failed to retrieve YAML from URL");
      return json_encode(["error" => "Failed to retrieve YAML from URL: $yamlUrl"]);
    }
    // Parser le contenu YAML
    $yamlArray = yaml_parse($yamlContent);
    // Vérifier si le parsing est réussi
    if ($yamlArray === false) {
      log::add(__CLASS__, 'error', "yamlToJsonFromUrl : Invalid YAML content or file not found");
      return json_encode(["error" => "Invalid YAML content or file not found"]);
    }
    // Convertir le tableau PHP en JSON
    $jsonContent = json_encode($yamlArray, JSON_PRETTY_PRINT);
    return $jsonContent;
  }

  private static function checkFriagetVersion()
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";
    $stats = self::getcURL("Stats", $resultURL);
    $version = strstr($stats['service']['version'], '-', true);
    $latestVersion = $stats['service']['latest_version'];
    if (version_compare($version, $latestVersion, "<")) {
      message::add('frigate', __("Une nouvelle version de Frigate (" . $latestVersion . ") est disponible.", __FILE__), null, null);
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

  private function parseEventParameters($_options)
  {
    // Valeurs par défaut
    // TODO : récupérer les valeurs par défaut pour chaque caméra (et plus au niveau de la config plugin)
    $defaults = [
      'label' => config::byKey('defaultLabel', 'frigate'),
      'video' => (int)config::byKey('defaultVideo', 'frigate'),
      'duration' => (int)config::byKey('defaultDuration', 'frigate'),
      'score' => (int)config::byKey('defaultScore', 'frigate')
    ];

    // Récupération du label
    if ($_options['title'] != '') $defaults['label'] = $_options['title'];

    // Récupération des paramètres dans $_options['message']
    $params = explode('|', $_options['message']);
    foreach ($params as $param) {
      list($key, $value) = explode('=', $param);
      $key = trim($key);
      $value = trim($value);

      if ($key === 'video') {
        if (is_numeric($value)) {
          $defaults['video'] = (int)$value;
        }
      }

      if ($key === 'duration') {
        if (is_numeric($value) && $value > 0) {
          $defaults['duration'] = (int)$value;
        }
      }

      if ($key === 'score') {
        if (is_numeric($value) && $value >= 0 && $value <= 100) {
          $defaults['score'] = (int)$value;
        }
      }
    }

    return $defaults;
  }

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
      case 'action_enable_camera':
      case 'action_disable_camera':
        //config/set?cameras.frigate1.enabled=true
        $enable = $logicalId === 'action_enable_camera' ? 1 : 0;
        $response = frigate::enableCamera($camera, $enable);
        if ($response['success']) {
          $infoCamera = $logicalId === 'action_enable_camera' ? 1 : 0;
          $frigate->getCmd(null, 'enable_camera')->event($infoCamera);
        }
        break;
      case 'action_toggle_camera':
        $enable = 1 - $frigate->getCmd(null, 'enable_camera')->execCmd();
        $response = frigate::enableCamera($camera, $enable);
        if ($response['success']) {
          $frigate->getCmd(null, 'enable_camera')->event($enable);
        }
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
        //score=12|video=1|duration=20
        $eventParams = self::parseEventParameters($_options);
        $result = frigate::createEvent($camera, $eventParams['label'], $eventParams['video'], $eventParams['duration'], $eventParams['score']);
        $deamon_info = frigate::deamon_info();
        if ($deamon_info['launchable'] === 'nok') {
          log::add('frigate', 'debug', "| action_make_api_event result = " . json_encode($result));
          frigate::getEvent($result['event_id']);
        }
        break;
      case 'action_create_snapshot':
        frigate::createSnapshot($frigate);
        break;
      default:
        log::add(__CLASS__, 'error', "Action inconnue. Action: $logicalId");
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
