<?php
/**
 * Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche (rene@system25.de)
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
 */

/**
 * linker interface for mod tables.
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_rnbase_mod_linker_LinkerInterface {


	/**
	 * Link zur Detailseite erzeugen
	 *
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param int $currentPid
	 * @param tx_rnbase_model_data $options
	 * @return string
	 */
	public function makeLink($item, $formTool, $currentPid, $options);

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/linker/class.tx_rnbase_mod_LinkerInterface.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/linker/class.tx_rnbase_mod_LinkerInterface.php']);
}
