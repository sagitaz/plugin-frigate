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

use Log;
use frigate\DB\DBEvents;

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
  public static function cron5() {
    frigate::getEvents();
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

    log::add(__CLASS__, 'debug', $function . " = " . json_encode($response));
    return $response;
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
      $frigate = DBEvents::byEventId($event['id']);
    }
    if (!is_object($frigate)) {
      $frigate = new DBEvents();
    }
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
    $frigate->setTopScore($event['top_score']);
    $frigate->setZones($event['zones']);
    $frigate->save();
  }
  public static function getEvents2()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    $resultURL = $url . ":" . $port . "/api/events";

    $events = self::getcURL("Events", $resultURL);
    $events = array_reverse($events);
    $result = [];

    foreach ($events as $event) {
      $img = "http://" . $url . ":" . $port . "/api/events/" . $event["id"] . "/thumbnail.jpg";
      $date = date("d-m-Y H:i:s", $event['start_time']);
      $duree = round($event['end_time'] - $event['start_time'], 0);
      $result[] = array(
        "img" => $img,
        "camera" => $event["camera"],
        "label" => $event["label"],
        "date" => $date,
        "duree" => $duree,
      );
    }

    log::add(__CLASS__, 'debug', "Result = " . json_encode($result));
    return $result;
  }

  public static function getStats()
  {
    $url = config::byKey('URL', 'frigate');
    $port = config::byKey('port', 'frigate');

    $resultURL = $url . ":" . $port . "/api/stats";

    $result = self::getcURL("Stats", $resultURL);

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
  }

  /*     * **********************Getteur Setteur*************************** */
}
