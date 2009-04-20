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
 */
class tx_rnbase_maps_Factory {
	static $typeInits = array();

	/**
	 * Erstellt eine GoogleMap
	 *
	 * @return tx_rnbase_maps_google_Map
	 */
	static function createGoogleMap(&$configurations, $confId) {
		$map = self::createMap('tx_rnbase_maps_google_Map', $configurations, $confId);
		return $map;
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlLargeMap(){
//		$registry->addType($this,RNMAP_CONTROL_SCALE, 'scale');
//		$registry->addType($this,RNMAP_CONTROL_ZOOM, 'smallZoom');
//		$registry->addType($this,RNMAP_CONTROL_OVERVIEW, 'overviewMap');
//		$registry->addType($this,RNMAP_CONTROL_MAPTYPE, 'mapType');
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('largeMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlSmallMap(){
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('smallMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlScale(){
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('scale');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlSmallZoom(){
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('smallZoom');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlOverview(){
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('overviewMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlMapType(){
		$classname = tx_div::makeInstanceClassname('tx_rnbase_maps_google_Control');
		return new $classname('mapType');
	}
	
	/**
	 * Erstellt eine Map
	 *
	 * @param string $clazzName
	 * @return tx_rnbase_maps_IMap
	 */
	static function createMap($clazzName, &$configurations, $confId) {
		$map = tx_div::makeInstance($clazzName);
		$provId = $map->getPROVID();
		if(!array_key_exists($provId, self::$typeInits)) {
			$map->initTypes(tx_rnbase_maps_TypeRegistry::getInstance());
			self::$typeInits[$provId] = 1;
		}
		$map->init($configurations, $confId);
		return $map;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Factory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Factory.php']);
}

?>