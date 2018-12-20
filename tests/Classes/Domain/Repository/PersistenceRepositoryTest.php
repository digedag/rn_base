<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Test for persistence repository
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 */
class Tx_Rnbase_Domain_Repository_PersistenceRepositoryTest extends tx_rnbase_tests_BaseTestCase
{
    private $backup = [];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->backup['TCA'] = $GLOBALS['TCA'];
        if (empty($GLOBALS['EXEC_TIME'])) {
            $GLOBALS['EXEC_TIME'] = time();
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        $GLOBALS['TCA'] = $this->backup['TCA'];
    }

    /**
     * Test the IsModelWrapperClass method
     *
     * @return void
     *
     * @group unit
     * @test
     */
    public function testIsModelWrapperClassWithRightClass()
    {
        tx_rnbase::load('Tx_Rnbase_Domain_Model_Base');
        self::assertTrue(
            $this->callInaccessibleMethod(
                $this->getRepository(),
                'isModelWrapperClass',
                $this->getModel(null, 'Tx_Rnbase_Domain_Model_Base')
            )
        );
    }
    /**
     * Test the IsModelWrapperClass method
     *
     * @return void
     *
     * @group unit
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
     * Test the persist method
     *
     * @return void
     *
     * @group unit
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
            ]
        ];

        $repo = $this->getRepository();
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $repo->getEmptyModel()->setTableName('tt_content');
        $model = $this->getModel(
            ['pid' => 5, 'header' => 'New element', 'unknown_column' => 'temp'],
            'Tx_Rnbase_Domain_Model_Base'
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
     * Test the persist method
     *
     * @return void
     *
     * @group unit
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
            ]
        ];

        $repo = $this->getRepository();
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $repo->getEmptyModel()->setTableName('tt_content');
        $model = $this->getModel(
            ['uid' => 7, 'pid' => 5, 'header' => 'New element', 'unknown_column' => 'temp'],
            'Tx_Rnbase_Domain_Model_Base'
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
                $this->equalTo('uid=7'),
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
     * Test the persist method
     *
     * @return void
     *
     * @group unit
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
            'Tx_Rnbase_Domain_Model_Base'
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
     * The mocked repo to test
     *
     * @param array $methods
     *
     * @return PHPUnit_Framework_MockObject_MockObject|Tx_Rnbase_Domain_Repository_PersistenceRepository
     */
    protected function getRepository(
        array $methods = []
    ) {
        tx_rnbase::load('Tx_Rnbase_Database_Connection');
        $connection = $this->getMock(
            'Tx_Rnbase_Database_Connection',
            get_class_methods('Tx_Rnbase_Database_Connection')
        );
        tx_rnbase::load('Tx_Rnbase_Domain_Model_Base');
        $model = $this->getModel(null, 'Tx_Rnbase_Domain_Model_Base');

        tx_rnbase::load('Tx_Rnbase_Domain_Repository_PersistenceRepository');
        $repo = $this->getMockForAbstractClass(
            'Tx_Rnbase_Domain_Repository_PersistenceRepository',
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
            ->will(self::returnValue('tx_rnbase_util_SearchGeneric'));
        $repo
            ->expects(self::any())
            ->method('getEmptyModel')
            ->will(self::returnValue($model));
        $repo
            ->expects(self::any())
            ->method('getWrapperClass')
            ->will(self::returnValue('Tx_Rnbase_Domain_Model_Base'));

        return $repo;
    }
}
