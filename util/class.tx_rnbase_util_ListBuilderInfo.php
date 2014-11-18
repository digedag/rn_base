<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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
 * Provide additional data for ListBuilder
 *
 */
interface ListBuilderInfo {
	/**
	 * Get a message string for empty list
	 *
	 * @param array_object $viewData
	 * @param tx_rnbase_configurations $configurations
	 * @return string
	 */
	function getEmptyListMessage($confId, &$viewData, &$configurations);
	function getListMarkerInfo();

	function setMarkerArrays(&$markerArray, &$subpartArray, &$wrappedSubpartArray);
}
/**
 * Default implementation for ListBuilderInfo.
 */
class tx_rnbase_util_ListBuilderInfo implements ListBuilderInfo {

	/**
	 * Get a message string for empty list. This is an language string. The key is 
	 * taken from ts-config: [item].listinfo.llkeyEmpty
	 *
	 * @param array_object $viewData
	 * @param tx_rnbase_configurations $configurations
	 * @return string
	 */
	function getEmptyListMessage($confId, &$viewData, &$configurations) {
		return $configurations->getLL($configurations->get($confId.'listinfo.llkeyEmpty'));
	}
	function setMarkerArrays(&$markerArray, &$subpartArray, &$wrappedSubpartArray) {
		
	}
	function getListMarkerInfo() {
		return NULL;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilderInfo.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilderInfo.php']);
}
