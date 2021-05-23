<?php

use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2020 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Contains utility functions for HTML-Templates
 * This is mainly a replacement for tslib_content::substituteMarkerArrayCached(). There is a memory
 * problem with this method. If it is heavely used the call to $GLOBALS['TT']->push() takes a lot
 * of memory.
 */
class tx_rnbase_util_Templates
{
    public static $substMarkerCache = [];

    private static $tmpl;

    private static $substCacheEnabled = null;

    /**
     * Shortcut to \TYPO3\CMS\Core\Service\MarkerBasedTemplateService::getSubpart.
     *
     * @param string $template
     * @param string $subpart
     *
     * @return string
     */
    public static function getSubpart($template, $subpart)
    {
        \TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class;
        $parser = tx_rnbase::makeInstance(
            'TYPO3\CMS\Core\Service\MarkerBasedTemplateService'
        );
        $content = $parser->getSubpart($template, $subpart);

        // check for Subtemplates
        return self::includeSubTemplates($content);
    }

    /**
     * @return TYPO3\CMS\Core\TypoScript\TemplateService
     */
    public static function getTSTemplate()
    {
        if (!is_object(self::$tmpl)) {
            if ($GLOBALS['TSFE']->tmpl) {
                self::$tmpl = $GLOBALS['TSFE']->tmpl;
            } else {
                self::$tmpl = tx_rnbase::makeInstance(
                    tx_rnbase_util_Typo3Classes::getTemplateServiceClass()
                );
                self::$tmpl->init();
                self::$tmpl->tt_track = 0;
            }
        }

        return self::$tmpl;
    }

    /**
     * Returns a subpart from file.
     *
     * @param string $fileName filepath or url
     * @param string $subpart
     *
     * @return string
     *
     * @throws Exception if file or subpart not found
     */
    public static function getSubpartFromFile($fileName, $subpart)
    {
        $file = tx_rnbase_util_Files::getFileName($fileName);

        if (TYPO3_MODE == 'BE' && false === strpos($file, \Sys25\RnBase\Utility\Environment::getPublicPath())) {
            $file = \Sys25\RnBase\Utility\Environment::getPublicPath().$file;
        } // Im BE auf absoluten Pfad setzen

        $templateCode = tx_rnbase_util_Network::getUrl($file);
        if (!$templateCode) {
            throw new Exception('File not found: '.htmlspecialchars($fileName));
        }
        $template = self::getSubpart($templateCode, $subpart);
        if (!$template) {
            throw new Exception('Subpart not found! File: '.htmlspecialchars($file).' Subpart: '.htmlspecialchars($subpart));
        }

        return $template;
    }

    /**
     * check the template for includes.
     *
     * Examples: (the @ seperates the file from the subpart)
     *     <!--
     *         Subtemplate fuer dam einbinden
     *         ###INCLUDE_TEMPLATE typo3conf/ext/rn_base/res/simplegallery.html@DAM_IMAGES###
     *         und eingebunden
     *     -->
     *     or
     *     <!-- ### INCLUDE_TEMPLATE EXT:rn_base/res/simplegallery.html@DAM_IMAGES ### -->
     *
     * @param string $template
     *
     * @return string
     */
    public static function includeSubTemplates($template)
    {
        // nothing to do, if we dont have a template
        if (empty($template)) {
            return $template;
        }

        $cache = $included = false;

        if (!TYPO3::getTSFE()->no_cache) {
            $cache = tx_rnbase_cache_Manager::getCache('rnbase');
            $cacheKey = 'includeSubTemplateFor_'.md5($template);
            $included = $cache->get($cacheKey);
        }

        // search and replace the subparts
        if (empty($included)) {
            $included = preg_replace_callback(
                '!\<\!--[a-zA-Z0-9_ \s]*###[ ]*INCLUDE_TEMPLATE([^###]*)\###[a-zA-Z0-9_ \s]*-->!is',
                [self::class, 'cbIncludeSubTemplates'],
                $template
            );
            // store the template in the cache
            if ($cache) {
                $cache->set($cacheKey, $included);
            }
        }

        return $included;
    }

    /**
     * This callback is called by the includeSubTemplates preg_replace.
     *
     * @param array $match
     *
     * @return string
     */
    public static function cbIncludeSubTemplates($match)
    {
        list($filePath, $subPart) = tx_rnbase_util_Strings::trimExplode('@', $match[1]);

        try {
            $fileContent = self::getSubpartFromFile(
                $filePath,
                '###'.strtoupper($subPart).'###'
            );
        } catch (Exception $e) {
            $fileContent = '<!-- '.$e->getMessage().' -->';
        }

        return $fileContent;
    }

    /**
     * Multi substitution function with caching.
     *
     * This function should be a one-stop substitution function for working with HTML-template. It does not substitute by str_replace but by splitting.
     * This secures that the value inserted does not themselves contain markers or subparts.
     * This function takes three kinds of substitutions in one:
     * $markContentArray is a regular marker-array where the 'keys' are substituted in $content with their values
     * $subpartContentArray works exactly like markContentArray only is whole subparts substituted and not only a single marker.
     * $wrappedSubpartContentArray is an array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating.
     *
     * @param   string      the content stream, typically HTML template content
     * @param array       Regular marker-array where the 'keys' are substituted in $content with their values
     * @param   array       exactly like markContentArray only is whole subparts substituted and not only a single marker
     * @param   array       an array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating
     *
     * @return string The output content stream
     *
     * @see substituteSubpart(), substituteMarker(), substituteMarkerInObject(), TEMPLATE()
     */
    public function substituteMarkerArrayCached_old($content, $markContentArray = [], $subpartContentArray = [], $wrappedSubpartContentArray = [])
    {
        tx_rnbase_util_Misc::pushTT('substituteMarkerArray');

        // If not arrays then set them
        if (!is_array($markContentArray)) {
            $markContentArray = [];
        }    // Plain markers
        if (!is_array($subpartContentArray)) {
            $subpartContentArray = [];
        }    // Subparts being directly substituted
        if (!is_array($wrappedSubpartContentArray)) {
            $wrappedSubpartContentArray = [];
        }    // Subparts being wrapped

        // Finding keys and check hash:
        $sPkeys = array_keys($subpartContentArray);
        $wPkeys = array_keys($wrappedSubpartContentArray);
        $aKeys = array_merge(array_keys($markContentArray), $sPkeys, $wPkeys);
        if (!count($aKeys)) {
            tx_rnbase_util_Misc::pullTT();

            return $content;
        }
        asort($aKeys);
        $storeKey = md5('substituteMarkerArrayCached_storeKey:'.serialize([$content, $aKeys]));

        if (self::$substMarkerCache[$storeKey]) {
            $storeArr = self::$substMarkerCache[$storeKey];
            $GLOBALS['TT']->setTSlogMessage('Cached', 0);
        } else {
            $storeArrDat = TYPO3::getSysPage()->getHash($storeKey, 0);
            if (!isset($storeArrDat)) {
                // Initialize storeArr
                $storeArr = [];

                // Finding subparts and substituting them with the subpart as a marker
                foreach ($sPkeys as $sPK) {
                    $content = self::substituteSubpart($content, $sPK, $sPK);
                }

                // Finding subparts and wrapping them with markers
                foreach ($wPkeys as $wPK) {
                    $content = self::substituteSubpart($content, $wPK, [$wPK, $wPK]);
                }

                // traverse keys and quote them for reg ex.
                foreach ($aKeys as $tK => $tV) {
                    $aKeys[$tK] = quotemeta($tV);
                }
                $regex = implode('|', $aKeys);
                // Doing regex's
                $storeArr['c'] = explode($regex, $content);
                $keyList = [];
                preg_match_all('/'.$regex.'/', $content, $keyList);
                $storeArr['k'] = $keyList[0];
                // Setting cache:
                self::$substMarkerCache[$storeKey] = $storeArr;

                // Storing the cached data:
                $GLOBALS['TSFE']->sys_page->storeHash($storeKey, serialize($storeArr), 'substMarkArrayCached');

                $GLOBALS['TT']->setTSlogMessage('Parsing', 0);
            } else {
                // Unserializing
                $storeArr = unserialize($storeArrDat);
                // Setting cache:
                self::$substMarkerCache[$storeKey] = $storeArr;
                $GLOBALS['TT']->setTSlogMessage('Cached from DB', 0);
            }
        }

        // Substitution/Merging:
        // Merging content types together, resetting
        $valueArr = array_merge($markContentArray, $subpartContentArray, $wrappedSubpartContentArray);

        $wSCA_reg = [];
        $content = '';
        // traversin the keyList array and merging the static and dynamic content
        foreach ($storeArr['k'] as $n => $keyN) {
            $content .= $storeArr['c'][$n];
            if (!is_array($valueArr[$keyN])) {
                $content .= $valueArr[$keyN];
            } else {
                $content .= $valueArr[$keyN][(intval($wSCA_reg[$keyN]) % 2)];
                ++$wSCA_reg[$keyN];
            }
        }
        $content .= $storeArr['c'][count($storeArr['k'])];

        tx_rnbase_util_Misc::pullTT();

        return $content;
    }

    /**
     * Whether or not the caching in substituteMarkerArrayCached is enabled.
     *
     * @return bool
     */
    public static function isSubstCacheEnabled()
    {
        if (null === self::$substCacheEnabled) {
            self::$substCacheEnabled = (bool) Tx_Rnbase_Configuration_Processor::getExtensionCfgValue('rn_base', 'activateSubstCache');
        }

        return self::$substCacheEnabled;
    }

    /**
     * Enable caching in substituteMarkerArrayCached.
     */
    public static function enableSubstCache()
    {
        self::$substCacheEnabled = true;
    }

    /**
     * Disable caching in substituteMarkerArrayCached.
     */
    public static function disableSubstCache()
    {
        self::$substCacheEnabled = false;
    }

    /**
     * resets the cache configuration for substituteMarkerArrayCached.
     */
    public static function resetSubstCache()
    {
        self::$substCacheEnabled = null;
    }

    /**
     * Multi substitution function with caching.
     *
     * This function should be a one-stop substitution function for working with HTML-template. It does not substitute by str_replace but by splitting. This secures that the
     * value inserted does not themselves contain markers or subparts.
     * This function takes three kinds of substitutions in one:
     * $markContentArray is a regular marker-array where the 'keys' are substituted in $content with their values
     * $subpartContentArray works exactly like markContentArray only is whole subparts substituted and not only a single marker.
     * $wrappedSubpartContentArray is an array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating.
     *
     * @param   string      the content stream, typically HTML template content
     * @param array       Regular marker-array where the 'keys' are substituted in $content with their values
     * @param   array       exactly like markContentArray only is whole subparts substituted and not only a single marker
     * @param   array       an array of arrays with 0/1 keys where the subparts pointed to by the main key is wrapped with the 0/1 value alternating
     *
     * @return string The output content stream
     *
     * @see substituteSubpart(), substituteMarker(), substituteMarkerInObject(), TEMPLATE()
     */
    public static function substituteMarkerArrayCached($content, array $markContentArray = null, array $subpartContentArray = null, array $wrappedSubpartContentArray = null)
    {
        tx_rnbase_util_Misc::pushTT('substituteMarkerArray');

        // If not arrays then set them
        if (is_null($markContentArray)) {
            $markContentArray = [];
        }    // Plain markers
        if (is_null($subpartContentArray)) {
            $subpartContentArray = [];
        }    // Subparts being directly substituted
        if (is_null($wrappedSubpartContentArray)) {
            $wrappedSubpartContentArray = [];
        }    // Subparts being wrapped
        // Finding keys and check hash:
        $sPkeys = array_keys($subpartContentArray);
        $wPkeys = array_keys($wrappedSubpartContentArray);
        $mPKeys = array_keys($markContentArray);
        $aKeys = array_merge($mPKeys, $sPkeys, $wPkeys);
        if (!count($aKeys)) {
            tx_rnbase_util_Misc::pullTT();

            return $content;
        }
        asort($aKeys);

        asort($mPKeys);
        asort($sPkeys);
        asort($wPkeys);

        $storeKey = '';
        if (self::isSubstCacheEnabled()) {
            $storeKey = md5('substituteMarkerArrayCached_storeKey:'.serialize([$content, $mPKeys, $sPkeys, $wPkeys]));
        }

        if (self::isSubstCacheEnabled() && self::$substMarkerCache[$storeKey]) {
            $storeArr = self::$substMarkerCache[$storeKey];
        } else {
            if (self::isSubstCacheEnabled()) {
                $storeArrDat = TYPO3::getSysPage()->getHash($storeKey);
            } else {
                $storeArrDat = null;
            }
            if (!self::isSubstCacheEnabled() || null === $storeArrDat) {
                // Initialize storeArr
                $storeArr = [];

                // Finding subparts and substituting them with the subpart as a marker
                foreach ($sPkeys as $sPK) {
                    $content = self::substituteSubpart($content, $sPK, $sPK);
                }

                // Finding subparts and wrapping them with markers
                foreach ($wPkeys as $wPK) {
                    $content = self::substituteSubpart($content, $wPK, [$wPK, $wPK]);
                }

                // traverse keys and quote them for reg ex.
                foreach ($aKeys as $tK => $tV) {
                    $aKeys[$tK] = preg_quote($tV, '/');
                }
                $regex = '/'.implode('|', $aKeys).'/';
                // Doing regex's
                $storeArr['c'] = preg_split($regex, $content);
                $keyList = [];
                preg_match_all($regex, $content, $keyList);
                $storeArr['k'] = $keyList[0];

                if (self::isSubstCacheEnabled()) {
                    // Setting cache:
                    self::$substMarkerCache[$storeKey] = $storeArr;

                    // Storing the cached data:
                    TYPO3::getSysPage()->storeHash($storeKey, serialize($storeArr), 'substMarkArrayCached');
                }
            } else {
                // Unserializing
                $storeArr = unserialize($storeArrDat);
                // Setting cache:
                self::$substMarkerCache[$storeKey] = $storeArr;
            }
        }

        // Substitution/Merging:
        // Merging content types together, resetting
        $valueArr = array_merge($markContentArray, $subpartContentArray, $wrappedSubpartContentArray);

        $wSCA_reg = [];
        $content = '';
        // traversing the keyList array and merging the static and dynamic content
        foreach ($storeArr['k'] as $n => $keyN) {
            $content .= $storeArr['c'][$n];
            if (!is_array($valueArr[$keyN])) {
                $content .= $valueArr[$keyN];
            } else {
                $content .= $valueArr[$keyN][(intval($wSCA_reg[$keyN]) % 2)];
                ++$wSCA_reg[$keyN];
            }
        }
        $content .= $storeArr['c'][count($storeArr['k'])];

        tx_rnbase_util_Misc::pullTT();

        return $content;
    }

    /**
     * Substitute subpart in input template stream.
     * This function substitutes a subpart in $content with the content of $subpartContent.
     * Wrapper for t3lib_parsehtml::substituteSubpart which behaves identical.
     *
     * @param   string      the content stream, typically HTML template content
     * @param   string      The marker string, typically on the form "###[the marker string]###"
     * @param   mixed       The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the
     *                              HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around
     *                              the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
     * @param bool     If $recursive is set, the function calls itself with the content set to the remaining part of the content after the second
     *                               marker. This means that proceding subparts are ALSO substituted!
     *
     * @return string the processed HTML content string
     *
     * @see getSubpart(), t3lib_parsehtml::substituteSubpart()
     */
    public static function substituteSubpart($content, $marker, $subpartContent, $recursive = 1)
    {
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $parser = tx_rnbase::makeInstance(
                'TYPO3\\CMS\\Core\\Service\\MarkerBasedTemplateService'
            );

            return $parser->substituteSubpart($content, $marker, $subpartContent, $recursive);
        } else {
            $parser = tx_rnbase_util_Typo3Classes::getHtmlParserClass();

            return $parser::substituteSubpart($content, $marker, $subpartContent, $recursive);
        }
    }
}
