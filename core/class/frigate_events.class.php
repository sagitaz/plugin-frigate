<?php

/**
 * Évènements Frigate en base de données
 *
 * @filesource   frigate_events.class.php
 * @created      06.06.2024
 * @package      sagitaz\plugin-frigate
 * @author       sagitaz
 * @copyright    2024 sagitaz
 * @license      GNU General Public License v3.0 and later; see license.txt
 */

/**
 * Représente un évènement Frigate persisté en base de données Jeedom.
 */
class frigate_events
{
	/*     * *************************Attributs****************************** */
	/**
	 * ATTENTION :
	 * Ces propriétés ne doivent PAS être typées nativement : PDO::FETCH_CLASS
	 * écrit directement dans les propriétés sans passer par les setters, ce qui
	 * provoquerait une TypeError sur les colonnes numériques rendues en string.
	 */

	/** @var int|string|null Identifiant technique Jeedom */
	private $id;
	/** @var string|null Identifiant de l'évènement côté Frigate */
	private $event_id;
	/** @var string|array|null Boîte englobante JSON */
	private $box;
	/** @var string|null Nom de la caméra Frigate */
	private $camera;
	/** @var string|array|null Charge utile brute de l'évènement */
	private $data;
	/** @var string|null URL de la dernière image connue */
	private $lasted;
	/** @var int|string|null Timestamp Unix de début */
	private $startTime;
	/** @var int|string|null Timestamp Unix de fin */
	private $endTime;
	/** @var int|string|null Faux positif déclaré par Frigate */
	private $false_positive;
	/** @var int|null 1 si un clip est disponible */
	private $hasClip;
	/** @var string|null URL locale du clip */
	private $clip;
	/** @var int|null 1 si un snapshot est disponible */
	private $hasSnapshot;
	/** @var string|null URL locale du snapshot */
	private $snapshot;
	/** @var string|null Label de l'objet détecté */
	private $label;
	/** @var string|null Identifiant Frigate+ */
	private $plusId;
	/** @var int|string|null Conservation illimitée */
	private $retain;
	/** @var string|null Sous-label Frigate */
	private $subLabel;
	/** @var string|null URL locale de la miniature */
	private $thumbnail;
	/** @var int|float|string|null Meilleur score en % */
	private $topScore;
	/** @var int|float|string|null Score courant en % */
	private $score;
	/** @var string|null Zones traversées, séparées par des virgules */
	private $zones;
	/** @var string|null Type d'évènement (new, update, end) */
	private $type;
	/** @var int|null 1 si l'évènement est un favori ; 0 pour un nouvel évènement, que les purges peuvent alors sélectionner */
	private $isFavorite = 0;
	/** @var string|null Type de reconnaissance */
	private $recognition_type;
	/** @var string|null Description générée par IA */
	private $recognition_description;
	/** @var string|null Nom reconnu */
	private $recognition_name;
	/** @var string|null Sous-nom reconnu */
	private $recognition_subname;
	/** @var string|array|null Attributs de classification */
	private $recognition_attributes;
	/** @var string|null Plaque d'immatriculation reconnue */
	private $recognition_plate;
	/** @var float|null Score de reconnaissance */
	private $recognition_score;

	/*     * ***********************Methode static*************************** */

	/**
	 * Exécute une requête SELECT sur la table des évènements et hydrate la classe.
	 *
	 * Mutualise toutes les requêtes de lecture afin d'éviter la duplication du
	 * SELECT et du mapping PDO dans chaque accesseur statique.
	 *
	 * @param string               $_where     Clause WHERE/ORDER/LIMIT, sans le mot clé SELECT
	 * @param array<string, mixed> $_values    Valeurs des paramètres nommés
	 * @param string               $_fetchType DB::FETCH_TYPE_ALL ou DB::FETCH_TYPE_ROW
	 * @return frigate_events|frigate_events[]|null
	 * @throws Exception
	 */
	private static function query(string $_where = '', array $_values = array(), string $_fetchType = DB::FETCH_TYPE_ALL)
	{
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . ' FROM frigate_events';
		if ($_where !== '') {
			$sql .= ' ' . $_where;
		}

		return DB::Prepare($sql, $_values, $_fetchType, PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * Retourne l'ensemble des évènements enregistrés.
	 *
	 * @param bool $_onlyEnable Limiter aux évènements actifs
	 * @param bool $_allType    Inclure les évènements sans type
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function all(bool $_onlyEnable = false, bool $_allType = false)
	{
		$where = array();

		if (!$_allType) {
			$where[] = 'type IS NOT NULL';
		}
		if ($_onlyEnable) {
			$where[] = 'enabled = 1';
		}

		$clause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

		return self::query($clause);
	}

	/**
	 * Retourne un évènement à partir de son identifiant technique Jeedom.
	 *
	 * @param int|string $_id Identifiant technique
	 * @return frigate_events|null
	 * @throws Exception
	 */
	public static function byId($_id)
	{
		return self::query('WHERE id = :id', array('id' => $_id), DB::FETCH_TYPE_ROW);
	}

	/**
	 * Retourne un évènement à partir de son identifiant Frigate.
	 *
	 * @param string $_event_id Identifiant Frigate
	 * @return frigate_events|null
	 * @throws Exception
	 */
	public static function byEventId($_event_id)
	{
		return self::query('WHERE event_id = :event_id', array('event_id' => $_event_id), DB::FETCH_TYPE_ROW);
	}

	/**
	 * Retourne les évènements non favoris restés incomplets (new, update ou sans type) commencés avant une date.
	 *
	 * Un évènement sans date de début est retourné aussi.
	 *
	 * @param int $_before Timestamp limite
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function incompleteBefore($_before)
	{
		return self::query(
			"WHERE (type IS NULL OR type IN ('new', 'update')) AND (isFavorite IS NULL OR isFavorite != 1) AND (startTime IS NULL OR startTime < :before)",
			array('before' => $_before)
		);
	}

	/**
	 * Retourne toutes les lignes enregistrées sous un identifiant Frigate, de la plus ancienne à la plus récente.
	 *
	 * @param string $_event_id Identifiant Frigate
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function allByEventId($_event_id)
	{
		return self::query('WHERE event_id = :event_id ORDER BY id', array('event_id' => $_event_id));
	}

	/**
	 * Fusionne les évènements enregistrés plusieurs fois sous le même identifiant Frigate.
	 *
	 * Pour chaque identifiant en double, la ligne au type le plus avancé est gardée (end, puis update, puis new,
	 * puis sans type ; la plus ancienne à égalité). Elle reprend le favori, et les champs de reconnaissance
	 * qu'elle n'a pas, depuis les autres lignes, qui sont supprimées de la base. Les fichiers, communs à toutes
	 * les lignes, ne sont pas touchés.
	 *
	 * @return int Nombre de lignes supprimées
	 * @throws Exception
	 */
	public static function mergeDuplicates()
	{
		$order = array('end' => 3, 'update' => 2, 'new' => 1);
		$fields = array('Recognition_type', 'Recognition_name', 'Recognition_subname', 'Recognition_description', 'Recognition_plate', 'Recognition_attributes', 'Recognition_score');
		$removed = 0;

		$duplicates = DB::Prepare('SELECT event_id FROM frigate_events GROUP BY event_id HAVING COUNT(*) > 1', array(), DB::FETCH_TYPE_ALL);
		foreach ($duplicates as $duplicate) {
			$rows = self::allByEventId($duplicate['event_id']);
			$rank = function ($row) use ($order) {
				return $order[$row->getType()] ?? 0;
			};
			// Lignes triées par id : à rang égal, la plus ancienne est gardée
			$keeper = $rows[0];
			foreach ($rows as $row) {
				if ($rank($row) > $rank($keeper)) {
					$keeper = $row;
				}
			}

			foreach ($rows as $row) {
				if ($row === $keeper) {
					continue;
				}
				if ($row->getIsFavorite() == 1) {
					$keeper->setIsFavorite(1);
				}
				foreach ($fields as $field) {
					$value = $row->{'get' . $field}();
					if (($keeper->{'get' . $field}() === null || $keeper->{'get' . $field}() === '') && $value !== null && $value !== '') {
						$keeper->{'set' . $field}($value);
					}
				}
				$row->remove();
				$removed++;
			}
			$keeper->save();
		}

		return $removed;
	}

	/**
	 * Retourne tous les évènements d'un type donné.
	 *
	 * @param string $_type Type recherché (new, update, end)
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function byType($_type)
	{
		return self::query('WHERE type = :type', array('type' => $_type));
	}

	/**
	 * Retourne les N évènements non favoris les plus anciens.
	 *
	 * @param int $_limit Nombre maximum d'évènements
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function getOldestByLimit($_limit = 10)
	{
		$limit = max(1, (int) $_limit);

		return self::query(
			'WHERE isFavorite != 1 ORDER BY startTime ASC LIMIT ' . $limit
		);
	}

	/**
	 * Retourne les évènements non favoris antérieurs à un nombre de jours donné.
	 *
	 * @param int|string $_days Ancienneté en jours
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function getOlderThanDays($_days)
	{
		$seconds = max(0, (int) $_days) * 86400;

		return self::query(
			'WHERE isFavorite != 1 AND startTime < (UNIX_TIMESTAMP(NOW()) - :seconds)',
			array('seconds' => $seconds)
		);
	}

	/**
	 * Alias de getOldestByLimit().
	 *
	 * @deprecated Utiliser getOldestByLimit()
	 * @param int $_limit Nombre maximum d'évènements
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function getOldestNotFavorite($_limit = 10)
	{
		return self::getOldestByLimit($_limit);
	}

	/**
	 * Alias de getOlderThanDays().
	 *
	 * @deprecated Utiliser getOlderThanDays()
	 * @param int|string $_days Ancienneté en jours
	 * @return frigate_events[]
	 * @throws Exception
	 */
	public static function getOldestNotFavorites($_days)
	{
		return self::getOlderThanDays($_days);
	}

	/**
	 * Normalise une valeur quelconque en drapeau 0/1 stockable en base.
	 *
	 * @param mixed $_value Valeur à normaliser
	 * @return int
	 */
	private static function toFlag($_value): int
	{
		if (is_bool($_value)) {
			return $_value ? 1 : 0;
		}
		if (is_string($_value)) {
			$_value = trim(strtolower($_value));
			return in_array($_value, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
		}

		return ((int) $_value === 1) ? 1 : 0;
	}

	/*     * *********************Methode d'instance************************* */

	/**
	 * Hook appelé par Jeedom avant l'enregistrement en base.
	 *
	 * @return void
	 */
	public function preSave() {}

	/**
	 * Enregistre l'évènement en base de données.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function save()
	{
		return DB::save($this);
	}

	/**
	 * Supprime l'évènement de la base de données.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		DB::remove($this);
	}

	/**
	 * Retourne le nom de la table SQL, requis par la couche DB de Jeedom.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'frigate_events';
	}

	/*     * **********************Getteur Setteur*************************** */

	/**
	 * Retourne l'identifiant technique Jeedom.
	 *
	 * @return int|string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Définit l'identifiant technique Jeedom.
	 *
	 * @param int|string|null $id Identifiant
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Retourne l'identifiant Frigate de l'évènement.
	 *
	 * @return string|null
	 */
	public function getEventId()
	{
		return $this->event_id;
	}

	/**
	 * Définit l'identifiant Frigate de l'évènement.
	 *
	 * @param string|null $event_id Identifiant Frigate
	 * @return $this
	 */
	public function setEventId($event_id)
	{
		$this->event_id = $event_id;
		return $this;
	}

	/**
	 * Retourne la boîte englobante de l'objet détecté.
	 *
	 * @return string|array|null
	 */
	public function getBox()
	{
		return $this->box;
	}

	/**
	 * Définit la boîte englobante de l'objet détecté.
	 *
	 * @param string|array|null $box Boîte englobante
	 * @return $this
	 */
	public function setBox($box)
	{
		$this->box = $box;
		return $this;
	}

	/**
	 * Retourne le nom de la caméra Frigate.
	 *
	 * @return string|null
	 */
	public function getCamera()
	{
		return $this->camera;
	}

	/**
	 * Définit le nom de la caméra Frigate.
	 *
	 * @param string|null $camera Nom de la caméra
	 * @return $this
	 */
	public function setCamera($camera)
	{
		$this->camera = $camera;
		return $this;
	}

	/**
	 * Retourne la charge utile brute de l'évènement.
	 *
	 * @return string|array|null
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Définit la charge utile brute de l'évènement.
	 *
	 * @param string|array|null $data Charge utile
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Retourne l'URL de la dernière image connue.
	 *
	 * @return string|null
	 */
	public function getLasted()
	{
		return $this->lasted;
	}

	/**
	 * Définit l'URL de la dernière image connue.
	 *
	 * @param string|null $lasted URL de l'image
	 * @return $this
	 */
	public function setLasted($lasted)
	{
		$this->lasted = $lasted;
		return $this;
	}

	/**
	 * Retourne le timestamp Unix de début d'évènement.
	 *
	 * @return int|string|null
	 */
	public function getStartTime()
	{
		return $this->startTime;
	}

	/**
	 * Définit le timestamp Unix de début d'évènement.
	 *
	 * @param int|string|null $startTime Timestamp de début
	 * @return $this
	 */
	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		return $this;
	}

	/**
	 * Retourne le timestamp Unix de fin d'évènement.
	 *
	 * @return int|string|null
	 */
	public function getEndTime()
	{
		return $this->endTime;
	}

	/**
	 * Définit le timestamp Unix de fin d'évènement.
	 *
	 * @param int|string|null $endTime Timestamp de fin
	 * @return $this
	 */
	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		return $this;
	}

	/**
	 * Indique si Frigate a marqué l'évènement comme faux positif.
	 *
	 * @return int|string|null
	 */
	public function getFalsePositive()
	{
		return $this->false_positive;
	}

	/**
	 * Définit le drapeau de faux positif.
	 *
	 * @param int|string|bool|null $false_positive Faux positif
	 * @return $this
	 */
	public function setFalsePositive($false_positive)
	{
		$this->false_positive = $false_positive;
		return $this;
	}

	/**
	 * Indique si un clip vidéo est disponible pour l'évènement.
	 *
	 * @return int|null
	 */
	public function getHasClip()
	{
		return $this->hasClip;
	}

	/**
	 * Définit la disponibilité du clip vidéo (normalisée en 0 ou 1).
	 *
	 * @param mixed $hasClip Disponibilité du clip
	 * @return $this
	 */
	public function setHasClip($hasClip)
	{
		$this->hasClip = self::toFlag($hasClip);
		return $this;
	}

	/**
	 * Retourne l'URL locale du clip vidéo.
	 *
	 * @return string|null
	 */
	public function getClip()
	{
		return $this->clip;
	}

	/**
	 * Définit l'URL locale du clip vidéo.
	 *
	 * @param string|null $clip URL du clip
	 * @return $this
	 */
	public function setClip($clip)
	{
		$this->clip = $clip;
		return $this;
	}

	/**
	 * Indique si un snapshot est disponible pour l'évènement.
	 *
	 * @return int|null
	 */
	public function getHasSnapshot()
	{
		return $this->hasSnapshot;
	}

	/**
	 * Définit la disponibilité du snapshot (normalisée en 0 ou 1).
	 *
	 * @param mixed $hasSnapshot Disponibilité du snapshot
	 * @return $this
	 */
	public function setHasSnapshot($hasSnapshot)
	{
		$this->hasSnapshot = self::toFlag($hasSnapshot);
		return $this;
	}

	/**
	 * Retourne l'URL locale du snapshot.
	 *
	 * @return string|null
	 */
	public function getSnapshot()
	{
		return $this->snapshot;
	}

	/**
	 * Définit l'URL locale du snapshot.
	 *
	 * @param string|null $snapshot URL du snapshot
	 * @return $this
	 */
	public function setSnapshot($snapshot)
	{
		$this->snapshot = $snapshot;
		return $this;
	}

	/**
	 * Retourne le label de l'objet détecté.
	 *
	 * @return string|null
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Définit le label de l'objet détecté.
	 *
	 * @param string|null $label Label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	/**
	 * Retourne l'identifiant Frigate+ de l'évènement.
	 *
	 * @return string|null
	 */
	public function getPlusId()
	{
		return $this->plusId;
	}

	/**
	 * Définit l'identifiant Frigate+ de l'évènement.
	 *
	 * @param string|null $plusId Identifiant Frigate+
	 * @return $this
	 */
	public function setPlusId($plusId)
	{
		$this->plusId = $plusId;
		return $this;
	}

	/**
	 * Indique si l'évènement est conservé indéfiniment côté Frigate.
	 *
	 * @return int|string|null
	 */
	public function getRetain()
	{
		return $this->retain;
	}

	/**
	 * Définit le drapeau de conservation illimitée.
	 *
	 * @param int|string|bool|null $retain Conservation
	 * @return $this
	 */
	public function setRetain($retain)
	{
		$this->retain = $retain;
		return $this;
	}

	/**
	 * Retourne le sous-label Frigate de l'évènement.
	 *
	 * @return string|null
	 */
	public function getSubLabel()
	{
		return $this->subLabel;
	}

	/**
	 * Définit le sous-label Frigate de l'évènement.
	 *
	 * @param string|null $subLabel Sous-label
	 * @return $this
	 */
	public function setSubLabel($subLabel)
	{
		$this->subLabel = $subLabel;
		return $this;
	}

	/**
	 * Retourne l'URL locale de la miniature.
	 *
	 * @return string|null
	 */
	public function getThumbnail()
	{
		return $this->thumbnail;
	}

	/**
	 * Définit l'URL locale de la miniature.
	 *
	 * @param string|null $thumbnail URL de la miniature
	 * @return $this
	 */
	public function setThumbnail($thumbnail)
	{
		$this->thumbnail = $thumbnail;
		return $this;
	}

	/**
	 * Retourne le meilleur score de détection, en pourcentage.
	 *
	 * @return int|float|string|null
	 */
	public function getTopScore()
	{
		return $this->topScore;
	}

	/**
	 * Définit le meilleur score de détection, en pourcentage.
	 *
	 * @param int|float|string|null $topScore Meilleur score
	 * @return $this
	 */
	public function setTopScore($topScore)
	{
		$this->topScore = $topScore;
		return $this;
	}

	/**
	 * Retourne le score de détection courant, en pourcentage.
	 *
	 * @return int|float|string|null
	 */
	public function getScore()
	{
		return $this->score;
	}

	/**
	 * Définit le score de détection courant, en pourcentage.
	 *
	 * @param int|float|string|null $score Score
	 * @return $this
	 */
	public function setScore($score)
	{
		$this->score = $score;
		return $this;
	}

	/**
	 * Retourne la liste des zones traversées, séparées par des virgules.
	 *
	 * @return string|null
	 */
	public function getZones()
	{
		return $this->zones;
	}

	/**
	 * Définit la liste des zones traversées.
	 *
	 * @param string|null $zones Zones
	 * @return $this
	 */
	public function setZones($zones)
	{
		$this->zones = $zones;
		return $this;
	}

	/**
	 * Retourne le type d'évènement (new, update, end).
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Définit le type d'évènement.
	 *
	 * @param string|null $type Type
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Indique si l'évènement a été marqué comme favori par l'utilisateur.
	 *
	 * @return int|null
	 */
	public function getIsFavorite()
	{
		return $this->isFavorite;
	}

	/**
	 * Définit le statut favori (normalisé en 0 ou 1).
	 *
	 * @param mixed $isFavorite Statut favori
	 * @return $this
	 */
	public function setIsFavorite($isFavorite)
	{
		$this->isFavorite = self::toFlag($isFavorite);
		return $this;
	}

	/**
	 * Retourne le type de reconnaissance appliqué à l'évènement.
	 *
	 * @return string|null
	 */
	public function getRecognition_type()
	{
		return $this->recognition_type;
	}

	/**
	 * Définit le type de reconnaissance appliqué à l'évènement.
	 *
	 * @param string|null $recognition_type Type de reconnaissance
	 * @return $this
	 */
	public function setRecognition_type($recognition_type)
	{
		$this->recognition_type = $recognition_type;
		return $this;
	}

	/**
	 * Retourne la description générée par l'IA générative.
	 *
	 * @return string|null
	 */
	public function getRecognition_description()
	{
		return $this->recognition_description;
	}

	/**
	 * Définit la description générée par l'IA générative.
	 *
	 * @param string|null $recognition_description Description
	 * @return $this
	 */
	public function setRecognition_description($recognition_description)
	{
		$this->recognition_description = $recognition_description;
		return $this;
	}

	/**
	 * Retourne le nom identifié par la reconnaissance.
	 *
	 * @return string|null
	 */
	public function getRecognition_name()
	{
		return $this->recognition_name;
	}

	/**
	 * Définit le nom identifié par la reconnaissance.
	 *
	 * @param string|null $recognition_name Nom
	 * @return $this
	 */
	public function setRecognition_name($recognition_name)
	{
		$this->recognition_name = $recognition_name;
		return $this;
	}

	/**
	 * Retourne le sous-nom identifié par la reconnaissance.
	 *
	 * @return string|null
	 */
	public function getRecognition_subname()
	{
		return $this->recognition_subname;
	}

	/**
	 * Définit le sous-nom identifié par la reconnaissance.
	 *
	 * @param string|null $recognition_subname Sous-nom
	 * @return $this
	 */
	public function setRecognition_subname($recognition_subname)
	{
		$this->recognition_subname = $recognition_subname;
		return $this;
	}

	/**
	 * Retourne les attributs de classification de l'objet suivi.
	 *
	 * @return string|array|null
	 */
	public function getRecognition_attributes()
	{
		return $this->recognition_attributes;
	}

	/**
	 * Définit les attributs de classification de l'objet suivi.
	 *
	 * @param string|array|null $recognition_attributes Attributs
	 * @return $this
	 */
	public function setRecognition_attributes($recognition_attributes)
	{
		$this->recognition_attributes = $recognition_attributes;
		return $this;
	}

	/**
	 * Retourne la plaque d'immatriculation reconnue (LPR).
	 *
	 * @return string|null
	 */
	public function getRecognition_plate()
	{
		return $this->recognition_plate;
	}

	/**
	 * Définit la plaque d'immatriculation reconnue (LPR).
	 *
	 * @param string|null $recognition_plate Plaque
	 * @return $this
	 */
	public function setRecognition_plate($recognition_plate)
	{
		$this->recognition_plate = $recognition_plate;
		return $this;
	}

	/**
	 * Retourne le score de reconnaissance.
	 *
	 * @return float|null
	 */
	public function getRecognition_score()
	{
		return $this->recognition_score;
	}

	/**
	 * Définit le score de reconnaissance, null si la valeur est vide.
	 *
	 * @param float|int|string|null $recognition_score Score
	 * @return $this
	 */
	public function setRecognition_score($recognition_score)
	{
		$this->recognition_score = ($recognition_score === '' || $recognition_score === null)
			? null
			: (float) $recognition_score;
		return $this;
	}
}
