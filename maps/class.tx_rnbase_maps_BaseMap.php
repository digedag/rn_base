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

tx_rnbase::load('tx_rnbase_maps_IMap');
tx_rnbase::load('tx_rnbase_maps_TypeRegistry');



/**
 * Common Interface for Maps
 */
abstract class tx_rnbase_maps_BaseMap implements tx_rnbase_maps_IMap {
	/**
	 * Set a map type
	 */
	function setMapTypeStreet() {
		$type = tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, RNMAP_MAPTYPE_STREET);
		$this->setMapType($type);
	}
	/**
	 * Set a map type
	 */
	function setMapTypeHybrid(){
		$type = tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, RNMAP_MAPTYPE_HYBRID);
		$this->setMapType($type);
	}
	/**
	 * Set a map type
	 */
	function setMapTypePhysical(){
		$type = tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, RNMAP_MAPTYPE_PHYSICAL);
		$this->setMapType($type);
	}
	/**
	 * Set a map type
	 */
	function setMapTypeSatellite(){
		$type = tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, RNMAP_MAPTYPE_SATELLITE);
		$this->setMapType($type);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_BaseMap.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_BaseMap.php']);
}
