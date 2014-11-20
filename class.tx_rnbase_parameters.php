<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
 *  Contact: rene@system25.de
 *
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
tx_rnbase::load('tx_rnbase_util_Arrays');

interface tx_rnbase_IParameters {
	/**
	 * Liefert den Parameter-Wert
	 *
	 * @param string $paramName
	 * @param string $qualifier
	 * @return mixed
	 */
	function get($paramName, $qualifier='');
	/**
	 * removes xss etc. from the value
	 *
	 * @param string $field
	 * @return string
	 */
	public function getCleaned($paramName, $qualifier = '');
	/**
	 * Liefert den Parameter-Wert als int
	 *
	 * @param string $paramName
	 * @param string $qualifier
	 * @return int
	 */
	function getInt($paramName, $qualifier='');
	/**
	 * Liefert alle Parameter-Werte
	 *
	 * @param string $qualifier
	 * @return array
	 */
	function getAll($qualifier='');
}


class tx_rnbase_parameters extends ArrayObject implements tx_rnbase_IParameters {
	private $qualifier='';

	/**
	 * Initialize this instance for a plugin
	 * @param string $qualifier
	 */
	public function init($qualifier) {
		$this->setQualifier($qualifier);
		// get parametersArray for defined qualifier
		$parametersArray = $this->getParametersPlain($qualifier);
		tx_rnbase_util_Arrays::overwriteArray($this, $parametersArray);
	}
	public function setQualifier($qualifier) {
		$this->qualifier = $qualifier;
	}
	public function getQualifier() {
		return $this->qualifier;
	}
	function get($paramName, $qualifier='') {
		if($qualifier) {
			$params = $this->getParametersPlain($qualifier);
			$value = array_key_exists($paramName, $params) ? $params[$paramName] : $params['NK_'.$paramName];
			return $value;
		}
		return $this->offsetExists($paramName)
			? $this->offsetGet($paramName)
			: $this->offsetGet('NK_'.$paramName);
	}

	/**
	 * removes xss from the value
	 *
	 * @param string $field
	 * @return string
	 */
	public function getCleaned($paramName, $qualifier = '') {
		$value = $this->get($paramName, $qualifier);
		// remove Cross-Site Scripting
		if (!empty($value) && strlen($value) > 3) {
			$value = t3lib_div::removeXSS($value);
		}
		return $value;
	}
	/**
	 * Liefert den Parameter-Wert als int
	 *
	 * @param string $paramName
	 * @param string $qualifier
	 * @return int
	 */
	function getInt($paramName, $qualifier='') {
		return intval($this->get($paramName, $qualifier));
	}
	private function getParametersPlain($qualifier) {
		$parametersArray = tx_rnbase_util_TYPO3::isTYPO43OrHigher() ?
				t3lib_div::_GPmerged($qualifier) :
				t3lib_div::GParrayMerged($qualifier);
		return $parametersArray;
	}
	function getAll($qualifier='') {
		$ret = array();
		$qualifier = $qualifier ? $qualifier : $this->getQualifier();
		$params = $this->getParametersPlain($qualifier);
		foreach($params As $key => $value) {
			$key = ($key{0} === 'N' && substr($key, 0, 3) === 'NK_') ? substr($key, 3) : $key;
			if(is_string($value))
				$ret[$key] = $value;
		}
		return $ret;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/class.tx_rnbase_parameters.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/class.tx_rnbase_parameters.php']);
}
