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

tx_rnbase::load('tx_rnbase_maps_BaseMap');
if(!t3lib_extMgm::isLoaded('wec_map'))
	throw new Exception('Extension wec_map must be installed to use GoogleMaps!');
require_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');

/**
 * Implementation for GoogleMaps based on extension wec_map.
 */
class tx_rnbase_maps_google_Map extends tx_rnbase_maps_BaseMap {
	static $PROVID = 'GOOGLEMAPS';
	static $mapTypes = array();
	private $map, $conf, $confId;
	function init($conf, $confId) {
		$this->conf = $conf;
		$this->confId = $confId;
		$apiKey = $conf->get($confId.'google.apikey');
		$apiKey = $apiKey ? $apiKey : NULL;
		$width = $conf->get($confId.'width');
		$height = $conf->get($confId.'height');
		
		$this->map = tx_rnbase::makeInstance('tx_wecmap_map_google', $apiKey, $width, $height);
		// Der MapType
		$mapType = $conf->get($confId.'maptype') ? constant($conf->get($confId.'maptype')) : NULL;
		$types = array_flip(tx_rnbase_maps_TypeRegistry::getMapTypes());
		if($mapType && array_key_exists($mapType, $types)) {
			$this->setMapType(tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, $mapType));
		}
		// Controls
		$controls = $conf->get($confId.'google.controls');
		if($controls) {
			$controls = t3lib_div::trimExplode(',', $controls);
			foreach($controls As $control) {
				$this->addControl(tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', $control));
			}
		}
	}
	function initTypes(tx_rnbase_maps_TypeRegistry $registry) {
		$registry->addType($this, RNMAP_MAPTYPE_STREET, 'G_NORMAL_MAP');
		$registry->addType($this, RNMAP_MAPTYPE_SATELLITE, 'G_SATELLITE_MAP');
		$registry->addType($this, RNMAP_MAPTYPE_HYBRID, 'G_HYBRID_MAP');
		$registry->addType($this, RNMAP_MAPTYPE_PHYSICAL, 'G_PHYSICAL_MAP');
		
	}
	/**
	 * Set a map type
	 * @param string $mapType map specific type string
	 */
	function setMapType($mapType) {
		$this->getWecMap()->setType($mapType);
	}
	/**
	 * Adds a control
	 *
	 * @param tx_rnbase_maps_IControl $control
	 */
	function addControl(tx_rnbase_maps_IControl $control) {
		$this->getWecMap()->addControl($control->render());
	}
	
	/**
	 * Adds a marker to this map
	 * @param tx_rnbase_maps_IMarker $marker
	 */
	function addMarker(tx_rnbase_maps_IMarker $marker) {
		$icon = $marker->getIcon();
		$iconName = '';
		if($icon) {
			$this->map->icons[] = $icon->render();
			$iconName = $icon->getName();
		}

		$coord = $marker->getCoords();
		if($coord) {
			$this->getWecMap()->addMarkerByLatLong($coord->getLatitude(), $coord->getLongitude(), 
				$marker->getTitle(), $marker->getDescription(), $marker->getZoomMin(), $marker->getZoomMax(), $iconName);
			return;
		}
		
		$this->getWecMap()->addMarkerByAddress($marker->getStreet(), $marker->getCity(), $marker->getState(), 
			$marker->getZip(), $marker->getCountry(), 
			$marker->getTitle(), $marker->getDescription(), $marker->getZoomMin(), $marker->getZoomMax(), $iconName);
	}
	function draw() {
		$code = $this->map->drawMap();
		if(intval($this->conf->get($this->confId.'google.forcejs'))) {
			// This is necessary if
			$code .= "\n". '<script type="text/javascript">GEvent.addDomListener(window, "load", function(){drawMap_'. $this->map->mapName .'();})</script>';
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

	/**
	 * Returns an ID-String for the map provider.
	 * @return 
	 */
	function getPROVID() {
		return self::$PROVID;
	}
	function getMapName() {
		return $this->getWecMap()->mapName;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Map.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/google/class.tx_rnbase_maps_google_Map.php']);
}

