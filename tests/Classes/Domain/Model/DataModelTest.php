<?php

namespace Sys25\RnBase\Domain\Model;

use Exception;
use Sys25\RnBase\Testing\BaseTestCase;
use Tx_Rnbase_Domain_Model_Data;

/***************************************************************
 * Copyright notice
 *
 * (c) 2007-2023 Rene Nitzsche (rene@system25.de)
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

/**
 * Data model unit tests.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DataModelTest extends BaseTestCase
{
    /**
     * Test the getProperties method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetPropertiesCallsProperty()
    {
        $model = $this->getModel(
            null,
            DataModel::class,
            ['getProperty']
        );

        $model
            ->expects(self::once())
            ->method('getProperty')
            ->with(self::equalTo(null))
            ->will(self::returnValue(['uid' => 1]));

        $data = $model->getProperties();

        self::assertTrue(is_array($data));
        self::assertCount(1, $data);
        self::assertEquals(1, $data['uid']);
    }

    /**
     * Test Magic calls.
     *
     * @group unit
     *
     * @test
     */
    public function testMagicCalls()
    {
        $model = $this->getModelInstance();
        $this->assertEquals(50, $model->getUid());

        $this->assertTrue($model->hasFirstName());
        $this->assertEquals('John', $model->getFirstName());
        $this->assertInstanceOf(DataModel::class, $model->setFirstName('Max'));
        $this->assertEquals('Max', $model->getFirstName());

        $this->assertTrue($model->hasLastName());
        $this->assertEquals('Doe', $model->getLastName());
        $this->assertInstanceOf(DataModel::class, $model->unsLastName());
        $this->assertFalse($model->hasLastName());
        $this->assertNull($model->getLastName());

        $this->assertFalse($model->hasGender());
        $this->assertInstanceOf(DataModel::class, $model->setGender('male'));
        $this->assertTrue($model->hasGender());
        $this->assertEquals('male', $model->getGender());
        $this->assertInstanceOf(DataModel::class, $model->unsGender());
        $this->assertFalse($model->hasGender());
        $this->assertNull($model->getGender());
    }

    /**
     * Test record overloding for getters.
     *
     * @group unit
     *
     * @test
     */
    public function testRecordOverloadingGet()
    {
        $model = $this->getModelInstance();
        $this->assertSame(50, $model->uid);
        $this->assertSame('John', $model->first_name);
        $this->assertSame('testProperty', $model->property);
        $this->assertNull($model->column_does_not_exist);
    }

    /**
     * Test if magic calls throw exception on unknown method.
     *
     * @group unit
     *
     * @test
     */
    public function testMagicCallThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1406625817);
        $this->getModelInstance()->methodDoesNotExist();
    }

    /**
     * Test getInstance with recursive data.
     *
     * @group unit
     *
     * @test
     */
    public function testRecursiveInstance()
    {
        $data = [
            'gender' => 'm',
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
                'test' => [],
            ],
        ];
        $model = DataModel::getInstance($data);

        $this->assertSame('m', $model->getGender());
        $this->assertInstanceOf(DataModel::class, $model->getName());
        $this->assertSame('John', $model->getName()->getFirst());
        $this->assertSame('Doe', $model->getName()->getLast());
        $this->assertInstanceOf(DataModel::class, $model->getName()->getTest());
    }

    /**
     * Test dirty state.
     *
     * @group unit
     *
     * @test
     */
    public function testIsDirtyOnGet()
    {
        $model = $this->getModelInstance();
        $model->getFirstName('Jonny');
        $this->assertFalse($model->isDirty());
    }

    /**
     * Test dirty state.
     *
     * @group unit
     *
     * @test
     */
    public function testIsDirtyOnSet()
    {
        $model = $this->getModelInstance();
        $model->setFirstName('Johnny');
        // after set, the model has to be dirty
        $this->assertTrue($model->isDirty());
        $this->callInaccessibleMethod($model, 'resetCleanState');
        // after setting the clear state, the model should be clean
        $this->assertFalse($model->isDirty());
        // check with setProperty([])
        $model->setProperty(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]
        );
        $this->assertTrue($model->isDirty());
    }

    /**
     * Test dirty state.
     *
     * @group unit
     *
     * @test
     */
    public function testIsDirtyOnUns()
    {
        $model = $this->getModelInstance();
        // after unset an nonexisting value, the model has to be clean
        $model->unsSomeNotExistingColumn();
        $this->assertFalse($model->isDirty());
        // after set an existing value, the model has to be dirty
        $model->unsFirstName();
        $this->assertTrue($model->isDirty());
    }

    /**
     * Creates a test object.
     *
     * @return Tx_Rnbase_Domain_Model_Data
     */
    private function getModelInstance()
    {
        $data = [
            'uid' => 50,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'property' => 'testProperty',
        ];

        return DataModel::getInstance($data);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testOffsetExists()
    {
        $model = $this->getModel(
            ['test_value' => 'dummy', 'property' => 'testProperty'],
            DataModel::class,
            []
        );

        self::assertTrue(isset($model['testValue']));
        self::assertTrue(isset($model['property']));
        self::assertTrue(isset($model['test_value']));
        self::assertTrue(isset($model['TestValue']));
        self::assertFalse(isset($model['wrongTest']));
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testOffsetGet()
    {
        $model = new DataModel(['test_value' => 'dummy', 'property' => 'testProperty']);

        self::assertSame('dummy', $model['testValue']);
        self::assertSame('dummy', $model['test_value']);
        self::assertSame('dummy', $model['TestValue']);
        self::assertSame('testProperty', $model['property']);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testOffsetSet()
    {
        $model = $this->getModel(
            ['test_value' => 'wrong', 'property' => 'testProperty'],
            DataModel::class,
            []
        );

        $model['testValue'] = 'dummy';
        self::assertSame('dummy', $model['testValue']);
        $model['test_value'] = 'dummy2';
        self::assertSame('dummy2', $model['test_value']);
        $model['TestValue'] = 'dummy3';
        self::assertSame('dummy3', $model['TestValue']);
        $model['property'] = 'testProperty1';
        self::assertSame('testProperty1', $model['property']);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testOffsetUnset()
    {
        $model = $this->getModel(['test_value' => 'wrong'], DataModel::class);
        self::assertTrue(isset($model['test_value']));
        unset($model['testValue']);
        self::assertFalse(isset($model['test_value']));

        $model = $this->getModel(['test_value' => 'wrong'], DataModel::class);
        self::assertTrue(isset($model['testValue']));
        unset($model['test_value']);
        self::assertFalse(isset($model['testValue']));

        $model = $this->getModel(['test_value' => 'wrong'], DataModel::class);
        self::assertTrue(isset($model['test_value']));
        unset($model['TestValue']);
        self::assertFalse(isset($model['test_value']));
    }
}
