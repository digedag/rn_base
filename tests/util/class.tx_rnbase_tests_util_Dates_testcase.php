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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Dates');

class tx_rnbase_tests_util_Dates_testcase extends tx_phpunit_testcase {

	public function test_datetime_getTimeStamp() {
		$tstamp = tx_rnbase_util_Dates::getTimeStamp(1970, 1, 1, 1, 0, 0, 'CET');
		$this->assertEquals(0, $tstamp);
		$tstamp = tx_rnbase_util_Dates::getTimeStamp(1970, 1, 1, 1, 0, 0, 'UTC');
		$this->assertEquals(3600, $tstamp);
	}
	
	public function test_datetime_mysql2tstamp() {
		$tstamp = tx_rnbase_util_Dates::datetime_mysql2tstamp('1970-01-1 01:00:00', 'CET');
		$this->assertEquals(0, $tstamp);
		$tstamp = tx_rnbase_util_Dates::datetime_mysql2tstamp('1970-01-1 00:00:00', 'UTC');
		$this->assertEquals(0, $tstamp);
	}
	
	public function test_dateConv() {
		$zeit1 = '2009-02-11';
		$tstamp1 = tx_rnbase_util_Dates::date_mysql2tstamp($zeit1);
		$zeit2 = tx_rnbase_util_Dates::date_tstamp2mysql($tstamp1);

//		$sDate = gmstrftime("%d.%m.%Y", $tstamp1);
//		t3lib_div::debug($sDate, 'tx_rnbase_tests_dates_testcase :: test_dateConv'); // TODO: remove me
		$this->assertEquals($zeit1, $zeit2);
	}
	public function test_convert4TCA2Timestamp() {
		$record = array('datetime' => '2011-10-20 12:00:00', 'date' =>'2011-10-20', 'emptydate' =>'0000-00-00');
		tx_rnbase_util_Dates::convert4TCA2Timestamp($record, array('datetime', 'date', 'emptydate'));
		$this->assertEquals('1319112000', $record['datetime']);
		$this->assertEquals('1319068800', $record['date']);
		$this->assertEquals('0', $record['emptydate']);
	}
	public function test_convert4TCA2DateTime() {
		$record = array('datetime' => '1319112000');
		tx_rnbase_util_Dates::convert4TCA2DateTime($record, array('datetime'), TRUE);
		$this->assertEquals('2011-10-20 12:00:00', $record['datetime']);
		
	}
	public function test_convert4TCA2Date() {
		$record = array('date' => '1319068800');
		tx_rnbase_util_Dates::convert4TCA2Date($record, array('date'), TRUE);
		$this->assertEquals('2011-10-20', $record['date']);
		
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_Dates_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_Dates_testcase.php']);
}

