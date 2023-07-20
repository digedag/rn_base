<?php

use Sys25\RnBase\Frontend\Filter\BaseFilter;
use Sys25\RnBase\Frontend\Filter\Utility\Category;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Testing\TestUtility;

/*
 * @package TYPO3
 * @subpackage tx_myext
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 *
 *  Copyright notice
 *
 *  (c) 2010-2019 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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

/**
 * tx_rnbase_tests_filter_BaseFilter_testcase.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_filter_BaseFilter_testcase extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetCategoryFilterUtility()
    {
        $configurations = TestUtility::createConfigurations([], 'rnbase');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        self::assertInstanceOf(
            Category::class,
            $this->callInaccessibleMethod(
                tx_rnbase::makeInstance('tx_rnbase_filter_BaseFilter', $parameters, $configurations, ''),
                'getCategoryFilterUtility'
            )
        );
    }

    /**
     * @group unit
     * @dataProvider dataProviderInitReturnsCorrectValue
     */
    public function testInitReturnsCorrectValue($initFilterReturnValue, $doSearchVariableValue, $expectedReturnValue)
    {
        $configurations = TestUtility::createConfigurations([], 'myext');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $filter = $this->getAccessibleMock(
            BaseFilter::class,
            ['initFilter'],
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
     * @return bool[][]|null[][]
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
