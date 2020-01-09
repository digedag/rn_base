<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2016 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Files');

/**
 * Wrapper for language usage.
 *
 * @author Rene Nitzsche
 */
class tx_rnbase_util_Lang
{
    protected $LOCAL_LANG = array();

    protected $LOCAL_LANG_charset = array();

    /**
     * Load a local lang from a file.
     * merging with the existing local lang.
     *
     * @param string $filename
     */
    public function loadLLFile($filename)
    {
        if (!$filename) {
            return;
        }

        // Find language file
        $basePath = tx_rnbase_util_Files::getFileAbsFileName($filename);
        // php or xml as source: In any case the charset will be that of the system language.
        // However, this function guarantees only return output for default language plus the specified language (which is different from how 3.7.0 dealt with it)
        self::addLang(self::readLLfile($basePath, self::getLLKey(), $GLOBALS['TSFE']->renderCharset));
        if ($llKey = self::getLLKey(true)) {
            self::addLang(self::readLLfile($basePath, $llKey, $GLOBALS['TSFE']->renderCharset));
        }
    }

    /**
     * Returns parsed data from a given file and language key.
     *
     * @param string $fileReference
     * @param string $languageKey
     * @param string $charset
     * @param int    $errorMode
     * @param bool   $isLocalizationOverride
     *
     * @return bool
     */
    public function readLLfile(
        $fileReference,
        $languageKey,
        $charset = '',
        $errorMode = 0,
        $isLocalizationOverride = false
    ) {
        if (tx_rnbase_util_TYPO3::isTYPO74OrHigher()) {
            /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
            $languageFactory = tx_rnbase::makeInstance(
                'TYPO3\\CMS\\Core\\Localization\\LocalizationFactory'
            );

            return $languageFactory->getParsedData(
                $fileReference,
                $languageKey,
                $charset,
                $errorMode,
                $isLocalizationOverride
            );
        }

        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::readLLfile($fileReference, $languageKey, $charset);
    }

    /**
     * Load local lang from TS. exsting local lang
     * is enhanced/overlayed.
     *
     * @param array $langArr
     */
    public function loadLLTs($langArr)
    {
        if (tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
            $this->loadLLOverlay46($langArr);
        } else {
            $this->loadLLOverlay($langArr);
        }
    }

    /**
     * Get the configured language.
     *
     * @param bool $alt
     *
     * @return string
     */
    protected function getLLKey($alt = false)
    {
        $ret = $GLOBALS['TSFE']->config['config'][$alt ? 'language_alt' : 'language'];

        return $ret ? $ret : ($alt ? '' : 'default');
    }

    /**
     * Add a new local lang array from Typoscript _LOCAL_LANG. Merged with existing local lang.
     *
     * @param array $langArr
     */
    protected function addLang($langArr)
    {
        if (!is_array($langArr)) {
            return;
        }
        //new values from the given array are added to the existing local lang.
        //existing values in the local lang are overruled with those of the given array.
        tx_rnbase::load('tx_rnbase_util_Arrays');
        $this->LOCAL_LANG = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            is_array($this->LOCAL_LANG) ? $this->LOCAL_LANG : array(),
            $langArr
        );
    }

    /**
     * Loads local language file for frontend rendering if defined in configuration.
     * Also locallang values from TypoScript property "_LOCAL_LANG" are merged onto the
     * values. This is a reimplementation from tslib_pibase::pi_loadLL().
     */
    protected function loadLLOverlay($langArr)
    {
        if (!is_array($langArr)) {
            return;
        }
        foreach ($langArr as $k => $lA) {
            if (is_array($lA)) {
                $k = substr($k, 0, -1);
                foreach ($lA as $llK => $llV) {
                    if (!is_array($llV)) {
                        $this->LOCAL_LANG[$k][$llK] = $llV;
                        if ('default' != $k) {
                            // For labels coming from the TypoScript (database) the charset is assumed to
                            // be "forceCharset" and if that is not set, assumed to be that of the individual
                            // system languages (thus no conversion)
                            $this->LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
                        }
                    }
                }
            }
        }
    }

    /**
     * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($this->scriptRelPath) and if found includes it.
     * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.php" file.
     */
    protected function loadLLOverlay46($confLL)
    {
        if (!is_array($confLL)) {
            return;
        }
        foreach ($confLL as $languageKey => $languageArray) {
            // Don't process label if the langue is not loaded
            $languageKey = substr($languageKey, 0, -1);
            if (is_array($languageArray) && is_array($this->LOCAL_LANG[$languageKey])) {
                // Remove the dot after the language key
                foreach ($languageArray as $labelKey => $labelValue) {
                    if (!is_array($labelValue)) {
                        $this->LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;
                        // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset"
                        // and if that is not set, assumed to be that of the individual system languages
                        if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
                            $this->LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
                        } else {
                            $this->LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TSFE']->csConvObj->charSetArray[$languageKey];
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the localized label of the LOCAL_LANG key.
     * This is a reimplementation from tslib_pibase::pi_getLL().
     *
     * @param string $key
     * @param string $alt
     * @param string $hsc
     * @param string $labelDebug
     *
     * @return string
     */
    public function getLL($key, $alt = '', $hsc = false, $labelDebug = false)
    {
        $label = tx_rnbase_util_TYPO3::isTYPO46OrHigher() ? $this->getLL46($key, $alt, $hsc) : $this->getLL40($key, $alt, $hsc);
        if ($labelDebug) {
            $options = array();
            if ('html' !== $labelDebug) {
                $options['plain'] = true;
            }
            $label = tx_rnbase_util_Debug::wrapDebugInfo(
                $label,
                strtolower($key),
                $options
            );
        }

        return $label;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key.
     * This is a reimplementation from tslib_pibase::pi_getLL().
     *
     * @param string $key
     * @param string $alternativeLabel
     * @param string $hsc
     *
     * @return string
     */
    private function getLL46($key, $alternativeLabel = '', $hsc = false)
    {
        // support for LLL: syntax
        if (!strcmp(substr($key, 0, 4), 'LLL:')) {
            return self::sL($key);
        }
        if (isset($this->LOCAL_LANG[$this->getLLKey()][$key][0]['target'])) {
            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (isset($this->LOCAL_LANG_charset[$this->getLLKey()][$key])) {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$this->getLLKey()][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$this->getLLKey()][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->getLLKey()][$key][0]['target'];
            }
        } elseif ($this->getLLKey(true) && isset($this->LOCAL_LANG[$this->getLLKey(true)][$key][0]['target'])) {
            // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
            if (isset($this->LOCAL_LANG_charset[$this->getLLKey(true)][$key])) {
                $word = $GLOBALS['TSFE']->csConv(
                    $this->LOCAL_LANG[$this->getLLKey(true)][$key][0]['target'],
                    $this->LOCAL_LANG_charset[$this->getLLKey(true)][$key]
                );
            } else {
                $word = $this->LOCAL_LANG[$this->getLLKey(true)][$key][0]['target'];
            }
        } elseif (isset($this->LOCAL_LANG['default'][$key][0]['target'])) {
            // Get default translation (without charset conversion, english)
            $word = $this->LOCAL_LANG['default'][$key][0]['target'];
        } else {
            // Im BE die LANG fragen...
            // Das $alternativeLabel wirkt nicht, weil $GLOBALS['LANG'] immer gesetzt ist...
            $word = is_object($GLOBALS['LANG']) ? $GLOBALS['LANG']->getLL($key) : $alternativeLabel;
        }

        $output = (isset($this->LLtestPrefix)) ? $this->LLtestPrefix.$word : $word;

        if ($hsc) {
            $output = htmlspecialchars($output);
        }

        return $output;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key.
     * This is a reimplementation from tslib_pibase::pi_getLL().
     *
     * @param string $key
     * @param string $alt
     * @param string $hsc
     *
     * @return string
     */
    private function getLL40($key, $alt = '', $hsc = false)
    {
        if (!strcmp(substr($key, 0, 4), 'LLL:')) {
            return self::sL($key);
        }

        if (isset($this->LOCAL_LANG[$this->getLLKey()][$key])) {
            $tsfe = tx_rnbase_util_TYPO3::getTSFE();
            // The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
            $word = $tsfe->csConv($this->LOCAL_LANG[$this->getLLKey()][$key], $this->LOCAL_LANG_charset[$this->getLLKey()][$key]);
        } elseif ($this->getLLKey(true) && isset($this->LOCAL_LANG[$this->getLLKey(true)][$key])) {
            tx_rnbase_util_TYPO3::getTSFE();
            // The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
            $word = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->getLLKey(true)][$key], $this->LOCAL_LANG_charset[$this->getLLKey(true)][$key]);
        } elseif (isset($this->LOCAL_LANG['default'][$key])) {
            // No charset conversion because default is english and thereby ASCII
            $word = $this->LOCAL_LANG['default'][$key];
        } else {
            // Im BE die LANG fragen...
            $word = is_object($GLOBALS['LANG']) ? $GLOBALS['LANG']->getLL($key) : '';
            if (!$word) {
                $word = $this->LLtestPrefixAlt.$alt;
            }
        }

        $output = $this->LLtestPrefix.$word;
        if ($hsc) {
            $output = htmlspecialchars($output);
        }

        return $output;
    }

    /**
     * Split Label function.
     *
     * @param string $key Key string. Accepts the "LLL:" prefix.
     *
     * @return string label value, if any
     */
    public static function sL($key)
    {
        /* @var $lang TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
        /* @var $lang TYPO3\CMS\Lang\LanguageService */
        $lang = $GLOBALS[TYPO3_MODE == 'BE' ? 'LANG' : 'TSFE'];

        if (!$lang) {
            throw new Exception('Languageservice for "'.TYPO3_MODE.'" not initialized yet.');
        }

        return $lang->sL($key);
    }
}
