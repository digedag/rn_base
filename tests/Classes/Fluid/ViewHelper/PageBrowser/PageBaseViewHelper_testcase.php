<?php
namespace Sys25\RnBase\Fluid\ViewHelper\PageBrowser;

use Sys25\RnBase\Fluid\ViewHelper\BaseViewHelperTestCase;

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
// although the tests will never run below TYPO3 8.7 we need to
// make sure thath BaseViewHelperTestCase is loaded as the autoloading in TYPO3 6.2
// won't work resulting in a fatal error when this file is loaded
// @todo can be removed when support for TYPO3 6.2 is dropped.
require_once(\tx_rnbase_util_Extensions::extPath('rn_base') . 'tests/Classes/Fluid/ViewHelper/BaseViewHelperTest.php');

/**
 * Sys25\RnBase\Fluid\ViewHelper\PageBrowser$PageBaseViewHelperTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBaseViewHelperTest extends BaseViewHelperTestCase
{

    /**
     * @group unit
     */
    public function testChildrenAreUsedAsLinkTextByDefault()
    {
        $viewHelper = $this->getMock(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\PageBaseViewHelper',
            array('renderChildren')
        );

        $viewHelper->expects(self::once())
            ->method('renderChildren')
            ->will(self::returnValue('I\'m a child'));

        $viewHelper = $this->getPreparedVîewHelper($viewHelper);

        $renderedContent = $viewHelper->render();
        self::assertContains('I\'m a child', $renderedContent);
        self::assertNotContains('456', $renderedContent);
    }

    /**
     * @group unit
     */
    public function testPageNumberIsUsedAsLinkTextIfConfigured()
    {
        $viewHelper = $this->getMock(
            'Sys25\\RnBase\\Fluid\\ViewHelper\\PageBrowser\\PageBaseViewHelper',
            array('renderChildren')
        );

        $viewHelper->expects(self::never())
            ->method('renderChildren');

        $viewHelper = $this->getPreparedVîewHelper($viewHelper);

        $renderedContent = $viewHelper->render(null, array(), 0, false, false, '', false, false, false, array(), true);
        self::assertContains('456', $renderedContent);
    }

    /**
     * {@inheritDoc}
     * @see \Sys25\RnBase\Fluid\ViewHelper\BaseViewHelperTest::getPreparedVîewHelper()
     */
    protected function getPreparedVîewHelper($viewHelper)
    {
        $viewHelper = parent::getPreparedVîewHelper($viewHelper);

        $this->renderingContext->getViewHelperVariableContainer()->add(
            PageBrowserViewHelper::class,
            'pageBrowserQualifier',
            'rn_base'
        );

        $this->renderingContext->getVariableProvider()->add(
            'pagebrowser', \tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 123)
        );
        $this->renderingContext->getVariableProvider()->add('currentPage', 123);
        $this->renderingContext->getVariableProvider()->add('pageNumber', 456);

        $viewHelper->initializeArguments();
        $viewHelper->setArguments(array('data-tagname' => 'a'));

        return $viewHelper;
    }
}
