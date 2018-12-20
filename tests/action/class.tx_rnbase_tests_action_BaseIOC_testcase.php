<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2015 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 * tx_rnbase_tests_action_BaseIOC_testcase
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_action_BaseIOC_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->cleanUpPageRenderer();

        tx_rnbase_util_Misc::prepareTSFE(['force' => true]);
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->cleanUpPageRenderer();

        $property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $property->setValue(\tx_rnbase_util_TYPO3::getTSFE(), []);
    }

    /**
     * @return void
     */
    protected function cleanUpPageRenderer()
    {
        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsFiles');
        $property->setAccessible(true);
        $property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), []);

        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsLibs');
        $property->setAccessible(true);
        $property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), []);

        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'cssFiles');
        $property->setAccessible(true);
        $property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), []);
    }

    /**
     * @group unit
     */
    public function testAddRessourcesAddsCssFiles()
    {
        $action = $this->getAction();
        $configurations = $this->createConfigurations([
            'testConfId.' => [
                'includeCSS.' => [
                    1 => 'typo3conf/ext/rn_base/ext_emconf.php',
                    2 => 'EXT:rn_base/ext_icon.gif'
                ]
            ]
        ], 'rn_base');
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'cssFiles');
        $property->setAccessible(true);
        $files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

        self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['typo3conf/ext/rn_base/ext_emconf.php']['file']);
        self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['typo3conf/ext/rn_base/ext_icon.gif']['file']);
    }

    /**
     * @group unit
     */
    public function testAddRessourcesAddsJavaScriptFooterFiles()
    {
        $action = $this->getAction();
        $configurations = $this->createConfigurations([
            'testConfId.' => [
                'includeJSFooter.' => [
                    '1' => 'typo3conf/ext/rn_base/ext_emconf.php',
                    '2' => 'EXT:rn_base/ext_icon.gif',
                    '3' => '//www.dmk-ebusiness.de',
                    '3.' => ['external' => 1]
                ]
            ]
        ], 'rn_base');
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsFiles');
        $property->setAccessible(true);
        $files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

        self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['typo3conf/ext/rn_base/ext_emconf.php']['file']);
        self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['typo3conf/ext/rn_base/ext_icon.gif']['file']);
        self::assertEquals('//www.dmk-ebusiness.de', $files['//www.dmk-ebusiness.de']['file']);
    }

    /**
     * @group unit
     */
    public function testAddRessourcesAddsJavaScriptLibraryFiles()
    {
        $action = $this->getAction();
        $configurations = $this->createConfigurations([
            'testConfId.' => [
                'includeJSlibs.' => [
                    'first' => 'typo3conf/ext/rn_base/ext_emconf.php',
                    'second' => 'EXT:rn_base/ext_icon.gif',
                    'third' => '//www.dmk-ebusiness.de',
                    'third.' => ['external' => 1]
                ],
                'includeJSLibs.' => [
                    'fourth' => 'typo3conf/ext/rn_base/ext_conf_template.txt',
                ]
            ]
        ], 'rn_base');
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

        $property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsLibs');
        $property->setAccessible(true);
        $files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

        self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['first']['file']);
        self::assertFalse($files['first']['compress']);
        self::assertFalse($files['first']['excludeFromConcatenation']);

        self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['second']['file']);
        self::assertFalse($files['second']['compress']);
        self::assertFalse($files['second']['excludeFromConcatenation']);

        self::assertEquals('//www.dmk-ebusiness.de', $files['third']['file']);
        self::assertFalse($files['third']['compress']);
        self::assertTrue($files['third']['excludeFromConcatenation']);

        self::assertEquals('typo3conf/ext/rn_base/ext_conf_template.txt', $files['fourth']['file']);
        self::assertFalse($files['fourth']['compress']);
        self::assertFalse($files['fourth']['excludeFromConcatenation']);
    }

    /**
     * @group unit
     */
    public function testAddCacheTags()
    {
        $action = $this->getAction();
        $configurations = $this->createConfigurations([
            'testConfId.' => [
                'cacheTags.' => [
                    0 => 'first',
                    1 => 'second',
                ]
            ]
        ], 'rn_base');
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod($action, 'addCacheTags');

        $property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $cacheTags = $property->getValue(tx_rnbase_util_TYPO3::getTSFE());

        self::assertEquals(['first', 'second'], $cacheTags);
    }

    /**
     * @group unit
     */
    public function testAddCacheTagsIfNotConfigured()
    {
        $action = $this->getAction();
        $configurations = $this->createConfigurations(['testConfId.' => []], 'rn_base');
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod($action, 'addCacheTags');

        $property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $cacheTags = $property->getValue(tx_rnbase_util_TYPO3::getTSFE());

        self::assertEquals([], $cacheTags);
    }

    /**
     * @return tx_rnbase_action_BaseIOC
     */
    protected function getAction()
    {
        $action = $this->getMockForAbstractClass(
            'tx_rnbase_action_BaseIOC',
            [],
            '',
            true,
            true,
            true,
            ['getTemplateName', 'getViewClassName', 'handleRequest']
        );

        $action->expects(self::any())
            ->method('getTemplateName')
            ->will(self::returnValue('testConfId'));

        return $action;
    }
}
