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

	/**
	 * Checks, if the debug output is anabled.
	 * the given key has to match with the key from the extconf.
	 *
	 * @return boolean
	 */
	public static function isDebugEnabled($key = NULL) {
		static $debugKey = NULL;
		if ($debugKey === NULL) {
			tx_rnbase::load('tx_rnbase_configurations');
			$debugKey = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'debugKey');
		}
		if (empty($debugKey)) {
			return FALSE;
		}
		if ($key === NULL) {
			$key = $_GET['debug'];
		}
		return $debugKey === $key;
	}

	/**
	 * Prüft, ob per Parameter oder Konfiguration der Debug für die Labels aktiv ist.
	 *
	 * @param tx_rnbase_configurations $configurations
	 * @return boolean or string with debug type (plain, html)
	 */
	public static function isLabelDebugEnabled(
		tx_rnbase_configurations $configurations = NULL
	) {
		static $status = array();
		// check global debug params
		if (!isset($status['global'])) {
			$status['global'] = !empty($_GET['labeldebug']) && self::isDebugEnabled()
				? $_GET['labeldebug'] : self::isDebugEnabled();
		}
		if ($status['global']) {
			return $status['global'];
		}
		// check plugin debug config
		if ($configurations instanceof tx_rnbase_configurations) {
			$pluginId = $configurations->getPluginId();
			if (!isset($status[$pluginId])) {
				$status[$pluginId] = $configurations->get('labeldebug');
			}
			return empty($status[$pluginId]) ? FALSE : $status[$pluginId];
		}
		// no debug!
		return FALSE;
	}

	/**
	 *
	 * @param string $text
	 * @param string $debug
	 */
	public static function wrapDebugInfo($text, $debug, array $options = array()) {
		if (!empty($options['plain'])) {
			return $text . ' [' . $debug . ']';
		}
		self::addDebugInfoHeaderData();
		$out  = '<span class="rnbase-debug-text">';
		$out .= 	'<span class="rnbase-debug-info">';
		$out .= 		is_scalar($debug) ? $debug : var_export($debug, true);
		$out .= 	'</span> ';
		$out .= 	$text;
		$out .= '</span> ';
		return $out;
	}
	/**
	 * Adds the CSS for the hidden debug info wrap for self::wrapDebugInfo
	 */
	private static function addDebugInfoHeaderData() {
		static $added = false;
		if ($added) {
			return;
		}
		$added = true;
		// javascript für das autocpmplete
		$code  = '';
		$code .= '
			.rnbase-debug-text {
				border: 1px solid red;
				padding: 3px 4px;
				position: relative;
			}
			.rnbase-debug-info {
				display: none;
				background: #fff;
				border: 1px solid red;
				font-size: 10px;
				line-height: 12px;
				color: red;
				padding: 2px 4px;
				position: absolute;
				left: -1px;
				top: -18px;
			}
			.rnbase-debug-text:hover > .rnbase-debug-info {
				display: block;
			}
		';
		if (TYPO3_MODE === 'BE') {
			// @TODO: this is too late, for the most cases!
			$GLOBALS['TBE_STYLES']['inDocStyles_TBEstyle'] .= $code;
		} else {
			$code = '<style type="text/css">' . $code . '</style>';
			$GLOBALS['TSFE']->additionalHeaderData['rnbase-debug-info'] = $code;
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Debug.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Debug.php']);
}

