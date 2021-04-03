<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche <rene@system25.de>
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
 * @method Tx_Rnbase_Domain_Model_Data setUid() setUid(int $uid)
 * @method bool hasUid()
 * @method Tx_Rnbase_Domain_Model_Data unsUid()
 *
 * @author Michael Wagner
 */
class Tx_Rnbase_Domain_Model_Data implements Tx_Rnbase_Domain_Model_DataInterface, IteratorAggregate
{
    /**
     * A flag indication if the model was modified after initialisation
     * (eg. by changing a property).
     *
     * @var bool
     */
    private $isModified = false;

    /**
     * Holds the data!
     *
     * @var array
     */
    private $record = [];

    /**
     * Constructor of the data object.
     *
     * @param array|int|null $record
     */
    public function __construct($record = null)
    {
        $this->init($record);
    }

    /**
     * Initialize the data.
     *
     * @param int|array $record
     */
    protected function init($record = null)
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
     * Sets the models's clean state, e.g. after it has been initialized.
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
     * Create a new data model.
     *
     * @param array |Tx_Rnbase_Domain_Model_DataInterface $data
     *
     * @return Tx_Rnbase_Domain_Model_Data
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

        // use get_called_class for backwards compatibility!
        return tx_rnbase::makeInstance(get_called_class(), $data);
    }

    /**
     * Setzt einen Wert oder ersetzt alle Werte.
     *
     * @param string|array $property
     * @param mixed        $value
     *
     * @return $this
     */
    public function setProperty($property, $value = null)
    {
        if (is_array($property)) {
            // wir Überschreiben den kompletten record
            $this->init($property);
        } else {
            // wir setzen einen bestimmten wert
            $this->record[$property] = $value;
        }

        // set the modified state
        $this->isModified = true;

        return $this;
    }

    /**
     * Liefert einen bestimmten Wert oder alle.
     *
     * @param string|null $property
     *
     * @return string|array
     */
    public function getProperty($property = null)
    {
        if (is_null($property)) {
            return $this->record;
        }

        return $this->hasProperty($property) ? $this->record[$property] : null;
    }

    /**
     * Liefert alle properties des Models.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->getProperty();
    }

    /**
     * Entfernt einen Wert.
     *
     * @param string $property
     *
     * @return $this
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
     * @return bool
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
        tx_rnbase::load('Tx_Rnbase_Utility_Strings');

        return Tx_Rnbase_Utility_Strings::camelCaseToLowerCaseUnderscored($string);
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
    public function __call($method, array $args)
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
     * Check if the property exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->record);
    }

    /**
     * Attribute getter.
     *
     * @param string $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        $var = $this->underscore($var);

        return $this->getProperty($var);
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
        return new ArrayIterator($this->getProperties());
    }

    /**
     * Wandelt das Model rekursiv in ein Array um.
     *
     * @return array
     */
    public function toArray()
    {
        $array = $this->getProperties();
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
        $data = $this->getProperties();
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
