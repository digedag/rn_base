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
tx_rnbase::load('tx_rnbase_util_Strings');

class tx_rnbase_tests_util_Strings_testcase extends tx_phpunit_testcase {
	// UTF-8 Text: 'The € - ä ö ü';
	static $utf8Str = 'a:18:{i:0;s:2:"54";i:1;s:2:"68";i:2;s:2:"65";i:3;s:2:"20";i:4;s:2:"e2";i:5;s:2:"82";i:6;s:2:"ac";i:7;s:2:"20";i:8;s:2:"2d";i:9;s:2:"20";i:10;s:2:"c3";i:11;s:2:"a4";i:12;s:2:"20";i:13;s:2:"c3";i:14;s:2:"b6";i:15;s:2:"20";i:16;s:2:"c3";i:17;s:2:"bc";}';
	// ISO-Text: 'The EUR - ä ö ü';
	static $iso8Str = 'a:15:{i:0;s:2:"54";i:1;s:2:"68";i:2;s:2:"65";i:3;s:2:"20";i:4;s:2:"45";i:5;s:2:"55";i:6;s:2:"52";i:7;s:2:"20";i:8;s:2:"2d";i:9;s:2:"20";i:10;s:2:"e4";i:11;s:2:"20";i:12;s:2:"f6";i:13;s:2:"20";i:14;s:2:"fc";}';
	
	public function test_isUtf8String() {

		$text = tx_rnbase_util_Strings::hexArr2bin(unserialize(self::$utf8Str));
		$value = $text;
		$this->assertTrue(tx_rnbase_util_Strings::isUtf8String($value)>0, 'String sollte in UTF-8 sein.');

//		$value = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text);

		$text = tx_rnbase_util_Strings::hexArr2bin(unserialize(self::$iso8Str));
		$value = $text;
		$this->assertTrue(tx_rnbase_util_Strings::isUtf8String($value) === FALSE, 'String sollte nicht in UTF-8 sein.');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_Strings_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_Strings_testcase.php']);
}

