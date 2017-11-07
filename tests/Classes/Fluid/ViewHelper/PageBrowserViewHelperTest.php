<?php
namespace Sys25\RnBase\Fluid\ViewHelper;

use Sys25\RnBase\Fluid\ViewHelper\PageBrowser\PageBaseViewHelper;
use Sys25\RnBase\Fluid\ViewHelper\PageBrowser\CurrentPageViewHelper;
use Sys25\RnBase\Fluid\View\Factory;

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
 * Sys25\RnBase\Fluid\ViewHelper$PageBrowserViewHelperTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBrowserViewHelperTest extends BaseViewHelperTestCase
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        \tx_rnbase_util_Misc::prepareTSFE();
    }

    /**
     * @group unit
     */
    public function testRenderReturnsEmptyStringIfNoPageBrowserSetToTemplateVariableContainer()
    {
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser();
        $renderedPageBrowser = $viewHelper->render();
        self::assertEquals('', $renderedPageBrowser, 'Pagebrowser doch gerendered');
    }

    /**
     * @group unit
     */
    public function testRenderReturnsEmptyStringIfOnePageAndHideIfSingelPageIsTrue()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);

        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser(PageBrowserViewHelper::class, $pageBrowser);
        $renderedPageBrowser = $viewHelper->render(true, 10, 'CENTER', ' ', 'myQualifier');
        self::assertEquals('', $renderedPageBrowser, 'Pagebrowser doch gerendered');
    }

    /**
     * @group unit
     */
    public function testRenderCallsOnlyNoRenderPageMethodIfPointerZeroAndReturnsEmptyString()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);

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

        $renderedPageBrowser = $viewHelper->render(false, 10, 'CENTER', ' ', 'myQualifier');
        self::assertEquals(
            '',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group unit
     */
    public function testRenderCallsRenderFirstPageAndRenderPrevPageMethodIfPointer2AndTotalPages3AndReturnsCorrectBrowserParts()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 3, 1);
        $pageBrowser->setPointer(2);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);

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

        $renderedPageBrowser = $viewHelper->render(false, 10, 'CENTER', ' ', 'myQualifier');
        self::assertEquals(
            'renderFirstPageCalled renderPrevPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group unit
     */
    public function testRenderCallsRenderNextPageAndRenderLastPageMethodIfPointer0AndTotalPages2AndReturnsCorrectBrowserParts()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 3, 1);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);

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

        $renderedPageBrowser = $viewHelper->render(false, 10, 'CENTER', ' ', 'myQualifier');
        self::assertEquals(
            'renderNextPageCalled renderLastPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group unit
     */
    public function testRenderCallsRenderNormalPageAndRenderCurrentPageCorrect()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 2, 1);
        $pageBrowser->setPointer(0);

        $methods = array(
            'renderFirstPage', 'renderPrevPage',
            'renderNormalPage', 'renderCurrentPage', 'renderNextPage', 'renderLastPage'
        );
        $viewHelper = $this->getViewHelperMock($methods);
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);

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

        $renderedPageBrowser = $viewHelper->render(false, 10, 'CENTER', ' ', 'myQualifier');
        self::assertEquals(
            'renderCurrentPageCalled renderNormalPageCalled renderNextPageCalled renderLastPageCalled',
            $renderedPageBrowser,
            'Pagebrowser falsch gerendered'
        );
    }

    /**
     * @group unit
     */
    public function testRenderAddsAndRemovesQualifierCorrectInViewHelperVariableContainer()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 0, 0);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);
        $viewHelperVariableContainer = $this->getMock(
            'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
            array('add', 'remove')
        );
        $qualifier = 'myQualifier';
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
        $property = new \ReflectionProperty(
            PageBrowserViewHelper::class,
            'viewHelperVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $viewHelperVariableContainer);

        $viewHelper->render(false, 10, 'CENTER', ' ', $qualifier);
    }

    /**
     * @group unit
     */
    public function testRenderAddsAndRemovesQualifierCorrectInViewHelperVariableContainerIfQualifierFromConfigurations()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 0, 0);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);
        $viewHelperVariableContainer = $this->getMock(
            'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
            array('add', 'remove')
        );
        $qualifier = 'myQualifier';
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
        $property = new \ReflectionProperty(
            PageBrowserViewHelper::class,
            'viewHelperVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $viewHelperVariableContainer);

        $controllerContext = new \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext();
        $controllerContext->setRequest(new \TYPO3\CMS\Extbase\Mvc\Request());
        $controllerContext->configurations = $this->getMock(
            'tx_rnbase_configurations',
            array('getQualifier')
        );
        $controllerContext->configurations->expects($this->once())
            ->method('getQualifier')
            ->will($this->returnValue($qualifier));

        $property = new \ReflectionProperty(
            PageBrowserViewHelper::class,
            'controllerContext'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $controllerContext);

        $viewHelper->render();
    }

    /**
     * @group unit
     */
    public function testRenderAddsAndRemovesCountAndTotalPagesCorrectInTemplateVariableContainer()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 6, 3);
        $pageBrowser->setPointer(0);

        $viewHelper = $this->getViewHelperMock();
        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser($viewHelper, $pageBrowser);
        $templateVariableContainer = $this->getMock(
            'TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
            array('add', 'remove'),
            array(array('pagebrowser' => $pageBrowser))
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

        $property = new \ReflectionProperty(
            PageBrowserViewHelper::class,
            'templateVariableContainer'
        );
        $property->setAccessible(true);
        $property->setValue($viewHelper, $templateVariableContainer);

        $viewHelper->render(false, 10, 'CENTER', ' ', $qualifier);
    }

    /**
     * @group unit
     */
    public function testRenderEvaluatesPageBrowserViewHelperInChildNodesCorrect()
    {
        $pageBrowser = \tx_rnbase::makeInstance(
            'tx_rnbase_util_PageBrowser',
            1
        );
        $pageBrowser->setState(0, 1, 1);

        $viewHelper = $this->getPreparedVîewHelperWithPageBrowser(PageBrowserViewHelper::class, $pageBrowser);

        $renderingContext = new \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext();
        $viewHelperNode = $this->getAccessibleMock(
            'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
            array('evaluate'),
            array(),
            '',
            false
        );
        $viewHelperNode->_set('viewHelperClassName', CurrentPageViewHelper::class);
        $viewHelperNode
            ->expects(self::once())
            ->method('evaluate')
            ->with($viewHelper->getRenderingContext())
            // damit testen wir ob die Daten in templateVariableContainer temporär korrekt gesetzt werden
            ->willReturnCallback(function($renderingContext){
                $variableProvider = $renderingContext->getVariableProvider();
                return $variableProvider->get('pageNumber') . ' ' . $variableProvider->get('currentPage');
            });
        $viewHelper->setChildNodes(array($viewHelperNode));

        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('pageNumber'));
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('currentPage'));
        self::assertSame('1 0', $viewHelper->render(false, 10, 'CENTER', ' ', 'myQualifier'));
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('pageNumber'));
        self::assertFalse($renderingContext->getVariableProvider()->offsetExists('currentPage'));
    }

    /**
     * @group integration
     */
    public function testRenderIfNoPageBrowser()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');

        $view = Factory::getViewInstance($configurations);
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertEquals(
            '<div class="pagebrowser"></div>',
            trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderRespectingHideIfSinglePageIfPresent()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');

        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 5));

        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertEquals(
            '<div class="pagebrowser"></div>',
            trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderRespectingHideIfSinglePageIfNotPresent()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');

        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 1, 1);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 5));

        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelperWithoutHideIfSinglePage.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser"><a href=".*&amp;pb-1-pointer=0">1<\/a><\/div>/', trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderIfOnFirstPage()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(0);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 5));
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );
        self::assertRegExp(
            '/<div class="pagebrowser">' .
            '<a class="current" href=".*&amp;pb-1-pointer=0">1<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=1">2<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=2">3<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=3">4<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=4">5<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=1">next<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=9">last<\/a>' .
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderIfOnMiddlePage()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(3);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 5));
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">' .
            '<a href=".*&amp;pb-1-pointer=0">first<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=2">previous<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=1">2<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=2">3<\/a> ' .
            '<a class="current" href=".*&amp;pb-1-pointer=3">4<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=4">5<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=5">6<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=4">next<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=9">last<\/a>' .
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderIfOnLastPage()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(9);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 5));
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">' .
            '<a href=".*&amp;pb-1-pointer=0">first<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=8">previous<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=5">6<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=6">7<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=7">8<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=8">9<\/a> ' .
            '<a class="current" href=".*&amp;pb-1-pointer=9">10<\/a>' .
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @group integration
     */
    public function testRenderRespectsMaxPagesConfiguration()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $pageBrowser = \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 1);
        $pageBrowser->setState(0, 20, 2);
        $pageBrowser->setPointer(3);

        $view = Factory::getViewInstance($configurations);
        $view->assignMultiple(array('pagebrowser' => $pageBrowser, 'maxPages' => 3));
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/PageBrowserViewHelper.html')
        );

        self::assertRegExp(
            '/<div class="pagebrowser">' .
            '<a href=".*&amp;pb-1-pointer=0">first<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=2">previous<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=2">3<\/a> ' .
            '<a class="current" href=".*&amp;pb-1-pointer=3">4<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=4">5<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=4">next<\/a> ' .
            '<a href=".*&amp;pb-1-pointer=9">last<\/a>' .
            '<\/div>/',
            trim($view->render())
        );
    }

    /**
     * @param string | \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
     * @param tx_rnbase_util_PageBrowser $pageBrowser
     *
     * @return \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
     */
    protected function getPreparedVîewHelperWithPageBrowser(
        $viewHelper = PageBrowserViewHelper::class, \tx_rnbase_util_PageBrowser $pageBrowser = null
    )
    {
        $viewHelper = parent::getPreparedVîewHelper($viewHelper);

        if ($pageBrowser !== null) {
            $this->renderingContext->getVariableProvider()->add('pagebrowser', $pageBrowser);
        }

        return $viewHelper;
    }

    /**
     * @param array $methods
     *
     * @return Tx_Mktegutfe_ViewHelpers_PageBrowserViewHelper
     */
    protected function getViewHelperMock(
        array $methods = array(
            'getPageFloat', 'getFirstAndLastPage', 'renderFirstPage', 'renderPrevPage',
            'renderNormalPage', 'renderCurrentPage', 'renderNextPage', 'renderLastPage'
        )
    ) {
        return $this->getMock(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowserViewHelper',
            $methods
        );
    }
}
