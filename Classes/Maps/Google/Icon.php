<?php

namespace Sys25\RnBase\Maps\Google;


use Sys25\RnBase\Maps\IIcon;

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
 * Implementation for GoogleControls.
 */
class Icon implements IIcon
{
    private $id = null;

    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    public function initFromTS($conf, $confId)
    {
    }

    public function setName($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->id;
    }

    public function setImage($img, $width = 0, $height = 0)
    {
        $this->image = $img;
        if ($width + $height > 0) {
            $this->size = $width.','.$height;
        }
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setShadow($img, $width = 0, $height = 0)
    {
        $this->shadow = $img;
        if ($width + $height > 0) {
            $this->shadowSize = $width.','.$height;
        }
    }

    public function getShadow()
    {
        return $this->shadow;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getShadowSize()
    {
        return $this->shadowSize;
    }

    public function setAnchorPoint($x, $y)
    {
        $this->anchorPoint = $x.','.$y;
    }

    public function getAnchorPoint()
    {
        return $this->anchorPoint ? $this->anchorPoint : '0,0';
    }

    public function setInfoWindowAnchorPoint($x, $y)
    {
        $this->winAnchorPoint = $x.','.$y;
    }

    public function getInfoWindowAnchorPoint()
    {
        return $this->winAnchorPoint ? $this->winAnchorPoint : '0,0';
    }

    /**
     * Returns an ID-String for the map provider.
     *
     * @return string
     */
    public function render()
    {
        $mapName = $this->map->getMapName();

        $image = $this->getImage();
        $size = $this->getSize() ? $this->getSize() : '20,20';
        $shadow = $this->getShadow();
        $shadowSize = $this->getShadowSize() ? $this->getShadowSize() : '20,20';

        $ret = 'WecMap.addIcon("'.$mapName.'", "'.$this->getName().
                '", "'.$image.'", "'.$shadow.
                '", new google.maps.Size('.$size.'), new google.maps.Size('.$shadowSize.
                '), new google.maps.Point('.$this->getAnchorPoint().'), new google.maps.Point('.$this->getInfoWindowAnchorPoint().'));';

        // Für die wec_map ist nur die ID notwendig
        return $ret;
    }
}
