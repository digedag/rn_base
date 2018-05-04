<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2014 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_Extensions');

/**
 * Statische Informationen über TYPO3
 */
class tx_rnbase_util_TYPO3
{

    /**
     * Prüft, ob mindestens TYPO3 Version 6.0 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO60OrHigher()
    {
        return true;
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 6.1 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO61OrHigher()
    {
        return true;
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 6.2 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO62OrHigher()
    {
        return true;
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 7.0 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO70OrHigher()
    {
        return self::isTYPO3VersionOrHigher(7000000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 7.6 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO76OrHigher()
    {
        return self::isTYPO3VersionOrHigher(7006000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 8 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO80OrHigher()
    {
        return self::isTYPO3VersionOrHigher(8000000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 8.6 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO86OrHigher()
    {
        return self::isTYPO3VersionOrHigher(8006000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 8.7 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO87OrHigher()
    {
        return self::isTYPO3VersionOrHigher(8007000);
    }

    /**
     * Prüft, ob mindestens TYPO3 Version 9 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO90OrHigher()
    {
        return self::isTYPO3VersionOrHigher(9000000);
    }

    /**
     * Prüft, ob mindestens TYPO3 Version 4.7 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO47OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4007000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 4.6 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO46OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4006000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 4.5 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO45OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4005000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 4.4 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO44OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4004000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 4.3 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO43OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4003000);
    }
    /**
     * Prüft, ob mindestens TYPO3 Version 4.2 vorhanden ist.
     *
     * @return bool
     */
    public static function isTYPO42OrHigher()
    {
        return self::isTYPO3VersionOrHigher(4002000);
    }
    private static $TYPO3_VERSION = false;
    /**
     * Prüft, ob eine bestimmte TYPO3 Version vorhanden ist.
     *
     * @param int $versionNumber
     * @return bool
     */
    public static function isTYPO3VersionOrHigher($version)
    {
        if (self::$TYPO3_VERSION === false) {
            self::$TYPO3_VERSION = self::convertVersionNumberToInteger(TYPO3_version);
        }

        return self::$TYPO3_VERSION >= $version;
    }
    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     * This method is taken from t3lib_utility_VersionNumber.
     *
     * @param $versionNumber string Version number on format x.x.x
     * @return int Integer version of version number (where each part can count to 999)
     */
    public static function convertVersionNumberToInteger($versionNumber)
    {
        $versionParts = explode('.', $versionNumber);

        return intval((int) $versionParts[0] . str_pad((int) $versionParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int) $versionParts[2], 3, '0', STR_PAD_LEFT));
    }

    /**
     * Liefert das EM_CONF-Array einer Extension
     *
     * @param string $extKey
     * @return array
     */
    public static function loadExtInfo($_EXTKEY)
    {
        $path = tx_rnbase_util_Extensions::extPath($_EXTKEY).'ext_emconf.php';
        @include($path);
        if (is_array($EM_CONF[$_EXTKEY])) {
            return $EM_CONF[$_EXTKEY];
        }

        return array();
    }
    /**
     * Wrapper function for tx_rnbase_util_Extensions::isLoaded()
     * @param string $_EXTKEY
     */
    public static function isExtLoaded($_EXTKEY)
    {
        return tx_rnbase_util_Extensions::isLoaded($_EXTKEY);
    }
    /**
     * Liefert die Versionsnummer einer Extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getExtVersion($extKey)
    {
        $info = self::loadExtInfo($extKey);

        return $info['version'];
    }
    /**
     * Prüft, ob die Extension mindestens auf einer bestimmten Version steht
     * @param string $_EXTKEY
     * @param int $version
     * @return bool
     */
    public static function isExtMinVersion($_EXTKEY, $version)
    {
        if (!self::isExtLoaded($_EXTKEY)) {
            return false;
        }

        return intval($version) <= self::convertVersionNumberToInteger(self::getExtVersion($_EXTKEY));
    }

    /**
     * Get the current frontend user
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication or tslib_feUserAuth current frontend user.
     */
    public static function getFEUser()
    {
        return $GLOBALS['TSFE']->fe_user;
    }
    /**
     * Get the current frontend user uid
     *
     * @return int current frontend user uid or FALSE
     */
    public static function getFEUserUID()
    {
        $feuser = self::getFEUser();

        return is_object($feuser) && isset($feuser->user['uid']) ? $feuser->user['uid'] : false;
    }
    /**
     * Get the current backend user if available
     * @return t3lib_tsfeBeUserAuth
     */
    public static function getBEUser()
    {
        return $GLOBALS['BE_USER'];
    }
    /**
     * Get the current backend user uid if available
     * @return int
     */
    public static function getBEUserUID()
    {
        $beuser = self::getBEUser();

        return is_object($beuser) && isset($beuser->user['uid']) ? $beuser->user['uid'] : false;
    }

    /**
     * Creates a new instance of the cobject
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer|tslib_cObj
     */
    public static function getContentObject()
    {
        return tx_rnbase::makeInstance(
            tx_rnbase_util_Typo3Classes::getContentObjectRendererClass()
        );
    }

    /**
     * Returns TSFE.
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController or tslib_fe
     */
    public static function getTSFE()
    {
        if (!is_object($GLOBALS['TSFE'])) {
            tx_rnbase::load('tx_rnbase_util_Misc');
            tx_rnbase_util_Misc::prepareTSFE();
        }

        return $GLOBALS['TSFE'];
    }
    /**
     * Returns the Page renderer instance
     *
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    public static function getPageRenderer()
    {
        if (self::isTYPO80OrHigher()) {
            return tx_rnbase::makeInstance(
                'TYPO3\\CMS\\Core\\Page\\PageRenderer'
            );
        }

        return self::getTSFE()->getPageRenderer();
    }

    private static $sysPage = null;
    /**
     * @return \TYPO3\CMS\Frontend\Page\PageRepository or t3lib_pageSelect
     */
    public static function getSysPage()
    {
        if (!is_object(self::$sysPage)) {
            if (is_object($GLOBALS['TSFE']->sys_page)) {
                self::$sysPage = $GLOBALS['TSFE']->sys_page;
            } // Use existing SysPage from TSFE
            else {
                self::$sysPage = tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
                self::$sysPage->init(0);
            }
        }

        return self::$sysPage;
    }

    /**
     * wrapper Methode mit Abhängigkeit von TYPO3 Version
     *
     * @return string
     * @deprecated use tx_rnbase_util_Typo3Classes::getHttpUtilityClass
     */
    public static function getHttpUtilityClass()
    {
        return tx_rnbase_util_Typo3Classes::getHttpUtilityClass();
    }

    /**
     * @return boolean
     */
    public static function isCliMode()
    {
        if (self::isTYPO80OrHigher()) {
            $isCliMode = TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI;
        } else {
            $isCliMode = defined('TYPO3_cliMode');
        }

        return $isCliMode;
    }
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TYPO3.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_TYPO3.php']);
}
