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

tx_rnbase::load('tx_rnbase_util_TYPO3');

class tx_rnbase_tests_cache_testcase extends tx_phpunit_testcase {

	function test_TYPO3Cache() {
		if(!tx_rnbase_util_TYPO3::isTYPO43OrHigher()) return; // Geht erst ab 4.3
		$cache = self::createTYPO3Cache('__rnbaseTestTYPO3Cache__');
		$this->assertTrue(is_object($cache), 'Cache not instanciated');
		$cache->set('key1', array('id' => '100'));
		$arr = $cache->get('key1');
		$this->assertTrue(count($arr) == 1, 'Array has wrong size');
		$this->assertEquals($arr['id'], '100', 'Array content is wrong');
	}

	function test_NoCache() {
		$cache = tx_rnbase::makeInstance('tx_rnbase_cache_NoCache', '__rnbaseTestNoCache__');
		$this->assertTrue(is_object($cache), 'Cache not instanciated');
		$cache->set('key1', array('id' => '100'));
		$arr = $cache->get('key1');
		$this->assertTrue($arr == null, 'Array is set');
	}

	/**
	 * Returns the cache
	 *
	 * @param string $name
	 * @return tx_rnbase_cache_ICache
	 */
	private static function createTYPO3Cache($name) {
		return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache', $name);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_cache_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_cache_testcase.php']);
}

?>