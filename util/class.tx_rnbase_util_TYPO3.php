<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2014 Rene Nitzsche (rene@system25.de)
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
	 * Prüft, ob mindestens TYPO3 Version 6.0 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO60OrHigher() {
		return self::isTYPO3VersionOrHigher(6000000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 6.1 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO61OrHigher() {
		return self::isTYPO3VersionOrHigher(6001000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 6.1 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO62OrHigher() {
		return self::isTYPO3VersionOrHigher(6002000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 4.7 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO47OrHigher() {
		return self::isTYPO3VersionOrHigher(4007000);
	}
	/**
	 * Prüft, ob mindestens TYPO3 Version 4.6 vorhanden ist.
	 *
	 * @return boolean
	 */
	public static function isTYPO46OrHigher() {
		return self::isTYPO3VersionOrHigher(4006000);
	}
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
	private static $TYPO3_VERSION = FALSE;
	/**
	 * Prüft, ob eine bestimmte TYPO3 Version vorhanden ist.
	 *
	 * @param int $versionNumber
	 * @return boolean
	 */
	public static function isTYPO3VersionOrHigher($version) {
		if(self::$TYPO3_VERSION === FALSE) {
			self::$TYPO3_VERSION = self::convertVersionNumberToInteger(TYPO3_version);
		}
		return self::$TYPO3_VERSION >= $version;
	}
	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 * This method is taken from t3lib_utility_VersionNumber.
	 *
	 * @param $versionNumber string Version number on format x.x.x
	 * @return integer Integer version of version number (where each part can count to 999)
	 */
	public static function convertVersionNumberToInteger($versionNumber) {
		$versionParts = explode('.', $versionNumber);
		return intval((int) $versionParts[0] . str_pad((int) $versionParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int) $versionParts[2], 3, '0', STR_PAD_LEFT));
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
	 * Wrapper function for t3lib_extMgm::isLoaded()
	 * @param string $_EXTKEY
	 */
	public static function isExtLoaded($_EXTKEY) {
		return t3lib_extMgm::isLoaded($_EXTKEY);
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
	 * Prüft, ob die Extension mindestens auf einer bestimmten Version steht
	 * @param string $_EXTKEY
	 * @param int $version
	 * @return boolean
	 */
	public static function isExtMinVersion($_EXTKEY, $version) {
		if(!self::isExtLoaded($_EXTKEY))
			return false;
		return intval($version) <= self::convertVersionNumberToInteger(self::getExtVersion($_EXTKEY));
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
	 * Get the current frontend user uid
	 *
	 * @return int current frontend user uid or FALSE
	 */
	public static function getFEUserUID() {
		$feuser = self::getFEUser();
		return is_object($feuser) && isset($feuser->user['uid']) ? $feuser->user['uid'] : FALSE;
	}
	/**
	 * Get the current backend user if available
	 * @return t3lib_tsfeBeUserAuth
	 */
	public static function getBEUser() {
		return $GLOBALS['BE_USER'];
	}
	/**
	 * Get the current backend user uid if available
	 * @return int
	 */
	public static function getBEUserUID() {
		$beuser = self::getBEUser();
		return is_object($beuser) && isset($beuser->user['uid']) ? $beuser->user['uid'] : FALSE;
	}


	/**
	 * Returns TSFE.
	 * @param boolean $force create new tsfe if not available
	 * @return tslib_fe
	 */
	public static function getTSFE($force=FALSE) {
		if(!is_object($GLOBALS['TSFE'])) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::prepareTSFE();
		}
		return $GLOBALS['TSFE'];
	}
	private static $sysPage = NULL;
	/**
	 * @return t3lib_pageSelect
	 */
	public static function getSysPage() {
		if (!is_object(self::$sysPage)) {
			if(is_object($GLOBALS['TSFE']->sys_page))
				self::$sysPage = $GLOBALS['TSFE']->sys_page; // Use existing SysPage from TSFE
			else {
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
