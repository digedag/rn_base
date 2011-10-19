<?php
/**
 * 	@package tx_rnbase
 *  @subpackage tx_rnbase_mod
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 */

tx_rnbase::load('tx_rnbase_mod_BaseModule');

/**
 * Backend Modul für rnbase
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_mod1
 */
class tx_rnbase_tests_fixtures_classes_Mod extends tx_rnbase_mod_BaseModule {
	var $pageinfo;
    var $tabs;

    /**
     * Method to get the extension key
     *
     * @return	string Extension key
     */
	function getExtensionKey() {
		return 'rnbase';
	}
	
	/**
	 * Method to set the tabs for the mainmenu
	 * Umstellung von SelectBox auf Menu
	 */
	protected function getFuncMenu() {
		$mainmenu = $this->getFormTool()->showTabMenu($this->getPid(), 'function', $this->getName(), $this->MOD_MENU['function']);
		return $mainmenu['menu'];
	}
	
	/**
	 * Returns the module ident name
	 * @return string
	 */
	public function getName() {
		return 'dummyMod';
	}

}