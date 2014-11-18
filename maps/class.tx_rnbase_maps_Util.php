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
 * Util methods
 */
class tx_rnbase_maps_Util {
	/**
	 * Calculate distance for two long/lat-points.
	 * Method used from wec_map
	 * @param double $lat1
	 * @param double $lon1
	 * @param double $lat2
	 * @param double $lon2
	 * @param string $distanceType
	 * @return double
	 */
	public static function calculateDistance($lat1, $lon1, $lat2, $lon2, $distanceType='K') {
		$l1 = deg2rad ($lat1);
    $l2 = deg2rad ($lat2);
    $o1 = deg2rad ($lon1);
    $o2 = deg2rad ($lon2);
		$radius = $distanceType == 'K' ? 6372.795 : 3959.8712;
		$distance = 2 * $radius * asin(min(1, sqrt( pow(sin(($l2-$l1)/2), 2) + cos($l1)*cos($l2)* pow(sin(($o2-$o1)/2), 2) )));
		return $distance;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Util.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/maps/class.tx_rnbase_maps_Util.php']);
}

