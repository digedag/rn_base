<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2015 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * TCA Util and wrapper methods
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_TcaTool {

	/**
	 * Add a wizard to column.
	 * Usage:
	 *
	 * tx_rnbase::load('Tx_Rnbase_Util_TCA');
	 * $tca = new Tx_Rnbase_Util_TCA();
	 * $tca->addWizard($tcaTableArray, 'teams', 'add', 'wizard_add', array());
	 *
	 * @param array &$tcaTable
	 * @param string $colName
	 * @param string $wizardName
	 * @param string $moduleName
	 * @param array $urlParams
	 * @return void
	 */
	public function addWizard(&$tcaTable, $colName, $wizardName, $moduleName, $urlParams = array()) {
		if(\tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$tcaTable['columns'][$colName]['config']['wizards'][$wizardName]['module'] = array(
					'name' => $moduleName,
					'urlParameters' => $urlParams
			);
		}
		else {
			$tcaTable['columns'][$colName]['config']['wizards'][$wizardName]['script'] =
					$moduleName . '.php?' . http_build_query($urlParams);
		}
	}
}

/**
 * the old class for backwards compatibility
 *
 * @deprecated: will be dropped in the feature!
 */
class Tx_Rnbase_Util_TCATool
	extends Tx_Rnbase_Utility_TcaTool {
	/**
	 * constructor to log deprecation!
	 *
	 * @return void
	 */
	function __construct() {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		$utility::deprecationLog(
			'Usage of "Tx_Rnbase_Util_TCATool" is deprecated' .
			'Please use "Tx_Rnbase_Utility_TcaTool" instead!'
		);
	}
}
