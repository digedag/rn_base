<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Rene Nitzsche (rene@system25.de)
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

/**
 * Class that adds the wizard icon.
 *
 * @author	René Nitzsche <rene[at]system25.de>
 */
abstract class tx_rnbase_util_Wizicon {
	/**
	 * 
	 * @param string $id
	 * @param string $clazz
	 */
	public static function addWizicon($id, $clazz) {
		$GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses'][$id] = $clazz;
	}
	/**
	 * Adds the T3sports report plugin wizard icon
	 *
	 * @param array Input array with wizard items for plugins
	 * @return array Modified input array, having the items for T3sports plugins added.
	 */
	function proc($wizardItems)	{
		global $LANG;

		$lang = $this->includeLocalLang();
		$plugins = $this->getPluginData();
		foreach($plugins As $id => $plugin) {
			$wizardItems['plugins_'.$id] = array(
				'icon'=>$plugin['icon'],
				'title'=>$lang->getLL($plugin['title']),
				'description'=>$lang->getLL($plugin['description']),
				'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]='.$id
			);
		}
		return $wizardItems;
	}
	protected abstract function getPluginData();
	protected abstract function getLLFile();

	/**
	 * @return tx_rnbase_util_Lang
	 */
	private function includeLocalLang()	{
		$llFile = $this->getLLFile();
		/* @var $lang tx_rnbase_util_Lang */
		$lang = tx_rnbase::makeInstance('tx_rnbase_util_Lang');
		$lang->loadLLFile($llFile);
		return $lang;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/class.tx_cfcleaguefe_util_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/class.tx_cfcleaguefe_util_wizicon.php']);
}
