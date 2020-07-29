<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_maps_ILocation');
tx_rnbase::load('tx_rnbase_maps_Coord');

/**
 * Implementation for a POI with description. Useful to mark a point in a map.
 */
class tx_rnbase_maps_POI extends tx_rnbase_maps_Coord implements tx_rnbase_maps_ILocation
{
    private $city;

    private $street;

    private $zip;

    private $countryCode;

    private $description;

    private $zoomMin;

    private $zoomMax;

    public function __construct($data = [])
    {
        if (!$data) {
            return;
        }

        $this->initField($data, 'lat', 'setLatitude');
        $this->initField($data, 'lng', 'setLongitude');
        $fields = ['description', 'city', 'zip', 'countryCode', 'zoomMin', 'zoomMax'];
        foreach ($fields as $field) {
            $this->initField($data, $field);
        }
    }

    private function initField($data, $fieldname, $methodName = '')
    {
        if (isset($data[$fieldname])) {
            $methodName = $methodName ?: 'set' . ucfirst($fieldname);
            $this->$methodName($data[$fieldname]);
        }
    }

    /**
     * @return int
     */
    public function getZoomMin()
    {
        return $this->zoomMin;
    }

    /**
     * @param int $zoom
     *
     * @return tx_rnbase_maps_POI
     */
    public function setZoomMin($zoom)
    {
        $this->zoomMin = $zoom;

        return $this;
    }

    /**
     * @return int
     */
    public function getZoomMax()
    {
        return $this->zoomMax;
    }

    /**
     * @param int $zoom
     *
     * @return tx_rnbase_maps_POI
     */
    public function setZoomMax($zoom)
    {
        $this->zoomMax = $zoom;

        return $this;
    }

    /* (non-PHPdoc)
     * @see tx_rnbase_maps_ILocation::getCity()
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return tx_rnbase_maps_POI
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /* (non-PHPdoc)
     * @see tx_rnbase_maps_ILocation::getStreet()
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return tx_rnbase_maps_POI
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /* (non-PHPdoc)
     * @see tx_rnbase_maps_ILocation::getZip()
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     *
     * @return tx_rnbase_maps_POI
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /* (non-PHPdoc)
     * @see tx_rnbase_maps_ILocation::getCountryCode()
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $cc
     *
     * @return tx_rnbase_maps_POI
     */
    public function setCountryCode($cc)
    {
        $this->countryCode = $cc;

        return $this;
    }

    /**
     * @param string $desc
     *
     * @return tx_rnbase_maps_POI
     */
    public function setDescription($desc)
    {
        $this->description = $desc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
