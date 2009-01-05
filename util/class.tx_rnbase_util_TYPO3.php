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


require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');


/**
 * Statische Informationen über TYPO3
 */
class tx_rnbase_util_TYPO3 {

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
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TYPO3.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rnbase/util/class.tx_rnbase_util_TYPO3.php']);
}
?>