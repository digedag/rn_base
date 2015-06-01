<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 das Medienkombinat
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
tx_rnbase::load('tx_rnbase_model_data');

/**
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 * @author Michael Wagner <michael.wagner@dmk-business.de>
 */
class tx_rnbase_util_TCA {

	/**
	 * Liefert den Spaltennamen für das Parent der aktuellen lokalisierung
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function getTransOrigPointerFieldForTable($tableName) {
		if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])) {
			return '';
		}
		return $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'];
	}
	/**
	 * Liefert den Spaltennamen für das Parent der aktuellen lokalisierung
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function getLanguageFieldForTable($tableName) {
		if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
			return '';
		}
		return $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
	}
	/**
	 * Liefert den Spaltennamen für den Titel der Tabelle.
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function getLabelFieldForTable($tableName) {
		if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['label'])) {
			return '';
		}
		return $GLOBALS['TCA'][$tableName]['ctrl']['label'];
	}
	/**
	 * Liefert den Spaltennamen für den tstamp der Tabelle.
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function getTstampFieldForTable($tableName) {
		if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['tstamp'])) {
			return '';
		}
		return $GLOBALS['TCA'][$tableName]['ctrl']['tstamp'];
	}
	/**
	 * Liefert den Spaltennamen für den tstamp der Tabelle.
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function getCrdateFieldForTable($tableName) {
		if (empty($GLOBALS['TCA'][$tableName]) || empty($GLOBALS['TCA'][$tableName]['ctrl']['crdate'])) {
			return '';
		}
		return $GLOBALS['TCA'][$tableName]['ctrl']['crdate'];
	}

	/**
	 * Load TCA for a specific table. Since T3 6.1 the complete TCA is loaded.
	 * @param string $tablename
	 */
	public static function loadTCA($tablename) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO61OrHigher()) {
			if (!is_array($GLOBALS['TCA'])) {
	 			\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
			}
		}
		else {
			t3lib_div::loadTCA($tablename);
		}
	}

	/**
	 * validates the data of a model with the tca definition of a its table.
	 *
	 * @param tx_rnbase_model_base $model
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record
	 * @return bolean
	 */
	public static function validateModel(
		tx_rnbase_model_base $model,
		$options = NULL
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
	 * @param array $record
	 * @param string $tableName
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record
	 * @return bolean
	 */
	public static function validateRecord(
		array $record,
		$tableName,
		$options = NULL
	) {
		$options = tx_rnbase_model_data::getInstance($options);
		$columns = self::getTcaColumns($tableName, $options);

		if (empty($columns)) {
			throw new LogicException('No TCA found for "' . $tableName . '".');
		}

		foreach (array_keys($columns) as $column) {
			$recordHasField = array_key_exists($column, $record);
			$value = $recordHasField ? $record[$column] : NULL;
			// skip, if we have to ignore nonexisten records
			if (!$recordHasField && $options->getOnlyRecordFields()) {
				continue;
			}
			if (!self::validateField($value, $column, $tableName,$options)) {
				// set the error field.
				// only relevant, if $options are given as data object
				$options->setLastInvalidField($column);
				$options->setLastInvalidValue($value);

				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * validates a value with the tca definition of a specific table.
	 *
	 * @param string $value
	 * @param string $field
	 * @param string $tableName
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record
	 * @return boolean
	 */
	public static function validateField(
		$value,
		$field,
		$tableName,
		$options = NULL
	) {
		$options = tx_rnbase_model_data::getInstance($options);

		$columns = self::getTcaColumns($tableName, $options);

		// skip, if there is no config
		if (empty($columns[$field]['config'])) {
			return TRUE;
		}

		$config = &$columns[$field]['config'];

		// check minitems
		if (!empty($config['minitems']) && $config['minitems'] > 0 && empty($value)) {
			return FALSE;
		}

		// check eval list
		if (!empty($config['eval'])) {
			// check eval list
			tx_rnbase::load('tx_rnbase_util_Strings');
			$evalList = tx_rnbase_util_Strings::trimExplode(
				',',
				$config['eval'],
				TRUE
			);
			foreach ($evalList as $func) {
				switch ($func) {
					// @TODO: implement the other evals
					case 'required':
						if (empty($value)) {
							return FALSE;
						}
						break;

					default:
						// fiel is not invalid!
						break;
				}
			}
		}

		return TRUE;
	}
	/**
	 *
	 * @param string $tableName
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record
	 * @return array
	 */
	public static function getTcaColumns($tableName, $options = NULL) {
		self::loadTCA($tableName);
		$options = tx_rnbase_model_data::getInstance($options);
		$columns = empty($GLOBALS['TCA'][$tableName]['columns'])
			? array()
			: $GLOBALS['TCA'][$tableName]['columns']
		;
		$tcaOverrides = $options->getTcaOverrides();
		if (!empty($tcaOverrides['columns'])) {
			tx_rnbase::load('tx_rnbase_util_Arrays');
			$columns = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
				$columns,
				$tcaOverrides['columns']
			);
		}

		return $columns;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TCA.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TCA.php']);
}