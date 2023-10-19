<?php

namespace Sys25\RnBase\Utility;

use tx_rnbase;
use TYPO3\CMS\Core\Context\Context;

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
 * Class FrontendControllerUtility.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class FrontendControllerUtility
{
    /**
     * @see https://forge.typo3.org/issues/85543
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85543-Language-relatedPropertiesInTypoScriptFrontendControllerAndPageRepository.html
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController
     *
     * @return int
     */
    public static function getLanguageContentId($typoScriptFrontendController)
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $languageContentId = tx_rnbase::makeInstance(Context::class)->getAspect('language')->getContentId();
        } else {
            $languageContentId = $typoScriptFrontendController->sys_language_content;
        }

        return $languageContentId;
    }

    /**
     * @see https://forge.typo3.org/issues/85543
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85543-Language-relatedPropertiesInTypoScriptFrontendControllerAndPageRepository.html
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController
     *
     * @return string
     */
    public static function getLanguageMode($typoScriptFrontendController)
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $languageMode = tx_rnbase::makeInstance(Context::class)->getAspect('language')->getLegacyLanguageMode();
        } else {
            $languageMode = $typoScriptFrontendController->sys_language_mode;
        }

        return $languageMode;
    }

    /**
     * @see https://forge.typo3.org/issues/85543
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85543-Language-relatedPropertiesInTypoScriptFrontendControllerAndPageRepository.html
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController
     *
     * @return int
     */
    public static function getLanguageId($typoScriptFrontendController)
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $languageId = tx_rnbase::makeInstance(Context::class)->getAspect('language')->getId();
        } else {
            $languageId = $typoScriptFrontendController->sys_language_uid;
        }

        return $languageId;
    }
}
