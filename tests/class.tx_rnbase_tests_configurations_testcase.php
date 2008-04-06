<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');


//tx_div::load('tx_rnbase_configurations');
//require_once(PATH_t3lib."class.t3lib_content.php");
require_once(t3lib_extMgm::extPath('cms') . 'tslib/class.tslib_content.php');


class tx_rnbase_tests_configurations_testcase extends tx_phpunit_testcase {
  function test_tsSetup() {
  	$GLOBALS['TSFE'] = new tx_rnbase_tsfeDummy();
		$GLOBALS['TSFE']->tmpl->setup['lib.']['match.'] = array('limit' => '10' , 'count' => '99');

		$configurationArray['matchtable.']['match'] = '< lib.match';
  	$configurationArray['matchtable.']['match.']['limit'] = '100';
    $cObj = t3lib_div::makeInstance('tslib_cObj');
    $configurations = tx_div::makeInstance('tx_rnbase_configurations');
    $configurations->init($configurationArray, $cObj, 'extkey_text', 'rntest');

    $this->assertEquals(100, $configurations->get('matchtable.match.limit'), 'Limit should be 100');
    $this->assertEquals(99, $configurations->get('matchtable.match.count'), 'count should be 99');
  }
}

class tx_rnbase_tsfeDummy {
	var $tmpl;
	function tx_rnbase_tsfeDummy() {
		$this->tmpl = new tx_rnbase_templateDummy();
	}
}
class tx_rnbase_templateDummy {
	var $setup;
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnabse_tests_configurations_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnabse_tests_configurations_testcase.php']);
}

?>