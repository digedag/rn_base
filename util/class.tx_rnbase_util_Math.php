<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Rene Nitzsche (rene@system25.de)
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
 * Wrapper for math usage.
 */
class tx_rnbase_util_Math {
	/**
	 * Tests if the input can be interpreted as integer.
	 * @return boolean
	 */
	public static function testInt($var) {
		if(tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
			return t3lib_utility_Math::canBeInterpretedAsInteger($var);
		}
		else {
			return t3lib_div::testInt($var);
		}
	}
	/**
	 * Forces the integer $theInt into the boundaries of $min and $max. If the $theInt is 'FALSE' then the $zeroValue is applied.
	 *
	 * @param integer $theInt Input value
	 * @param integer $min Lower limit
	 * @param integer $max Higher limit
	 * @param integer $zeroValue Default value if input is FALSE.
	 * @return integer The input value forced into the boundaries of $min and $max
	 * @deprecated since TYPO3 4.6, will be removed in TYPO3 4.8 - Use t3lib_utility_Math::forceIntegerInRange() instead
	 */
	public static function intInRange($theInt, $min, $max = 2000000000, $zeroValue = 0) {
		if(tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
			return t3lib_utility_Math::forceIntegerInRange($theInt, $min, $max, $zeroValue);
		}
		else {
			return t3lib_div::intInRange($theInt, $min, $max, $zeroValue);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Math.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Math.php']);
}
