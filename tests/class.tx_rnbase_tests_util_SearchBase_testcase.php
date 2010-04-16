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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_tests_util_DB_testcase');

class tx_rnbase_tests_utilSearchBase_testcase extends tx_phpunit_testcase {
	function test_search() {
		$searcher = tx_rnbase::makeInstance('tx_rnbase_util_SearchGeneric');
		$options = $this->createOptions();

		// Test 1: Fehlerhaften Code in t3users korrigieren. Ein falsch gesetztes SEARCH_FIELD_JOINED muss ignoriert werden
		$fields = array();
		$fields[SEARCH_FIELD_JOINED][0]['cols'][] = 'FEUSER.UID';
		$ret = $searcher->search($fields, $options);

//		tx_rnbase_tests_util_DB_testcase::debugString($ret);
		t3lib_div::debug($ret,strpos($ret, 'AND  AND').' - class.tx_rnbase_tests_util_SearchBase_testcase.php : '); // TODO: remove me
		$this->assertTrue(strpos($ret, 'AND  AND') === false, 'SQL is wrong');
	}
	private function createOptions() {
		$options = array();
		$options['sqlonly'] = true;
		$options['searchdef']['basetable'] = 'fe_users';
//		$options['searchdef']['usealias'] = true;
//		$options['searchdef']['basetablealias'] = 'FEUSER';
		$options['orderby']['username'] = 'asc';
		
		return $options;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_utilSearchBase_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_utilSearchBase_testcase.php']);
}

?>