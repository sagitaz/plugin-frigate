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
  /*     * ***********************Methode static*************************** */
  /**
   * Initialise la configuration générale du plugin avec des valeurs par défaut.
   * @return void
   */
  public static function setConfig(): void
  {
    $defaultConfigs = [
      'URL'                  => '',
      'port'                 => '5000',
      'recovery_days'        => '7',
      'remove_days'          => '7',
      'datas_weight'         => '500',
      'refresh_snapshot'     => '5',
      'cron'                 => '5',
      'cron::run'            => '0',
      'excludeBackup'        => '1',
      'event::displayVideo'  => '1',
      'event::confirmDelete' => '1',
    ];

    if (class_exists('mqtt2')) {
      $defaultConfigs['topic']     = 'frigate';
      $defaultConfigs['presetMax'] = '0';
    }

    self::configSave($defaultConfigs);
  }

  /**
   * Initialise l'activation par défaut des différents crons Jeedom.
   * @return void
   */
  public static function setConfigCron(): void
  {
    $defaultCrons = [
      'functionality::cron::enable'       => '0',
      'functionality::cron5::enable'      => '0',
      'functionality::cron10::enable'     => '0',
      'functionality::cron15::enable'     => '0',
      'functionality::cron30::enable'     => '0',
      'functionality::cronHourly::enable' => '0',
      'functionality::cronDaily::enable'  => '1',
    ];

    self::configSave($defaultCrons);
  }

  /**
   * Parcourt un tableau de configurations et les sauvegarde si elles n'existent pas.
   * @param array<string, string> $array Tableau associatif [clé => valeur]
   * @return void
   */
  private static function configSave(array $array = []): void
  {
    foreach ($array as $key => $value) {
      $current = config::byKey($key, 'frigate');

      if ($current === null || $current === '') {
        config::save($key, $value, 'frigate');
      }
    }
  }
  private static function execCron($frequence)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START CRON:/fg: ════════════════════════");
    log::add(__CLASS__, 'debug', "║ Exécution du cron : {$frequence}");
    if (config::byKey("cron::run", 'frigate')) {
      log::add(__CLASS__, 'debug', "║ Un cron est deja en cours d'exécution, on n'exécute pas de nouveau.");
      config::save('cron::run', 0, 'frigate');
      log::add(__CLASS__, 'debug', "╚════════════════════════ END CRON ════════════════════════");
      return;
    }
    config::save('cron::run', 1, 'frigate');
    // Si on utilise MQTT2, les crons 1, 5, 10 et 15 ne sont pas utilisés
    if (class_exists('mqtt2')) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['launchable'] === 'ok' && (
        $frequence === "functionality::cron::enable" ||
        $frequence === "functionality::cron5::enable" ||
        $frequence === "functionality::cron10::enable" ||
        $frequence === "functionality::cron15::enable")) {
        log::add(__CLASS__, 'debug', "║ Les crons 1, 5, 10 et 15 sont désactivés avec MQTT et ne sont pas utilisés.");
        config::save('cron::run', 0, 'frigate');
        log::add(__CLASS__, 'debug', "╚════════════════════════ END CRON ═══════════════════");
        return;
      }
    }

    // Exécution des autres fréquences et nettoyage
    self::cleanFolderData();
    self::cleanAllOldestFiles();

    // Si la fréquence n'est pas parmi les crons désactivés, exécuter cleanByType
    if (!($frequence === "functionality::cron::enable" ||
      $frequence === "functionality::cron5::enable" ||
      $frequence === "functionality::cron10::enable" ||
      $frequence === "functionality::cron15::enable")) {
      self::cleanByType();
      self::cleanByType("update");
    }

    // Exécution des actions si Frigate est disponible
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!empty($frigate) && config::byKey($frequence, 'frigate', 0) == 1) {
      $cmd = $frigate->getCmd(null, 'info_Cron');
      $cmdEnabled = $frigate->getCmd(null, 'info_enabled');
      $execute = "1";
      $enabled = "1";
      if (is_object($cmd)) {
        $execute = $cmd->execCmd();
      }
      if (is_object($cmdEnabled)) {
        $enabled = $cmdEnabled->execCmd();
      }

      if ($execute == "1" && $enabled == "1") {
        self::getEvents();
        self::getStats();
      }
    }

    config::save('cron::run', 0, 'frigate');
    log::add(__CLASS__, 'debug', "╚════════════════════════ END CRON ═══════════════════");
  }


  private static function isFrigateServerAvailable()
  {
    $urlFrigate = self::getUrlFrigate();
    if ($urlFrigate === false) {
      return false;
    }

    $ch = curl_init("http://" . $urlFrigate . "/api/version");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300 && !empty($result);
  }

  // Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron::enable');
  }
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron5 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron5::enable');
  }
  // Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron10 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron10::enable');
  }
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron15 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron15::enable');
  }
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron30 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron30::enable');
  }
  // Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. CronHourly non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cronHourly::enable');
  }
  // Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. CronDaily non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::checkFrigateVersion();
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
    $CommunityInfo .= "```\n";
    $CommunityInfo .= 'URL : ' . self::getUrlFrigate() . "\n";
    $CommunityInfo .= 'MQTT topic : ' . config::byKey('topic', 'frigate') . "\n";
    $CommunityInfo .= 'Frigate : ' . config::byKey('frigate_version', 'frigate') . "\n";
    $CommunityInfo .= 'Plugin : ' . config::byKey('pluginVersion', 'frigate') . "\n";
    $CommunityInfo .= "``` \n";
    $CommunityInfo .= "<b>Informations à ajouter</b> \n";
    $CommunityInfo .= "Afin de traiter au mieux votre demande d'aide, merci d'ajouter les logs du plugin Frigate en mode debug et nettoyé, pas de 6 mois. \n";
    $CommunityInfo .= "Vous pouvez également ajouter les logs HTTP_ERROR s'ils comportent des infos sur Frigate. \n";
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
    // on nettoie l'url si elle contient http:// ou https://
    $url = preg_replace('#^https?://#', '', $url);
    config::save('URL', $url, 'frigate');
    $port = config::byKey('port', 'frigate');


    if ($this->getLogicalId() != 'eqFrigateStats' && $this->getLogicalId() != 'eqFrigateEvents') {
      if ($this->getConfiguration('ptz') == '') {
        $this->setConfiguration('ptz', '0');
      }
      // on verifie le preset et on le save
      $preset = ($this->getConfiguration('presetMax') <= "10") ? $this->getConfiguration('presetMax') : "10";
      $this->setConfiguration('presetMax', $preset);

      $name = $this->getConfiguration('name');
      $bbox = $this->getConfiguration('bbox', 0);
      $timestamp = $this->getConfiguration('timestamp', 1);
      $zones = $this->getConfiguration('zones', 0);
      $mask = $this->getConfiguration('mask', 0);
      $motion = $this->getConfiguration('motion', 0);
      $regions = $this->getConfiguration('regions', 0);
      $quality = $this->getConfiguration('quality', 70);

      $urlLatest = "http://" . $url . ":" . $port . "/api/" . $name . "/latest.jpg?timestamp=" . $timestamp . "&bbox=" . $bbox . "&zones=" . $zones . "&mask=" . $mask . "&motion=" . $motion . "&regions=" . $regions;
      $img = urlencode($urlLatest);
      $this->setConfiguration('img', $img);

      // maj lien et cmd snapshot
      $urlStream = "";
      $cmd = cmd::byEqLogicIdCmdName($this->getId(), "SNAPSHOT LIVE");
      if (is_object($cmd)) {
        $urlStream = $cmd->execCmd();

        if ($this->getConfiguration('urlStream') == '' || $this->getConfiguration('urlStream') != $urlStream) {
          $urlJeedom = network::getNetworkAccess('external');
          if ($urlJeedom == "") {
            $urlJeedom = network::getNetworkAccess('internal');
          }
          $urlStream = "/plugins/frigate/core/ajax/frigate.proxy.php?url=" . $img;
          $this->setConfiguration('urlStream', $urlStream);
          $cmd->event($urlJeedom . $urlStream);
          $cmd->save();
        }
      }

      // maj lien et cmd rtsp    
      $rtspStream = "";
      $cmd = cmd::byEqLogicIdCmdName($this->getId(), "RTSP");
      if (is_object($cmd)) {
        $rtspStream = $cmd->execCmd();

        $rtsp = $this->getConfiguration('cameraStreamAccessUrl');
        if ($rtsp == '' || $rtsp != $rtspStream) {
          if ($rtsp == '') {
            $rtsp = 'rtsp://' . $url . ':8554/' . $this->getConfiguration('name');
          }

          $this->setConfiguration('cameraStreamAccessUrl', $rtsp);

          $cmd->event($rtsp);
          $cmd->save();
        }
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
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START REMOVE EQLOGIC:/fg: ═══════════════════");
    foreach ($events as $event) {
      if ($event->getCamera() == $name) {
        $event->setIsFavorite(0);
        $event->save();
        $eventId = $event->getEventId();
        self::cleanDbEvent($eventId);
      }
    }
    log::add(__CLASS__, 'debug', "╚════════════════════════ END REMOVE EQLOGIC ═══════════════════");
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
    $type = $this->detectType();
    $panel = $this->isPanelVersion($_version);
    if ($panel) $_version = 'dashboard';

    if ($type !== 'camera') return parent::toHtml($_version);

    $replace = $this->preToHtml($_version);
    if (!is_array($replace)) return $replace;

    $replace['#cameraEqlogicId#'] = $this->getLogicalId();
    $replace['#cameraName#']      = $this->getConfiguration("name");
    $replace['#imgUrl#']          = $this->getConfiguration("img");
    $replace['#enabled#']         = $this->getCmd('info', 'info_enabled') ? $this->getCmd('info', 'info_enabled')->execCmd() : 1;
    $replace['#refresh#']         = (float)(config::byKey('refresh_snapshot', 'frigate')) * 1000;

    $replace['#actions#']      = $this->buildActions();
    $replace['#iaActions#'] = $this->buildIaActions();
    $replace['#detectNow#']    = $this->buildDetectNow();
    $replace['#actionsPreset#'] = $this->buildPresetsSelect();
    $replace['#ptzWidget#']    = $this->buildPtzWidget();
    $replace['#ptzZoom#']      = $this->buildPtzZoom();
    $replace['#actionsModal#'] = $replace['#actions#'] . $this->buildPtzModal() . $replace['#actionsPreset#'];

    return $this->renderTemplate($replace, $_version, $panel);
  }

  private function detectType(): string
  {
    return strpos($this->getLogicalId(), "eqFrigateCamera_") !== false ? "camera" : "";
  }

  private function isPanelVersion(string &$_version): bool
  {
    if ($_version === 'panel') {
      $_version = 'dashboard';
      return true;
    }
    return false;
  }

  // Construit un bouton icône générique
  private function buildBtnIcon(string $icon, string $title, int $cmdId, string $cssClass): string
  {
    return '<div class="btn-icon">'
      . '<i class="' . $icon . ' ' . $cssClass . $this->getId() . '" title="' . $title . '" onclick="execAction(' . $cmdId . ')"></i>'
      . '</div>';
  }

  // Construit un toggle start/stop selon l'état d'une info cmd
  private function buildToggleAction(string $startLogical, string $stopLogical, string $infoLogical, string $iconOn, string $iconOff, string $titleOn, string $titleOff): string
  {
    $on   = $this->getCmd('action', $startLogical);
    $off  = $this->getCmd('action', $stopLogical);
    $etat = $this->getCmd('info', $infoLogical);

    if (!is_object($on) || !is_object($off)) return '';
    if ($on->getIsVisible() != 1 || $off->getIsVisible() != 1) return '';

    if ($etat->execCmd() == 0) {
      return $this->buildBtnIcon($iconOn, $titleOn, $on->getId(), 'iconActionOff');
    } else {
      return $this->buildBtnIcon($iconOff, $titleOff, $off->getId(), 'iconAction');
    }
  }

  private function buildActions(): string
  {
    $html  = $this->buildToggleAction('action_start_recordings', 'action_stop_recordings', 'info_recordings', 'fas fa-video', 'fas fa-video', 'recording ON', 'recording OFF');
    $html .= $this->buildToggleAction('action_start_snapshots', 'action_stop_snapshots', 'info_snapshots', 'fas fa-camera', 'fas fa-camera', 'snapshot ON', 'snapshot OFF');
    $html .= $this->buildToggleAction('action_start_detect', 'action_stop_detect', 'info_detect', 'fas fa-user-shield', 'fas fa-user-shield', 'detection ON', 'detection OFF');
    $html .= $this->buildToggleAction('action_start_audio', 'action_stop_audio', 'info_audio', 'fas fa-volume-off', 'fas fa-volume-down', 'audio ON', 'audio OFF');
    $html .= $this->buildToggleAction('action_start_motion', 'action_stop_motion', 'info_motion', 'fas fa-male', 'fas fa-walking', 'motion ON', 'motion OFF');
    // Boutons simples
    foreach (
      [
        ['action_make_api_event',  'fas fa-camera-retro', __("Créer un évènement", __FILE__)],
        ['action_create_snapshot', 'fas fa-image',        __("Créer une capture", __FILE__)],
      ] as [$logical, $icon, $title]
    ) {
      $cmd = $this->getCmd('action', $logical);
      if (is_object($cmd) && $cmd->getIsVisible() == 1) {
        $html .= $this->buildBtnIcon($icon, $title, $cmd->getId(), 'iconActionOff');
      }
    }

    return $html;
  }

  private function buildIaToggleRow(string $startLogical, string $stopLogical, string $infoLogical, string $label): string
  {
    $on   = $this->getCmd('action', $startLogical);
    $off  = $this->getCmd('action', $stopLogical);
    $etat = $this->getCmd('info', $infoLogical);

    if (!is_object($on) || !is_object($off) || !is_object($etat)) return '';
    if ($on->getIsVisible() != 1 || $off->getIsVisible() != 1) return '';

    $isActive = $etat->execCmd() != 0;
    $cmdId    = $isActive ? $off->getId() : $on->getId();

    return '<div class="ia-toggle-row">'
      . '<span class="ia-toggle-label">' . $label . '</span>'
      . '<i class="' . ($isActive ? 'fas fa-toggle-on' : 'fas fa-toggle-off') . ' ia-toggle-icon" onclick="execAction(' . $cmdId . ')"></i>'
      . '</div>';
  }

  private function buildIaActions(): string
  {
    return $this->buildIaToggleRow('action_start_review_alerts',       'action_stop_review_alerts',       'info_review_alerts',       '{{Review alerts}}')
      . $this->buildIaToggleRow('action_start_review_detections',   'action_stop_review_detections',   'info_review_detections',   '{{Review detections}}')
      . $this->buildIaToggleRow('action_start_review_descriptions', 'action_stop_review_descriptions', 'info_review_descriptions', '{{Review descriptions}}')
      . $this->buildIaToggleRow('action_start_object_descriptions', 'action_stop_object_descriptions', 'info_object_descriptions', '{{Object descriptions}}')
      . $this->buildIaToggleRow('action_start_enabled', 'action_stop_enabled', 'info_enabled', '{{Activations}}');
  }
  private function buildDetectNow(): string
  {
    $html = '';
    foreach ($this->getCmd('info') as $cmd) {
      $logicalId = $cmd->getLogicalId();
      if (strpos($logicalId, 'info_detect_') !== 0 || $logicalId === 'info_detect_all') continue;
      if ($cmd->getIsVisible() != 1 || $cmd->execCmd() != 1) continue;

      $icon = $cmd->getDisplay("icon", "fas fa-exclamation-circle");
      $icon = preg_replace('/<i class="([^"]+)"><\/i>/', '$1', $icon);
      $html .= '<div class="btn-detect"><i class="' . $icon . ' iconDetect' . $this->getId() . '"></i></div>';
    }
    return $html;
  }

  private function buildPresetsSelect(): string
  {
    $options   = '';
    $hasPresets = false;

    for ($i = 0; $i <= 10; $i++) {
      $preset = $this->getCmd('action', 'action_preset_' . $i);
      if (is_object($preset) && $preset->getIsVisible() == 1) {
        $hasPresets = true;
        $options .= '<option value="' . $preset->getId() . '">' . $preset->getName() . '</option>';
      }
    }

    foreach (cmd::byEqLogicIdAndLogicalId($this->getId(), "action_http", true) as $httpCmd) {
      if ($httpCmd && $httpCmd->getIsVisible() == 1) {
        $hasPresets = true;
        $options .= '<option value="' . $httpCmd->getId() . '">' . $httpCmd->getName() . '</option>';
      }
    }

    if (!$hasPresets) return '';

    return '<div class="btn-icon">'
      . '<select class="preset-select' . $this->getId() . '" id="presetSelect' . $this->getId() . '" onchange="execSelectedPreset' . $this->getId() . '()">'
      . '<option value="" disabled selected hidden>{{action}}</option>'
      . $options
      . '</select>'
      . '</div>';
  }

  // PTZ : définition des boutons [logical, iconWidget, cssWidget, titleWidget, iconModal]
  private function ptzButtonsConfig(): array
  {
    return [
      ['action_ptz_down',  'fas fa-caret-down',  'iconPTZdown',  'btn-ptz-down',  'PTZ DOWN',  'fas fa-chevron-circle-down'],
      ['action_ptz_up',    'fas fa-caret-up',    'iconPTZup',    'btn-ptz-up',    'PTZ UP',    'fas fa-chevron-circle-up'],
      ['action_ptz_left',  'fas fa-caret-left',  'iconPTZleft',  'btn-ptz-left',  'PTZ LEFT',  'fas fa-chevron-circle-left'],
      ['action_ptz_right', 'fas fa-caret-right', 'iconPTZright', 'btn-ptz-right', 'PTZ RIGHT', 'fas fa-chevron-circle-right'],
      ['action_ptz_stop',  'fas fa-stop',        'iconPTZstop',  'btn-ptz-stop',  'PTZ STOP',  'fas fa-stop-circle'],
    ];
  }

  private function buildPtzWidget(): string
  {
    $hasPtz = false;
    foreach ($this->ptzButtonsConfig() as [$logical]) {
      $cmd = $this->getCmd('action', $logical);
      if (is_object($cmd) && $cmd->getIsVisible() == 1) {
        $hasPtz = true;
        break;
      }
    }

    $html = $hasPtz ? '<div class="circle-overlay"></div>' : '';

    foreach ($this->ptzButtonsConfig() as [$logical, $icon, $css, $btnClass, $title]) {
      $cmd = $this->getCmd('action', $logical);
      if (!is_object($cmd) || $cmd->getIsVisible() != 1) continue;
      $html .= '<div class="' . $btnClass . '">'
        . '<i class="' . $icon . ' ' . $css . $this->getId() . '" title="' . $title . '" onclick="execAction(' . $cmd->getId() . ')"></i>'
        . '</div>';
    }
    return $html;
  }

  private function buildPtzModal(): string
  {
    $html = '';
    foreach (
      array_merge($this->ptzButtonsConfig(), [
        ['action_ptz_zoom_in',  '', '', '', 'PTZ ZOOM IN',  'fas fa-plus-circle'],
        ['action_ptz_zoom_out', '', '', '', 'PTZ ZOOM OUT', 'fas fa-minus-circle'],
      ]) as [$logical,,,, $title, $iconModal]
    ) {
      $cmd = $this->getCmd('action', $logical);
      if (!is_object($cmd) || $cmd->getIsVisible() != 1) continue;
      $html .= $this->buildBtnIcon($iconModal, $title, $cmd->getId(), 'iconActionOff');
    }
    return $html;
  }

  private function buildPtzZoom(): string
  {
    $html = '';
    foreach (
      [
        ['action_ptz_zoom_in',  'fas fa-plus',  'iconZoomIn',  'PTZ ZOOM IN',  'fas fa-plus-circle'],
        ['action_ptz_zoom_out', 'fas fa-minus', 'iconZoomOut', 'PTZ ZOOM OUT', 'fas fa-minus-circle'],
      ] as [$logical, $icon, $css, $title]
    ) {
      $cmd = $this->getCmd('action', $logical);
      if (!is_object($cmd) || $cmd->getIsVisible() != 1) continue;
      $btnClass = ($logical === 'action_ptz_zoom_in') ? 'btn-ptz-zoom-in' : 'btn-ptz-zoom-out';
      $html .= '<div class="' . $btnClass . '"><i class="' . $icon . ' ' . $css . $this->getId() . '" title="' . $title . '" onclick="execAction(' . $cmd->getId() . ')"></i></div>';
    }
    return $html;
  }



  private function renderTemplate(array $replace, string $_version, bool $panel): string
  {
    $version  = "dashboard";
    $imgOnly  = $panel
      ? $this->getConfiguration('templatePanelImgOnly', 0)
      : $this->getConfiguration('templateDashboardImgOnly', 0);

    if ($imgOnly == 1) {
      foreach (['#detectNow#', '#ptzWidget#', '#ptzZoom#', '#actions#', '#actionsPreset#'] as $key) {
        $replace[$key] = '';
      }
    }

    $templateName = $panel ? 'widgetPanel' : 'widgetCamera';
    $cacheKey     = ($panel ? 'widgetPanel' : 'widgetCamera') . $_version . $this->getId();

    $html = template_replace($replace, getTemplate('core', $version, $templateName, __CLASS__));
    $html = translate::exec($html, 'plugins/frigate/core/template/' . $version . '/' . $templateName . '.html');
    $html = $this->postToHtml($_version, $html);
    cache::set($cacheKey, $html, 0);
    return $html;
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
      log::add(__CLASS__, "error", "║ Erreur: L'URL ne peut être vide.");
      return false;
    }
    $port = config::byKey('port', 'frigate');
    if ($port == "") {
      log::add(__CLASS__, "error", "║ Erreur: Le port ne peut être vide");
      return false;
    }
    $urlFrigate = $url . ":" . $port;
    return $urlFrigate;
  }

  public static function addMessages()
  {
    message::add('frigate', __("Merci d'avoir installé le plugin. Pour toutes les demandes d'aide, veuillez contacter le support sur Discord ou sur Community.", __FILE__));
    $system = system::getOsVersion();
    if (version_compare($system, "11", "<")) {
      message::add('frigate', __("Attention, vous utilisez la version " . $system . " de Debian, aucun support n'est disponible. La version 11 de Debian est recommandée.", __FILE__));
    }
    $jeedom = jeedom::version();
    if (version_compare($jeedom, "4.4", "<")) {
      message::add('frigate', __("Attention, vous utilisez la version " . $jeedom . " de Jeedom. La version 4.4.x de Jeedom est recommandée.", __FILE__));
    }
  }

  public static function publish_camera_message(string $camera, string $subTopic, string $payload)
  {
    self::publish_message("{$camera}/{$subTopic}", $payload);
  }

  public static function publish_message(string $subTopic, string $payload)
  {
    log::add(__CLASS__, 'debug', "║ publish_message : " . self::getTopic() . "/{$subTopic} avec payload : {$payload}");
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
      log::add(__CLASS__, "error", "║ Erreur getcURL (" . $method . "): " . curl_error($ch));
      return null;
    }
    curl_close($ch);
    $response = $decodeJson ? json_decode($data, true) : $data;
    log::add(__CLASS__, 'debug', "║ " . $function . " : requête " . $method . " exécutée.");
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
      log::add(__CLASS__, "error", "║ Erreur: deletecURL" . curl_error($ch));
      die();
    }
    curl_close($ch);
    $response = json_decode($data, true);
    log::add(__CLASS__, 'debug', "║ Suppression sur le serveur Frigate : " . json_encode($response));
    return $response;
  }

  public static function getStats()
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START STATS:/fg: ═══════════════════");
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";
    $stats = self::getcURL("Stats", $resultURL);
    if ($stats == null) {
      log::add(__CLASS__, 'debug', "║ Erreur: Impossible de récupérer les stats de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return;
    }
    self::majStatsCmds($stats);
    log::add(__CLASS__, 'debug', "╚════════════════════════ END STATS ═══════════════════");
  }

  public static function getPresets($camera)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START IMPORT PRESETS:/fg: ═══════════════════");
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/" . $camera . "/ptz/info";
    $presets = self::getcURL("Presets", $resultURL);
    if ($presets == null) {
      log::add(__CLASS__, 'debug', "║ Erreur: Impossible de récupérer les presets de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return;
    }
    log::add(__CLASS__, 'debug', "╚════════════════════════ END IMPORT PRESET ═══════════════════");
    return $presets;
  }

  public static function createEvent($camera, $label, $video = 1, $duration = 20, $score = 30, $subLabel = '')
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/events/" . $camera . "/" . rawurlencode($label) . "/create";

    $score = max(0, min(100, floatval($score)));
    $score = $score / 100;
    $duration = floatval($duration);
    $includeRecording = ($video == 1);

    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START CREATE EVENT:/fg: ═══════════════════");
    log::add(__CLASS__, 'debug', "║ label : {$label}");
    log::add(__CLASS__, 'debug', "║ score : {$score}");
    log::add(__CLASS__, 'debug', "║ duration : {$duration}");
    log::add(__CLASS__, 'debug', "║ video : {$video}");
    log::add(__CLASS__, 'debug', "║ include_recording : " . ($includeRecording ? "true" : "false"));
    log::add(__CLASS__, 'debug', "║ sub_label : {$subLabel}");

    $params = [
      'source_type' => 'api',
      'sub_label' => $subLabel,
      'score' => $score,
      'duration' => $duration,
      'include_recording' => $includeRecording
    ];
    $response = self::postcURL("CreateEvent", $resultURL, $params);

    log::add(__CLASS__, 'debug', "╚════════════════════════ END CREATE EVENT ═══════════════════");
    return $response;
  }

  // Méthodes de modification du fichier de configuration par API
  // Attention : Redémarrage Frigate nécessaire pour prise en compte
  // TODO : Ajouter des méthodes appelant cette méthode pour une modification de paramètres du fichier de configuration
  public static function saveConfig($config)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START SAVE CONFIG:/fg: ═══════════════════");
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config/set?{$config}";

    log::add(__CLASS__, 'debug', "║ url : {$resultURL}");
    $response = self::putcURL("saveConfig", $resultURL); //, $params);

    event::add('frigate::config', array('message' => 'api_config_update', 'type' => 'config'));

    log::add(__CLASS__, 'debug', "╚════════════════════════ END SAVE CONFIG ═══════════════════");
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
    if ($logs == null) {
      log::add(__CLASS__, 'debug', "║ Erreur: Impossible de récupérer les logs de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return;
    }
    return $logs;
  }

  private static function saveAndCompareEvents($filePath, $events)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START COMPARE EVENTS:/fg: ═══════════════════");
    log::add(__CLASS__, 'debug', "║ Sauvegarde et comparaison des événements dans le fichier : " . $filePath);
    if (file_exists($filePath)) {
      $oldEvents = json_decode(file_get_contents($filePath), true);
      $newEvents = array_udiff($events, $oldEvents, function ($a, $b) {
        return strcmp($a['start_time'], $b['start_time']);
      });
      if (!empty($newEvents)) {
        file_put_contents($filePath, json_encode(array_merge($oldEvents, $newEvents)));
        log::add(__CLASS__, 'debug', "║ Detection d'événements nouveaux : " . json_encode($newEvents));
      } else {
        log::add(__CLASS__, 'debug', "║ Aucun nouveau événement détecté.");
      }
      log::add(__CLASS__, 'debug', "╚════════════════════════ END COMPARE EVENTS ═══════════════════");
      return $newEvents;
    } else {
      file_put_contents($filePath, json_encode($events));
      log::add(__CLASS__, 'debug', "║ Fichier de sauvegarde créé : " . $filePath);
      log::add(__CLASS__, 'debug', "║ Sauvegarde des événements : " . json_encode($events));
      log::add(__CLASS__, 'debug', "╚════════════════════════ END COMPARE EVENTS ═══════════════════");
      return $events;
    }
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
      $recoveryDays = config::byKey('recovery_days', 'frigate');
      if ($recoveryDays == 0) {
        log::add(__CLASS__, 'debug', "║ Recupération des évènements sur 0 jour, processus stoppé.");
        log::add(__CLASS__, 'debug', "╚════════════════════════ END ═══════════════════");
        return;
      }

      $urlFrigate = self::getUrlFrigate();
      $resultURL = "{$urlFrigate}/api/events";
      $events = self::getcURL("Events", $resultURL);
      if ($events == null) {
        log::add(__CLASS__, 'debug', "║ Erreur: Impossible de récupérer les événements de Frigate.");
        log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
        return;
      }
      // Traiter les evenements du plus ancien au plus recent
      $events = array_reverse($events);
      $filePath = __DIR__ . '/../../data/frigate_events.json';
      // vérifier si le dossier existe sinon le créer
      if (!file_exists(dirname($filePath))) {
        mkdir(dirname($filePath), 0777, true);
      }
      $newEvents = self::saveAndCompareEvents($filePath, $events);
      if (empty($newEvents)) {
        return;
      }
      $events = $newEvents;
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
      if (!is_array($event)) {
        log::add(__CLASS__, 'error', "║ Erreur: Événement invalide, ce n'est pas un tableau.");
        log::add(__CLASS__, 'debug', "║ Événement concerné : " . json_encode($event));
        return false;
      }

      // On choisit start_time si dispo, sinon end_time
      $time = $event['start_time'] ?? $event['end_time'] ?? null;

      if ($time === null) {
        log::add(__CLASS__, 'error', "║ Erreur: Événement invalide, le champ start_time ou end_time est manquant.");
        log::add(__CLASS__, 'debug', "║ Événement concerné : " . json_encode($event));
        return false; // rien à comparer
      }

      return $time >= time() - $recoveryDays * 86400;
    });

    $filteredRecoveryEvents = array_values($filteredRecoveryEvents);

    foreach ($filteredRecoveryEvents as $event) {
      $frigate = frigate_events::byEventId($event['id']);

      log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START EVENT:/fg: ═══════════════════");

      $infos = self::getEventinfos($mqtt, $event, false, $type);

      if (!$frigate) {
        log::add(__CLASS__, 'debug', "║ Events (type=" . $type . ") => " . json_encode($event));
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
        log::add(__CLASS__, 'debug', "║ Evénement Frigate créé et sauvegardé, event ID: " . $event['id']);
      } else {
        $updated = false;

        $fieldsToUpdate = [
          'StartTime' => $infos["startTime"] ?? $infos['endTime'],
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
          'Lasted' => $infos["image"],
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
            // log::add(__CLASS__, 'debug', "║ BOX, ancienne valeur: " . $currentValue . ", nouvelle valeur: " . $newValue);
          }

          if ((is_null($currentValue) || $currentValue === '' || $currentValue != $newValue) && !is_null($newValue) && $newValue !== '') {
            log::add(__CLASS__, 'debug', "║ Mise à jour du champ '$field' pour event ID: " . $event['id'] . ". ancienne valeur: " . json_encode($currentValue) . ", nouvelle valeur: " . json_encode($newValue));
            $frigate->$setMethod($newValue);
            $updated = true;
            if ($field == 'Type' && $newValue != $frigate->getType()) {
              $infos = self::getEventinfos($mqtt, $event, true);
              $frigate->setSnapshot($infos["snapshot"]);
              $frigate->setClip($infos["clip"]);
              log::add(__CLASS__, 'debug', "║ Mise à jour forcé des champs snapshot et clip pour event ID: " . $event['id']);
              $frigate->save();
            }
          }
        }

        if ($updated) {
          $frigate->setData($event['data']);
          log::add(__CLASS__, 'debug', "║ Mise à jour du champ data pour event ID: " . $event['id']);

          // si data description existe, le mettre à jour aussi
          if (isset($event['data']['description'])) {
            log::add(__CLASS__, 'debug', "║ Mise à jour du champ recognition_description pour event ID: " . $event['id'] . ". ancienne valeur: " . json_encode($frigate->getRecognition_description()) . ", nouvelle valeur: " . json_encode($event['data']['description']));
            $frigate->setRecognition_description($event['data']['description']);
          }
          $frigate->save();
          self::majEventsCmds($frigate);
          log::add(__CLASS__, 'debug', "║ Evénement Frigate mis à jour et sauvegardé, event ID: " . $event['id']);
        } else {
          log::add(__CLASS__, 'debug', "║ Pas de mise à jour pour event ID: " . $event['id']);
        }
      }
      log::add(__CLASS__, 'debug', "╚════════════════════════ END EVENT ═══════════════════");
    }
  }

  private static function eventAdd($event, $eqLogicId)
  {

    $date = date("d-m-Y H:i:s", $event->getStartTime());
    $duree = round($event->getEndTime() - $event->getStartTime(), 0);
    $box = $event->getBox();
    $boxArray = is_array($box) ? $box : json_decode($box, true);

    $result = array(
      "id" => $event->getId(),
      "img" => $event->getLasted(),
      "camera" => $event->getCamera(),
      "label" => $event->getLabel(),
      "box" => $boxArray,
      "date" => $date,
      "duree" => $duree,
      "startTime" => $event->getStartTime(),
      "endTime" => $event->getEndTime(),
      "snapshot" => $event->getSnapshot(),
      "clip" => $event->getClip(),
      "thumbnail" => $event->getThumbnail(),
      "hasSnapshot" => $event->getHasSnapshot(),
      "hasClip" => $event->getHasClip(),
      "eventId" => $event->getEventId(),
      "score" => $event->getScore(),
      "top_score" => $event->getTopScore(),
      "type" => $event->getType(),
      "isFavorite" => $event->getIsFavorite() ?? 0,
      "zones" => $event->getZones() ?? '',
      "description" => $event->getRecognition_description()
    );


    event::add(
      'frigate::event',
      [
        'pluginId' => 'frigate',
        'type' => 'pluginEvent',
        'value' => [
          'eqlogicId' => $eqLogicId,
          'value' => $result
        ]
      ]
    );
  }

  public static function getEventInfos($mqtt, $event, $force = false, $type = "end")
  {
    $dir = dirname(__FILE__, 3) . "/data/" . $event['camera'];
    $sleep = config::byKey('sleep', 'frigate');
    if ($sleep < 0 || $sleep > 10) {
      $sleep = 5;
    } else {
      $sleep = intval($sleep);
    }
    // Fonction de vérification et téléchargement
    sleep($sleep);
    $img = self::processImage($dir, $event, true, $force);
    log::add(__CLASS__, "debug", "║ Thumbnail: " . json_encode($img));

    $snapshot = self::processImage($dir, $event,  false, $force);
    log::add(__CLASS__, "debug", "║ Snapshot: " . json_encode($snapshot));

    $clip = self::processClip($dir, $event, $type, $force);
    self::processPreview($dir, $event);

    // Gestion du end_time
    $endTime = !empty($event['end_time']) ? ceil($event['end_time']) : 0;

    // Calcul des scores
    $newTopScore = round(($mqtt ? $event['top_score'] : $event['data']['top_score']) * 100, 0);
    $newScore = round(($mqtt ? $event['score'] : $event['data']['score']) * 100, 0);

    // Calcul des zones
    $newZones = isset($event['zones']) && is_array($event['zones']) && !empty($event['zones'])
      ? implode(', ', $event['zones'])
      : "";

    // Retour des infos
    return array(
      "image" => isset($img['url']) ? $img['url'] : "",
      "thumbnail" => isset($img['url']) ? $img['url'] : "",
      "snapshot" => isset($snapshot['url']) ? $snapshot['url'] : "",
      "hasSnapshot" => isset($snapshot['has']) ? $snapshot['has'] : 0,
      "clip" => isset($clip['url']) ? $clip['url'] : "",
      "hasClip" => isset($clip['has']) ? $clip['has'] : 0,
      "startTime" => isset($event['start_time']) && is_numeric($event['start_time']) && ceil($event['start_time']) > 0 ? ceil($event['start_time']) : (isset($event['start_time']) ? $event['start_time'] : ""),
      "endTime" => $endTime,
      "topScore" => $newTopScore,
      "score" => $newScore,
      "zones" => $newZones,
      "label" => isset($event['label']) ? self::cleanLabel($event['label']) : ""
    );
  }

  private static function processImage($dir, $event, $isThumbnail = false, $force = false)
  {
    log::add(__CLASS__, 'debug', "║════════════════════════ :fg-success:Process Image:/fg: ═══════════════════");

    $id = $event['id'];
    $camera = $event['camera'];
    $type = $isThumbnail ? 'thumbnail' : 'snapshot';
    $basePath = $dir . '/' . $id . "_{$type}";
    $jpgPath = $basePath . '.jpg';
    $webpPath = $basePath . '.webp';

    // --- PRIORITÉ AU WEBP ---
    if (file_exists($webpPath) && !$force) {
      if (file_exists($jpgPath)) {
        unlink($jpgPath);
        log::add(__CLASS__, 'debug', "║ Suppression du fichier JPG (doublon) pour $type ID: $id");
      }
      log::add(__CLASS__, 'debug', "║ Fichier WEBP déjà existant pour $type ID: $id");
      return ['url' => "/plugins/frigate/data/$camera/{$id}_{$type}.webp", 'has' => 1];
    }

    // --- Vérifie si un fichier (jpg ou webp) existe sinon téléchargement ---
    if (!file_exists($jpgPath) && !file_exists($webpPath) || $force) {
      log::add(__CLASS__, 'debug', "║ Aucun fichier local trouvé pour $type ID: $id");

      // Pour les snapshots seulement, on vérifie has_snapshot avant de télécharger
      if (!$isThumbnail) {
        if ($event['has_snapshot'] != "true") {
          log::add(__CLASS__, 'debug', "║ Has Snapshot: false → téléchargement annulé pour ID: $id");
          return ['url' => 'null', 'has' => 0];
        }
      }

      log::add(__CLASS__, 'debug', "║ Téléchargement du fichier $type pour ID: $id");
      $img = self::saveURL($id, $isThumbnail ? null : "snapshot", $camera, $isThumbnail);
      return ['url' => $img == "error" ? "null" : $img, 'has' => ($img != "error") ? 1 : 0];
    }

    // --- Fichier déjà présent ---
    $ext = file_exists($webpPath) ? 'webp' : 'jpg';
    log::add(__CLASS__, 'debug', "║ Fichier $ext trouvé localement pour $type ID: $id");

    return ['url' => "/plugins/frigate/data/$camera/{$id}_{$type}.$ext", 'has' => 1];
  }

  private static function processClip($dir, $event, $type, $force)
  {
    log::add(__CLASS__, 'debug', "║════════════════════════ :fg-success:Process Clip:/fg: ═══════════════════");

    if ($type != "end") {
      log::add(__CLASS__, 'debug', "║ Pas de clip, le type n'est pas 'end' " . json_encode($event));
      return ['url' => "null", 'has' => 0];
    }

    if (!file_exists($dir . '/' . $event['id'] . '_clip.mp4') || $force) {
      log::add(__CLASS__, 'debug', "║ Fichier clip non trouvé: " . $dir . '/' . $event['id'] . '_clip.mp4');
      if ($event['has_clip'] == "true") {
        $clip = self::saveURL($event['id'], "clip", $event['camera']);
        if ($clip == "error") return ['url' => "null", 'has' => 0];

        $duration = self::getVideoDuration($dir . '/' . $event['id'] . '_clip.mp4');
        if ($duration !== false) {
          log::add(__CLASS__, 'debug', "║ La durée de la video est de " . gmdate("H:i:s", $duration));
        }
        return ['url' => $clip, 'has' => 1];
      } else {
        log::add(__CLASS__, 'debug', "║ Has Clip: false, telechargement annulé");
        return ['url' => "null", 'has' => 0];
      }
    }
  }
  private static function processPreview($dir, $event)
  {
    log::add(__CLASS__, 'debug', "║════════════════════════ :fg-success:Process Preview:/fg: ═══════════════════");

    if (!file_exists($dir . '/' . $event['id'] . '_preview.gif')) {
      log::add(__CLASS__, 'debug', "║ Fichier preview non trouvé: " . $dir . '/' . $event['id'] . '_preview.gif');
      $preview = self::saveURL($event['id'], "preview", $event['camera']);
      return ['url' => $preview == "error" ? "null" : $preview, 'has' => $preview != "error"];
    }
    return "/plugins/frigate/data/" . $event['camera'] . "/" . $event['id'] . '_preview.gif';
  }

  private static function processJpgImage($filePath, $height = null, $quality = 100, $convertToWebp = false, $isThumbnail = false)
  {
    if (!file_exists($filePath)) {
      log::add(__CLASS__, 'debug', "║ processJpgImage : fichier introuvable → $filePath");
      return null;
    }

    log::add(__CLASS__, 'debug', "║ :b:Traitement de l'image:/b: $filePath (" . ($isThumbnail ? "thumbnail" : "snapshot") . ")");

    // --- Vérification du format réel ---
    $imgInfo = @getimagesize($filePath);
    if ($imgInfo === false) {
      log::add(__CLASS__, 'debug', "║ Fichier non reconnu comme image → $filePath");
      return null;
    }

    $mime = $imgInfo['mime'];
    switch ($mime) {
      case 'image/jpeg':
        $source = @imagecreatefromjpeg($filePath);
        break;
      case 'image/png':
        $source = @imagecreatefrompng($filePath);
        break;
      case 'image/webp':
        $source = @imagecreatefromwebp($filePath);
        break;
      default:
        log::add(__CLASS__, 'debug', "║ Format d’image non supporté ($mime) → $filePath");
        return null;
    }

    if (!$source) {
      log::add(__CLASS__, 'debug', "║ Impossible de charger le fichier image → $filePath");
      return null;
    }

    $width = imagesx($source);
    $origHeight = imagesy($source);
    $newImage = $source;

    // --- Redimensionnement uniquement si ce n’est PAS un thumbnail ---
    if (!$isThumbnail && !empty($height) && $height > 0 && $height < $origHeight) {
      $ratio = $width / $origHeight;
      $newWidth = (int)($height * $ratio);
      $newImage = imagecreatetruecolor($newWidth, $height);
      imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $height, $width, $origHeight);
      log::add(__CLASS__, 'debug', "║ Redimensionnement appliqué → {$newWidth}x{$height}");
      imagedestroy($source);
    }

    // --- Enregistrer en JPG ---
    $jpgPath = preg_replace('/\.(png|webp)$/i', '.jpg', $filePath);
    if ($quality === 0) {
      $quality = 80;
    }
    imagejpeg($newImage, $jpgPath, $quality);
    log::add(__CLASS__, 'debug', "║ JPEG sauvegardé avec qualité = $quality");

    // --- Conversion WebP si demandé ---
    $finalPath = $jpgPath;
    if ($convertToWebp) {
      $webpPath = preg_replace('/\.jpg$/i', '.webp', $jpgPath);
      if (imagewebp($newImage, $webpPath, $quality)) {
        unlink($jpgPath);
        $finalPath = $webpPath;
        log::add(__CLASS__, 'debug', "║ Conversion WebP réussie → $webpPath");
      } else {
        log::add(__CLASS__, 'debug', "║ Échec de la conversion WebP pour $jpgPath");
      }
    }

    imagedestroy($newImage);
    return $finalPath;
  }


  private static function cleanLabel($label)
  {
    return $label;
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
              log::add(__CLASS__, 'debug', "║ Fichier " . $path . " non trouvé en database.");
              if (unlink($path)) {
                log::add(__CLASS__, 'debug', "║ Suppresion reussie: " . $path);
              } else {
                log::add(__CLASS__, "error", "║ Suppresion echouée: " . $path);
              }
            }
          }
        }
      }
    } else {
      log::add(__CLASS__, "error", "║ Dossier inexistant: " . $folder);
    }
  }


  // nettoyer la DB de tous les fichiers dont la date de creation est supérieure au nombre de jours configurer
  // Exécution en cronDaily
  public static function cleanAllOldestFiles()
  {
    $days = config::byKey('remove_days', 'frigate', "7");
    $recoveryDays = config::byKey('recovery_days', 'frigate', "7");
    if (!is_numeric($days) || $days <= 0) {
      log::add(__CLASS__, "error", "║ Configuration invalide pour 'remove_days': " . $days . " Cela doit être un nombre positif.");
      return;
    }
    if ($days < $recoveryDays) {
      log::add(__CLASS__, "warning", "║ 'remove_days' doit être supérieur à 'recovery_days'");
      $days = $recoveryDays;
    }
    log::add(__CLASS__, 'info', "║ Nettoyage des fichiers datant de plus de " . $days . " jours.");

    $events = frigate_events::getOldestNotFavorites($days);

    if (!empty($events)) {
      foreach ($events as $event) {
        $eventId = $event->getEventId();

        log::add(__CLASS__, 'info', "║ Nettoyage de l'événement ID: " . $eventId);

        $result = self::cleanDbEvent($eventId);

        if ($result) {
          log::add(__CLASS__, 'info', "║ Événement ID: " . $eventId . " nettoyé avec succès.");
        } else {
          log::add(__CLASS__, "error", "║ Échec du nettoyage de l'événement ID: " . $eventId);
        }
      }
    } else {
      log::add(__CLASS__, 'info', "║ Aucun événement trouvé datant de plus de " . $days . " jours.");
    }
  }

  public static function cleanByType($type = "new")
  {
    $events = frigate_events::byType($type);

    if (!empty($events)) {
      foreach ($events as $event) {
        $eventId = $event->getEventId();

        log::add(__CLASS__, 'info', "║ Nettoyage de l'événement ID: " . $eventId . " il est de type: " . $type);

        $result = self::cleanDbEvent($eventId);

        if ($result) {
          log::add(__CLASS__, 'info', "║ Événement ID: " . $eventId . " nettoyé avec succès.");
        } else {
          log::add(__CLASS__, "error", "║ Échec du nettoyage de l'événement ID: " . $eventId);
        }
      }
    }
  }

  public static function cleanOldestFile()
  {
    $totalSizeGain = 0;
    // On ne récupère que les 5 ou 10 plus vieux pour ne pas saturer la RAM
    $events = frigate_events::getOldestNotFavorite(5);

    if (!empty($events)) {
      foreach ($events as $event) {
        $totalSizeGain += self::cleanDbEvent($event->getEventId());
      }
    }
    return $totalSizeGain; // Renvoie par exemple 15.45 (Mo)
  }

  // Supprime le plus vieux si dossier plein
  public static function cleanFolderDataIfFull()
  {
    $maxSize = (float)config::byKey('datas_weight', 'frigate', 500);
    $currentSize = (float)self::getFolderSize(); // UNIQUE appel lourd au système

    log::add(__CLASS__, 'debug', "║ Taille actuelle : $currentSize Mo / Max : $maxSize Mo");

    $limit = 0;
    while ($currentSize > $maxSize && $limit < 100) {
      $removedSize = self::cleanOldestFile();

      if ($removedSize <= 0) {
        log::add(__CLASS__, 'debug', "║ [Fin] Plus rien à supprimer ou erreur.");
        break;
      }

      $currentSize -= $removedSize;
      $limit++;
      log::add(__CLASS__, 'debug', "║ Nettoyage en cours... Taille estimée : " . round($currentSize, 2) . " Mo");
    }
  }



  public static function getFolderSize()
  {
    $t0 = microtime(true);
    $folder = realpath(__DIR__ . '/../../data');

    if (!$folder || !is_dir($folder)) {
      return 0;
    }

    // 1. Tentative via Shell (Rapide)
    if (function_exists('shell_exec')) {
      $output = shell_exec('du -s ' . escapeshellarg($folder) . ' 2>/dev/null');
      if (is_string($output)) {
        $sizeInKb = (int)trim(explode("\t", $output)[0]);
        if ($sizeInKb > 0) {
          $t1 = microtime(true);
          log::add(__CLASS__, 'debug', "║ Taille du dossier calculée via shell : " . round($sizeInKb / 1024, 2) . " Mo en " . round($t1 - $t0, 2) . "s");
          return round($sizeInKb / 1024, 2);
        }
      }
    }

    // 2. Fallback PHP (Moins rapide)
    $size = 0;
    try {
      $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
      );
      foreach ($files as $file) {
        $size += $file->getSize();
      }
    } catch (Exception $e) {
      return 0;
    }
      $t1 = microtime(true);
      log::add(__CLASS__, 'debug', "║ Taille du dossier calculée via PHP : " . round($size / (1024 * 1024), 2) . " Mo en " . round($t1 - $t0, 2) . "s");
    return round($size / (1024 * 1024), 2);
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

    // Sécurité : vérifier si l'objet existe
    if (!is_object($frigate)) {
      return 0;
    }

    // Vérifier si le fichier est un favori
    $isFavorite = $frigate->getIsFavorite() ?? 0;
    if ($isFavorite == 1) {
      log::add(__CLASS__, 'debug', "║ Événement " . $frigate->getEventId() . " est un favori, il ne doit pas être supprimé de la base de données.");
      return 0;
    }

    $totalRemovedSize = 0;
    $basePath = dirname(__FILE__, 3) . "/data/" . $frigate->getCamera() . "/" . $frigate->getEventId();

    // On définit les fichiers à vérifier
    $files = [
      'clip' => $basePath . "_clip.mp4",
      'snapshot' => $basePath . "_snapshot.jpg",
      'snapshotWebp' => $basePath . "_snapshot.webp",
      'thumbnail' => $basePath . "_thumbnail.jpg",
      'thumbnailWebp' => $basePath . "_thumbnail.webp",
      'preview' => $basePath . "_preview.gif"
    ];

    // Traitement des fichiers avec récupération de la taille avant suppression
    if (file_exists($files['clip'])) {
      $totalRemovedSize += filesize($files['clip']);
      unlink($files['clip']);
      log::add(__CLASS__, 'debug', "║ Clip MP4 supprimé pour l'événement " . $frigate->getEventId());
    }
    if (file_exists($files['snapshot'])) {
      $totalRemovedSize += filesize($files['snapshot']);
      unlink($files['snapshot']);
      log::add(__CLASS__, 'debug', "║ Snapshot JPG supprimé pour l'événement " . $frigate->getEventId());
    }
    if (file_exists($files['snapshotWebp'])) {
      $totalRemovedSize += filesize($files['snapshotWebp']);
      unlink($files['snapshotWebp']);
      log::add(__CLASS__, 'debug', "║ Snapshot WEBP supprimé pour l'événement " . $frigate->getEventId());
    }
    if (file_exists($files['thumbnail'])) {
      $totalRemovedSize += filesize($files['thumbnail']);
      unlink($files['thumbnail']);
      log::add(__CLASS__, 'debug', "║ Miniature JPG supprimée pour l'événement " . $frigate->getEventId());
    }
    if (file_exists($files['thumbnailWebp'])) {
      $totalRemovedSize += filesize($files['thumbnailWebp']);
      unlink($files['thumbnailWebp']);
      log::add(__CLASS__, 'debug', "║ Miniature WEBP supprimée pour l'événement " . $frigate->getEventId());
    }
    if (file_exists($files['preview'])) {
      $totalRemovedSize += filesize($files['preview']);
      unlink($files['preview']);
      log::add(__CLASS__, 'debug', "║ GIF supprimé pour l'événement " . $frigate->getEventId());
    }

    $frigate->remove();
    log::add(__CLASS__, 'debug', "║ Événement " . $frigate->getEventId() . " supprimé de la base de données.");

    // On retourne la taille libérée en Mo pour mettre à jour le compteur global du while
    return $totalRemovedSize / 1024 / 1024;
  }

  public static function deleteEvents($ids)
  {
    foreach ($ids as $id) {
      self::deleteEvent($id);
    }
    return true;
  }
  public static function deleteEvent($id, $all = false)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:SUPPRESSION EVENEMENT:/fg: ═══════════════════");
    $frigate = frigate_events::byEventId($id);
    $isFavorite = $frigate->getIsFavorite() ?? 0;
    if ($isFavorite == 1) {
      log::add(__CLASS__, 'debug', "║ Evènement " . $frigate[0]->getEventId() . " est un favori, il ne doit pas être supprimé de la DB.");
      message::add('frigate', __("L'évènement est un favori, il ne peut pas être supprimé de la DB.", __FILE__));
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
    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════");
    return "OK";
  }
  public static function showEvents()
  {
    $result = [];
    $events = frigate_events::all();

    foreach ($events as $event) {
      $date = date("d-m-Y H:i:s", $event->getStartTime());
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);
      $box = $event->getBox();
      $boxArray = is_array($box) ? $box : json_decode($box, true);

      $result[] = array(
        "id" => $event->getId(),
        "img" => $event->getLasted(),
        "camera" => $event->getCamera(),
        "label" => $event->getLabel(),
        "box" => $boxArray,
        "date" => $date,
        "duree" => $duree,
        "startTime" => $event->getStartTime(),
        "endTime" => $event->getEndTime(),
        "snapshot" => $event->getSnapshot(),
        "clip" => $event->getClip(),
        "thumbnail" => $event->getThumbnail(),
        "hasSnapshot" => $event->getHasSnapshot(),
        "hasClip" => $event->getHasClip(),
        "eventId" => $event->getEventId(),
        "score" => $event->getScore(),
        "top_score" => $event->getTopScore(),
        "type" => $event->getType(),
        "isFavorite" => $event->getIsFavorite() ?? 0,
        "zones" => $event->getZones() ?? '',
        "description" => $event->getRecognition_description()
      );
    }

    usort($result, [self::class, 'orderByDate']);


    return $result;
  }

  private static function orderByDate($a, $b)
  {
    $dateA = new DateTime($a['date']);
    $dateB = new DateTime($b['date']);
    return $dateB <=> $dateA;
  }

  public static function generateAllEqs()
  {

    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:CREATION DES EQUIPEMENTS:/fg: ═══════════════════");
    $urlfrigate = self::getUrlFrigate();
    if (empty($urlfrigate)) {
      log::add(__CLASS__, "error", "║ Impossible de récupérer l'URL de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS DANS LA CONFIGURATION:/fg: ═══════════════════");
      return false;
    }
    // récupérer le json de configuration
    $configurationArray = self::jsonFromUrl("http://" . $urlfrigate . "/api/config");
    if ($configurationArray == null) {
      log::add(__CLASS__, "error", "║ Impossible de récupérer le fichier de configuration de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS DANS LA CONFIGURATION:/fg: ═══════════════════");
      return false;
    }
    log::add(__CLASS__, 'debug', "║ Fichier de configuration : " . json_encode($configurationArray));

    frigate::generateEqEvents($configurationArray);
    frigate::generateEqStats();
    $n = 0;
    $n = frigate::generateEqCameras($configurationArray);
    if ($n === 0) {
      $n = "aucun";
    }

    log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-success:FIN CREATION DES EQUIPEMENTS:/fg: ═══════════════════");
    return $n;
  }
  public static function generateEqCameras($configurationArray)
  {

    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:CREATION DES CAMERAS:/fg: ═══════════════════");
    $urlFrigateWithoutPort = config::byKey('URL', 'frigate');
    $urlfrigate = self::getUrlFrigate();
    $mqttCmds = isset($configurationArray['mqtt']['host']) && !empty($configurationArray['mqtt']['host']);
    $classificationCmds = isset($configurationArray['classification']['custom']) && !empty($configurationArray['classification']['custom']);
    $addToName = "";
    $create = 1;
    $name = "";
    //  $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;

    foreach ($configurationArray['cameras'] as $cameraName => $cameraConfig) {
      $exist = 0;
      $eqlogics = eqLogic::byObjectId($defaultRoom, false);
      foreach ($eqlogics as $eqlogic) {
        $name = $eqlogic->getName();
        // Utilisation de strcasecmp pour une comparaison insensible à la casse
        if (strcasecmp($name, $cameraName) === 0) {
          $exist = 1;
          break;
        }
      }
      if ($exist) {
        log::add(__CLASS__, 'debug', "║ L'équipement : " . json_encode($cameraName) . " existe dans la pièce : " . jeeObject::byId($defaultRoom)->getName());
        $addToName = " by frigate plugin";
      }
      // Recherche équipement caméra
      $frigate = eqLogic::byLogicalId("eqFrigateCamera_" . $cameraName, "frigate");
      // position de la caméra sur l'ui / panel
      $panelOrder = isset($cameraConfig['ui']['order']) ? $cameraConfig['ui']['order'] : ($n);

      if (!is_object($frigate)) {
        $n++;
        $urlLatest = "http://" . $urlfrigate . "/api/" . $name . "/latest.jpg?timestamp=0&bbox=0&zones=0&mask=0&motion=0&regions=0";
        $img = urlencode($urlLatest);

        $frigate = new frigate();
        $frigate->setName($cameraName . $addToName);
        $frigate->setEqType_name("frigate");
        $frigate->setConfiguration("name", $cameraName);
        $frigate->setConfiguration('panel', 0);
        $frigate->setConfiguration('ptz', 0);
        $frigate->setConfiguration('preset_max', 0);
        $frigate->setConfiguration('userName', "");
        $frigate->setConfiguration('password', "");
        $frigate->setConfiguration('bbox', 0);
        $frigate->setConfiguration('timestamp', 0);
        $frigate->setConfiguration('zones', 0);
        $frigate->setConfiguration('mask', 0);
        $frigate->setConfiguration('motion', 0);
        $frigate->setConfiguration('regions', 0);
        $frigate->setConfiguration('img', $img);
        $frigate->setConfiguration('cameraStreamAccessUrl', 'rtsp://' . $urlFrigateWithoutPort . ':8554/' . $cameraName);
        $frigate->setConfiguration('urlStream', "/plugins/frigate/core/ajax/frigate.proxy.php?url=" . $img);
        if ($defaultRoom) $frigate->setObject_id($defaultRoom);
        $frigate->setIsEnable(1);
        $frigate->setIsVisible(1);
        log::add(__CLASS__, 'debug', "║ L'équipement : " . json_encode($cameraName . $addToName) . " est créé.");
      } else {
        log::add(__CLASS__, 'debug', "║ L'équipement : " . json_encode($cameraName) . " n'est pas créé.");
      }
      $frigate->setConfiguration('panelOrder', $panelOrder);
      $frigate->setLogicalId("eqFrigateCamera_" . $cameraName);
      $frigate->save();
      // commandes identique pour toutes les caméras
      log::add(__CLASS__, 'debug', "║ Création des commandes générales pour : " . json_encode($cameraName));
      self::createCamerasCmds($frigate->getId());
      // commandes MQTT s'il est configuré
      if ($mqttCmds) {
        log::add(__CLASS__, 'debug', "║ Création des commandes MQTT pour : " . json_encode($cameraName));
        $value["detect"] = isset($cameraConfig['detect']['enabled']) ? $cameraConfig['detect']['enabled'] : $configurationArray['detect']['enabled'];
        $value["recordings"] = isset($cameraConfig['record']['enabled']) ? $cameraConfig['record']['enabled'] : $configurationArray['record']['enabled'];
        $value["snapshots"] = isset($cameraConfig['snapshots']['enabled']) ? $cameraConfig['snapshots']['enabled'] : $configurationArray['snapshots']['enabled'];
        $value["motion"] = isset($cameraConfig['motion']['enabled']) ? $cameraConfig['motion']['enabled'] : $configurationArray['motion']['enabled'];

        self::createMqttCmds($frigate->getId(), $value);

        // verifier les objects configurés en détection
        $objectsGeneral = $configurationArray['objects']['track'];
        $objectsCamera = $cameraConfig['objects']['track'];
        // fusionner les 2 tableaux
        $objects = array_merge($objectsGeneral, $objectsCamera);
        // supprimer les entrées identique :
        $objects = array_unique($objects);
        // créé les commandes 
        foreach ($objects as $object) {
          self::createObjectDetectorCmd($frigate->getId(), $object);
        }
        // commande PTZ si onvif est configuré
        if (isset($cameraConfig['onvif']['host']) && !empty($cameraConfig['onvif']['host']) && $cameraConfig['onvif']['host'] !== '0.0.0.0') {
          log::add(__CLASS__, 'debug', "║ Création des commandes PTZ pour : " . json_encode($cameraName));
          self::createPTZcmds($frigate->getId());
          self::createPresetPTZcmds($frigate->getId());
          $frigate->setConfiguration("ptz", 1);
          $frigate->save();
        }
      }
      // commandes audio s'il est configuré
      $isAudioEnabledGlobally  = isset($configurationArray['audio']['enabled']) && !empty($configurationArray['audio']['enabled']);
      $isAudioEnabledForCamera = isset($cameraConfig['audio']['enabled_in_config']) && !empty($cameraConfig['audio']['enabled_in_config']);
      if ($isAudioEnabledGlobally  || $isAudioEnabledForCamera) {
        log::add(__CLASS__, 'debug', "║ Création des commandes audio pour : " . json_encode($cameraName));

        $valueAudio = $isAudioEnabledForCamera ? $cameraConfig['audio']['enabled'] : $configurationArray['audio']['enabled'];

        self::createAudioCmds($frigate->getId(), $valueAudio);
      }

      // commandes etat des classifications
      if ($classificationCmds) {
        foreach ($configurationArray['classification']['custom'] as $key => $item) {
          if (!isset($item['state_config'])) {
            continue;
          }
          $cameras = $item['state_config']['cameras'] ?? [];

          if (!array_key_exists($cameraName, $cameras)) {
            continue;
          }
          log::add(__CLASS__, 'debug', "║ Création de la commande d'état : " . $key . " pour la caméra : " . $cameraName);
          self::createCmd($frigate->getId(), "Reconnaissance - Etat " . $key, "string", "", "info_classification_state", "", 0);
        }
      }
    }
    message::add('frigate', __("Frigate : " . $n . " caméras créées, les commandes, évènements et statistiques sont mises à jour. Veuillez patienter...", __FILE__));
    // commandes de statisque
    self::getStats();
    // commandes des events
    self::getEvents(false, array(), 'end', null, 1);
    message::add('frigate', __("Mise à jour des commandes, évènements et statistiques terminée.", __FILE__));

    log::add(__CLASS__, 'debug', "╚════════════════════════ END CREATION DES CAMERAS ═══════════════════");
    return $n;
  }

  public static function restartFrigate()
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-warning:RESTART FRIGATE:/fg: ═══════════════════");
    self::publish_message('restart', '');
    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════════");
  }
  public static function generateEqEvents($configurationArray)
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
    // création des commandes d'activation des cron
    frigate::setCmdsCron();
    // création des commandes détection objects        
    $objectsGeneral = $configurationArray['objects']['track'];
    $objectsCamera = []; // tableau pour stocker les objets détectés des caméras

    // Parcourir toutes les caméras
    foreach ($configurationArray['cameras'] as $cameraId => $cameraConfig) {
      if (isset($cameraConfig['objects']['track'])) {
        $objectsCamera = array_merge($objectsCamera, $cameraConfig['objects']['track']);
      }
    }

    // Fusionner les objets généraux et ceux des caméras
    $objects = array_merge($objectsGeneral, $objectsCamera);

    // Supprimer les entrées en double
    $objects = array_unique($objects);

    // Créer les commandes pour chaque objet
    foreach ($objects as $object) {
      self::createObjectDetectorCmd($frigate->getId(), $object);
    }

    // Sauvegarder la configuration mise à jour
    $frigate->setConfiguration("objects", $objects);
    $frigate->save();
  }

  public static function generateEqStats()
  {
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    // créer l'équipement s'il n'existe pas.
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

    // Création de la commande restart Frigate si elle n'existe pas
    $eqlogicId = $frigate->getId();
    self::createEqStatsCmd($eqlogicId);
  }

  public static function updateTrackedObjects($trackedObjects)
  {
    $type = $trackedObjects['type'] ?? null;
    $camera = $trackedObjects['camera'] ?? null;
    $id = $trackedObjects['id'] ?? null;

    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:UPDATE TRACKED OBJECTS:/fg: ═══════════════════");
    log::add(__CLASS__, 'debug', "║ Type d'objet : $type | Événement ID : $id | Caméra : $camera");

    // Vérification équipement Frigate
    $frigate = frigate::byLogicalId("eqFrigateCamera_" . $camera, 'frigate');
    if (!is_object($frigate)) {
      log::add(__CLASS__, "error", "║ Équipement introuvable pour la caméra : $camera");
      return;
    }

    $eqlogicId = $frigate->getId();
    $frigateEvent = frigate_events::byEventId($id);
    log::add(__CLASS__, 'debug', "║ Données reçues : " . json_encode($trackedObjects));

    // Création / Mise à jour de la base de données
    $frigateEvent = self::updateDatabase($frigateEvent, $type, $trackedObjects);

    // Création / mise à jour des commandes Jeedom
    self::updateCommands($eqlogicId, $type, $frigateEvent);

    log::add(__CLASS__, 'debug', "╚════════════════════════ END UPDATE TRACKED OBJECTS ═══════════════════");
  }

  private static function updateDatabase($frigateEvent, $type, $trackedObjects)
  {
    $id = $trackedObjects['id'] ?? null;
    if (!is_object($frigateEvent)) {
      log::add(__CLASS__, "debug", "║ Événement introuvable (id: $id), il sera est créé dans la DB.");
      $frigateEvent = new frigate_events();
      $frigateEvent->setCamera($trackedObjects['camera']);
      $frigateEvent->setEventId($id);
      // on commence par vidér les champs de reconnaissance pour éviter d'avoir des données obsolètes
      $frigateEvent->setRecognition_type('');
      $frigateEvent->setRecognition_name('');
      $frigateEvent->setRecognition_description('');
      $frigateEvent->setRecognition_plate('');
      $frigateEvent->setRecognition_subname('');
      $frigateEvent->setRecognition_attributes('');
      $frigateEvent->setRecognition_score(0);
    }
    $score = $trackedObjects['score'] ?? '';
    if (is_numeric($score)) {
      $score = round($score * 100, 2);
    }

    switch ($type) {
      case "description":
        log::add(__CLASS__, 'debug', "║ MAJ DB → Description générée");
        $frigateEvent->setRecognition_type("description");
        $frigateEvent->setRecognition_description($trackedObjects['description'] ?? '');
        break;

      case "face":
        log::add(__CLASS__, 'debug', "║ MAJ DB → Reconnaissance faciale");
        $frigateEvent->setRecognition_type("face");
        $frigateEvent->setRecognition_name($trackedObjects['name'] ?? '');
        break;

      case "lpr":
        log::add(__CLASS__, 'debug', "║ MAJ DB → Plaque d’immatriculation");
        $frigateEvent->setRecognition_type("lpr");
        $frigateEvent->setRecognition_plate($trackedObjects['plate'] ?? '');
        $frigateEvent->setRecognition_name($trackedObjects['name'] ?? '');
        break;

      case "classification":
        log::add(__CLASS__, 'debug', "║ MAJ DB → Classification d'objet");
        $frigateEvent->setRecognition_type("classification");
        $frigateEvent->setRecognition_name($trackedObjects['model'] ?? '');
        if (isset($trackedObjects['sub_label'])) {
          $frigateEvent->setRecognition_subname($trackedObjects['sub_label'] ?? '');
          $frigateEvent->setRecognition_attributes('');
        }
        if (isset($trackedObjects['attributes'])) {
          $frigateEvent->setRecognition_attributes($trackedObjects['attributes']);
          $frigateEvent->setRecognition_subname('');
        }
        break;

      default:
        log::add(__CLASS__, 'debug', "║ Type de suivi inconnu : $type");
        return null;
    }

    $frigateEvent->setRecognition_score($score);
    $frigateEvent->save();
    return $frigateEvent;
  }
  private static function updateCommands($eqlogicId, $type, $frigateEvent)
  {
    log::add(__CLASS__, 'debug', "║ MAJ Commandes pour le type : $type");

    $update = function ($label, $subtype, $unit, $logicalId, $value) use ($eqlogicId) {
      $cmd = self::createCmd($eqlogicId, $label, $subtype, $unit, $logicalId, "", 0, null, 0);
      $cmd->save();
      $cmd->event($value ?? '');
      $cmd->save();
    };

    $update("Reconnaissance - Type", "string", "", "info_detection_type", $type);

    $withNameScore = ['face', 'lpr', 'classification'];
    if (in_array($type, $withNameScore)) {
      $update("Reconnaissance - Nom",   "string",  "",  "info_detection_name",  $frigateEvent->getRecognition_name());
      $update("Reconnaissance - Score", "numeric", "%", "info_detection_score", $frigateEvent->getRecognition_score());
    }

    if ($type === 'description') {
      log::add(__CLASS__, 'debug', "║ Mise à jour de la description : " . $frigateEvent->getRecognition_description());
      $update("Reconnaissance - Description", "string", "", "info_description", $frigateEvent->getRecognition_description());
    }

    if ($type === 'lpr') {
      $update("Reconnaissance - Plaque d'immatriculation", "string", "", "info_plate", $frigateEvent->getRecognition_plate());
    }

    if ($type === 'classification') {
      $update("Reconnaissance - Label", "string", "", "info_detection_subname", $frigateEvent->getRecognition_subname());
      $update("Reconnaissance - Attributs", "string", "", "info_detection_attributes", $frigateEvent->getRecognition_attributes());
    }
  }

  private static function createCmd($eqLogicId, $name, $subType, $unite, $logicalId, $genericType, $isVisible = 1, $infoCmd = null, $historized = 0, $type = "info")
  {
    // Nettoyer le nom exactement comme Jeedom le fera en base
    $cleanName = substr(cleanComponanteName($name), 0, 127);
    $cleanName = trim($cleanName);

    $cmd = cmd::byEqLogicIdCmdName($eqLogicId, $cleanName);

    if (!is_object($cmd)) {
      $cmd = new frigateCmd();
      $cmd->setLogicalId($logicalId);
      $cmd->setEqLogic_id($eqLogicId);
      $cmd->setName($cleanName); // déjà nettoyé, setName ne changera rien
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

  public static function createAudioCmds($eqlogicId, $value = 0)
  {
    $infoCmd = self::createCmd($eqlogicId, "audio Etat", "binary", "", "info_audio", "JEEMATE_CAMERA_AUDIO_STATE", 0);
    //On vérifie la valeur présente et mets à jour que dans le cas ou elle est différente
    $currentState = $infoCmd->execCmd();
    if ($currentState !== $value) {
      $infoCmd->event($value);
    }
    $infoCmd->save();

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

    $cmd = self::createCmd($eqlogicId, "Créer un évènement", "message", "", "action_make_api_event", "CAMERA_TAKE", 1, null, 0, "action");
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
    $infoCmd->setGeneric_type("CAMERA_URL");
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

  public static function createObjectDetectorCmd($eqlogicId, $object)
  {
    $infoCmd = self::createCmd($eqlogicId, "Détection " . $object, "binary", "", "info_detect_" . $object, "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->save();
    $infoCmd = self::createCmd($eqlogicId, "Détection tout", "binary", "", "info_detect_all", "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->save();
  }

  public static function createMQTTcmds($eqlogicId, $value)
  {
    $groupDefs = [
      'detect'              => 'DETECT',
      'recordings'          => 'NVR',
      'snapshots'           => 'SNAPSHOT',
      'motion'              => 'MOTION',
      'review_alerts'       => 'REVIEW_ALERTS',
      'review_detections'   => 'REVIEW_DETECTIONS',
      'object_descriptions' => 'OBJECT_DESCRIPTIONS',
      'review_descriptions' => 'REVIEW_DESCRIPTIONS',
      'notifications'       => 'NOTIFICATIONS',
      'improve_contrast'    => 'IMPROVE_CONTRAST',
      'enabled'              => 'ENABLED'
    ];

    foreach ($groupDefs as $key => $prefix) {
      $infoCmd = self::createCmd($eqlogicId, "{$key} Etat", "binary", "", "info_{$key}", "JEEMATE_CAMERA_{$prefix}_STATE", 0);
      if (isset($value[$key])) {
        $currentState = $infoCmd->execCmd();
        if ($currentState !== $value[$key]) {
          $infoCmd->event($value[$key]);
        }
      }
      $infoCmd->save();

      foreach (['off' => [1, 'stop'], 'on' => [1, 'start'], 'toggle' => [0, 'toggle']] as $action => [$showOn, $logicalAction]) {
        $cmd = self::createCmd(
          $eqlogicId,
          "{$key} {$action}",
          "other",
          "",
          "action_{$logicalAction}_{$key}",   // stop/start/toggle
          "JEEMATE_CAMERA_{$prefix}_SET_" . strtoupper($action),  // OFF/ON/TOGGLE
          $showOn,
          $infoCmd,
          0,
          "action"
        );
        $cmd->save();
      }
    }

    // Cas particulier : "détection en cours" (pas de on/off/toggle)
    $infoCmd = self::createCmd($eqlogicId, "détection en cours", "binary", "", "info_detectNow", "JEEMATE_CAMERA_DETECT_EVENT_STATE", 1);
    $valueDetectNow = $infoCmd->execCmd();
    if (!isset($valueDetectNow) || $valueDetectNow == null || $valueDetectNow == '') {
      $infoCmd->event(1);
    }
    $infoCmd->save();
  }

  public static function createHTTPcmd($eqlogicId, $name, $link)
  {
    log::add("frigate", 'debug', '║ création de la commande ' . $name . ' pour ' . $eqlogicId . ' liens : ' . $link);

    $infoCmd = self::createCmd($eqlogicId, "Etat HTTP command", "string", "", "info_http", "", 0, null, 0, "info");
    $infoCmd->save();

    // commande action
    $cmd = self::createCmd($eqlogicId, $name, "other", "", "action_http", "", 0, $infoCmd, 0, "action");
    $cmd->save();
    log::add("frigate", 'debug', '║ commande crée');
    $cmd->setConfiguration("request", $link);
    $cmd->save();
    log::add("frigate", 'debug', '║ commande mise à jour');
    return true;
  }
  public static function createEqStatsCmd($eqlogicId)
  {
    $cmd = self::createCmd($eqlogicId, "redémarrer frigate", "other", "", "action_restart", "GENERIC_ACTION", 1, "", 0, "action");
    $cmd->save();

    $cmd = self::createCmd($eqlogicId, "status serveur", "binary", "", "info_status", "", 0, null, 0);
    $cmd->save();
    // seulement en MQTT
    if (class_exists('mqtt2')) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['launchable'] === 'ok') {
        $cmd = self::createCmd($eqlogicId, "Disponibilité", "string", "", "info_available", "", 0, null, 0, "info");
        $cmd->save();
      }
    }
    return true;
  }
  public static function editHTTP($cmdId, $link)
  {
    $cmd = cmd::byid($cmdId);
    $cmd->setConfiguration("request", $link);
    $cmd->save();
    log::add("frigate", 'debug', '║ commande mise à jour');
    return true;
  }

  public static function createPTZdebug($eqlogicId)
  {
    log::add("frigate", 'debug', '║ création des commandes PTZ en mode DEBUG pour ' . $eqlogicId);
    self::createPTZcmds($eqlogicId);
    self::createPresetPTZcmds($eqlogicId, 1);
    self::createAudioCmds($eqlogicId);
    log::add("frigate", 'debug', '║ penser à supprimer les commandes après le debug... ');
  }
  private static function createPTZcmds($eqlogicId)
  {
    log::add("frigate", 'debug', '║ création des commandes PTZ move et zoom pour ' . $eqlogicId);

    $ptzCmds = [
      'left'     => ['CAMERA_LEFT',   1, 'PTZ move left'],
      'right'    => ['CAMERA_RIGHT',  1, 'PTZ move right'],
      'up'       => ['CAMERA_UP',     1, 'PTZ move up'],
      'down'     => ['CAMERA_DOWN',   1, 'PTZ move down'],
      'stop'     => ['CAMERA_STOP',   0, 'PTZ move stop'],
      'zoom_in'  => ['CAMERA_ZOOM',   1, 'PTZ zoom in'],
      'zoom_out' => ['CAMERA_DEZOOM', 1, 'PTZ zoom out'],
    ];

    foreach ($ptzCmds as $action => [$const, $showOn, $label]) {
      $cmd = self::createCmd($eqlogicId, $label, "other", "", "action_ptz_{$action}", $const, $showOn, "", 0, "action");
      $cmd->save();
    }
    return true;
  }

  private static function createPresetPTZcmds($eqlogicId, $debug = false)
  {
    log::add("frigate", 'debug', '║ Création des commandes Preset PTZ pour ' . $eqlogicId);
    $eqlogic = eqLogic::byId($eqlogicId);
    $camera = $eqlogic->getConfiguration("name");
    if (!$debug) {
      $presets = self::getPresets($camera);
    } else {
      $presets = [
        "features" => [
          "pt",
          "zoom",
          "pt-r",
          "zoom-r",
          "zoom-a"
        ],
        "name" => "entree",
        "presets" => [
          "preset1",
          "preset2",
          "preset3",
          "preset4",
          "preset5",
          "preset6",
          "preset7",
          "preset8",
          "preset9",
          "preset10",
          "preset11",
          "preset12",
          "preset13"
        ]
      ];
    }

    $presetList = $presets['presets'];

    if (!is_array($presetList) || count($presetList) == 0) {
      return;
    } else {
    }

    $presetMaxforEqloc = $eqlogic->getConfiguration("presetMax") ?? 0;
    $presetMaxforall = config::byKey("presetMax", "frigate");
    if ($presetMaxforEqloc > 0) {
      $max = $presetMaxforEqloc;
    } else {
      $max = $presetMaxforall;
    }
    if ($max == 0) {
      return;
    }
    if ($max > 10) {
      $max = 10;
    }

    // Création des commandes jusqu'au nombre max de presets configurés
    for ($i = 0; $i < $max && $i < count($presetList); $i++) {
      $presetName = $presetList[$i];
      log::add(__CLASS__, 'debug', "║ PRESET CREE . " . $presetName); // Utiliser le nom du preset correspondant
      // Vérifier que le nom du preset est une chaîne de caractères valide
      if (is_string($presetName) && !empty($presetName)) {
        $cmd = self::createCmd($eqlogicId, $presetName, "other", "", "action_preset_" . $i, "CAMERA_PRESET", 1, "", 0, "action");
        $cmd->save();
      }
    }
  }


  public static function setCmdsCron()
  {
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (!is_object($frigate)) {
      return; // frigate n'existe pas
    }
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
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-warning:MAJ EVENTS:/fg: ═══════════════════");
    $eqlogicIds = [];
    $cameraAction = [];
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
      $cameraActions = $eqCamera->getConfiguration('actions');
      if (is_array($cameraActions) && isset($cameraActions[0])) {
        $cameraAction = $cameraActions[0];
      } else {
        $cameraAction = null;
      }
      // Vérifier si la liste d'actions est vide
      $cameraActionsExist = !empty($cameraAction);
      self::eventAdd($event, $eqCamera->getId());
    }

    // verifier si la date de l'event est le plus récent

    $eventDate = $event->getStartTime();
    // récupérer info de la commande timestamp
    $cmdtimestamp = cmd::byEqLogicIdCmdName($eqCamera->getId(), "timestamp");
    if (is_object($cmdtimestamp)) {
      $timestamp = $cmdtimestamp->execCmd();
      $cmdtype = cmd::byEqLogicIdCmdName($eqCamera->getId(), "type");
      if (is_object($cmdtype)) {
        $type = $cmdtype->execCmd();
      } else {
        $type = "";
      }
      // Vérifier si le timestamp est supérieur ou égale à la date de l'événement et le type end
      if (($timestamp >= $eventDate) && ($type === "end")) {
        log::add(__CLASS__, 'debug', "║ ACTION: L'évènement est plus ancien que le dernier évènement enregistré.");
        return;
      }
    }

    //  $eqCamera->getId();

    if ($cameraActionsExist) {
      log::add(__CLASS__, 'debug', "║ ACTION: Vérification des actions caméra.");

      // Vérifier si toutes les actions sont désactivées
      $allActionsDisabled = true;
      foreach ($cameraAction as $action) {
        // Vérifier si l'action est activée
        $enable = $action['options']['enable'] ?? false;

        if ($enable) {
          // Si au moins une action est activée, on met à jour $allActionsDisabled à false
          $allActionsDisabled = false;
          log::add(__CLASS__, 'debug', "║ ACTION: Une action caméra est activée.");
          break;
        }
      }

      // Si toutes les actions sont désactivées, on met à jour $cameraActionsExist à false
      if ($allActionsDisabled) {
        $cameraActionsExist = false;
        log::add(__CLASS__, 'debug', "║ ACTION: Toutes les actions caméra sont désactivées.");
      }
    } else {
      log::add(__CLASS__, 'debug', "║ ACTION: Aucune action configurée.");
    }

    // Vérification des actions caméra existantes
    // Si la liste d'actions n'est pas vide et qu'au moins une action est activée
    // verifier si l'équipement event est autorisé a executer des actions
    $autorizeAction = $frigate->getConfiguration('autorizeActions');
    if ($autorizeAction == 1) {
      log::add(__CLASS__, 'debug', "║ ACTION: Les actions sont autorisées pour l'équipement Events (ID: " . $frigate->getId() . ").");
    } else {
      log::add(__CLASS__, 'debug', "║ ACTION: Les actions sont désactivées pour l'équipement Events (ID: " . $frigate->getId() . ").");
    }
    if ($cameraActionsExist && $autorizeAction) {
      log::add(__CLASS__, 'debug', "║ ACTION: Exécution des actions pour la caméra (ID: " . $eqCamera->getId() . ").");
      self::executeActionNewEvent($eqCamera->getId(), $event);
      log::add(__CLASS__, 'debug', "║ ACTION: Exécution des actions pour l'équipement Events (ID: " . $frigate->getId() . ").");
      self::executeActionNewEvent($frigate->getId(), $event);
    } elseif ($cameraActionsExist) {
      // Si les actions caméra sont activées mais que l'équipement event n'est pas autorisé à exécuter des actions
      log::add(__CLASS__, 'debug', "║ ACTION: Exécution des actions pour la caméra (ID: " . $eqCamera->getId() . ").");
      self::executeActionNewEvent($eqCamera->getId(), $event);
    } else {
      // Sinon, on exécute les actions suivantes
      log::add(__CLASS__, 'debug', "║ ACTION: Aucune action caméra activée, exécution des actions pour l'équipement Events (ID: " . $frigate->getId() . ").");
      self::executeActionNewEvent($frigate->getId(), $event);
    }



    foreach ($eqlogicIds as $eqlogicId) {

      $duration = $event->getEndTime() != null
        ? round($event->getEndTime() - $event->getStartTime(), 0)
        : 0;

      $cmds = [
        ["caméra",           "string",  "",   "info_camera",         "GENERIC_INFO",                   $event->getCamera()],
        ["label",            "string",  "",   "info_label",          "JEEMATE_CAMERA_DETECT_TYPE_STATE", $event->getLabel()],
        ["clip disponible",  "binary",  "",   "info_clips",          "",                                $event->getHasClip()],
        ["snapshot disponible", "binary", "",   "info_snapshot",       "",                                $event->getHasSnapshot()],
        ["top score",        "numeric", "%",  "info_topscore",       "GENERIC_INFO",                   $event->getTopScore()],
        ["score",            "numeric", "%",  "info_score",          "",                                $event->getScore()],
        ["zones",            "string",  "",   "info_zones",          "",                                $event->getZones()],
        ["Reconnaissance - Description",      "string",  "",   "info_description",    "",                                $event->getRecognition_description()],
        ["id",               "string",  "",   "info_id",             "",                                $event->getEventId()],
        ["type",             "string",  "",   "info_type",           "",                                $event->getType()],
        ["timestamp",        "numeric", "",   "info_timestamp",      "GENERIC_INFO",                   $event->getStartTime()],
        ["durée",            "numeric", "sc", "info_duree",          "GENERIC_INFO",                   $duration],
        ["URL snapshot",     "string",  "",   "info_url_snapshot",   "",                                $event->getSnapshot()],
        ["URL clip",         "string",  "",   "info_url_clip",       "",                                $event->getClip()],
        ["URL thumbnail",    "string",  "",   "info_url_thumbnail",  "",                                $event->getThumbnail()],
      ];

      foreach ($cmds as [$label, $subtype, $unit, $logicalId, $const, $value]) {
        $cmd = self::createCmd($eqlogicId, $label, $subtype, $unit, $logicalId, $const, 0, null, 0);
        $cmd->event($value);
        $cmd->save();
      }
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
      }
    }

    // Statistiques pour eqLogic statistiques générales
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    $eqlogicId = $frigate->getId();

    // Mise à jour des statistiques des détecteurs
    if (isset($stats['detectors']) && is_array($stats['detectors'])) {
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
    }

    // Mise à jour des usages GPU
    if (isset($stats['gpu_usages']) && is_array($stats['gpu_usages'])) {
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
    }

    // Mise à jour des usages CPU
    if (isset($stats['cpu_usages']['frigate.full_system']) && is_array($stats['cpu_usages']['frigate.full_system'])) {
      foreach ($stats['cpu_usages']['frigate.full_system'] as $key => $value) {
        $cmdName = 'Full system_' . $key;
        $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", "cpu_" . $key, "GENERIC_INFO");
        $cmd->event($value);
        $cmd->save();
      }
    }

    // Mise a jour storage
    // Filtrer les clés qui se terminent par "recordings"
    $recordingsPaths = array_filter($stats['service']['storage'], function ($key) {
      return preg_match('/recordings$/', $key);
    }, ARRAY_FILTER_USE_KEY);
    foreach ($recordingsPaths as $key => $value) {
      // Liste des valeurs à enregistrer
      $metrics = ['total', 'used', 'free'];

      foreach ($metrics as $metric) {
        if (isset($value[$metric])) {
          $cmdName = 'Recordings_' . ucfirst($metric); // Ex: Recordings_media_frigate_recordings_Total
          // Créer ou récupérer la commande
          $cmd = self::createCmd($eqlogicId, $cmdName, "numeric", "", $cmdName, "GENERIC_INFO");
          // Enregistrer la valeur correspondante
          $cmd->event($value[$metric]);
          $cmd->save();
        }
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

    // Créer ou récupérer la valeur de uptime en secondes
    $uptime = $stats['service']['uptime'] ?? 0;
    $cmd = self::createCmd($eqlogicId, "uptime", "numeric", "", "info_uptime", "", 0, null, 0);
    // Enregistrer la valeur de l'événement
    $cmd->event($uptime);
    $cmd->save();

    // Créer ou récupérer la valeur de uptime en format lisible
    $uptimeTimestamp = time() - $uptime;
    $uptimeDate = date("Y-m-d H:i:s", $uptimeTimestamp);
    $cmd = self::createCmd($eqlogicId, "uptimeDate", "string", "", "info_uptimeDate", "", 0, null, 0);
    // Enregistrer la valeur de l'événement
    $cmd->event($uptimeDate);
    $cmd->save();
  }

  private static function executeActionNewEvent($eqLogicId, $event)
  {
    // Récupération des URLs externes et internes
    $urlJeedom = network::getNetworkAccess('external');
    if ($urlJeedom == "") {
      $urlJeedom = network::getNetworkAccess('internal');
    }
    $getPreview = str_replace("snapshot.jpg", "preview.gif", $event->getSnapshot());
    // Initialisation des variables d'événement
    $eventId = $event->getEventId();
    $hasClip = $event->getHasClip();
    $hasSnapshot = $event->getHasSnapshot();
    $topScore = $event->getTopScore();
    $clip = $urlJeedom . $event->getClip();
    $snapshot = $urlJeedom . $event->getSnapshot();
    if (is_array($event->getThumbnail())) {
      $valueThumbnail = json_encode($event->getThumbnail());
    } else {
      $valueThumbnail = $event->getThumbnail();
    }
    $thumbnail = $urlJeedom . $valueThumbnail;
    $preview = $urlJeedom . $getPreview;
    $clipPath = "/var/www/html" . $event->getClip();
    $snapshotPath = "/var/www/html" . $event->getSnapshot();
    $thumbnailPath = "/var/www/html" . $valueThumbnail;
    $previewPath = "/var/www/html" . $getPreview;
    $camera = $event->getCamera();
    $cameraId = eqLogic::byLogicalId("eqFrigateCamera_" . $camera, "frigate")->getId();
    $label = $event->getLabel();
    $description = $event->getRecognition_description() ?? "";
    $attributes = $event->getRecognition_attributes() ?? "";
    $sublabel = $event->getRecognition_subname() ?? "";
    $zones = $event->getZones();
    $score = $event->getScore();
    $type = $event->getType();
    $start = date("d-m-Y H:i:s", $event->getStartTime());
    $end = $event->getEndTime() ? date("d-m-Y H:i:s", $event->getEndTime()) : $start;
    $duree = $event->getEndTime() ? round($event->getEndTime() - $event->getStartTime(), 0) : 0;
    $time = date("H:i");
    $jeemate = $eventId . ";;start=" . $start . ";;end=" . $end . ";;camera=" . $camera . ";;label=" . $label . ";;zones=" . $zones . ";;topScore=" . $topScore . ";;type=" . $type . ";;snapshot=" . $snapshot . ";;thumbnail=" . $thumbnail . ";;clip=" . $clip;
    $conditionIsActived = false;
    $eqLogic = eqLogic::byId($eqLogicId);

    // Vérification de la condition d'exécution
    $conditionIf = $eqLogic->getConfiguration('conditionIf');
    $conditionIf = str_replace(
      ['#camera#', '#score#', '#top_score#'],
      [$camera, $score, $topScore],
      $conditionIf
    );
    if ($conditionIf && jeedom::evaluateExpression($conditionIf)) {
      $conditionIsActived = true;
    }

    $actionsArray = $eqLogic->getConfiguration('actions');

    // Vérifie que $cameraActions est bien un tableau et qu'il contient un élément à l'indice 0
    if (is_array($actionsArray) && isset($actionsArray[0])) {
      $actions = $actionsArray[0];
    } else {
      // Gérer le cas où $cameraActions n'est pas un tableau ou est vide
      // Par exemple, loguer un message d'erreur ou initialiser $cameraAction avec une valeur par défaut
      $actions = null;
      // Ou afficher un message d'erreur
    }
    if (is_array($actions)) {
      log::add("frigate_Actions", 'info', "╔═════════════════════════════ :b:START " . $type . ":/b: ═══════════════════════════════════╗");
      log::add("frigate_Actions", 'info',  "║ Caméra : " . $eqLogic->getHumanName());
      log::add("frigate_Actions", 'info',  "║ HasSnapshot : " . $hasSnapshot);
      log::add("frigate_Actions", 'info',  "║ HasClip : " . $hasClip);
      log::add("frigate_Actions", 'info',  "║ Label : " . $label);
      foreach ($actions as $action) {
        log::add("frigate_Actions", 'info', "╠════════════════════════════════════");

        // Vérifier la condition d'éxècution
        $options = $action['options'];
        $actionForced = $action['options']['actionForced'] ?? false;

        if (!$conditionIsActived) {
          log::add("frigate_Actions", 'info', "║ Commande en cour d'éxècution.");
        } elseif ($actionForced) {
          log::add("frigate_Actions", 'info', "║ Commande en cour d'éxècution car la condition principale est ignorée");
        } else {
          log::add("frigate_Actions", 'info', "║ Action non exécutées car la condition principale " . $conditionIf .  " est vrai.");
          continue;
        }

        // vérifier si la commande est activée
        $enable = $action['options']['enable'] ?? false;
        if (!$enable) {
          log::add("frigate_Actions", 'info', "║ Commande désactivée");
          continue;
        }

        // vérifier si la condition de l'action est remplie
        $actionConditionIsActived = true;
        $actionCondition = $action['actionCondition'];
        $actionCondition = str_replace(
          ['#camera#', '#score#', '#top_score#'],
          [$camera, $score, $topScore],
          $actionCondition
        );
        if ($actionCondition != "" && !jeedom::evaluateExpression($actionCondition)) {
          $actionConditionIsActived = false;
        }
        log::add("frigate_Actions", 'info', "║ Condition de l'action  : " . $actionCondition . ", etat : " . json_encode($actionConditionIsActived));

        if (!$actionConditionIsActived) {
          log::add("frigate_Actions", 'info', "║ Condition de l'action non remplie : " . $actionCondition . ", l'action sera ignorée.");
          continue;
        }

        log::add("frigate_Actions", 'info',  "║ Action : " . json_encode($action));
        $cmd = $action['cmd'];
        $cmdLabelName = $action['cmdLabelName'] ?: "all";
        $cmdTypeName = $action['cmdTypeName'] ?: "end";
        $cmdZoneName = $action['cmdZoneName'] ?: "all";
        $cmdZoneEndName = $action['cmdZoneEndName'] ?: "";

        // Convertir les chaînes en tableaux
        $cmdLabels = array_map(fn($s) => self::cleanString(trim($s)), explode(',', $cmdLabelName));
        $cmdZones = array_map(fn($s) => self::cleanString(trim($s)), explode(',', $cmdZoneName));
        $cmdZonesEnd = array_map(fn($s) => self::cleanString(trim($s)), explode(',', $cmdZoneEndName));
        $eventZones = array_map(fn($s) => self::cleanString(trim($s)), explode(',', $zones));
        $cmdTypes = array_map(fn($s) => self::cleanString(trim($s)), explode(',', $cmdTypeName));

        // Ajouter aux tableaux si nécessaire une valeur par défaut
        if ($cmdLabelName == '') {
          $cmdLabels = ["all"];
        }
        if ($cmdZoneName == '') {
          $cmdZones = ["all"];
        }
        if ($cmdTypes == '') {
          $cmdTypes = ["end"];
        }
        log::add("frigate_Actions", 'info', "║ Labels configurés : " . json_encode($cmdLabels) . ", labels de l'évènement : " . json_encode($label));

        log::add("frigate_Actions", 'info', "║ Zones configurées : " . json_encode($cmdZones) . ", zones de l'évènement : " . json_encode($eventZones));

        log::add("frigate_Actions", 'info', "║ Types configurés : " . json_encode($cmdTypes) . ", type de l'évènement : " . json_encode($type));

        // Vérifier les trois conditions
        $labelMatch = in_array($label, $cmdLabels) || in_array("all", $cmdLabels);
        $typeMatch = in_array($type, $cmdTypes);
        // Verifier si on utilise zone end, si non utilisé gestion classique sinon verifier ordre des zones
        if (empty($cmdZoneEndName)) {
          log::add("frigate_Actions", 'info', "║ Pas de zone de sortie configurée, vérification des zones d'entrée uniquement.");
          // Vérifier si au moins une des zones d'entrée est présente dans les zones de l'événement
          $zoneMatch = count(array_intersect($cmdZones, $eventZones)) > 0 || in_array("all", $cmdZones);
        } else {
          // Récupérer les zones configurées
          $enterZone = $cmdZones[0]; // Zone d'entrée configurée
          $quitZone = $cmdZonesEnd[0]; // Zone de sortie configurée

          // Trouver les positions des zones dans la séquence
          $enterZonePos = array_search($enterZone, $eventZones);
          $quitZonePos = array_search($quitZone, $eventZones);

          // Vérifier que les deux zones sont présentes et dans le bon ordre
          if ($enterZonePos !== false && $quitZonePos !== false) {
            $zoneMatch = $enterZonePos < $quitZonePos;
          } else {
            $zoneMatch = false;
          }
          log::add("frigate_Actions", 'info', "║ Zones de l'évènement : " . json_encode($eventZones));
          log::add("frigate_Actions", 'info', "║ Zone d'entrée' : " . json_encode($enterZone));
          log::add("frigate_Actions", 'info', "║ Zone de sortie : " . json_encode($quitZone));
          if ($zoneMatch) {
            log::add("frigate_Actions", 'info', "║ Correspondance trouvé, déclenchement de l'action.");
          } else {
            log::add("frigate_Actions", 'info', "║ Les zones ne correspondent pas !");
          }
        }
        // Si au moins une des conditions n'est pas remplie, ignorer l'action
        if (!($labelMatch && $typeMatch && $zoneMatch)) {
          log::add("frigate_Actions", 'info', "║ Au moins une des conditions (label : " . json_encode($labelMatch) . ", type : " . json_encode($typeMatch) . ", zone : " . json_encode($zoneMatch) . ") n'est pas remplie, l'action sera ignorée.");
          continue;
        }

        $tags = ['#time#', '#event_id#', '#camera#', '#cameraId#', '#score#', '#has_clip#', '#has_snapshot#', '#top_score#', '#zones#', '#snapshot#', '#snapshot_path#', '#clip#', '#clip_path#', '#thumbnail#', '#thumbnail_path#', '#label#', '#description#', '#start#', '#end#', '#duree#', '#type#', '#jeemate#', '#preview#', '#preview_path#', '#attributes#', '#sublabel#'];
        $values = [$time, $eventId, $camera, $cameraId, $score, $hasClip, $hasSnapshot, $topScore, $zones, $snapshot, $snapshotPath, $clip, $clipPath, $thumbnail, $thumbnailPath, $label, $description, $start, $end, $duree, $type, $jeemate, $preview, $previewPath, $attributes, $sublabel];

        $options = str_replace(
          $tags,
          array_map('strval', $values),
          $options
        );

        // Vérifie si le temps de début de l'événement est inférieur ou égal à trois heures avant le temps actuel
        if ($event->getStartTime() <= time() - 10800) {
          log::add("frigate_Actions", 'info', "║ Événement trop ancien (plus de 3 heures), il sera ignoré.");
          continue;
        }

        // Exécuter l'action selon le contenu des options
        $optionsJson = json_encode($action['options']);
        if (strpos($optionsJson, '#clip#') !== false || strpos($optionsJson, '#clip_path#') !== false) {
          if ($hasClip == 1) {
            log::add("frigate_Actions", 'info', "║ ACTION CLIP : " . $optionsJson);
            scenarioExpression::createAndExec('action', $cmd, $options);
          } else {
            log::add("frigate_Actions", 'info', "║ Le clip n'est pas disponible, actions non exécutées.");
            log::add("frigate_Actions", 'info', "╠════════════════════════════════════");
          }
        } elseif (strpos($optionsJson, '#snapshot#') !== false || strpos($optionsJson, '#snapshot_path#') !== false) {
          if ($hasSnapshot == 1) {
            log::add("frigate_Actions", 'info', "║ ACTION SNAPSHOT : " . $optionsJson);
            scenarioExpression::createAndExec('action', $cmd, $options);
          } else {
            log::add("frigate_Actions", 'info', "║ Le snapshot n'est pas disponible, actions non exécutées.");
            log::add("frigate_Actions", 'info', "╠════════════════════════════════════");
          }
        } else {
          log::add("frigate_Actions", 'info', "║ ACTION OTHER: " . $optionsJson);
          scenarioExpression::createAndExec('action', $cmd, $options);
        }
      }
      log::add("frigate_Actions", 'info', "╚═════════════════════════════ :b:END   " . $type . ":/b: ═══════════════════════════════════╝");
    }
  }

  public static function saveURL($eventId = null, $type = null, $camera = null, $mode = 0, $file = "")
  {
    // mode de fonctionnement : 0 = defaut, 1 = thumbnail, 2 = latest, 3 = snapshot, 4 = clip
    $result = "";
    $urlJeedom = network::getNetworkAccess('external') ?: network::getNetworkAccess('internal');
    $urlFrigate = self::getUrlFrigate();
    $eqLogic = eqLogic::byLogicalId("eqFrigateCamera_" . $camera, "frigate");
    $timestamp = $eqLogic->getConfiguration('timestamp');
    $extra = "";

    // --- Définir type et extension ---
    switch ($type) {
      case "preview":
        $extension = "gif";
        break;
      case "snapshot":
        $extension = "jpg";
        $extra = '?timestamp=' . $timestamp . '&bbox=1';
        break;
      default:
        $extension = "mp4";
    }

    $lien = "http://{$urlFrigate}/api/events/{$eventId}/{$type}.{$extension}{$extra}";
    $path = "/data/{$camera}/{$eventId}_{$type}.{$extension}";

    // --- Modes spécifiques ---
    if ($mode == 1) { // Thumbnail
      $lien = "http://{$urlFrigate}/api/events/{$eventId}/thumbnail.jpg";
      $path = "/data/{$camera}/{$eventId}_thumbnail.jpg";
    } elseif ($mode == 2) { // Latest
      $lien = $file;
      $path = "/data/{$camera}/latest.jpg";
    } elseif ($mode == 3) { // Snapshot externe
      $lien = urldecode($file);
      $path = "/data/snapshots/{$eventId}_snapshot.jpg";
    } elseif ($mode == 4) { // Clip
      $path = "/data/{$camera}/{$eventId}_clip.mp4";
      $newPath = dirname(__FILE__, 3) . $path;
      $cmd = 'ffmpeg -rtsp_transport tcp -loglevel fatal -i "' . $file . '" -c:v copy -bsf:a aac_adtstoasc -y -t 10 -movflags faststart ' . escapeshellarg($newPath);
      exec($cmd, $output, $return_var);
      $result = "/plugins/frigate" . $path;
      log::add(__CLASS__, 'debug', "║ Commande exécutée : " . $cmd);
      log::add(__CLASS__, 'debug', "║ Code de retour : " . $return_var);
      return $result;
    }

    $fullPath = dirname(__FILE__, 3) . $path;

    // --- Si déjà téléchargé (sauf latest) ---
    if (file_exists($fullPath) && $mode != 2) {
      return "/plugins/frigate" . $path;
    }

    // --- Création du dossier ---
    $destinationDir = dirname($fullPath);
    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
      log::add(__CLASS__, 'debug', "║ Échec de la création du répertoire : $destinationDir");
      return "error";
    }

    // --- Téléchargement ---
    $headers = @get_headers($lien);
    $content = ($headers && strpos($headers[0], '200') !== false) ? file_get_contents($lien) : false;

    if ($content === false) {
      log::add(__CLASS__, 'debug', "║ Le fichier n'existe pas ou une erreur s'est produite : " . $lien);
      return "error";
    }

    // --- Sauvegarde initiale ---
    $fileSaved = file_put_contents($fullPath, $content);
    if ($fileSaved === false) {
      log::add(__CLASS__, 'debug', "║ Échec de l'enregistrement du fichier : " . $lien);
      return "error";
    }

    // --- Récupération paramètres user ---
    $snapshotQuality = (int)($eqLogic->getConfiguration('snapshotQuality') ?? 100);
    $snapshotHeight  = $eqLogic->getConfiguration('snapshotHeight');
    $snapshotHeight  = is_numeric($snapshotHeight) ? (int)$snapshotHeight : null;
    $snapshotWebp    = ($eqLogic->getConfiguration('snapshotWebp') ?? "0") == "1";

    // --- Traitement image (JPG uniquement) ---
    $isJpg = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'jpg';
    $isImageMode = in_array($mode, [0, 1, 3]); // modes image : defaut, thumbnail, snapshot

    if ($isJpg && $isImageMode) {
      // mode 1 = thumbnail → redimensionnement désactivé
      $isThumbnail = ($mode == 1);

      $newPath = self::processJpgImage(
        $fullPath,
        $snapshotHeight,
        $snapshotQuality,
        $snapshotWebp,
        $isThumbnail
      );

      if ($newPath !== null) {
        $path = str_replace(dirname(__FILE__, 3), "", $newPath);
      }
    }

    $result = "/plugins/frigate" . $path;
    // log::add(__CLASS__, 'debug', "║ :b:Fichier enregistré:/b: " . $result);
    return $result;
  }


  private static function cleanString($string)
  {
    // Supprimer les accents
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    // Mettre en minuscule
    return mb_strtolower($string);
  }

  public static function createSnapshot($eqLogic)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════════════════════════════");
    log::add(__CLASS__, 'debug', "║ Créer snapshot");
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
    log::add(__CLASS__, 'debug', "║ Mise à jour de la commande.");
    $eqLogic->getCmd(null, 'info_url_capture')->event($url);
    $eqLogic->getCmd(null, 'info_label')->event("capture");
    $eqLogic->getCmd(null, 'info_score')->event(0);
    $eqLogic->getCmd(null, 'info_topscore')->event(0);
    $eqLogic->getCmd(null, 'info_duree')->event(0);

    // Creation de l'evenement  dans la DB
    log::add(__CLASS__, 'debug', "║ Création d'un nouveau évènement Frigate pour l'event ID: " . $uniqueId);
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
    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════");
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
      log::add(__CLASS__, 'debug', "║ Erreur lors de la récupération de la configuration de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return false;
    } else if ($config == null) {
      log::add(__CLASS__, "error", "║Erreur: Impossible de récupérer la configuration de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return false;
    } else {
      log::add(__CLASS__, 'debug', "║ Configuration de Frigate récupérée avec succès.");
      log::add(__CLASS__, 'debug', "║ Configuration : " . json_encode($config));
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
    if (!is_object($frigate)) {
      return; // frigate n'existe pas
    } else {
      $eqlogicId = $frigate->getId();
      $cmd = self::createCmd($eqlogicId, "version", "string", "", "info_version", "", 0, null, 0);
      $version = $cmd->execCmd();
      if ($version != config::byKey('frigate_version', 'frigate')) {
        config::save('frigate_version', $version, 'frigate');
      }
    }

    foreach ($_message[self::getTopic()] as $key => $value) {
      log::add("frigate_MQTT", 'info', 'handle Mqtt Message pour : :b:' . $key . ':/b: = ' . json_encode($value));

      switch ($key) {
        case 'events':
          if (version_compare($version, "0.14", "<")) {
            log::add("frigate_MQTT", 'info', ' => Traitement mqtt events <0.14');
            log::add("frigate_MQTT", 'warning', ' => Version < 0.14, mettre à jour votre serveur frigate !');
            message::add("frigate",__("Version de Frigate détectée : " . $version . ", certaines fonctionnalités du plugin peuvent ne pas fonctionner correctement. Veuillez mettre à jour votre serveur Frigate pour une expérience optimale.", __FILE__));
            self::getEvents(true, [$value['after']], $value['type']);
            event::add('frigate::events', array('message' => 'mqtt_update', 'type' => 'event'));
          }
          break;

        case 'reviews':
          $eventId = $value['after']['data']['detections'][0];
          $eventType = $value['type'];

          self::getEvent($eventId, $eventType);
          event::add('frigate::events', array('message' => 'mqtt_update_manual', 'type' => 'event'));
          break;

        case 'stats':
          self::majStatsCmds($value, true);
          break;

        case 'available':
          $cmd = self::createCmd($eqlogicId, "Disponibilité", "string", "", "info_available", "", 0, null, 0, "info");
          $cmd->event($value);
          $cmd->save();
          break;

        case 'tracked_object_update':
          log::add("frigate_MQTT", 'info', ' => Traitement mqtt tracked_object_update');
          self::updateTrackedObjects($value);
          break;

        default:
          $eqCamera = eqLogic::byLogicalId("eqFrigateCamera_" . $key, "frigate");
          if (!is_object($eqCamera)) {
            continue 2;
          }

          self::processCameraData($eqCamera, $key, $value);
          break;
      }
    }
  }

  private static function processCameraData($eqCamera, $key, $data)
  {
    $eqEvent = eqLogic::byLogicalId("eqFrigateEvents", "frigate");
    $objects = $eqEvent->getConfiguration("objects");

    $stateMap = [
      'detect'              => 'DETECT',
      'recordings'          => 'NVR',
      'snapshots'           => 'SNAPSHOT',
      'audio'               => 'AUDIO',
      'review_alerts'       => 'REVIEW_ALERTS',
      'review_detections'   => 'REVIEW_DETECTIONS',
      'object_descriptions' => 'OBJECT_DESCRIPTIONS',
      'review_descriptions' => 'REVIEW_DESCRIPTIONS',
      'notifications'       => 'NOTIFICATIONS',
      'improve_contrast'    => 'IMPROVE_CONTRAST',
      'enabled'              => 'ENABLED'
    ];

    $skipKeys = ['birdseye', 'motion_contour_area', 'motion_threshold', 'ptz_autotracker', 'model_state'];

    foreach ($data as $innerKey => $innerValue) {

      // clés ignorées
      if (in_array($innerKey, $skipKeys)) {
        continue;
      }

      // objet détecté (person, car, etc.)
      if (in_array($innerKey, $objects)) {
        log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-success:START OBJET DETECT :/fg: ════════════════════════════════╗");
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
        log::add("frigate_Detect", 'info', "║ Objet : " . $innerKey . ', Etat : ' . json_encode($innerValue));
        self::handleObject($eqCamera, $innerKey, $innerValue);
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqEvent->getHumanName() . ":/b:");
        self::handleObject($eqEvent, $innerKey, $innerValue);
        log::add("frigate_Detect", 'info', "╚══════════════════════════════════════════════════════════════════════════════════╝");
        continue;
      }

      // mouvement en cours
      if ($innerKey === 'motion') {
        self::handleMotion($eqCamera, $key, $innerValue);
        continue;
      }

      // classification en cours
      if ($innerKey === 'classification') {
        foreach ($innerValue as $key => $value) {
          log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-info:START CLASSIFICATION:/fg: ═══════════════════════════════════╗");
          log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
          log::add("frigate_Detect", 'info', '║ Objet : ' . $innerKey . ', Etat : ' . json_encode($innerValue));
          $infoCmd = self::createCmd($eqCamera->getId(), "Reconnaissance - Etat " . $key, "string", "", "info_classification_state", "", 0);
          $infoCmd->event($value);
          $infoCmd->save();
          $eqCamera->refreshWidget();
          log::add("frigate_Detect", 'info', "╚═════════════════════════════ :fg-info:END CLASSIFICATION:/fg: ═══════════════════════════════════╝");
        }
        continue;
      }

      // tous les objets (all)
      if ($innerKey === 'all') {
        log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-danger:START ALL DETECT:/fg: ═══════════════════════════════════╗");
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
        log::add("frigate_Detect", 'info', '║ Objet : ' . $innerKey . ', Etat : ' . json_encode($innerValue));
        self::handleAllObject($eqCamera, $innerKey, $innerValue);
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqEvent->getHumanName() . ":/b:");
        self::handleAllObject($eqEvent, $innerKey, $innerValue);
        log::add("frigate_Detect", 'info', "╚══════════════════════════════════════════════════════════════════════════════════╝");
        continue;
      }

      // états on/off génériques
      if (isset($stateMap[$innerKey], $innerValue['state'])) {
        self::updateCameraState($eqCamera, $innerKey, $innerValue['state'], "JEEMATE_CAMERA_{$stateMap[$innerKey]}_STATE");
      }
    }
  }

  private static function handleMotion($eqCamera, $key, $innerValue)
  {
    if (isset($innerValue['state']) && $innerValue['state']) {
      $state = ($innerValue['state'] == 'ON') ? "1" : "0";
      log::add("frigate_MQTT", 'info', $key . ' => Valeur motion state : ' . $state);
      $infoCmd = self::createCmd($eqCamera->getId(), 'motion Etat', 'binary', '', 'info_motion', 'JEEMATE_CAMERA_DETECT_STATE', 0);
      $infoCmd->event($state);
      $infoCmd->save();
      $eqCamera->refreshWidget();
    }

    if (isset($innerValue) && !is_array($innerValue)) {
      $state = ($innerValue == 'ON') ? "1" : "0";
      log::add("frigate_MQTT", 'info', $key . ' => Valeur motion : ' . $state);
      $infoCmd = self::createCmd($eqCamera->getId(), 'détection en cours', 'binary', '', 'info_detectNow', 'JEEMATE_CAMERA_SNAPSHOT_STATE', 1);
      $infoCmd->event($state);
      $infoCmd->save();
      $eqCamera->refreshWidget();
    }
  }

  private static function handleObject($eqCamera, $key, $innerValue)
  {
    // Traiter le cas où $innerValue est un nombre ou un tableau avec "active"
    $value = 0;
    if (is_array($innerValue) && isset($innerValue["active"])) {
      $value = ($innerValue["active"] !== 0) ? 1 : 0;
    } else {
      $value = ($innerValue !== 0) ? 1 : 0;
    }
    $infoCmd = self::createCmd($eqCamera->getId(), "Détection " . $key, "binary", "", "info_detect_" . $key, "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->event($value);
    $infoCmd->save();
    log::add("frigate_Detect", 'info', '║ Objet : ' . $key . ', Valeur enregistrée : ' . json_encode($value));
  }
  private static function handleAllObject($eqCamera, $key, $innerValue)
  {
    // Traiter le cas où $innerValue est un nombre ou un tableau avec "active"
    if (is_array($innerValue)) {
      $value = ($innerValue["active"] !== 0) ? 1 : 0;
    } else {
      $value = ($innerValue !== 0) ? 1 : 0;
    }
    $infoCmd = self::createCmd($eqCamera->getId(), "Détection tout", "binary", "", "info_detect_all", "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->event($value);
    $infoCmd->save();
    log::add("frigate_Detect", 'info', '║ Objet : ' . $key . ', Valeur enregistrée : ' . json_encode($value));
    if ($value === 0) {
      $cmds = cmd::byEqLogicId($eqCamera->getId(), "info");
      foreach ($cmds as $cmd) {
        if ((substr($cmd->getLogicalId(), 0, 12) == 'info_detect_') && ($cmd->getLogicalId() !== "info_detect_all") && ($cmd->execCmd() == 1)) {
          $cmd->event($value);
          $cmd->save();
          log::add("frigate_Detect", 'info', '║ cmd : ' . $cmd->getName() . ', Valeur forcée : ' . json_encode($value));
        }
      }
    }
  }
  private static function updateCameraState($eqCamera, $type, $state, $jeemateState)
  {

    if (isset($state)) {
      $infoCmd = self::createCmd($eqCamera->getId(), $type . " Etat", "binary", "", "info_" . $type, $jeemateState, 0);
      $currentState = $infoCmd->execCmd(); // Obtenir l'état actuel

      $stateValue = ($state == 'ON') ? "1" : "0";

      if ($currentState !== $stateValue) {
        $infoCmd->event($stateValue);
        //  $infoCmd->save();
        $eqCamera->refreshWidget();
        log::add("frigate_MQTT", 'info', 'L\'etat de la commande ' . $type . ' a été modifié, mise a jour du status.');
      }
    }
  }

  /**
   * @param string $eventId
   * @param int|bool $isFav
   * @return int|null
   */
  public static function setFavorite($eventId, $isFav)
  {
    $event = frigate_events::byEventId($eventId);

    if (!is_object($event)) {
      log::add(__CLASS__, 'error', "║ setFavorite :: Aucun événement trouvé avec l'eventId : " . (string)$eventId);
      return null;
    }
    $event->setIsFavorite($isFav);
    $event->save();

    return (int)$event->getIsFavorite();
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
        log::add(__CLASS__, 'debug', "║ Fichier supprimé: " . $file->getPathname());
      }
    }
  }

  public static function backupExclude()
  {
    // retourne le répertoire de sauvegarde des snapshots et des vidéos des events à ne pas enregistrer dans le backup Jeedom
    if (config::byKey('excludeBackup', 'frigate', 0)) {
      return ['data'];
    }
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
      log::add(__CLASS__, "error", '║ getFrigateConfiguration :: ' . $error);
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
      log::add(__CLASS__, "error", '║ getFrigateConfiguration :: ' . $error);
      $response = array(
        'status' => 'error',
        'message' => $error
      );

      return $response;
    }

    curl_close($ch);

    //log::add(__CLASS__, 'info', "getFrigateConfiguration:: save config file");
    //$file = file_put_contents(dirname(__FILE__, 3) . '/data/config.yaml', $curlResponse);

    $cResponse = json_decode($curlResponse);
    if ($cResponse != null) {
      // 0.15
      $curlResponse = $cResponse;
    }

    $response = array(
      'status' => 'success',
      'message' => $curlResponse
    );

    return $response;
  }

  public static function sendFrigateConfiguration($frigateConfiguration, $restart = false)
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/config/save" . ($restart ? '?save_option=restart' : '?save_option=saveonly');

    $ch = curl_init($resultURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-yaml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $frigateConfiguration);

    log::add(__CLASS__, 'debug', '║ sendFrigateConfiguration :: Data : ' . $frigateConfiguration);
    $curlResponse = curl_exec($ch);

    if ($curlResponse === false) {
      $error = 'Erreur cURL : ' . curl_error($ch);
      curl_close($ch);
      log::add(__CLASS__, "error", '║ sendFrigateConfiguration :: ' . $error);
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
      log::add(__CLASS__, "error", '║ sendFrigateConfiguration :: ' . $error);
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


  private static function jsonFromUrl($jsonUrl)
  {
    $headers = @get_headers($jsonUrl);

    $code = substr($headers[0], 9, 3);
    if ($code == '200') {
      // Le fichier existe, on peut le télécharger
      $jsonContent = file_get_contents($jsonUrl);
    } else {
      $jsonContent = false;
      log::add(__CLASS__, "error", "║ jsonFromUrl : HTTP Error $code lors du téléchargement de $jsonUrl");
    }

    // Vérifier si le téléchargement a réussi
    if ($jsonContent === false) {
      log::add(__CLASS__, "error", "║ jsonFromUrl : Failed to retrieve JSON from URL");
      return null;
    }

    // Décoder le JSON en tableau PHP
    $jsonArray = json_decode($jsonContent, true);

    // Vérifier si la conversion a réussi
    if ($jsonArray === null && json_last_error() !== JSON_ERROR_NONE) {
      log::add(__CLASS__, "error", "║ jsonFromUrl : Failed to decode JSON content");
      return null;
    }

    return $jsonArray;
  }

  private static function checkFrigateStatus()
  {
    $frigate = frigate::byLogicalId('eqFrigateStats', 'frigate');
    if (!$frigate) {
      return;
    }
    $eqlogicId = $frigate->getId();
    $urlFrigate = self::getUrlFrigate();
    $etat = 0;

    $ch = curl_init($urlFrigate);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);

    // Obtenir le code de statut HTTP
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
      $etat = 1; // Site accessible
    } else {
      $etat = 0; // Site inaccessible
    }
    $cmd = self::createCmd($eqlogicId, "status serveur", "binary", "", "info_status", "", 0, null, 0);
    // Enregistrer la valeur de l'événement
    $cmd->event($etat);
    $cmd->save();

    return $etat;
  }
  private static function checkFrigateVersion()
  {
    $urlfrigate = self::getUrlFrigate();
    $resultURL = $urlfrigate . "/api/stats";
    $stats = self::getcURL("Stats", $resultURL);
    if ($stats == null) {
      log::add(__CLASS__, "error", "║ Erreur: Impossible de récupérer les stats de Frigate.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ :fg-warning:ERREURS:/fg: ═══════════════════");
      return;
    }
    $version = strstr($stats['service']['version'], '-', true);
    $latestVersion = $stats['service']['latest_version'];
    if (version_compare($version, $latestVersion, "<")) {
      config::save('frigate_maj', 1, 'frigate');
      message::add('frigate', __("Une nouvelle version de Frigate (" . $latestVersion . ") est disponible.", __FILE__));
    } else {
      config::save('frigate_maj', 0, 'frigate');
    }
  }
  public static function getPluginVersion()
  {
    $pluginVersion = '0.0.0';
    try {
      if (!file_exists(dirname(__FILE__) . '/../../plugin_info/info.json')) {
        log::add('frigate', "warning", '[Plugin-Version] fichier info.json manquant');
      }
      $data = json_decode(file_get_contents(dirname(__FILE__) . '/../../plugin_info/info.json'), true);
      if (!is_array($data)) {
        log::add('frigate', "warning", '[Plugin-Version] Impossible de décoder le fichier info.json');
      }

      $pluginVersion = $data['pluginVersion'];
    } catch (\Exception $e) {
      log::add('frigate', 'debug', '[Plugin-Version] Get ERROR :: ' . $e->getMessage());
    }
    log::add('frigate', 'info', '[Plugin-Version] PluginVersion :: ' . $pluginVersion);
    return $pluginVersion;
  }


  public static function timeElapsedString($datetime, $full = false)
  {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // On extrait les valeurs dans un tableau pour pouvoir ajouter les semaines
    // sans modifier l'objet DateInterval original
    $diffValues = [
      'y' => $diff->y,
      'm' => $diff->m,
      'w' => (int)floor($diff->d / 7),
      'd' => $diff->d % 7, // Le reste des jours après avoir retiré les semaines
      'h' => $diff->h,
      'i' => $diff->i,
      's' => $diff->s,
    ];

    $units = [
      'y' => ['année', 'années'],
      'm' => ['mois', 'mois'],
      'w' => ['semaine', 'semaines'],
      'd' => ['jour', 'jours'],
      'h' => ['heure', 'heures'],
      'i' => ['minute', 'minutes'],
      's' => ['seconde', 'secondes'],
    ];

    $strings = [];
    foreach ($units as $key => $names) {
      if ($diffValues[$key] > 0) {
        $count = $diffValues[$key];
        $strings[] = $count . ' ' . ($count > 1 ? $names[1] : $names[0]);
      }
    }

    if (!$full) {
      $strings = array_slice($strings, 0, 1);
    }

    return $strings ? 'il y a ' . implode(', ', $strings) : 'à l\'instant';
  }

  public static function getPercentageClass($score)
  {
    $score = (int) $score;
    if ($score === 100) return 'percentage-100';
    if ($score >= 90) return 'percentage-99';
    if ($score >= 80) return 'percentage-89';
    if ($score >= 70) return 'percentage-79';
    if ($score >= 60) return 'percentage-69';
    if ($score >= 50) return 'percentage-59';
    if ($score >= 40) return 'percentage-49';
    if ($score >= 30) return 'percentage-39';
    if ($score >= 20) return 'percentage-29';
    if ($score >= 10) return 'percentage-19';
    if ($score > 0) return 'percentage-9';

    return 'percentage-0';
  }

  public static function formatDuration($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $formattedDuration = '';
    if ($hours > 0) {
      $formattedDuration .= $hours . 'h';
      $formattedDuration .= ' ' . str_pad((string)$minutes, 2, '0', STR_PAD_LEFT) . 'mn';
    } elseif ($minutes > 0) {
      $formattedDuration .= $minutes . 'mn';
      $formattedDuration .= ' ' . str_pad((string)$remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } else {
      $formattedDuration .= str_pad((string)$remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    }

    return $formattedDuration;
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

    // Vérification de l'existence de la clé 'title'
    if (isset($_options['title']) && $_options['title'] != '') {
      $defaults['label'] = $_options['title'];
    }

    // Vérification de l'existence de la clé 'message'
    if (isset($_options['message'])) {
      $params = explode('|', $_options['message']);
      foreach ($params as $param) {
        $keyValue = explode('=', $param);

        // Vérification de l'existence d'un couple clé=valeur
        if (
          count($keyValue) === 2
        ) {
          $key = trim($keyValue[0]);
          $value = trim($keyValue[1]);

          if ($key === 'video' && is_numeric($value)) {
            $defaults['video'] = (int)$value;
          }

          if (
            $key === 'duration' && is_numeric($value) && $value > 0
          ) {
            $defaults['duration'] = (int)$value;
          }

          if ($key === 'score' && is_numeric($value) && $value >= 0 && $value <= 100) {
            $defaults['score'] = (int)$value;
          }
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
    $cmdName = $this->getName();
    $link = $this->getConfiguration('request') ?? "";
    $user = $frigate->getConfiguration('userName');
    $password = $frigate->getConfiguration('password');
    // la pause doit etre entre 0.1s et 1.0s, on multiplie donc le resultat par 10000 pour faire le usleep
    $pause = config::byKey("pausePTZ", "frigate", 10);
    if ($pause < 0 || $pause > 10) {
      $pause = 10;
    }
    $pause = 10000 * $pause;

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
      case 'action_start_improve_contrast':
      case 'action_stop_improve_contrast':
        $this->publishCameraMessage($camera, 'improve_contrast/set', $logicalId === 'action_start_improve_contrast' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_improve_contrast':
        $this->toggleCameraSetting($frigate, $camera, 'info_improve_contrast', 'improve_contrast/set');
        break;
      case 'action_start_review_alerts':
      case 'action_stop_review_alerts':
        $this->publishCameraMessage($camera, 'review_alerts/set', $logicalId === 'action_start_review_alerts' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_review_alerts':
        $this->toggleCameraSetting($frigate, $camera, 'info_review_alerts', 'review_alerts/set');
        break;
      case 'action_start_review_detections':
      case 'action_stop_review_detections':
        $this->publishCameraMessage($camera, 'review_detections/set', $logicalId === 'action_start_review_detections' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_review_detections':
        $this->toggleCameraSetting($frigate, $camera, 'info_review_detections', 'review_detections/set');
        break;
      case 'action_start_review_descriptions':
      case 'action_stop_review_descriptions':
        $this->publishCameraMessage($camera, 'review_descriptions/set', $logicalId === 'action_start_review_descriptions' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_review_descriptions':
        $this->toggleCameraSetting($frigate, $camera, 'info_review_descriptions', 'review_descriptions/set');
        break;
      case 'action_start_object_descriptions':
      case 'action_stop_object_descriptions':
        $this->publishCameraMessage($camera, 'object_descriptions/set', $logicalId === 'action_start_object_descriptions' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_object_descriptions':
        $this->toggleCameraSetting($frigate, $camera, 'info_object_descriptions', 'object_descriptions/set');
        break;
      case 'action_start_notifications':
      case 'action_stop_notifications':
        $this->publishCameraMessage($camera, 'notifications/set', $logicalId === 'action_start_notifications' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_notifications':
        $this->toggleCameraSetting($frigate, $camera, 'info_notifications', 'notifications/set');
        break;
      case 'action_start_enabled':
      case 'action_stop_enabled':
        $this->publishCameraMessage($camera, 'enabled/set', $logicalId === 'action_start_enabled' ? 'ON' : 'OFF');
        break;
      case 'action_toggle_enabled':
        $this->toggleCameraSetting($frigate, $camera, 'info_enabled', 'enabled/set');
        break;
      case 'action_ptz_left':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_LEFT');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_right':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_RIGHT');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_up':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_UP');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_down':
        $this->publishCameraMessage($camera, 'ptz', 'MOVE_DOWN');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_stop':
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_zoom_in':
        $this->publishCameraMessage($camera, 'ptz', 'ZOOM_IN');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_ptz_zoom_out':
        $this->publishCameraMessage($camera, 'ptz', 'ZOOM_OUT');
        usleep($pause);
        $this->publishCameraMessage($camera, 'ptz', 'STOP');
        break;
      case 'action_preset_1':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_2':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_3':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_4':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_5':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_6':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_7':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_8':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_9':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_preset_0':
        $this->publishCameraMessage($camera, 'ptz', 'preset_' . $cmdName);
        break;
      case 'action_make_api_event':
        //score=12|video=1|duration=20
        $eventParams = self::parseEventParameters($_options);
        $result = frigate::createEvent($camera, $eventParams['label'], $eventParams['video'], $eventParams['duration'], $eventParams['score']);
        $deamon_info = frigate::deamon_info();
        if ($deamon_info['launchable'] === 'nok') {
          log::add('frigate', 'debug', "║ action_make_api_event result = " . json_encode($result));
          frigate::getEvent($result['event_id']);
        }
        break;
      case 'action_create_snapshot':
        frigate::createSnapshot($frigate);
        break;
      case 'action_http':
        // Gérer les variables user et password
        log::add('frigate', 'info', "║ 01 action_http $logicalId $link");
        $user = $frigate->getConfiguration("userName") ?? "";
        $password = $frigate->getConfiguration("password") ?? "";
        $link = str_replace("#user#", $user, $link);
        $link = str_replace("#password#", $password, $link);
        // Gérer les actions HTTP statiques
        log::add('frigate', 'info', "║ 02 action_http $logicalId $link");
        $response = self::getCurlcmd($link, $user, $password);
        if ($response !== false) {
          $frigate->getCmd(null, 'info_http')->event($response);
        } else {
          log::add('frigate', "error", "Erreur lors de l'appel HTTP: $link");
        }
        break;
      default:
        // Gérer les actions HTTP dynamiques
        if (strpos($logicalId, 'action_http_') === 0) {
          $response = self::getCurlcmd($link, $user, $password);
          if ($response !== false) {
            $frigate->getCmd(null, 'info_http')->event($response);
          } else {
            log::add('frigate', "error", "Erreur lors de l'appel HTTP: $link");
          }
        }
    }
  }
  private function getCurlcmd($link, $username, $password)
  {

    $ch = curl_init();
    $verbose = fopen('php://temp', 'w+'); // Flux temporaire pour verbose

    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose); // Rediriger verbose vers ce flux temporaire
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); // Utiliser l'authentification Digest
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password"); // Nom d'utilisateur et mot de passe

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add('frigate', "error", "Erreur cURL: " . curl_error($ch));
    } else {
      log::add('frigate', 'debug', "║ Resultat de la commande HTTP : " . $response);
    }

    // Récupérer les informations verbose
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    log::add('frigate', 'debug', "║ cURL verbose log: " . $verboseLog);

    fclose($verbose);
    curl_close($ch);
    return $response;
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
