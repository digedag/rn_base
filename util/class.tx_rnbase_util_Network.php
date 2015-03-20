<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Rene Nitzsche
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
 * tx_rnbase_util_Network
 *
 * Wrapper for Network related functions
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_Network {

	/**
	 * (non-PHPdoc)
	 * @see t3lib_div::cmpIP()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP()
	 */
	public static function cmpIP($baseIP, $list) {
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			return TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP($baseIP, $list);
		}
		else {
			return t3lib_div::cmpIP($baseIP, $list);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Network.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Network.php']);
}