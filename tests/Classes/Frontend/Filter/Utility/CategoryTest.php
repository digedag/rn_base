<?php

namespace Sys25\RnBase\Frontend\Filter\Utility;

use Prophecy\PhpUnit\ProphecyTrait;
use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Testing\BaseTestCase;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2021 Rene Nitzsche (rene@system25.de)
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
 * Tx_Rnbase_Category_FilterUtilityTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CategoryTest extends BaseTestCase
{
    use ProphecyTrait;

    protected $configurations;

    protected $categoryUtil;

    protected $parametersMock;

    protected $dbConnection;

    protected function setUp(): void
    {
        $this->dbConnection = $this->prophesize(Connection::class);
        $this->parametersMock = $this->prophesize(Parameters::class);
        $this->configurations = $this->prophesize(Processor::class);
        $this->configurations->getParameters()->willReturn($this->parametersMock->reveal());
        $this->categoryUtil = new Category($this->configurations->reveal(), 'myList.filter.');
        $this->categoryUtil->setDatabaseConnection($this->dbConnection->reveal());
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        if (isset($_GET['second_ext']['second_parameter'])) {
            unset($_GET['second_ext']['second_parameter']);
        }
    }

    // ---------------------------------

    /**
     * @group unit
     */
    public function testHandleSysCategoryFilterCorrect()
    {
        $this->configurations->get('myList.filter.useSysCategoriesOfItemFromParameters')->willReturn(0);
        $this->configurations->get('myList.filter.useSysCategoriesOfContentElement')->willReturn(0);
        $this->configurations->get('myList.filter.useSysCategoriesFromParameters')->willReturn(0);

        $fields = [];
        $this->assertTrue($this->categoryUtil->handleSysCategoryFilter($fields, true));
        $this->assertFalse($this->categoryUtil->handleSysCategoryFilter($fields, false));
    }

    /**
     * @group unit
     *
     * @dataProvider dataProviderHandleSysCategoryFilter
     */
    public function testHandleSysCategoryFilter(
        $configs,
        $fields,
        $expectedFields,
        $expectedDoSearchValue
    ) {
        if (!defined('OP_IN_INT')) {
            define('OP_IN_INT', 'IN');
        }
        foreach ($configs as $confId => $data) {
            $this->configurations->get('myList.filter.'.$confId)->willReturn($data);
        }

        $this->parametersMock->getInt('param1', 'myext')->willReturn(23);
        $this->dbConnection->doSelect('uid_local', 'sys_category_record_mm', \Prophecy\Argument::any())
                ->willReturn([
                    ['uid_local' => 3],
                    ['uid_local' => 5],
                ]);
        $this->dbConnection->fullQuoteStr('mytable')->willReturn('mytable');
        $this->dbConnection->fullQuoteStr('catfield')->willReturn('catfield');

        $doSearch = $this->categoryUtil->handleSysCategoryFilter($fields, null);

        self::assertEquals($expectedFields, $fields);
        self::assertSame($expectedDoSearchValue, $doSearch);
    }

    /**
     * @return string[][]|number[][]|string[][][]
     */
    public static function dataProviderHandleSysCategoryFilter()
    {
        return [
            [
                [
                    'useSysCategoriesOfItemFromParameters' => 1,
                    'useSysCategoriesOfItemFromParameters.dontSearchIfNoCategoriesFound' => 0,
                    'useSysCategoriesOfItemFromParameters.supportedParameters.' => [
                        '10.' => [
                            'parameterName' => 'param1',
                            'parameterQualifier' => 'myext',
                            'table' => 'mytable',
                            'categoryField' => 'catfield',
                        ],
                    ],
                    'useSysCategoriesOfItemFromParameters.sysCategoryTableAlias' => 'CAT',
                    'useSysCategoriesOfContentElement' => 0,
                    'useSysCategoriesOfContentElement.dontSearchIfNoCategoriesFound' => 0,
                    'useSysCategoriesFromParameters' => 0,
                    'useSysCategoriesFromParameters.dontSearchIfNoCategoriesFound' => 0,
                ],

                ['TABLE.field' => [OP_EQ_INT => 23]],
                ['TABLE.field' => [OP_EQ_INT => 23], 'CAT.uid' => ['IN' => '3,5']], null,
            ],
        ];
    }
}
