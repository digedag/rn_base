<?php
namespace Sys25\RnBase\Fluid\View;
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
 * Sys25\RnBase\Fluid\View$ActionTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class ActionTest extends \tx_rnbase_tests_BaseTestCase
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        \tx_rnbase_util_Misc::prepareTSFE(array('force' => true));
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        @unlink(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Templates/MyTestAction.html'));
        @unlink(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Layouts/MyTestAction.html'));
        @unlink(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Partials/MyTestAction.html'));
    }

    /**
     * @group unit
     */
    public function testGetConfigurationId()
    {
        $controller = $this->getMock('stdClass', array('getConfId'));
        $controller
            ->expects(self::once())
            ->method('getConfId')
            ->willReturn('confId');

        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getController'));
        $view
            ->expects(self::once())
            ->method('getController')
            ->willReturn($controller);

        self::assertSame('confId', $this->callInaccessibleMethod($view, 'getConfigurationId'));
    }

    /**
     * @group integration
     */
    public function testRenderWithFixedTemplateFile()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::once())
            ->method('getConfigurationId')
            ->willReturn('myConfId');

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestAction.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<div class="test">myConfId</div>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderWithDefaultPaths()
    {
        $this->copyHtmlFilesToCommonResourcesFolder(true);

        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::once())
            ->method('getConfigurationId')
            ->willReturn('myConfId');

        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<div class="test">myConfId</div>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @param string $includeTemplate
     * @return void
     */
    protected function copyHtmlFilesToCommonResourcesFolder($includeTemplate = false)
    {
        if ($includeTemplate) {
            file_put_contents(
                \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Templates/MyTestAction.html'),
                file_get_contents(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/MyTestAction.html'))
            );
        }
        file_put_contents(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Layouts/MyTestAction.html'),
            file_get_contents(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/Layouts/MyTestAction.html'))
        );
        file_put_contents(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/Resources/Private/Partials/MyTestAction.html'),
            file_get_contents(\tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/Partials/MyTestAction.html'))
        );
    }

    /**
     * @group integration
     */
    public function testRenderWithConfiguredPaths()
    {
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::once())
            ->method('getConfigurationId')
            ->willReturn('myConfId');

        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurationArray = array(
            'view.' => array(
                'templateRootPaths.' => array(0 => 'EXT:rn_base/tests/fixtures/html/'),
                'layoutRootPaths.' => array(0 => 'EXT:rn_base/tests/fixtures/html/Layouts/'),
                'partialRootPaths.' => array(0 => 'EXT:rn_base/tests/fixtures/html/Partials/')
            )
        );
        $configurations = $this->createConfigurations($configurationArray, 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<div class="test">myConfId</div>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderWithOldTemplatePathsConfiguration()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::once())
            ->method('getConfigurationId')
            ->willReturn('myConfId');

        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurationArray = array(
            'templatePath' =>'EXT:rn_base/tests/fixtures/html/'
        );
        $configurations = $this->createConfigurations($configurationArray, 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<div class="test">myConfId</div>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     * @expectedException TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function testRenderThrowsExceptionIfTemplateCanNotBeResolved()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::once())
            ->method('getConfigurationId')
            ->willReturn('myConfId');

        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurationArray = array();
        $configurations = $this->createConfigurations($configurationArray, 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<div class="test">myConfId</div>', $view->render('MyUnknownTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderHandlesAssignsItemsCorrect()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestAction2.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);
        $configurations->getViewData()->offsetSet('testAssignment', 'JohnDoe');

        self::assertEquals('JohnDoe', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderTrimsOutput()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestActionWithTrimmableContent.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);

        self::assertEquals('<span>test</span>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderIfFilterNoObjectAndNoConfigurationId()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestActionWithTrimmableContent.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);
        $configurations->getViewData()->offsetSet('filter', 'test');

        self::assertEquals('<span>test</span>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderIfFilterObjectAndNoConfigurationId()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestActionWithTrimmableContent.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);
        $configurations->getViewData()->offsetSet('filter', 'test');

        $filter = $this->getMock('tx_rnbase_filter_BaseFilter', array('parseTemplate'), array(), '', false);
        $filter
            ->expects(self::never())
            ->method('parseTemplate');

        $configurations->getViewData()->offsetSet('filter', $filter);

        self::assertEquals('<span>test</span>', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderIfFilterObjectAndConfigurationId()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::any())
            ->method('getConfigurationId')
            ->will(self::returnValue('testId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestAction.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);

        $filter = $this->getMock('tx_rnbase_filter_BaseFilter', array('parseTemplate'), array(), '', false);
        $filter
            ->expects(self::once())
            ->method('parseTemplate')
            ->with('<div class="test">testId</div>', $configurations->getFormatter(), 'testId')
            ->will(self::returnValue('filtered'));

        $configurations->getViewData()->offsetSet('filter', $filter);

        self::assertEquals('filtered', $view->render('MyTestAction', $configurations));
    }

    /**
     * @group integration
     */
    public function testRenderIfFilterObjectButHasNoParseTemplateMethod()
    {
        $this->copyHtmlFilesToCommonResourcesFolder();
        $view = $this->getMock('Sys25\\RnBase\\Fluid\\View\\Action', array('getConfigurationId'));
        $view
            ->expects(self::any())
            ->method('getConfigurationId')
            ->will(self::returnValue('testId'));

        $view->setTemplateFile('EXT:rn_base/tests/fixtures/html/MyTestAction.html');
        $parameters = \tx_rnbase::makeInstance('tx_rnbase_parameters');
        $configurations = $this->createConfigurations(array(), 'rn_base', 'rn_base', $parameters);

        $filter = $this->getMock('tx_rnbase_filter_BaseFilter', array('parseTemplateNew'), array(), '', false);

        $configurations->getViewData()->offsetSet('filter', $filter);

        self::assertEquals('<div class="test">testId</div>', $view->render('MyTestAction', $configurations));
    }
}
