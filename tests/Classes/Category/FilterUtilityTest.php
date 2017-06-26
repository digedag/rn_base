<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('Tx_Rnbase_Category_SearchUtility');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Tx_Rnbase_Category_FilterUtilityTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Category_FilterUtilityTest extends tx_rnbase_tests_BaseTestCase
{

    /**
     *
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        if (isset($_GET['second_ext']['second_parameter'])) {
            unset($_GET['second_ext']['second_parameter']);
        }
    }

    /**
     * @group unit
     */
    public function testGetDatabaseConnection()
    {
        self::assertInstanceOf(
            'Tx_Rnbase_Database_Connection',
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Rnbase_Category_FilterUtility'), 'getDatabaseConnection')
        );
    }

    /**
     * @group unit
     */
    public function testGetCategoryUidsOfCurrentDetailViewItem()
    {
        $databaseConnection = $this->getMock('Tx_Rnbase_Database_Connection', array('doSelect'));
        $databaseConnection
            ->expects(self::once())
            ->method('doSelect')
            ->with(
                'uid_local', 'sys_category_record_mm',
                array(
                    'where' =>
                        'sys_category_record_mm.tablenames = \'second_table\' AND ' .
                        'sys_category_record_mm.fieldname = \'second_field\' AND ' .
                        'sys_category_record_mm.uid_foreign = 123',
                    'enablefieldsoff' => true
                )
            )
            ->will(self::returnValue(array(
                0 => array('uid_local' => 1),
                1 => array('uid_local' => 2),
            )));

        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getDatabaseConnection'));
        $utility
            ->expects(self::once())
            ->method('getDatabaseConnection')
            ->will(self::returnValue($databaseConnection));

        $_GET['second_ext']['second_parameter'] = 123;
        $configurations = $this->createConfigurations(
            array('confId.' => array('supportedParameters.' => array(
                0 => array(
                    'parameterQualifier' => 'first_ext',
                    'parameterName' => 'first_parameter',
                    'table' => 'first_table',
                    'categoryField' => 'first_field',
                ),
                1 => array(
                    'parameterQualifier' => 'second_ext',
                    'parameterName' => 'second_parameter',
                    'table' => 'second_table',
                    'categoryField' => 'second_field',
                ),
                2 => array(
                    'parameterQualifier' => 'third_ext',
                    'parameterName' => 'third_parameter',
                    'table' => 'third_table',
                    'categoryField' => 'third_field',
                ),
            ))),
            'rn_base',
            '',
            tx_rnbase::makeInstance('tx_rnbase_parameters')
        );

        self::assertEquals(
            array(1,2),
            $this->callInaccessibleMethod($utility, 'getCategoryUidsOfCurrentDetailViewItem', $configurations, 'confId.')
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfItemFromParametersIfNoCategoriesFound()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $confId = 'confId.';
        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsOfCurrentDetailViewItem'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsOfCurrentDetailViewItem')
            ->with($configurations, $confId)
            ->will(self::returnValue(array()));


        self::assertEquals(
            array('test' => 'test'),
            $utility->setFieldsBySysCategoriesOfItemFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfItemFromParametersIfCategoriesFound()
    {
        $configurations = $this->createConfigurations(array(), 'rn_base');
        $confId = 'confId.';
        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsOfCurrentDetailViewItem'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsOfCurrentDetailViewItem')
            ->with($configurations, $confId)
            ->will(self::returnValue(array(1,2,3)));

        self::assertEquals(
            array('test' => 'test', 'SYS_CATEGORY.uid' => array(OP_IN_INT => '1,2,3')),
            $utility->setFieldsBySysCategoriesOfItemFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfItemFromParametersIfOtherAlias()
    {
        $confId = 'confId.';
        $configurations = $this->createConfigurations(array($confId => array('sysCategoryTableAlias' => 'ALIAS')), 'rn_base');
        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsOfCurrentDetailViewItem'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsOfCurrentDetailViewItem')
            ->with($configurations, $confId)
            ->will(self::returnValue(array(1,2,3)));

        self::assertEquals(
            array('test' => 'test', 'ALIAS.uid' => array(OP_IN_INT => '1,2,3')),
            $utility->setFieldsBySysCategoriesOfItemFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testGetCategoryUidsByReference()
    {
        $databaseConnection = $this->getMock('Tx_Rnbase_Database_Connection', array('doSelect'));
        $databaseConnection
            ->expects(self::once())
            ->method('doSelect')
            ->with(
                'uid_local', 'sys_category_record_mm',
                array(
                    'where' =>
                        'sys_category_record_mm.tablenames = \'test_table\' AND ' .
                        'sys_category_record_mm.fieldname = \'test_field\' AND ' .
                        'sys_category_record_mm.uid_foreign = 123',
                    'enablefieldsoff' => true
                )
                )
            ->will(self::returnValue(array(
                0 => array('uid_local' => 1),
                1 => array('uid_local' => 2),
            )));

        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getDatabaseConnection'));
        $utility
            ->expects(self::once())
            ->method('getDatabaseConnection')
            ->will(self::returnValue($databaseConnection));

        self::assertEquals(
            array(1,2),
            $this->callInaccessibleMethod($utility, 'getCategoryUidsByReference', 'test_table', 'test_field', 123)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfContentElementIfNoCategoriesFound()
    {
        $contentObject = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $contentObject->data['uid'] = 123;
        $configurations = $this->createConfigurations(array(), 'rn_base', '', $contentObject);
        $confId = 'confId.';
        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsByReference'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsByReference')
            ->with('tt_content', 'categories', 123)
            ->will(self::returnValue(array()));


        self::assertEquals(
            array('test' => 'test'),
            $utility->setFieldsBySysCategoriesOfContentElement(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfContentElementIfCategoriesFound()
    {
        $contentObject = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $contentObject->data['uid'] = 123;
        $configurations = $this->createConfigurations(array(), 'rn_base', '', $contentObject);
        $confId = 'confId.';
        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsByReference'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsByReference')
            ->with('tt_content', 'categories', 123)
            ->will(self::returnValue(array(1,2,3)));

        self::assertEquals(
            array('test' => 'test', 'SYS_CATEGORY.uid' => array(OP_IN_INT => '1,2,3')),
            $utility->setFieldsBySysCategoriesOfContentElement(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesOfContentElementIfOtherAlias()
    {
        $contentObject = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $contentObject->data['uid'] = 123;
        $confId = 'confId.';
        $configurations = $this->createConfigurations(
            array($confId => array('sysCategoryTableAlias' => 'ALIAS')), 'rn_base', '', $contentObject
        );

        $utility = $this->getMock('Tx_Rnbase_Category_FilterUtility', array('getCategoryUidsByReference'));
        $utility
            ->expects(self::once())
            ->method('getCategoryUidsByReference')
            ->with('tt_content', 'categories', 123)
            ->will(self::returnValue(array(1,2,3)));

        self::assertEquals(
            array('test' => 'test', 'ALIAS.uid' => array(OP_IN_INT => '1,2,3')),
            $utility->setFieldsBySysCategoriesOfContentElement(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesFromParametersIfNoCategoriesFound()
    {
        $confId = 'confId.';
        $configurations = $this->createConfigurations(
            array($confId => array(
                'parameterQualifier' => 'second_ext',
                'parameterName' => 'second_parameter'
            )),
            'rn_base', '', tx_rnbase::makeInstance('tx_rnbase_parameters')
        );
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Category_FilterUtility');

        self::assertEquals(
            array('test' => 'test'),
            $utility->setFieldsBySysCategoriesFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesFromParametersIfCategoriesFound()
    {
        $_GET['second_ext']['second_parameter'] = 123;
        $confId = 'confId.';
        $configurations = $this->createConfigurations(
            array($confId => array(
                'parameterQualifier' => 'second_ext',
                'parameterName' => 'second_parameter'
            )),
            'rn_base', '', tx_rnbase::makeInstance('tx_rnbase_parameters')
        );
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Category_FilterUtility');

        self::assertEquals(
            array('test' => 'test', 'SYS_CATEGORY.uid' => array(OP_IN_INT => '123')),
            $utility->setFieldsBySysCategoriesFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }

    /**
     * @group unit
     */
    public function testSetFieldsBySysCategoriesFromParametersIfOtherAlias()
    {
        $_GET['second_ext']['second_parameter'] = 123;
        $confId = 'confId.';
        $configurations = $this->createConfigurations(
            array($confId => array(
                'sysCategoryTableAlias' => 'ALIAS',
                'parameterQualifier' => 'second_ext',
                'parameterName' => 'second_parameter'
            )), 'rn_base', '', $contentObject,
            tx_rnbase::makeInstance('tx_rnbase_parameters')
        );

        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Category_FilterUtility');

        self::assertEquals(
            array('test' => 'test', 'ALIAS.uid' => array(OP_IN_INT => '123')),
            $utility->setFieldsBySysCategoriesFromParameters(array('test' => 'test'), $configurations, $confId)
        );
    }
}
