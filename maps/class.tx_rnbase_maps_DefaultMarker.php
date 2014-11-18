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

tx_rnbase::load('tx_rnbase_maps_IMarker');

/**
 * Common Interface for Maps
 */
class tx_rnbase_maps_DefaultMarker implements tx_rnbase_maps_IMarker {
	private $country, $state, $street, $zip, $city;
	private $coords;
	private $title, $description;
	private $minZoom = 0;
	private $maxZoom = 17;
	/**
	 * Returns the country
	 * @return string
	 */
	function getCountry() {
		return $this->country;
	}
	/**
	 * Set the country
	 * @param string $country
	 */
	function setCountry($country) {
		$this->country = $country;
	}
	/**
	 * Returns the state/province
	 * @return string
	 */
	function getState() {
		return $this->state;
	}
	/**
	 * Set the state/province
	 * @param string $state
	 */
	function setState($state) {
		$this->state = $state;
	}
	/**
	 * Returns the street
	 * @return string
	 */
	function getStreet() {
		return $this->street;
	}
	/**
	 * Set the street
	 * @param string $street
	 */
	function setStreet($street) {
		$this->street = $street;
	}
	/**
	 * Returns the zip code
	 * @return string
	 */
	function getZip() {
		return $this->zip;
	}
	/**
	 * Set the zip code
	 * @param string $zip
	 */
	function setZip($zip) {
		$this->zip = $zip;
	}
	/**
	 * Returns the city
	 * @return string
	 */
	function getCity() {
		return $this->city;
	}
	/**
	 * Set the city
	 * @param string $city
	 */
	function setCity($city) {
		$this->city = $city;
	}
	/**
	 * Returns the coordinates of this marker. This way is preferred to address data.
	 *
	 * @return tx_rnbase_maps_ICoord
	 */
	function getCoords() {
		return $this->coords;
	}
	/**
	 * Set coordinates for this marker
	 *
	 * @param tx_rnbase_maps_ICoord $coord
	 */
	function setCoords(tx_rnbase_maps_ICoord $coord) {
		$this->coords = $coord;
	}
	
	function setIcon(tx_rnbase_maps_IIcon $icon) {
		$this->icon = $icon;
	}
	function getIcon() {
		return $this->icon;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($desc) {
		$this->description = $desc;
	}
	function getZoomMin() {
		return $this->minZoom;
	}
	function getZoomMax() {
		return $this->maxZoom;
	}
	/**
	 * Set minimum zoom level for marker
	 * @param int $zoom
	 */
	function setZoomMin($zoom) {
		$this->minZoom = $zoom;
	}
	/**
	 * Set maximum zoom level for marker
	 * @param int $zoom
	 */
	function setZoomMax($zoom) {
		$this->maxZoom = $zoom;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_DefaultMarker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_DefaultMarker.php']);
}

