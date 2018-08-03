<?php
/**
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
 * tx_rnbase_tests_filter_BaseFilter_testcase.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_filter_BaseFilter_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetCategoryFilterUtility()
    {
        $configurations = $this->createConfigurations([], 'rnbase');
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
    public function testInitCallsHandleSysCategoryFilterCorrect()
    {
        $configurations = $this->createConfigurations([], 'myext');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getAccessibleMock(
            'tx_rnbase_filter_BaseFilter', ['handleSysCategoryFilter'],
            [
                &$parameters,
                &$configurations,
                'myList.filter.',
            ]
        );
        $filter
            ->expects(self::once())
            ->method('handleSysCategoryFilter')
            ->with([])
            ->will(self::returnValue(['test']));

        $fields = $options = [];
        $filter->init($fields, $options);

        self::assertEquals(['test'], $fields);
        self::assertNull($filter->_get('doSearch'), '$doSearch nicht null');
    }

    /**
     * @group unit
     * @dataProvider dataProviderHandleSysCategoryFilter
     */
    public function testHandleSysCategoryFilter(
        $confId, $dontSearchIfNoCategoriesFound, $filterUtilityMethod, $expectedFields, $expectedDoSearchValue
    ) {
        $configurations = $this->createConfigurations(
            ['myList.' => ['filter.' => [
                $confId     => 1,
                $confId.'.' => [
                    'dontSearchIfNoCategoriesFound' => $dontSearchIfNoCategoriesFound,
                ],
            ]]], 'myext'
        );
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getAccessibleMock(
            'tx_rnbase_filter_BaseFilter', ['getCategoryFilterUtility'],
            [
                &$parameters,
                &$configurations,
                'myList.filter.',
            ]
        );

        $categoryFilterUtility = $this->getMock('Tx_Rnbase_Category_FilterUtility', [$filterUtilityMethod]);
        $categoryFilterUtility
            ->expects(self::once())
            ->method($filterUtilityMethod)
            ->with(['test' => 'test'], $configurations, 'myList.filter.'.$confId.'.')
            ->will(self::returnValue($expectedFields));

        $filter
            ->expects(self::once())
            ->method('getCategoryFilterUtility')
            ->will(self::returnValue($categoryFilterUtility));

        $fields = ['test' => 'test'];
        $fields = $this->callInaccessibleMethod($filter, 'handleSysCategoryFilter', $fields);

        self::assertEquals($expectedFields, $fields);
        self::assertSame($expectedDoSearchValue, $filter->_get('doSearch'));
    }

    /**
     * @return string[][]|number[][]|string[][][]
     */
    public function dataProviderHandleSysCategoryFilter()
    {
        return [
            [
                'useSysCategoriesOfItemFromParameters', 0, 'setFieldsBySysCategoriesOfItemFromParameters',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesOfItemFromParameters', 1, 'setFieldsBySysCategoriesOfItemFromParameters',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesOfItemFromParameters', 1, 'setFieldsBySysCategoriesOfItemFromParameters',
                ['test' => 'test'], false,
            ],
            [
                'useSysCategoriesOfContentElement', 0, 'setFieldsBySysCategoriesOfContentElement',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesOfContentElement', 1, 'setFieldsBySysCategoriesOfContentElement',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesOfContentElement', 1, 'setFieldsBySysCategoriesOfContentElement',
                ['test' => 'test'], false,
            ],
            [
                'useSysCategoriesFromParameters', 0, 'setFieldsBySysCategoriesFromParameters',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesFromParameters', 1, 'setFieldsBySysCategoriesFromParameters',
                ['test' => 'test', 'test2' => 'test2'], null,
            ],
            [
                'useSysCategoriesFromParameters', 1, 'setFieldsBySysCategoriesFromParameters',
                ['test' => 'test'], false,
            ],
        ];
    }

    /**
     * @group unit
     * @dataProvider dataProviderInitReturnsCorrectValue
     */
    public function testInitReturnsCorrectValue($initFilterReturnValue, $doSearchVariableValue, $expectedReturnValue)
    {
        $configurations = $this->createConfigurations([], 'myext');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $filter = $this->getAccessibleMock(
            'tx_rnbase_filter_BaseFilter', ['initFilter'],
            [
                &$parameters,
                &$configurations,
                'myList.filter.',
            ]
        );
        $filter
            ->expects(self::once())
            ->method('initFilter')
            ->will(self::returnValue($initFilterReturnValue));
        $filter->_set('doSearch', $doSearchVariableValue);

        $fields = $options = [];
        self::assertSame($expectedReturnValue, $filter->init($fields, $options));
    }

    /**
     * @return boolean[][]|NULL[][]
     */
    public function dataProviderInitReturnsCorrectValue()
    {
        return [
            // initFilter liefert true, doSearch nicht gesetzt, wir erwarten true
            [true, null, true],
            // initFilter liefert false, doSearch nicht gesetzt, wir erwarten false
            [false, null, false],
            // initFilter liefert false, doSearch steht auf true, wir erwarten true
            [false, true, true],
            // initFilter liefert true, doSearch steht auf false, wir erwarten false
            [true, false, false],
            // initFilter liefert true, doSearch steht auf true, wir erwarten true
            [true, true, true],
            // initFilter liefert false, doSearch steht auf false, wir erwarten false
            [false, false, false],
        ];
    }
}
