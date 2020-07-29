<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2016 Rene Nitzsche
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
 * Get a class name independent of the TYPO3 Version. The API
 * of the desired class should be the same.
 *
 * @author Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_Typo3Classes
{
    const LOWER6 = 'lower6';

    const HIGHER6 = 'higher6';

    /**
     * @return string|TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public static function getFlashMessageClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_FlashMessage',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Backend\Form\FormEngine
     */
    public static function getBackendFormEngineClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_tceforms',
            self::HIGHER6 => 'TYPO3\\CMS\\Backend\\Form\\FormEngine',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Utility\File\BasicFileUtility
     */
    public static function getBasicFileUtilityClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_basicFileFunctions',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
     */
    public static function getExtendedTypoScriptTemplateServiceClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_tsparser_ext',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public static function getContentObjectRendererClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'tslib_cObj',
            self::HIGHER6 => 'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public static function getTypoScriptFrontendControllerClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'tslib_fe',
            self::HIGHER6 => 'TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    public static function getFrontendUserAuthenticationClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'tslib_feUserAuth',
            self::HIGHER6 => 'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Charset\CharsetConverter
     */
    public static function getCharsetConverterClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_cs',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Charset\\CharsetConverter',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public static function getDataHandlerClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_tcemain',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\DataHandling\\DataHandler',
        ]);
    }

    /**
     * @return string|TYPO3\CMSBackend\Sprite\SpriteManager
     */
    public static function getSpriteManagerClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_SpriteManager',
            self::HIGHER6 => 'TYPO3\\CMS\Backend\\Sprite\\SpriteManager',
        ]);
    }

    /**
     * @return \TYPO3\CMS\Core\TimeTracker\TimeTracker
     */
    public static function getTimeTracker($enabled = null)
    {
        if (null === $enabled) {
            $beCookie = trim($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']) ?: 'be_typo_user';
            $enabled = (bool) $_COOKIE[$beCookie];
        }

        // for typo3 6 or 7 we has to initialise a NullTimeTracker if tracker is disabled
        if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            if ($enabled) {
                return new TYPO3\CMS\Core\TimeTracker\TimeTracker();
            }

            return new TYPO3\CMS\Core\TimeTracker\NullTimeTracker();
        }

        return new TYPO3\CMS\Core\TimeTracker\TimeTracker($enabled);
    }

    /**
     * @deprecated use getTimeTracker
     *
     * @return string|TYPO3\CMS\Core\TimeTracker\NullTimeTracker
     */
    public static function getTimeTrackClass()
    {
        $higher6Class = 'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker';
        if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
            $beCookie = trim($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']) ?: 'be_typo_user';
            if ($_COOKIE[$beCookie]) {
                $higher6Class = 'TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker';
            }
        }

        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_timeTrack',
            self::HIGHER6 => $higher6Class,
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Utility\CommandUtility
     */
    public static function getCommandUtilityClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_exec',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Utility\\CommandUtility',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Mail\MailMessage
     */
    public static function getMailMessageClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_mail_Message',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Mail\\MailMessage',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Html\HtmlParser
     */
    public static function getHtmlParserClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_parsehtml',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Html\\HtmlParser',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Utility\GeneralUtility
     *
     * @see Tx_Rnbase_Utility_T3General for better usage
     */
    public static function getGeneralUtilityClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_div',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser
     */
    public static function getTypoScriptParserClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_TSparser',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public static function getDocumentTemplateClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'template',
            self::HIGHER6 => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\TypoScript\TemplateService
     */
    public static function getTemplateServiceClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_TStemplate',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\TypoScript\\TemplateService',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Utility\HttpUtility
     */
    public static function getHttpUtilityClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_utility_Http',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Utility\\HttpUtility',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public static function getMediumDocumentTemplateClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'mediumDoc',
            self::HIGHER6 => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser
     */
    public static function getLocalizationParserClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_l10n_parser_Llxml',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Localization\\Parser\\LocallangXmlParser',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
     */
    public static function getAbstractUserAuthenticationClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_userAuth',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Authentication\\AbstractUserAuthentication',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Backend\Rte\AbstractRte
     */
    public static function getAbstractRteClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_rteapi',
            self::HIGHER6 => 'TYPO3\\CMS\\Backend\\Rte\\AbstractRte',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Database\SqlParser
     */
    public static function getSqlParserClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_sqlparser',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Database\\SqlParser',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Backend\FrontendBackendUserAuthentication
     */
    public static function getFrontendBackendUserAuthenticationClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_tsfeBeUserAuth',
            self::HIGHER6 => 'TYPO3\\CMS\\Backend\\FrontendBackendUserAuthentication',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    public static function getBackendUserAuthenticationClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 't3lib_beUserAuth',
            self::HIGHER6 => 'TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Frontend\Utility\EidUtility
     */
    public static function getEidUtilityClass()
    {
        return self::getClassByCurrentTypo3Version([
            self::LOWER6 => 'tslib_eidtools',
            self::HIGHER6 => 'TYPO3\\CMS\\Frontend\\Utility\\EidUtility',
        ]);
    }

    /**
     * @return string|TYPO3\CMS\Core\Cache\CacheManager
     */
    public static function getCacheManagerClass()
    {
        return 'TYPO3\\CMS\\Core\\Cache\\CacheManager';
    }

    /**
     * @return string
     */
    protected static function getClassByCurrentTypo3Version(array $possibleClasses)
    {
        return tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
            $possibleClasses[self::HIGHER6] : $possibleClasses[self::LOWER6];
    }
}
