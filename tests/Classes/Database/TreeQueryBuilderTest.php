<?php

/**
 *  Copyright notice
 *
 *  (c) 2016 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * Class Tx_Rnbase_Database_TreeQueryBuilderTest
 *
 * @author Mario Seidel <mario.seidel@dmk-ebusiness.de>
 */
class Tx_Rnbase_Database_TreeQueryBuilderTest extends tx_rnbase_tests_BaseTestCase
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
     *       -- 7
     */
    public function testGetTreeRecursive()
    {
        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (1)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 2]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (2)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 3], ['uid' => 6]]));

                $connection->expects(self::at(2))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (3)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 4]]));

                $connection->expects(self::at(3))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (4)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([]));

                $connection->expects(self::at(4))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (6)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 7]]));

                $connection->expects(self::at(5))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (7)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([]));
            }
        );


        $uidList = $treeQueryBuildMock->getPageTreeUidList(1);

        $this->assertEquals([1, 2, 3, 4, 6, 7], $uidList);
    }

    public function testLimitedTreeByDepth()
    {
        $options = [
            'depth' => 2
        ];
        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (1)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 2]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (2)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 3], ['uid' => 6]]));
            }
        );


        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 2, 3, 6], $uidList);
    }

    public function testAddPidToCustomQueryCorrectly()
    {
        $options = [
            'where' => 'hidden=1'
        ];

        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => 'hidden=1 AND pid IN (1)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 2]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => 'hidden=1 AND pid IN (2)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 3]]));
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 2, 3], $uidList);
    }

    public function testSetCustomTableNameCorrectly()
    {
        $options = [
            'tableName' => 'tt_content'
        ];

        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'tt_content',
                        ['where' => '1=1 AND pid IN (1)', 'tableName' => 'tt_content']
                    )
                    ->will(self::returnValue([['uid' => 33], ['uid' => 44]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'tt_content',
                        ['where' => '1=1 AND pid IN (33)', 'tableName' => 'tt_content']
                    )
                    ->will(self::returnValue([]));

                $connection->expects(self::at(2))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'tt_content',
                        ['where' => '1=1 AND pid IN (44)', 'tableName' => 'tt_content']
                    )
                    ->will(self::returnValue([]));
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
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'tt_content',
                        [
                            'where' => '(starttime > 12345 AND endtime < 98765) AND pid IN (1)',
                            'tableName' => 'tt_content',
                            'orderby' => 'header',
                            'limit' => 1
                        ]
                    )
                    ->will(self::returnValue([['uid' => 2]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'tt_content',
                        [
                            'where' => '(starttime > 12345 AND endtime < 98765) AND pid IN (2)',
                            'tableName' => 'tt_content',
                            'orderby' => 'header',
                            'limit' => 1
                        ]
                    )
                    ->will(self::returnValue([]));
            }
        );

        $uidList = $treeQueryBuildMock->getTreeUidListRecursive(1, 2, 0, $options);

        $this->assertEquals([1, 2], $uidList);
    }

    public function testSetCustomParentField()
    {
        $options = [
            'parentField' => 'parent_id'
        ];

        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        [
                            'where' => '1=1 AND parent_id IN (1)',
                            'tableName' => 'pages',
                            'parentField' => 'parent_id'
                        ]
                    )
                    ->will(self::returnValue([['uid' => 5]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        [
                            'where' => '1=1 AND parent_id IN (5)',
                            'tableName' => 'pages',
                            'parentField' => 'parent_id'
                        ]
                    )
                    ->will(self::returnValue([]));
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 5], $uidList);
    }

    public function testSetCustomKeyField()
    {
        $options = [
            'idField' => 'entity_id'
        ];

        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'entity_id',
                        'pages',
                        [
                            'where' => '1=1 AND pid IN (1)',
                            'tableName' => 'pages',
                            'idField' => 'entity_id'
                        ]
                    )
                    ->will(self::returnValue([['entity_id' => 5]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'entity_id',
                        'pages',
                        [
                            'where' => '1=1 AND pid IN (5)',
                            'tableName' => 'pages',
                            'idField' => 'entity_id'
                        ]
                    )
                    ->will(self::returnValue([]));
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList(1, $options);

        $this->assertEquals([1, 5], $uidList);
    }

    public function testGetTreeWithCommaSeparatedPidList()
    {
        /**
 * @var Tx_Rnbase_Database_TreeQueryBuilder $treeQueryBuildMock
*/
        $treeQueryBuildMock = $this->getTreeQueryBuilderMock(
            function ($connection) {
                $connection->expects(self::at(0))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (1,2,3)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 5]]));

                $connection->expects(self::at(1))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (5)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([['uid' => 6], ['uid' => 7]]));

                $connection->expects(self::at(2))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (6)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([]));

                $connection->expects(self::at(3))
                    ->method('doSelect')
                    ->with(
                        'uid',
                        'pages',
                        ['where' => '1=1 AND pid IN (7)', 'tableName' => 'pages']
                    )
                    ->will(self::returnValue([]));
            }
        );

        $uidList = $treeQueryBuildMock->getPageTreeUidList('1,2,3');

        $this->assertEquals([1, 2, 3, 5, 6, 7], $uidList);
    }

    /**
     * get a mock with tree structure defined in the expectFunc
     *
     * @param \Closure $expectFunc
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTreeQueryBuilderMock($expectFunc)
    {
        tx_rnbase::load('Tx_Rnbase_Database_Connection');
        $connection = $this->getMock(
            'Tx_Rnbase_Database_Connection',
            get_class_methods('Tx_Rnbase_Database_Connection')
        );

        $expectFunc($connection);

        tx_rnbase::load('Tx_Rnbase_Database_TreeQueryBuilder');
        $treeQueryBuildMock = $this->getMock(
            'Tx_Rnbase_Database_TreeQueryBuilder',
            ['getConnection']
        );

        $treeQueryBuildMock
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($connection));

        return $treeQueryBuildMock;
    }
}
