<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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

require_once t3lib_extMgm::extPath('rn_base',  'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Basic model with geter's and seter's
 *
 * @method integer getUid()
 * @method tx_rnbase_model_data setUid() setUid(integer $uid)
 * @method boolean hasUid()
 * @method tx_rnbase_model_data unsUid()
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_model
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_model_data {

	/**
	 *
	 * @TODO: declare as private!
	 *
	 * @var array
	 */
	var $record = NULL;

	/**
	 * constructor of the data object
	 *
	 * @param array $record
	 * @return NULL
	 */
	function __construct($record) {
		return $this->init($record);
	}

	/**
	 * initialize the data
	 *
	 * @param array $record
	 * @return NULL
	 */
	function init($record) {
		if (is_array($record)) {
			$this->record = $record;
		}
		else {
			$this->record = array('uid' => $record);
		}

		return NULL;
	}

	/**
	 * create a new data model
	 *
	 * @param array $data
	 * @return tx_rnbase_model_data
	 */
	public static function getInstance(array $data) {
		return tx_rnbase::makeInstance('tx_rnbase_model_data', $data);
	}

	/**
	 * Setzt einen Wert oder ersetzt alle Werte
	 *
	 * @param string|array $property
	 * @param mixed $value
	 * @return tx_t3socials_models_Base
	 */
	public function setProperty($property, $value = NULL) {
		// wir Überschreiben den kompletten record
		if (is_array($property)) {
			$this->init($property);
		}
		// wir setzen einen bestimmten wert
		else {
			$this->record[$property] = $value;
		}
		return $this;
	}

	/**
	 * Liefert einen bestimmten Wert oder alle.
	 *
	 * @param string $property
	 * @return string
	 */
	public function getProperty($property = NULL) {
		if (is_null($property)) {
			return $this->record;
		}
		return $this->hasProperty($property)
			? $this->record[$property]
			: NULL
		;
	}

	/**
	 * Entfernt einen Wert.
	 *
	 * @param string $property
	 * @return tx_t3socials_models_Base
	 */
	public function unsProperty($property) {
		unset($this->record[$property]);
		return $this;
	}

	/**
	 * Prüft ob eine Spalte gesetzt ist.
	 *
	 * @param string $property
	 * @return string
	 */
	public function hasProperty($property) {
		return isset($this->record[$property]);
	}

	/**
	 * Converts field names for setters and geters
	 *
	 * @param string $string
	 * @return string
	 */
	protected function underscore($string) {
		return tx_rnbase_util_Misc::camelCaseToLowerCaseUnderscored($string);
		// return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $string));
	}

	/**
	 * Set/Get attribute wrapper
	 *
	 * @param string $method
	 * @param array $args
	 * @throws Exception
	 * @return mixed
	 */
	public function __call($method, $args) {
		switch (substr($method, 0, 3)) {
			// getColumnValue > record[column_value]
			case 'get':
				$key = $this->underscore(substr($method, 3));
				return $this->getProperty($key);
			// setColumnValue > record[column_value] = $value
			case 'set':
				$key = $this->underscore(substr($method, 3));
				return $this->setProperty($key, isset($args[0]) ? $args[0] : NULL);
			// unsetColumnValue > unset(record[column_value])
			case 'uns':
				$key = $this->underscore(substr($method, 3));
				return $this->unsProperty($key);
			// hasColumnValue > isset(record[column_value])
			case 'has':
				$key = $this->underscore(substr($method, 3));
				return $this->hasProperty($key);
			default:
		}
		throw new Exception(
			'Sorry, Invalid method ' . get_class($this) . '::' . $method .
			'(' . print_r($args, 1) . ').',
			1406625817
		);

		return NULL;
	}

	/**
	 * Wandelt das Model in einen String um
	 *
	 * @return string
	 */
	public function __toString() {
		$data = $this->getProperty();
		$out  = get_class($this) . ' (' . CRLF;
		foreach ($data as $key => $value) {
			$type = gettype($value);
			$value = is_bool($value) ? (int) $value : $value;
			$value = is_string($value) ? '"' . $value . '"' : $value;
			$value = is_object($value) ? implode(CRLF . TAB, explode(CRLF, (string) $value)) : $value;
			$value = is_array($value) ? print_r($value, TRUE) : $value;
			$out .= TAB . $key . ' (' . $type . ')';
			$out .= ': ' . $value . CRLF;
		}
		return $out . ');';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']);
}
