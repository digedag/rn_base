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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
// Die Datenbank-Klasse
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_util_Misc');


/**
 * This interface defines a base model
 */
interface tx_rnbase_IModel {
	/**
	 * Returns the uid
	 * @return int
	 */
	public function getUid();

	/**
	 * Returns the data record as array
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
class tx_rnbase_model_base implements tx_rnbase_IModel {

	var $uid;
	var $record;

	function tx_rnbase_model_base($rowOrUid) {
		$this->init($rowOrUid);
	}
	/**
	 * Most model-classes will be initialized by a uid or a database record. So
	 * this is a common contructor.
	 * Ensure to overwrite getTableName()!
	 */
	function __construct($rowOrUid) {
		$this->tx_rnbase_model_base($rowOrUid);
	}

	/**
	 * Inits the model instance either with uid or a complete data record.
	 * As the result the instance should be completly loaded.
	 *
	 * @param mixed $rowOrUid
	 */
	function init($rowOrUid) {
		if(is_array($rowOrUid)) {
			$this->uid = $rowOrUid['uid'];
			$this->record = $rowOrUid;
		}
		else {
			$this->uid = $rowOrUid;
			if($this->getTableName()) {
				$options = array();
				if(is_object($GLOBALS['BE_USER']) && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects']) {
					$options['enablefieldsbe'] = 1;
				}
				$this->record = tx_rnbase_util_DB::getRecord($this->getTableName(),$this->uid, $options);
			}
			// Der Record sollte immer ein Array sein
			$this->record = is_array($this->record) ? $this->record : array();
		}
	}
	/**
	 * Returns the records uid
	 * @return int
	 */
	function getUid() {
		tx_rnbase::load('tx_rnbase_util_TCA');
		// Take care for localized records where uid of original record
		// is stored in $this->record['l18n_parent'] instead of $this->record['uid']!
		$tableName = $this->getTableName();
		$sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);
		$languageParentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($tableName);
		$isTranslatedRecord = 	isset($this->record[$sysLanguageUidField]) &&
								$this->record[$sysLanguageUidField] &&
								isset($this->record[$languageParentField]);

		return $isTranslatedRecord ? $this->record[$languageParentField] : $this->uid;
	}

	/**
	 * Reload this records from database
	 */
	function reset() {
		$this->record = tx_rnbase_util_DB::getRecord($this->getTableName(),$this->getUid());
	}
	/**
	 * Kindklassen müssen diese Methode überschreiben und den Namen der gemappten Tabelle liefern!
	 * @return Tabellenname als String
	 */
	function getTableName() {
		return 0;
	}
	/**
	 * Check if this record is valid. If false, the record is maybe deleted in database.
	 *
	 * @return boolean
	 */
	function isValid() {
		return count($this->record) > 0;
	}
	/**
	 * Check if record is persisted in database. This is if uid is not 0.
	 *
	 * @return boolean
	 */
	function isPersisted() {
		return intval($this->getUid()) > 0;
	}

	/**
	 * Returns the record
	 * @return array
	 */
	function getRecord() { return $this->record; }

	public function __call($methodName, $parameters){
		if (strlen($methodName) > 3 && substr($methodName, 0, 3) === 'get'){
			// simple getter
			$propertyName = substr($methodName, 3);
			$value = tx_rnbase_util_Misc::camelCaseToLowerCaseUnderscored($propertyName);
			return $this->record[$value];
		}
		throw new Exception('Sorry, the model "'.get_class($this).'" does not support the method "' . $methodName . '".', 1370258960);
	}

	/**
	 * Liefert bei Tabellen, die im $TCA definiert sind, die Namen der Tabellenspalten als Array.
	 * @return Array mit Spaltennamen oder 0
	 */
	function getColumnNames() {
		global $TCA;
		t3lib_div::loadTCA($this->getTableName());
		$cols = $this->getTCAColumns();
		return is_array($cols) ? array_keys($cols) : 0;
	}

	/**
	 * Liefert die TCA-Definition der in der Tabelle definierten Spalten
	 *
	 * @return array
	 */
	function getTCAColumns() {
		global $TCA;
		t3lib_div::loadTCA($this->getTableName());
		return isset($TCA[$this->getTableName()]) ? $TCA[$this->getTableName()]['columns'] : 0;
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

	function __toString() {
		$out = get_class($this). "\n\nRecord:\n";
		while (list($key,$val)=each($this->record))	{
			$out .= $key. ' = ' . $val . "\n";
		}
		reset($this->record);
		return $out; //t3lib_div::view_array($this->record);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']);
}
?>
