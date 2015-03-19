<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2013 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_model_data');
// Die Datenbank-Klasse
tx_rnbase::load('tx_rnbase_util_DB');


/**
 * This interface defines a base model
 */
interface tx_rnbase_IModel {
	/**
	 * Returns the uid
	 *
	 * @return int
	 */
	public function getUid();

	/**
	 * Returns the data record as array
	 *
	 * @return array
	 */
	public function getRecord();

}


/**
 * Basisklasse für die meisten Model-Klassen. Sie stellt einen Konstruktor bereit, der sowohl
 * mit einer UID als auch mit einem Datensatz aufgerufen werden kann. Die Daten werden
 * in den Instanzvariablen $uid und $record abgelegt. Diese beiden Variablen sind also immer
 * verfügbar. Der Umfang von $record kann aber je nach Aufruf unterschiedlich sein!
 */
class tx_rnbase_model_base extends tx_rnbase_model_data implements tx_rnbase_IModel {

	var $uid;

	/**
	 *
	 * @var string|0
	 */
	private $tableName = 0;

	function tx_rnbase_model_base($rowOrUid = NULL) {
		return $this->init($rowOrUid);
	}

	/**
	 * Most model-classes will be initialized by a uid or a database record. So
	 * this is a common contructor.
	 * Ensure to overwrite getTableName()!
	 *
	 * @param mixed $rowOrUid
	 * @return NULL
	 */
	function __construct($rowOrUid = NULL) {
		return $this->tx_rnbase_model_base($rowOrUid);
	}

	/**
	 * Inits the model instance either with uid or a complete data record.
	 * As the result the instance should be completly loaded.
	 *
	 * @param mixed $rowOrUid
	 * @return NULL
	 */
	function init($rowOrUid = NULL) {
		if (is_array($rowOrUid)) {
			$this->uid = $rowOrUid['uid'];
			$this->record = $rowOrUid;
		}
		else {
			$rowOrUid = (int) $rowOrUid;
			$this->uid = $rowOrUid;
			if ($rowOrUid === 0) {
				$this->record = array();
			} elseif($this->getTableName()) {
				$options = array();
				if(is_object($GLOBALS['BE_USER']) && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects']) {
					$options['enablefieldsbe'] = 1;
				}
				$this->record = tx_rnbase_util_DB::getRecord($this->getTableName(), $this->uid, $options);
			}
			// Der Record sollte immer ein Array sein
			$this->record = is_array($this->record) ? $this->record : array();
		}

		return NULL;
	}
	/**
	 * Returns the records uid
	 *
	 * @return int
	 */
	function getUid() {
		$uid = 0;
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			tx_rnbase::load('tx_rnbase_util_TCA');
			// Take care for localized records where uid of original record
			// is stored in $this->record['l18n_parent'] instead of $this->record['uid']!
			$languageParentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($tableName);
			$sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);
			if (
				!(
					empty($languageParentField)
					&& empty($sysLanguageUidField)
					&& empty($this->record[$sysLanguageUidField])
					&& empty($this->record[$languageParentField])
				)
			) {
				$uid = $this->record[$languageParentField];
			}
		}
		return $uid > 0 ? $uid : $this->uid;
	}

	/**
	 * Reload this records from database
	 *
	 * @return tx_rnbase_model_base
	 */
	function reset() {
		$this->record = tx_rnbase_util_DB::getRecord(
			$this->getTableName(),
			$this->getUid()
		);
		return $this;
	}
	/**
	 * Liefert den aktuellen Tabellenname
	 *
	 * @return Tabellenname als String
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * Setzt den aktuellen Tabellenname
	 *
	 * @param string $tableName
	 * @return tx_rnbase_model_base
	 */
	public function setTableName($tableName = 0) {
		$this->tableName = $tableName;
		return $this;
	}

	/**
	 * Check if this record is valid. If FALSE, the record is maybe deleted in database.
	 *
	 * @return boolean
	 */
	public function isValid() {
		return count($this->record) > 0;
	}

	/**
	 * Check if record is persisted in database. This is if uid is not 0.
	 *
	 * @return boolean
	 */
	public function isPersisted() {
		return $this->getUid() > 0;
	}

	/**
	 * validates the data of a model with the tca definition of a its table.
	 *
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record (default)
	 * @return bolean
	 */
	public function validateProperties($options = NULL) {
		tx_rnbase::load('tx_rnbase_util_TCA');
		return tx_rnbase_util_TCA::validateModel(
			$this,
			$options === NULL ? array('only_record_fields' => TRUE) : $options
		);
	}

	/**
	 * Ist der Datensatz als gelöscht markiert?
	 * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
	 *
	 * @return boolean
	 */
	public function isDeleted() {
		$tableName = $this->getTableName();
		$field = empty($GLOBALS['TCA'][$tableName]['ctrl']['delete'])
			? 'deleted'
			: $GLOBALS['TCA'][$tableName]['ctrl']['delete']
		;
		$value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;
		return $value > 0;
	}

	/**
	 * Ist der Datensatz als gelöscht markiert?
	 * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
	 *
	 * @return boolean
	 */
	public function isHidden() {
		$tableName = $this->getTableName();
		$field = empty($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'])
			? 'hidden'
			: $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled']
		;
		$value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;
		return $value > 0;
	}

	/**
	 * Returns the record
	 *
	 * @return array
	 */
	function getRecord() {
		return $this->getProperty();
	}

	/**
	 * Liefert bei Tabellen, die im $TCA definiert sind,
	 * die Namen der Tabellenspalten als Array.
	 *
	 * @return array mit Spaltennamen oder 0
	 */
	public function getColumnNames() {
		tx_rnbase::load('tx_rnbase_util_TCA');
		tx_rnbase_util_TCA::loadTCA($this->getTableName());
		$cols = $this->getTCAColumns();
		return is_array($cols) ? array_keys($cols) : 0;
	}

	/**
	 * Liefert die TCA-Definition der in der Tabelle definierten Spalten
	 *
	 * @return array
	 */
	public function getTCAColumns() {
		tx_rnbase::load('tx_rnbase_util_TCA');
		tx_rnbase_util_TCA::loadTCA($this->getTableName());
		return isset($GLOBALS['TCA'][$this->getTableName()])
			? $GLOBALS['TCA'][$this->getTableName()]['columns']
			: 0
		;
	}

	/**
	 * Liefert den Inhalt eine Spalte formatiert durch eine stdWrap. Per Konvention wird
	 * erwartet, das der Name der Spalte auch in der TS-Config verwendet wird.
	 * Wenn in einem Objekt der Klasse event eine Spalte/Attribut "date" existiert, dann sollte
	 * das passende TypoScript folgendes Aussehen haben:
	 * <pre>
	 * event.date.strftime = %d-%b-%y
	 * </pre>
	 * Hier wäre <b>event.</b> die $confId und <b>date</b> der Spaltename
	 * @param $formatter ein voll initialisierter Formatter für den Wrap
	 * @param $columnName der Name der Spalte
	 * @param $baseConfId Id der übergeordneten Config
	 * @param $colConfId Id der Spalte in der Config zum Aussetzen der Konvention (muss mit Punkt enden)
	 * @deprecated
	 */
	function getColumnWrapped($formatter, $columnName, $baseConfId, $colConfId = '') {
		$colConfId = ( strlen($colConfId) ) ? $colConfId : $columnName . '.';
		return $formatter->wrap($this->record[$columnName], $baseConfId . $colConfId);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']);
}

