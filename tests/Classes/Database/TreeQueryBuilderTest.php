<?php

namespace Sys25\RnBase\Database;

use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Runner\Version;
use PHPUnit_Framework_MockObject_MockObject;
use Sys25\RnBase\Testing\BaseTestCase;

/**
 *  Copyright notice.
 *
 *  (c) 2016-2025 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * @group unit
 *
 * @author Mario Seidel <mario.seidel@dmk-ebusiness.de>
 */
class TreeQueryBuilderTest extends BaseTestCase
{
    /**
     * test tree sturcture like:
     * 1
     * |
     * -- 2
     *    |
     *    -- 3
     *    |  |
     *    |  -- 4
     *    |
     *    -- 6
     *       |
     *       -- 7.
     */
    public function testGetTreeRecursive()
    {
        $expectedCalls = [
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (1)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (2)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (3)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (4)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (6)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (7)', 'tableName' => 'pages'],
            ],
        ];

        $returnValues = [
            [['uid' => 2]],
            [['uid' => 3], ['uid' => 6]],
            [['uid' => 4]],
            [],
            [['uid' => 7]],
            [],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function (MockObject $connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::exactly(6))
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe, dass die aufgerufenen Argumente mit den erwarteten Werten übereinstimmen
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den entsprechenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1);

        self::assertEquals([1, 2, 3, 4, 6, 7], $uidList);
    }

    public function testLimitedTreeByDepth()
    {
        $options = [
            'depth' => 2,
        ];

        $expectedCalls = [
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (1)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (2)', 'tableName' => 'pages'],
            ],
        ];

        $returnValues = [
            [['uid' => 2]],
            [['uid' => 3], ['uid' => 6]],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe, dass die aufgerufenen Argumente mit den erwarteten übereinstimmen
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den entsprechenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 2, 3, 6], $uidList);
    }

    public function testAddPidToCustomQueryCorrectly()
    {
        $options = [
            'where' => 'hidden=1',
        ];

        $expectedCalls = [
            [
                'uid',
                'pages',
                ['where' => 'hidden=1 AND pid IN (1)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => 'hidden=1 AND pid IN (2)', 'tableName' => 'pages'],
            ],
        ];

        $returnValues = [
            [['uid' => 2]],
            [['uid' => 3]],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::any())
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe, dass die aufgerufenen Argumente mit den erwarteten übereinstimmen
                        $expectedCall = array_shift($expectedCalls);
                        if (null === $expectedCall) {
                            return [];
                        }
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den entsprechenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 2, 3], $uidList);
    }

    public function testSetCustomTableNameCorrectly()
    {
        $options = [
            'tableName' => 'tt_content',
        ];

        $expectedCalls = [
            [
                'uid',
                'tt_content',
                ['where' => '1=1 AND pid IN (1)', 'tableName' => 'tt_content'],
            ],
            [
                'uid',
                'tt_content',
                ['where' => '1=1 AND pid IN (33)', 'tableName' => 'tt_content'],
            ],
            [
                'uid',
                'tt_content',
                ['where' => '1=1 AND pid IN (44)', 'tableName' => 'tt_content'],
            ],
        ];

        $returnValues = [
            [['uid' => 33], ['uid' => 44]],
            [],
            [],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::exactly(3))
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe die Argumente mit den erwarteten Werten
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den passenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getTreeUidListRecursive(1, 2, 0, $options);

        self::assertEquals([1, 33, 44], $uidList);
    }

    public function testSetQueryOptions()
    {
        $options = [
            'where' => '(starttime > 12345 AND endtime < 98765)',
            'tableName' => 'tt_content',
            'orderby' => 'header',
            'limit' => 1,
        ];

        $expectedCalls = [
            [
                'uid',
                'tt_content',
                [
                    'where' => '(starttime > 12345 AND endtime < 98765) AND pid IN (1)',
                    'tableName' => 'tt_content',
                    'orderby' => 'header',
                    'limit' => 1,
                ],
            ],
            [
                'uid',
                'tt_content',
                [
                    'where' => '(starttime > 12345 AND endtime < 98765) AND pid IN (2)',
                    'tableName' => 'tt_content',
                    'orderby' => 'header',
                    'limit' => 1,
                ],
            ],
        ];

        $returnValues = [
            [['uid' => 2]],
            [],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe, dass die Argumente mit den erwarteten Werten übereinstimmen
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den passenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getTreeUidListRecursive(1, 2, 0, $options);

        $this->assertEquals([1, 2], $uidList);
    }

    public function testSetCustomParentField()
    {
        $options = [
            'parentField' => 'parent_id',
        ];

        $expectedCalls = [
            [
                'uid',
                'pages',
                [
                    'where' => '1=1 AND parent_id IN (1)',
                    'tableName' => 'pages',
                    'parentField' => 'parent_id',
                ],
            ],
            [
                'uid',
                'pages',
                [
                    'where' => '1=1 AND parent_id IN (5)',
                    'tableName' => 'pages',
                    'parentField' => 'parent_id',
                ],
            ],
        ];

        $returnValues = [
            [['uid' => 5]],
            [],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe, dass die Argumente mit den erwarteten Werten übereinstimmen
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den passenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 5], $uidList);
    }

    public function testGetTreeWithCommaSeparatedPidList()
    {
        $expectedCalls = [
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (1,2,3)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (5)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (6)', 'tableName' => 'pages'],
            ],
            [
                'uid',
                'pages',
                ['where' => '1=1 AND pid IN (7)', 'tableName' => 'pages'],
            ],
        ];

        $returnValues = [
            [['uid' => 5]],
            [['uid' => 6], ['uid' => 7]],
            [],
            [],
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) use (&$expectedCalls, &$returnValues) {
                $connection->expects(self::any())
                    ->method('doSelect')
                    ->willReturnCallback(function ($fields, $table, $criteria) use (&$expectedCalls, &$returnValues) {
                        // Überprüfe die Argumente mit den erwarteten Werten
                        $expectedCall = array_shift($expectedCalls);
                        self::assertSame($expectedCall[0], $fields);
                        self::assertSame($expectedCall[1], $table);
                        self::assertEqualsCanonicalizing($expectedCall[2], $criteria);

                        // Gib den passenden Rückgabewert zurück
                        return array_shift($returnValues);
                    });
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList('1,2,3');

        $this->assertEquals([1, 2, 3, 5, 6, 7], $uidList);
    }

    /**
     * get a mock with tree structure defined in the expectFunc.
     *
     * @param Closure $expectFunc
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTreeQueryBuilderMock($expectFunc)
    {
        $connection = $this->getMock(
            Connection::class,
            get_class_methods(Connection::class)
        );

        $expectFunc($connection);

        $treeQueryBuildMock = $this->getMock(
            TreeQueryBuilder::class,
            ['getConnection']
        );

        $treeQueryBuildMock
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($connection));

        return $treeQueryBuildMock;
    }
}
