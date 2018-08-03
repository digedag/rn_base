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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('Tx_Rnbase_Domain_Model_Base');

class Tx_Rnbase_Domain_Model_BaseTest extends tx_rnbase_tests_BaseTestCase
{
    public function test_magiccall()
    {
        $model = new Tx_Rnbase_Domain_Model_Base(['uid' => 1, 'test_value' => 45]);
        self::assertEquals(45, $model->getTestValue());
    }

    public function testSetPropertyArray()
    {
        $model = $this->getModel(
            [
                'first_name' => 'Max',
            ],
            'Tx_Rnbase_Domain_Model_Base'
        );

        // set a new record
        $model->setProperty(
            [
                'uid'        => 7,
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );

        self::assertTrue($model->isDirty());
        self::assertEquals(7, $model->getUid());
        self::assertEquals(7, $model->getProperty('uid'));
        self::assertEquals('John', $model->getFirstName());
        self::assertEquals('Doe', $model->getLastName());

        // // check uid overriding
        $model->setProperty(
            [
                'uid' => 5,
            ]
        );

        self::assertEquals(7, $model->getUid());
        self::assertEquals(7, $model->getProperty('uid'));
    }

    public function testGetUidWhenNoLocalisation()
    {
        $model = $this->getMock(
            'Tx_Rnbase_Domain_Model_Base',
            ['getTableName'],
            [['uid' => '123']]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        self::assertSame(123, $model->getUid(), 'uid field not used');
    }

    public function testGetUidWhenLocalisation()
    {
        $model = $this->getMock(
            'Tx_Rnbase_Domain_Model_Base',
            ['getTableName'],
            [['uid' => '123', 'l18n_parent' => '456', 'sys_language_uid' => '789']]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        self::assertSame(456, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForNonTca()
    {
        $model = $this->getMock(
            'Tx_Rnbase_Domain_Model_Base',
            ['getTableName'],
            [
                [
                    'uid'   => '57',
                    'field' => 'test',
                ],
            ]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tx_table_not_exists'));
        self::assertSame(57, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForNonTable()
    {
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'uid'   => '57',
                'field' => 'test',
            ]
        );
        self::assertSame(57, $model->getUid(), 'uid field not used');
    }

    public function testGetUidForTranslatedSingleRecord()
    {
        $model = $this->getMock(
            'Tx_Rnbase_Domain_Model_Base',
            ['getTableName'],
            [['uid' => '123', 'l18n_parent' => '0', 'sys_language_uid' => '789']]
        );
        $model->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tt_content'));

        self::assertSame(123, $model->getUid(), 'uid field not used');
    }

    public function testGetSysLanguageUidWithoutTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base'
        );
        self::assertSame(0, $model->getSysLanguageUid());
    }

    public function testGetSysLanguageUidWithLanguageFieldInTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'uid'              => '57',
                'header'           => 'Home',
                'sys_language_uid' => '5',
            ]
        )->setTableName('tt_content');
        self::assertSame(5, $model->getSysLanguageUid());
    }

    public function testGetTcaLabelWithoutTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base'
        );
        self::assertSame('', $model->getTcaLabel());
    }

    public function testGetTcaLabelWithTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'uid'    => 57,
                'header' => 'Home',
            ]
        )->setTableName('tt_content');
        self::assertSame('Home', $model->getTcaLabel());
    }

    public function testGetCreationDateTimeWithoutTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base'
        );
        self::assertSame(null, $model->getCreationDateTime());
    }

    public function testGetCreationDateTimeWithTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'uid'    => 57,
                'crdate' => 1433161484,
            ]
        )->setTableName('tt_content');
        self::assertInstanceOf('DateTime', $model->getCreationDateTime());
        self::assertSame('1433161484', $model->getCreationDateTime()->format('U'));
    }

    public function testGetLastModifyDateTimeWithoutTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base'
        );
        self::assertSame(null, $model->getLastModifyDateTime());
    }

    public function testGetLastModifyDateTimeWithTca()
    {
        /* @var $model Tx_Rnbase_Domain_Model_Base */
        $model = tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'uid'    => 57,
                'tstamp' => 1433161484,
            ]
        )->setTableName('tt_content');
        self::assertInstanceOf('DateTime', $model->getLastModifyDateTime());
        self::assertSame('1433161484', $model->getLastModifyDateTime()->format('U'));
    }

    public function testIsValidShouldBeFalseForOnlyUid()
    {
        $model = $this->getModel(['uid' => 57], 'Tx_Rnbase_Domain_Model_Base');
        $this->assertFalse($model->isValid());
    }

    public function testIsValidShouldBeTrueForOnlyTitle()
    {
        $model = $this->getModel(['title' => 'foo'], 'Tx_Rnbase_Domain_Model_Base');
        $this->assertTrue($model->isValid());
    }

    public function testIsValidShouldBeTrueForUidAndTitle()
    {
        $model = $this->getModel(['uid' => 57, 'title' => 'foo'], 'Tx_Rnbase_Domain_Model_Base');
        $this->assertTrue($model->isValid());
    }
}
