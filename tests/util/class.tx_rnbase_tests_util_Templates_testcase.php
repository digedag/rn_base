<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_Templates');

class tx_rnbase_tests_util_Templates_testcase extends tx_phpunit_testcase {

	private $backup = array();

	public function setUp() {
		$backup['getFileName_backPath'] = tx_rnbase_util_Templates::getTSTemplate()->getFileName_backPath;
		tx_rnbase_util_Templates::getTSTemplate()->getFileName_backPath = PATH_site;
	}
	public function tearDown(){
		tx_rnbase_util_Templates::getTSTemplate()->getFileName_backPath = $backup['getFileName_backPath'];

	}

	/**
	 */
	public function notest_performanceSimpleMarker() {

		$this->setTTOn();
		$runs = 10000;
		$markerArr = array('###UID###'=>2, '###PID###'=>1, '###TITLE###' => 'My Titel 1');
		$timeStart = microtime(TRUE);
		$memStart = memory_get_usage();
		for($i =1; $i<$runs; $i++) {
			$markerArr['###UID###'] = $i;
			tx_rnbase_util_Templates::substituteMarkerArrayCached(self::$template, $markerArr);
		}
		$time1 = microtime(TRUE) - $timeStart;
		$memEnd1 = memory_get_usage() - $memStart;

		$runs = 20000;
		$timeStart = microtime(TRUE);
		$memStart = memory_get_usage();
		for($i =1; $i<$runs; $i++) {
			$markerArr['###UID###'] = $i;
			tx_rnbase_util_Templates::substituteMarkerArrayCached(self::$template, $markerArr);
		}
		$time2 = microtime(TRUE) - $timeStart;
		$memEnd2 = memory_get_usage() - $memStart;

		$results = array();
		$results['Serie 1'] = array('Info'=> 'Timetrack on, Static MarkerArray', 'Time1'=>$time1, 'Time2'=> $time2,
			'Mem1'=>$memEnd1, 'Mem2'=>$memEnd2);


		$this->setTTOff();
		$runs = 10000;
		$markerArr = array('###UID###'=>2, '###PID###'=>1, '###TITLE###' => 'My Titel 1');
		$timeStart = microtime(TRUE);
		$memStart = memory_get_usage();
		for($i =1; $i<$runs; $i++) {
			$markerArr['###UID###'] = $i;
			tx_rnbase_util_Templates::substituteMarkerArrayCached(self::$template, $markerArr);
		}
		$time1 = microtime(TRUE) - $timeStart;
		$memEnd1 = memory_get_usage() - $memStart;

		$runs = 20000;
		$timeStart = microtime(TRUE);
		$memStart = memory_get_usage();
		for($i =1; $i<$runs; $i++) {
			$markerArr['###UID###'] = $i;
			tx_rnbase_util_Templates::substituteMarkerArrayCached(self::$template, $markerArr);
		}
		$time2 = microtime(TRUE) - $timeStart;
		$memEnd2 = memory_get_usage() - $memStart;
		$results['Serie 2'] = array('Info'=> 'Timetrack off, Static MarkerArray', 'Time1'=>$time1, 'Time2'=> $time2,
			'Mem1'=>$memEnd1, 'Mem2'=>$memEnd2);


		t3lib_div::debug($results, 'class.tx_rnbase_tests_util_Templates_testcase.php'); // TODO: remove me

	}

	public function test_includeSubTemplates() {

		$fixture = t3lib_div::getURL(
			t3lib_extMgm::extPath(
				'rn_base',
				'tests/fixtures/html/includeSubTemplates.html'
			)
		);

		$raw = tx_rnbase_util_Templates::getSubpart($fixture, '###TEMPLATE###');
		$expected = tx_rnbase_util_Templates::getSubpart($fixture, '###EXPECTED###');

		$included = tx_rnbase_util_Templates::includeSubTemplates($raw);

		$this->assertEquals($expected, $included);
	}
	public function test_substMarkerArrayCached() {
		$this->setTTOff();
		$markerArr = array('###UID###'=>2, '###PID###'=>1, '###TITLE###' => 'My Titel 1');
		$cnt = tx_rnbase_util_Templates::substituteMarkerArrayCached(self::$template, $markerArr);
		$exp = '
<html>
<h1>Test</h1>
<ul>
<li>UID: 2</li>
<li>PID: 1</li>
<li>Title: My Titel 1</li>
</ul>
</html>
';

		$this->assertEquals($exp, $cnt);
	}
	private function setTTOn() {
		$GLOBALS['TT'] = new t3lib_timeTrack;
		$GLOBALS['TT']->start();
	}
	private function setTTOff() {
		$GLOBALS['TT'] = new t3lib_timeTrackNull;
		$GLOBALS['TT']->start();
	}

	static $template = '
<html>
<h1>Test</h1>
<ul>
<li>UID: ###UID###</li>
<li>PID: ###PID###</li>
<li>Title: ###TITLE###</li>
</ul>
</html>
';
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_Templates_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_Templates_testcase.php']);
}

