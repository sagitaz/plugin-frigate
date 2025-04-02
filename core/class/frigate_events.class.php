<?php

/**
 * Class Database Event
 *
 * @filesource   frigate_events.class.php
 * @created      06.06.2024
 * @package      sagitaz\plugin-frigate
 * @author       sagitaz
 * @copyright    2024 sagitaz
 * @license      GNU General Public License v3.0 and later; see license.txt
 */
/* * ***************************Includes********************************* */
/*
use DB;
use Exception;
use PDO;
use Log;
*/
class frigate_events
{
	/*     * *************************Attributs****************************** */

	private $id;
	private $event_id;
	private $box;
	private $camera;
	private $data;
	private $lasted;
	private $startTime;
	private $endTime;
	private $false_positive;
	private $hasClip;
	private $clip;
	private $hasSnapshot;
	private $snapshot;
	private $label;
	private $plusId;
	private $retain;
	private $subLabel;
	private $thumbnail;
	private $topScore;
	private $score;
	private $zones;
	private $type;
	private $isFavorite;

	/*     * ***********************Methode static*************************** */

	/**
	 * @throws Exception
	 */
	public static function all(bool $_onlyEnable = FALSE)
	{
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM frigate_events';
		return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * @throws Exception
	 */
	public static function byId($_id): self
	{
		$values = array(
			'id' => $_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
			FROM frigate_events
			WHERE id=:id';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * @throws Exception
	 */
	public static function byEventId($_event_id)
	{
		$values = array(
			'event_id' => $_event_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
			FROM frigate_events
			WHERE event_id=:event_id';

		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}

	public static function byType($_type)
	{
		$values = array(
			'type' => $_type,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
			FROM frigate_events
			WHERE type=:type';

		return DB::Prepare($sql,
			$values,
			DB::FETCH_TYPE_ALL,
			PDO::FETCH_CLASS,
			__CLASS__
		);
	}
	/*     * *********************Methode d'instance************************* */

	public function preSave()
	{
	}

	public function save()
	{
		return DB::save($this);
	}

	public function remove()
	{
		DB::remove($this);
	}

	public function getTableName()
	{
		return 'frigate_events';
	}

	public static function getOldestNotFavorite()
	{
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
				FROM frigate_events
				WHERE isFavorite != 1
				ORDER BY startTime ASC
				LIMIT 1;';
		return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}

	public static function getOldestNotFavorites($_days)
	{
		$days = intval($_days);
		$seconds = $days * 24 * 60 * 60;

		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
            FROM frigate_events
            WHERE isFavorite != 1
            AND startTime < (UNIX_TIMESTAMP(NOW()) - :seconds);';

		$values = array(
			'seconds' => $seconds,
		);

		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}


	/*     * **********************Getteur Setteur*************************** */

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getEventId()
	{
		return $this->event_id;
	}

	public function setEventId($event_id)
	{
		$this->event_id = $event_id;
	}

	public function getBox()
	{
		return $this->box;
	}

	public function setBox($box)
	{
		$this->box = $box;
	}

	public function getCamera()
	{
		return $this->camera;
	}

	public function setCamera($camera)
	{
		$this->camera = $camera;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getLasted()
	{
		return $this->lasted;
	}

	public function setLasted($lasted)
	{
		$this->lasted = $lasted;
	}

	public function getStartTime()
	{
		return $this->startTime;
	}

	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
	}

	public function getFalsePositive()
	{
		return $this->false_positive;
	}

	public function setFalsePositive($false_positive)
	{
		$this->false_positive = $false_positive;
	}

	public function getHasClip()
	{
		return $this->hasClip;
	}

	public function setHasClip($hasClip)
	{
		// Force la valeur à 0 si différent de 1
		$this->hasClip = ($hasClip === 1 || $hasClip === '1') ? 1 : 0;
	}
	public function getClip()
	{
		return $this->clip;
	}

	public function setClip($clip)
	{
		$this->clip = $clip;
	}
	public function getHasSnapshot()
	{
		return $this->hasSnapshot;
	}

	public function setHasSnapshot($hasSnapshot)
	{
		// Force la valeur à 0 si différent de 1
		$this->hasSnapshot = ($hasSnapshot === 1 || $hasSnapshot === '1') ? 1 : 0;
	}
	public function getSnapshot()
	{
		return $this->snapshot;
	}

	public function setSnapshot($snapshot)
	{
		$this->snapshot = $snapshot;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function setLabel($label)
	{
		$this->label = $label;
	}

	public function getPlusId()
	{
		return $this->plusId;
	}

	public function setPlusId($plusId)
	{
		$this->plusId = $plusId;
	}

	public function getRetain()
	{
		return $this->retain;
	}

	public function setRetain($retain)
	{
		$this->retain = $retain;
	}

	public function getSubLabel()
	{
		return $this->subLabel;
	}

	public function setSubLabel($subLabel)
	{
		$this->subLabel = $subLabel;
	}

	public function getThumbnail()
	{
		return $this->thumbnail;
	}

	public function setThumbnail($thumbnail)
	{
		$this->thumbnail = $thumbnail;
	}

	public function getTopScore()
	{
		return $this->topScore;
	}

	public function setTopScore($topScore)
	{
		$this->topScore = $topScore;
	}
	public function getScore()
	{
		return $this->score;
	}

	public function setScore($score)
	{
		$this->score = $score;
	}
	public function getZones()
	{
		return $this->zones;
	}

	public function setZones($zones)
	{
		$this->zones = $zones;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getIsFavorite()
	{
		return $this->isFavorite;
	}

	public function setIsFavorite($isFavorite)
	{		
		// Force la valeur à 0 si différent de 1
		$this->isFavorite = ($isFavorite === 1 || $isFavorite === '1') ? 1 : 0;
	}
}