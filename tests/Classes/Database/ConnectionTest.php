<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('Tx_Rnbase_Database_Connection');
tx_rnbase::load('tx_rnbase_util_SearchBase');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Tx_Rnbase_Database_ConnectionTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Database_ConnectionTest extends tx_rnbase_tests_BaseTestCase
{

    /**
     * @var int
     */
    private $loadHiddenObjectsBackUp;

    private $beUserBackUp;

    private $systemLogConfigurationBackup;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->loadHiddenObjectsBackUp = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 0;

        $this->beUserBackUp = $GLOBALS['BE_USER'];

        tx_rnbase_util_TYPO3::getTSFE()->no_cache = false;
        // logging verhindern
        $this->systemLogConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = '';
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = $this->loadHiddenObjectsBackUp;
        $GLOBALS['BE_USER'] = $this->beUserBackUp;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = $this->systemLogConfigurationBackup;
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsBe()
    {
        $options['sqlonly'] = 1;
        $options['enablefieldsbe'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        // TYPO3 <= 7 deleted=0
        // TYPO3 >= 8 `deleted` = 0
        $this->assertRegExp('/deleted(` )?=/', $sql, 'deleted is missing');

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group');
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsFe()
    {
        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group', 'deleted');
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field . ' not found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testIsFrontend()
    {
        self::assertFalse($this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection'), 'isFrontend'));
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsFeLeavesEnableFieldsForFeIfLoadHiddenObjectAndBeUser()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $databaseConnection = $this->getMock('Tx_Rnbase_Database_Connection', array('isFrontend'));
        $databaseConnection ->expects(self::any())
            ->method('isFrontend')
            ->will(self::returnValue(true));
        $sql = $databaseConnection->doSelect('*', 'tt_content', $options);

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group', 'deleted');
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field . ' not found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithLoadHiddenObjectDeactivatesCacheNotIfNotInFrontend()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        $this->assertRegExp('/deleted(` )?=/', $sql, 'deleted is missing');

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group');
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }

        self::assertFalse(tx_rnbase_util_TYPO3::getTSFE()->no_cache, 'Cache doch deaktiviert');
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsFeSetsEnableFieldsForFeIfLoadHiddenObjectButNoBeUser()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $GLOBALS['BE_USER'] = null;
        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group', 'deleted');
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'/', $sql, $field.' not found');
        }

        self::assertFalse(tx_rnbase_util_TYPO3::getTSFE()->no_cache, 'Cache nicht aktiviert');
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsOffSetsEnableFieldsForBeNotIfLoadHiddenObjectAndBeUser()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $options['enablefieldsoff'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group', 'deleted');
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsOff()
    {
        $options['sqlonly'] = 1;
        $options['enablefieldsoff'] = 1;
        $sql = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->doSelect('*', 'tt_content', $options);

        $fields = array('hidden', 'starttime', 'endtime', 'fe_group', 'deleted');
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }
    }

    /**
     * @dataProvider singleFieldWhereProvider
     */
    public function test_setSingleWhereFieldWithOneTable($operator, $value, $expected)
    {
        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->setSingleWhereField('Table1', $operator, 'Col1', $value);
        $this->assertEquals($expected, $ret);
    }

    public function singleFieldWhereProvider()
    {
        return array(
            array(OP_LIKE, 'm', ' '), // warum müssen mindestens 3 buchstaben vorliegen?
            array(OP_LIKE, 'm & m', ' '), // warum wird alles verschluckt? ist das richtig?
            array(OP_LIKE, 'my m', " (Table1.col1 LIKE '%my%') "),
            array(OP_LIKE, 'my', " (Table1.col1 LIKE '%my%') "),
            array(OP_LIKE, 'myValue', " (Table1.col1 LIKE '%myValue%') "),
            array(OP_LIKE, 'myValue test', " (Table1.col1 LIKE '%myValue%') AND  (Table1.col1 LIKE '%test%') "),
            array(OP_LIKE_CONST, 'myValue test', " (Table1.col1 LIKE '%myValue test%') "),
            array(OP_INSET_INT, '23', " (FIND_IN_SET('23', Table1.col1)) "),
            array(OP_INSET_INT, '23,38', " (FIND_IN_SET('23', Table1.col1) OR FIND_IN_SET('38', Table1.col1)) "),
        );
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function test_searchWhere()
    {
        $sw = 'content management, system';
        $fields = 'tab1.bodytext,tab1.header';

        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere('23', 'tab1.single', 'FIND_IN_SET_OR');
        $this->assertEquals(" (FIND_IN_SET('23', tab1.single))", $ret, 'FIND_IN_SET failed.');

        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere('23', 't1.club,t2.club', OP_IN_INT);
        $this->assertEquals(' (t1.club IN (23) OR t2.club IN (23) )', $ret, 'FIND_IN_SET failed.');


        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere($sw, $fields, OP_EQ);
        $this->assertEquals($ret, " (tab1.bodytext = 'content' OR tab1.header = 'content' OR tab1.bodytext = 'management' OR tab1.header = 'management' OR tab1.bodytext = 'system' OR tab1.header = 'system' )", 'OR failed.');

        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere($sw.', 32', $fields, 'FIND_IN_SET_OR');
        $this->assertEquals($ret, " (FIND_IN_SET('content', tab1.bodytext) OR FIND_IN_SET('content', tab1.header) OR FIND_IN_SET('management', tab1.bodytext) OR FIND_IN_SET('management', tab1.header) OR FIND_IN_SET('system', tab1.bodytext) OR FIND_IN_SET('system', tab1.header) OR FIND_IN_SET('32', tab1.bodytext) OR FIND_IN_SET('32', tab1.header))", 'FIND_IN_SET failed');

        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere($sw, $fields, 'LIKE');
        $this->assertEquals($ret, " (tab1.bodytext LIKE '%content%' OR tab1.header LIKE '%content%') AND  (tab1.bodytext LIKE '%management%' OR tab1.header LIKE '%management%') AND  (tab1.bodytext LIKE '%system%' OR tab1.header LIKE '%system%')", 'LIKE failed.');

        $sw = 'content\'; INSERT';
        $fields = 'tab1.bodytext,tab1.header';
        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere($sw, $fields, OP_EQ);
        $this->assertEquals($ret, " (tab1.bodytext = 'content\';' OR tab1.header = 'content\';' OR tab1.bodytext = 'INSERT' OR tab1.header = 'INSERT' )", 'OR failed.');

        $sw = 0;
        $ret = tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection')->searchWhere($sw, $fields, OP_EQ_INT);
        $this->assertEquals($ret, ' (tab1.bodytext = 0 OR tab1.header = 0 )', 'OR failed.');
    }

    /**
     *
     * @deprecated use tx_rnbase_util_Strings::debugString
     */
    public static function debugString($str)
    {
        tx_rnbase::load('tx_rnbase_util_Strings');

        return tx_rnbase_util_Strings::debugString($str);
    }
}
