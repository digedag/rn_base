<?php

namespace Sys25\RnBase\ExtBaseFluid\ViewHelper;

use ReflectionProperty;
use Sys25\RnBase\ExtBaseFluid\View\Factory;
use Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\CurrentPageViewHelper;
use tx_rnbase;
use tx_rnbase_util_Files;
use tx_rnbase_util_Misc;
use tx_rnbase_util_PageBrowser;

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
 * Sys25\RnBase\ExtBaseFluid\ViewHelper$PageBrowserViewHelperTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBrowserViewHelperTest extends BaseViewHelperTest
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        tx_rnbase_util_Misc::prepareTSFE();
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderReturnsEmptyStringIfNoPageBrowserSetToTemplateVariableContainer()
    {
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser();
        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals('', $renderedPageBrowser, 'Pagebrowser doch gerendered');
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderReturnsEmptyStringIfOnePageAndHideIfSingelPageIsTrue()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);

        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser(
            PageBrowserViewHelper::class,
            $pageBrowser,
            true,
            10,
            'CENTER',
            ' ',
            'myQualifier'
        );
        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals('', $renderedPageBrowser, 'Pagebrowser doch gerendered');
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderCallsOnlyNoRenderPageMethodIfPointerZeroAndReturnsEmptyString()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser(
            $viewHelper,
            $pageBrowser,
            false,
            10,
            'CENTER',
            ' ',
            'myQualifier'
        );

        $viewHelper->expects($this->never())
            ->method('renderFirstPage');

        $viewHelper->expects($this->never())
            ->method('renderPrevPage');

        $viewHelper->expects($this->never())
            ->method('renderNormalPage');

        $viewHelper->expects($this->never())
            ->method('renderCurrentPage');

        $viewHelper->expects($this->never())
            ->method('renderNextPage');

        $viewHelper->expects($this->never())
            ->method('renderLastPage');

        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals(
            '',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderCallsRenderFirstPageAndRenderPrevPageMethodIfPointer2AndTotalPages3AndReturnsCorrectBrowserParts()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 3, 1);
        $pageBrowser->setPointer(2);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser, false, 10, 'CENTER', ' ', 'myQualifier');

        $viewHelper->expects($this->at(0))
            ->method('renderFirstPage')
            ->with(0)
            ->will($this->returnValue('renderFirstPageCalled'));

        $viewHelper->expects($this->at(1))
            ->method('renderPrevPage')
            ->with(1)
            ->will($this->returnValue('renderPrevPageCalled'));

        $viewHelper->expects($this->at(2))
            ->method('getFirstAndLastPage');

        $viewHelper->expects($this->never())
            ->method('renderNormalPage');

        $viewHelper->expects($this->never())
            ->method('renderCurrentPage');

        $viewHelper->expects($this->never())
            ->method('renderNextPage');

        $viewHelper->expects($this->never())
            ->method('renderLastPage');

        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals(
            'renderFirstPageCalled renderPrevPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderCallsRenderNextPageAndRenderLastPageMethodIfPointer0AndTotalPages2AndReturnsCorrectBrowserParts()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 3, 1);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser, false, 10, 'CENTER', ' ', 'myQualifier');

        $viewHelper->expects($this->never())
            ->method('renderPrevPage');

        $viewHelper->expects($this->never())
            ->method('renderFirstPage');

        $viewHelper->expects($this->at(0))
            ->method('getFirstAndLastPage');

        $viewHelper->expects($this->never())
            ->method('renderNormalPage');

        $viewHelper->expects($this->never())
            ->method('renderCurrentPage');

        $viewHelper->expects($this->at(1))
            ->method('renderNextPage')
            ->with(1)
            ->will($this->returnValue('renderNextPageCalled'));

        $viewHelper->expects($this->at(2))
            ->method('renderLastPage')
            ->with(2)
            ->will($this->returnValue('renderLastPageCalled'));

        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals(
            'renderNextPageCalled renderLastPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderCallsRenderNormalPageAndRenderCurrentPageCorrect()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 2, 1);
        $pageBrowser->setPointer(0);

        $methods = [
            'renderFirstPage', 'renderPrevPage',
            'renderNormalPage', 'renderCurrentPage', 'renderNextPage', 'renderLastPage',
        ];
        $viewHelper = $this->getViewHelperMock($methods);
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser, false, 10, 'CENTER', ' ', 'myQualifier');

        $viewHelper->expects($this->never())
            ->method('renderPrevPage');

        $viewHelper->expects($this->never())
            ->method('renderFirstPage');

        $viewHelper->expects($this->at(0))
            ->method('renderCurrentPage')
            ->with(0)
            ->will($this->returnValue('renderCurrentPageCalled'));

        $viewHelper->expects($this->at(1))
            ->method('renderNormalPage')
            ->with(1)
            ->will($this->returnValue('renderNormalPageCalled'));

        $viewHelper->expects($this->at(2))
            ->method('renderNextPage')
            ->with(1)
            ->will($this->returnValue('renderNextPageCalled'));

        $viewHelper->expects($this->at(3))
            ->method('renderLastPage')
            ->with(1)
            ->will($this->returnValue('renderLastPageCalled'));

        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals(
            'renderCurrentPageCalled renderNormalPageCalled renderNextPageCalled renderLastPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderAddsAndRemovesQualifierCorrectInViewHelperVariableContainer()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 0, 0);
        $pageBrowser->setPointer(0);

        $qualifier = 'myQualifier';
        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser, false, 10, 'CENTER', ' ', $qualifier);
        $viewHelperVariableContainer = $this->getMock(
            'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
            ['add', 'remove']
        );
        $viewHelperVariableContainer->expects($this->at(0))
            ->method('add')
            ->with(
                PageBrowserViewHelper::class,
                'pageBrowserQualifier',
                $qualifier
            );
        $viewHelperVariableContainer->expects($this->at(1))
            ->method('remove')
            ->with(
                PageBrowserViewHelper::class,
                'pageBrowserQualifier'
            );
        $property = new ReflectionProperty(
            PageBrowserViewHelper::class,
            'viewHelperVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $viewHelperVariableContainer);

        $viewHelper->render();
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderAddsAndRemovesQualifierCorrectInViewHelperVariableContainerIfQualifierFromConfigurations()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 0, 0);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);
        $qualifier = 'myQualifier';
        $viewHelper->expects(self::once())
            ->method('getQualifierFromConfigurations')
            ->will(self::returnValue($qualifier));
        $viewHelperVariableContainer = $this->getMock(
            'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
            ['add', 'remove']
        );

        $viewHelperVariableContainer->expects($this->at(0))
            ->method('add')
            ->with(
                PageBrowserViewHelper::class,
                'pageBrowserQualifier',
                $qualifier
            );
        $viewHelperVariableContainer->expects($this->at(1))
            ->method('remove')
            ->with(
                PageBrowserViewHelper::class,
                'pageBrowserQualifier'
            );
        $property = new ReflectionProperty(
            PageBrowserViewHelper::class,
            'viewHelperVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $viewHelperVariableContainer);

        $viewHelper->render();
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderAddsAndRemovesCountAndTotalPagesCorrectInTemplateVariableContainer()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 6, 3);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);
        $templateVariableContainer = $this->getMock(
            'TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
            ['add', 'remove'],
            [['pagebrowser' => $pageBrowser]]
        );
        $qualifier = 'myQualifier';
        $templateVariableContainer->expects($this->at(0))
            ->method('add')
            ->with('count', 6);
        $templateVariableContainer->expects($this->at(1))
            ->method('add')
            ->with('totalPages', 2);
        $templateVariableContainer->expects($this->at(2))
            ->method('remove')
            ->with('count');
        $templateVariableContainer->expects($this->at(3))
            ->method('remove')
            ->with('totalPages');

        $property = new ReflectionProperty(
            PageBrowserViewHelper::class,
            'templateVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $templateVariableContainer);

        $viewHelper->render(false, 10, 'CENTER', ' ', $qualifier);
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderEvaluatesPageBrowserViewHelperInChildNodesCorrect()
    {
        $pageBrowser = tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);

        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser(
            PageBrowserViewHelper::class,
            $pageBrowser,
            false,
            10,
            'CENTER',
            ' ',
            'myQualifier'
        );

        $renderingContext = new \TYPO3Fluid\Fluid\Core\Rendering\RenderingContext();
        $viewHelperNode = $this->getAccessibleMock(
            'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
            ['evaluate'],
            [],
            '',
            false
        );
        $viewHelperNode->_set('viewHelperClassName', CurrentPageViewHelper::class);
        $viewHelperNode
            ->expects(self::once())
            ->method('evaluate')
            ->with($viewHelper->getRenderingContext())
            // damit testen wir ob die Daten in templateVariableContainer temporär korrekt gesetzt werden
            ->willReturnCallback(function ($renderingContext) {
                $variableProvider = $renderingContext->getVariableProvider();

                return $variableProvider->get('pageNumber').' '.$variableProvider->get('currentPage');
            });
        $viewHelper->setChildNodes([$viewHelperNode]);

        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('pageNumber'));
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('currentPage'));
        self::assertSame('1 0', $viewHelper->render());
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('pageNumber'));
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('currentPage'));
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderIfNoPageBrowser()
    {
        $configurations = $this->createConfigurations([], 'rn_base');

        $view = Factory::getViewInstance($configurations);
        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertEquals(
            '<div class="pagebrowser"></div>',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderRespectingHideIfSinglePageIfPresent()
    {
        $configurations = $this->createConfigurations([], 'rn_base');

        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 5]);

        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertEquals(
            '<div class="pagebrowser"></div>',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderRespectingHideIfSinglePageIfNotPresent()
    {
        $configurations = $this->createConfigurations([], 'rn_base');

        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 5]);

        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelperWithoutHideIfSinglePage.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser"><a href=".*&amp;rn_base%5Bpb-1-pointer%5D=0">1<\/a><\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderIfOnFirstPage()
    {
        $configurations = $this->createConfigurations([], 'rn_base');
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 5]);
        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );
        self::assertRegExp(
            '/<div class="pagebrowser">'.
            '<a class="current" href=".*&amp;rn_base%5Bpb-1-pointer%5D=0">1<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=1">2<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=2">3<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=3">4<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=4">5<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=1">next<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=9">last<\/a>'.
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderIfOnMiddlePage()
    {
        $configurations = $this->createConfigurations([], 'rn_base');
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(3);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 5]);
        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">'.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=0">first<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=2">previous<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=1">2<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=2">3<\/a> '.
            '<a class="current" href=".*&amp;rn_base%5Bpb-1-pointer%5D=3">4<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=4">5<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=5">6<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=4">next<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=9">last<\/a>'.
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderIfOnLastPage()
    {
        $configurations = $this->createConfigurations([], 'rn_base');
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(9);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 5]);
        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">'.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=0">first<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=8">previous<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=5">6<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=6">7<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=7">8<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=8">9<\/a> '.
            '<a class="current" href=".*&amp;rn_base%5Bpb-1-pointer%5D=9">10<\/a>'.
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testRenderRespectsMaxPagesConfiguration()
    {
        $configurations = $this->createConfigurations([], 'rn_base');
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(3);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(['pagebrowser' => $pageBrowser, 'maxPages' => 3]);
        $view->setTemplatePathAndFilename(
            tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">'.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=0">first<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=2">previous<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=2">3<\/a> '.
            '<a class="current" href=".*&amp;rn_base%5Bpb-1-pointer%5D=3">4<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=4">5<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=4">next<\/a> '.
            '<a href=".*&amp;rn_base%5Bpb-1-pointer%5D=9">last<\/a>'.
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @param string|\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
     * @param tx_rnbase_util_PageBrowser                                   $pageBrowser
     *
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
     */
    protected function getPreparedVîewHelperWithPageBrowser(
        $viewHelper = PageBrowserViewHelper::class,
        ?tx_rnbase_util_PageBrowser $pageBrowser = null,
        $hideIfSinglePage = false,
        $maxPages = 10,
        $pageFloat = 'CENTER',
        $implode = '',
        $qualifier = null,
    ) {
        $viewHelper = parent::getPreparedVîewHelper($viewHelper);

        if (null !== $pageBrowser) {
            $this->renderingContext->getVariableProvider()->add('pagebrowser', $pageBrowser);
        }
        $viewHelper->setArguments([
            'hideIfSinglePage' => $hideIfSinglePage,
            'maxPages' => $maxPages,
            'pageFloat' => $pageFloat,
            'implode' => $implode,
            'qualifier' => $qualifier,
        ]);

        return $viewHelper;
    }

    /**
     * @param array $methods
     *
     * @return PageBrowserViewHelper
     */
    protected function getViewHelperMock(
        array $methods = [
            'getPageFloat', 'getFirstAndLastPage', 'renderFirstPage', 'renderPrevPage',
            'renderNormalPage', 'renderCurrentPage', 'renderNextPage', 'renderLastPage',
            'getQualifierFromConfigurations',
        ],
    ) {
        return $this->getMock(
            'Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowserViewHelper',
            $methods
        );
    }
}
