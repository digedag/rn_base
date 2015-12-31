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
class tx_rnbase_model_data
	implements IteratorAggregate, ArrayAccess {

	/**
	 * A flag indication if the model was modified after initialisation
	 * (eg. by changing a property)
	 *
	 * @var boolean
	 */
	private $isModified = FALSE;

	/**
	 *
	 * @TODO: declare as private!
	 *
	 * @var array
	 */
	var $record = array();

	/**
	 * constructor of the data object
	 *
	 * @param array $record
	 * @return NULL
	 */
	function __construct($record = NULL) {
		return $this->init($record);
	}

	/**
	 * initialize the data
	 *
	 * @param array $record
	 * @return NULL
	 */
	function init($record = NULL) {
		if (is_array($record)) {
			$this->record = $record;
		}
		else {
			$record = (int) $record;
			$this->record = $record > 0 ? array('uid' => $record) : array();
		}

		// set the modified state to clean
		$this->resetCleanState();

		return NULL;
	}

	/**
	 * sets the models's clean state, e.g. after it has been initialized.
	 *
	 * @return void
	 */
	protected function resetCleanState() {
		$this->isModified = FALSE;
	}

	/**
	 * Returns TRUE if the model was modified after initialisation.
	 *
	 * @return boolean
	 */
	public function isDirty() {
		return $this->isModified;
	}

	/**
	 * create a new data model
	 *
	 * @param array $data
	 * @return tx_rnbase_model_data
	 */
	public static function getInstance($data = NULL) {
		if ($data instanceof tx_rnbase_model_data) {
			return $data;
		}
		if (is_array($data)) {
			// create data instances recursive!
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$data[$key] = static::getInstance($value);
				}
			}
		} else {
			$data = array();
		}
		return tx_rnbase::makeInstance('tx_rnbase_model_data', $data);
	}

	/**
	 * Setzt einen Wert oder ersetzt alle Werte
	 *
	 * @param string|array $property
	 * @param mixed $value
	 * @return tx_rnbase_model_data
	 */
	public function setProperty($property, $value = NULL) {
		// set the modified state
		$this->isModified = TRUE;
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
	 * @return tx_rnbase_model_data
	 */
	public function unsProperty($property) {
		// set the modified state
		if (array_key_exists($property,$this->record)) {
			$this->isModified = TRUE;
		}
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
	 * Prüft ob eine Spalte leer ist.
	 *
	 * @param string $property
	 * @return boolean
	 */
	public function isPropertyEmpty($property) {
		return empty($this->record[$property]);
	}

	/**
	 * Converts field names for setters and geters
	 *
	 * @param string $string
	 * @return string
	 */
	protected function underscore($string) {
		tx_rnbase::load('tx_rnbase_util_Strings');
		return tx_rnbase_util_Strings::camelCaseToLowerCaseUnderscored($string);
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
	 * Attribute getter (deprecated)
	 *
	 * @param string $var
	 * @return mixed
	 */

	public function __get($var)
	{
		$var = $this->underscore($var);
		return $this->getProperty($var);
	}

	/**
	 * Attribute setter (deprecated)
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	public function __set($var, $value)
	{
		$var = $this->underscore($var);
		$this->setProperty($var, $value);
	}

    /**
     * Implementation of ArrayAccess::offsetSet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->record[$offset] = $value;
    }

    /**
     * Implementation of ArrayAccess::offsetExists()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->record[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetUnset()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->record[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetGet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->record[$offset]) ? $this->record[$offset] : NULL;
    }

	/**
	 * Implementation of IteratorAggregate::getIterator()
	 *
	 * WARNING: dont iterate over an object
	 * and manipulate the value by reference like this:
	 * foreach($data as $var) {$var = 0; };
	 * user this to manipulate the data:
	 * foreach($data as $key => $var) { $data->setProperty($key, 0); };
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->getProperty());
	}

	/**
	 * Wandelt das Model rekursiv in ein Array um.
	 *
	 * @return array
	 */
	public function toArray() {
		$array = $this->getProperty();
		foreach ($array as $key => $value) {
			if ($value instanceof tx_rnbase_model_data) {
				$array[$key] = $value->toArray();
			}
		}

		return $array;
	}

	/**
	 * Wandelt das Model in einen String um
	 *
	 * @return string
	 */
	public function toString() {
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

	/**
	 * Wandelt das Model in einen String um
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']);
}
