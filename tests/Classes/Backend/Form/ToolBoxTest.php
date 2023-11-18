<?php

namespace Sys25\RnBase\Backend\Form;

use Sys25\RnBase\Backend\Template\Override\DocumentTemplate;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2023 Rene Nitzsche (rene@system25.de)
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

/**
 * Tx_Rnbase_Backend_Form_ToolBoxTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class ToolBoxTest extends BaseTestCase
{
    protected function setUp(): void
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $cacheManager = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
            // needed for icon retrieval
            if (!$cacheManager->hasCache('assets')) {
                $cacheManager->registerCache(
                    new \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend(
                        'assets',
                        new \TYPO3\CMS\Core\Cache\Backend\NullBackend('tests', [])
                    )
                );
            }
        }
    }

    /**
     * @group unit
     */
    public function testCreateSelectSingleByArrayCallsJustCreateSelectByArray()
    {
        $formTool = $this->getMock(ToolBox::class, ['createSelectByArray']);
        $formTool->expects(self::once())
            ->method('createSelectByArray')
            ->with(1, 2, ['test1'], ['test2'])
            ->will(self::returnValue('returned'));

        self::assertEquals('returned', $formTool->createSelectSingleByArray(1, 2, ['test1'], ['test2']));
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArray()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray('testSelect', 2, [1 => 'John', 2 => 'Doe']);
        $expectedSelect = '<select name="testSelect" class="select"><option value="1" >John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfReloadOption()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1 => 'John', 2 => 'Doe'],
            ['reload' => true]
        );
        $expectedSelect = '<select name="testSelect" class="select" onchange=" this.form.submit(); " ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfOnchangeOption()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1 => 'John', 2 => 'Doe'],
            ['onchange' => 'myJsFunction']
        );
        $expectedSelect = '<select name="testSelect" class="select" onchange="myJsFunction" ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfReloadAndOnchangeOption()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1 => 'John', 2 => 'Doe'],
            ['onchange' => 'myJsFunction', 'reload' => true]
        );
        $expectedSelect = '<select name="testSelect" class="select" onchange=" this.form.submit(); myJsFunction" ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfMultipleOption()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray(
            'testSelect',
            '1,2',
            [1 => 'John', 2 => 'Doe'],
            ['multiple' => true]
        );
        $expectedSelect = '<select name="testSelect[]" class="select" multiple="multiple"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfSizeOption()
    {
        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $select = $formTool->createSelectByArray(
            'testSelect',
            '1,2',
            [1 => 'John', 2 => 'Doe'],
            ['size' => 20]
        );
        $expectedSelect = '<select name="testSelect" class="select" size="20"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires $GLOBALS['BE_USER']!
     *
     * @TODO: this test should test rn_base code, not TYPO3 internals
     */
    public function testGetJavaScriptForLinkToDataHandlerActionInTypo387()
    {
        if (TYPO3::isTYPO104OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }

        $formTool = tx_rnbase::makeInstance($this->buildAccessibleProxy(ToolBox::class));
        $formTool->init(tx_rnbase::makeInstance(DocumentTemplate::class), null);
        $options = ['test'];
        $urlParameters = 'someParameters';

        self::assertRegExp(
            '/return jumpToUrl\(\'.*someParameters.*redirect=\'\+T3_THIS_LOCATION.*/',
            $formTool->_call('getJavaScriptForLinkToDataHandlerAction', $urlParameters, $options)
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires $GLOBALS['BE_USER']!
     */
    public function testGetJavaScriptForLinkToDataHandlerActionAddsNecessaryJavaScriptsInTypo387()
    {
        $formTool = $this->getAccessibleMock(ToolBox::class, ['getBaseJavaScriptCode']);
        $formTool
            ->expects(self::once())
            ->method('getBaseJavaScriptCode')
            ->will(self::returnValue('javascriptCode'));

        $pageRenderer = $this->getMock('stdClass', ['addJsInlineCode']);
        $pageRenderer
            ->expects(self::once())
            ->method('addJsInlineCode')
            ->with('rnBaseMethods', 'javascriptCode');

        $document = $this->getMock(DocumentTemplate::class, ['getPageRenderer']);
        $document
            ->expects(self::once())
            ->method('getPageRenderer')
            ->will(self::returnValue($pageRenderer));

        $formTool->init($document, null);
        $options = ['test'];
        $urlParameters = 'someParameters';

        $formTool->_call('getJavaScriptForLinkToDataHandlerAction', $urlParameters, $options);
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires $GLOBALS['BE_USER']!
     */
    public function testGetJavaScriptForLinkToDataHandlerActionHandlesConfirmCode()
    {
        $urlParameters = 'someParameters';
        $options = ['test'];

        $formTool = $this->getAccessibleMock(ToolBox::class, ['getConfirmCode']);
        $formTool->init(tx_rnbase::makeInstance(DocumentTemplate::class), null);
        $formTool
            ->expects(self::once())
            ->method('getConfirmCode')
            ->with($this->stringContains($urlParameters), $options)
            ->will(self::returnValue('confirm code handled'));

        self::assertEquals(
            'confirm code handled',
            $formTool->_call('getJavaScriptForLinkToDataHandlerAction', $urlParameters, $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLink()
    {
        $pid = 123;
        $urlParameters = 'someParameters=2&param2=bar';
        $options = ['params' => ['someParameters' => '2', 'param2' => 'bar', 'id' => $pid]];

        /** @var ToolBox $formTool */
        $formTool = $this->getMock(ToolBox::class, ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, $options)
            ->will(self::returnValue('scriptUrl'));
        unset($options['params']['id']);

        self::assertEquals(
            sprintf('<a href="%s" class="%s" title="mylabel">mylabel</a>',
                htmlspecialchars('scriptUrl'), ToolBox::CSS_CLASS_BTN),
            $formTool->createLink($urlParameters, $pid, 'mylabel', $options)
        );
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires $GLOBALS['LANG']!
     */
    public function testCreateNewLink()
    {
        $options = [
            ToolBox::OPTION_PARAMS => '&someParameters=2&param2=bar',
            ToolBox::OPTION_DEFVALS => ['tx_cfcleague_games' => ['competition' => 2, 'round' => 4]],
        ];

        $formTool = tx_rnbase::makeInstance(ToolBox::class);
        $result = $formTool->createNewLink('tx_cfcleague_games', 2, 'mylabel', $options);

        self::assertContains('class="'.ToolBox::CSS_CLASS_BTN.'"', $result);
        self::assertContains('mylabel</a>', $result);
        self::assertContains('edit[tx_cfcleague_games][2]=new', $result);
        self::assertContains('defVals[tx_cfcleague_games][competition]=2', $result);
        self::assertContains('defVals[tx_cfcleague_games][round]=4', $result);
        self::assertContains('someParameters=2', $result);
        self::assertContains('param2=bar', $result);
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithHover()
    {
        $urlParameters = 'someParameter=1';
        $options = ['hover' => 'hoverTitle'];

        $formTool = $this->getMock(ToolBox::class, ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['someParameter' => 1, 'id' => 0]])
            ->will(self::returnValue('scriptUrl'));

        self::assertEquals(
            sprintf('<a href="%s" class="%s" title="hoverTitle">mylabel</a>',
                'scriptUrl', ToolBox::CSS_CLASS_BTN),
            $formTool->createLink($urlParameters, 0, 'mylabel', $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithCssClass()
    {
        $urlParameters = 'someParameter=1';
        $options = ['class' => 'myClass'];

        $formTool = $this->getMock(ToolBox::class, ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['someParameter' => 1, 'id' => 22]])
            ->will(self::returnValue('scriptUrl'));

        self::assertEquals(
            sprintf('<a href="%s" class="myClass" title="mylabel">mylabel</a>',
                'scriptUrl'),
            $formTool->createLink($urlParameters, 22, 'mylabel', $options)
        );
    }

    /**
     * @TODO: mock T3 dependencies
     *
     * @group unit
     */
    public function testCreateLinkWithIconForTypo387()
    {
        if (TYPO3::isTYPO104OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }
        $urlParameters = 'parameter=test';
        $options = ['icon' => 'actions-add', 'class' => 'myClass'];

        $formTool = $this->getMock(ToolBox::class, ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['parameter' => 'test', 'id' => '0']])
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="jumpUrl" class="myClass" title="mylabel">.*<img.*actions-add\.svg" width="16" height="16".*<\/a>/s',
            $formTool->createLink($urlParameters, 0, 'mylabel', $options)
        );
    }

    /**
     * @TODO: mock T3 dependencies
     *
     * @group unit
     */
    public function testCreateLinkWithIconAndSizeForTypo387()
    {
        if (TYPO3::isTYPO104OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }
        $urlParameters = 'parameter=test';
        $options = ['icon' => 'actions-add', 'class' => 'myClass', 'size' => 'default'];

        $formTool = $this->getMock(ToolBox::class, ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['parameter' => 'test', 'id' => '0']])
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="jumpUrl" class="myClass" title="mylabel">.*<img.*actions\-add\.svg" width="32" height="32".*<\/a>/s',
            $formTool->createLink($urlParameters, 0, 'mylabel', $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkForDataHandlerAction()
    {
        $urlParameters = 'someParameters';
        $options = ['test' => 'value'];

        $formTool = $this->getMock(ToolBox::class, ['getJavaScriptForLinkToDataHandlerAction']);
        $formTool
            ->expects(self::once())
            ->method('getJavaScriptForLinkToDataHandlerAction')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertEquals(
            '<a href="#" class="'.ToolBox::CSS_CLASS_BTN.'" onclick="jumpUrl" >mylabel</a>',
            $formTool->createLinkForDataHandlerAction($urlParameters, 'mylabel', $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkForDataHandlerActionWithHover()
    {
        $urlParameters = 'someParameters';
        $options = ['hover' => 'hoverTitle'];

        $formTool = $this->getMock(ToolBox::class, ['getJavaScriptForLinkToDataHandlerAction']);
        $formTool
            ->expects(self::once())
            ->method('getJavaScriptForLinkToDataHandlerAction')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertEquals(
            '<a href="#" class="'.ToolBox::CSS_CLASS_BTN.'" onclick="jumpUrl" title="hoverTitle">mylabel</a>',
            $formTool->createLinkForDataHandlerAction($urlParameters, 'mylabel', $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkForDataHandlerActionWithCssClass()
    {
        $urlParameters = 'someParameters';
        $options = ['class' => 'myClass'];

        $formTool = $this->getMock(ToolBox::class, ['getJavaScriptForLinkToDataHandlerAction']);
        $formTool
            ->expects(self::once())
            ->method('getJavaScriptForLinkToDataHandlerAction')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertEquals(
            '<a href="#" class="myClass" onclick="jumpUrl" >mylabel</a>',
            $formTool->createLinkForDataHandlerAction($urlParameters, 'mylabel', $options)
        );
    }
}
