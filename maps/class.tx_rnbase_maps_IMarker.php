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
 * Common Interface for MapMarkers
 */
interface tx_rnbase_maps_IMarker {
	/**
	 * Returns the country
	 * @return string
	 */
	function getCountry();
	/**
	 * Returns the state/province
	 * @return string
	 */
	function getState();
	/**
	 * Returns the street
	 * @return string
	 */
	function getStreet();
	/**
	 * Returns the zip code
	 * @return string
	 */
	function getZip();
	/**
	 * Returns the city
	 * @return string
	 */
	function getCity();
	/**
	 * Returns a specific coordination
	 * @return tx_rnbase_maps_ICoord
	 */
	function getCoords();

	/**
	 * Returns the Icon for this marker
	 * @return tx_rnbase_maps_IIcon
	 */
	function getIcon();
	/**
	 * Returns the label
	 * @return string
	 */
	function getTitle();
	/**
	 * Returns the description
	 * @return string
	 */
	function getDescription();
	/**
	 * Minimum zoom level to show the marker
	 * @return int 0 up to 17
	 */
	function getZoomMin();
	/**
	 * Maximum zoom level to show the marker
	 * @return int 0 up to 17
	 */
	function getZoomMax();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_IMarker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_IMarker.php']);
}

