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

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
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
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TCA.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TCA.php']);
}