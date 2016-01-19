<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('Tx_Rnbase_Domain_Model_DataInterface');

/**
 * Basic model with geter's and seter's
 *
 * @method integer getUid()
 * @method tx_rnbase_model_data setUid() setUid(integer $uid)
 * @method boolean hasUid()
 * @method tx_rnbase_model_data unsUid()
 *
 * @deprecated: IS NO LONGER BEING DEVELOPED!!!
 *              please use Tx_Rnbase_Domain_Model_Data
 *              THIS CLASS WILL BE DROPPED IN THE FUTURE!!!
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_model
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_model_data
	extends Tx_Rnbase_Domain_Model_Data
{
	/**
	 * holds the data!
	 *
	 * @var array
	 */
	public $record = array();

	public function tx_rnbase_model_data($record = NULL) {
		return $this->init($record);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_data.php']);
}
