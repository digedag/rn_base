<?php
namespace Sys25\RnBase\Fluid\ViewHelper\PageBrowser;

use Sys25\RnBase\Fluid\ViewHelper\PageBrowserViewHelper;

/***************************************************************
 * Copyright notice
 *
 * (c) René Nitzsche <rene@system25.de>
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
 * Sys25\RnBase\Fluid\ViewHelper\PageBrowser$PageBaseViewHelper
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBaseViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Arguments initialization
     *
     * @return void
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('target', 'string', 'Target of link', false);
        $this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document', false);
        $this->registerArgument('data-tagname', 'string', 'Type of Tag to render', false, 'a');
        $this->registerArgument('pageUid', 'int', 'Target page. See TypoLink destination', false, null);
        $this->registerArgument(
            'additionalParams',
            'array',
            'Additional query parameters that won\'t be prefixed like $arguments (overrule $arguments)',
            false,
            []
        );
        $this->registerArgument('pageType', 'int', 'Type of the target page. See typolink.parameter', false, 0);
        $this->registerArgument(
            'noCache',
            'bool',
            'Set this to disable caching for the target page. You should not need this.',
            false,
            false
        );
        $this->registerArgument(
            'noCacheHash',
            'bool',
            'Set this to suppress the cHash query parameter created by TypoLink. You should not need this.',
            false,
            false
        );
        $this->registerArgument('section', 'string', 'The anchor to be added to the URI', false, '');
        $this->registerArgument(
            'linkAccessRestrictedPages',
            'bool',
            'If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.',
            false,
            false
        );
        $this->registerArgument('absolute', 'bool', 'If set, the URI of the rendered link is absolute', false, false);
        $this->registerArgument(
            'addQueryString',
            'bool',
            'If set, the current query parameters will be kept in the URI',
            false,
            false
        );
        $this->registerArgument(
            'argumentsToBeExcludedFromQueryString',
            'array',
            'Arguments to be removed from the URI. Only active if $addQueryString = TRUE',
            false,
            []
        );
        $this->registerArgument('usePageNumberAsLinkText', 'bool', 'Use page number as link text?', false, false);
    }

    /**
     * @return string Rendered page URI
     */
    public function render()
    {
        $pageUid = $this->arguments['pageUid'];
        $additionalParams = $this->arguments['additionalParams'];
        $pageType = $this->arguments['pageType'];
        $noCache = $this->arguments['noCache'];
        $noCacheHash = $this->arguments['noCacheHash'];
        $section = $this->arguments['section'];
        $linkAccessRestrictedPages = $this->arguments['linkAccessRestrictedPages'];
        $absolute = $this->arguments['absolute'];
        $addQueryString = $this->arguments['addQueryString'];
        $argumentsToBeExcludedFromQueryString = $this->arguments['argumentsToBeExcludedFromQueryString'];
        $usePageNumberAsLinkText = $this->arguments['usePageNumberAsLinkText'];

        $this->tag->setTagName($this->arguments['data-tagname']);
        $pageBrowser = $this->templateVariableContainer->offsetGet('pagebrowser');
        $currentPage = $this->templateVariableContainer->get('currentPage');

        $pageBrowserQualifier = $this->viewHelperVariableContainer->get(
            PageBrowserViewHelper::class, 'pageBrowserQualifier'
        );
        $pageBrowserParams = array(
            $pageBrowserQualifier => array(
                $pageBrowser->getParamName('pointer') => $currentPage,
            ),
        );
        $additionalParams = \tx_rnbase_util_Arrays::mergeRecursiveWithOverrule($additionalParams, $pageBrowserParams);

        $uriBuilder = $this->renderingContext->getControllerContext()->getUriBuilder();
        $uri = $uriBuilder
            ->reset()
            ->setTargetPageUid($pageUid)
            ->setTargetPageType($pageType)
            ->setNoCache($noCache)
            ->setUseCacheHash(!$noCacheHash)
            ->setSection($section)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri($absolute)
            ->setAddQueryString($addQueryString)
            ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
            ->build();

        $this->tag->addAttribute('href', $uri);

        if ($usePageNumberAsLinkText) {
            $this->tag->setContent($this->templateVariableContainer->get('pageNumber'));
        } else {
            $this->tag->setContent($this->renderChildren());
        }

        return $this->tag->render();
    }
}
