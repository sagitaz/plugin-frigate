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
  /*     * *************************Constantes***************************** */

  // Modes de saveURL(), passés dans son paramètre $mode
  /** Snapshot, clip ou preview d'évènement, selon le type demandé. */
  public const SAVE_MODE_DEFAULT = 0;
  /** Miniature d'évènement, sans redimensionnement. */
  public const SAVE_MODE_THUMBNAIL = 1;
  /** Dernière image de la caméra. */
  public const SAVE_MODE_LATEST = 2;
  /** Capture manuelle depuis une URL externe. */
  public const SAVE_MODE_SNAPSHOT = 3;

  /**
   * Bascules MQTT d'une caméra : clé Frigate => suffixe du type générique JeeMate.
   *
   * createMQTTcmds() s'en sert pour créer les commandes, dans l'ordre de la table,
   * et processCameraData() pour les mettre à jour. Le type générique complet vaut
   * JEEMATE_CAMERA_{suffixe}_STATE pour les commandes d'information, et
   * JEEMATE_CAMERA_{suffixe}_SET_{ON|OFF|TOGGLE} pour les commandes d'action.
   *
   * @var array<string, string>
   */
  private const MQTT_TOGGLES = [
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
    'enabled'             => 'ENABLED',
  ];

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

  /**
   * Donne aux équipements sans fréquence de rafraîchissement celle de la configuration générale (refresh_snapshot).
   *
   * Une configuration absente vaut une chaîne vide : c'est la valeur par défaut de getConfiguration().
   * L'équipement n'est sauvegardé que s'il a reçu au moins une valeur.
   *
   * @return void
   */
  public static function setConfigEqlogic()
  {
    $refresh = config::byKey('refresh_snapshot', 'frigate', 5);
    foreach (self::byType('frigate') as $eqLogic) {
      $changed = false;
      foreach (['normal::refresh', 'normal::mobilerefresh'] as $key) {
        $current = $eqLogic->getConfiguration($key);
        if ($current === '' || $current === null) {
          $eqLogic->setConfiguration($key, $refresh);
          $changed = true;
        }
      }
      if ($changed) {
        $eqLogic->save();
      }
    }
  }

  /**
   * Traitement commun à toutes les fréquences de cron.
   *
   * Purge le dossier data et les évènements anciens ; hors crons 1, 5, 10 et 15 minutes, supprime aussi les
   * évènements restés en type new ou update. Récupère ensuite les évènements et les statistiques de Frigate si
   * la fréquence est activée dans la configuration et que les commandes info_Cron et info_enabled de
   * l'équipement Events, quand elles existent, sont à 1. Avec MQTT Manager opérationnel, les crons 1, 5, 10 et
   * 15 minutes ne font rien. Un cron qui trouve le drapeau cron::run levé ne s'exécute pas et le rabaisse.
   *
   * @param string $frequence Clé de configuration de la fréquence, par exemple functionality::cron5::enable
   * @return void
   */
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

    // Évènements incomplets et verrous, aux crons de 30 minutes et plus
    if (!($frequence === "functionality::cron::enable" ||
      $frequence === "functionality::cron5::enable" ||
      $frequence === "functionality::cron10::enable" ||
      $frequence === "functionality::cron15::enable")) {
      self::reconcileEvents();
      self::cleanEventLocks();
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


  /**
   * Indique si le serveur Frigate répond sur /api/version, avec un délai de 5 s.
   *
   * @return bool false aussi quand l'URL ou le port n'est pas configuré
   */
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

  /**
   * Cron exécuté par Jeedom toutes les minutes.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cron()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron::enable');
  }
  /**
   * Cron exécuté par Jeedom toutes les 5 minutes.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cron5()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron5 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron5::enable');
  }
  /**
   * Cron exécuté par Jeedom toutes les 10 minutes.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cron10()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron10 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron10::enable');
  }
  /**
   * Cron exécuté par Jeedom toutes les 15 minutes.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cron15()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron15 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron15::enable');
  }
  /**
   * Cron exécuté par Jeedom toutes les 30 minutes.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cron30()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. Cron30 non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cron30::enable');
  }
  /**
   * Cron exécuté par Jeedom toutes les heures.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur » puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
  public static function cronHourly()
  {
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, "warning", "Le serveur Frigate n'est pas disponible. CronHourly non exécuté.");
      return;
    }
    self::checkFrigateStatus();
    self::execCron('functionality::cronHourly::enable');
  }
  /**
   * Cron exécuté par Jeedom chaque jour.
   *
   * Si le serveur Frigate répond, met à jour la commande « status serveur », signale une nouvelle version de
   * Frigate puis exécute le traitement de cette fréquence.
   *
   * @return void
   */
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

  /**
   * Retourne les informations de configuration à joindre à un post Community.
   *
   * Appelée par Jeedom lors de la création semi-automatique d'un sujet sur le forum.
   *
   * @return string
   */
  public static function getConfigForCommunity()
  {
    $communityInfo  = "```\n";
    $communityInfo .= 'URL : ' . self::getUrlFrigate() . "\n";
    $communityInfo .= 'MQTT topic : ' . config::byKey('topic', 'frigate') . "\n";
    $communityInfo .= 'Frigate : ' . config::byKey('frigate_version', 'frigate') . "\n";
    $communityInfo .= 'Plugin : ' . config::byKey('pluginVersion', 'frigate') . "\n";
    $communityInfo .= "``` \n";
    $communityInfo .= "<b>Informations à ajouter</b> \n";
    $communityInfo .= "Afin de traiter au mieux votre demande d'aide, merci d'ajouter les logs du plugin Frigate en mode debug et nettoyé, pas de 6 mois. \n";
    $communityInfo .= "Vous pouvez également ajouter les logs HTTP_ERROR s'ils comportent des infos sur Frigate. \n";

    return $communityInfo;
  }


  /*     * *********************Méthodes d'instance************************* */

  /**
   * Appelée par Jeedom avant la création de l'équipement, sans traitement.
   *
   * @return void
   */
  public function preInsert() {}

  /**
   * Appelée par Jeedom après la création de l'équipement, sans traitement.
   *
   * @return void
   */
  public function postInsert() {}

  /**
   * Appelée par Jeedom avant la mise à jour de l'équipement, sans traitement.
   *
   * @return void
   */
  public function preUpdate() {}

  /**
   * Appelée par Jeedom après la mise à jour de l'équipement, sans traitement.
   *
   * @return void
   */
  public function postUpdate() {
    }

  /**
   * Appelée par Jeedom avant chaque sauvegarde de l'équipement.
   *
   * Retire le schéma http(s):// de l'URL Frigate de la configuration générale. Pour une caméra : valeurs par
   * défaut du PTZ et du nombre de presets (10 au plus), URL de la dernière image avec ses options d'affichage,
   * puis mise à jour des commandes « SNAPSHOT LIVE » et « RTSP » quand leur valeur a changé.
   *
   * @return void
   */
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

  /**
   * Appelée par Jeedom après chaque sauvegarde de l'équipement, sans traitement.
   *
   * @return void
   */
  public function postSave() {}

  /**
   * Appelée par Jeedom avant la suppression de l'équipement, sans traitement.
   *
   * @return void
   */
  public function preRemove() {}

  /**
   * Appelée par Jeedom après la suppression de l'équipement.
   *
   * Supprime les évènements de la caméra, favoris compris, avec leurs fichiers.
   *
   * @return void
   */
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
        self::cleanDbEvent($eventId, $event);
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


  /**
   * Construit le widget de l'équipement.
   *
   * Les caméras utilisent le template du plugin, les autres équipements le widget standard de Jeedom. La
   * version « panel » s'affiche avec le template dashboard.
   *
   * @param string $_version Version d'affichage : dashboard, mobile ou panel
   * @return string HTML du widget
   */
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
    $enabledCmd = $this->getCmd('info', 'info_enabled');
    if ($enabledCmd) {
      $value = $enabledCmd->execCmd();
      $replace['#enabled#'] = ($value !== null && $value !== '') ? $value : 1;
    } else {
      $replace['#enabled#'] = 1;
    }
    if ($this->getConfiguration('normal::refresh') != '') {
      $replace['#refresh#']       = (float)$this->getConfiguration('normal::refresh') * 1000;
    } else {
      $replace['#refresh#']         = (float)(config::byKey('refresh_snapshot', 'frigate', 5)) * 1000;
    }

    $replace['#actions#']      = $this->buildActions();
    $replace['#iaActions#'] = $this->buildIaActions();
    $replace['#detectNow#']    = $this->buildDetectNow();
    $replace['#actionsPreset#'] = $this->buildPresetsSelect();
    $replace['#ptzWidget#']    = $this->buildPtzWidget();
    $replace['#ptzZoom#']      = $this->buildPtzZoom();
    $replace['#actionsModal#'] = $replace['#actions#'] . $this->buildPtzModal() . $replace['#actionsPreset#'];

    return $this->renderTemplate($replace, $_version, $panel);
  }

  /**
   * Retourne « camera » pour un équipement caméra, une chaîne vide sinon.
   *
   * @return string
   */
  private function detectType(): string
  {
    return strpos($this->getLogicalId(), "eqFrigateCamera_") !== false ? "camera" : "";
  }

  /**
   * Indique si l'affichage demandé est la version « panel », et la remplace alors par « dashboard ».
   *
   * @param string $_version Version d'affichage, modifiée par référence
   * @return bool
   */
  private function isPanelVersion(string &$_version): bool
  {
    if ($_version === 'panel') {
      $_version = 'dashboard';
      return true;
    }
    return false;
  }

  /**
   * Construit un bouton icône qui exécute une commande au clic.
   *
   * @param string $icon     Classes de l'icône Font Awesome
   * @param string $title    Infobulle
   * @param int    $cmdId    Identifiant de la commande à exécuter
   * @param string $cssClass Classe CSS, suffixée par l'identifiant de l'équipement
   * @return string HTML du bouton
   */
  private function buildBtnIcon(string $icon, string $title, int $cmdId, string $cssClass): string
  {
    return '<div class="btn-icon">'
      . '<i class="' . $icon . ' ' . $cssClass . $this->getId() . '" title="' . $title . '" onclick="execAction(' . $cmdId . ')"></i>'
      . '</div>';
  }

  /**
   * Construit le bouton d'une bascule marche/arrêt, selon l'état de sa commande info.
   *
   * Le bouton exécute la commande qui inverse l'état. Rien n'est affiché si l'une des deux commandes d'action
   * manque ou est masquée.
   *
   * @param string $startLogical logicalId de la commande de mise en marche
   * @param string $stopLogical  logicalId de la commande d'arrêt
   * @param string $infoLogical  logicalId de la commande info d'état
   * @param string $iconOn       Icône affichée quand l'état est à 0
   * @param string $iconOff      Icône affichée quand l'état est à 1
   * @param string $titleOn      Infobulle quand l'état est à 0
   * @param string $titleOff     Infobulle quand l'état est à 1
   * @return string HTML du bouton, vide s'il n'est pas affichable
   */
  private function buildToggleAction(string $startLogical, string $stopLogical, string $infoLogical, string $iconOn, string $iconOff, string $titleOn, string $titleOff): string
  {
    $on   = $this->getCmd('action', $startLogical);
    $off  = $this->getCmd('action', $stopLogical);
    $etat = $this->getCmd('info', $infoLogical);

    if (!is_object($on) || !is_object($off) || !is_object($etat)) return '';
    if ($on->getIsVisible() != 1 || $off->getIsVisible() != 1) return '';

    if ($etat->execCmd() == 0) {
      return $this->buildBtnIcon($iconOn, $titleOn, $on->getId(), 'iconActionOff');
    } else {
      return $this->buildBtnIcon($iconOff, $titleOff, $off->getId(), 'iconAction');
    }
  }

  /**
   * Construit les boutons d'action du widget.
   *
   * Bascules enregistrement, snapshots, détection, audio et mouvement, puis création d'évènement et de capture.
   *
   * @return string HTML des boutons
   */
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

  /**
   * Construit une ligne interrupteur du panneau des options IA, selon l'état de sa commande info.
   *
   * @param string $startLogical logicalId de la commande de mise en marche
   * @param string $stopLogical  logicalId de la commande d'arrêt
   * @param string $infoLogical  logicalId de la commande info d'état
   * @param string $label        Libellé affiché
   * @param string $title        Infobulle du libellé
   * @return string HTML de la ligne, vide si l'une des commandes manque ou est masquée
   */
  private function buildIaToggleRow(string $startLogical, string $stopLogical, string $infoLogical, string $label, string $title = ''): string
  {
    $on   = $this->getCmd('action', $startLogical);
    $off  = $this->getCmd('action', $stopLogical);
    $etat = $this->getCmd('info', $infoLogical);

    if (!is_object($on) || !is_object($off) || !is_object($etat)) return '';
    if ($on->getIsVisible() != 1 || $off->getIsVisible() != 1) return '';

    $isActive = $etat->execCmd() != 0;
    $cmdId    = $isActive ? $off->getId() : $on->getId();

    return '<div class="ia-toggle-row">'
      . '<span class="ia-toggle-label" title="' . $title . '">' . $label . '</span>'
      . '<i class="' . ($isActive ? 'fas fa-toggle-on' : 'fas fa-toggle-off') . ' ia-toggle-icon" onclick="execAction(' . $cmdId . ')"></i>'
      . '</div>';
  }

  /**
   * Construit le panneau des options IA : activation de la caméra, alertes et détections des activités,
   * descriptions par IA générative.
   *
   * @return string HTML des lignes
   */
  private function buildIaActions(): string
  {
    return
        $this->buildIaToggleRow('action_start_enabled', 'action_stop_enabled', 'info_enabled', '{{Activer la caméra}}', '{{Active ou désactive la caméra. La désactivation interrompt complètement le traitement des flux de la caméra par Frigate. La détection, l\'enregistrement et le débogage deviennent alors indisponibles. À partir de Frigate 0.18, l\'état est conservé au redémarrage de Frigate ; avant, la caméra revient à sa configuration.}}')
      . $this->buildIaToggleRow('action_start_review_alerts',       'action_stop_review_alerts',       'info_review_alerts',       '{{Activités : alertes}}', '{{Active ou désactive temporairement les alertes pour cette caméra jusqu\'au redémarrage de Frigate. Lorsque cette option est désactivée, aucune activité nouvelle n\'est générée.}}')
      . $this->buildIaToggleRow('action_start_review_detections',   'action_stop_review_detections',   'info_review_detections',   '{{Activités : détections}}', '{{Active ou désactive temporairement les alertes et les détections pour cette caméra jusqu\'au redémarrage de Frigate. Lorsque cette option est désactivée, aucune activité nouvelle n\'est générée.}}')
      . $this->buildIaToggleRow('action_start_review_descriptions', 'action_stop_review_descriptions', 'info_review_descriptions', '{{Descriptions des activités}}', '{{Activez ou désactivez temporairement les descriptions d\'activités par IA générative jusqu\'au redémarrage. Si désactivé, l\'IA ne sera plus sollicitée pour décrire les activités sur cette caméra.}}')
      . $this->buildIaToggleRow('action_start_object_descriptions', 'action_stop_object_descriptions', 'info_object_descriptions', '{{Descriptions d\'objets}}', '{{Activez ou désactivez temporairement les descriptions par IA générative jusqu\'au redémarrage. Si désactivé, l\'IA ne sera plus sollicitée pour décrire les objets suivis sur cette caméra.}}');
  }
  /**
   * Construit les icônes des objets détectés en ce moment : une par commande info_detect_* visible et à 1,
   * hors info_detect_all.
   *
   * @return string HTML des icônes
   */
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

  /**
   * Construit la liste déroulante des presets PTZ et des commandes HTTP visibles.
   *
   * @return string HTML de la liste, vide s'il n'y a rien à proposer
   */
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

  /**
   * Définition des boutons PTZ : logicalId, icône et classe CSS du widget, classe du bouton, infobulle, icône de la modale.
   *
   * @return array<int, array<int, string>>
   */
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

  /**
   * Construit les boutons PTZ du widget, précédés du cercle de fond quand au moins un est visible.
   *
   * @return string HTML des boutons
   */
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

  /**
   * Construit les boutons PTZ de la modale, zoom compris.
   *
   * @return string HTML des boutons
   */
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

  /**
   * Construit les boutons de zoom PTZ du widget.
   *
   * @return string HTML des boutons
   */
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



  /**
   * Applique le template de la caméra et met le widget en cache.
   *
   * En mode « image seule » (option de l'équipement pour le dashboard ou le panel), les boutons sont retirés.
   *
   * @param array<string, string> $replace  Valeurs des balises du template
   * @param string                $_version Version d'affichage demandée
   * @param bool                  $panel    Affichage dans le panel
   * @return string HTML du widget
   */
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
  /**
   * Retourne le topic MQTT racine de Frigate, tel que configuré.
   *
   * @return string
   */
  private static function getTopic()
  {
    return config::byKey('topic', 'frigate');
  }

  /**
   * Retourne l'adresse du serveur Frigate sous la forme hôte:port, sans schéma.
   *
   * @return string|false false si l'URL ou le port n'est pas configuré
   */
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

  /**
   * Publie les messages d'installation : remerciement, puis avertissement si Debian ou Jeedom sont plus anciens
   * que les versions recommandées.
   *
   * @return void
   */
  public static function addMessages()
  {
    message::add('frigate', __("Merci d'avoir installé le plugin. Pour toutes les demandes d'aide, veuillez contacter le support sur Discord ou sur Community.", __FILE__));
    $system = system::getOsVersion();
    if (version_compare($system, "11", "<")) {
      message::add('frigate', str_replace('#version#', $system, __("Attention, vous utilisez la version #version# de Debian, aucun support n'est disponible. La version 11 de Debian est recommandée.", __FILE__)));
    }
    $jeedom = jeedom::version();
    if (version_compare($jeedom, "4.4", "<")) {
      message::add('frigate', str_replace('#version#', $jeedom, __("Attention, vous utilisez la version #version# de Jeedom. La version 4.4.x de Jeedom est recommandée.", __FILE__)));
    }
  }

  /**
   * Publie un message MQTT sur un sous-topic d'une caméra.
   *
   * @param string $camera   Nom de la caméra dans Frigate
   * @param string $subTopic Sous-topic, par exemple detect/set
   * @param string $payload  Charge utile
   * @return void
   */
  public static function publish_camera_message(string $camera, string $subTopic, string $payload)
  {
    self::publish_message("{$camera}/{$subTopic}", $payload);
  }

  /**
   * Publie un message MQTT sous le topic racine de Frigate, par MQTT Manager.
   *
   * @param string $subTopic Sous-topic
   * @param string $payload  Charge utile
   * @return void
   */
  public static function publish_message(string $subTopic, string $payload)
  {
    log::add(__CLASS__, 'debug', "║ publish_message : " . self::getTopic() . "/{$subTopic} avec payload : {$payload}");
    mqtt2::publish(self::getTopic() . "/{$subTopic}", $payload);
  }

  /**
   * Envoie une requête HTTP au serveur Frigate et retourne sa réponse.
   *
   * Les paramètres partent en JSON pour POST et PUT. La commande curl équivalente est écrite au log en debug.
   *
   * @param string $function   Nom de l'appelant, repris dans le log
   * @param string $url        URL de la requête
   * @param mixed  $params     Paramètres envoyés en JSON (POST et PUT)
   * @param bool   $decodeJson Décoder la réponse JSON
   * @param string $method     Méthode HTTP : GET, POST, PUT ou DELETE
   * @return mixed Réponse, décodée ou brute ; null en cas d'erreur, de réponse vide ou de code HTTP autre que 200
   */
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

    // log de la commande curl
    $curl_cmd = "curl -k -X " . $method;
    if ($method !== 'DELETE') {
      $curl_cmd .= " -H 'Content-Type: application/json'";
      $curl_cmd .= " -d '" . json_encode($params) . "'";
    }
    $curl_cmd .= " '" . $url . "'";
    log::add(__CLASS__, 'debug', "║ Commande exécutée : " . $curl_cmd);
    // Fin du log

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, "error", "║ Erreur getcURL (" . $method . ") : " . curl_error($ch));
      curl_close($ch);
      return null;
    }

    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!is_string($data)) {
      log::add(__CLASS__, "error", "║ Erreur getcURL (" . $method . ") : réponse vide.");
      return null;
    }
    if ($httpCode !== 200) {
      log::add(__CLASS__, "error", "║ Erreur getcURL (" . $method . ") : code HTTP " . $httpCode . " sur " . $url);
      return null;
    }

    $response = $decodeJson ? json_decode($data, true) : $data;
    log::add(__CLASS__, 'debug', "║ " . $function . " : requête " . $method . " exécutée.");

    return $response;
  }

  /**
   * Envoie une requête HTTP POST au serveur Frigate (voir getcURL()).
   *
   * @param string $function   Nom de l'appelant, repris dans le log
   * @param string $url        URL de la requête
   * @param mixed  $params     Paramètres envoyés en JSON
   * @param bool   $decodeJson Décoder la réponse JSON
   * @return mixed Réponse, null en cas d'échec
   */
  private static function postcURL($function, $url, $params = null, $decodeJson = true)
  {
    return self::getcURL($function, $url, $params, $decodeJson, 'POST');
  }

  /**
   * Envoie une requête HTTP PUT au serveur Frigate, avec un objet JSON vide à défaut de paramètres (voir getcURL()).
   *
   * @param string $function   Nom de l'appelant, repris dans le log
   * @param string $url        URL de la requête
   * @param mixed  $params     Paramètres envoyés en JSON
   * @param bool   $decodeJson Décoder la réponse JSON
   * @return mixed Réponse, null en cas d'échec
   */
  private static function putcURL($function, $url, $params = null, $decodeJson = true)
  {
    if (empty($params)) {
      $params = new stdClass();
    }
    return self::getcURL($function, $url, $params, $decodeJson, 'PUT');
  }

  /**
   * Envoie une requête HTTP DELETE au serveur Frigate.
   *
   * @param string $url URL absolue de la ressource à supprimer
   * @return array<string, mixed>|null Réponse décodée, null en cas d'échec
   */
  private static function deletecURL($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, "error", "║ Erreur deletecURL : " . curl_error($ch));
      curl_close($ch);
      return null;
    }
    curl_close($ch);

    if (!is_string($data)) {
      log::add(__CLASS__, "error", "║ Erreur deletecURL : réponse vide pour " . $url);
      return null;
    }

    $response = json_decode($data, true);
    log::add(__CLASS__, 'debug', "║ Suppression sur le serveur Frigate : " . json_encode($response));

    return $response;
  }

  /**
   * Récupère les statistiques de Frigate et met à jour les commandes correspondantes.
   *
   * @return void
   */
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

  /**
   * Récupère les informations PTZ d'une caméra, dont la liste de ses presets.
   *
   * @param string $camera Nom de la caméra dans Frigate
   * @return array|null Réponse de /api/<caméra>/ptz/info, null en cas d'échec
   */
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

  /**
   * Crée un évènement manuel dans Frigate.
   *
   * @param string    $camera     Nom de la caméra dans Frigate
   * @param string    $label      Label de l'évènement
   * @param int       $video      1 pour inclure l'enregistrement vidéo
   * @param int|float $duration   Durée en secondes
   * @param int|float $score      Score en %, ramené entre 0 et 100
   * @param string    $subLabel   Sous-label
   * @param int|null  $preCapture Secondes enregistrées avant la création (Frigate 0.18 et plus), null pour le
   *                              pré-enregistrement de la caméra
   * @return mixed Réponse de Frigate, null en cas d'échec
   */
  public static function createEvent($camera, $label, $video = 1, $duration = 20, $score = 30, $subLabel = '', $preCapture = null)
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
    // Frigate antérieur à 0.18 ignore ce champ
    if ($preCapture !== null) {
      $params['pre_capture'] = (int)$preCapture;
      log::add(__CLASS__, 'debug', "║ pre_capture : {$params['pre_capture']}");
    }
    $response = self::postcURL("CreateEvent", $resultURL, $params);

    log::add(__CLASS__, 'debug', "╚════════════════════════ END CREATE EVENT ═══════════════════");
    return $response;
  }

  /**
   * Modifie un paramètre du fichier de configuration de Frigate par l'API.
   *
   * La modification n'est prise en compte qu'après un redémarrage de Frigate. Un évènement Jeedom
   * frigate::config signale la mise à jour à l'interface.
   *
   * @param string $config Paramètre sous la forme chemin=valeur, par exemple cameras.jardin.enabled=true
   * @return mixed Réponse de Frigate, null en cas d'échec
   * @todo Ajouter des méthodes qui l'appellent pour d'autres paramètres du fichier de configuration.
   */
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

  /**
   * Modifie un paramètre de la configuration d'une caméra dans Frigate (voir saveConfig()).
   *
   * @param string $camera Nom de la caméra dans Frigate
   * @param string $config Paramètre sous la forme chemin=valeur, relatif à la caméra
   * @return mixed Réponse de Frigate, null en cas d'échec
   */
  public static function saveCameraConfig($camera, $config)
  {
    $response = self::saveConfig("cameras.{$camera}.{$config}");

    return $response;
  }

  /**
   * Active ou désactive une caméra dans la configuration de Frigate (voir saveConfig()).
   *
   * @param string     $camera Nom de la caméra dans Frigate
   * @param int|string $enable 1 pour activer, toute autre valeur pour désactiver
   * @return mixed Réponse de Frigate, null en cas d'échec
   * @todo Ajouter d'autres paramètres de caméra.
   */
  public static function enableCamera($camera, $enable)
  {
    $enabled = $enable == 1 ? 'true' : 'false';
    $response = self::saveCameraConfig($camera, "enabled={$enabled}");

    return $response;
  }

  /**
   * Récupère les logs d'un service de Frigate.
   *
   * @param string $service Nom du service, par exemple frigate, go2rtc ou nginx
   * @return string|null Logs bruts, null en cas d'échec
   */
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

  /**
   * Enregistre les évènements dans un fichier JSON de suivi et retourne ceux qui n'y figuraient pas encore.
   *
   * La comparaison porte sur start_time. À la première utilisation, le fichier est créé et tous les évènements
   * sont retournés.
   *
   * @param string                           $filePath Chemin du fichier de suivi
   * @param array<int, array<string, mixed>> $events   Évènements reçus de Frigate
   * @return array<int, array<string, mixed>> Évènements nouveaux
   */
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
  /**
   * Récupère un évènement Frigate par son identifiant et le traite (voir getEvents()).
   *
   * @param string|null $id   Identifiant Frigate ; rien n'est fait s'il est vide
   * @param string      $type Type d'évènement : new, update ou end
   * @param bool        $wait Attendre le délai configuré avant de télécharger les médias (voir getEventInfos())
   * @return void
   */
  public static function getEvent($id = null, $type = 'end', $wait = true)
  {
    if ($id == null) return;

    self::getEvents(false, array(), $type, $id, null, $wait);
  }

  /**
   * Récupère des évènements Frigate et les enregistre en base.
   *
   * Trois sources : un évènement précis quand $id est fourni, les évènements reçus par MQTT quand $mqtt est
   * vrai, sinon l'API, dont seuls les évènements absents du fichier de suivi data/frigate_events.json sont
   * traités. Seuls les évènements des recovery_days derniers jours sont gardés (7 par défaut). Le dossier data
   * est d'abord purgé s'il dépasse la taille maximale. Chaque évènement est créé ou mis à jour en base avec
   * ses médias, puis publié sur les commandes. Le type reçu ne fait qu'avancer le type en base (voir
   * advanceType()). Quand le type d'un évènement change, sa miniature et son snapshot sont retéléchargés. Un
   * évènement déjà terminé en base ne déclenche pas d'action.
   *
   * @param bool                             $mqtt         Évènements reçus par MQTT
   * @param array<int, array<string, mixed>> $events       Évènements reçus par MQTT
   * @param string                           $type         Type d'évènement : new, update ou end
   * @param string|null                      $id           Identifiant Frigate d'un évènement précis
   * @param int|null                         $recoveryDays 1 pour limiter la récupération au dernier jour
   * @param bool                             $wait         Attendre le délai configuré avant de télécharger les médias
   * @return void
   */
  public static function getEvents($mqtt = false, $events = array(), $type = 'end', $id = null, $recoveryDays = null, $wait = true)
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
      $lock = self::lockEvent($event['id']);
      try {
        $frigate = frigate_events::byEventId($event['id']);
        $previousType = is_object($frigate) ? $frigate->getType() : null;
        $eventType = self::advanceType($previousType, $type);

        log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:START EVENT:/fg: ═══════════════════");
        if ($eventType !== $type) {
          log::add(__CLASS__, 'debug', "║ Type reçu « " . $type . " » ignoré, l'évènement est déjà « " . $eventType . " ».");
        }

        // Un changement de type (new, update, end) retélécharge les médias : Frigate améliore le snapshot
        // au fil de l'évènement, celui du premier message n'est pas le définitif.
        $force = is_object($frigate) && $previousType != $eventType;
        $infos = self::getEventinfos($mqtt, $event, $force, $eventType, $wait);

        if (!$frigate) {
          log::add(__CLASS__, 'debug', "║ Events (type=" . $eventType . ") => " . json_encode($event));
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
          $frigate->setType($eventType);
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
            'Type' => $eventType,
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
            }
          }

          // La description de l'IA peut arriver seule, par la revue genai d'un évènement terminé
          if (isset($event['data']['description']) && $event['data']['description'] !== $frigate->getRecognition_description()) {
            $updated = true;
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
            // Les actions d'un évènement déjà terminé ont été exécutées à sa fin
            self::majEventsCmds($frigate, $previousType !== 'end');
            log::add(__CLASS__, 'debug', "║ Evénement Frigate mis à jour et sauvegardé, event ID: " . $event['id']);
          } else {
            log::add(__CLASS__, 'debug', "║ Pas de mise à jour pour event ID: " . $event['id']);
          }
        }
        log::add(__CLASS__, 'debug', "╚════════════════════════ END EVENT ═══════════════════");
      } finally {
        self::unlockEvent($lock);
      }
    }
  }

  /**
   * Retourne le type à enregistrer pour un évènement : le type reçu, sauf s'il ferait reculer le type en base.
   *
   * Les types avancent dans l'ordre new, update, end. Un message tardif, comme une revue new ou update d'un
   * objet encore suivi, laisse donc un évènement terminé en end, avec son clip. Un type reçu inconnu ne
   * remplace pas un type connu.
   *
   * @param string|null $current  Type en base, null pour un évènement absent ou sans type
   * @param string|null $received Type reçu
   * @return string|null
   */
  private static function advanceType($current, $received)
  {
    $order = ['new' => 1, 'update' => 2, 'end' => 3];
    if (isset($order[$current]) && (!isset($order[$received]) || $order[$current] > $order[$received])) {
      return $current;
    }
    return $received;
  }

  /**
   * Prend le verrou d'un évènement, pour que deux messages du même évènement ne soient pas traités en même temps.
   *
   * Deux messages reçus à une seconde d'écart sont traités par deux processus PHP : sans verrou, aucun ne trouve
   * l'évènement en base et chacun le crée. Le verrou est un flock sur un fichier du dossier temporaire du plugin,
   * que PHP rend à la fin de la requête, même après une erreur fatale ; un verrou SQL (GET_LOCK) survivrait au
   * processus, les connexions PDO du core étant persistantes. Le second message attend la fin du premier, au plus
   * $timeout secondes, puis est traité sans verrou.
   *
   * @param string|null $eventId Identifiant Frigate de l'évènement
   * @param int         $timeout Attente maximale, en secondes
   * @return resource|null Verrou à rendre avec unlockEvent(), null s'il n'a pas pu être pris ou sans identifiant
   */
  private static function lockEvent($eventId, $timeout = 60)
  {
    if ($eventId === null || $eventId === '') {
      return null;
    }
    $dir = jeedom::getTmpFolder(__CLASS__) . '/locks';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
      log::add(__CLASS__, 'warning', "║ Dossier des verrous impossible à créer : " . $dir);
      return null;
    }
    $path = $dir . '/' . preg_replace('/[^A-Za-z0-9._-]/', '_', $eventId) . '.lock';
    $handle = @fopen($path, 'c');
    if ($handle === false) {
      log::add(__CLASS__, 'warning', "║ Verrou de l'évènement " . $eventId . " impossible à ouvrir, traitement sans verrou.");
      return null;
    }
    $start = microtime(true);
    while (!flock($handle, LOCK_EX | LOCK_NB)) {
      if (microtime(true) - $start >= $timeout) {
        log::add(__CLASS__, 'warning', "║ Verrou de l'évènement " . $eventId . " non obtenu après " . $timeout . " s, traitement sans verrou.");
        fclose($handle);
        return null;
      }
      usleep(200000);
    }
    // Date d'utilisation, lue par cleanEventLocks()
    @touch($path);
    return $handle;
  }

  /**
   * Rend le verrou d'un évènement pris par lockEvent().
   *
   * @param resource|null $handle Verrou, null s'il n'a pas été pris
   * @return void
   */
  private static function unlockEvent($handle)
  {
    if (is_resource($handle)) {
      flock($handle, LOCK_UN);
      fclose($handle);
    }
  }

  /**
   * Supprime les fichiers de verrou des évènements inutilisés depuis plus d'un jour.
   *
   * @return void
   */
  private static function cleanEventLocks()
  {
    $files = glob(jeedom::getTmpFolder(__CLASS__) . '/locks/*.lock');
    foreach ($files ?: [] as $file) {
      if (filemtime($file) < time() - 86400) {
        @unlink($file);
      }
    }
  }

  /**
   * Publie un évènement vers l'interface, par un évènement Jeedom frigate::event.
   *
   * @param frigate_events $event     Évènement à publier
   * @param int|string     $eqLogicId Identifiant de l'équipement caméra
   * @return void
   */
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

  /**
   * Télécharge les médias d'un évènement et prépare ses valeurs à enregistrer.
   *
   * Attend d'abord le délai configuré (sleep, de 0 à 10 s ; 5 s si la valeur sort de cette plage) pour laisser
   * à Frigate le temps de produire les médias, sauf avec $wait à faux. Télécharge ensuite la miniature, le
   * snapshot, le clip (évènement de type end seulement) et l'aperçu GIF.
   *
   * @param bool                 $mqtt  Évènement reçu par MQTT (scores à la racine) ou par l'API (scores dans data)
   * @param array<string, mixed> $event Évènement Frigate
   * @param bool                 $force Retélécharger les médias déjà présents
   * @param string               $type  Type d'évènement : new, update ou end
   * @param bool                 $wait  Attendre le délai configuré
   * @return array<string, mixed> URL et disponibilité des médias, dates, scores en %, zones et label
   */
  public static function getEventInfos($mqtt, $event, $force = false, $type = "end", $wait = true)
  {
    $dir = dirname(__FILE__, 3) . "/data/" . $event['camera'];
    $sleep = config::byKey('sleep', 'frigate');
    if ($sleep < 0 || $sleep > 10) {
      $sleep = 5;
    } else {
      $sleep = intval($sleep);
    }
    // Fonction de vérification et téléchargement
    if ($wait) {
      sleep($sleep);
    }
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

  /**
   * Retourne la miniature ou le snapshot d'un évènement, téléchargé s'il n'est pas déjà présent.
   *
   * Un fichier webp est préféré à un jpg, et un jpg en double est supprimé. Un snapshot n'est téléchargé que
   * si Frigate en signale un (has_snapshot). Quand un téléchargement forcé échoue, le fichier déjà présent
   * est conservé.
   *
   * @param string               $dir         Dossier local de la caméra
   * @param array<string, mixed> $event       Évènement Frigate
   * @param bool                 $isThumbnail true pour la miniature, false pour le snapshot
   * @param bool                 $force       Retélécharger le fichier même s'il est présent
   * @return array{url: string, has: int} URL du fichier (« null » s'il n'y en a pas) et disponibilité
   */
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
      if (!$isThumbnail && $event['has_snapshot'] != "true") {
        log::add(__CLASS__, 'debug', "║ Has Snapshot: false → téléchargement annulé pour ID: $id");
        $img = "error";
      } else {
        log::add(__CLASS__, 'debug', "║ Téléchargement du fichier $type pour ID: $id");
        $mode = $isThumbnail ? self::SAVE_MODE_THUMBNAIL : self::SAVE_MODE_DEFAULT;
        $img = self::saveURL($id, $isThumbnail ? null : "snapshot", $camera, $mode, "", $force);
      }
      if ($img != "error") {
        return ['url' => $img, 'has' => 1];
      }
      // Un téléchargement forcé qui échoue laisse en place le fichier déjà présent
      if (!file_exists($jpgPath) && !file_exists($webpPath)) {
        return ['url' => 'null', 'has' => 0];
      }
      log::add(__CLASS__, 'debug', "║ Nouveau téléchargement impossible, fichier local conservé pour $type ID: $id");
    }

    // --- Fichier déjà présent ---
    $ext = file_exists($webpPath) ? 'webp' : 'jpg';
    log::add(__CLASS__, 'debug', "║ Fichier $ext trouvé localement pour $type ID: $id");

    return ['url' => "/plugins/frigate/data/$camera/{$id}_{$type}.$ext", 'has' => 1];
  }

  /**
   * Retourne le clip d'un évènement terminé, téléchargé s'il n'est pas déjà présent.
   *
   * @param string               $dir   Dossier local de la caméra
   * @param array<string, mixed> $event Évènement Frigate
   * @param string               $type  Type d'évènement ; seul end donne lieu à un clip
   * @param bool                 $force Retélécharger le clip même s'il est présent
   * @return array{url: string, has: int} URL du clip (« null » s'il n'y en a pas) et disponibilité
   */
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

    log::add(__CLASS__, 'debug', "║ Clip déjà présent pour ID: " . $event['id']);
    return ['url' => "/plugins/frigate/data/" . $event['camera'] . "/" . $event['id'] . "_clip.mp4", 'has' => 1];
  }
  /**
   * Télécharge l'aperçu GIF d'un évènement s'il n'est pas déjà présent.
   *
   * @param string               $dir   Dossier local de la caméra
   * @param array<string, mixed> $event Évènement Frigate
   * @return array|string Résultat du téléchargement, ou URL de l'aperçu déjà présent
   */
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

  /**
   * Réencode une image téléchargée en JPEG, avec redimensionnement et conversion WebP éventuels.
   *
   * Le redimensionnement à la hauteur demandée, quand l'image est plus haute, ne s'applique pas aux miniatures.
   * Une qualité hors de 1 à 100 vaut 70.
   *
   * @param string   $filePath      Chemin de l'image (jpeg, png ou webp)
   * @param int|null $height        Hauteur maximale en pixels, null pour garder la taille
   * @param int      $quality       Qualité JPEG et WebP, de 1 à 100
   * @param bool     $convertToWebp Convertir en WebP et supprimer le JPEG
   * @param bool     $isThumbnail   L'image est une miniature
   * @return string|null Chemin du fichier final, null si l'image est illisible
   */
  private static function processJpgImage($filePath, $height = null, $quality = 70, $convertToWebp = false, $isThumbnail = false)
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
      // Libère l'image source avant l'enregistrement : sur un gros snapshot, la garder double la mémoire utilisée
      unset($source);
    }

    // --- Enregistrer en JPG ---
    $jpgPath = preg_replace('/\.(png|webp)$/i', '.jpg', $filePath);
    if ($quality < 1 || $quality > 100) {
      $quality = 70;
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

    return $finalPath;
  }


  /**
   * Retourne le label de l'évènement sans transformation.
   *
   * @param string $label Label reçu de Frigate
   * @return string
   */
  private static function cleanLabel($label)
  {
    return $label;
  }

  /**
   * Retourne la durée d'une vidéo, lue par ffmpeg.
   *
   * @param string $filePath Chemin de la vidéo
   * @return int|false Durée en secondes, false si elle est illisible
   */
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

  /**
   * Indique si l'URL d'un média du plugin désigne un fichier présent sur le disque.
   *
   * Un évènement garde en base l'URL de ses médias même quand le fichier a disparu,
   * par exemple après la restauration d'une sauvegarde sans le dossier data. Afficher
   * cette URL lancerait une requête vouée à l'échec.
   *
   * @param string|null $url URL web du média, de la forme /plugins/frigate/data/...
   * @return bool
   */
  public static function mediaFileExists($url)
  {
    $path = self::mediaFilePath($url);

    return $path !== null && is_file($path);
  }

  /**
   * Retourne le chemin local désigné par l'URL d'un média du plugin.
   *
   * Seules les URL du dossier data sont acceptées, sans remontée « .. » : la purge
   * supprime les fichiers désignés par les URL enregistrées en base.
   *
   * @param string|null $url URL web du média, de la forme /plugins/frigate/data/...
   * @return string|null Chemin local, null si l'URL ne désigne pas un fichier du dossier data
   */
  private static function mediaFilePath($url)
  {
    $dataWebPath = '/plugins/frigate/data/';
    if (!is_string($url) || strpos($url, $dataWebPath) !== 0 || strpos($url, '..') !== false) {
      return null;
    }

    return dirname(__FILE__, 3) . '/data/' . substr($url, strlen($dataWebPath));
  }

  /**
   * Retourne les URL de médias enregistrées sur les évènements, en clés d'un tableau.
   *
   * @return array<string, true>
   */
  private static function referencedMediaUrls()
  {
    $urls = [];
    foreach (frigate_events::all(false, true) ?: [] as $event) {
      foreach ([$event->getSnapshot(), $event->getThumbnail(), $event->getLasted()] as $url) {
        if (is_string($url) && $url !== '') {
          $urls[$url] = true;
        }
      }
    }

    return $urls;
  }

  /**
   * Nettoie le dossier data, et le recrée s'il manque.
   *
   * Supprime les fichiers des caméras dont l'évènement n'existe plus en base, et les captures manuelles
   * qu'aucun évènement ne référence depuis plus d'une heure. Appelée par chaque cron (voir execCron()).
   *
   * @return void
   */
  public static function cleanFolderData()
  {
    // Nettoyage du dossier des images caméra
    $folder = dirname(__FILE__, 3) . "/data";

    if (file_exists($folder)) {
      $snapshotsDir = $folder . DIRECTORY_SEPARATOR . 'snapshots' . DIRECTORY_SEPARATOR;
      $referencedUrls = null;
      // Parcourt récursivement tous les fichiers et dossiers
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile()) {
          $path = $file->getPathname();
          // Capture manuelle : son nom ne porte pas toujours l'event_id, elle se rattache à son
          // évènement par l'URL enregistrée. L'heure de marge laisse createSnapshot() enregistrer
          // l'évènement après avoir écrit le fichier.
          if (strpos($path, $snapshotsDir) === 0) {
            if ($referencedUrls === null) {
              $referencedUrls = self::referencedMediaUrls();
            }
            $url = '/plugins/frigate/data/snapshots/' . $file->getFilename();
            if (!isset($referencedUrls[$url]) && $file->getMTime() < time() - 3600) {
              log::add(__CLASS__, 'debug', "║ Capture " . $path . " rattachée à aucun évènement.");
              if (unlink($path)) {
                log::add(__CLASS__, 'debug', "║ Suppresion reussie: " . $path);
              } else {
                log::add(__CLASS__, "error", "║ Suppresion echouée: " . $path);
              }
            }
            continue;
          }
          // Vérifiez que le fichier est dans un sous-dossier de /data
          if (strpos($path, $folder . DIRECTORY_SEPARATOR) === 0 && $path !== $folder . DIRECTORY_SEPARATOR . basename($path)) {
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
      // Le dossier manque notamment après la restauration d'une sauvegarde faite avec l'option excludeBackup.
      // Il est recréé vide : les sous-dossiers des caméras le sont au téléchargement des médias.
      if (mkdir($folder, 0755, true)) {
        log::add(__CLASS__, 'info', "║ Dossier inexistant, recréé : " . $folder);
      } else {
        log::add(__CLASS__, "error", "║ Dossier inexistant, création impossible : " . $folder);
      }
    }
  }


  /**
   * Supprime les évènements non favoris plus anciens que remove_days jours, avec leurs fichiers.
   *
   * remove_days est porté à recovery_days s'il est plus petit, pour ne pas supprimer des évènements aussitôt
   * récupérés. Appelée par chaque cron (voir execCron()).
   *
   * @return void
   */
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

        self::cleanDbEvent($eventId, $event);
      }
    } else {
      log::add(__CLASS__, 'info', "║ Aucun événement trouvé datant de plus de " . $days . " jours.");
    }
  }

  /**
   * Fait le point avec Frigate sur les évènements restés incomplets en base : new, update ou sans type.
   *
   * Un évènement reste incomplet quand sa fin n'est jamais reçue, par exemple pendant une coupure MQTT. Seuls
   * les évènements non favoris commencés depuis plus de 3 heures sont examinés : un évènement plus récent peut
   * être en cours, et passé 3 heures aucune action n'est exécutée (voir executeActionNewEvent()). Sans réponse de
   * Frigate, rien n'est fait. Un évènement terminé dans Frigate est complété comme end, avec son clip et sans
   * le délai d'attente ; un évènement inconnu de Frigate est supprimé avec ses fichiers ; un évènement encore
   * en cours est conservé jusqu'au passage suivant. Un évènement plus ancien que recovery_days, que getEvents()
   * ne traiterait pas, est laissé à la purge par ancienneté.
   *
   * @return void
   */
  public static function reconcileEvents()
  {
    $events = frigate_events::incompleteBefore(time() - 10800);
    if (empty($events)) {
      return;
    }
    if (!self::isFrigateServerAvailable()) {
      log::add(__CLASS__, 'info', "║ Frigate ne répond pas : " . count($events) . " évènement(s) incomplet(s) conservé(s) jusqu'au prochain passage.");
      return;
    }
    // Même limite que getEvents()
    $recoveryDays = config::byKey('recovery_days', 'frigate');
    if (empty($recoveryDays)) {
      $recoveryDays = 7;
    }

    foreach ($events as $event) {
      $eventId = $event->getEventId();
      if ($event->getStartTime() !== null && $event->getStartTime() < time() - $recoveryDays * 86400) {
        continue;
      }
      $frigateEvent = self::fetchFrigateEvent($eventId);
      if ($frigateEvent === false) {
        log::add(__CLASS__, 'info', "║ Évènement " . $eventId . " incomplet et inconnu de Frigate : supprimé.");
        self::cleanDbEvent($eventId, $event);
      } elseif ($frigateEvent === null) {
        log::add(__CLASS__, 'info', "║ Évènement " . $eventId . " incomplet, réponse de Frigate illisible : conservé.");
      } elseif (!empty($frigateEvent['end_time'])) {
        log::add(__CLASS__, 'info', "║ Évènement " . $eventId . " incomplet, terminé dans Frigate : complété.");
        self::getEvent($eventId, 'end', false);
      } else {
        log::add(__CLASS__, 'info', "║ Évènement " . $eventId . " incomplet, encore en cours dans Frigate : conservé.");
      }
    }
  }

  /**
   * Interroge Frigate sur un évènement, sans tenir un évènement inconnu pour une erreur.
   *
   * @param string $eventId Identifiant Frigate
   * @return array<string, mixed>|false|null Évènement, false s'il est inconnu (404), null sans réponse lisible
   */
  private static function fetchFrigateEvent($eventId)
  {
    $urlFrigate = self::getUrlFrigate();
    if ($urlFrigate === false) {
      return null;
    }
    $ch = curl_init("http://" . $urlFrigate . "/api/events/" . rawurlencode($eventId));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 404) {
      return false;
    }
    $event = ($httpCode === 200 && is_string($result)) ? json_decode($result, true) : null;
    return is_array($event) ? $event : null;
  }

  /**
   * Supprime les cinq évènements non favoris les plus anciens, avec leurs fichiers.
   *
   * @return float Espace libéré, en Mo
   */
  public static function cleanOldestFile()
  {
    $totalSizeGain = 0;
    // On ne récupère que les 5 ou 10 plus vieux pour ne pas saturer la RAM
    $events = frigate_events::getOldestNotFavorite(5);

    if (!empty($events)) {
      foreach ($events as $event) {
        $totalSizeGain += self::cleanDbEvent($event->getEventId(), $event);
      }
    }
    return $totalSizeGain; // Renvoie par exemple 15.45 (Mo)
  }

  /**
   * Supprime les évènements les plus anciens tant que le dossier data dépasse la taille maximale
   * (datas_weight, 500 Mo par défaut).
   *
   * Les suppressions se font par lots de cinq, dans la limite de 100 lots ; la boucle s'arrête dès qu'un lot
   * ne libère rien.
   *
   * @return void
   */
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



  /**
   * Retourne la taille du dossier data, calculée par du ou, à défaut, en PHP.
   *
   * @return float Taille en Mo, 0 si le dossier est absent
   */
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

  /**
   * Extrait l'identifiant d'évènement Frigate placé en tête d'un nom de fichier.
   *
   * @param string $filename Nom du fichier, par exemple 1718992955.613576-zulr2q_snapshot.jpg
   * @return string|null Identifiant, null si le nom ne commence pas par un identifiant
   */
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

  /**
   * Supprime un évènement de la base avec ses fichiers, sauf s'il est en favori.
   *
   * Les fichiers sont retrouvés par leur nom dans le dossier de la caméra, et par les URL enregistrées sur
   * l'évènement pour les captures manuelles. Quand une autre ligne porte le même identifiant Frigate (doublon),
   * seule la ligne est supprimée : les fichiers lui sont communs.
   *
   * @param string              $id      Identifiant Frigate de l'évènement
   * @param frigate_events|null $frigate Ligne à supprimer, quand l'appelant l'a déjà ; sinon lue par identifiant
   * @return float|int Espace libéré en Mo, 0 si rien n'a été supprimé
   */
  public static function cleanDbEvent($id, $frigate = null)
  {
    if (!is_object($frigate)) {
      $frigate = frigate_events::byEventId($id);
    }

    // Sécurité : vérifier si l'objet existe
    if (!is_object($frigate)) {
      log::add(__CLASS__, 'debug', "║ Aucune entrée en base de données pour l'événement ID: " . $id . ". Aucun fichier ne sera supprimé.");
      return 0;
    }

    // Vérifier si le fichier est un favori
    $isFavorite = $frigate->getIsFavorite() ?? 0;
    if ($isFavorite == 1) {
      log::add(__CLASS__, 'debug', "║ Événement " . $frigate->getEventId() . " est un favori, il ne doit pas être supprimé de la base de données.");
      return 0;
    }

    if (count(frigate_events::allByEventId($frigate->getEventId())) > 1) {
      $frigate->remove();
      log::add(__CLASS__, 'debug', "║ Doublon de l'événement " . $frigate->getEventId() . " supprimé de la base de données, fichiers conservés pour l'autre ligne.");
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

    // Les captures manuelles sont rangées dans data/snapshots/, hors du dossier de la caméra :
    // leurs fichiers se retrouvent par les URL enregistrées sur l'évènement
    foreach (array_unique([$frigate->getSnapshot(), $frigate->getThumbnail(), $frigate->getLasted()]) as $url) {
      $path = self::mediaFilePath($url);
      if ($path !== null && is_file($path)) {
        $totalRemovedSize += filesize($path);
        unlink($path);
        log::add(__CLASS__, 'debug', "║ Fichier " . basename($path) . " supprimé pour l'événement " . $frigate->getEventId());
      }
    }

    $frigate->remove();
    log::add(__CLASS__, 'debug', "║ Événement " . $frigate->getEventId() . " supprimé de la base de données.");

    // On retourne la taille libérée en Mo pour mettre à jour le compteur global du while
    return $totalRemovedSize / 1024 / 1024;
  }

  /**
   * Supprime plusieurs évènements du plugin (voir deleteEvent()).
   *
   * @param string[] $ids Identifiants Frigate des évènements
   * @return bool Toujours true
   */
  public static function deleteEvents($ids)
  {
    foreach ($ids as $id) {
      self::deleteEvent($id);
    }
    return true;
  }
  /**
   * Supprime un évènement du plugin, et optionnellement du serveur Frigate.
   *
   * @param string $id  Identifiant Frigate de l'évènement
   * @param bool   $all Supprimer également côté serveur Frigate
   * @return string "OK", "Error 01" si favori, "Error 02" si introuvable
   */
  public static function deleteEvent($id, $all = false)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:SUPPRESSION EVENEMENT:/fg: ═══════════════════");

    $frigate = frigate_events::byEventId($id);
    if (!is_object($frigate)) {
      log::add(__CLASS__, 'debug', "║ Evènement " . $id . " introuvable en base de données.");
      log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════");
      return "Error 02";
    }

    if ((int) $frigate->getIsFavorite() === 1) {
      log::add(__CLASS__, 'debug', "║ Evènement " . $frigate->getEventId() . " est un favori, il ne doit pas être supprimé de la DB.");
      message::add('frigate', __("L'évènement est un favori, il ne peut pas être supprimé de la DB.", __FILE__));
      log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════");
      return "Error 01";
    }

    if ($all) {
      $urlFrigate = self::getUrlFrigate();
      if ($urlFrigate !== false) {
        self::deletecURL('http://' . $urlFrigate . '/api/events/' . $id);
      }
    }
    self::cleanDbEvent($id);

    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════");
    return "OK";
  }
  /**
   * Retourne les évènements enregistrés, prêts à afficher, du plus récent au plus ancien.
   *
   * @param bool $_onlyEnable Limiter aux évènements actifs
   * @param bool $_allType    Inclure les évènements sans type
   * @return array<int, array<string, mixed>>
   */
  public static function showEvents(bool $_onlyEnable = FALSE, bool $_allType = FALSE)
  {
    $result = [];

    $events = frigate_events::all($_onlyEnable, $_allType);

    foreach ($events as $event) {
      $date = date("d-m-Y H:i:s", $event->getStartTime());
      $duree = round($event->getEndTime() - $event->getStartTime(), 0);

      $result[] = array(
        "id"           => $event->getId(),
        "eventId"      => $event->getEventId(),
        "img"          => $event->getLasted(),
        "camera"       => $event->getCamera(),
        "label"        => $event->getLabel(),
        "subLabel"     => $event->getSubLabel(),
        "box"          => json_decode($event->getBox(), true),
        "date"         => $date,
        "duree"        => $duree,
        "startTime"    => $event->getStartTime(),
        "endTime"      => $event->getEndTime(),
        "falsePositive" => $event->getFalsePositive(),
        "snapshot"     => $event->getSnapshot(),
        "clip"         => $event->getClip(),
        "thumbnail"    => $event->getThumbnail(),
        "hasSnapshot"  => $event->getHasSnapshot(),
        "hasClip"      => $event->getHasClip(),
        "score"        => $event->getScore(),
        "top_score"     => $event->getTopScore(),
        "plusId"       => $event->getPlusId(),
        "retain"       => $event->getRetain(),
        "type"         => $event->getType(),
        "isFavorite"   => $event->getIsFavorite() ?? 0,
        "zones"        => $event->getZones() ?? '',
        "data"         => $event->getData(),
        "recognition_type"        => $event->getRecognition_type(),
        "description" => $event->getRecognition_description(),
        "recognition_name"        => $event->getRecognition_name(),
        "recognition_subname"     => $event->getRecognition_subname(),
        "recognition_attributes"  => $event->getRecognition_attributes(),
        "recognition_plate"       => $event->getRecognition_plate(),
        "recognition_score"       => $event->getRecognition_score()
      );
    }

    if (!empty($result)) {
      usort($result, 'frigate::orderByDate');
    }

    return $result;
  }

  /**
   * Compare deux évènements par date, pour un tri du plus récent au plus ancien.
   *
   * @param array<string, mixed> $a
   * @param array<string, mixed> $b
   * @return int
   */
  private static function orderByDate($a, $b)
  {
    $dateA = new DateTime($a['date']);
    $dateB = new DateTime($b['date']);
    return $dateB <=> $dateA;
  }

  /**
   * Crée ou met à jour les équipements Events, Statistiques et caméras à partir de la configuration de Frigate.
   *
   * @return int|string|false Nombre de caméras créées, « aucun » s'il n'y en a pas, false si la configuration est inaccessible
   */
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
  /**
   * Crée les équipements des caméras de la configuration Frigate et met à jour leurs commandes.
   *
   * Un équipement homonyme dans la pièce par défaut vaut au nouveau le suffixe « by frigate plugin ». Selon la
   * configuration : commandes MQTT, de détection d'objets et PTZ (ONVIF) si MQTT est configuré, commandes
   * audio, états de classification. La position sur le panel reprend l'ordre défini dans Frigate seulement si
   * elle n'est pas renseignée. Les statistiques et les évènements du dernier jour sont ensuite récupérés.
   *
   * @param array<string, mixed> $configurationArray Configuration de Frigate (/api/config)
   * @return int Nombre de caméras créées
   */
  public static function generateEqCameras($configurationArray)
  {

    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-success:CREATION DES CAMERAS:/fg: ═══════════════════");
    $urlFrigateWithoutPort = config::byKey('URL', 'frigate');
    $urlfrigate = self::getUrlFrigate();
    $mqttCmds = isset($configurationArray['mqtt']['host']) && !empty($configurationArray['mqtt']['host']);
    $classificationCmds = isset($configurationArray['classification']['custom']) && !empty($configurationArray['classification']['custom']);
    $create = 1;
    $name = "";
    //  $stats = self::getcURL("create eqCameras", $resultURL);
    $defaultRoom = intval(config::byKey('parentObject', 'frigate', '', true));
    $n = 0;

    foreach ($configurationArray['cameras'] as $cameraName => $cameraConfig) {
      $exist = 0;
      $addToName = "";
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
      if (!is_object($frigate)) {
        $n++;
        $urlLatest = "http://" . $urlfrigate . "/api/" . $cameraName . "/latest.jpg?timestamp=0&bbox=0&zones=0&mask=0&motion=0&regions=0";
        $img = urlencode($urlLatest);

        $frigate = new frigate();
        $frigate->setName($cameraName . $addToName);
        $frigate->setEqType_name("frigate");
        $frigate->setConfiguration("name", $cameraName);
        $frigate->setConfiguration('panel', 0);
        $frigate->setConfiguration('ptz', 0);
        $frigate->setConfiguration('presetMax', 0);
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
      // Position sur le panel : l'ordre défini dans Frigate, seulement si aucune position n'est renseignée
      if ((int)$frigate->getConfiguration('panelOrder', 0) <= 0) {
        $frigate->setConfiguration('panelOrder', (int)($cameraConfig['ui']['order'] ?? 0));
      }
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
    message::add('frigate', str_replace('#count#', $n, __("Frigate : #count# caméras créées, les commandes, évènements et statistiques sont mises à jour. Veuillez patienter...", __FILE__)));
    // commandes de statisque
    self::getStats();
    // commandes des events
    self::getEvents(false, array(), 'end', null, 1);
    message::add('frigate', __("Mise à jour des commandes, évènements et statistiques terminée.", __FILE__));

    log::add(__CLASS__, 'debug', "╚════════════════════════ END CREATION DES CAMERAS ═══════════════════");
    return $n;
  }

  /**
   * Demande le redémarrage de Frigate par MQTT (topic restart).
   *
   * @return void
   */
  public static function restartFrigate()
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-warning:RESTART FRIGATE:/fg: ═══════════════════");
    self::publish_message('restart', '');
    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════════════════");
  }

  /**
   * Active un profil de Frigate par MQTT (topic profile/set, Frigate 0.18 et plus) ; none désactive le profil
   * actif.
   *
   * @param string $profile Nom du profil dans la configuration de Frigate, ou none
   * @return void
   */
  public static function setProfile($profile)
  {
    $profile = trim((string)$profile);
    if ($profile === '') {
      log::add(__CLASS__, 'warning', "Changement de profil ignoré : aucun profil indiqué.");
      return;
    }
    self::publish_message('profile/set', $profile);
  }
  /**
   * Crée l'équipement Events s'il n'existe pas, puis ses commandes de cron et de détection.
   *
   * Les objets suivis, généraux et par caméra, sont enregistrés dans sa configuration « objects ».
   *
   * @param array<string, mixed> $configurationArray Configuration de Frigate (/api/config)
   * @return void
   */
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

  /**
   * Crée l'équipement Statistiques s'il n'existe pas, puis ses commandes.
   *
   * @return void
   */
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

  /**
   * Traite un message MQTT tracked_object_update : description IA, reconnaissance faciale, plaque ou classification.
   *
   * @param array<string, mixed> $trackedObjects Contenu du message
   * @return void
   */
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
    log::add(__CLASS__, 'debug', "║ Données reçues : " . json_encode($trackedObjects));

    // Création / Mise à jour de la base de données, sous le verrou de l'évènement partagé avec getEvents()
    $lock = self::lockEvent($id);
    try {
      $frigateEvent = frigate_events::byEventId($id);
      $frigateEvent = self::updateDatabase($frigateEvent, $type, $trackedObjects);
    } finally {
      self::unlockEvent($lock);
    }

    // Création / mise à jour des commandes Jeedom
    self::updateCommands($eqlogicId, $type, $frigateEvent);

    log::add(__CLASS__, 'debug', "╚════════════════════════ END UPDATE TRACKED OBJECTS ═══════════════════");
  }

  /**
   * Enregistre en base le résultat d'une reconnaissance sur un évènement, créé s'il n'existe pas encore.
   *
   * @param frigate_events|null  $frigateEvent   Évènement existant
   * @param string|null          $type           Type de reconnaissance : description, face, lpr ou classification
   * @param array<string, mixed> $trackedObjects Contenu du message
   * @return frigate_events|null Évènement enregistré, null pour un type inconnu
   */
  private static function updateDatabase($frigateEvent, $type, $trackedObjects)
  {
    $id = $trackedObjects['id'] ?? null;
    if (!is_object($frigateEvent)) {
      log::add(__CLASS__, "debug", "║ Événement introuvable (id: $id), il sera est créé dans la DB.");
      $frigateEvent = new frigate_events();
      $frigateEvent->setCamera($trackedObjects['camera']);
      $frigateEvent->setEventId($id);
      // L'identifiant Frigate commence par le timestamp de début : la purge par ancienneté s'en sert
      $start = explode('-', (string) $id)[0];
      if (is_numeric($start)) {
        $frigateEvent->setStartTime((int) ceil((float) $start));
      }
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
  /**
   * Met à jour les commandes « Reconnaissance » d'une caméra.
   *
   * @param int|string          $eqlogicId    Identifiant de l'équipement caméra
   * @param string|null         $type         Type de reconnaissance
   * @param frigate_events|null $frigateEvent Évènement enregistré
   * @return void
   */
  private static function updateCommands($eqlogicId, $type, $frigateEvent)
  {
    log::add(__CLASS__, 'debug', "║ MAJ Commandes pour le type : $type");

    $update = function ($label, $subtype, $unit, $logicalId, $genericType, $value) use ($eqlogicId) {
      $cmd = self::createCmd($eqlogicId, $label, $subtype, $unit, $logicalId, $genericType, 0, null, 0);
      $cmd->save();
      $cmd->event($value ?? '');
      $cmd->save();
    };

    $update("Reconnaissance - Type", "string", "", "info_detection_type", "", $type);

    $withNameScore = ['face', 'lpr', 'classification'];
    if (in_array($type, $withNameScore)) {
      $update("Reconnaissance - Nom",   "string",  "",  "info_detection_name",  "", $frigateEvent->getRecognition_name());
      $update("Reconnaissance - Score", "numeric", "%", "info_detection_score", "", $frigateEvent->getRecognition_score());
    }

    if ($type === 'description') {
      log::add(__CLASS__, 'debug', "║ Mise à jour de la description : " . $frigateEvent->getRecognition_description());
      $update("Reconnaissance - Description", "string", "", "info_description", "", $frigateEvent->getRecognition_description());
    }

    if ($type === 'lpr') {
      $update("Reconnaissance - Plaque d'immatriculation", "string", "", "info_plate", "", $frigateEvent->getRecognition_plate());
    }

    if ($type === 'classification') {
      $update("Reconnaissance - Label", "string", "", "info_detection_subname", "", $frigateEvent->getRecognition_subname());
      $update("Reconnaissance - Attributs", "string", "", "info_detection_attributes", "", $frigateEvent->getRecognition_attributes());
    }
  }

  /**
   * Retourne la commande d'un équipement, créée si elle n'existe pas.
   *
   * La recherche se fait par le nom, nettoyé comme Jeedom le fait en base et limité à 127 caractères : une
   * commande renommée par l'utilisateur est recréée. Une commande d'action liée à une commande info prend le
   * template toggle.
   *
   * @param int|string      $eqLogicId   Identifiant de l'équipement
   * @param string          $name        Nom de la commande
   * @param string          $subType     Sous-type Jeedom
   * @param string          $unite       Unité
   * @param string          $logicalId   logicalId
   * @param string          $genericType Type générique
   * @param int             $isVisible   Visibilité à la création
   * @param cmd|string|null $infoCmd     Commande info liée, pour une commande d'action
   * @param int             $historized  Historisation à la création
   * @param string          $type        info ou action
   * @return cmd
   */
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
  /**
   * Met à jour la commande « URL » d'un équipement, créée si besoin.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement
   * @param string     $url       Valeur à publier
   * @return void
   */
  public static function createAndRefreshURLcmd($eqlogicId, $url)
  {
    $cmd = self::createCmd($eqlogicId, "URL", "string", "", "info_url", "");
    $cmd->save();
    $cmd->event($url);
    $cmd->save();
  }

  /**
   * Crée les commandes audio d'une caméra (état, marche, arrêt, bascule) et met l'état à jour.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement caméra
   * @param mixed      $value     État audio selon la configuration de Frigate
   * @return void
   */
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
  /**
   * Crée les commandes communes à toutes les caméras.
   *
   * Création d'évènement, capture d'image, liens RTSP et snapshot live, activation de la caméra. Les liens et
   * l'état d'activation ne reçoivent une valeur que s'ils sont vides.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement caméra
   * @return void
   */
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

  /**
   * Crée la commande de détection d'un objet et la commande « Détection tout ».
   *
   * @param int|string $eqlogicId Identifiant de l'équipement
   * @param string     $object    Objet suivi par Frigate, par exemple person
   * @return void
   */
  public static function createObjectDetectorCmd($eqlogicId, $object)
  {
    $infoCmd = self::createCmd($eqlogicId, "Détection " . $object, "binary", "", "info_detect_" . $object, "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->save();
    $infoCmd = self::createCmd($eqlogicId, "Détection tout", "binary", "", "info_detect_all", "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->save();
  }

  /**
   * Crée les commandes des bascules MQTT d'une caméra (voir MQTT_TOGGLES) et la commande « détection en cours ».
   *
   * Chaque bascule a une commande info d'état et trois actions : off, on et toggle.
   *
   * @param int|string           $eqlogicId Identifiant de l'équipement caméra
   * @param array<string, mixed> $value     États connus, par clé de bascule
   * @return void
   */
  public static function createMQTTcmds($eqlogicId, $value)
  {
    foreach (self::MQTT_TOGGLES as $key => $prefix) {
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

  /**
   * Crée une commande d'action HTTP et la commande info « Etat HTTP command » qui reçoit ses réponses.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement
   * @param string     $name      Nom de la commande
   * @param string     $link      URL à appeler ; #user# et #password# y sont remplacés à l'exécution
   * @return bool Toujours true
   */
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
  /**
   * Crée les commandes de l'équipement Statistiques : redémarrage de Frigate, état du serveur, et
   * disponibilité quand MQTT est opérationnel.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement Statistiques
   * @return bool Toujours true
   */
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
  /**
   * Modifie l'URL d'une commande HTTP.
   *
   * @param int|string $cmdId Identifiant de la commande
   * @param string     $link  Nouvelle URL
   * @return bool Toujours true
   */
  public static function editHTTP($cmdId, $link)
  {
    $cmd = cmd::byid($cmdId);
    $cmd->setConfiguration("request", $link);
    $cmd->save();
    log::add("frigate", 'debug', '║ commande mise à jour');
    return true;
  }

  /**
   * Crée des commandes PTZ, de presets et audio fictives, pour tester l'affichage sans caméra PTZ.
   *
   * Les presets viennent d'une liste d'exemple ; les commandes sont à supprimer après le test.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement caméra
   * @return void
   */
  public static function createPTZdebug($eqlogicId)
  {
    log::add("frigate", 'debug', '║ création des commandes PTZ en mode DEBUG pour ' . $eqlogicId);
    self::createPTZcmds($eqlogicId);
    self::createPresetPTZcmds($eqlogicId, 1);
    self::createAudioCmds($eqlogicId);
    log::add("frigate", 'debug', '║ penser à supprimer les commandes après le debug... ');
  }
  /**
   * Crée les commandes de mouvement et de zoom PTZ d'une caméra.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement caméra
   * @return bool Toujours true
   */
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

  /**
   * Crée les commandes de presets PTZ d'une caméra, d'après la liste de Frigate.
   *
   * Le nombre de presets vient de la configuration de l'équipement (presetMax), à défaut de la configuration
   * générale, limité à 10.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement caméra
   * @param bool       $debug     Utiliser une liste de presets d'exemple
   * @return void
   */
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


  /**
   * Crée les commandes de cron de l'équipement Events : état (à 1 à la création), marche et arrêt.
   *
   * @return void
   */
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

  /**
   * Met à jour les commandes Jeedom à partir d'un évènement Frigate, et exécute les actions configurées.
   *
   * @param frigate_events $event      Évènement à publier
   * @param bool           $runActions false pour publier sans exécuter les actions
   * @return void
   */
  public static function majEventsCmds($event, $runActions = true)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════ :fg-warning:MAJ EVENTS:/fg: ═══════════════════");

    $eqlogicIds         = [];
    $cameraAction       = [];
    $cameraActionsExist = false;

    // Équipement « Events » général
    $frigate = frigate::byLogicalId('eqFrigateEvents', 'frigate');
    if (is_object($frigate)) {
      $eqlogicIds[] = $frigate->getId();
    }

    // Équipement caméra
    $cameraName = $event->getCamera();
    $eqCamera   = eqLogic::byLogicalId('eqFrigateCamera_' . $cameraName, 'frigate');

    if (!is_object($eqCamera)) {
      log::add(__CLASS__, 'warning', "║ Équipement caméra introuvable pour « " . $cameraName . " ». Évènement ignoré.");
      log::add(__CLASS__, 'debug', "╚════════════════════════ END MAJ EVENTS ═══════════════════");
      return;
    }

    $eqlogicIds[] = $eqCamera->getId();

    // Récupération de la configuration des actions de la caméra
    $cameraActions = $eqCamera->getConfiguration('actions');
    if (is_array($cameraActions) && isset($cameraActions[0])) {
      $cameraAction = $cameraActions[0];
    }
    $cameraActionsExist = !empty($cameraAction);

    self::eventAdd($event, $eqCamera->getId());

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
    $autorizeAction = is_object($frigate)
      ? (int) $frigate->getConfiguration('autorizeActions')
      : 0;
    $eventsId = is_object($frigate) ? $frigate->getId() : null;

    if ($autorizeAction === 1) {
      log::add(__CLASS__, 'debug', "║ ACTION: Les actions sont autorisées pour l'équipement Events (ID: " . $eventsId . ").");
    } else {
      log::add(__CLASS__, 'debug', "║ ACTION: Les actions sont désactivées pour l'équipement Events (ID: " . $eventsId . ").");
    }

    if (!$runActions) {
      log::add(__CLASS__, 'debug', "║ ACTION: Évènement déjà terminé, ses actions ont été exécutées à sa fin.");
    } elseif ($cameraActionsExist) {
      log::add(__CLASS__, 'debug', "║ ACTION: Exécution des actions pour la caméra (ID: " . $eqCamera->getId() . ").");
      self::executeActionNewEvent($eqCamera->getId(), $event);
    }
    if ($runActions && $eventsId !== null && ($autorizeAction === 1 || !$cameraActionsExist)) {
      log::add(__CLASS__, 'debug', "║ ACTION: Exécution des actions pour l'équipement Events (ID: " . $eventsId . ").");
      self::executeActionNewEvent($eventsId, $event);
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

  /**
   * Met à jour les commandes de statistiques à partir des stats de Frigate.
   *
   * Par caméra : une commande par valeur, et l'état d'activation déduit du pid. Pour l'équipement
   * Statistiques : détecteurs, GPU, CPU, stockage des enregistrements, version (aussi enregistrée dans la
   * configuration) et uptime.
   *
   * @param array<string, mixed> $stats Stats reçues de /api/stats ou par MQTT
   * @param bool                 $mqtt  Non utilisé
   * @return void
   */
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
    if (!is_object($frigate)) {
      log::add(__CLASS__, 'warning', "║ Équipement Statistiques introuvable, mise à jour des stats ignorée.");
      return;
    }
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

  /**
   * Exécute les actions configurées sur un équipement pour un évènement.
   *
   * La condition principale de l'équipement (conditionIf), quand elle est vraie, bloque les actions, sauf
   * celles marquées forcées. Une action s'exécute si elle est activée, si sa propre condition est vraie, si le
   * label, le type et les zones de l'évènement correspondent (entrée puis sortie quand une zone de sortie est
   * définie), si l'évènement a moins de 3 h, et si le clip ou le snapshot est disponible quand ses options
   * l'utilisent. Les options reçoivent les tags de l'évènement (#camera#, #label#, #snapshot#, #clip#, #jeemate#…).
   *
   * @param int|string     $eqLogicId Identifiant de l'équipement caméra ou Events
   * @param frigate_events $event     Évènement
   * @return void
   */
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
    $eqCamera = eqLogic::byLogicalId('eqFrigateCamera_' . $camera, 'frigate');
    $cameraId = is_object($eqCamera) ? $eqCamera->getId() : null;
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
    if (!is_object($eqLogic)) {
      log::add('frigate_Actions', 'warning', "║ ACTION: Équipement " . $eqLogicId . " introuvable, actions ignorées.");
      return;
    }

    // Vérification de la condition d'exécution
    $conditionIf = $eqLogic->getConfiguration('conditionIf');
    if (is_string($conditionIf) && $conditionIf !== '') {
      $conditionIf = str_replace(
        ['#camera#', '#score#', '#top_score#'],
        [$camera, $score, $topScore],
        $conditionIf
      );
      if (jeedom::evaluateExpression($conditionIf)) {
        $conditionIsActived = true;
      }
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

  /**
   * Télécharge un média de Frigate dans le dossier data et retourne son URL locale.
   *
   * Selon le mode : snapshot, clip ou aperçu d'un évènement ($type), miniature, dernière image de la caméra, ou
   * capture manuelle depuis une URL. Un fichier déjà présent n'est pas retéléchargé, sauf la dernière image ou
   * avec $force. Les images JPEG passent par processJpgImage(), selon les réglages de la caméra.
   *
   * @param string|null $eventId Identifiant de l'évènement
   * @param string|null $type    snapshot, clip ou preview, en mode par défaut
   * @param string|null $camera  Nom de la caméra dans Frigate
   * @param int         $mode    Une des constantes self::SAVE_MODE_*
   * @param string      $file    URL source, pour la dernière image et la capture manuelle
   * @param bool        $force   Retélécharger un fichier déjà présent
   * @return string URL locale du fichier, « error » en cas d'échec
   */
  public static function saveURL($eventId = null, $type = null, $camera = null, $mode = self::SAVE_MODE_DEFAULT, $file = "", $force = false)
  {
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
    if ($mode == self::SAVE_MODE_THUMBNAIL) {
      $lien = "http://{$urlFrigate}/api/events/{$eventId}/thumbnail.jpg";
      $path = "/data/{$camera}/{$eventId}_thumbnail.jpg";
    } elseif ($mode == self::SAVE_MODE_LATEST) {
      $lien = $file;
      $path = "/data/{$camera}/latest.jpg";
    } elseif ($mode == self::SAVE_MODE_SNAPSHOT) {
      $lien = urldecode($file);
      $path = "/data/snapshots/{$eventId}_snapshot.jpg";
    }

    $fullPath = dirname(__FILE__, 3) . $path;

    // --- Si déjà téléchargé (sauf latest et téléchargement forcé) ---
    if (!$force && file_exists($fullPath) && $mode != self::SAVE_MODE_LATEST) {
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
    $snapshotQuality = (int)$eqLogic->getConfiguration('snapshotQuality', 70);
    $snapshotHeight  = $eqLogic->getConfiguration('snapshotHeight');
    $snapshotHeight  = is_numeric($snapshotHeight) ? (int)$snapshotHeight : null;
    $snapshotWebp    = ($eqLogic->getConfiguration('snapshotWebp') ?? "0") == "1";

    // --- Traitement image (JPG uniquement) ---
    $isJpg = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'jpg';
    $isImageMode = in_array($mode, [self::SAVE_MODE_DEFAULT, self::SAVE_MODE_THUMBNAIL, self::SAVE_MODE_SNAPSHOT]);

    if ($isJpg && $isImageMode) {
      // Miniature : redimensionnement désactivé
      $isThumbnail = ($mode == self::SAVE_MODE_THUMBNAIL);

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


  /**
   * Retire les accents et met en minuscules, pour comparer labels et zones.
   *
   * @param string $string
   * @return string
   */
  private static function cleanString($string)
  {
    // Supprimer les accents
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    // Mettre en minuscule
    return mb_strtolower($string);
  }

  /**
   * Crée une capture instantanée d'une caméra et l'enregistre comme évènement.
   *
   * @param eqLogic $eqLogic Équipement caméra concerné
   * @return string|null Identifiant unique de la capture, null en cas d'échec
   */
  public static function createSnapshot($eqLogic)
  {
    log::add(__CLASS__, 'debug', "╔════════════════════════════════════════════════");
    log::add(__CLASS__, 'debug', "║ Créer snapshot");

    if (!is_object($eqLogic)) {
      log::add(__CLASS__, 'error', "║ createSnapshot : équipement invalide.");
      return null;
    }

    $camera    = $eqLogic->getConfiguration('name');
    $file      = $eqLogic->getConfiguration('img');
    $startTime = time();
    $uniqueId  = self::createUniqueId(sprintf('%.6f', microtime(true)));

    // Écrit data/snapshots/{uniqueId}_snapshot.jpg
    $url = self::saveURL($uniqueId, null, $camera, self::SAVE_MODE_SNAPSHOT, $file);
    if ($url === 'error') {
      log::add(__CLASS__, 'error', "║ createSnapshot : échec de la capture pour " . $camera);
      log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════");
      return null;
    }

    // Mise à jour des commandes existantes uniquement : les commandes d'évènement
    // ne sont créées qu'à la réception du premier évènement Frigate.
    log::add(__CLASS__, 'debug', "║ Mise à jour des commandes.");
    $cmdValues = [
      'info_url_capture' => $url,
      'info_label'       => 'capture',
      'info_score'       => 0,
      'info_topscore'    => 0,
      'info_duree'       => 0,
    ];
    foreach ($cmdValues as $logicalId => $value) {
      $cmd = $eqLogic->getCmd(null, $logicalId);
      if (is_object($cmd)) {
        $cmd->event($value);
      } else {
        log::add(__CLASS__, 'debug', "║ Commande " . $logicalId . " absente, mise à jour ignorée.");
      }
    }

    // L'event_id vaut $uniqueId, le préfixe du nom du fichier de la capture
    log::add(__CLASS__, 'debug', "║ Création d'un nouvel évènement Frigate pour l'event ID: " . $uniqueId);
    $frigate = new frigate_events();
    $frigate->setCamera($camera);
    $frigate->setLasted($url);
    $frigate->setHasClip(0);
    $frigate->setClip("");
    $frigate->setHasSnapshot(1);
    $frigate->setSnapshot($url);
    $frigate->setStartTime($startTime);
    $frigate->setEndTime($startTime);
    $frigate->setEventId($uniqueId);
    $frigate->setLabel('capture');
    $frigate->setThumbnail($url);
    $frigate->setTopScore(0);
    $frigate->setScore(0);
    $frigate->setType('end');
    $frigate->setIsFavorite(0);
    $frigate->save();

    log::add(__CLASS__, 'debug', "╚════════════════════════════════════════════════");

    return $uniqueId;
  }

  /**
   * Construit un identifiant unique de capture : le timestamp suivi de six caractères aléatoires.
   *
   * @param string $timestamp Timestamp avec microsecondes, par exemple 1727561234.123456
   * @return string Identifiant, par exemple 1727561234.123456-ab12cd
   */
  public static function createUniqueId($timestamp)
  {
    // Chaîne aléatoire de 6 caractères
    $randomStr = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
    // Assemble le timestamp et la chaîne aléatoire
    $uniqueId = $timestamp . '-' . $randomStr;

    return $uniqueId;
  }

  /**
   * Retourne la configuration de Frigate (/api/config).
   *
   * @return array|false Configuration décodée, false en cas d'échec
   */
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

  /**
   * Appelée par Jeedom avant l'enregistrement du topic MQTT : désabonne le plugin de l'ancien topic s'il change.
   *
   * @param string $value Nouveau topic
   * @return string Topic à enregistrer
   */
  public static function preConfig_topic($value)
  {
    if (self::getTopic() != $value) {
      self::removeMQTTTopicRegistration();
    }
    return $value;
  }

  /**
   * Appelée par Jeedom après l'enregistrement du topic MQTT : abonne le plugin au topic enregistré, quand MQTT
   * Manager est démarré.
   *
   * @param string $value Topic enregistré
   * @return void
   */
  public static function postConfig_topic($value)
  {
    if (class_exists('mqtt2')) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['launchable'] === 'ok') {
        self::deamon_start();
      }
    }
  }

  /**
   * Désabonne le plugin du topic MQTT de Frigate auprès de MQTT Manager.
   *
   * @return void
   */
  public static function removeMQTTTopicRegistration()
  {
    $topic = self::getTopic();
    if (class_exists('mqtt2')) {
      log::add(__CLASS__, 'info', "Arrêt de l'écoute du topic Frigate sur mqtt2:'{$topic}'");
      mqtt2::removePluginTopic($topic);
    }
  }

  /**
   * Démarre le démon : abonne le plugin au topic MQTT de Frigate auprès de MQTT Manager.
   *
   * @return bool Toujours true
   * @throws Exception Si le démon n'est pas lançable (MQTT Manager absent ou arrêté, topic vide)
   */
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

  /**
   * Arrête le démon : désabonne le plugin de son topic MQTT.
   *
   * @return void
   */
  public static function deamon_stop()
  {
    if (class_exists('mqtt2')) {
      log::add(__CLASS__, 'info', __('Arrêt du démon Frigate', __FILE__));
      mqtt2::removePluginTopic(self::getTopic());
    }
  }

  /**
   * Retourne l'état du démon, au format attendu par Jeedom.
   *
   * Le démon n'est lançable qu'avec MQTT Manager installé et démarré, et un topic configuré.
   *
   * @return array<string, string> Clés log, launchable, state, et launchable_message quand il n'est pas lançable
   */
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


  /**
   * Indique si le démon tourne, c'est-à-dire si MQTT Manager transmet le topic de Frigate au plugin.
   *
   * Un état faux après une mise à jour, qui désabonne le topic, permet au core de relancer le démon (cron
   * plugin::checkDeamon, toutes les 5 minutes, gestion automatique active). Sans getPluginForTopic() dans
   * MQTT Manager, l'abonnement ne peut pas être vérifié et le démon est réputé tourner.
   *
   * @return bool
   */
  public static function isRunning()
  {
    if (!class_exists('mqtt2')) {
      return false;
    }
    if (!method_exists('mqtt2', 'getPluginForTopic')) {
      return true;
    }
    $topic = self::getTopic();
    return $topic != '' && mqtt2::getPluginForTopic($topic) === __CLASS__;
  }

  /**
   * Traite les messages MQTT reçus sur le topic de Frigate.
   *
   * events (Frigate antérieur à 0.14 seulement), reviews, stats, available, profile et tracked_object_update ont
   * leur traitement ; toute autre clé qui désigne une caméra passe par processCameraData().
   *
   * @param array<string, mixed> $_message Messages reçus, indexés par topic
   * @return void
   */
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
            message::add("frigate", str_replace('#version#', $version, __("Version de Frigate détectée : #version#, certaines fonctionnalités du plugin peuvent ne pas fonctionner correctement. Veuillez mettre à jour votre serveur Frigate pour une expérience optimale.", __FILE__)));
            self::getEvents(true, [$value['after']], $value['type']);
            event::add('frigate::events', array('message' => 'mqtt_update', 'type' => 'event'));
          }
          break;

        case 'reviews':
          $eventId = $value['after']['data']['detections'][0];
          // genai : résumé de la revue par l'IA, que Frigate envoie après la fin de la revue
          $eventType = $value['type'] === 'genai' ? 'end' : $value['type'];

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

        case 'profile':
          if (isset($value['state'])) {
            self::updateProfileCmds($eqlogicId, $value['state']);
          }
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

  /**
   * Traite les données MQTT d'une caméra et met à jour les commandes associées.
   *
   * Un message d'objet qui ne porte pas de nombre d'objets, comme le snapshot, est ignoré (voir detectionCount()).
   *
   * @param eqLogic              $eqCamera Équipement caméra
   * @param string               $key      Nom de la caméra
   * @param array<string, mixed> $data     Données reçues
   * @return void
   */
  private static function processCameraData($eqCamera, $key, $data)
  {
    $eqEvent = eqLogic::byLogicalId('eqFrigateEvents', 'frigate');
    if (!is_object($eqEvent)) {
      log::add('frigate_Detect', 'warning', "║ Équipement Events introuvable, traitement MQTT caméra ignoré.");
      return;
    }

    $objects = $eqEvent->getConfiguration('objects');
    if (!is_array($objects)) {
      $objects = [];
    }

    // L'audio est géré à part par createAudioCmds(), d'où l'ajout explicite ici.
    $stateMap = self::MQTT_TOGGLES + ['audio' => 'AUDIO'];
    $skipKeys = ['birdseye', 'motion_contour_area', 'motion_threshold', 'ptz_autotracker', 'model_state'];

    foreach ($data as $innerKey => $innerValue) {

      // clés ignorées
      if (in_array($innerKey, $skipKeys)) {
        continue;
      }

      // objet détecté (person, car, etc.)
      if (in_array($innerKey, $objects)) {
        $count = self::detectionCount($innerValue);
        if ($count === null) {
          continue;
        }
        $value = $count > 0 ? 1 : 0;
        log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-success:START OBJET DETECT :/fg: ════════════════════════════════╗");
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
        log::add("frigate_Detect", 'info', "║ Objet : " . $innerKey . ', Etat : ' . $count);
        self::handleObject($eqCamera, $innerKey, $value);
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqEvent->getHumanName() . ":/b:");
        self::handleObject($eqEvent, $innerKey, self::anyCameraDetects('info_detect_' . $innerKey));
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
        if (!is_array($innerValue)) {
          continue;
        }
        foreach ($innerValue as $model => $state) {
          log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-info:START CLASSIFICATION:/fg: ═══════════════════════════════════╗");
          log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
          log::add("frigate_Detect", 'info', '║ Objet : ' . $innerKey . ', Etat : ' . json_encode([$model => $state]));
          $infoCmd = self::createCmd($eqCamera->getId(), "Reconnaissance - Etat " . $model, "string", "", "info_classification_state", "", 0);
          $infoCmd->event($state);
          $infoCmd->save();
          $eqCamera->refreshWidget();
          log::add("frigate_Detect", 'info', "╚═════════════════════════════ :fg-info:END CLASSIFICATION:/fg: ═══════════════════════════════════╝");
        }
        continue;
      }

      // tous les objets (all)
      if ($innerKey === 'all') {
        $count = self::detectionCount($innerValue);
        if ($count === null) {
          continue;
        }
        $value = $count > 0 ? 1 : 0;
        log::add("frigate_Detect", 'info', "╔═════════════════════════════ :fg-danger:START ALL DETECT:/fg: ═══════════════════════════════════╗");
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqCamera->getHumanName() . ":/b:");
        log::add("frigate_Detect", 'info', '║ Objet : ' . $innerKey . ', Etat : ' . $count);
        self::handleAllObject($eqCamera, $innerKey, $value);
        log::add("frigate_Detect", 'info', '║ Equipement : :b:' . $eqEvent->getHumanName() . ":/b:");
        self::handleAllObject($eqEvent, $innerKey, self::anyCameraDetects('info_detect_all'));
        log::add("frigate_Detect", 'info', "╚══════════════════════════════════════════════════════════════════════════════════╝");
        continue;
      }

      // statut des flux (Frigate 0.18 et plus)
      if ($innerKey === 'status') {
        if (is_array($innerValue)) {
          foreach ($innerValue as $role => $status) {
            self::updateStreamStatus($eqCamera, $role, $status);
          }
        }
        continue;
      }

      // états on/off génériques
      if (isset($stateMap[$innerKey], $innerValue['state'])) {
        self::updateCameraState($eqCamera, $innerKey, $innerValue['state'], "JEEMATE_CAMERA_{$stateMap[$innerKey]}_STATE");
      }
    }
  }

  /**
   * Met à jour les commandes de mouvement d'une caméra : « motion Etat » pour l'état de la bascule, « détection
   * en cours » pour le mouvement lui-même.
   *
   * @param eqLogic $eqCamera   Équipement caméra
   * @param string  $key        Nom de la caméra
   * @param mixed   $innerValue Valeur reçue : tableau avec state, ou ON / OFF
   * @return void
   */
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

  /**
   * Retourne le nombre d'objets porté par un message MQTT de détection.
   *
   * Frigate publie ce nombre sur <caméra>/<objet> et sur <caméra>/<objet>/active ; le second arrive sous la
   * forme d'un tableau à clé active. Le message <caméra>/<objet>/snapshot, un tableau à clé snapshot, ne porte
   * pas de nombre : Frigate le republie aussi quand l'objet n'est plus détecté.
   *
   * @param mixed $innerValue Valeur reçue pour l'objet
   * @return int|null Nombre d'objets, null si le message n'en porte pas
   */
  private static function detectionCount($innerValue)
  {
    if (is_array($innerValue)) {
      $innerValue = $innerValue['active'] ?? null;
    }
    return is_numeric($innerValue) ? (int) $innerValue : null;
  }

  /**
   * Indique si au moins une caméra active a une commande de détection à 1.
   *
   * L'équipement Events reprend ainsi l'état de toutes les caméras : le 0 d'une caméra ne le remet pas à 0 tant
   * qu'une autre détecte encore. Une caméra désactivée est ignorée, le core ne mettant plus ses commandes à jour
   * (cmd::event()).
   *
   * @param string $logicalId Commande de détection, par exemple info_detect_person ou info_detect_all
   * @return int 1 ou 0
   */
  private static function anyCameraDetects($logicalId)
  {
    foreach (self::byType(__CLASS__, true) as $eqLogic) {
      if (strpos($eqLogic->getLogicalId(), 'eqFrigateCamera_') !== 0) {
        continue;
      }
      $cmd = $eqLogic->getCmd('info', $logicalId);
      if (is_object($cmd) && $cmd->execCmd() == 1) {
        return 1;
      }
    }
    return 0;
  }

  /**
   * Remet à 0 les commandes de détection d'objets restées à 1, sur les caméras et l'équipement Events.
   *
   * Appelée à la mise à jour du plugin : une détection restée à 1 sans message de fin bloquerait l'équipement
   * Events, qui reste à 1 tant qu'une caméra détecte (voir anyCameraDetects()). Frigate publie le nombre
   * d'objets au changement suivant.
   *
   * @return void
   */
  public static function resetDetections()
  {
    foreach (self::byType(__CLASS__) as $eqLogic) {
      foreach ($eqLogic->getCmd('info') as $cmd) {
        if (strpos($cmd->getLogicalId(), 'info_detect_') === 0 && $cmd->execCmd() == 1) {
          $cmd->event(0);
        }
      }
    }
  }

  /**
   * Met à jour la commande de détection d'un objet sur un équipement.
   *
   * @param eqLogic $eqCamera Équipement caméra ou Events
   * @param string  $key      Objet, par exemple person
   * @param int     $value    1 si au moins un objet est détecté, 0 sinon
   * @return void
   */
  private static function handleObject($eqCamera, $key, $value)
  {
    $infoCmd = self::createCmd($eqCamera->getId(), "Détection " . $key, "binary", "", "info_detect_" . $key, "JEEMATE_CAMERA_DETECT_EVENT_STATE", 0);
    $infoCmd->event($value);
    $infoCmd->save();
    log::add("frigate_Detect", 'info', '║ Objet : ' . $key . ', Valeur enregistrée : ' . json_encode($value));
  }
  /**
   * Met à jour la commande « Détection tout » d'un équipement ; à 0, remet aussi à 0 les détections d'objets
   * encore actives.
   *
   * @param eqLogic $eqCamera Équipement caméra ou Events
   * @param string  $key      Clé reçue (all)
   * @param int     $value    1 si au moins un objet est détecté, 0 sinon
   * @return void
   */
  private static function handleAllObject($eqCamera, $key, $value)
  {
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
  /**
   * Met à jour la commande d'état d'une bascule MQTT d'une caméra, quand sa valeur change.
   *
   * @param eqLogic     $eqCamera     Équipement caméra
   * @param string      $type         Clé de la bascule, par exemple detect
   * @param string|null $state        ON ou OFF
   * @param string      $jeemateState Type générique de la commande d'état
   * @return void
   */
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
   * Met à jour la commande de statut d'un flux d'une caméra, quand sa valeur change.
   *
   * Frigate 0.18 et plus republie ce statut à intervalle régulier, même inchangé.
   *
   * @param eqLogic $eqCamera Équipement caméra
   * @param mixed   $role     Rôle du flux : detect, record ou audio
   * @param mixed   $status   online, offline ou disabled
   * @return void
   */
  private static function updateStreamStatus($eqCamera, $role, $status)
  {
    $names = ['detect' => 'détection', 'record' => 'enregistrement', 'audio' => 'audio'];
    if (!is_string($role) || !isset($names[$role]) || !is_string($status) || $status === '') {
      return;
    }

    $infoCmd = self::createCmd($eqCamera->getId(), "Statut flux " . $names[$role], "string", "", "info_stream_status_" . $role, "", 0);
    if ($infoCmd->execCmd() !== $status) {
      $infoCmd->event($status);
      log::add("frigate_MQTT", 'info', $eqCamera->getHumanName() . ' => statut du flux ' . $role . ' : ' . $status);
    }
  }

  /**
   * Met à jour le profil actif sur l'équipement Statistiques, et crée les commandes de profil si besoin.
   *
   * Seul Frigate 0.18 et plus publie le profil actif ; les commandes n'existent donc qu'avec ces versions.
   *
   * @param int|string $eqlogicId Identifiant de l'équipement Statistiques
   * @param mixed      $profile   Nom du profil actif, ou none
   * @return void
   */
  private static function updateProfileCmds($eqlogicId, $profile)
  {
    $infoCmd = self::createCmd($eqlogicId, "Profil actif", "string", "", "info_profile", "", 0);
    $infoCmd->event((string)$profile);

    $cmd = self::createCmd($eqlogicId, "Changer de profil", "message", "", "action_set_profile", "", 0, null, 0, "action");
    if ($cmd->getDisplay('title_disable') != 1) {
      $cmd->setValue($infoCmd->getId());
      $cmd->setDisplay('title_disable', 1);
      $cmd->setDisplay('message_placeholder', __("Profil, ou none pour aucun", __FILE__));
      $cmd->save();
    }
  }

  /**
   * Change le statut favori d'un évènement ; un favori n'est jamais purgé.
   *
   * @param string   $eventId Identifiant Frigate de l'évènement
   * @param int|bool $isFav   1 ou true pour le mettre en favori
   * @return int|null Nouveau statut, null si l'évènement est introuvable
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


  /**
   * Supprime les fichiers latest.jpg du dossier data.
   *
   * @return void
   */
  public static function deleteLatestFile()
  {
    $folder = dirname(__FILE__, 3) . "/data/";
    $fileName = "latest.jpg";
    // RecursiveDirectoryIterator lève une exception sur un dossier absent, ce qui interromprait frigate_update()
    if (!is_dir($folder)) {
      return;
    }
    // Parcourt récursivement tous les fichiers et dossiers
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
      if ($file->isFile() && $file->getFilename() === $fileName) {
        // Supprime le fichier
        unlink($file->getPathname());
        log::add(__CLASS__, 'debug', "║ Fichier supprimé: " . $file->getPathname());
      }
    }
  }

  /**
   * Appelée par Jeedom lors d'une sauvegarde : retourne les dossiers du plugin à exclure.
   *
   * Le dossier data, qui contient les snapshots et les clips, est exclu quand l'option excludeBackup est cochée.
   *
   * @return string[]|null
   */
  public static function backupExclude()
  {
    // retourne le répertoire de sauvegarde des snapshots et des vidéos des events à ne pas enregistrer dans le backup Jeedom
    if (config::byKey('excludeBackup', 'frigate', 0)) {
      return ['data'];
    }
  }


  /**
   * Retourne la configuration brute de Frigate (/api/config/raw), pour l'éditeur du plugin.
   *
   * @return array{status: string, message: mixed} success avec la configuration, ou error avec le message d'erreur
   */
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

  /**
   * Envoie une configuration YAML à Frigate, avec ou sans redémarrage.
   *
   * @param string $frigateConfiguration Configuration YAML
   * @param bool   $restart              Redémarrer Frigate après l'enregistrement
   * @return array{status: string, message: mixed} success avec la réponse de Frigate, ou error avec le message d'erreur
   */
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


  /**
   * Télécharge et décode un document JSON distant.
   *
   * @param string $jsonUrl URL absolue du document
   * @return array<mixed>|null Tableau décodé, null en cas d'échec
   */
  private static function jsonFromUrl($jsonUrl)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $jsonUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $jsonContent = curl_exec($ch);

    if (curl_errno($ch)) {
      log::add(__CLASS__, "error", "║ jsonFromUrl : " . curl_error($ch) . " (" . $jsonUrl . ")");
      curl_close($ch);
      return null;
    }

    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !is_string($jsonContent)) {
      log::add(__CLASS__, "error", "║ jsonFromUrl : erreur HTTP " . $httpCode . " lors du téléchargement de " . $jsonUrl);
      return null;
    }

    $jsonArray = json_decode($jsonContent, true);
    if (!is_array($jsonArray)) {
      log::add(__CLASS__, "error", "║ jsonFromUrl : impossible de décoder le contenu JSON de " . $jsonUrl);
      return null;
    }

    return $jsonArray;
  }

  /**
   * Met à jour la commande « status serveur » de l'équipement Statistiques selon la réponse du serveur Frigate.
   *
   * @return int|null 1 si le serveur répond en 200, 0 sinon, null sans équipement Statistiques
   */
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
  /**
   * Signale par un message une nouvelle version de Frigate, et la note dans la configuration (frigate_maj).
   *
   * @return void
   */
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
      message::add('frigate', str_replace('#version#', $latestVersion, __("Une nouvelle version de Frigate (#version#) est disponible.", __FILE__)));
    } else {
      config::save('frigate_maj', 0, 'frigate');
    }
  }
  /**
   * Retourne la version du plugin déclarée dans plugin_info/info.json.
   *
   * @return string Version du plugin, "0.0.0" si indéterminable
   */
  public static function getPluginVersion()
  {
    $pluginVersion = '0.0.0';
    $infoFile      = dirname(__FILE__) . '/../../plugin_info/info.json';

    try {
      if (!file_exists($infoFile)) {
        log::add('frigate', "warning", '[Plugin-Version] fichier info.json manquant');
        return $pluginVersion;
      }

      $content = file_get_contents($infoFile);
      if ($content === false) {
        log::add('frigate', "warning", '[Plugin-Version] fichier info.json illisible');
        return $pluginVersion;
      }

      $data = json_decode($content, true);
      if (!is_array($data) || !isset($data['pluginVersion'])) {
        log::add('frigate', "warning", '[Plugin-Version] Impossible de décoder le fichier info.json');
        return $pluginVersion;
      }

      $pluginVersion = (string) $data['pluginVersion'];
    } catch (\Exception $e) {
      log::add('frigate', 'debug', '[Plugin-Version] Get ERROR :: ' . $e->getMessage());
    }

    log::add('frigate', 'info', '[Plugin-Version] PluginVersion :: ' . $pluginVersion);

    return $pluginVersion;
  }


  /**
   * Retourne le temps écoulé depuis une date, en français (« il y a 3 heures »).
   *
   * @param string $datetime Date lisible par DateTime
   * @param bool   $full     Toutes les unités au lieu de la plus grande
   * @return string
   */
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

  /**
   * Retourne la classe CSS de couleur d'un score, par tranche de 10 %.
   *
   * @param int|float|string $score Score en %
   * @return string
   */
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

  /**
   * Formate une durée en secondes : « 1h 05mn », « 3mn 07s » ou « 42s ».
   *
   * @param int|float $seconds
   * @return string
   */
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

  /**
   * Lit les paramètres de création d'un évènement dans les options de la commande.
   *
   * Le titre donne le label ; le message peut porter video, duration, score et pre_capture, sous la forme
   * video=1|duration=20|score=30|pre_capture=5. Les valeurs absentes viennent de la configuration générale, sauf
   * pre_capture : sans lui, Frigate applique le pré-enregistrement de la caméra.
   *
   * @param array<string, mixed> $_options Options de la commande
   * @return array{label: string, video: int, duration: int, score: int, pre_capture: int|null}
   */
  private function parseEventParameters($_options)
  {
    // Valeurs par défaut
    // TODO : récupérer les valeurs par défaut pour chaque caméra (et plus au niveau de la config plugin)
    $defaults = [
      'label' => config::byKey('defaultLabel', 'frigate'),
      'video' => (int)config::byKey('defaultVideo', 'frigate'),
      'duration' => (int)config::byKey('defaultDuration', 'frigate'),
      'score' => (int)config::byKey('defaultScore', 'frigate'),
      'pre_capture' => null
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

          if ($key === 'pre_capture' && is_numeric($value) && $value >= 0) {
            $defaults['pre_capture'] = (int)$value;
          }
        }
      }
    }

    return $defaults;
  }


  /**
   * Exécute la commande d'action.
   *
   * Bascules MQTT (marche, arrêt, inversion), activation de la caméra par l'API, mouvements PTZ (arrêtés après
   * la pause pausePTZ), presets, création d'évènement et de capture, crons, redémarrage de Frigate, changement de
   * profil et actions HTTP.
   *
   * @param array<string, mixed> $_options Options de la commande
   * @return void
   */
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
      case 'action_set_profile':
        frigate::setProfile($_options['message'] ?? '');
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
        // config/set?cameras.{camera}.enabled=true
        $enable   = ($logicalId === 'action_enable_camera') ? 1 : 0;
        $response = frigate::enableCamera($camera, $enable);
        if (is_array($response) && !empty($response['success'])) {
          $enableCmd = $frigate->getCmd(null, 'enable_camera');
          if (is_object($enableCmd)) {
            $enableCmd->event($enable);
          }
        } else {
          log::add('frigate', 'error', "Échec de la modification de l'état de la caméra " . $camera);
        }
        break;
      case 'action_toggle_camera':
        $enableCmd = $frigate->getCmd(null, 'enable_camera');
        if (!is_object($enableCmd)) {
          log::add('frigate', 'error', "Commande enable_camera introuvable sur " . $camera);
          break;
        }
        $enable   = 1 - (int) $enableCmd->execCmd();
        $response = frigate::enableCamera($camera, $enable);
        if (is_array($response) && !empty($response['success'])) {
          $enableCmd->event($enable);
        } else {
          log::add('frigate', 'error', "Échec de la bascule de l'état de la caméra " . $camera);
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
        //score=12|video=1|duration=20|pre_capture=5
        $eventParams = self::parseEventParameters($_options);
        $result = frigate::createEvent($camera, $eventParams['label'], $eventParams['video'], $eventParams['duration'], $eventParams['score'], '', $eventParams['pre_capture']);
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
        $this->runHttpAction($frigate, $link, $user, $password);
        break;
      default:
        // Commandes HTTP au logicalId action_http_<nom>, créées par les premières versions du plugin
        if (strpos($logicalId, 'action_http_') === 0) {
          $this->runHttpAction($frigate, $link, $user, $password);
        }
    }
  }

  /**
   * Exécute une action HTTP et publie la réponse sur la commande info_http.
   *
   * Les variables #user# et #password# du lien sont remplacées par les identifiants
   * de l'équipement. Le mot de passe est masqué dans tout ce qui part au log.
   *
   * @param eqLogic $_frigate  Équipement porteur de la commande
   * @param string  $_link     URL à appeler, avec ses éventuelles variables
   * @param string  $_user     Identifiant d'authentification
   * @param string  $_password Mot de passe d'authentification
   * @return void
   */
  private function runHttpAction($_frigate, $_link, $_user, $_password)
  {
    $link = str_replace(['#user#', '#password#'], [(string) $_user, (string) $_password], (string) $_link);
    $safeLink = self::maskSecret($link, $_password);
    log::add('frigate', 'info', "║ action_http " . $safeLink);

    $response = $this->getCurlcmd($link, $_user, $_password);
    if ($response === false) {
      log::add('frigate', "error", "Erreur lors de l'appel HTTP: " . $safeLink);
      return;
    }

    $httpCmd = $_frigate->getCmd(null, 'info_http');
    if (is_object($httpCmd)) {
      $httpCmd->event($response);
    }
  }
  /**
   * Appelle une URL avec une authentification Digest et retourne la réponse.
   *
   * La réponse et le détail de l'échange sont écrits au log en debug, mot de passe masqué.
   *
   * @param string $link     URL à appeler
   * @param string $username Identifiant
   * @param string $password Mot de passe
   * @return string|false Réponse, false en cas d'erreur
   */
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
      log::add('frigate', "error", "Erreur cURL: " . self::maskSecret(curl_error($ch), $password));
    } else {
      log::add('frigate', 'debug', "║ Resultat de la commande HTTP : " . self::maskSecret($response, $password));
    }

    // Récupérer les informations verbose
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    log::add('frigate', 'debug', "║ cURL verbose log: " . self::maskSecret($verboseLog, $password));

    fclose($verbose);
    curl_close($ch);
    return $response;
  }

  /**
   * Remplace un secret par des astérisques dans un texte destiné au log.
   *
   * @param string|false|null $_text   Texte à écrire au log
   * @param string|null       $_secret Secret à masquer ; une valeur vide laisse le texte intact
   * @return string
   */
  private static function maskSecret($_text, $_secret)
  {
    $text = (string) $_text;
    $secret = (string) $_secret;

    return $secret === '' ? $text : str_replace($secret, '****', $text);
  }

  /**
   * Active ou désactive le cron de l'équipement Events (commande info_Cron).
   *
   * @param eqLogic $frigate Équipement Events
   * @param int     $status  1 pour activer, 0 pour désactiver
   * @param string  $message Message écrit au log
   * @return void
   */
  private function updateCronStatus($frigate, $status, $message)
  {
    $frigate->getCmd(null, 'info_Cron')->event($status);
    log::add(__CLASS__, 'debug', $message);
  }

  /**
   * Publie un message MQTT sur un sous-topic d'une caméra.
   *
   * @param string $camera  Nom de la caméra dans Frigate
   * @param string $topic   Sous-topic, par exemple detect/set
   * @param string $message Charge utile
   * @return void
   */
  private function publishCameraMessage($camera, $topic, $message)
  {
    frigate::publish_camera_message($camera, $topic, $message);
  }

  /**
   * Inverse une bascule MQTT d'une caméra, d'après la valeur de sa commande d'état.
   *
   * @param eqLogic $frigate Équipement caméra
   * @param string  $camera  Nom de la caméra dans Frigate
   * @param string  $infoCmd logicalId de la commande d'état
   * @param string  $setCmd  Sous-topic de commande, par exemple detect/set
   * @return void
   */
  private function toggleCameraSetting($frigate, $camera, $infoCmd, $setCmd)
  {
    $currentStatus = $frigate->getCmd(null, $infoCmd)->execCmd();
    $newStatus = $currentStatus == 1 ? 'OFF' : 'ON';
    frigate::publish_camera_message($camera, $setCmd, $newStatus);
  }

  /*     * **********************Getteur Setteur*************************** */
}
