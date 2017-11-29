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
 * Tx_Rnbase_Backend_Form_ToolBoxTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
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
        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('createSelectByArray'));
        $formTool->expects(self::once())
            ->method('createSelectByArray')
            ->with(1, 2, array('test1'), array('test2'))
            ->will(self::returnValue('returned'));

        self::assertEquals('returned', $formTool->createSelectSingleByArray(1, 2, array('test1'), array('test2')));
    }

    /**
     * @group unit
     */
    public function testCreateSelectByArray()
    {
        $formTool = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_ToolBox');
        $select = $formTool->createSelectByArray('testSelect', 2, array(1 => 'John', 2 => 'Doe'));
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
            array(1 => 'John', 2 => 'Doe'),
            array('reload' => true)
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
            array(1 => 'John', 2 => 'Doe'),
            array('onchange' => 'myJsFunction')
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
            array(1 => 'John', 2 => 'Doe'),
            array('onchange' => 'myJsFunction', 'reload' => true)
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
            array(1 => 'John', 2 => 'Doe'),
            array('multiple' => true)
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
            array(1 => 'John', 2 => 'Doe'),
            array('size' => 20)
        );
        $expectedSelect = '<select name="testSelect" class="select" size="20"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

        self::assertEquals($expectedSelect, $select);
    }

    /**
     * @group unit
     */
    public function testBuildJumpUrlInTypo387()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('wir testen die Version ab TYPO3 8.7');
        }

        $formTool = tx_rnbase::makeInstance($this->buildAccessibleProxy('Tx_Rnbase_Backend_Form_ToolBox'));
        $options = ['test'];
        $urlParameters = 'someParameters';

        self::assertRegExp(
            '/return jumpExt\(\'.*someParameters.*redirect=\'\+T3_THIS_LOCATION.*/',
            $formTool->_call('buildJumpUrl', $urlParameters, $options)
        );
    }

    /**
     * @group unit
     */
    public function testBuildJumpUrlHandlesConfirmCode()
    {
        $urlParameters = 'someParameters';
        $options = ['test'];

        $formTool = $this->getAccessibleMock('Tx_Rnbase_Backend_Form_ToolBox', array('getConfirmCode'));
        $formTool
            ->expects(self::once())
            ->method('getConfirmCode')
            ->with($this->stringContains($urlParameters), $options)
            ->will(self::returnValue('confirm code handled'));

        self::assertEquals(
            'confirm code handled',
            $formTool->_call('buildJumpUrl', $urlParameters, $options)
        );
    }

    /**
     * @group unit
     */
    public function testCreateLink()
    {
        $urlParameters = 'someParameters=2&param2=bar';
        $options = ['params'=>['someParameters' => '2', 'param2'=>'bar']];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('getLinkThisScript'));
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, $options)
            ->will(self::returnValue('scriptUrl'));
        self::assertEquals(
            htmlspecialchars('<a href="#" class="' . Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN .
                '" onclick="'."window.location.href='scriptUrl'; return false;".'" >mylabel</a>'),
            htmlspecialchars($formTool->createLink($urlParameters, 0, 'mylabel', $options))
        );
    }

    /**
     * @group unit
     */
    public function testCreateLinkWithHover()
    {
        $urlParameters = 'someParameter=1';
        $options = ['hover' => 'hoverTitle'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('getLinkThisScript'));
        $formTool
            ->expects(self::once())
            ->method('getLinkThisScript')
            ->with(false, ['params'=>['someParameter'=>1]])
            ->will(self::returnValue('scriptUrl'));

        self::assertEquals(
            htmlspecialchars('<a href="#" class="' . Tx_Rnbase_Backend_Form_ToolBox::CSS_CLASS_BTN .
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

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('getLinkThisScript'));
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
        $urlParameters = 'someParameters';
        $options = ['icon' => 'actions-add', 'class' => 'myClass'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('buildJumpUrl'));
        $formTool
            ->expects(self::once())
            ->method('buildJumpUrl')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="#" class="myClass" onclick="jumpUrl" >.*<img.*actions\-add\.svg" width="16" height="16".*<\/a>/s',
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
        $urlParameters = 'someParameters';
        $options = ['icon' => 'actions-add', 'class' => 'myClass', 'size' => 'default'];

        $formTool = $this->getMock('Tx_Rnbase_Backend_Form_ToolBox', array('buildJumpUrl'));
        $formTool
            ->expects(self::once())
            ->method('buildJumpUrl')
            ->with($urlParameters, $options)
            ->will(self::returnValue('jumpUrl'));

        self::assertRegExp(
            '/<a href="#" class="myClass" onclick="jumpUrl" >.*<img.*actions\-add\.svg" width="32" height="32".*<\/a>/s',
            $formTool->createLink($urlParameters, 0, 'mylabel', $options)
        );
    }
}
