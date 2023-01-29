<?php

namespace Sys25\RnBase\Configuration;

use Sys25\RnBase\Exception\SkipActionException;
use Sys25\RnBase\Frontend\View\ContextInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2019 Rene Nitzsche <rene@system25.de>
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
 * Interface for configuiration processor.
 *
 * @author Michael Wagner
 */
interface ConfigurationInterface
{
    /**
     * Converts an USER to USER_INT, so the cache can be disabled in the action!
     *
     * If this method is called, the Skip Exception can be thrown.
     * The controller is called twice,
     * whenn a USER Object is convertet to USER_INTERNAL.
     * The SkipAction avoids this!
     *
     * @param bool $convert
     *
     * @return bool|void
     *
     * @throws SkipActionException
     */
    public function convertToUserInt($convert = true);

    /**
     * Whether or not the current plugin is executed as USER_INT.
     *
     * @return bool
     */
    public function isPluginUserInt();

    /**
     * Factory-Method for links. The new link is initialized with qualifier and optional
     * with keepVars set.
     *
     * @param bool $addKeepVars Whether or not keepVars should be set
     *
     * @return \tx_rnbase_util_Link
     */
    public function createLink($addKeepVars = true);

    /**
     * Returns the extension key.
     *
     * @return string
     */
    public function getExtensionKey();

    /**
     * Returns the qualifier for plugin links: qualifier[param]=value.
     *
     * @return string
     */
    public function getQualifier();

    /**
     * The plugins original content object.
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getContentObject();

    /**
     * Returns the formatter connected to this configuration object.
     *
     * @return \tx_rnbase_util_FormatUtil
     */
    public function getFormatter();

    /**
     * Return the data container for view by reference. This container should be filled
     * by Controller-Action.
     *
     * @return ContextInterface
     */
    public function getViewData();

    /**
     * Returns the complete TS config array.
     *
     * @return array
     */
    public function getConfigArray();

    /**
     * Get a value or an array by providing a relative pathKey.
     *
     * @param string $pathKey Relative setupPath like detail.item.links.show
     * @param bool   $deep
     *
     * @return array|string|null
     */
    public function get($pathKey, $deep = false);

    /**
     * Returns a boolean config value. The return value is FALSE if the value is empty or 0 or 'FALSE'.
     *
     * @param string $pathKey
     * @param bool   $deep
     * @param bool   $notDefined Value to return if no value configured or empty
     *
     * @return bool
     */
    public function getBool($pathKey, $deep = false, $notDefined = false);

    /**
     * Returns a int config value.
     *
     * @param string $pathKey
     * @param bool   $deep
     *
     * @return int
     */
    public function getInt($pathKey, $deep = false);

    /**
     * Get a exploded value.
     *
     * @param string $pathKey
     * @param string $delim
     * @param bool   $deep
     *
     * @return array
     */
    public function getExploded($pathKey, $delim = ',', $deep = false);

    /**
     * Returns all keynames below a config branch. Any trailing points will be removed.
     *
     * @param string $confId
     *
     * @return array of strings or empty array
     */
    public function getKeyNames($confId);

    /**
     * Returns all keynames below a config branch.
     * Any trailing points will be removed.
     *
     * @param array $conf Configuration array
     *
     * @return array
     */
    public function getUniqueKeysNames(array $conf);

    /**
     * Finds a value either from config or in language markers. Please note, that all points are
     * replaced by underscores for language strings. This is, because TYPO3 doesn't like point
     * notations for language keys.
     *
     * @param string $pathKey
     *
     * @return mixed but should be a string
     */
    public function getCfgOrLL($pathKey);

    /**
     * Returns the UID of the current content object in tt_content.
     *
     * @return int
     */
    public function getPluginId();

    public function getExtensionConfigValue($extKey, $cfgKey = '');
}
