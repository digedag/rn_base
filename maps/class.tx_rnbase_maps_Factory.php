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
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'largeMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlSmallMap(){
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'smallMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlScale(){
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'scale');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlSmallZoom(){
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'smallZoom');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlOverview(){
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'overviewMap');
	}
	/**
	 * creates a control
	 * @return tx_rnbase_maps_IControl
	 */
	static function createGoogleControlMapType(){
		return tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', 'mapType');
	}
	
	/**
	 * Erstellt eine Map
	 *
	 * @param string $clazzName
	 * @return tx_rnbase_maps_IMap
	 */
	static function createMap($clazzName, &$configurations, $confId) {
		$map = tx_rnbase::makeInstance($clazzName);
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

