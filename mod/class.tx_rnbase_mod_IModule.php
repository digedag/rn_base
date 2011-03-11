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

/**
 */
interface tx_rnbase_mod_IModule {
	public function getDoc();
	/**
	 * Returns the form tool
	 * @return tx_rnbase_util_FormTool
	 */
	public function getFormTool();
	/**
	 * Returns the configuration
	 * @return tx_rnbase_configurations
	 */
	public function getConfigurations();
	/**
	 * Returns the module ident name
	 * @return string
	 */
	public function getName();
	/**
	 * Return current PID for Web-Modules
	 * @return uid
	 */
	public function getPid();
	/**
	 * Submenu String for the marker ###TABS###
	 * @param $menuString
	 */
	public function setSubMenu($menuString);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModule.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModule.php']);
}

?>