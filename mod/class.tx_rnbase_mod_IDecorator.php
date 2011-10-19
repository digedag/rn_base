<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Hannes Bochmann (hannes.bochmann@das-medienkombinat.de)
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
 * Base interface for a decorator
 */
interface tx_rnbase_mod_IDecorator {
	
	/**
	 * Constructor
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 */
	public function __construct(tx_rnbase_mod_IModule $mod);
	
	/**
	 * Formatiert jede Zelle einer Tabelle im BE. Ausgenommen sind die Header
	 * 
	 * @param string $value der aktuelle Wert
	 * @param string $colName der Name der aktuellen Spalte
	 * @param array $record der gesamte aktuelle record
	 * @param tx_rnbase_model_base $item das gesamte aktuelle model
	 * @return 	string html code to visualize the current value
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IDecorator.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IDecorator.php']);
}

