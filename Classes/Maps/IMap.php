<?php

namespace Sys25\RnBase\Maps;

use Sys25\RnBase\Configuration\ConfigurationInterface;

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
 * Common Interface for Maps.
 */
interface IMap
{
    public function init(ConfigurationInterface $conf, $confId);

    public function initTypes(TypeRegistry $registry);

    /**
     * Adds a marker to this map.
     *
     * @param IMarker $marker
     */
    public function addMarker(IMarker $marker);

    /**
     * Set a map type.
     */
    public function setMapTypeStreet();

    /**
     * Set a map type.
     */
    public function setMapTypeHybrid();

    /**
     * Set a map type.
     */
    public function setMapTypeSatellite();

    /**
     * Set a map type.
     *
     * @param string $mapType map specific type string
     */
    public function setMapType($mapType);

    /**
     * Add control.
     *
     * @param IControl $control map specific control
     */
    public function addControl(IControl $control);

    /**
     * Render the map. Returns all the HTML- and JS-Code to display the map.
     *
     * @return string
     */
    public function draw();

    /**
     * Returns an ID-String for the map provider.
     *
     * @return
     */
    public function getPROVID();
}
