<?php
/**
 *  Copyright notice
 *
 *  (c) 2015 Hannes Bochmann <rene@system25.de>
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
 */

/**
 * Tx_Rnbase_Backend_Utility
 *
 * Wrapper für t3lib_BEfunc bzw \TYPO3\CMS\Backend\Utility\BackendUtility
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <rene@system25.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_Utility {

	/**
	 * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData
	 * @see t3lib_BEfunc::getModuleData
	 *
	 * @param array $MOD_MENU MOD_MENU is an array that defines the options in menus.
	 * @param array $CHANGED_SETTINGS CHANGED_SETTINGS represents the array used when passing values to the script from the menus.
	 * @param string $modName modName is the name of this module. Used to get the correct module data.
	 * @param string $type If type is 'ses' then the data is stored as session-lasting data. This means that it'll be wiped out the next time the user logs in.
	 * @param string $dontValidateList dontValidateList can be used to list variables that should not be checked if their value is found in the MOD_MENU array. Used for dynamically generated menus.
	 * @param string $setDefaultList List of default values from $MOD_MENU to set in the output array (only if the values from MOD_MENU are not arrays)
	 * @return array The array $settings, which holds a key for each MOD_MENU key and the values of each key will be within the range of values for each menuitem
	 */
	static public function getModuleData($MOD_MENU, $CHANGED_SETTINGS, $modName, $type = '', $dontValidateList = '', $setDefaultList = '') {
		$backendUtilityClass = static::getBackendUtilityClass();
		return $backendUtilityClass::getModuleData(
			$MOD_MENU, $CHANGED_SETTINGS, $modName, $type, $dontValidateList, $setDefaultList
		);
	}

	/**
	 * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle
	 * @see t3lib_BEfunc::getRecordTitle
	 *
	 * @param string $table Table name, present in TCA
	 * @param array $row Row from table
	 * @param boolean $prep If set, result is prepared for output: The output is cropped to a limited length (depending on BE_USER->uc['titleLen']) and if no value is found for the title, '<em>[No title]</em>' is returned (localized). Further, the output is htmlspecialchars()'ed
	 * @param boolean $forceResult If set, the function always returns an output. If no value is found for the title, '[No title]' is returned (localized).
	 * @return string
	 */
	static public function getRecordTitle($table, $row, $prep = FALSE, $forceResult = TRUE) {
		$backendUtilityClass = static::getBackendUtilityClass();
		return $backendUtilityClass::getRecordTitle($table, $row, $prep, $forceResult);
	}


	/**
	 * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig
	 * @see t3lib_BEfunc::getPagesTSconfig
	 *
	 * @param $id integer Page uid for which to create Page TSconfig
	 * @param $rootLine array If $rootLine is an array, that is used as rootline, otherwise rootline is just calculated
	 * @param boolean $returnPartArray If $returnPartArray is set, then the array with accumulated Page TSconfig is returned non-parsed. Otherwise the output will be parsed by the TypoScript parser.
	 * @return array Page TSconfig
	 */
	static public function getPagesTSconfig($id, $rootLine = NULL, $returnPartArray = FALSE) {
		$backendUtilityClass = static::getBackendUtilityClass();
		return $backendUtilityClass::getPagesTSconfig($id, $rootLine, $returnPartArray);
	}

	/**
	 * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu
	 * @see t3lib_BEfunc::getFuncMenu
	 *
	 * @param mixed $id The "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
	 * @param string $elementName The form elements name, probably something like "SET[...]
	 * @param string $currentValue The value to be selected currently.
	 * @param array	 $menuItems An array with the menu items for the selector box
	 * @param string $script The script to send the &id to, if empty it's automatically found
	 * @param string $addParams Additional parameters to pass to the script.
	 * @return string HTML code for selector box
	 */
	static public function getFuncMenu($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '') {
		$backendUtilityClass = static::getBackendUtilityClass();
		return $backendUtilityClass::getFuncMenu(
			$mainParams, $elementName, $currentValue, $menuItems, $script, $addparams
		);
	}

	/**
	 * @return \TYPO3\CMS\Backend\Utility\BackendUtility or t3lib_BEfunc
	 */
	static protected function getBackendUtilityClass() {
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$backendUtilityClass = '\TYPO3\CMS\Backend\Utility\BackendUtility';
		} else {
			$backendUtilityClass = 't3lib_BEfunc';
		}

		return $backendUtilityClass;
	}
}