<?php
/**
 *  Copyright notice.
 *
 *  (c) 2015 Hannes Bochmann <rene@system25.de>
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
 * Tx_Rnbase_Backend_Utility_Icons.
 *
 * Wrapper für \TYPO3\CMS\Backend\Utility\IconUtility bzw. t3lib_iconWorks
 *
 * @author          Hannes Bochmann <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_Utility_Icons
{
    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([static::getIconUtilityClass(), $method], $arguments);
    }

    /**
     * @return \TYPO3\CMS\Backend\Utility\IconUtility
     */
    protected static function getIconUtilityClass()
    {
        if (tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $class = 'TYPO3\\CMS\\Core\\Imaging\\IconFactory';
        } else {
            $class = 'TYPO3\\CMS\\Backend\\Utility\\IconUtility';
        }

        return $class;
    }

    /**
     * This method is used throughout the TYPO3 Backend to show icons for a DB record.
     *
     * @param string $table
     * @param array  $row
     * @param string $size  "large" "small" or "default", see the constants of the Icon class
     *
     * @return Icon
     */
    public static function getSpriteIconForRecord(
        $table,
        array $row,
        $size = 'default'
    ) {
        if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            return static::getIconFactory()->getIconForRecord($table, $row, $size);
        }

        return static::__callStatic(
            'getSpriteIconForRecord',
            [
                $table,
                $row,
            ]
        );
    }

    /**
     * Returns a string with all available Icons in TYPO3 system. Each icon has a tooltip with its identifier.
     *
     * @return string
     */
    public static function debugSprites()
    {
        $iconsAvailable = $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'];

        if (tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $iconsAvailable = static::getIconRegistry()->getAllRegisteredIconIdentifiers();
        }

        $icons = '<h2>iconsAvailable</h2>';
        foreach ($iconsAvailable as $icon) {
            $icons .= sprintf(
                '<span title="%1$s">%2$s</span>',
                $icon,
                static::getSpriteIcon($icon)
            );
        }

        return $icons;
    }

    /**
     * @param string $iconName
     * @param array  $options
     * @param array  $overlays
     *
     * @return string
     */
    public static function getSpriteIcon(
        $iconName,
        array $options = [],
        array $overlays = []
    ) {
        // @TODO: shoult be used for TYPO3 7 too!
        if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $class = static::getIconUtilityClass();

            return $class::getSpriteIcon($iconName, $options, $overlays);
        }

        $size = \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL;
        if (!empty($options['size'])) {
            $size = $options['size'];
        }

        return static::getIconFactory()->getIcon($iconName, $size)->render();
    }

    /**
     * This helper functions looks up the column that is used for the type of
     * the chosen TCA table. And then fetches the corresponding iconname
     * based on the chosen iconsprite class in this TCA.
     *
     * see ext:core/Configuration/TCA/pages.php for an example with the TCA table "pages"
     *
     * @param string $table The TCA table
     * @param array  $row   The selected record
     *
     * @return string The CSS class for the sprite icon of that DB record
     */
    public static function mapRecordTypeToSpriteIconName($table, array $row)
    {
        if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $class = static::getIconUtilityClass();

            return $class::mapRecordTypeToSpriteIconName($table, $row);
        }

        return static::getIconFactory()->mapRecordTypeToIconIdentifier($table, $row);
    }

    /**
     * The TYPO3 icon factory.
     *
     * @return \TYPO3\CMS\Core\Imaging\IconFactory
     */
    public static function getIconFactory()
    {
        return tx_rnbase::makeInstance(
            'TYPO3\\CMS\\Core\\Imaging\\IconFactory'
        );
    }

    /**
     * The TYPO3 icon factory.
     *
     * @return \TYPO3\CMS\Core\Imaging\IconRegistry
     */
    public static function getIconRegistry()
    {
        return tx_rnbase::makeInstance(
            'TYPO3\\CMS\\Core\\Imaging\\IconRegistry'
        );
    }
}
