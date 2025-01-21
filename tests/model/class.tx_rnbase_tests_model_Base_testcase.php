<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Testing\BaseTestCase;

class tx_rnbase_tests_model_Base_testcase extends BaseTestCase
{
    public function testMagiccall()
    {
        $model = new tx_rnbase_model_base(['uid' => 1, 'test_value' => 45]);
        $this->assertEquals(45, $model->getTestValue());
    }

    public function testGetUidWhenNoLocalisation()
    {
        $model = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 123]]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $this->assertEquals(123, $model->getUid(), 'uid field not used');
    }

    public function testGetUidWhenLocalisation()
    {
        $model = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 123, 'l18n_parent' => 456, 'sys_language_uid' => 789]]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $this->assertEquals(456, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForNonTca()
    {
        $model = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [
                [
                    'uid' => '57',
                    'field' => 'test',
                ],
            ]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tx_table_not_exists'));
        $this->assertEquals(57, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForNonTable()
    {
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            [
                'uid' => '57',
                'field' => 'test',
            ]
        );
        $this->assertEquals(57, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForTranslatedSingleRecord()
    {
        $model = $this->getMock(
            'tx_rnbase_model_base',
            ['getTableName'],
            [['uid' => 123, 'l18n_parent' => 0, 'sys_language_uid' => 789]]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        $this->assertEquals(123, $model->getUid(), 'uid field not used');
    }

    public function testGetSysLanguageUidWithoutTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base'
        );
        $this->assertSame(0, $model->getSysLanguageUid());
    }

    public function testGetSysLanguageUidWithLanguageFieldInTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            [
                'uid' => 57,
                'header' => 'Home',
                'sys_language_uid' => '5',
            ]
        )->setTableName('tt_content');
        $this->assertSame(5, $model->getSysLanguageUid());
    }

    public function testGetTcaLabelWithoutTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base'
        );
        $this->assertSame('', $model->getTcaLabel());
    }

    public function testGetTcaLabelWithTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            [
                'uid' => 57,
                'header' => 'Home',
            ]
        )->setTableName('tt_content');
        $this->assertSame('Home', $model->getTcaLabel());
    }

    public function testGetCreationDateTimeWithoutTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base'
        );
        $this->assertNull($model->getCreationDateTime());
    }

    public function testGetCreationDateTimeWithTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            [
                'uid' => 57,
                'crdate' => 1433161484,
            ]
        )->setTableName('tt_content');
        $this->assertInstanceOf('DateTime', $model->getCreationDateTime());
        $this->assertSame('1433161484', $model->getCreationDateTime()->format('U'));
    }

    public function testGetLastModifyDateTimeWithoutTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base'
        );
        $this->assertNull($model->getLastModifyDateTime());
    }

    public function testGetLastModifyDateTimeWithTca()
    {
        /* @var $model tx_rnbase_model_base */
        $model = tx_rnbase::makeInstance(
            'tx_rnbase_model_base',
            [
                'uid' => 57,
                'tstamp' => 1433161484,
            ]
        )->setTableName('tt_content');
        $this->assertInstanceOf('DateTime', $model->getLastModifyDateTime());
        $this->assertSame('1433161484', $model->getLastModifyDateTime()->format('U'));
    }
}
