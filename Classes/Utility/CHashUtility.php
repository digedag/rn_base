<?php

namespace Sys25\RnBase\Utility;

use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
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
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CHashUtility
{
    /**
     * @param array $parameters
     */
    public static function addExcludedParametersForCacheHash(array $parameters)
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $typo3ConfVarsEntry = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'];
        } else {
            $typo3ConfVarsEntry = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'];
        }
        self::addConfigurationToCacheHashCalculator(
            $typo3ConfVarsEntry,
            'excludedParameters',
            $parameters
        );
    }

    /**
     * @param array $parameters
     */
    public static function addCacheHashRequiredParameters(array $parameters)
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $typo3ConfVarsEntry = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters'];
        } else {
            $typo3ConfVarsEntry = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'];
        }
        self::addConfigurationToCacheHashCalculator(
            $typo3ConfVarsEntry,
            'requireCacheHashPresenceParameters',
            $parameters
        );
    }

    /**
     * @param mixed  $typo3ConfVarsEntry
     * @param string $cacheHashCalculatorInternalConfigurationKey
     * @param array  $configurationValue
     */
    protected static function addConfigurationToCacheHashCalculator(
        &$typo3ConfVarsEntry,
        $cacheHashCalculatorInternalConfigurationKey,
        array $configurationValue
    ) {
        if (!TYPO3::isTYPO90OrHigher()) {
            $startingGlue = '';
            if ($typo3ConfVarsEntry) {
                $startingGlue = ',';
            }
            $typo3ConfVarsEntry .= $startingGlue.implode(',', $configurationValue);

            $cacheHashCalculatorInternalConfiguration =
                Strings::trimExplode(',', $typo3ConfVarsEntry, true);
        } else {
            $cacheHashCalculatorInternalConfiguration = $typo3ConfVarsEntry =
                array_merge($typo3ConfVarsEntry, $configurationValue);
        }
        /* @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator $cacheHashCalculator */
        $cacheHashCalculator = \tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator');
        $cacheHashCalculator->setConfiguration([
            $cacheHashCalculatorInternalConfigurationKey => $cacheHashCalculatorInternalConfiguration,
        ]);
    }

    /**
     * @param string $urlQueryString Query-parameters: "&xxx=yyy&zzz=uuu
     *
     * @return string Hash of all the values
     */
    public static function generateCacheHashForUrlQueryString($urlQueryString)
    {
        /* @var $calculator \TYPO3\CMS\Frontend\Page\CacheHashCalculator */
        $calculator = tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator');
        $hash = $calculator->generateForParameters($urlQueryString);

        return $hash;
    }

    /**
     * Is mainly used as user function in TypoScript. Example:.
     *
     * plugin.tt_news.stdWrap.postUserFunc = Tx_Rnbase_Utility_Cache->addCacheTagsToPage
     * plugin.tt_news.stdWrap.postUserFunc {
     *      0 = tt_news
     *      1 = tt_news_category
     * }
     *
     * @param string $content
     * @param array  $cacheTags
     *
     * @return string
     */
    public function addCacheTagsToPage($content, array $cacheTags)
    {
        if (!empty($cacheTags)) {
            TYPO3::getTSFE()->addCacheTags($cacheTags);
        }

        return $content;
    }
}
