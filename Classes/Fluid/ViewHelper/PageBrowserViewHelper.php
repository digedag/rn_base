<?php

namespace Sys25\RnBase\Fluid\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

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
 * Sys25\RnBase\Fluid\ViewHelper$Tx_Mktegutfe_ViewHelpers_PageBrowserViewHelper.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBrowserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{
    /**
     * @var array
     */
    private $pdid;

    /**
     * @var unknown
     */
    private $pagePartsDef = ['normal', 'current', 'first', 'last', 'prev', 'next'];

    /**
     * @var \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
     */
    protected $renderingContext;

    /**
     * An array of \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode.
     *
     * @var array
     */
    protected $childNodes = [];

    /**
     * Setter for ChildNodes - as defined in ChildNodeAccessInterface.
     *
     * @param array $childNodes Child nodes of this syntax tree node
     *
     * @api
     */
    public function setChildNodes(array $childNodes)
    {
        $this->childNodes = $childNodes;
    }

    /**
     * Handles rn_base PageBrowser.
     *
     * @param bool   $hideIfSinglePage
     * @param int    $maxPages
     * @param mixed  $pageFloat        Das richtet den Ausschnitt der gezeigten Seiten im PageBrowser ein.
     * @param mixed  $implode          Seiten werden mittels dieses Trenners aufgelistet
     * @param string $qualifier        set this if you want to use another qualifier than the one defined in $configuration context
     *
     * @return string
     */
    public function render(
        $hideIfSinglePage = false,
        $maxPages = 10,
        $pageFloat = 'CENTER',
        $implode = ' ',
        $qualifier = null
    ) {
        if (!$this->templateVariableContainer->offsetExists('pagebrowser')) {
            return '';
        }

        if ($qualifier === null) {
            $qualifier = $this->controllerContext->configurations->getQualifier();
        }

        $this->viewHelperVariableContainer->add(self::class, 'pageBrowserQualifier', $qualifier);

        $pageBrowser = $this->templateVariableContainer->offsetGet('pagebrowser');
        $pointer = $pageBrowser->getPointer();
        $count = $pageBrowser->getListSize();
        $results_at_a_time = $pageBrowser->getPageSize();
        $totalPages = ceil($count / $results_at_a_time);

        if ($totalPages == 1 && $hideIfSinglePage) {
            return '';
        }

        $this->templateVariableContainer->add('count', $count);
        $this->templateVariableContainer->add('totalPages', $totalPages);

        $pageBrowserHtmlParts = $this->getPageBrowserHtmlParts(
            $pointer,
            $pageFloat,
            $maxPages,
            $totalPages
        );
        $result = implode($pageBrowserHtmlParts, $implode ? $implode : ' ');

        $this->templateVariableContainer->remove('count');
        $this->templateVariableContainer->remove('totalPages');

        $this->viewHelperVariableContainer->remove(self::class, 'pageBrowserQualifier');

        return $result;
    }

    /**
     * @param int    $pointer
     * @param string $pageFloat
     * @param int    $maxPages
     * @param int    $totalPages
     *
     * @return array[string]
     */
    private function getPageBrowserHtmlParts(
        $pointer,
        $pageFloat,
        $maxPages,
        $totalPages
    ) {
        $pageBrowserHtmlParts = [];

        if ($this->notOnFirstPage($pointer)) {
            $pageBrowserHtmlParts[] = $this->renderFirstPage(0);
            $pageBrowserHtmlParts[] = $this->renderPrevPage($pointer - 1);
        }

        $firstAndLastPage = $this->getFirstAndLastPage(
            $pointer,
            $pageFloat,
            $totalPages,
            $maxPages
        );
        $pageBrowserHtmlParts = array_merge(
            $pageBrowserHtmlParts,
            $this->getPageBrowserHtmlPartsFromFirstToLastPage(
                $pointer,
                $firstAndLastPage['first'],
                $firstAndLastPage['last']
            )
        );

        if ($this->notOnLastPage($pointer, $totalPages)) {
            $pageBrowserHtmlParts[] = $this->renderNextPage($pointer + 1);
            $pageBrowserHtmlParts[] = $this->renderLastPage($totalPages - 1);
        }

        return $pageBrowserHtmlParts;
    }

    /**
     * @param int $pointer
     *
     * @return bool
     */
    private function notOnFirstPage($pointer)
    {
        return $pointer > 0;
    }

    /**
     * @param int $pointer
     * @param int $totalPages
     *
     * @return bool
     */
    private function notOnLastPage($pointer, $totalPages)
    {
        //pointer beginnt bei 0, totalPages bei 1 (daher " -1")
        return $pointer < ($totalPages - 1);
    }

    /**
     * @param int $pointer
     * @param int $firstPage
     * @param int $lastPage
     *
     * @return array
     */
    private function getPageBrowserHtmlPartsFromFirstToLastPage(
        $pointer,
        $firstPage,
        $lastPage
    ) {
        $pageBrowserHtmlParts = [];
        for ($i = $firstPage; $i < $lastPage; $i++) {
            $pageId = ($i == $pointer) ? 'current' : 'normal';
            switch ($pageId) {
                case 'normal':
                    $pageBrowserHtmlParts[] = $this->renderNormalPage($i);
                    break;
                case 'current':
                    $pageBrowserHtmlParts[] = $this->renderCurrentPage($i);
            }
        }

        return $pageBrowserHtmlParts;
    }

    /**
     * Liefert den korrekten Wert für den PageFloat. Das richtet den Ausschnitt der gezeigten
     * Seiten im PageBrowser ein.
     */
    protected function getPageFloat($pageFloat, $maxPages)
    {
        if ($pageFloat) {
            if (strtoupper($pageFloat) == 'CENTER') {
                $pageFloat = ceil(($maxPages - 1) / 2);
            } else {
                $pageFloat = \tx_rnbase_util_Math::intInRange(
                    $pageFloat,
                    -1,
                    $maxPages - 1
                );
            }
        } else {
            $pageFloat = -1;
        }

        return $pageFloat;
    }

    /**
     * Ermittelt die erste und die letzte Seite, die im Browser gezeigt wird.
     *
     * @return array with keys 'first' and 'last'
     */
    protected function getFirstAndLastPage($pointer, $pageFloat, $totalPages, $maxPages)
    {
        $pageFloat = $this->getPageFloat($pageFloat, $maxPages);
        $ret = [];
        if ($pageFloat > -1) {
            $ret['last'] = min($totalPages, max($pointer + 1 + $pageFloat, $maxPages));
            $ret['first'] = max(0, $ret['last'] - $maxPages);
        } else {
            $ret['first'] = 0;
            $ret['last'] = \tx_rnbase_util_Math::intInRange($totalPages, 1, $maxPages);
        }

        return $ret;
    }

    protected function renderFirstPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\FirstPageViewHelper', $currentPage
        );
    }

    protected function renderPrevPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\PrevPageViewHelper', $currentPage
        );
    }

    protected function renderNormalPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\NormalPageViewHelper', $currentPage
        );
    }

    protected function renderCurrentPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\CurrentPageViewHelper', $currentPage
        );
    }

    protected function renderNextPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\NextPageViewHelper', $currentPage
        );
    }

    protected function renderLastPage($currentPage)
    {
        return $this->renderAnyPageViewHelperIfExists(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\LastPageViewHelper', $currentPage
        );
    }

    /**
     * iterates through child nodes and renders the given viewhelper.
     * If then attribute is not set and no ThenViewHelper is found, all child nodes are rendered.
     *
     * @return string rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
     *
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     * @author Bastian Waidelich <bastian@typo3.org>
     * @author Stephan Reuther <stephan.reuther@das-medienkombinat.de>
     */
    protected function renderAnyPageViewHelperIfExists($pageViewHelperName, $currentPage)
    {
        $this->templateVariableContainer->add('childNodes', $this->childNodes);
        foreach ($this->childNodes as $childNode) {
            if (
                $childNode instanceof \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode &&
                $childNode->getViewHelperClassName() === $pageViewHelperName
            ) {
                $this->templateVariableContainer->add('currentPage', $currentPage);
                $this->templateVariableContainer->add('pageNumber', $currentPage + 1);

                $data = $childNode->evaluate($this->renderingContext);

                $this->templateVariableContainer->remove('currentPage');
                $this->templateVariableContainer->remove('pageNumber');

                return $data;
            }
        }

        return '';
    }

    /**
     * @param string           $argumentsName
     * @param string           $closureName
     * @param string           $initializationPhpCode
     * @param ViewHelperNode   $node
     * @param TemplateCompiler $compiler
     */
    public function compile(
        $argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler
    ) {
        $this->setViewHelperNode($node);
        $this->setChildNodes($node->getChildNodes());

        // @TODO: replace with a true compiling method to make compilable!
        // @see https://blog.reelworx.at/detail/fluid-compilable-speed-it-up/
        // an sich kein Problem. Bisher aber keinen weg gefunden, in renderStatic
        // an die childNodes zu kommen-
        $compiler->disable();
    }

    /**
     * @return \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext|\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface
     */
    public function getRenderingContext()
    {
        return $this->renderingContext;
    }
}
