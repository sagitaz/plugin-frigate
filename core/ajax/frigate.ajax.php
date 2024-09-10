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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
    ajax::init();

    if (init('action') == 'logs') {
        // Récupération des logs
      	$result = frigate::getLogs(init('service'));
        ajax::success($result);
    }

    if (init('action') == 'deleteEvent') {
        // Suppression d'un event
        $result = frigate::deleteEvent(init('eventId'), true);
        ajax::success($result);
    }

    if (init('action') == 'createEvent') {
        // Creation d'un event
        $result = frigate::createEvent(init('camera'), init('label'), init('video'), init('duration'),init('score'));
        ajax::success($result);
    }
    
    if (init('action') == 'searchAndCreate') {
        // Recherche et creation de cameras
        $result = frigate::generateEqCameras();
        ajax::success($result);
    }

    if (init('action') == 'restartFrigate') {
        // Redémarrage Frigate
        frigate::restartFrigate();
        ajax::success();
    }

    if (init('action') == 'refreshCameras') {
        // Rafraichit la visualisation
        $name = init('name');
        $img = init('img');
        $eqlogicId = init('eqlogicId');
        $who = init('who');
        $result = frigate::saveURL(null, null, $name, 2, $img);
        if ($who != "dashboard") {
            frigate::createAndRefreshURLcmd($eqlogicId, $result);
        }
        ajax::success($result);
    }

    if (init('action') == 'setFavorite') {
        // Changement de favori
        $result = frigate::setFavorite(init('eventId'), init('isFav'));
        ajax::success($result);
    }

    if (init('action') == 'getFrigateConfiguration') {
        log::add("frigate", 'info', "getFrigateConfiguration");
        $result = frigate::getFrigateConfiguration();
        ajax::success($result);
    }
    
    if (init('action') == 'sendFrigateConfiguration') {
        log::add("frigate", 'info', "sendFrigateConfiguration");
        $config = init('data');
        $result = frigate::sendFrigateConfiguration($config);
        ajax::success($result);
    }
    
    if (init('action') == 'sendFrigateConfigurationAndRestart') {
        log::add("frigate", 'info', "sendFrigateConfigurationAndRestart");
        $config = init('data');
        $result = frigate::sendFrigateConfiguration($config, true);
        ajax::success($result);
    }

    if (init('action') == 'stream') {
        // Récupère l'objet caméra à partir de son ID
        $camera = frigate::byId(init('id'));

        // Vérifie si la caméra existe
        if (!is_object($camera)) {
            throw new \Exception(__('Impossible de trouver la camera : ', __FILE__) . init('id'));
        }

        // Détermine le script à utiliser en fonction de la configuration de la caméra
        $rtspScript = dirname(__FILE__) . '/../../3rdparty/rtsp-to-hls.sh';

        // Vérifie si le processus RTSP-to-HLS n'est pas déjà en cours pour cette caméra
        if (count(system::ps('rtsp-to-hls.sh.*' . $camera->getConfiguration('localApiKey'))) == 0) {
            // Récupère les PID des processus FFmpeg en cours liés à cette caméra
            $pids = shell_exec('(ps ax || ps w) | grep ffmpeg.*' . $camera->getConfiguration('localApiKey') . ' | grep -v grep | awk \'{print $1}\'');

            // Si des PID sont trouvés, les tuer
            if (!empty($pids)) {
                $pids = explode("\n", trim($pids));
                foreach ($pids as $pid) {
                    if (is_numeric($pid)) {
                        shell_exec('sudo kill -9 ' . $pid);
                    }
                }
            }

            // Crée le répertoire pour les segments HLS si nécessaire
            if (!file_exists(dirname(__FILE__) . '/../../data/segments')) {
                mkdir(dirname(__FILE__) . '/../../data/segments', 0777, true);
            }

            $rtspFlux = "";

            // Exécute le script RTSP-to-HLS en arrière-plan
            exec('nohup ' . $rtspScript . ' ' . $rtspFlux . ' "' . $camera->getConfiguration('localApiKey') . '" > /dev/null 2>&1 &');

            // Attendre jusqu'à 30 secondes que le fichier M3U8 soit généré
            $i = 0;
            while (!file_exists(__DIR__ . '/../../data/' . $camera->getConfiguration('localApiKey') . '.m3u8')) {
                sleep(1);
                $i++;
                if ($i > 30) {
                    break;
                }
            }
        }

        // Met à jour le cache de la caméra avec le dernier appel de flux
        $camera->setCache('lastStreamCall', strtotime('now'));

        // Supprime les anciens segments HLS (fichiers .ts) de plus de 5 minutes
        shell_exec(system::getCmdSudo() . ' find ' . __DIR__ . '/../../data/segments/' .
            $camera->getConfiguration('localApiKey') . '-*.ts -mmin +5 -type f -exec rm -f {} \; 2>&1 > /dev/null');

        // Retourne une réponse de succès
        ajax::success();
    }


    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
