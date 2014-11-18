<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Misc');

class tx_rnbase_dummyMisc extends tx_rnbase_util_Misc {

	public static function callGetErrorMailHtml($e, $actionName) {
		return self::getErrorMailHtml($e, $actionName);
	}
}

class tx_rnbase_tests_misc_testcase extends tx_phpunit_testcase {
	
	public function test_encodeParams() {
		$params['dat1'] = '1';
		$params['dat2'] = array('1', '2');
		$params['dat3'] = 123;
		$hash1 = tx_rnbase_util_Misc::createHash($params);
		$this->assertEquals(8, strlen($hash1));
		$params['dat2'] = array('2', '2');
		$hash2 = tx_rnbase_util_Misc::createHash($params);
		$this->assertEquals($hash2, $hash1);
		$hash2 = tx_rnbase_util_Misc::createHash($params, FALSE);
		$this->assertTrue($hash2 != $hash1);
		$params = array('1', array(1, 2), 123);
		$hash2 = tx_rnbase_util_Misc::createHash($params);
		$this->assertEquals($hash2, $hash1);
		$params = array(array(1, 2), '1', 123);
		$hash2 = tx_rnbase_util_Misc::createHash($params);
		$this->assertEquals($hash2, $hash1);
		
	}

	function testGetErrorMailHtmlRemovesPasswordParams() {
		$_GET['getSubArray']['password'] = 'somePass';
		$_GET['getSubArray']['getSubDontRemove'] = 'inSubArray';
		$_GET['getDontRemove'] = 'inRootArray';
		$_POST['passwort'] = 'somePass';
		$_POST['postDontRemove'] = 'somePass';

		$html = tx_rnbase_dummyMisc::callGetErrorMailHtml(new Exception('test'), 'myaction');
		// hier wird nur die removePasswordParams Methode getestet,
		// lässt sich im HTML schwierig prüfen.
		// Besser direkt den removePasswordParams Aufruf testen?
		$this->assertNotContains('password', $html, '"Password" Params not removed!');
		$this->assertNotContains('passwort', $html, '"Passwort" Params not removed!');
		$this->assertContains('postDontRemove', $html, '"postDontRemove" Params removed!');
		$this->assertContains('getDontRemove', $html, '"getDontRemove" Params removed!');
		$this->assertContains('getSubDontRemove', $html, '"getSubDontRemove" Params removed!');
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_misc_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_misc_testcase.php']);
}

