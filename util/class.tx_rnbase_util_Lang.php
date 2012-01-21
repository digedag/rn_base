<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Rene Nitzsche (rene@system25.de)
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
 * Wrapper for language usage.
 */
class tx_rnbase_util_Lang {
	protected static $LOCAL_LANG = array();
	protected static $LOCAL_LANG_charset = array();

	public static function loadLL($filename, $configurations = false) {
		if(!$filename)	return;
		if(tx_rnbase_util_TYPO3::isTYPO46OrHigher())
			self::loadLL46($filename, $configurations);
		else
			self::loadLL40($filename, $configurations);
	}
	protected static function getLLKey($alt = false) {
		$ret = $GLOBALS['TSFE']->config['config'][$alt ? 'language_alt' : 'language'];
		return $ret ? $ret : ($alt ? '' : 'default');
	}
	protected function addLang($langArr) {
		self::$LOCAL_LANG = array_merge(is_array(self::$LOCAL_LANG) ? self::$LOCAL_LANG : array(), $langArr);
	}
	/**
	 * Loads local language file for frontend rendering if defined in configuration.
	 * Also locallang values from TypoScript property "_LOCAL_LANG" are merged onto the
	 * values. This is a reimplementation from tslib_pibase::pi_loadLL()
	 */
	protected static function loadLL40($filename, $configurations = false) {
		// Load language to use

		self::loadLLFile($filename);

		if(!is_object($configurations))
			return;
		// Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
		$langArr = $configurations->get('_LOCAL_LANG.');
		if (!is_array($langArr))
			return;
		while(list($k,$lA)=each($langArr)) {
			if (is_array($lA)) {
				$k = substr($k,0,-1);
				foreach($lA as $llK => $llV) {
					if (!is_array($llV)) {
						self::$LOCAL_LANG[$k][$llK] = $llV;
						if ($k != 'default') {
							self::$LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];        // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
						}
					}
				}
			}
		}
	}

	/**
	 * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($this->scriptRelPath) and if found includes it.
	 * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.php" file.
	 *
	 * @return	void
	 */
	protected static function loadLL46($filename, $configurations = false) {

		// Find language file
		self::loadLLFile($filename);
		if(!is_object($configurations))
			return;

		// Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
		$confLL = $configurations->get('_LOCAL_LANG.');
		if (!is_array($confLL)) {
			return;
		}
		foreach ($confLL as $languageKey => $languageArray) {
			// Don't process label if the langue is not loaded
			$languageKey = substr($languageKey,0,-1);
			if (is_array($languageArray) && is_array(self::$LOCAL_LANG[$languageKey])) {
				// Remove the dot after the language key
				foreach ($languageArray as $labelKey => $labelValue) {
					if (!is_array($labelValue))	{
						self::$LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;
						// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset"
						// and if that is not set, assumed to be that of the individual system languages
						if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
							self::$LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
						} else {
							self::$LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TSFE']->csConvObj->charSetArray[$languageKey];
						}
					}
				}
			}
		}
	}
	protected static function loadLLFile($filename) {
		// Find language file
		$basePath = t3lib_div::getFileAbsFileName($filename);
		// php or xml as source: In any case the charset will be that of the system language.
		// However, this function guarantees only return output for default language plus the specified language (which is different from how 3.7.0 dealt with it)
		self::addLang(t3lib_div::readLLfile($basePath, self::getLLKey(), $GLOBALS['TSFE']->renderCharset));
		if ($llKey = self::getLLKey(true)) {
			self::addLang(t3lib_div::readLLfile($basePath, $llKey, $GLOBALS['TSFE']->renderCharset));
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Lang.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Lang.php']);
}
