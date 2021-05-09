<?php

namespace Sys25\RnBase\Domain\Model;

use DateTime;
use DateTimeZone;
use Sys25\RnBase\Database\Connection;
use tx_rnbase;
use tx_rnbase_util_Dates;
use tx_rnbase_util_TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche <rene@system25.de>
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
 * Basisklasse für die meisten Model-Klassen.
 * Sie stellt einen Konstruktor bereit,
 * der sowohl mit einer UID als auch mit einem Datensatz aufgerufen werden kann.
 * Die Daten werden in den Instanzvariablen $uid und $record abgelegt.
 * Der Umfang von $record kann aber je nach Aufruf unterschiedlich sein!
 *
 * @author René Nitzsche
 * @author Michael Wagner
 */
class BaseModel extends DataModel implements DomainInterface, DynamicTableInterface, RecordInterface
{
    /**
     * The table name of this record.
     *
     * @var string|0
     */
    private $tableName = 0;

    /**
     * Most model-classes will be initialized by a uid or a database record. So
     * this is a common contructor.
     * Ensure to overwrite getTableName()!
     *
     * @param mixed $rowOrUid
     */
    public function __construct($rowOrUid = null)
    {
        $this->init($rowOrUid);
    }

    /**
     * Inits the model instance either with uid or a complete data record.
     * As the result the instance should be completly loaded.
     *
     * @param mixed $rowOrUid
     */
    protected function init($rowOrUid = null)
    {
        if (is_array($rowOrUid)) {
            parent::init($rowOrUid);
        } else {
            parent::init((int) $rowOrUid);
            if ($this->getTableName()) {
                $this->loadRecord();
            }
        }

        // set the modified state to clean
        $this->resetCleanState();

        return null;
    }

    /**
     * Loads the record to the model by its uid.
     */
    protected function loadRecord()
    {
        // skip record loading, if there is no uid!
        if (!$this->isPersisted()) {
            return;
        }

        $db = Connection::getInstance();
        $record = $db->getRecord(
            $this->getTableName(),
            $this->getUidRaw()
        );

        $this->setProperty($record);
    }

    /**
     * Setzt einen Wert oder ersetzt alle Werte.
     *
     * @param string|array $property
     * @param mixed        $value
     *
     * @return DataModel
     */
    public function setProperty($property, $value = null)
    {
        if (is_array($property)) {
            foreach ($property as $subProperty => $subValue) {
                // ignore uid overriding!!!
                if ('uid' === $subProperty && $this->hasUid()) {
                    continue;
                }
                parent::setProperty($subProperty, $subValue);
            }
        } else {
            parent::setProperty($property, $value);
        }

        return $this;
    }

    /**
     * Returns the records uid.
     *
     * If this record is a language overlay, so the uid of the parent are given back.
     *
     * @return int
     */
    public function getUid()
    {
        return (int) tx_rnbase_util_TCA::getUid(
            $this->getTableName(),
            $this->getProperties() + ['uid' => $this->getUidRaw()]
        );
    }

    /**
     * Returns the original uid from the record.
     *
     * @return int
     */
    public function getUidRaw()
    {
        return (int) $this->getProperty('uid');
    }

    /**
     * Returns the label of the record, defined in the tca.
     *
     * @return int
     */
    public function getTcaLabel()
    {
        $label = '';
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $labelField = tx_rnbase_util_TCA::getLabelFieldForTable($tableName);
            if (!$this->isPropertyEmpty($labelField)) {
                $label = (string) $this->getProperty($labelField);
            }
        }

        return $label;
    }

    /**
     * Returns the Language id of the record.
     *
     * @return int
     */
    public function getSysLanguageUid()
    {
        $uid = 0;
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);
            if (!$this->isPropertyEmpty($sysLanguageUidField)) {
                $uid = (int) $this->getProperty($sysLanguageUidField);
            }
        }

        return $uid;
    }

    /**
     * Returns the creation date of the record as DateTime object.
     *
     * @param DateTimeZone $timezone
     *
     * @return DateTime
     */
    public function getCreationDateTime(
        DateTimeZone $timezone = null
    ) {
        $datetime = null;
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $field = tx_rnbase_util_TCA::getCrdateFieldForTable($tableName);
            if (!$this->isPropertyEmpty($field)) {
                $tstamp = (int) $this->getProperty($field);
                $datetime = tx_rnbase_util_Dates::getDateTime(
                    '@'.$tstamp,
                    $timezone
                );
            }
        }

        return $datetime;
    }

    /**
     * Returns the creation date of the record as DateTime object.
     *
     * @param DateTimeZone $timezone
     *
     * @return DateTime
     */
    public function getLastModifyDateTime(
        DateTimeZone $timezone = null
    ) {
        $datetime = null;
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $field = tx_rnbase_util_TCA::getTstampFieldForTable($tableName);
            if (!$this->isPropertyEmpty($field)) {
                $tstamp = (int) $this->getProperty($field);
                tx_rnbase::load('tx_rnbase_util_Dates');
                $datetime = tx_rnbase_util_Dates::getDateTime(
                    '@'.$tstamp,
                    $timezone
                );
            }
        }

        return $datetime;
    }

    /**
     * Reload this records from database.
     *
     * @return BaseModel
     */
    public function reset()
    {
        $this->loadRecord();

        // set the modified state to clean
        $this->resetCleanState();

        return $this;
    }

    /**
     * Liefert den aktuellen Tabellenname.
     *
     * @return string Tabellenname als String
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Setzt den aktuellen Tabellenname.
     *
     * @param string $tableName
     *
     * @return BaseModel
     */
    public function setTableName($tableName = 0)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Check if this record is valid.
     * If FALSE, the record is maybe deleted in database.
     * A valid model must have an uid or at least one other column.
     *
     * @return bool
     */
    public function isValid()
    {
        $mincount = (int) $this->hasProperty('uid') + 1;

        return !$this->isEmpty() && count($this->getProperties()) >= $mincount;
    }

    /**
     * Check if record is persisted in database. This is if uid is not 0.
     *
     * @return bool
     */
    public function isPersisted()
    {
        return $this->getUidRaw() > 0;
    }

    /**
     * Validates the data of a model with the tca definition of a its table.
     *
     * @param array|null $options
     *                            only_record_fields: validates only fields included in the record (default)
     *
     * @return bool
     */
    public function validateProperties($options = null)
    {
        return tx_rnbase_util_TCA::validateModel(
            $this,
            null === $options ? ['only_record_fields' => true] : $options
        );
    }

    /**
     * Ist der Datensatz als gelöscht markiert?
     * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
     *
     * @return bool
     */
    public function isDeleted()
    {
        $tableName = $this->getTableName();
        $field = empty($GLOBALS['TCA'][$tableName]['ctrl']['delete']) ? 'deleted' : $GLOBALS['TCA'][$tableName]['ctrl']['delete'];
        $value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;

        return $value > 0;
    }

    /**
     * Ist der Datensatz als gelöscht markiert?
     * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
     *
     * @return bool
     */
    public function isHidden()
    {
        $tableName = $this->getTableName();
        $field = empty($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled']) ? 'hidden' : $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'];
        $value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;

        return $value > 0;
    }

    /**
     * Returns the record.
     *
     * @return array
     */
    public function getRecord()
    {
        return $this->getProperties();
    }

    /**
     * Liefert bei Tabellen, die im $TCA definiert sind,
     * die Namen der Tabellenspalten als Array.
     *
     * @return array mit Spaltennamen oder 0
     */
    public function getColumnNames()
    {
        $columns = $this->getTcaColumns();

        return is_array($columns) ? array_keys($columns) : 0;
    }

    /**
     * Liefert die TCA-Definition der in der Tabelle definierten Spalten.
     *
     * @return array mit Spaltennamen oder 0
     */
    public function getTcaColumns()
    {
        $columns = tx_rnbase_util_TCA::getTcaColumns($this->getTableName());

        return empty($columns) ? 0 : $columns;
    }
}
