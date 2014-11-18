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
 * Common Interface for Maps
 */
interface tx_rnbase_maps_IMap {
	function init($conf, $confId);
	function initTypes(tx_rnbase_maps_TypeRegistry $registry);
	/**
	 * Adds a marker to this map
	 * @param tx_rnbase_maps_IMarker $marker
	 */
	function addMarker(tx_rnbase_maps_IMarker $marker);
	/**
	 * Set a map type
	 */
	function setMapTypeStreet();
	/**
	 * Set a map type
	 */
	function setMapTypeHybrid();
	/**
	 * Set a map type
	 */
	function setMapTypeSatellite();
	/**
	 * Set a map type
	 * @param string $mapType map specific type string
	 */
	function setMapType($mapType);

	/**
	 * Add control
	 * @param tx_rnbase_maps_IControl $control map specific control
	 */
	function addControl(tx_rnbase_maps_IControl $control);
	
	/**
	 * Render the map. Returns all the HTML- and JS-Code to display the map.
	 * @return string
	 */
	function draw();
	/**
	 * Returns an ID-String for the map provider.
	 * @return 
	 */
	function getPROVID();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_IMap.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_IMap.php']);
}

