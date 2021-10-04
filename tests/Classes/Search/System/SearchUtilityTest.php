<?php

namespace Sys25\RnBase\Search\System;

use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Search\System\CategorySearchUtility as SearchUtility;
use Sys25\RnBase\Tests\BaseTestCase;
use tx_rnbase;

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
 * Tx_Rnbase_Category_SearchUtilityTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class SearchUtilityTest extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testAddTableMapping()
    {
        self::assertEquals(
            ['SYS_CATEGORY' => 'sys_category'],
            tx_rnbase::makeInstance(SearchUtility::class)->addTableMapping([])
        );
        self::assertEquals(
            ['ALREADY' => 'present', 'SYS_CATEGORY' => 'sys_category'],
            tx_rnbase::makeInstance(SearchUtility::class)->addTableMapping(['ALREADY' => 'present'])
        );
        self::assertEquals(
            ['MY_NEW_ALIAS' => 'sys_category'],
            tx_rnbase::makeInstance(SearchUtility::class)->addTableMapping([], 'MY_NEW_ALIAS')
        );
    }

    /**
     * @group unit
     */
    public function testAddTableJoins()
    {
        $joins = tx_rnbase::makeInstance(SearchUtility::class)->addJoins(
            'my_table',
            'MY_ALIAS',
            'my_field',
            ['SYS_CATEGORY' => 1]
        );
        self::assertCount(2, $joins);
        $join = $joins[0];

        self::assertInstanceOf(Join::class, $join);
        self::assertEquals('MY_ALIAS', $join->getFromAlias());
        self::assertEquals('SYS_CATEGORY_MM', $join->getAlias());
        self::assertEquals('sys_category_record_mm', $join->getTable());
        self::assertEquals(
            'SYS_CATEGORY_MM.uid_foreign = MY_ALIAS.uid AND SYS_CATEGORY_MM.tablenames = \'my_table\' AND SYS_CATEGORY_MM.fieldname = \'my_field\'',
            $join->getOnClause()
        );
        $join = $joins[1];
        self::assertInstanceOf(Join::class, $join);
        self::assertEquals('SYS_CATEGORY_MM', $join->getFromAlias());
        self::assertEquals('SYS_CATEGORY', $join->getAlias());
        self::assertEquals('sys_category', $join->getTable());
        self::assertEquals('SYS_CATEGORY.uid = SYS_CATEGORY_MM.uid_local', $join->getOnClause());

        $joins = tx_rnbase::makeInstance(SearchUtility::class)->addJoins(
            'my_table',
            'MY_ALIAS',
            'my_field',
            ['SYS_CATEGORY_2' => 1],
            'SYS_CATEGORY_2'
        );

        self::assertCount(2, $joins);
        $join = $joins[0];
        self::assertInstanceOf(Join::class, $join);
        self::assertEquals('MY_ALIAS', $join->getFromAlias());
        self::assertEquals('SYS_CATEGORY_2_MM', $join->getAlias());
        self::assertEquals('sys_category_record_mm', $join->getTable());
        self::assertEquals(
            'SYS_CATEGORY_2_MM.uid_foreign = MY_ALIAS.uid AND SYS_CATEGORY_2_MM.tablenames = \'my_table\' AND SYS_CATEGORY_2_MM.fieldname = \'my_field\'',
            $join->getOnClause()
        );
        $join = $joins[1];
        self::assertInstanceOf(Join::class, $join);
        self::assertEquals('SYS_CATEGORY_2_MM', $join->getFromAlias());
        self::assertEquals('SYS_CATEGORY_2', $join->getAlias());
        self::assertEquals('sys_category', $join->getTable());
        self::assertEquals('SYS_CATEGORY_2.uid = SYS_CATEGORY_2_MM.uid_local', $join->getOnClause());

//         self::assertEquals(
//             ' LEFT JOIN sys_category_record_mm'.
//             ' AS SYS_CATEGORY_2_MM ON SYS_CATEGORY_2_MM.uid_foreign'.
//             ' = MY_ALIAS.uid AND SYS_CATEGORY_2_MM.tablenames = "my_table" AND SYS_CATEGORY_2_MM.fieldname = "my_field"'.
//             ' LEFT JOIN sys_category AS SYS_CATEGORY_2 ON SYS_CATEGORY_2.uid = SYS_CATEGORY_2_MM.uid_local',
//         );
    }

    public function testAddTableJoinsWithOtherTable()
    {
        self::assertCount(
            0,
            tx_rnbase::makeInstance(SearchUtility::class)->addJoins(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SOME_OTHER_ALIAS' => 1]
            )
        );

        self::assertEquals(
            0,
            tx_rnbase::makeInstance(SearchUtility::class)->addJoins(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SYS_CATEGORY' => 1],
                'SYS_CATEGORY_2'
            )
        );
    }

    /**
     * @group unit
     */
    public function testAddTableJoinsWithoutAlias()
    {
        self::assertEquals(
            ' LEFT JOIN sys_category_record_mm'.
            ' ON sys_category_record_mm.uid_foreign'.
            ' = my_table.uid AND sys_category_record_mm.tablenames = "my_table" AND sys_category_record_mm.fieldname = "my_field"'.
            ' LEFT JOIN sys_category ON sys_category.uid = sys_category_record_mm.uid_local',
            \tx_rnbase::makeInstance(SearchUtility::class)->addJoinsWithoutAlias(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SYS_CATEGORY' => 1]
            )
        );

        self::assertEquals(
            '',
            \tx_rnbase::makeInstance(SearchUtility::class)->addJoinsWithoutAlias(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SOME_OTHER_ALIAS' => 1]
            )
        );

        self::assertEquals(
            '',
            \tx_rnbase::makeInstance(SearchUtility::class)->addJoinsWithoutAlias(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SYS_CATEGORY' => 1],
                'SYS_CATEGORY_2'
            )
        );

        self::assertEquals(
            ' LEFT JOIN sys_category_record_mm'.
            ' ON sys_category_record_mm.uid_foreign'.
            ' = my_table.uid AND sys_category_record_mm.tablenames = "my_table" AND sys_category_record_mm.fieldname = "my_field"'.
            ' LEFT JOIN sys_category ON sys_category.uid = sys_category_record_mm.uid_local',
            \tx_rnbase::makeInstance(SearchUtility::class)->addJoinsWithoutAlias(
                'my_table',
                'MY_ALIAS',
                'my_field',
                ['SYS_CATEGORY_2' => 1],
                'SYS_CATEGORY_2'
            )
        );
    }
}
