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
	 * Returns the relative path to the extension as measured from the PATH_site (frontend)
	 * If the extension is not loaded the function will die with an error message
	 * Useful for images and links from the frontend
	 *
	 * @param string $key Extension key
	 * @return string
	 */
	public static function siteRelPath($key) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($key);
		return t3lib_extMgm::siteRelPath($key);
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

	/**
	 * Adds an entry to the "ds" array of the tt_content field "pi_flexform".
	 * This is used by plugins to add a flexform XML reference / content for use when they are selected as plugin or content element.
	 * Usage: 0
	 *
	 * @param	string		Plugin key as used in the list_type field. Use the asterisk * to match all list_type values.
	 * @param	string		Either a reference to a flex-form XML file (eg. "FILE:EXT:newloginbox/flexform_ds.xml") or the XML directly.
	 * @param	string		Value of tt_content.CType (Content Type) to match. The default is "list" which corresponds to the "Insert Plugin" content element.  Use the asterisk * to match all CType values.
	 * @return	void
	 * @see addPlugin()
	 */
	public static function addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch = 'list') {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch);
		return t3lib_extMgm::addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch);
	}
	/**
	 * Adds an entry to the list of plugins in content elements of type "Insert plugin"
	 * Takes the $itemArray (label, value[,icon]) and adds to the items-array of $GLOBALS['TCA'][tt_content] elements with CType "listtype" (or another field if $type points to another fieldname)
	 * If the value (array pos. 1) is already found in that items-array, the entry is substituted, otherwise the input array is added to the bottom.
	 * Use this function to add a frontend plugin to this list of plugin-types - or more generally use this function to add an entry to any selectorbox/radio-button set in the TCEFORMS
	 * FOR USE IN ext_tables.php FILES or files in Configuration/TCA/Overrides/*.php Use the latter to benefit from TCA caching!
	 *
	 * @param array $itemArray Numerical array: [0] => Plugin label, [1] => Underscored extension key, [2] => Path to plugin icon relative to TYPO3_mainDir
	 * @param string $type Type (eg. "list_type") - basically a field from "tt_content" table
	 * @param string $extensionKey The extension key
	 * @throws \RuntimeException
	 * @return void
	 */
	public static function addPlugin($itemArray, $type = 'list_type', $extensionKey = NULL) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin($itemArray, $type, $extensionKey);
		return t3lib_extMgm::addPlugin($itemArray, $type, $extensionKey);
	}

	/**
	 * Adds a module (main or sub) to the backend interface
	 * FOR USE IN ext_tables.php FILES
	 *
	 * @param string $main The main module key, $sub is the submodule key. So $main would be an index in the $TBE_MODULES array and $sub could be an element in the lists there.
	 * @param string $sub The submodule key. If $sub is not set a blank $main module is created.
	 * @param string $position Can be used to set the position of the $sub module within the list of existing submodules for the main module. $position has this syntax: [cmd]:[submodule-key]. cmd can be "after", "before" or "top" (or blank which is default). If "after"/"before" then submodule will be inserted after/before the existing submodule with [submodule-key] if found. If not found, the bottom of list. If "top" the module is inserted in the top of the submodule list.
	 * @param string $path The absolute path to the module. If this value is defined the path is added as an entry in $TBE_MODULES['_PATHS'][  main_sub  ] = $path; and thereby tells the backend where the newly added modules is found in the system. This option is deprecated as of TYPO3 CMS 7, and will have no effect in TYPO3 CMS 8 anymore.
	 * @param array $moduleConfiguration additional configuration, previously put in "conf.php" of the module directory
	 * @return void
	 */
	public static function addModule($main, $sub = '', $position = '', $path = '', $moduleConfiguration = array()) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule($main, $sub, $position, $path, $moduleConfiguration);
		return t3lib_extMgm::addModule($main, $sub, $position, $path, $moduleConfiguration);
	}

	/**
	 * Adds a "Function menu module" ('third level module') to an existing function menu for some other backend module
	 * The arguments values are generally determined by which function menu this is supposed to interact with
	 * See Inside TYPO3 for information on how to use this function.
	 * FOR USE IN ext_tables.php FILES
	 *
	 * @param string $modname Module name
	 * @param string $className Class name
	 * @param string $classPath Class path, deprecated since 6.2, use auto-loading instead
	 * @param string $title Title of module
	 * @param string $MM_key Menu array key - default is "function
	 * @param string $WS Workspace conditions. Blank means all workspaces, any other string can be a comma list of "online", "offline" and "custom
	 * @return void
	 * @see \TYPO3\CMS\Backend\Module\BaseScriptClass::mergeExternalItems()
	 */
	public static function insertModuleFunction($modname, $className, $classPath = null, $title, $MM_key = 'function', $WS = '') {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction($modname, $className, $classPath, $title, $MM_key, $WS);
		return t3lib_extMgm::insertModuleFunction($modname, $className, $classPath, $title, $MM_key, $WS);
	}

	/**
	 * Adds a reference to a locallang file with $GLOBALS['TCA_DESCR'] labels
	 * FOR USE IN ext_tables.php FILES
	 * eg. \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages', 'EXT:lang/locallang_csh_pages.xlf'); for the pages table or \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_MOD_web_layout', 'EXT:frontend/Resources/Private/Language/locallang_csh_weblayout.xlf'); for the Web > Page module.
	 *
	 * @param string $tca_descr_key Description key. Typically a database table (like "pages") but for applications can be other strings, but prefixed with "_MOD_")
	 * @param string $file_ref File reference to locallang file, eg. "EXT:lang/locallang_csh_pages.xlf" (or ".xml")
	 * @return void
	 */
	public static function addLLrefForTCAdescr($tca_descr_key, $file_ref) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr($tca_descr_key, $file_ref);
		return t3lib_extMgm::addLLrefForTCAdescr($tca_descr_key, $file_ref);
	}


	/**
	 * Adds a service to the global services array
	 *
	 * @param string $extKey Extension key
	 * @param string $serviceType Service type, must not be prefixed "tx_" or "Tx_
	 * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_
	 * @param array $info Service description array
	 * @return void
	 */
	public static function addService($extKey, $serviceType, $serviceKey, $info) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($extKey, $serviceType, $serviceKey, $info);
		return t3lib_extMgm::addService($extKey, $serviceType, $serviceKey, $info);
	}

	/**
	 * Adds $content to the default User TSconfig as set in $GLOBALS['TYPO3_CONF_VARS'][BE]['defaultUserTSconfig']
	 * Prefixed with a [GLOBAL] line
	 * FOR USE IN ext_tables.php/ext_localconf.php FILES
	 *
	 * @param string $content User TSconfig content
	 * @return void
	 */
	public static function addUserTSConfig($content) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig($content);
		return t3lib_extMgm::addUserTSConfig($content);
	}
	/**
	 * Adds $content to the default Page TSconfig as set in $GLOBALS['TYPO3_CONF_VARS'][BE]['defaultPageTSconfig']
	 * Prefixed with a [GLOBAL] line
	 * FOR USE IN ext_tables.php/ext_localconf.php FILES
	 *
	 * @param string $content Page TSconfig content
	 * @return void
	 */
	public static function addPageTSConfig($content) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($content);
		return t3lib_extMgm::addPageTSConfig($content);
	}

}
