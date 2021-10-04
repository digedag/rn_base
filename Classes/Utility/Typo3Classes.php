<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2021 Rene Nitzsche
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
class Typo3Classes
{
    public const LOWER6 = 'lower6';

    public const HIGHER6 = 'higher6';

    /**
     * @return string|\TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public static function getFlashMessageClass()
    {
        return 'TYPO3\\CMS\\Core\\Messaging\\FlashMessage';
    }

    /**
     * @return string|\TYPO3\CMS\Backend\Form\FormEngine
     */
    public static function getBackendFormEngineClass()
    {
        return 'TYPO3\\CMS\\Backend\\Form\\FormEngine';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Utility\File\BasicFileUtility
     */
    public static function getBasicFileUtilityClass()
    {
        return 'TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility';
    }

    /**
     * @return string|\TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
     */
    public static function getExtendedTypoScriptTemplateServiceClass()
    {
        return 'TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService';
    }

    /**
     * @return string|\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public static function getContentObjectRendererClass()
    {
        return 'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer';
    }

    /**
     * @return string|\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public static function getTypoScriptFrontendControllerClass()
    {
        return 'TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController';
    }

    /**
     * @return string|\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    public static function getFrontendUserAuthenticationClass()
    {
        return 'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Charset\CharsetConverter
     */
    public static function getCharsetConverterClass()
    {
        return 'TYPO3\\CMS\\Core\\Charset\\CharsetConverter';
    }

    /**
     * @return string|\TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public static function getDataHandlerClass()
    {
        return 'TYPO3\\CMS\\Core\\DataHandling\\DataHandler';
    }

    /**
     * @return string|\TYPO3\CMSBackend\Sprite\SpriteManager
     */
    public static function getSpriteManagerClass()
    {
        return 'TYPO3\\CMS\Backend\\Sprite\\SpriteManager';
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
        if (!TYPO3::isTYPO80OrHigher()) {
            if ($enabled) {
                return new \TYPO3\CMS\Core\TimeTracker\TimeTracker();
            }

            return new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker();
        }

        return new \TYPO3\CMS\Core\TimeTracker\TimeTracker($enabled);
    }

    /**
     * @deprecated use getTimeTracker
     *
     * @return string|\TYPO3\CMS\Core\TimeTracker\NullTimeTracker
     */
    public static function getTimeTrackClass()
    {
        $higher6Class = 'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker';
        $beCookie = trim($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']) ?: 'be_typo_user';
        if ($_COOKIE[$beCookie]) {
            $higher6Class = 'TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker';
        }

        return $higher6Class;
    }

    /**
     * @return string|\TYPO3\CMS\Core\Utility\CommandUtility
     */
    public static function getCommandUtilityClass()
    {
        return 'TYPO3\\CMS\\Core\\Utility\\CommandUtility';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Mail\MailMessage
     */
    public static function getMailMessageClass()
    {
        return 'TYPO3\\CMS\\Core\\Mail\\MailMessage';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Html\HtmlParser
     */
    public static function getHtmlParserClass()
    {
        return 'TYPO3\\CMS\\Core\\Html\\HtmlParser';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Utility\GeneralUtility
     *
     * @see T3General for better usage
     */
    public static function getGeneralUtilityClass()
    {
        return 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
    }

    /**
     * @return string|\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser
     */
    public static function getTypoScriptParserClass()
    {
        return 'TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser';
    }

    /**
     * @return string|\TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public static function getDocumentTemplateClass()
    {
        return 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate';
    }

    /**
     * @return string|\TYPO3\CMS\Core\TypoScript\TemplateService
     */
    public static function getTemplateServiceClass()
    {
        return 'TYPO3\\CMS\\Core\\TypoScript\\TemplateService';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Utility\HttpUtility
     */
    public static function getHttpUtilityClass()
    {
        return 'TYPO3\\CMS\\Core\\Utility\\HttpUtility';
    }

    /**
     * @return string|\TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public static function getMediumDocumentTemplateClass()
    {
        return 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser
     */
    public static function getLocalizationParserClass()
    {
        return 'TYPO3\\CMS\\Core\\Localization\\Parser\\LocallangXmlParser';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
     */
    public static function getAbstractUserAuthenticationClass()
    {
        return 'TYPO3\\CMS\\Core\\Authentication\\AbstractUserAuthentication';
    }

    /**
     * @return string|\TYPO3\CMS\Backend\Rte\AbstractRte
     */
    public static function getAbstractRteClass()
    {
        return 'TYPO3\\CMS\\Backend\\Rte\\AbstractRte';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Database\SqlParser
     */
    public static function getSqlParserClass()
    {
        return 'TYPO3\\CMS\\Core\\Database\\SqlParser';
    }

    /**
     * @return string|\TYPO3\CMS\Backend\FrontendBackendUserAuthentication
     */
    public static function getFrontendBackendUserAuthenticationClass()
    {
        return 'TYPO3\\CMS\\Backend\\FrontendBackendUserAuthentication';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    public static function getBackendUserAuthenticationClass()
    {
        return 'TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication';
    }

    /**
     * @return string|\TYPO3\CMS\Frontend\Utility\EidUtility
     */
    public static function getEidUtilityClass()
    {
        return 'TYPO3\\CMS\\Frontend\\Utility\\EidUtility';
    }

    /**
     * @return string|\TYPO3\CMS\Core\Cache\CacheManager
     */
    public static function getCacheManagerClass()
    {
        return 'TYPO3\\CMS\\Core\\Cache\\CacheManager';
    }

    /**
     * @param array $possibleClasses
     *
     * @return string
     */
    protected static function getClassByCurrentTypo3Version(array $possibleClasses)
    {
        return TYPO3::isTYPO62OrHigher() ?
            $possibleClasses[self::HIGHER6] : $possibleClasses[self::LOWER6];
    }
}
