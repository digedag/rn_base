<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_IListProvider');

/**
 * Provide data for ListBuilder
 */
class tx_rnbase_util_ListProvider implements tx_rnbase_util_IListProvider {
	public function initBySearch($searchCallback, $fields, $options) {
		$this->mode = 1;
		$this->searchCallback = $searchCallback;
		$this->fields = $fields;
		$this->options = $options;
	}
	/**
	 * Starts iteration over all items. The callback method is called for each single item.
	 * @param array $callback
	 */
	public function iterateAll($itemCallback) {
		switch($this->mode) {
			case 1:
				$this->options['callback'] = $itemCallback;
				call_user_func($this->searchCallback, $this->fields, $this->options);
				break;
			default:
				throw new Exception('Undefined list mode.');
				break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListProvider.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListProvider.php']);
}
