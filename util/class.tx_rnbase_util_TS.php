<?php

/***************************************************************
 *  Copyright notice
 *
*  (c) 2013 Rene Nitzsche (rene@system25.de)
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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');

/**
 * Contains utility functions for TypoScript
 *
 * @author Michael Wagner <mihcael.wagner@das-medienkombinat.de>
 */
class tx_rnbase_util_TS {

	/**
	 *
	 * @return \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser
	 */
	private static function getTsParser() {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return t3lib_div::makeInstance('TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser');
		else
			return t3lib_div::makeInstance('t3lib_tsparser');
	}

	/**
	 * Parse the configuration of the given models
	 *
	 * @param string $typoScript
	 */
	public static function parseTsConfig($typoScript) {
	    $parser = self::getTsParser();
	    $parser->parse($typoScript);
	    return $parser->setup;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TS.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TS.php']);
}
