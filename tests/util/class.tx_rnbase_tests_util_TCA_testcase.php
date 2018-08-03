<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * tx_rnbase_tests_util_TCA Tests.
 *
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 * @author Michael Wagner <michael.wagner@dmk-business.de>
 */
class tx_rnbase_tests_util_TCA_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // for the testValidateModel Test
        $GLOBALS['TCA']['pages']['columns']['storage_pid']['config']['minitems'] = '1';
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $GLOBALS['TCA']['pages']['columns']['storage_pid']['config']['minitems'] = '0';
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetTransOrigPointerFieldForTableWithUnknownTable()
    {
        $this->assertEmpty(
            tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('unknown'),
            'transOrigPointerField returned'
        );
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetTransOrigPointerFieldForTableWithUnconfiguredTable()
    {
        $this->assertEmpty(
            tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('be_users'),
            'transOrigPointerField returned'
        );
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetTransOrigPointerFieldForTableWithTtContentTable()
    {
        $this->assertEquals(
            'l18n_parent',
            tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('tt_content'),
            'wrong transOrigPointerField returned'
        );
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetLanguageFieldForTableWithUnknownTable()
    {
        $this->assertEmpty(
            tx_rnbase_util_TCA::getLanguageFieldForTable('unknown'),
            'languageField returned'
        );
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetLanguageFieldForTableWithUnconfiguredTable()
    {
        $this->assertEmpty(
            tx_rnbase_util_TCA::getLanguageFieldForTable('be_users'),
            'languageField returned'
        );
    }

    /**
     * @return void
     *
     * @group unit
     * @test
     */
    public function testGetLanguageFieldForTableWithTtContentTable()
    {
        $this->assertEquals(
            'sys_language_uid',
            tx_rnbase_util_TCA::getLanguageFieldForTable('tt_content'),
            'wrong languageField returned'
        );
    }

    /**
     * is testing th following methods:
     *     tx_rnbase_util_TCA::validateModel();
     *     tx_rnbase_util_TCA::validateRecord();
     *     tx_rnbase_model_base::validateProperties();.
     *
     * @return void
     *
     * @group unit
     * @test
     * @dataProvider getValidateModelData
     */
    public function testValidateModel(
        tx_rnbase_model_base $model,
        array $options,
        $valid
    ) {
        /* @var $options tx_rnbase_model_data */
        $options = tx_rnbase::makeInstance(
            'tx_rnbase_model_data',
            is_array($options) ? $options : []
        );

        // test the tx_rnbase_util_TCA::validateModel method
        $this->assertEquals(
            $valid,
            tx_rnbase_util_TCA::validateModel(
                $model,
                $options
            )
        );
        // test the tx_rnbase_util_TCA::validateRecord method
        $this->assertEquals(
            $valid,
            tx_rnbase_util_TCA::validateRecord(
                $model->getProperty(),
                $model->getTableName(),
                $options
            )
        );
        // test the tx_rnbase_model_base::validateProperties method
        $this->assertEquals(
            $valid,
            $model->validateProperties($options)
        );

        if ($valid) {
            $this->assertFalse(
                $options->hasLastInvalidField(),
                'last_invalid_field was set, why?'
            );
            $this->assertFalse(
                $options->hasLastInvalidValue(),
                'last_invalid_value was set, why?'
            );
        } else {
            $this->assertTrue(
                $options->hasLastInvalidField(),
                'last_invalid_field was not set, why?'
            );
            // we has to check the property key,
            // hasLastInvalidValue returns false, if the value is NULL
            $this->assertTrue(
                array_key_exists('last_invalid_value', $options->getProperty()),
                'last_invalid_value was not set, why?'
            );
        }
    }

    /**
     * Liefert die Daten für den testValidateModel testcase.
     *
     * @return array
     */
    public function getValidateModelData()
    {
        return [
            __LINE__ => [
                // title is requiren and we check only the fields are in the record => valid, title is not empty
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => 'test', 'storage_pid' => '1']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => true],
                true,
            ],
            __LINE__ => [
                // title is requiren but we check the whole tca definition > invalid missing fields in record
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => 'test', 'storage_pid' => '1']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => false],
                false,
            ],
            __LINE__ => [
                // title is requiren and we check only the fields are in the record => invalid, title is required
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => '', 'storage_pid' => '1']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => true],
                false,
            ],
            __LINE__ => [
                // storage_pid is required (minitems) and we check only the fields are in the record => invalid, storage_pid is required!
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => 'test', 'storage_pid' => '0']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => true],
                false,
            ],
            __LINE__ => [
                // of is'nt a field in tca, so we add tca_overrides to define as required!
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => 'oftest', 'storage_pid' => '1', 'of' => '']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => true, 'tca_overrides' => ['columns' => ['of' => ['config' => ['eval' => 'required']]]]],
                false,
            ],
            __LINE__ => [
                // of is'nt a field in tca, so we add tca_overrides to define as required!
                'record' => tx_rnbase::makeInstance(
                    'tx_rnbase_model_base',
                    ['title' => 'oftest', 'storage_pid' => '1', 'of' => 'done']
                )->setTableName('pages'),
                'options' => ['only_record_fields' => true, 'tca_overrides' => ['columns' => ['of' => ['config' => ['eval' => 'required']]]]],
                true,
            ],
        ];
    }
}
