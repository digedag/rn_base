<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2013 Rene Nitzsche
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
 * Contains utility functions for HTML-Forms
 */
class tx_rnbase_util_FormUtil {

	/**
	 * Creates a HTML-Select.
	 */
	public static function createSelect($name, $arr, $attr='class="inputField"') {
		$out = '<select name="' . $name . '" ' . $attr . '>';
		$value = $arr[1];
		// Die Options ermitteln
		foreach($arr[0] As $key => $val){
			$sel = '';
			if (strval($value) == strval($key)) $sel = 'selected="selected"';
			$out .= '<option value="' . $key . '" ' . $sel . '>' . $val . '</option>';
		}

		$out .= '</select>';

		return $out;
	}
	/**
	 * Returns Array as Hiddenfields
	 */
	public static function getAsHiddenFields($arr, $qualifier = '') {
		$out = '';
		foreach($arr As $key => $value) {
			$key = strlen($qualifier) > 0 ? $qualifier.'['.$key.']' : $key;
			$out .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
		}
		return $out;
	}

	/**
	 * Erzeugt anhand von einer URL hidden Felder, welche mit übergeben werden.
	 * Dabei werden die Get-Parameter aus der Action URL entfernt.
	 * Das ist wichtig, wenn das Formular mit GET abgeschickt wird
	 * und die Action URL bereits GET Parameter enthält.
	 * Die in der URL enthaltenen Parameter gehen verloren!
	 *
	 * @author Michael Wagner
	 * @param string $url
	 * @return string
	 */
	public static function getHiddenFieldsForUrlParams($url) {
		$sysHidden = '';
		$params = array();

		if (strpos($url, '?') !== FALSE) {
			$params = substr($url, strpos($url, '?') + 1);
			$params = t3lib_div::explodeUrl2Array($params);
			$url = substr($url, 0, strpos($url, '?'));
		}
		foreach ($params as $name => $value) {
			$name = t3lib_div::removeXSS($name);
			$value = t3lib_div::removeXSS($value);
			$sysHidden .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
		}
		return $sysHidden;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormUtil.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormUtil.php']);
}
