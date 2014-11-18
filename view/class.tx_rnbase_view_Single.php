<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_view_List');


/**
 * Default view class to show a single record.
 */
class tx_rnbase_view_Single extends tx_rnbase_view_List {
	/**
	 * Erstellen des Frontend-Outputs
	 */
	function createOutput($template, &$viewData, &$configurations, &$formatter){

		$confId = $this->getController()->getConfId();
		// $itemKey = 
		// Die ViewData bereitstellen
		$item = $viewData->offsetGet('item');
		$itemPath = $this->getItemPath($configurations, $confId);
		$markerClass = $this->getMarkerClass($configurations, $confId);

		$marker = tx_rnbase::makeInstance($markerClass);

		$out = $marker->parseTemplate($template, $item, $formatter, $confId.$itemPath.'.', strtoupper($itemPath));
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_Single.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_Single.php']);
}
