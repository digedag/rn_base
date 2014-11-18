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
tx_rnbase::load('tx_rnbase_model_base');

class tx_rnbase_tests_model_Base_testcase extends tx_phpunit_testcase {

	public function test_magiccall() {
		$model = new tx_rnbase_model_base(array('uid'=>1, 'test_value'=>45));
		$this->assertEquals(45, $model->getTestValue());
	}

	public function testGetUidWhenNoLocalisation() {
		$model = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 123))
		);
		$model->expects($this->once())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$this->assertEquals(123, $model->getUid(), 'uid field not used');
	}

	public function testGetUidWhenLocalisation() {
		$model = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 123, 'l18n_parent' => 456, 'sys_language_uid' => 789))
		);
		$model->expects($this->once())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$this->assertEquals(456, $model->getUid(), 'uid field not used');
	}

	public function testGetUidForNonTca() {
		$model = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(
				array(
					'uid' => '57',
					'field' => 'test',
				)
			)
		);
		$model->expects($this->once())
			->method('getTableName')
			->will($this->returnValue('tx_table_not_exists'));
		$this->assertEquals(57, $model->getUid(), 'uid field not used');
	}

	public function testGetUidForNonTable() {
		$model = tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			array(
				'uid' => '57',
				'field' => 'test',
			)
		);
		$this->assertEquals(57, $model->getUid(), 'uid field not used');
	}


	public function testGetUidForTranslatedSingleRecord() {
		$model = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 123, 'l18n_parent' => 0, 'sys_language_uid' => 789))
		);
		$model->expects($this->once())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$this->assertEquals(123, $model->getUid(), 'uid field not used');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Base_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Base_testcase.php']);
}

