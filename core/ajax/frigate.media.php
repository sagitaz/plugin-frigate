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

/*
 * Sert les médias des évènements rangés dans data/, dossier fermé à Apache par data/.htaccess.
 *
 * Paramètres :
 *   f        fichier, relatif à data/ : <caméra>/<fichier> ou snapshots/<fichier>
 *   t        signature du fichier (frigate::mediaSignature()), pour un accès sans session Jeedom
 *   v        date de modification du fichier : quand elle correspond, la réponse est mise en cache longtemps
 *   download 1 pour proposer le fichier au téléchargement
 *
 * Gère les requêtes partielles (Range), nécessaires à la lecture des clips dans Safari et l'app iOS, et les
 * requêtes conditionnelles (If-None-Match, If-Modified-Since).
 */

require_once __DIR__ . '/../../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');

/**
 * Termine la requête avec un code d'erreur, sans corps.
 *
 * @param int $code Code HTTP
 * @return never
 */
function frigateMediaFail($code)
{
  http_response_code($code);
  header('Cache-Control: no-store');
  exit;
}

// Types servis, par extension. Tout autre fichier de data/ (frigate_events.json…) est refusé.
$mimeTypes = [
  'jpg'  => 'image/jpeg',
  'jpeg' => 'image/jpeg',
  'png'  => 'image/png',
  'webp' => 'image/webp',
  'gif'  => 'image/gif',
  'mp4'  => 'video/mp4',
];

// Chemin demandé : un dossier (caméra ou snapshots) puis un fichier, sans fichier caché ni remontée « .. ».
$file = (string) ($_GET['f'] ?? '');
if (
  preg_match('#^[A-Za-z0-9_-]+/[A-Za-z0-9_-][A-Za-z0-9._-]*\.([A-Za-z0-9]+)$#', $file, $matches) !== 1
  || strpos($file, '..') !== false
  || !isset($mimeTypes[strtolower($matches[1])])
) {
  frigateMediaFail(403);
}
$mimeType = $mimeTypes[strtolower($matches[1])];

// Accès : session Jeedom, ou signature valide pour ce fichier (liens des notifications et de JeeMate)
if (!isConnect() && !hash_equals(frigate::mediaSignature($file), (string) ($_GET['t'] ?? ''))) {
  frigateMediaFail(403);
}
@session_write_close();

// Chemin réel : un lien symbolique ne doit pas mener hors de data/
$dataDir = realpath(__DIR__ . '/../../data');
$path = $dataDir === false ? false : realpath($dataDir . '/' . $file);
if ($path === false || strpos($path, $dataDir . DIRECTORY_SEPARATOR) !== 0) {
  frigateMediaFail(404);
}
if (!is_file($path) || !is_readable($path)) {
  frigateMediaFail(404);
}

clearstatcache(true, $path);
$size = filesize($path);
$mtime = filemtime($path);
if ($size === false || $mtime === false) {
  frigateMediaFail(404);
}
$etag = '"' . dechex($mtime) . '-' . dechex($size) . '"';
$lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

// session_start() a ajouté des en-têtes anti-cache : ils sont remplacés ici
header_remove('Pragma');
header_remove('Expires');
header('Content-Type: ' . $mimeType);
header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');
header('ETag: ' . $etag);
header('Last-Modified: ' . $lastModified);
// Une URL qui porte la date du fichier change avec lui : elle peut rester en cache
if (isset($_GET['v']) && (string) $_GET['v'] === (string) $mtime) {
  header('Cache-Control: private, max-age=31536000, immutable');
} else {
  header('Cache-Control: private, no-cache');
}
$disposition = (init('download') == 1) ? 'attachment' : 'inline';
header('Content-Disposition: ' . $disposition . '; filename="' . basename($path) . '"');

/**
 * Indique si une liste d'ETag reçue dans If-None-Match ou If-Range contient l'ETag du fichier.
 *
 * @param string $header Valeur de l'en-tête
 * @param string $etag   ETag du fichier
 * @return bool
 */
function frigateMediaEtagMatches($header, $etag)
{
  foreach (explode(',', $header) as $candidate) {
    $candidate = trim($candidate);
    if ($candidate === '*' || preg_replace('#^W/#', '', $candidate) === $etag) {
      return true;
    }
  }
  return false;
}

// Requête conditionnelle : If-None-Match l'emporte sur If-Modified-Since
$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
$ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? null;
if ($ifNoneMatch !== null) {
  if (frigateMediaEtagMatches($ifNoneMatch, $etag)) {
    http_response_code(304);
    exit;
  }
} elseif ($ifModifiedSince !== null) {
  $since = strtotime($ifModifiedSince);
  if ($since !== false && $mtime <= $since) {
    http_response_code(304);
    exit;
  }
}

// Requête partielle : une seule plage, ignorée si If-Range ne correspond plus au fichier
$start = 0;
$end = $size - 1;
$partial = false;
$range = $_SERVER['HTTP_RANGE'] ?? null;
if ($range !== null && $size > 0) {
  // If-Range porte un ETag (comparaison stricte) ou une date
  $ifRange = isset($_SERVER['HTTP_IF_RANGE']) ? trim($_SERVER['HTTP_IF_RANGE']) : null;
  if ($ifRange === null) {
    $rangeValid = true;
  } elseif ($ifRange !== '' && ($ifRange[0] === '"' || strpos($ifRange, 'W/') === 0)) {
    $rangeValid = $ifRange === $etag;
  } else {
    $rangeDate = strtotime($ifRange);
    $rangeValid = $rangeDate !== false && $mtime <= $rangeDate;
  }
  if ($rangeValid && preg_match('#^bytes=(\d*)-(\d*)$#', trim($range), $rangeMatches) === 1) {
    if ($rangeMatches[1] === '' && $rangeMatches[2] === '') {
      $rangeValid = false;
    } elseif ($rangeMatches[1] === '') {
      // Derniers octets : bytes=-N
      $suffix = (int) $rangeMatches[2];
      if ($suffix === 0) {
        header('Content-Range: bytes */' . $size);
        frigateMediaFail(416);
      }
      $start = max(0, $size - $suffix);
    } else {
      $start = (int) $rangeMatches[1];
      if ($rangeMatches[2] !== '') {
        $end = min((int) $rangeMatches[2], $size - 1);
      }
      if ($start >= $size || $start > $end) {
        header('Content-Range: bytes */' . $size);
        frigateMediaFail(416);
      }
    }
    $partial = $rangeValid;
  }
  // Plusieurs plages ou syntaxe inconnue : le fichier entier est servi
}

$length = $end - $start + 1;
if ($partial) {
  http_response_code(206);
  header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}
header('Content-Length: ' . $length);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD' || $length <= 0) {
  exit;
}

while (ob_get_level() > 0) {
  ob_end_clean();
}
@set_time_limit(0);
$handle = fopen($path, 'rb');
if ($handle === false) {
  exit;
}
if ($start > 0) {
  fseek($handle, $start);
}
$remaining = $length;
while ($remaining > 0 && !feof($handle) && !connection_aborted()) {
  $chunk = fread($handle, (int) min(65536, $remaining));
  if ($chunk === false || $chunk === '') {
    break;
  }
  echo $chunk;
  flush();
  $remaining -= strlen($chunk);
}
fclose($handle);
exit;
