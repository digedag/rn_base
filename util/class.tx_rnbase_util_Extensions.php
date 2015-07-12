<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Wrapperclass for TYPO3 Extension Manager
 * @author René Nitzsche
 *
 */
class tx_rnbase_util_Extensions {

	/**
	 * Wrapper for t3lib_extMgm::addStaticFile
	 *
	 * Call this method to add an entry in the static template list found in sys_templates
	 * "static template files" are the modern equalent (provided from extensions) to the traditional records in "static_templates"
	 * FOR USE IN ext_localconf.php FILES
	 * Usage: 3
	 *
	 * @param	string		$extKey is of course the extension key
	 * @param	string		$path is the path where the template files (fixed names) include_static.txt (integer list of uids from the table "static_templates"), constants.txt, setup.txt, editorcfg.txt, and include_static_file.txt is found (relative to extPath, eg. 'static/'). The file include_static_file.txt, allows you to include other static templates defined in files, from your static template, and thus corresponds to the field 'include_static_file' in the sys_template table. The syntax for this is a commaseperated list of static templates to include, like:  EXT:css_styled_content/static/,EXT:da_newsletter_subscription/static/,EXT:cc_random_image/pi2/static/
	 * @param	string		$title is the title in the selector box.
	 * @return	void
	 * @see addTypoScript()
	 */
	public static function addStaticFile($extKey, $path, $title)	{
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, $path, $title);
		return t3lib_extMgm::addStaticFile($extKey, $path, $title);
	}
	/**
	 * Wrapper for t3lib_extMgm::extPath
	 *
	 * Returns the absolute path to the extension with extension key $key
	 *
	 * @param	string		Extension key
	 * @param	string		$script is appended to the output if set.
	 * @return string
	 */
	public static function extPath($key, $script = '') {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($key, $script);
		return t3lib_extMgm::extPath($key, $script);
	}

	/**
	 * Returns the relative path to the extension as measured from from the TYPO3_mainDir
	 * If the extension is not loaded the function will die with an error message
	 * Useful for images and links from backend
	 *
	 * @param	string		Extension key
	 * @return	string
	 */
	public static function extRelPath($key) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($key);
		return t3lib_extMgm::extRelPath($key);
	}

	/**
	 * Returns TRUE if the extension with extension key $key is loaded.
	 *
	 * @param string $key Extension key to test
	 * @param boolean $exitOnError If $exitOnError is TRUE and the extension is not loaded the function will die with an error message
	 * @return boolean
	 * @throws \BadFunctionCallException
	 */
	public static function isLoaded($key, $exitOnError = FALSE) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($key, $exitOnError);
		return t3lib_extMgm::isLoaded($key, $exitOnError);
	}

	/**
	 * Adding fields to an existing table definition in $GLOBALS['TCA']
	 * Adds an array with $GLOBALS['TCA'] column-configuration to the $GLOBALS['TCA']-entry for that table.
	 * This function adds the configuration needed for rendering of the field in TCEFORMS - but it does NOT add the field names to the types lists!
	 * So to have the fields displayed you must also call fx. addToAllTCAtypes or manually add the fields to the types list.
	 * FOR USE IN ext_tables.php FILES or files in Configuration/TCA/Overrides/*.php Use the latter to benefit from TCA caching!
	 *
	 * @param string $table The table name of a table already present in $GLOBALS['TCA'] with a columns section
	 * @param array $columnArray The array with the additional columns (typical some fields an extension wants to add)
	 * @param boolean $addTofeInterface DEPRECATED: Usage of feInterface is no longer part of the TYPO3 CMS Core. Please check EXT:statictemplates.
	 * @return void
	 */
	public static function addTCAcolumns($table, $columnArray, $addTofeInterface = FALSE) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $columnArray, $addTofeInterface);
		return t3lib_extMgm::addTCAcolumns($table, $columnArray, $addTofeInterface);
	}
	/**
	 * Makes fields visible in the TCEforms, adding them to the end of (all) "types"-configurations
	 *
	 * Adds a string $string (comma separated list of field names) to all ["types"][xxx]["showitem"] entries for table $table (unless limited by $typeList)
	 * This is needed to have new fields shown automatically in the TCEFORMS of a record from $table.
	 * Typically this function is called after having added new columns (database fields) with the addTCAcolumns function
	 * FOR USE IN ext_tables.php FILES or files in Configuration/TCA/Overrides/*.php Use the latter to benefit from TCA caching!
	 *
	 * @param string $table Table name
	 * @param string $newFieldsString Field list to add.
	 * @param string $typeList List of specific types to add the field list to. (If empty, all type entries are affected)
	 * @param string $position Insert fields before (default) or after one, or replace a field
	 * @return void
	 */
	public static function addToAllTCAtypes($table, $newFieldsString, $typeList = '', $position = '') {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes($table, $newFieldsString, $typeList, $position);
		return t3lib_extMgm::addToAllTCAtypes($table, $newFieldsString, $typeList, $position);
	}
}
