<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2016 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_model_data');
tx_rnbase::load('tx_rnbase_mod_Util');

/**
 *
 * @author rene
 * @deprecated use Tx_Rnbase_Backend_Utility_Tables
 */
class tx_rnbase_mod_Tables {

	/**
	 *
	 * @param array $entries
	 * @param array $columns
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param Tx_Rnbase_Domain_Model_DataInterface $options
	 * @return array 0 are data and 1 layout
	 * @deprecated use
	 */
	public static function prepareTable($entries, $columns, $formTool, $options) {
		$tableUtil = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
		return $tableUtil->prepareTable($entries, $columns, $formTool, $options);
	}





	/**
	 * Returns a table based on the input $data
	 * This method is taken from TYPO3 core. It will be removed there for version 8.
	 *
	 * Typical call until now:
	 * $content .= tx_rnbase_mod_Tables::buildTable($data, $module->getTableLayout());
	 * Should we include a better default layout here??
	 *
	 * @param array $data Multidim array with first levels = rows, second levels = cells
	 * @param array $layout If set, then this provides an alternative layout array instead of $this->tableLayout
	 * @return string The HTML table.
	 */
	public static function buildTable($data, $layout = null) {
		$tableUtil = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
		return $tableUtil->buildTable($data, $layout);
	}

	/**
	 * Returns a default table layout
	 * @return array
	 */
	public static function getTableLayout() {
		$tableUtil = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
		return $tableUtil->getTableLayout();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php']);
}
