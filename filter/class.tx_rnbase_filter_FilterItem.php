<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_SearchBase');


interface tx_rnbase_IFilterItem {
	/**
	 * Returns the name of item
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the current value
	 * @return string
	 */
	public function getValue();
}

class tx_rnbase_filter_FilterItem implements tx_rnbase_IFilterItem {
	var $record;
	function tx_rnbase_filter_FilterItem($name, $value) {
		$this->record = array();
		$this->setName($name);
		$this->setValue($value);
	}
	/**
	 * Returns the name of item
	 * @return string
	 */
	public function getName() {
		return $this->record['name'];
	}
	public function setName($name) {
		$this->record['name'] = $name;
	}
	
	/**
	 * Returns the current value
	 * @return string
	 */
	public function getValue() {
		return $this->record['value'];
	}
	public function setValue($value) {
		$this->record['value'] = $value;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_FilterItem.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_FilterItem.php']);
}
