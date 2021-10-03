<?php

namespace Sys25\RnBase\Maps;

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2021 Rene Nitzsche (rene@system25.de)
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
class DefaultMarker implements IMarker
{
    private $country;

    private $state;

    private $street;

    private $zip;

    private $city;

    private $coords;

    private $title;

    private $description;

    private $minZoom = 0;

    private $maxZoom = 17;

    /**
     * Returns the country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the country.
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Returns the state/province.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the state/province.
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set the street.
     *
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * Returns the zip code.
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set the zip code.
     *
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the city.
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the coordinates of this marker. This way is preferred to address data.
     *
     * @return ICoord
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * Set coordinates for this marker.
     *
     * @param ICoord $coord
     */
    public function setCoords(ICoord $coord)
    {
        $this->coords = $coord;
    }

    public function setIcon(IIcon $icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    public function getZoomMin()
    {
        return $this->minZoom;
    }

    public function getZoomMax()
    {
        return $this->maxZoom;
    }

    /**
     * Set minimum zoom level for marker.
     *
     * @param int $zoom
     */
    public function setZoomMin($zoom)
    {
        $this->minZoom = $zoom;
    }

    /**
     * Set maximum zoom level for marker.
     *
     * @param int $zoom
     */
    public function setZoomMax($zoom)
    {
        $this->maxZoom = $zoom;
    }
}
