<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Generic search class. All parameters will be configured.
 * 
 * @author Rene Nitzsche
 */
class tx_rnbase_util_SearchGeneric extends tx_rnbase_util_SearchBase {

	protected function getTableMappings() {
		$tableMapping = array();
		// Hook to append other tables
		tx_rnbase_util_Misc::callHook('rn_base', 'search_generic_getTableMapping_hook',
			array('tableMapping' => &$tableMapping), $this);
		return $tableMapping;
	}
  protected function getBaseTable() {
  	return '';
  }
  function getWrapperClass() {
  	return '';
  }
	
	protected function getJoins($tableAliases) {
		$join = '';

		// Hook to append other tables
		tx_rnbase_util_Misc::callHook('rn_base', 'search_generic_getJoins_hook',
			array('join' => &$join, 'tableAliases' => $tableAliases), $this);
		return $join;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SearchGeneric.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SearchGeneric.php']);
}
