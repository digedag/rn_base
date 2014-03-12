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
tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 */
class tx_rnbase_tests_util_TCA_testcase extends tx_phpunit_testcase {

	public function testGetTransOrigPointerFieldForTableWithUnknownTable() {
		$this->assertEmpty(
			tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('unknown'),
			'transOrigPointerField returned'
		);
	}

	public function testGetTransOrigPointerFieldForTableWithUnconfiguredTable() {
		$this->assertEmpty(
			tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('be_users'),
			'transOrigPointerField returned'
		);
	}

	public function testGetTransOrigPointerFieldForTableWithTtContentTable() {
		$this->assertEquals(
			'l18n_parent',
			tx_rnbase_util_TCA::getTransOrigPointerFieldForTable('tt_content'),
			'wrong transOrigPointerField returned'
		);
	}

	public function testGetLanguageFieldForTableWithUnknownTable() {
		$this->assertEmpty(
			tx_rnbase_util_TCA::getLanguageFieldForTable('unknown'),
			'languageField returned'
		);
	}

	public function testGetLanguageFieldForTableWithUnconfiguredTable() {
		$this->assertEmpty(
			tx_rnbase_util_TCA::getLanguageFieldForTable('be_users'),
			'languageField returned'
		);
	}

	public function testGetLanguageFieldForTableWithTtContentTable() {
		$this->assertEquals(
			'sys_language_uid',
			tx_rnbase_util_TCA::getLanguageFieldForTable('tt_content'),
			'wrong languageField returned'
		);
	}
}