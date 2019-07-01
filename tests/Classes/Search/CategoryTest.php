<?php
namespace Sys25\RnBase\Search;

/**
 *  Copyright notice
 *
 *  (c) René Nitzsche <rene@system25.de>
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
 * Class CategoryTest
 *
 * @package Sys25\RnBase\Search
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CategoryTest extends \tx_rnbase_tests_BaseTestCase
{

    /**
     * @group unit
     */
    public function testGetJoinsIfNoTableAlias()
    {
        self::assertEmpty(
            $this->callInaccessibleMethod(\tx_rnbase::makeInstance(Category::class), 'getJoins', []),
            'doch ein join geliefert'
        );
    }

    /**
     * @group unit
     */
    public function testGetJoinsForSysCategoryRecordMmTable()
    {
        $expectedJoin = ' LEFT JOIN sys_category_record_mm AS SYS_CATEGORY_RECORD_MM ON' .
                        ' SYS_CATEGORY_RECORD_MM.uid_local = SYS_CATEGORY.uid';

        self::assertEquals(
            $expectedJoin,
            $this->callInaccessibleMethod(
                \tx_rnbase::makeInstance(Category::class),
                'getJoins',
                ['SYS_CATEGORY_RECORD_MM' => 1]
            ),
            'join falsch'
        );
    }
}
