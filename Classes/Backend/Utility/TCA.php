<?php

namespace Sys25\RnBase\Backend\Utility;

use LogicException;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Domain\Model\RecordInterface;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Environment;
use Sys25\RnBase\Utility\Extensions;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Utility\Typo3Classes;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015-2021 René Nitzsche <rene@system25.de>
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
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 * @author Michael Wagner <michael.wagner@dmk-business.de>
 */
class TCA
{
    /**
     * Liefert den Wert für ein Attribut aus dem ctrl-Bereich der TCA.
     *
     * @param string $tableName
     * @param string $fieldName
     *
     * @return mixed
     */
    public static function getControlFieldForTable($tableName, $fieldName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl'][$fieldName])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl'][$fieldName];
    }

    /**
     * Liefert den Spaltennamen für das Parent der aktuellen Lokalisierung.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getTransOrigPointerFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'];
    }

    /**
     * Liefert den Spaltennamen für das Parent der aktuellen Lokalisierung.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getLanguageFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
    }

    /**
     * Liefert den Spaltennamen für den Titel der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getLabelFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['label'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['label'];
    }

    /**
     * Liefert den Spaltennamen für den tstamp der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getTstampFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['tstamp'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['tstamp'];
    }

    /**
     * Liefert den Spaltennamen für den tstamp der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getCrdateFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['crdate'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['crdate'];
    }

    /**
     * Liefert den Spaltennamen für die sortierung der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getSortbyFieldForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['sortby'])) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['sortby'];
    }

    /**
     * Liefert alle EnableColumns einer Tabelle.
     *
     * @param string $tableName
     *
     * @return array Array with values:
     *               'fe_group' => 'fe_group',
     *               'delete' =>'deleted',
     *               'disabled' =>'hidden',
     *               'starttime' => 'starttime',
     *               'endtime' => 'endtime'
     */
    protected static function getEnableColumnsForTable($tableName)
    {
        if (empty($GLOBALS['TCA'][$tableName])
            || empty($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'])
        ) {
            return [];
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'];
    }

    /**
     * Liefert den Spaltennamen für die gelöschte elemente der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getDeletedFieldForTable($tableName)
    {
        if (
            empty($GLOBALS['TCA'][$tableName])
            || empty($GLOBALS['TCA'][$tableName]['ctrl']['delete'])
        ) {
            return '';
        }

        return $GLOBALS['TCA'][$tableName]['ctrl']['delete'];
    }

    /**
     * Liefert den Spaltennamen für die deaktivierte elemente der Tabelle.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getDisabledFieldForTable($tableName)
    {
        $cols = self::getEnableColumnsForTable($tableName);

        return empty($cols['disabled']) ? '' : $cols['disabled'];
    }

    /**
     * Load TCA for a specific table. Since T3 6.1 the complete TCA is loaded.
     *
     * @param string $tablename
     */
    public static function loadTCA($tablename)
    {
        if (Environment::isFrontend() && isset($_REQUEST['eID'])) {
            $eidUtility = Typo3Classes::getEidUtilityClass();
            $eidUtility::initTCA();
        } else {
            if (!is_array($GLOBALS['TCA'])) {
                Extensions::loadBaseTca(true);
            }
        }
    }

    /**
     * validates the data of a model with the tca definition of a its table.
     *
     * @param RecordInterface $model
     * @param array $options
     *                                                        only_record_fields: validates only fields included in the record
     *
     * @return bool
     */
    public static function validateModel(
        RecordInterface $model,
        $options = null
    ) {
        return self::validateRecord(
            $model->getProperty(),
            $model->getTableName(),
            $options
        );
    }

    /**
     * validates an array with data with the tca definition of a specific table.
     *
     * @param array  $record
     * @param string $tableName
     * @param array  $options
     *                          only_record_fields: validates only fields included in the record
     *
     * @return bool
     */
    public static function validateRecord(
        array $record,
        $tableName,
        $options = null
    ) {
        $options = DataModel::getInstance($options);
        $columns = self::getTcaColumns($tableName, $options);

        if (empty($columns)) {
            throw new LogicException('No TCA found for "'.$tableName.'".');
        }

        foreach (array_keys($columns) as $column) {
            $recordHasField = array_key_exists($column, $record);
            $value = $recordHasField ? $record[$column] : null;
            // skip, if we have to ignore nonexisten records
            if (!$recordHasField && $options->getOnlyRecordFields()) {
                continue;
            }
            if (!self::validateField($value, $column, $tableName, $options)) {
                // set the error field.
                // only relevant, if $options are given as data object
                $options->setLastInvalidField($column);
                $options->setLastInvalidValue($value);

                return false;
            }
        }

        return true;
    }

    /**
     * validates a value with the tca definition of a specific table.
     *
     * @param string $value
     * @param string $field
     * @param string $tableName
     * @param array  $options only_record_fields: validates only fields included in the record
     *
     * @return bool
     */
    public static function validateField(
        $value,
        $field,
        $tableName,
        $options = null
    ) {
        $options = DataModel::getInstance($options);

        $columns = self::getTcaColumns($tableName, $options);

        // skip, if there is no config
        if (empty($columns[$field]['config'])) {
            return true;
        }

        $config = &$columns[$field]['config'];

        // check minitems
        if (!empty($config['minitems']) && $config['minitems'] > 0 && empty($value)) {
            return false;
        }

        // check eval list
        if (!empty($config['eval'])) {
            // check eval list
            $evalList = Strings::trimExplode(
                ',',
                $config['eval'],
                true
            );
            foreach ($evalList as $func) {
                switch ($func) {
                    // @TODO: implement the other evals
                    case 'required':
                        if (empty($value)) {
                            return false;
                        }

                        break;

                    default:
                        // fiel is not invalid!
                        break;
                }
            }
        }

        return true;
    }

    /**
     * @param string $tableName
     * @param array  $options
     *                          only_record_fields: validates only fields included in the record
     *
     * @return array
     */
    public static function getTcaColumns($tableName, $options = null)
    {
        self::loadTCA($tableName);
        $options = DataModel::getInstance($options);
        $columns = empty($GLOBALS['TCA'][$tableName]['columns']) ? [] : $GLOBALS['TCA'][$tableName]['columns'];
        $tcaOverrides = $options->getTcaOverrides();
        if (!empty($tcaOverrides['columns'])) {
            $columns = Arrays::mergeRecursiveWithOverrule(
                $columns,
                $tcaOverrides['columns']
            );
        }

        return $columns;
    }

    /**
     * Eleminate non-TCA-defined columns from given data.
     *
     * Doesn't do anything if no TCA columns are found.
     *
     * @param array $data Data to be filtered
     *
     * @return array Data now containing only TCA-defined columns
     */
    public static function eleminateNonTcaColumns(
        RecordInterface $model,
        array $data
    ) {
        $needle = $model->getColumnNames();
        // if there is no array means, there is no tca or no columns
        if (!is_array($needle)) {
            return [];
        }

        return Arrays::removeNotIn($data, $needle);
    }

    /**
     * Return the correct uid in respect of localisation.
     *
     * @param string $tableName
     * @param array  $rawData
     *
     * @return int
     */
    public static function getUid($tableName, array $rawData)
    {
        $uid = 0;
        if (!empty($tableName)) {
            // Take care for localized records where uid of original record
            // is stored in $this->record['l18n_parent'] instead of $this->record['uid']!
            $languageParentField = self::getTransOrigPointerFieldForTable($tableName);
            $sysLanguageUidField = self::getLanguageFieldForTable($tableName);

            if (!(
                empty($languageParentField)
                && empty($sysLanguageUidField)
                && empty($rawData[$sysLanguageUidField])
                && empty($rawData[$languageParentField])
            )) {
                $uid = $rawData[$languageParentField] ?? 0;
            }
        }

        return $uid > 0 ? $uid : $rawData['uid'];
    }
}
