<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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
 * Statische Informationen über TYPO3
 */
class tx_rnbase_util_TYPO3 {

	/**
	 * Prüft, ob mindestens TYPO3 Version 4.5 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO45OrHigher() {
		return self::isTYPO3VersionOrHigher(4005000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 4.4 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO44OrHigher() {
		return self::isTYPO3VersionOrHigher(4004000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 4.3 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO43OrHigher() {
		return self::isTYPO3VersionOrHigher(4003000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 4.2 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO42OrHigher() {
		return self::isTYPO3VersionOrHigher(4002000);
	}
	/**
	 * Prüft, ob eine bestimmte TYPO3 Version vorhanden ist.
	 *
	 * @param int $version
	 * @return boolean
	 */
	public static function isTYPO3VersionOrHigher($version) {
		return t3lib_div::int_from_ver(TYPO3_version) >= $version;
	}
	/**
	 * Liefert das EM_CONF-Array einer Extension
	 *
	 * @param string $extKey
	 * @return array
	 */
	public static function loadExtInfo($_EXTKEY) {
		$path = t3lib_extMgm::extPath($_EXTKEY).'ext_emconf.php';
		@include($path);
		if(is_array($EM_CONF[$_EXTKEY])) {
			return $EM_CONF[$_EXTKEY];
		}
		return array();
	}
	/**
	 * Liefert die Versionsnummer einer Extension
	 *
	 * @param string $extKey
	 * @return string
	 */
	public static function getExtVersion($extKey) {
		$info = self::loadExtInfo($extKey);
		return $info['version'];
	}

	/** 
	 * Get the current frontend user
	 *
	 * @return tslib_feUserAuth current frontend user.
	 */ 
	public static function getFEUser() {
		return $GLOBALS['TSFE']->fe_user;
	}
	/**
	 * Get the current backend user if available
	 * @return t3lib_tsfeBeUserAuth
	 */
	public static function getBEUser() {
		return $GLOBALS['BE_USER'];
	}
	/**
	 * @return tslib_fe
	 */
	public static function getTSFE() {
		return $GLOBALS['TSFE'];
	}
	private static $sysPage = null;
	/**
	 * @return t3lib_pageSelect
	 */
	public static function getSysPage() {
		if (!is_object(self::$sysPage)) {
			if(is_object($GLOBALS['TSFE']->sys_page)) 
				self::$sysPage = $GLOBALS['TSFE']->sys_page; // Use existing SysPage from TSFE
			else {
				require_once(PATH_t3lib.'class.t3lib_page.php');
				self::$sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
				self::$sysPage->init(0); // $this->showHiddenPage
			}
		}
		return self::$sysPage;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TYPO3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TYPO3.php']);
}
?>