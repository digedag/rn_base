<?php
/**
 * @package TYPO3
 * @subpackage tx_mktegutfe
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 */
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * tx_rnbase_tests_filter_BaseFilter_testcase
 *
 * @package         TYPO3
 * @subpackage      tx_rnbase
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_filter_BaseFilter_testcase extends tx_rnbase_tests_BaseTestcase
{

    /**
     * @group unit
     */
    public function testGetCategoryFilterUtility()
    {
        $configurations = $this->createConfigurations(array(), 'rnbase');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        self::assertInstanceOf(
            'Tx_Rnbase_Category_FilterUtility',
            $this->callInaccessibleMethod(
                tx_rnbase::makeInstance('tx_rnbase_filter_BaseFilter', $parameters, $configurations, ''),
                'getCategoryFilterUtility'
            )
        );
    }

    /**
     * @group unit
     */
    public function testInitDoesNothingByDefault()
    {
        $configurations = $this->createConfigurations(array(), 'mktegutfe');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getMock(
            'tx_rnbase_filter_BaseFilter', array('getCategoryFilterUtility'),
            array(
                &$parameters,
                &$configurations,
                'myList.filter.'
            )
        );
        $filter
            ->expects(self::never())
            ->method('getCategoryFilterUtility');

        $fields = $options = array();
        $filter->init($fields, $options);
    }

    /**
     * @group unit
     */
    public function testInitUsesSysCategoriesOfItemFromParametersSysCategoriesIfConfigured()
    {
        $configurations = $this->createConfigurations(
            array('myList.' => array('filter.' => array('useSysCategoriesOfItemFromParameters' => 1))), 'mktegutfe'
        );
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getMock(
            'tx_rnbase_filter_BaseFilter', array('getCategoryFilterUtility'),
            array(
                &$parameters,
                &$configurations,
                'myList.filter.'
            )
        );

        $categoryFilterUtility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('setFieldsBySysCategoriesOfItemFromParameters'));
        $categoryFilterUtility
            ->expects(self::once())
            ->method('setFieldsBySysCategoriesOfItemFromParameters')
            ->with(array('test' => 'test'), $configurations, 'myList.filter.useSysCategoriesOfItemFromParameters.')
            ->will(self::returnValue(array('test' => 'test', 'test2' => 'test2')));

        $filter
            ->expects(self::once())
            ->method('getCategoryFilterUtility')
            ->will(self::returnValue($categoryFilterUtility));

        $fields = $options = array('test' => 'test');
        $filter->init($fields, $options);
        self::assertEquals(array('test' => 'test', 'test2' => 'test2'), $fields);
    }

    /**
     * @group unit
     */
    public function testInitUsesSysCategoriesOfPluginIfConfigured()
    {
        $configurations = $this->createConfigurations(
            array('myList.' => array('filter.' => array('useSysCategoriesOfContentElement' => 1))), 'mktegutfe'
        );
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getMock(
            'tx_rnbase_filter_BaseFilter', array('getCategoryFilterUtility'),
            array(
                &$parameters,
                &$configurations,
                'myList.filter.'
            )
        );

        $categoryFilterUtility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('setFieldsBySysCategoriesOfContentElement'));
        $categoryFilterUtility
            ->expects(self::once())
            ->method('setFieldsBySysCategoriesOfContentElement')
            ->with(array('test' => 'test'), $configurations, 'myList.filter.useSysCategoriesOfContentElement.')
            ->will(self::returnValue(array('test' => 'test', 'test2' => 'test2')));

        $filter
            ->expects(self::once())
            ->method('getCategoryFilterUtility')
            ->will(self::returnValue($categoryFilterUtility));

        $fields = $options = array('test' => 'test');
        $filter->init($fields, $options);
        self::assertEquals(array('test' => 'test', 'test2' => 'test2'), $fields);
    }

    /**
     * @group unit
     */
    public function testInitUsesSysCategoriesFromParametersIfConfigured()
    {
        $configurations = $this->createConfigurations(
            array('myList.' => array('filter.' => array('useSysCategoriesFromParameters' => 1))), 'mktegutfe'
        );
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getMock(
            'tx_rnbase_filter_BaseFilter', array('getCategoryFilterUtility'),
            array(
                &$parameters,
                &$configurations,
                'myList.filter.'
            )
            );

        $categoryFilterUtility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('setFieldsBySysCategoriesFromParameters'));
        $categoryFilterUtility
            ->expects(self::once())
            ->method('setFieldsBySysCategoriesFromParameters')
            ->with(array('test' => 'test'), $configurations, 'myList.filter.useSysCategoriesFromParameters.')
            ->will(self::returnValue(array('test' => 'test', 'test2' => 'test2')));

        $filter
            ->expects(self::once())
            ->method('getCategoryFilterUtility')
            ->will(self::returnValue($categoryFilterUtility));

        $fields = $options = array('test' => 'test');
        $filter->init($fields, $options);
        self::assertEquals(array('test' => 'test', 'test2' => 'test2'), $fields);
    }

    /**
     * @group unit
     * @dataProvider dataProviderInitReturnsCorrectValue
     */
    public function testInitReturnsCorrectValue($initFilterReturnValue, $doSearchVariableValue, $expectedReturnValue)
    {
        $configurations = $this->createConfigurations(array(), 'mktegutfe');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getAccessibleMock(
            'tx_rnbase_filter_BaseFilter', array('initFilter'),
            array(
                &$parameters,
                &$configurations,
                'myList.filter.'
            )
        );
        $filter
            ->expects(self::once())
            ->method('initFilter')
            ->will(self::returnValue($initFilterReturnValue));
        $filter->_set('doSearch', $doSearchVariableValue);

        $fields = $options = array();
        self::assertSame($expectedReturnValue, $filter->init($fields, $options));
    }

    /**
     * @return boolean[][]|NULL[][]
     */
    public function dataProviderInitReturnsCorrectValue()
    {
        return array(
            // initFilter liefert true, doSearch nicht gesetzt, wir erwarten true
            array(true, null, true),
            // initFilter liefert false, doSearch nicht gesetzt, wir erwarten false
            array(false, null, false),
            // initFilter liefert false, doSearch steht auf true, wir erwarten true
            array(false, true, true),
            // initFilter liefert true, doSearch steht auf false, wir erwarten false
            array(true, false, false),
            // initFilter liefert true, doSearch steht auf true, wir erwarten true
            array(true, true, true),
            // initFilter liefert false, doSearch steht auf false, wir erwarten false
            array(false, false, false),
        );
    }
}
