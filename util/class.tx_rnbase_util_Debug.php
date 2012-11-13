<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Rene Nitzsche
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');

/**
 * Encapsulate debug functionality of TYPO3 for backward compatibility.
 */
class tx_rnbase_util_Debug {

	/**
	 * Makes debug output
	 * Prints $var in bold between two vertical lines
	 * If not $var the word 'debug' is printed
	 * If $var is an array, the array is printed by t3lib_div::print_array()
	 * Wrapper method for TYPO3 debug methods 
	 * 
	 * @param	mixed		Variable to print
	 * @param	string		The header.
	 * @param	string		Group for the debug console
	 * @return	void
	 */
	public static function debug($var = '', $header = '', $group = 'Debug') {
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher()) {
			return t3lib_utility_Debug::debug($var, $header, $group);
		}
		else {
			return t3lib_div::debug($var, $header, $group);
		}
	}
	/**
	 * Returns HTML-code, which is a visual representation of a multidimensional array
	 * use t3lib_div::print_array() in order to print an array
	 * Returns false if $array_in is not an array
	 *
	 * @param	mixed		Array to view
	 * @return	string		HTML output
	 */
	public static function viewArray($array_in) {
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher()) {
			return t3lib_utility_Debug::viewArray($array_in);
		}
		else {
			return t3lib_div::view_array($array_in);
		}
	}
	
	/**
	 * @return string
	 */
	public static function getDebugTrail() {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher()) {
			return t3lib_utility_Debug::debugTrail(); 
		} elseif (is_callable(array('t3lib_div', 'debug_trail'))) {
			return t3lib_div::debug_trail();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Debug.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Debug.php']);
}

?>