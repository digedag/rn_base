<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Rene Nitzsche
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
 * Replacement class for former FormEngine-class.
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			René Nitzsche <rene@system25.de>
 */

class Tx_Rnbase_Backend_Form_FormBuilder {

	public function initDefaultBEmode() {

	}

	public function getSoloField($table, $row, $theFieldToReturn) {
		return $theFieldToReturn;
	}
}
