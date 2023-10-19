<?php

namespace Sys25\RnBase\Database;

use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit_Framework_MockObject_MockObject;
use Sys25\RnBase\Testing\BaseTestCase;

/**
 *  Copyright notice.
 *
 *  (c) 2016-2021 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function (MockObject $connection) {
                $connection->expects(self::exactly(6))
                    ->method('doSelect')
                    ->withConsecutive(
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
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 2]],
                        [['uid' => 3], ['uid' => 6]],
                        [['uid' => 4]],
                        [],
                        [['uid' => 7]],
                        []
                    );
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1);

        $this->assertEquals([1, 2, 3, 4, 6, 7], $uidList);
    }

    public function testLimitedTreeByDepth()
    {
        $options = [
            'depth' => 2,
        ];
        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->withConsecutive(
                        [
                            'uid',
                            'pages',
                            ['where' => '1=1 AND pid IN (1)', 'tableName' => 'pages'],
                        ],
                        [
                            'uid',
                            'pages',
                            ['where' => '1=1 AND pid IN (2)', 'tableName' => 'pages'],
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 2]],
                        [['uid' => 3], ['uid' => 6]]
                    );
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

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::any())
                    ->method('doSelect')
                    ->withConsecutive(
                        [
                            'uid',
                            'pages',
                            ['where' => 'hidden=1 AND pid IN (1)', 'tableName' => 'pages'],
                        ],
                        [
                            'uid',
                            'pages',
                            ['where' => 'hidden=1 AND pid IN (2)', 'tableName' => 'pages'],
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 2]],
                        [['uid' => 3]]
                    );
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

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::exactly(3))
                    ->method('doSelect')
                    ->withConsecutive(
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
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 33], ['uid' => 44]],
                        [],
                        []
                    );
            }
        );

        $uidList = $treeQueryBuildMock->getTreeUidListRecursive(1, 2, 0, $options);

        $this->assertEquals([1, 33, 44], $uidList);
    }

    public function testSetQueryOptions()
    {
        $options = [
            'where' => '(starttime > 12345 AND endtime < 98765)',
            'tableName' => 'tt_content',
            'orderby' => 'header',
            'limit' => 1,
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->withConsecutive(
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
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 2]],
                        []
                    );
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

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->withConsecutive(
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
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 5]],
                        []
                    );
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 5], $uidList);
    }

    public function testSetCustomKeyField()
    {
        $options = [
            'idField' => 'entity_id',
        ];

        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::exactly(2))
                    ->method('doSelect')
                    ->withConsecutive(
                        [
                            'entity_id',
                            'pages',
                            [
                                'where' => '1=1 AND pid IN (1)',
                                'tableName' => 'pages',
                                'idField' => 'entity_id',
                            ],
                        ],
                        [
                            'entity_id',
                            'pages',
                            [
                                'where' => '1=1 AND pid IN (5)',
                                'tableName' => 'pages',
                                'idField' => 'entity_id',
                            ],
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['entity_id' => 5]],
                        []
                    );
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 5], $uidList);
    }

    public function testGetTreeWithCommaSeparatedPidList()
    {
        /**
         * @var TreeQueryBuilder
         */
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::any())
                    ->method('doSelect')
                    ->withConsecutive(
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
                        ]
                    )
                    ->willReturnOnConsecutiveCalls(
                        [['uid' => 5]],
                        [['uid' => 6], ['uid' => 7]],
                        [],
                        []
                    );
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
