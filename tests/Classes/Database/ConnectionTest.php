<?php

namespace Sys25\RnBase\Database;

use PHPUnit_Framework_TestCase;
use stdClass;
use Sys25\RnBase\Tests\BaseTestCase;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2021 Rene Nitzsche (rene@system25.de)
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

/**
 * @author Hannes Bochmann
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ConnectionTest extends BaseTestCase
{
    /**
     * @var int
     */
    private $loadHiddenObjectsBackUp;

    private $beUserBackUp;

    private $systemLogConfigurationBackup;
    private $connection;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->connection = tx_rnbase::makeInstance(Connection::class);
    }

    /**
     * Initialices the TSFE an sets some TYPO3_CONF_VARS.
     */
    protected function prepareTsfeSetUp()
    {
        $this->loadHiddenObjectsBackUp = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 0;

        $this->beUserBackUp = $GLOBALS['BE_USER'];
        if (!is_object($GLOBALS['BE_USER'])) {
            $GLOBALS['BE_USER'] = new stdClass();
        }

        TYPO3::getTSFE()->no_cache = false;

        // logging verhindern
        $this->systemLogConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = '';
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = $this->loadHiddenObjectsBackUp;
        $GLOBALS['BE_USER'] = $this->beUserBackUp;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = $this->systemLogConfigurationBackup;
    }

    /**
     * Tests the getDatabase method.
     *
     * @group unit
     * @test
     */
    public function testGetDatabaseForTypo3()
    {
        $this->assertInstanceOf(
            'tx_rnbase_util_db_TYPO3',
            $this->callInaccessibleMethod(
                $this->getMock(Connection::class),
                'getDatabase',
                'typo3'
            )
        );
    }

    /**
     * Tests the getDatabase method.
     *
     * @group unit
     * @test
     */
    public function testGetDatabaseForTypo3Dbal()
    {
        $this->assertInstanceOf(
            'tx_rnbase_util_db_TYPO3DBAL',
            $this->callInaccessibleMethod(
                $this->getMock(Connection::class),
                'getDatabase',
                'typo3dbal'
            )
        );
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsBe()
    {
        $this->prepareTsfeSetUp();

        $options['sqlonly'] = 1;
        $options['enablefieldsbe'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        // TYPO3 <= 7 deleted=0
        // TYPO3 >= 8 `deleted` = 0
        $this->assertRegExp('/deleted(` )?=/', $sql, 'deleted is missing');

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group'];
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
        $this->prepareTsfeSetUp();

        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group', 'deleted'];
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' not found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testIsFrontend()
    {
        $this->prepareTsfeSetUp();

        self::assertFalse($this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Rnbase_Database_Connection'), 'isFrontend'));
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsFeLeavesEnableFieldsForFeIfLoadHiddenObjectAndBeUser()
    {
        $this->prepareTsfeSetUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $databaseConnection = $this->getMock(Connection::class, ['isFrontend']);
        $databaseConnection->expects(self::any())
            ->method('isFrontend')
            ->will(self::returnValue(true));
        $sql = $databaseConnection->doSelect('*', 'tt_content', $options);

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group', 'deleted'];
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' not found');
        }
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithLoadHiddenObjectDeactivatesCacheNotIfNotInFrontend()
    {
        $this->prepareTsfeSetUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        $this->assertRegExp('/deleted(` )?=/', $sql, 'deleted is missing');

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group'];
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }

        self::assertFalse(TYPO3::getTSFE()->no_cache, 'Cache doch deaktiviert');
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsFeSetsEnableFieldsForFeIfLoadHiddenObjectButNoBeUser()
    {
        $this->prepareTsfeSetUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $GLOBALS['BE_USER'] = null;
        $options['sqlonly'] = 1;
        $options['enablefieldsfe'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group', 'deleted'];
        foreach ($fields as $field) {
            $this->assertRegExp('/'.$field.'/', $sql, $field.' not found');
        }

        self::assertFalse(TYPO3::getTSFE()->no_cache, 'Cache nicht aktiviert');
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testDoSelectWithEnableFieldsOffSetsEnableFieldsForBeNotIfLoadHiddenObjectAndBeUser()
    {
        $this->prepareTsfeSetUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] = 1;
        $options['sqlonly'] = 1;
        $options['enablefieldsoff'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group', 'deleted'];
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
        $this->prepareTsfeSetUp();

        $options['sqlonly'] = 1;
        $options['enablefieldsoff'] = 1;
        $sql = $this->connection->doSelect('*', 'tt_content', $options);

        $fields = ['hidden', 'starttime', 'endtime', 'fe_group', 'deleted'];
        foreach ($fields as $field) {
            $this->assertNotRegExp('/'.$field.'(` )?(=|<=)/', $sql, $field.' found');
        }
    }

    /**
     * @dataProvider singleFieldWhereProvider
     */
    public function testSetSingleWhereFieldWithOneTable($operator, $value, $expected)
    {
        $ret = $this->connection->setSingleWhereField('Table1', $operator, 'Col1', $value);
        $this->assertEquals($expected, $ret);
    }

    public function singleFieldWhereProvider()
    {
        return [
            [OP_LIKE, 'm', ' '], // warum müssen mindestens 3 buchstaben vorliegen?
            [OP_LIKE, 'm & m', ' '], // warum wird alles verschluckt? ist das richtig?
            [OP_LIKE, 'my m', " (Table1.col1 LIKE '%my%') "],
            [OP_LIKE, 'my', " (Table1.col1 LIKE '%my%') "],
            [OP_LIKE, 'myValue', " (Table1.col1 LIKE '%myValue%') "],
            [OP_LIKE, 'myValue test', " (Table1.col1 LIKE '%myValue%') AND  (Table1.col1 LIKE '%test%') "],
            [OP_LIKE_CONST, 'myValue test', " (Table1.col1 LIKE '%myValue test%') "],
            [OP_INSET_INT, '23', " (FIND_IN_SET('23', Table1.col1)) "],
            [OP_INSET_INT, '23,38', " (FIND_IN_SET('23', Table1.col1) OR FIND_IN_SET('38', Table1.col1)) "],
        ];
    }

    /**
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testSearchWhere()
    {
        $this->prepareTsfeSetUp();

        $sw = 'content management, system';
        $fields = 'tab1.bodytext,tab1.header';

        $ret = $this->connection->searchWhere('23', 'tab1.single', 'FIND_IN_SET_OR');
        $this->assertEquals(" (FIND_IN_SET('23', tab1.single))", $ret, 'FIND_IN_SET failed.');

        $ret = $this->connection->searchWhere('23', 't1.club,t2.club', OP_IN_INT);
        $this->assertEquals(' (t1.club IN (23) OR t2.club IN (23) )', $ret, 'FIND_IN_SET failed.');

        $ret = $this->connection->searchWhere($sw, $fields, OP_EQ);
        $this->assertEquals($ret, " (tab1.bodytext = 'content' OR tab1.header = 'content' OR tab1.bodytext = 'management' OR tab1.header = 'management' OR tab1.bodytext = 'system' OR tab1.header = 'system' )", 'OR failed.');

        $ret = $this->connection->searchWhere($sw.', 32', $fields, 'FIND_IN_SET_OR');
        $this->assertEquals($ret, " (FIND_IN_SET('content', tab1.bodytext) OR FIND_IN_SET('content', tab1.header) OR FIND_IN_SET('management', tab1.bodytext) OR FIND_IN_SET('management', tab1.header) OR FIND_IN_SET('system', tab1.bodytext) OR FIND_IN_SET('system', tab1.header) OR FIND_IN_SET('32', tab1.bodytext) OR FIND_IN_SET('32', tab1.header))", 'FIND_IN_SET failed');

        $ret = $this->connection->searchWhere($sw, $fields, 'LIKE');
        $this->assertEquals($ret, " (tab1.bodytext LIKE '%content%' OR tab1.header LIKE '%content%') AND  (tab1.bodytext LIKE '%management%' OR tab1.header LIKE '%management%') AND  (tab1.bodytext LIKE '%system%' OR tab1.header LIKE '%system%')", 'LIKE failed.');

        $sw = 'content\'; INSERT';
        $fields = 'tab1.bodytext,tab1.header';
        $ret = $this->connection->searchWhere($sw, $fields, OP_EQ);
        $this->assertEquals($ret, " (tab1.bodytext = 'content\';' OR tab1.header = 'content\';' OR tab1.bodytext = 'INSERT' OR tab1.header = 'INSERT' )", 'OR failed.');

        $sw = 0;
        $ret = $this->connection->searchWhere($sw, $fields, OP_EQ_INT);
        $this->assertEquals($ret, ' (tab1.bodytext = 0 OR tab1.header = 0 )', 'OR failed.');
    }

    /**
     * Tests the lookupLanguage method.
     *
     * @group functional
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testLookupLanguageGivesCorrectOverlayForPages()
    {
        $this->prepareTsfeSetUp();

        $row = ['uid' => 123, 'title' => 'test DE'];
        $tableName = 'pages';
        $options = ['forcei18n' => true];
        $connectionMock = $this->getMock('Tx_Rnbase_Database_Connection', ['getDatabase']);

        $reflectionObject = new \ReflectionObject($connectionMock);
        $reflectionMethod = $reflectionObject->getMethod('lookupLanguage');
        $reflectionMethod->setAccessible(true);

        //TODO: mock page repo and set sys lang

        $reflectionMethod->invokeArgs($connectionMock, [&$row, $tableName, $options]);

        $this->assertEquals(
            'test EN',
            $row['title']
        );
    }

    /**
     * @deprecated use Strings::debugString
     */
    public static function debugString($str)
    {
        return Strings::debugString($str);
    }
}
