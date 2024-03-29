<?php

namespace Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser;

use Sys25\RnBase\ExtBaseFluid\ViewHelper\BaseViewHelperTest;
use Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowserViewHelper;
use tx_rnbase;

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
 * Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser$PageBaseViewHelperTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class PageBaseViewHelper_testcase extends BaseViewHelperTest
{
    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testChildrenAreUsedAsLinkTextByDefault()
    {
        $viewHelper = $this->getMock(
            'Sys25\\RnBase\\ExtBaseFluid\\ViewHelper\\PageBrowser\\PageBaseViewHelper',
            ['renderChildren']
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
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testPageNumberIsUsedAsLinkTextIfConfigured()
    {
        $viewHelper = $this->getMock(
            'Sys25\\RnBase\\ExtBaseFluid\\ViewHelper\\PageBrowser\\PageBaseViewHelper',
            ['renderChildren']
        );

        $viewHelper->expects(self::never())
            ->method('renderChildren');

        $viewHelper = $this->getPreparedVîewHelper($viewHelper, ['usePageNumberAsLinkText' => true]);

        $renderedContent = $viewHelper->render();
        self::assertContains('456', $renderedContent);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Sys25\RnBase\ExtBaseFluid\ViewHelper\BaseViewHelperTest::getPreparedVîewHelper()
     */
    protected function getPreparedVîewHelper($viewHelper, array $arguments = [])
    {
        $viewHelper = parent::getPreparedVîewHelper($viewHelper);

        $this->renderingContext->getViewHelperVariableContainer()->add(
            PageBrowserViewHelper::class,
            'pageBrowserQualifier',
            'rn_base'
        );

        $this->renderingContext->getVariableProvider()->add(
            'pagebrowser',
            tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 123)
        );
        $this->renderingContext->getVariableProvider()->add('currentPage', 123);
        $this->renderingContext->getVariableProvider()->add('pageNumber', 456);

        $viewHelper->initializeArguments();
        $viewHelper->setArguments(array_merge(
            $arguments,
            [
                'data-tagname' => 'a',
                'additionalParams' => [],
                'argumentsToBeExcludedFromQueryString' => [],
            ]
        ));

        return $viewHelper;
    }
}
