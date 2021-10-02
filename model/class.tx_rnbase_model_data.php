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

tx_rnbase::load('Tx_Rnbase_Domain_Model_DataInterface');

/**
 * Basic model with geter's and seter's.
 *
 * @method int getUid()
 * @method intteger $uid)
 * @method bool hasUid()
 * @method tx_rnbase_model_data unsUid()
 *
 * @deprecated: IS NO LONGER BEING DEVELOPED!!!
 *              please use Tx_Rnbase_Domain_Model_Data
 *              THIS CLASS WILL BE DROPPED IN THE FUTURE!!!
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_model_data implements Tx_Rnbase_Domain_Model_DataInterface, IteratorAggregate
{
    /**
     * A flag indication if the model was modified after initialisation
     * (eg. by changing a property).
     *
     * @var bool
     */
    private $isModified = false;

    /**
     * @TODO: declare as private!
     *
     * @var array
     */
    public $record = [];

    /**
     * constructor of the data object.
     *
     * @param array $record
     */
    public function __construct($record = null)
    {
        $this->init($record);
    }

    /**
     * initialize the data.
     *
     * @param array $record
     */
    public function init($record = null)
    {
        if (is_array($record)) {
            $this->record = $record;
        } else {
            $record = (int) $record;
            $this->record = $record > 0 ? ['uid' => $record] : [];
        }

        // set the modified state to clean
        $this->resetCleanState();

        return null;
    }

    /**
     * sets the models's clean state, e.g. after it has been initialized.
     */
    protected function resetCleanState()
    {
        $this->isModified = false;
    }

    /**
     * Returns TRUE if the model was modified after initialisation.
     *
     * @return bool
     */
    public function isDirty()
    {
        return $this->isModified;
    }

    /**
     * Returns TRUE if the model has no data.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->record);
    }

    /**
     * create a new data model.
     *
     * @param array $data
     *
     * @return tx_rnbase_model_data
     */
    public static function getInstance($data = null)
    {
        if ($data instanceof self) {
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
            $data = [];
        }

        return tx_rnbase::makeInstance('tx_rnbase_model_data', $data);
    }

    /**
     * Setzt einen Wert oder ersetzt alle Werte.
     *
     * @param string|array $property
     * @param mixed        $value
     *
     * @return tx_rnbase_model_data
     */
    public function setProperty($property, $value = null)
    {
        // set the modified state
        $this->isModified = true;
        // wir Überschreiben den kompletten record
        if (is_array($property)) {
            $this->init($property);
        } // wir setzen einen bestimmten wert
        else {
            $this->record[$property] = $value;
        }

        return $this;
    }

    /**
     * Liefert einen bestimmten Wert oder alle.
     *
     * @param string $property
     *
     * @return string
     */
    public function getProperty($property = null)
    {
        if (is_null($property)) {
            return $this->record;
        }

        return $this->hasProperty($property) ? $this->record[$property] : null;
    }

    /**
     * Entfernt einen Wert.
     *
     * @param string $property
     *
     * @return tx_rnbase_model_data
     */
    public function unsProperty($property)
    {
        // set the modified state
        if (array_key_exists($property, $this->record)) {
            $this->isModified = true;
        }
        unset($this->record[$property]);

        return $this;
    }

    /**
     * Prüft ob eine Spalte gesetzt ist.
     *
     * @param string $property
     *
     * @return string
     */
    public function hasProperty($property)
    {
        return isset($this->record[$property]);
    }

    /**
     * Prüft ob eine Spalte leer ist.
     *
     * @param string $property
     *
     * @return bool
     */
    public function isPropertyEmpty($property)
    {
        return empty($this->record[$property]);
    }

    /**
     * Converts field names for setters and geters.
     *
     * @param string $string
     *
     * @return string
     */
    protected function underscore($string)
    {
        return tx_rnbase_util_Strings::camelCaseToLowerCaseUnderscored($string);
    }

    /**
     * Set/Get attribute wrapper.
     *
     * @param string $method
     * @param array  $args
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            // getColumnValue > record[column_value]
            case 'get':
                $key = $this->underscore(substr($method, 3));

                return $this->getProperty($key);
            // setColumnValue > record[column_value] = $value
            case 'set':
                $key = $this->underscore(substr($method, 3));

                return $this->setProperty($key, isset($args[0]) ? $args[0] : null);
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
        throw new Exception('Sorry, Invalid method '.get_class($this).'::'.$method.'('.print_r($args, 1).').', 1406625817);
    }

    /**
     * Implementation of IteratorAggregate::getIterator().
     *
     * WARNING: dont iterate over an object
     * and manipulate the value by reference like this:
     * foreach($data as $var) {$var = 0; };
     * user this to manipulate the data:
     * foreach($data as $key => $var) { $data->setProperty($key, 0); };
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getProperty());
    }

    /**
     * Wandelt das Model rekursiv in ein Array um.
     *
     * @return array
     */
    public function toArray()
    {
        $array = $this->getProperty();
        foreach ($array as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            }
        }

        return $array;
    }

    /**
     * Wandelt das Model in einen String um.
     *
     * @return string
     */
    public function toString()
    {
        $data = $this->getProperty();
        $out = get_class($this).' ('.CRLF;
        foreach ($data as $key => $value) {
            $type = gettype($value);
            $value = is_bool($value) ? (int) $value : $value;
            $value = is_string($value) ? '"'.$value.'"' : $value;
            $value = is_object($value) ? implode(CRLF.TAB, explode(CRLF, (string) $value)) : $value;
            $value = is_array($value) ? print_r($value, true) : $value;
            $out .= TAB.$key.' ('.$type.')';
            $out .= ': '.$value.CRLF;
        }

        return $out.');';
    }

    /**
     * Wandelt das Model in einen String um.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
