<?php

use Sys25\RnBase\Domain\Model\DataInterface;

/**
 *  Copyright notice.
 *
 *  (c) 2016 René Nitzsche <rene@system25.de>
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
 */

/**
 * @author          René Nitzsche <rene@system25.de>
 */
class Tx_Rnbase_Frontend_Marker_Utility
{
    /**
     * Returns an array with all attribute names not used in template.
     *
     * We accept DataInterface, but the model must also
     * implement IteratorAggregate!
     *
     * @param DataInterface $item
     * @param string $template
     * @param string $marker
     *
     * @return array
     */
    public static function findUnusedAttributes(DataInterface $item, $template, $marker)
    {
        $ignore = [];
        $minfo = static::containsMarker($template, $marker.'___MINFO');
        $minfoArr = [];
        foreach ($item as $key => $value) {
            if ($minfo) {
                $minfoArr[$key] = $marker.'_'.strtoupper($key);
            }
            if (!static::containsMarker($template, $marker.'_'.strtoupper($key))) {
                $ignore[] = $key;
            }
        }
        if ($minfo) {
            tx_rnbase::load('tx_rnbase_util_Debug');
            $item->setProperty('__MINFO', tx_rnbase_util_Debug::viewArray($minfoArr));
        }

        return $ignore;
    }

    /**
     * @param string $template
     * @param string $markerPrefix a string like MATCH_HOME
     *
     * @return bool
     */
    public static function containsMarker($template, $markerPrefix)
    {
        return false !== strpos($template, '###'.$markerPrefix);
    }
}
