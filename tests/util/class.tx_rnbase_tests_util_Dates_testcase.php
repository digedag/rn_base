<?php

use Sys25\RnBase\Testing\BaseTestCase;

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

class tx_rnbase_tests_util_Dates_testcase extends BaseTestCase
{
    public function testDatetimeGetTimeStamp()
    {
        $tstamp = tx_rnbase_util_Dates::getTimeStamp(1970, 1, 1, 1, 0, 0, 'CET');
        $this->assertEquals(0, $tstamp);
        $tstamp = tx_rnbase_util_Dates::getTimeStamp(1970, 1, 1, 1, 0, 0, 'UTC');
        $this->assertEquals(3600, $tstamp);
    }

    public function testDatetimeMysql2tstamp()
    {
        $tstamp = tx_rnbase_util_Dates::datetime_mysql2tstamp('1970-01-1 01:00:00', 'CET');
        $this->assertEquals(0, $tstamp);
        $tstamp = tx_rnbase_util_Dates::datetime_mysql2tstamp('1970-01-1 00:00:00', 'UTC');
        $this->assertEquals(0, $tstamp);
    }

    public function testDateConv()
    {
        $zeit1 = '2009-02-11';
        $tstamp1 = tx_rnbase_util_Dates::date_mysql2tstamp($zeit1);
        $zeit2 = tx_rnbase_util_Dates::date_tstamp2mysql($tstamp1);

        //		$sDate = gmstrftime("%d.%m.%Y", $tstamp1);
        $this->assertEquals($zeit1, $zeit2);
    }

    public function testConvert4TCA2Timestamp()
    {
        $record = ['datetime' => '2011-10-20 12:00:00', 'date' => '2011-10-20', 'emptydate' => '0000-00-00'];
        tx_rnbase_util_Dates::convert4TCA2Timestamp($record, ['datetime', 'date', 'emptydate']);
        $this->assertEquals('1319112000', $record['datetime']);
        $this->assertEquals('1319068800', $record['date']);
        $this->assertEquals('0', $record['emptydate']);
    }

    public function testConvert4TCA2DateTime()
    {
        $record = ['datetime' => '1319112000'];
        tx_rnbase_util_Dates::convert4TCA2DateTime($record, ['datetime'], true);
        $this->assertEquals('2011-10-20 12:00:00', $record['datetime']);
    }

    public function testConvert4TCA2Date()
    {
        $record = ['date' => '1319068800'];
        tx_rnbase_util_Dates::convert4TCA2Date($record, ['date'], true);
        $this->assertEquals('2011-10-20', $record['date']);
    }

    /**
     * @param string $mysqlDate
     * @param int    $expectedTimestamp
     *
     * @dataProvider dataProviderDateMysql2Tstamp
     */
    public function testDateMysql2tstamp($mysqlDate, $expectedTimestamp)
    {
        self::assertSame($expectedTimestamp, tx_rnbase_util_Dates::date_mysql2tstamp($mysqlDate));
    }

    /**
     * @return array
     */
    public function dataProviderDateMysql2Tstamp()
    {
        return [
            ['1985-08-14', 492818400],
            ['aa-bb-cccc', null],
            ['', null],
        ];
    }
}
