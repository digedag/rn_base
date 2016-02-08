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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Network');

/**
 * tx_rnbase_tests_util_Network_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <rene@system25.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_util_Network_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @var string $devIpMaskBackup
	 */
	protected $devIpMaskBackup;

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->devIpMaskBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $this->devIpMaskBackup;
	}

	/**
	 * @group unit
	 */
	public function testLocationHeaderUrl() {
		self::assertEquals(
			'http://www.google.de/url.html',
			tx_rnbase_util_Network::locationHeaderUrl('http://www.google.de/url.html')
		);
	}

	/**
	 * @group unit
	 */
	public function testShouldExceptionBeDebuggedIfDevIpMaskMatches() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = tx_rnbase_util_Misc::getIndpEnv('REMOTE_ADDR');
		self::assertTrue(tx_rnbase_util_Network::isDevelopmentIp());
	}

	/**
	 * @group unit
	 */
	public function testShouldExceptionBeDebuggedIfDevIpMaskMatchesNot() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = 'invalid';
		self::assertFalse(tx_rnbase_util_Network::isDevelopmentIp());
	}
}
