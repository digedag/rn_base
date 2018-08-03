<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2017 Rene Nitzsche (rene@system25.de)
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

/**
 * Tx_Rnbase_Backend_Form_ToolBoxTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_Form_ToolBoxTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testCreateSelectSingleByArrayCallsJustCreateSelectByArray()
    {
        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['createSelectByArray']);
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
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray('testSelect', 2, [1 => 'John', 2 => 'Doe']);
        $expectedSelect = '<select name="testSelect" class="select"><option value="1" >John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArrayIfReloadOption()
    {
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1        => 'John', 2 => 'Doe'],
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
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1          => 'John', 2 => 'Doe'],
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
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray(
            'testSelect',
            1,
            [1          => 'John', 2 => 'Doe'],
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
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray(
            'testSelect',
            '1,2',
            [1          => 'John', 2 => 'Doe'],
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
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray(
            'testSelect',
            '1,2',
            [1      => 'John', 2 => 'Doe'],
            ['size' => 20]
        );
        $expectedSelect = '<select name="testSelect" class="select" size="20"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testGetJavaScriptForLinkToDataHandlerActionInTypo387()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }

        $formTool = tx_rnbase::makeInstance($this->buildAccessibleProxy('Tx_Rnbase_Backend_Form_ToolBox'));
        $formTool->init(tx_rnbase::makeInstance('Tx_Rnbase_Backend_Template_Override_DocumentTemplate'), null);
        $options = ['test'];
        $urlParameters = 'someParameters';

        self::assertRegExp(
            '/return jumpToUrl\(\'.*someParameters.*redirect=\'\+T3_THIS_LOCATION.*/',
            $formTool->_call('getJavaScriptForLinkToDataHandlerAction', $urlParameters, $options)
        );
    }

    /**
     * @group unit
     */
    public function testGetJavaScriptForLinkToDataHandlerActionAddsNecessaryJavaScriptsInTypo387()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }

        $formTool = $this->getAccessibleMock('Tx_Rnbase_Backend_Form_ToolBox', ['getBaseJavaScriptCode']);
        $formTool
            ->expects(self::once())
            ->method('getBaseJavaScriptCode')
            ->will(self::returnValue('javascriptCode'));

        $pageRenderer = $this->getMock('stdClass', ['addJsInlineCode']);
        $pageRenderer
            ->expects(self::once())
            ->method('addJsInlineCode')
            ->with('rnBaseMethods', 'javascriptCode');

        $document = $this->getMock('Tx_Rnbase_Backend_Template_Override_DocumentTemplate', ['getPageRenderer']);
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
     * @group unit
     */
    public function testGetJavaScriptForLinkToDataHandlerActionHandlesConfirmCode()
    {
        $urlParameters = 'someParameters';
        $options = ['test'];

        $formTool = $this->getAccessibleMock('Tx_Rnbase_Backend_Form_ToolBox', ['getConfirmCode']);
        $formTool->init(tx_rnbase::makeInstance('Tx_Rnbase_Backend_Template_Override_DocumentTemplate'), null);
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
        $urlParameters = 'someParameters=2&param2=bar';
        $options = ['params'=>['someParameters' => '2', 'param2'=>'bar']];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, $options)
            ->will(self::returnValue('scriptUrl'));
        self::assertEquals(
            htmlspecialchars('<a href="#" class="'.Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN.
                '" onclick="'."window.location.href='scriptUrl'; return false;".'" >mylabel</a>'),
            htmlspecialchars($formTool->createLink($urlParameters, 0, 'mylabel', $options))
        );
    }

    /**
     * @group unit
     */
    public function testCreateNewLink()
    {
        $options = [
            Tx_Rnbase_Backend_Form_ToolBox::OPTION_PARAMS  => '&someParameters=2&param2=bar',
            Tx_Rnbase_Backend_Form_ToolBox::OPTION_DEFVALS => ['tx_cfcleague_games' => ['competition' => 2, 'round' => 4]],
        ];

        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $result = $formTool->createNewLink('tx_cfcleague_games', 2, 'mylabel', $options);

        self::assertContains('class="'.Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN.'"', $result);
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

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params'=>['someParameter'=>1]])
            ->will(self::returnValue('scriptUrl'));

        self::assertEquals(
            htmlspecialchars('<a href="#" class="'.Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN.
                '" onclick="'."window.location.href='scriptUrl'; return false;".'" title="hoverTitle">mylabel</a>'),
            htmlspecialchars($formTool->createLink($urlParameters, 0, 'mylabel', $options))
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithCssClass()
    {
        $urlParameters = 'someParameter=1';
        $options = ['class' => 'myClass'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params'=>['someParameter'=>1]])
            ->will(self::returnValue('scriptUrl'));

        self::assertEquals(
            htmlspecialchars('<a href="#" class="myClass" onclick="'.
                "window.location.href='scriptUrl'; return false;".'" >mylabel</a>'),
            htmlspecialchars($formTool->createLink($urlParameters, 0, 'mylabel', $options))
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithIconForTypo387()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }
        $urlParameters = 'parameter=test';
        $options = ['icon' => 'actions-add', 'class' => 'myClass'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['parameter' => 'test']])
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="#" class="myClass" onclick="window.location.href=\'jumpUrl\'; return false;" >.*<img.*actions\-add\.svg" width="16" height="16".*<\/a>/s',
            $formTool->createLink($urlParameters, 0, 'mylabel', $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithIconAndSizeForTypo387()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }
        $urlParameters = 'parameter=test';
        $options = ['icon' => 'actions-add', 'class' => 'myClass', 'size' => 'default'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getLinkThisScript']);
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params' => ['parameter' => 'test']])
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="#" class="myClass" onclick="window.location.href=\'jumpUrl\'; return false;" >.*<img.*actions\-add\.svg" width="32" height="32".*<\/a>/s',
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

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getJavaScriptForLinkToDataHandlerAction']);
        $formTool
            ->expects(self::once())
            ->method('getJavaScriptForLinkToDataHandlerAction')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertEquals(
            '<a href="#" class="'.Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN.'" onclick="jumpUrl" >mylabel</a>',
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

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getJavaScriptForLinkToDataHandlerAction']);
        $formTool
            ->expects(self::once())
            ->method('getJavaScriptForLinkToDataHandlerAction')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertEquals(
            '<a href="#" class="'.Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN.'" onclick="jumpUrl" title="hoverTitle">mylabel</a>',
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

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', ['getJavaScriptForLinkToDataHandlerAction']);
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
