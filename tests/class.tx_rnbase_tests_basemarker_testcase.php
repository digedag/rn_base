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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_util_BaseMarker');


class tx_rnbase_tests_basemarker_testcase extends tx_phpunit_testcase {
	function test_containsMarker() {

		$template = '
<html>
Das Spielergebnis:
###MATCH_HOME_NAME### - ###MATCH_GUEST_NAME###
###MATCH_GOALS_HOME_2### : ###MATCH_GOALS_GUEST_2###
</html>
';

		$this->assertTrue(tx_rnbase_util_BaseMarker::containsMarker($template, 'MATCH'), 'Marker MATCH nicht gefunden');
		$this->assertTrue(tx_rnbase_util_BaseMarker::containsMarker($template, 'MATCH_HOME'), 'Marker MATCH_HOME nicht gefunden');
		$this->assertFalse(tx_rnbase_util_BaseMarker::containsMarker($template, 'MATCH_ERR'), 'Marker MATCH_ERR wurde gefunden');
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_basemarker_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_basemarker_testcase.php']);
}

