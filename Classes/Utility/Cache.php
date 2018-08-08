<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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
 * Tx_Rnbase_Utility_Cache
 *
 * @package         TYPO3
 * @subpackage      Tx_Rnbase
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_Cache
{

    /**
     * @param array $parameters
     * @return void
     */
    public static function addExcludedParametersForCacheHash(array $parameters)
    {
        self::addConfigurationToCacheHashCalculator(
            'cHashExcludedParameters',
            'excludedParameters',
            $parameters
        );
    }

    /**
     * @param array $parameters
     *
     * @return void
     */
    public static function addCacheHashRequiredParameters(array $parameters)
    {
        self::addConfigurationToCacheHashCalculator(
            'cHashRequiredParameters',
            'requireCacheHashPresenceParameters',
            $parameters
        );
    }

    /**
     * @param string $typo3ConfVarsKey
     * @param string $cacheHashCalculatorInternalConfigurationKey
     * @param array $configurationValue
     *
     * @return void
     */
    protected static function addConfigurationToCacheHashCalculator(
        $typo3ConfVarsKey,
        $cacheHashCalculatorInternalConfigurationKey,
        array $configurationValue
    ) {
        $startingGlue = '';
        if ($GLOBALS['TYPO3_CONF_VARS']['FE'][$typo3ConfVarsKey]) {
            $startingGlue = ',';
        }
        $GLOBALS['TYPO3_CONF_VARS']['FE'][$typo3ConfVarsKey] .= $startingGlue . join(',', $configurationValue);
        /* @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator $cacheHashCalculator */
        $cacheHashCalculator = \tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator');
        $cacheHashCalculator->setConfiguration(array(
            $cacheHashCalculatorInternalConfigurationKey => explode(
                ',',
                $GLOBALS['TYPO3_CONF_VARS']['FE'][$typo3ConfVarsKey]
            )
        ));
    }

    /**
     * @param string $urlQueryString Query-parameters: "&xxx=yyy&zzz=uuu
     * @return string Hash of all the values
     */
    public static function generateCacheHashForUrlQueryString($urlQueryString)
    {
        if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            /* @var $calculator \TYPO3\CMS\Frontend\Page\CacheHashCalculator */
            $calculator = tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator');
            $hash = $calculator->generateForParameters($urlQueryString);
        } elseif (class_exists('t3lib_cacheHash')) {
            $calculator = new t3lib_cacheHash();
            $hash = $calculator->generateForParameters($urlQueryString);
        } elseif (is_callable(array(t3lib_div, 'generateCHash'))) {
            $hash = t3lib_div::generateCHash($urlQueryString);
        }

        return $hash;
    }

    /**
     * Is mainly used as user function in TypoScript. Example:
     *
     * plugin.tt_news.stdWrap.postUserFunc = Tx_Rnbase_Utility_Cache->addCacheTagsToPage
     * plugin.tt_news.stdWrap.postUserFunc {
     *      0 = tt_news
     *      1 = tt_news_category
     * }
     *
     * @param string $content
     * @param array $cacheTags
     *
     * @return string
     */
    public function addCacheTagsToPage($content, array $cacheTags)
    {
        if (!empty($cacheTags)) {
            tx_rnbase_util_TYPO3::getTSFE()->addCacheTags($cacheTags);
        }

        return $content;
    }
}
