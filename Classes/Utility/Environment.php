<?php

namespace Sys25\RnBase\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A wrapper for the Core Environment.
 *
 * @author          Michael Wagner
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Environment
{
    /**
     * The path + filename to the current PHP script.
     *
     * @return string
     */
    public static function getCurrentScript()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO95OrHigher()) {
            return \TYPO3\CMS\Core\Core\Environment::getCurrentScript();
        }

        // Deprecated path related constant
        return PATH_thisScript;
    }

    /**
     * The public web folder where index.php (= the frontend application) is put. This is equal to the legacy constant
     * PATH_site, without the trailing slash. For non-composer installations, the project path = the public path.
     *
     * @return string
     * @return string
     */
    public static function getPublicPath()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO95OrHigher()) {
            return \TYPO3\CMS\Core\Core\Environment::getPublicPath().'/';
        }

        // Deprecated path related constant
        return PATH_site;
    }

    /**
     * The public web folder of typo3.
     *
     * @return string
     */
    public static function getPublicTypo3Path()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO95OrHigher()) {
            return \TYPO3\CMS\Core\Core\Environment::getPublicPath().'/typo3/';
        }

        // Deprecated path related constant
        return PATH_typo3;
    }

    /**
     * The public web folder of typo3conf.
     *
     * @return string
     */
    public static function getPublicTypo3confPath()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO95OrHigher()) {
            return \TYPO3\CMS\Core\Core\Environment::getPublicPath().'/typo3conf';
        }

        // Deprecated path related constant
        return PATH_typo3conf;
    }

    /**
     * @return string
     */
    public static function getCurrentLanguageKey()
    {
        if (TYPO3_MODE === 'BE') {
            return $GLOBALS['LANG']->lang;
        }
        if (!\tx_rnbase_util_TYPO3::isTYPO95OrHigher()) {
            return $GLOBALS['TSFE']->lang;
        }

        $request = isset($GLOBALS['TYPO3_REQUEST']) ? $GLOBALS['TYPO3_REQUEST'] : null;
        $languageKey = isset($GLOBALS['TSFE']->config['config']['language'])
            ? $GLOBALS['TSFE']->config['config']['language'] : 'default';

        if (!$request instanceof ServerRequestInterface) {
            return $languageKey;
        }

        if (!$request->getAttribute('language') instanceof SiteLanguage) {
            return $languageKey;
        }

        return $request->getAttribute('language')->getTypo3Language();
    }
}
