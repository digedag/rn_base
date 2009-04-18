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

tx_div::load('tx_rnbase_maps_IMap');
if(!t3lib_extMgm::isLoaded('wec_map'))
	throw new Exception('Extension wec_map must be installed to use GoogleMaps!');
require_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');

/**
 * Implementation for GoogleMaps based on extension wec_map.
 */
class tx_rnbase_maps_google_Map implements tx_rnbase_maps_IMap {
	private $map, $conf, $confId;
	function init($conf, $confId) {
		$this->conf = $conf;
		$this->confId = $confId;
		$apiKey = $conf->get($confId.'google.apikey');
		$width = $conf->get($confId.'width');
		$height = $conf->get($confId.'height');
		$className = t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$this->map = new $className($apiKey, $width, $height);
	}
	/**
	 * Adds a marker to this map
	 * @param tx_rnbase_maps_IMarker $marker
	 */
	function addMarker(tx_rnbase_maps_IMarker $marker) {
		$coord = $marker->getCoords();
		if($coord) {
			$this->getWecMap()->addMarkerByLatLong($coord->getLatitude(), $coord->getLongitude(), 
				$marker->getTitle(), $marker->getDescription(), $marker->getZoomMin(), $marker->getZoomMin(),'');
			return;
		}
		$this->getWecMap()->addMarkerByAddress($marker->getStreet(), $marker->getCity(), $marker->getState(), 
			$marker->getZip(), $marker->getCountry(), 
			$marker->getTitle(), $marker->getDescription(), $marker->getZoomMin(), $marker->getZoomMin(),'');
	}
	function draw() {
		$code = $this->map->drawMap();
		if(intval($this->conf->get($this->confId.'google.forcejs'))) {
			// This is necessary if
			$code .= '<script type="text/javascript">setTimeout("drawMap_'. $this->map->mapName .'()",500);</script>';
		}
		return $code;
	}
	/**
	 * Returns an instance of wec map
	 * @return tx_wecmap_map
	 */
	function getWecMap() {
		return $this->map;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Map.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Map.php']);
}

?>
