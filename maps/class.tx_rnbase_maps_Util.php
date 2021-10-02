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
tx_rnbase::load('tx_rnbase_maps_ILocation');

/**
 * Util methods.
 */
class tx_rnbase_maps_Util
{
    /**
     * Returns the maps template from $confId.'template'.
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string                                     $confId
     *
     * @return string empty string if template was not found
     */
    public static function getMapTemplate($configurations, $confId)
    {
        $file = $configurations->get($confId.'template');
        if (!$file) {
            return '';
        }
        $subpartName = $configurations->get($confId.'subpart');
        if (!$subpartName) {
            return '';
        }

        try {
            $subpart = tx_rnbase_util_Templates::getSubpartFromFile($file, $subpartName);
            $ret = str_replace(["\r\n", "\n", "\r"], '', $subpart);
        } catch (Exception $e) {
            $ret = '';
        }

        return $ret;
    }

    /**
     * Calculate distance for two long/lat-points.
     * Method used from wec_map.
     *
     * @param float  $lat1
     * @param float  $lon1
     * @param float  $lat2
     * @param float  $lon2
     * @param string $distanceType
     *
     * @return float
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2, $distanceType = 'K')
    {
        $l1 = deg2rad($lat1);
        $l2 = deg2rad($lat2);
        $o1 = deg2rad($lon1);
        $o2 = deg2rad($lon2);
        $radius = 'K' == $distanceType ? 6372.795 : 3959.8712;
        $distance = 2 * $radius * asin(min(1, sqrt(pow(sin(($l2 - $l1) / 2), 2) + cos($l1) * cos($l2) * pow(sin(($o2 - $o1) / 2), 2))));

        return $distance;
    }

    private static function hasGeoData($item)
    {
        return !(!$item->getCity() && !$item->getZip() && !$item->getLongitude() && !$item->getLatitude());
    }

    /**
     * Create a bubble for GoogleMaps. This can be done if the item has address data.
     *
     * @param string                   $template
     * @param tx_rnbase_maps_ILocation $item
     */
    public static function createMapBubble(tx_rnbase_maps_ILocation $item)
    {
        if (!self::hasGeoData($item)) {
            return false;
        }

        $marker = new tx_rnbase_maps_DefaultMarker();
        if ($item->getLongitude() || $item->getLatitude()) {
            $coords = tx_rnbase::makeInstance('tx_rnbase_maps_Coord');
            $coords->setLatitude($item->getLatitude());
            $coords->setLongitude($item->getLongitude());
            $marker->setCoords($coords);
        } else {
            $marker->setCity($item->getCity());
            $marker->setZip($item->getZip());
            $marker->setStreet($item->getStreet());
            $marker->setCountry($item->getCountryCode());
        }
        $marker->setDescription('Fill me!');

        return $marker;
    }
}
