<?php

namespace Sys25\RnBase\Domain\Repository;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2024 René Nitzsche <rene@system25.de>
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

use Closure;
use stdClass;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Search\SearchGeneric;
use Sys25\RnBase\Testing\BaseTestCase;

/**
 * Test for persistence repository.
 *
 * @author Michael Wagner
 */
class PersistenceRepositoryTest extends BaseTestCase
{
    private $backup = [];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->backup['TCA'] = $GLOBALS['TCA'];
        if (empty($GLOBALS['EXEC_TIME'])) {
            $GLOBALS['EXEC_TIME'] = time();
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $GLOBALS['TCA'] = $this->backup['TCA'];
    }

    /**
     * Test the IsModelWrapperClass method.
     *
     * @group unit
     *
     * @test
     */
    public function testIsModelWrapperClassWithRightClass()
    {
        self::assertTrue(
            $this->callInaccessibleMethod(
                $this->getRepository(),
                'isModelWrapperClass',
                $this->getModel(null, BaseModel::class)
            )
        );
    }

    /**
     * Test the IsModelWrapperClass method.
     *
     * @group unit
     *
     * @test
     */
    public function testIsModelWrapperClassWithWrongClass()
    {
        self::assertFalse(
            $this->callInaccessibleMethod(
                $this->getRepository(),
                'isModelWrapperClass',
                new stdClass()
            )
        );
    }

    /**
     * Test the persist method.
     *
     * @group unit
     *
     * @test
     */
    public function testPersistNewModel()
    {
        // simulate tca
        $GLOBALS['TCA']['tt_content'] = [
            'ctrl' => [
                'crdate' => 'crdate',
                'tstamp' => 'tstamp',
            ],
            'columns' => [
                'pid' => [],
                'header' => [],
            ],
        ];

        $repo = $this->getRepository();
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $repo->getEmptyModel()->setTableName('tt_content');
        $model = $this->getModel(
            ['pid' => 5, 'header' => 'New element', 'unknown_column' => 'temp'],
            BaseModel::class
        )->setTableName('tt_content');

        // no update for new odels
        $connection
            ->expects(self::never())
            ->method('doUpdate');
        // an insert for new models
        $connection
            ->expects(self::once())
            ->method('doInsert')
            ->with(
                $this->equalTo('tt_content'),
                $this->callback(
                    function ($data) {
                        self::assertTrue(is_array($data));

                        self::assertArrayNotHasKey('unknown_column', $data);

                        self::assertArrayHasKey('pid', $data);
                        self::assertSame(5, $data['pid']);

                        self::assertArrayHasKey('header', $data);
                        self::assertSame('New element', $data['header']);

                        self::assertArrayHasKey('tstamp', $data);
                        self::assertSame($GLOBALS['EXEC_TIME'], $data['tstamp']);

                        self::assertArrayHasKey('crdate', $data);
                        self::assertSame($GLOBALS['EXEC_TIME'], $data['crdate']);

                        return true;
                    }
                )
            );

        // a new model requires no dirty state
        self::assertFalse($model->isDirty());

        $repo->persist($model, []);

        // check dirty state after persist
        self::assertFalse($model->isDirty());

        // non tca columns should still exist
        self::assertTrue($model->hasProperty('unknown_column'));
        self::assertSame('temp', $model->getProperty('unknown_column'));
    }

    /**
     * Test the persist method.
     *
     * @group unit
     *
     * @test
     */
    public function testPersistExistingModel()
    {
        // simulate tca
        $GLOBALS['TCA']['tt_content'] = [
            'ctrl' => [
                'crdate' => 'crdate',
                'tstamp' => 'tstamp',
            ],
            'columns' => [
                'header' => [],
            ],
        ];

        $repo = $this->getRepository();
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $repo->getEmptyModel()->setTableName('tt_content');
        $model = $this->getModel(
            ['uid' => 7, 'pid' => 5, 'header' => 'New element', 'unknown_column' => 'temp'],
            BaseModel::class
        )->setTableName('tt_content');

        // set a value, so the dirty flag was set
        $model->setProperty('header', 'Existing element');

        // no insert on already stored models
        $connection
            ->expects(self::never())
            ->method('doInsert');
        // there should be changes on dirty state
        $connection
            ->expects(self::once())
            ->method('doUpdate')
            ->with(
                $this->equalTo('tt_content'),
                $this->callback(
                    function ($where) {
                        self::assertInstanceOf(Closure::class, $where);

                        return true;
                    }
                ),
                $this->callback(
                    function ($data) {
                        self::assertTrue(is_array($data));

                        self::assertArrayNotHasKey('uid', $data);
                        // no pid change on update
                        self::assertArrayNotHasKey('pid', $data);
                        // no crdate change on update
                        self::assertArrayNotHasKey('crdate', $data);
                        self::assertArrayNotHasKey('unknown_column', $data);

                        self::assertArrayHasKey('header', $data);
                        self::assertSame('Existing element', $data['header']);

                        self::assertArrayHasKey('tstamp', $data);
                        self::assertSame($GLOBALS['EXEC_TIME'], $data['tstamp']);

                        return true;
                    }
                )
            );

        // a updated model requires a dirty state
        self::assertTrue($model->isDirty());

        $repo->persist($model);

        // check dirty state after persist
        self::assertFalse($model->isDirty());

        // non tca columns should still exist
        self::assertTrue($model->hasProperty('unknown_column'));
        self::assertSame('temp', $model->getProperty('unknown_column'));
    }

    /**
     * Test the persist method.
     *
     * @group unit
     *
     * @test
     */
    public function testPersistExistingNonDirtyModel()
    {
        $repo = $this->getRepository();
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $repo->getEmptyModel()->setTableName('tt_content');
        $record = ['uid' => 7, 'pid' => 5, 'header' => 'New element', 'unknown_column' => 'temp'];
        $model = $this->getModel(
            $record,
            BaseModel::class
        )->setTableName('tt_content');

        // no insert on already stored models
        $connection
            ->expects(self::never())
            ->method('doInsert');
        // no update on non dirty models
        $connection
            ->expects(self::never())
            ->method('doUpdate');

        // no updates on model should be done
        self::assertFalse($model->isDirty());

        $repo->persist($model);

        // check dirty state after persist
        self::assertFalse($model->isDirty());

        // the properties should be the same
        self::assertSame($record, $model->getProperty());
    }

    /**
     * The mocked repo to test.
     *
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|PersistenceRepository
     */
    protected function getRepository(
        array $methods = []
    ) {
        $connection = $this->getMock(
            Connection::class,
            get_class_methods(Connection::class)
        );
        $model = $this->getModel(null, BaseModel::class);

        $repo = $this->getMockForAbstractClass(
            PersistenceRepository::class,
            [],
            '',
            true,
            true,
            true,
            array_merge(
                ['getConnection', 'getSearchClass', 'getWrapperClass', 'getEmptyModel'],
                $methods
            )
        );

        $repo
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($connection));
        $repo
            ->expects(self::any())
            ->method('getSearchClass')
            ->will(self::returnValue(SearchGeneric::class));
        $repo
            ->expects(self::any())
            ->method('getEmptyModel')
            ->will(self::returnValue($model));
        $repo
            ->expects(self::any())
            ->method('getWrapperClass')
            ->will(self::returnValue(BaseModel::class));

        return $repo;
    }
}
