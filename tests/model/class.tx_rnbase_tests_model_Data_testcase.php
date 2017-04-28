<?php
/***************************************************************
*  Copyright notice
*
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_model_data');

/**
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_tests
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_model_Data_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     * test object with testdata
     *
     * @return tx_rnbase_model_data
     */
    private function getModelInstance()
    {
        $data = array(
            'uid' => 50,
            'first_name' => 'John',
            'last_name' => 'Doe',
        );

        return tx_rnbase_model_data::getInstance($data);
    }

    /**
     * @test
     */
    public function testMagicCalls()
    {
        $model = $this->getModelInstance();
        $this->assertEquals(50, $model->getUid());

        $this->assertTrue($model->hasFirstName());
        $this->assertEquals('John', $model->getFirstName());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->setFirstName('Max'));
        $this->assertEquals('Max', $model->getFirstName());

        $this->assertTrue($model->hasLastName());
        $this->assertEquals('Doe', $model->getLastName());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->unsLastName());
        $this->assertFalse($model->hasLastName());
        $this->assertNull($model->getLastName());

        $this->assertFalse($model->hasGender());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->setGender('male'));
        $this->assertTrue($model->hasGender());
        $this->assertEquals('male', $model->getGender());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->unsGender());
        $this->assertFalse($model->hasGender());
        $this->assertNull($model->getGender());
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionCode 1406625817
     */
    public function testMagicCallThrowsException()
    {
        $this->getModelInstance()->methodDoesNotExist();
    }

    /**
     * @test
     */
    public function testRecursiveInstance()
    {
        $data = array(
            'gender' => 'm',
            'name' => array(
                'first' => 'John',
                'last' => 'Doe',
                'test' => array(),
            )
        );
        $model = tx_rnbase_model_data::getInstance($data);

        $this->assertSame('m', $model->getGender());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->getName());
        $this->assertSame('John', $model->getName()->getFirst());
        $this->assertSame('Doe', $model->getName()->getLast());
        $this->assertInstanceOf('tx_rnbase_model_data', $model->getName()->getTest());
    }

    /**
     * @test
     */
    public function testIsDirtyOnGet()
    {
        $model = $this->getModelInstance();
        $model->getFirstName('Jonny');
        $this->assertFalse($model->isDirty());
    }

    /**
     * @test
     */
    public function testIsDirtyOnSet()
    {
        $model = $this->getModelInstance();
        $model->setFirstName('Jonny');
        // after set, the model has to be dirty
        $this->assertTrue($model->isDirty());
        $this->callInaccessibleMethod($model, 'resetCleanState');
        // after setting the clear state, the model should be clean
        $this->assertFalse($model->isDirty());
    }
    /**
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
     * @test
     */
    public function testIsDirtyRecordChanges()
    {
        $model = $this->getModelInstance();
        // after set a value without calling the property methods, the model has to be clean.
        $model->record['first_name'];
        $this->assertFalse($model->isDirty());
    }
    /**
     * @test
     */
    public function testRecordDirectAccessForBackwardsCompatibility()
    {
        $model = $this->getModelInstance();
        // check data without manipulation
        $this->assertSame('John', $model->record['first_name']);
        // check data after property change
        $model->setFirstName('Jonny');
        $this->assertSame('Jonny', $model->record['first_name']);
        // check backwards compatibility for direct record access
        $model->record['first_name'] = 'Jonas';
        $this->assertSame('Jonas', $model->record['first_name']);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Data_testcase.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Data_testcase.php']);
}
