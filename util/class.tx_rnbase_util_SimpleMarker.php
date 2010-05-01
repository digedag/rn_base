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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.rn_base.php');

rn_base::load('tx_rnbase_util_BaseMarker');

/**
 * A generic marker class. 
 */
class tx_rnbase_util_SimpleMarker extends tx_rnbase_util_BaseMarker {

	/**
	 * @param string $template das HTML-Template
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config
	 * @param string $marker Name des Markers
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$item, &$formatter, $confId, $marker) {
		if(!is_object($item)) {
			if($this->classname) return '';
			// Ist kein Item vorhanden wird ein leeres Objekt verwendet.
			$item = self::getEmptyInstance($this->classname);
		}

		// Es wird das MarkerArray mit den Daten des Teams gefüllt.
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , 0, $marker.'_',$item->getColumnNames());
		$wrappedSubpartArray = array();
		$subpartArray = array();

		$out = self::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * Set classname for items
	 * @param $name
	 */
	public function setClassname($name) {
		$this->classname = $name;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SimpleMarker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SimpleMarker.php']);
}
?>