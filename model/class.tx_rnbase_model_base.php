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

tx_rnbase::load('tx_rnbase_model_data');
tx_rnbase::load('Tx_Rnbase_Domain_Model_RecordInterface');
tx_rnbase::load('tx_rnbase_util_TCA');
// Die Datenbank-Klasse
tx_rnbase::load('tx_rnbase_util_DB');


/**
 * Basisklasse für die meisten Model-Klassen. Sie stellt einen Konstruktor bereit, der sowohl
 * mit einer UID als auch mit einem Datensatz aufgerufen werden kann. Die Daten werden
 * in den Instanzvariablen $uid und $record abgelegt. Diese beiden Variablen sind also immer
 * verfügbar. Der Umfang von $record kann aber je nach Aufruf unterschiedlich sein!
 *
 * @deprecated: IS NO LONGER BEING DEVELOPED!!!
 *              please use Tx_Rnbase_Domain_Model_Base
 *              THIS CLASS WILL BE DROPPED IN THE FUTURE!!!
 */
class tx_rnbase_model_base extends tx_rnbase_model_data implements Tx_Rnbase_Domain_Model_RecordInterface
{
    public $uid;

    /**
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
     * @return NULL
     */
    public function __construct($rowOrUid = null)
    {
        $this->init($rowOrUid);
    }

    /**
     * Most model-classes will be initialized by a uid or a database record. So
     * this is a common contructor.
     * Ensure to overwrite getTableName()!
     *
     * @param mixed $rowOrUid
     * @return NULL
     */
    public function tx_rnbase_model_base($rowOrUid = null)
    {
        return $this->init($rowOrUid);
    }

    /**
     * Inits the model instance either with uid or a complete data record.
     * As the result the instance should be completly loaded.
     *
     * @param mixed $rowOrUid
     * @return NULL
     */
    public function init($rowOrUid = null)
    {
        if (is_array($rowOrUid)) {
            $this->uid = $rowOrUid['uid'];
            $this->record = $rowOrUid;
        } else {
            $rowOrUid = (int) $rowOrUid;
            $this->uid = $rowOrUid;
            if ($rowOrUid === 0) {
                $this->record = array();
            } elseif ($this->getTableName()) {
                $this->record = tx_rnbase_util_DB::getRecord($this->getTableName(), $this->uid);
            }
            // Der Record sollte immer ein Array sein
            $this->record = is_array($this->record) ? $this->record : array();
        }

        // set the modified state to clean
        $this->resetCleanState();

        return null;
    }
    /**
     * Returns the records uid
     *
     * @return int
     */
    public function getUid()
    {
        return tx_rnbase_util_TCA::getUid(
            $this->getTableName(),
            $this->getRecord() + ['uid' => $this->uid]
        );
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
     * @return DateTime
     */
    public function getCreationDateTime($timezone = null)
    {
        $datetime = null;
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $field = tx_rnbase_util_TCA::getCrdateFieldForTable($tableName);
            if (!$this->isPropertyEmpty($field)) {
                $tstamp = (int) $this->getProperty($field);
                tx_rnbase::load('tx_rnbase_util_Dates');
                $datetime = tx_rnbase_util_Dates::getDateTime('@' . $tstamp);
            }
        }

        return $datetime;
    }

    /**
     * Returns the creation date of the record as DateTime object.
     *
     * @return DateTime
     */
    public function getLastModifyDateTime($timezone = null)
    {
        $datetime = null;
        $tableName = $this->getTableName();
        if (!empty($tableName)) {
            $field = tx_rnbase_util_TCA::getTstampFieldForTable($tableName);
            if (!$this->isPropertyEmpty($field)) {
                $tstamp = (int) $this->getProperty($field);
                tx_rnbase::load('tx_rnbase_util_Dates');
                $datetime = tx_rnbase_util_Dates::getDateTime('@' . $tstamp);
            }
        }

        return $datetime;
    }

    /**
     * Reload this records from database
     *
     * @return tx_rnbase_model_base
     */
    public function reset()
    {
        $this->record = tx_rnbase_util_DB::getRecord(
            $this->getTableName(),
            $this->getUid()
        );

        // set the modified state to clean
        $this->resetCleanState();

        return $this;
    }
    /**
     * Liefert den aktuellen Tabellenname
     *
     * @return Tabellenname als String
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Setzt den aktuellen Tabellenname
     *
     * @param string $tableName
     * @return tx_rnbase_model_base
     */
    public function setTableName($tableName = 0)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Check if this record is valid. If FALSE, the record is maybe deleted in database.
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->record) > 0;
    }

    /**
     * Check if record is persisted in database. This is if uid is not 0.
     *
     * @return bool
     */
    public function isPersisted()
    {
        return $this->getUid() > 0;
    }

    /**
     * validates the data of a model with the tca definition of a its table.
     *
     * @param array $options
     *     only_record_fields: validates only fields included in the record (default)
     * @return bolean
     */
    public function validateProperties($options = null)
    {
        return tx_rnbase_util_TCA::validateModel(
            $this,
            $options === null ? array('only_record_fields' => true) : $options
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
     * Returns the record
     *
     * @return array
     */
    public function getRecord()
    {
        return $this->getProperty();
    }

    /**
     * Liefert bei Tabellen, die im $TCA definiert sind,
     * die Namen der Tabellenspalten als Array.
     *
     * @return array mit Spaltennamen oder 0
     */
    public function getColumnNames()
    {
        $columns = $this->getTCAColumns();

        return is_array($columns) ? array_keys($columns) : 0;
    }

    /**
     * Liefert die TCA-Definition der in der Tabelle definierten Spalten
     *
     * @return array
     */
    public function getTCAColumns()
    {
        $columns = tx_rnbase_util_TCA::getTcaColumns($this->getTableName());

        return empty($columns) ? 0 : $columns;
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
     *
     * @param $formatter ein voll initialisierter Formatter für den Wrap
     * @param $columnName der Name der Spalte
     * @param $baseConfId Id der übergeordneten Config
     * @param $colConfId Id der Spalte in der Config zum Aussetzen der Konvention (muss mit Punkt enden)
     * @deprecated
     */
    public function getColumnWrapped($formatter, $columnName, $baseConfId, $colConfId = '')
    {
        $colConfId = (strlen($colConfId)) ? $colConfId : $columnName . '.';

        return $formatter->wrap($this->record[$columnName], $baseConfId . $colConfId);
    }
}
