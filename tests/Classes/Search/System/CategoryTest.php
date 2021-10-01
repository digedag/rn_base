<?php

namespace Sys25\RnBase\Search\System;

use Sys25\RnBase\Search\System\CategorySearch as Category;
use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Tests\BaseTestCase;
use tx_rnbase;

/**
 *  Copyright notice.
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
 */

/**
 * Class CategoryTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CategoryTest extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetJoinsIfNoTableAlias()
    {
        self::assertEmpty(
            $this->callInaccessibleMethod(tx_rnbase::makeInstance(Category::class), 'getJoins', []),
            'doch ein join geliefert'
        );
    }

    /**
     * @group unit
     */
    public function testGetJoinsForSysCategoryRecordMmTable()
    {
        // ' LEFT JOIN sys_category_record_mm AS SYS_CATEGORY_RECORD_MM ON'.
        // ' SYS_CATEGORY_RECORD_MM.uid_local = SYS_CATEGORY.uid';

        /* @var $join Join */
        $joins = $this->callInaccessibleMethod(
            tx_rnbase::makeInstance(Category::class),
            'getJoins',
            ['SYS_CATEGORY_RECORD_MM' => 1]
        );
        self::assertTrue(is_array($joins));
        self::assertCount(1, $joins);
        $join = reset($joins);
        self::assertInstanceOf(Join::class, $join);
        self::assertEquals('SYS_CATEGORY_RECORD_MM', $join->getAlias());
        self::assertEquals('SYS_CATEGORY', $join->getFromAlias());
        self::assertEquals('sys_category_record_mm', $join->getTable());
        self::assertEquals('SYS_CATEGORY_RECORD_MM.uid_local = SYS_CATEGORY.uid', $join->getOnClause());
    }
}
