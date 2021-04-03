<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2015-2016 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

tx_rnbase::load('Tx_Rnbase_Domain_Repository_AbstractRepository');

/**
 * Test for abstract repository.
 *
 * @author Michael Wagner
 */
class Tx_Rnbase_Domain_Repository_AbstractRepositoryTest extends tx_rnbase_tests_BaseTestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * @group unit
     * @dataProvider getOptions
     */
    public function testHandleEnableFieldsOptions(
        $options,
        $expectedOptions
    ) {
        $fields = [];
        $repository = $this->getRepositoryMock();

        $method = new ReflectionMethod(
            'Tx_Rnbase_Repository_AbstractRepository',
            'handleEnableFieldsOptions'
        );
        $method->setAccessible(true);

        $method->invokeArgs($repository, [&$fields, &$options]);

        self::assertEquals($expectedOptions, $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [['enablefieldsoff' => true], ['enablefieldsoff' => true]],
            [['enablefieldsbe' => true], ['enablefieldsbe' => true]],
            [['enablefieldsfe' => true], ['enablefieldsfe' => true]],
            [[], ['enablefieldsbe' => true]],
        ];
    }

    /**
     * @group unit
     */
    public function testGetSearcher()
    {
        $repository = $this->getRepositoryMock();

        self::assertInstanceOf(
            'tx_rnbase_util_SearchGeneric',
            $this->callInaccessibleMethod($repository, 'getSearcher')
        );
    }

    /**
     * @group unit
     */
    public function testFindByUidReturnsModelIfModelValid()
    {
        $repository = $this->getRepositoryMock();

        $expectedModel = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            ['uid' => 123]
        );

        self::assertEquals(
            $expectedModel,
            $repository->findByUid(['uid' => 123])
        );
    }

    /**
     * @group unit
     */
    public function testFindByUidReturnsNullIfModelInvalid()
    {
        $repository = $this->getRepositoryMock();

        self::assertNull(
            $repository->findByUid(0),
            'NULL nicht zurück gegeben'
        );
    }

    /**
     * @group unit
     */
    public function testGetWrapperClass()
    {
        $this->markTestIncomplete();
    }

    /**
     * @group unit
     */
    public function testSearchCallsSearcherCorrect()
    {
        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getWrapperClass', 'getSearcher', 'getCollectionClass']
        );

        $fields = ['someField' => 1];
        $options = [
            'collection' => 'TestCollection',
            'enablefieldsbe' => 1,
        ];

        $searcher = $this->getMock(
            'tx_rnbase_util_SearchGeneric',
            ['search']
        );

        $searcher
            ->expects(self::once())
            ->method('search')
            ->with($fields, $options)
            ->will(self::returnValue(['searched']));

        $repository
            ->expects(self::exactly(2))
            ->method('getCollectionClass')
            ->will(self::returnValue($options['collection']));
        unset($options['collection']);
        $repository
            ->expects(self::any())
            ->method('getSearcher')
            ->will(self::returnValue($searcher));

        self::assertEquals(
            ['searched'],
            $repository->search($fields, $options),
            'falsch gesucht'
        );
    }

    /**
     * @group integration
     * @TODO: refactor, requires tca for he lnparent field check!
     */
    public function testUniqueItemsReducesCorrect()
    {
        $this->markTestIncomplete('Fails, check why!');

        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $overlay = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $items = $this->callInaccessibleMethod($repository, 'uniqueItems', [$master, $overlay], ['distinct' => true]);

        self::assertCount(1, $items);
        self::assertArrayHasKey(0, $items);
        self::assertEquals($overlay, $items[0]);
    }

    /**
     * @group unit
     */
    public function testUniqueItemsDoesNotReduceCorrect()
    {
        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $overlay = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $items = $this->callInaccessibleMethod($repository, 'uniqueItems', [$master, $overlay], []);

        self::assertCount(2, $items);
        self::assertArrayHasKey(0, $items);
        self::assertEquals($master, $items[0]);
        self::assertArrayHasKey(1, $items);
        self::assertEquals($overlay, $items[1]);
    }

    /**
     * @group unit
     */
    public function testFindAll()
    {
        $repository = $this->getRepositoryMock(['search']);

        $repository
            ->expects($this->once())
            ->method('search')
            ->with([], [])
            ->will($this->returnValue(['searched']));

        self::assertEquals(
            ['searched'],
            $repository->findAll(),
            'falsch gesucht'
        );
    }

    /**
     * @param array $mockedMethods
     *
     * @return Tx_Rnbase_Repository_AbstractRepository
     */
    private function getRepositoryMock($mockedMethods = [])
    {
        $mockedMethods = array_unique(array_merge($mockedMethods, ['getSearchClass', 'getWrapperClass']));
        $repository = $this->getMockForAbstractClass(
            'Tx_Rnbase_Repository_AbstractRepository',
            [],
            '',
            false,
            false,
            false,
            $mockedMethods
        );

        $repository
            ->expects($this->any())
            ->method('getSearchClass')
            ->will($this->returnValue('tx_rnbase_util_SearchGeneric'));

        $repository
            ->expects($this->any())
            ->method('getWrapperClass')
            ->will($this->returnValue('tx_rnbase_model_base'));

        return $repository;
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfItemsFound()
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository
            ->expects($this->once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will($this->returnValue([0 => 'test']));

        self::assertEquals(
            'test',
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfNoItemsFound()
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository
            ->expects($this->once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will($this->returnValue([]));

        self::assertNull(
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }
}
