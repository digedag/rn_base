<?php

namespace Sys25\RnBase\Maps;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Maps\Google\Control;
use Sys25\RnBase\Maps\Google\Icon;
use Sys25\RnBase\Maps\Google\Map;
use Sys25\RnBase\Utility\Misc;
use tx_rnbase;

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

class Factory
{
    public static $typeInits = [];

    /**
     * Erstellt eine GoogleMap.
     *
     * @param ConfigurationInterface $configurations
     * @param string $confId
     *
     * @return Map
     */
    public static function createGoogleMap($configurations, $confId)
    {
        $map = self::createMap(Map::class, $configurations, $confId);
        $keys = $configurations->getKeyNames($confId.'poi.');
        if (isset($keys)) {
            foreach ($keys as $key) {
                $poi = $configurations->get($confId.'poi.'.$key.'.');
                $poi = tx_rnbase::makeInstance(POI::class, $poi);
                $bubble = MapUtility::createMapBubble($poi);
                if (!$bubble) {
                    continue;
                }
                $bubble->setDescription($poi->getDescription());
                // Prüfen, ob ein Icon konfiguriert ist
                $iconConfId = $confId.'poi.'.$key.'.icon.';
                if ($configurations->get($iconConfId)) {
                    $icon = new Icon($map);
                    $image = $configurations->get($iconConfId.'image', true);
                    $icon->setImage($image, $configurations->getInt($iconConfId.'image.file.maxW'), $configurations->getInt($iconConfId.'image.file.maxH'));
                    $image = $configurations->get($iconConfId.'shadow', true);
                    $icon->setShadow($image, $configurations->getInt($iconConfId.'shadow.file.maxW'), $configurations->getInt($iconConfId.'shadow.file.maxH'));
                    $name = $configurations->get($iconConfId.'name');
                    $icon->setName($name ? $name : Misc::createHash(['name' => $image]));
                    $bubble->setIcon($icon);
                }
                $map->addMarker($bubble);
            }
        }

        return $map;
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlLargeMap()
    {
        return tx_rnbase::makeInstance(Control::class, 'largeMap');
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlSmallMap()
    {
        return tx_rnbase::makeInstance(Control::class, 'smallMap');
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlScale()
    {
        return tx_rnbase::makeInstance(Control::class, 'scale');
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlSmallZoom()
    {
        return tx_rnbase::makeInstance(Control::class, 'smallZoom');
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlOverview()
    {
        return tx_rnbase::makeInstance(Control::class, 'overviewMap');
    }

    /**
     * creates a control.
     *
     * @return IControl
     */
    public static function createGoogleControlMapType()
    {
        return tx_rnbase::makeInstance(Control::class, 'mapType');
    }

    /**
     * Erstellt eine Map.
     *
     * @param string $clazzName
     *
     * @return IMap
     */
    public static function createMap($clazzName, &$configurations, $confId)
    {
        $map = tx_rnbase::makeInstance($clazzName);
        $provId = $map->getPROVID();
        if (!array_key_exists($provId, self::$typeInits)) {
            $map->initTypes(TypeRegistry::getInstance());
            self::$typeInits[$provId] = 1;
        }
        $map->init($configurations, $confId);

        return $map;
    }
}
