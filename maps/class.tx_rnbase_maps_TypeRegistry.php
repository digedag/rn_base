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

define('RNMAP_MAPTYPE_STREET', 1);
define('RNMAP_MAPTYPE_SATELLITE', 2);
define('RNMAP_MAPTYPE_HYBRID', 3);
define('RNMAP_MAPTYPE_PHYSICAL', 4);


/**
 * Registry
 */
class tx_rnbase_maps_TypeRegistry {
	static $instance = NULL;
	private static $mapTypes = array(RNMAP_MAPTYPE_STREET, RNMAP_MAPTYPE_SATELLITE, RNMAP_MAPTYPE_HYBRID, RNMAP_MAPTYPE_PHYSICAL);
	private $types = array();
	private function tx_rnbase_maps_TypeRegistry() {
	}
	/**
	 * Returns the singleton instance
	 *
	 * @return tx_rnbase_maps_TypeRegistry
	 */
	static function getInstance() {
		if(!is_object(self::$instance)) {
			self::$instance = new tx_rnbase_maps_TypeRegistry();
		}
		return self::$instance;
	}
	function addType(tx_rnbase_maps_IMap $map, $typeId, $mapType) {
		$this->types[$map->getPROVID()][$typeId] = $mapType;
	}
	/**
	 * Returns a map specific type string
	 *
	 * @param tx_rnbase_maps_IMap $map
	 * @param string $typeId
	 * @return string
	 */
	function getType(tx_rnbase_maps_IMap $map, $typeId) {
		$type = $this->types[$map->getPROVID()][$typeId];
		return $type;
	}
	/**
	 * Returns an array with all available default types
	 *
	 * @return array
	 */
	static function getMapTypes() {
		return self::$mapTypes;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_TypeRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_TypeRegistry.php']);
}

