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
     * @return class-string<\TYPO3\CMS\Core\Messaging\FlashMessage>
     */
    public static function getFlashMessageClass()
    {
        return \TYPO3\CMS\Core\Messaging\FlashMessage::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Backend\Form\FormEngine>
     */
    public static function getBackendFormEngineClass()
    {
        return 'TYPO3\\CMS\\Backend\\Form\\FormEngine';
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Utility\File\BasicFileUtility>
     */
    public static function getBasicFileUtilityClass()
    {
        return \TYPO3\CMS\Core\Utility\File\BasicFileUtility::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\TypoScript\ExtendedTemplateService>
     */
    public static function getExtendedTypoScriptTemplateServiceClass()
    {
        return \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer>
     */
    public static function getContentObjectRendererClass()
    {
        return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController>
     */
    public static function getTypoScriptFrontendControllerClass()
    {
        return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication>
     */
    public static function getFrontendUserAuthenticationClass()
    {
        return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Charset\CharsetConverter>
     */
    public static function getCharsetConverterClass()
    {
        return \TYPO3\CMS\Core\Charset\CharsetConverter::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\DataHandling\DataHandler>
     */
    public static function getDataHandlerClass()
    {
        return \TYPO3\CMS\Core\DataHandling\DataHandler::class;
    }

    /**
     * @return class-string<\TYPO3\CMSBackend\Sprite\SpriteManager>
     */
    public static function getSpriteManagerClass()
    {
        return 'TYPO3\\CMS\\Backend\\Sprite\\SpriteManager';
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
     * @return class-string<\TYPO3\CMS\Core\TimeTracker\NullTimeTracker>
     */
    public static function getTimeTrackClass()
    {
        $higher6Class = 'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker';
        $beCookie = trim($GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']) ?: 'be_typo_user';
        if ($_COOKIE[$beCookie]) {
            $higher6Class = \TYPO3\CMS\Core\TimeTracker\TimeTracker::class;
        }

        return $higher6Class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Utility\CommandUtility>
     */
    public static function getCommandUtilityClass()
    {
        return \TYPO3\CMS\Core\Utility\CommandUtility::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Mail\MailMessage>
     */
    public static function getMailMessageClass()
    {
        return \TYPO3\CMS\Core\Mail\MailMessage::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Html\HtmlParser>
     */
    public static function getHtmlParserClass()
    {
        return \TYPO3\CMS\Core\Html\HtmlParser::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Utility\GeneralUtility>
     *
     * @see T3General for better usage
     */
    public static function getGeneralUtilityClass()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser>
     */
    public static function getTypoScriptParserClass()
    {
        return \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Backend\Template\DocumentTemplate>
     */
    public static function getDocumentTemplateClass()
    {
        return \TYPO3\CMS\Backend\Template\DocumentTemplate::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\TypoScript\TemplateService>
     */
    public static function getTemplateServiceClass()
    {
        return \TYPO3\CMS\Core\TypoScript\TemplateService::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Utility\HttpUtility>
     */
    public static function getHttpUtilityClass()
    {
        return \TYPO3\CMS\Core\Utility\HttpUtility::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Backend\Template\DocumentTemplate>
     */
    public static function getMediumDocumentTemplateClass()
    {
        return \TYPO3\CMS\Backend\Template\DocumentTemplate::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser>
     */
    public static function getLocalizationParserClass()
    {
        return \TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Authentication\AbstractUserAuthentication>
     */
    public static function getAbstractUserAuthenticationClass()
    {
        return \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Backend\Rte\AbstractRte>
     */
    public static function getAbstractRteClass()
    {
        return 'TYPO3\\CMS\\Backend\\Rte\\AbstractRte';
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Database\SqlParser>
     */
    public static function getSqlParserClass()
    {
        return 'TYPO3\\CMS\\Core\\Database\\SqlParser';
    }

    /**
     * @return class-string<\TYPO3\CMS\Backend\FrontendBackendUserAuthentication>
     */
    public static function getFrontendBackendUserAuthenticationClass()
    {
        return \TYPO3\CMS\Backend\FrontendBackendUserAuthentication::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Authentication\BackendUserAuthentication>
     */
    public static function getBackendUserAuthenticationClass()
    {
        return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class;
    }

    /**
     * @return class-string<\TYPO3\CMS\Frontend\Utility\EidUtility>
     */
    public static function getEidUtilityClass()
    {
        return 'TYPO3\\CMS\\Frontend\\Utility\\EidUtility';
    }

    /**
     * @return class-string<\TYPO3\CMS\Core\Cache\CacheManager>
     */
    public static function getCacheManagerClass()
    {
        return \TYPO3\CMS\Core\Cache\CacheManager::class;
    }

    /**
     * @param array<string, class-string> $possibleClasses
     *
     * @return class-string
     */
    protected static function getClassByCurrentTypo3Version(array $possibleClasses)
    {
        return TYPO3::isTYPO62OrHigher() ?
            $possibleClasses[self::HIGHER6] : $possibleClasses[self::LOWER6];
    }
}
