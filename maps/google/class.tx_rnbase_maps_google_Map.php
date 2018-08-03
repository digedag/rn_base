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

tx_rnbase::load('tx_rnbase_maps_BaseMap');
tx_rnbase::load('tx_rnbase_util_Extensions');
tx_rnbase::load('tx_rnbase_util_Strings');

if (!tx_rnbase_util_Extensions::isLoaded('wec_map')) {
    throw new Exception('Extension wec_map must be installed to use GoogleMaps!');
}
require_once tx_rnbase_util_Extensions::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php';

/**
 * Implementation for GoogleMaps based on extension wec_map.
 */
class tx_rnbase_maps_google_Map extends tx_rnbase_maps_BaseMap
{
    public static $PROVID = 'GOOGLEMAPS';
    public static $mapTypes = [];
    /* @var $map tx_wecmap_map_google */
    private $map;
    private $conf;
    private $confId;

    public function init(Tx_Rnbase_Configuration_ProcessorInterface $conf, $confId)
    {
        $this->conf = $conf;
        $this->confId = $confId;
        $apiKey = $conf->get($confId.'google.apikey');
        $apiKey = $apiKey ? $apiKey : null;
        $width = $conf->get($confId.'width');
        $height = $conf->get($confId.'height');

        $this->map = tx_rnbase::makeInstance('tx_wecmap_map_google', $apiKey, $width, $height);
        // Der MapType
        $mapType = $conf->get($confId.'maptype') ? constant($conf->get($confId.'maptype')) : null;
        $types = array_flip(tx_rnbase_maps_TypeRegistry::getMapTypes());
        if ($mapType && array_key_exists($mapType, $types)) {
            $this->setMapType(tx_rnbase_maps_TypeRegistry::getInstance()->getType($this, $mapType));
        }
        // Controls
        $controls = $conf->get($confId.'google.controls');
        if ($controls) {
            $controls = tx_rnbase_util_Strings::trimExplode(',', $controls);
            foreach ($controls as $control) {
                $this->addControl(tx_rnbase::makeInstance('tx_rnbase_maps_google_Control', $control));
            }
        }
    }

    public function initTypes(tx_rnbase_maps_TypeRegistry $registry)
    {
        $registry->addType($this, RNMAP_MAPTYPE_STREET, 'G_NORMAL_MAP');
        $registry->addType($this, RNMAP_MAPTYPE_SATELLITE, 'G_SATELLITE_MAP');
        $registry->addType($this, RNMAP_MAPTYPE_HYBRID, 'G_HYBRID_MAP');
        $registry->addType($this, RNMAP_MAPTYPE_PHYSICAL, 'G_PHYSICAL_MAP');
    }

    /**
     * Set a map type.
     *
     * @param string $mapType map specific type string
     */
    public function setMapType($mapType)
    {
        $this->getWecMap()->setType($mapType);
    }

    /**
     * Adds a control.
     *
     * @param tx_rnbase_maps_IControl $control
     */
    public function addControl(tx_rnbase_maps_IControl $control)
    {
        $this->getWecMap()->addControl($control->render());
    }

    /**
     * Adds a marker to this map.
     *
     * @param tx_rnbase_maps_IMarker $marker
     */
    public function addMarker(tx_rnbase_maps_IMarker $marker)
    {
        $icon = $marker->getIcon();
        $iconName = '';
        if ($icon) {
            $this->map->icons[] = $icon->render();
            $iconName = $icon->getName();
        }

        $coord = $marker->getCoords();
        if ($coord) {
            $this->getWecMap()->addMarkerByLatLong(
                $coord->getLatitude(),
                $coord->getLongitude(),
                ($marker->getTitle() ? $marker->getTitle() : ''),
                $marker->getDescription(),
                $marker->getZoomMin(),
                $marker->getZoomMax(),
                $iconName
            );

            return;
        }

        $this->getWecMap()->addMarkerByAddress(
            $marker->getStreet(),
            $marker->getCity(),
            $marker->getState(),
            $marker->getZip(),
            $marker->getCountry(),
            ($marker->getTitle() ? $marker->getTitle() : ''),
            $marker->getDescription(),
            $marker->getZoomMin(),
            $marker->getZoomMax(),
            $iconName
        );
    }

    public function draw()
    {
        $code = $this->map->drawMap();

        return $code;
    }

    /**
     * Returns an instance of wec map.
     *
     * @return tx_wecmap_map_google
     */
    public function getWecMap()
    {
        return $this->map;
    }

    /**
     * Returns an ID-String for the map provider.
     *
     * @return
     */
    public function getPROVID()
    {
        return self::$PROVID;
    }

    public function getMapName()
    {
        return $this->getWecMap()->mapName;
    }
}
