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
	/**
	 * Selector String for the marker ###SELECTOR###
	 * @param $selectorString
	 */
	public function setSelector($selectorString);
	/**
	 * @param string $message
	 * @param string $title;
	 * @param int $severity Optional severity, must be either of t3lib_message_AbstractMessage::INFO, t3lib_message_AbstractMessage::OK,
	 *				  t3lib_message_AbstractMessage::WARNING or t3lib_message_AbstractMessage::ERROR. Default is t3lib_message_AbstractMessage::OK.
	 * 	const NOTICE  = -2;
	 * 	const INFO    = -1;
	 * 	const OK      = 0;
	 * 	const WARNING = 1;
	 * 	const ERROR   = 2;
	 * @param boolean $storeInSession Optional, defines whether the message should be stored in the session or only for one request (default)
	 */
	public function addMessage($message, $title = '', $severity = 0, $storeInSession = FALSE);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModule.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModule.php']);
}

