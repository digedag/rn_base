<?php
/**
 * 	@package tx_rnbase
 *  @subpackage tx_rnbase_view
 *
 *  Copyright notice
 *
 *  (c) 2011 René Nitzsche <rene@system25.de>
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
tx_rnbase::load('tx_rnbase_view_Base');

/**
 * Generic list view
 * @package tx_rnbase
 * @subpackage tx_rnbase_view
 * @author René Nitzsche
 */
class tx_rnbase_view_List extends tx_rnbase_view_Base {

  /**
   * Do the output rendering.
   *
   * As this is a generic view which can be called by
   * many different actions we need the actionConfId in
   * $viewData in order to read its special configuration,
   * including redirection options etc.
   *
   * @param string $template
   * @param ArrayObject	$viewData
   * @param tx_rnbase_configurations	$configurations
   * @param tx_rnbase_util_FormatUtil	$formatter
   * @return mixed Ready rendered output or HTTP redirect
   */
	public function createOutput($template, &$viewData, &$configurations, &$formatter) {
		//View-Daten abholen
		$items =& $viewData->offsetGet('items');
		$confId = $this->getController()->getConfId();

		$itemPath = $this->getItemPath($configurations, $confId);
		$markerClass = $this->getMarkerClass($configurations, $confId);
		
		//Liste generieren
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$out = $listBuilder->render($items, $viewData, $template, $markerClass,
			$confId.$itemPath.'.', strtoupper($itemPath), $formatter
		);


		return $out;
	}
	protected function getItemPath($configurations, $confId) {
		$itemPath = $configurations->get($confId.'template.itempath');
		return $itemPath ? $itemPath : 'item';
	}
	protected function getMarkerClass($configurations, $confId) {
		$marker = $configurations->get($confId.'template.markerclass');
		return $marker ? $marker : 'tx_rnbase_util_SimpleMarker';
	}

	/**
	 * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
	 * createOutput automatisch als $template übergeben.
	 *
	 * @return string
	 */
	public function getMainSubpart() {
		$confId = $this->getController()->getConfId();
		$subpart = $this->getController()->getConfigurations()->get($confId.'template.subpart');
		if(!$subpart) {
			$subpart = '###'. strtoupper(substr($confId, 0, strlen($confId)-1)) . '###';
		}
		return $subpart;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_List.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_List.php']);
}