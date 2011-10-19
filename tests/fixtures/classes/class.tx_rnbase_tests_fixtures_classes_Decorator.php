<?php
/**
 * 	@package tx_rnbase
 *  @subpackage tx_rnbase_mod
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_mod_IDecorator');

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 * 
 * @package tx_rnbase
 * @subpackage tx_rnbase_mod1
 */
class tx_rnbase_tests_fixtures_classes_Decorator implements tx_rnbase_mod_IDecorator{
	
	/**
	 * 
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 */
	public function __construct(tx_rnbase_mod_IModule $mod) {
		$this->mod = $mod;
	}
	
	
	/**
	 * 
	 * @param 	string 					$value
	 * @param 	string 					$colName
	 * @param 	array 					$record
	 * @param 	tx_rnbase_model_base 	$item
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item) {
		$ret = $value;
		
		//wir manipulieren ein bisschen die daten um zu sehen ob der decorator ansprint
		if($colName == 'col1'){
			$ret = str_replace('col1', 'spalte1', $ret);
		}
		
		return $ret;
	}
}