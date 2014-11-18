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

tx_rnbase::load('tx_rnbase_maps_ICoord');

/**
 * Default implementation for coordinates
 */
class tx_rnbase_maps_Coord implements tx_rnbase_maps_ICoord {
	private $latitude;
	private $longitude;

	public function __construct($latitude=0.0, $longitude=0.0) {
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
	}
	/**
	 * Returns the latitude
	 * @return float
	 */
	function getLatitude() {
		return $this->latitude;
	}
	/**
	 * Returns the longitude
	 * @return float
	 */
	function getLongitude(){
		return $this->longitude;
	}
	/**
	 * Returns the latitude
	 * @param float $lat
	 */
	function setLatitude($lat) {
		$this->latitude = $lat;
	}
	/**
	 * Returns the longitude
	 * @param float $long
	 */
	function setLongitude($long){
		$this->longitude = $long;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Coord.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Coord.php']);
}

