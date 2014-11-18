<?php
/***************************************************************
*  Copyright notice
*
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_model_data');

/**
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_tests
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_model_Data_testcase extends tx_phpunit_testcase {

	/**
	 * test object with testdata
	 *
	 * @return tx_rnbase_model_data
	 */
	private function getModelInstance() {
		$data = array(
			'uid' => 50,
			'first_name' => 'John',
			'last_name' => 'Doe',
		);
		return tx_rnbase_model_data::getInstance($data);
	}

	/**
	 * @test
	 */
	public function testMagicCalls() {
		$model = $this->getModelInstance();
		$this->assertEquals(50, $model->getUid());

		$this->assertTrue($model->hasFirstName());
		$this->assertEquals('John', $model->getFirstName());
		$this->assertInstanceOf('tx_rnbase_model_data', $model->setFirstName('Max'));
		$this->assertEquals('Max', $model->getFirstName());

		$this->assertTrue($model->hasLastName());
		$this->assertEquals('Doe', $model->getLastName());
		$this->assertInstanceOf('tx_rnbase_model_data', $model->unsLastName());
		$this->assertFalse($model->hasLastName());
		$this->assertNull($model->getLastName());

		$this->assertFalse($model->hasGender());
		$this->assertInstanceOf('tx_rnbase_model_data', $model->setGender('male'));
		$this->assertTrue($model->hasGender());
		$this->assertEquals('male', $model->getGender());
		$this->assertInstanceOf('tx_rnbase_model_data', $model->unsGender());
		$this->assertFalse($model->hasGender());
		$this->assertNull($model->getGender());
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1406625817
	 */
	public function testMagicCallThrowsException() {
		$this->getModelInstance()->methodDoesNotExist();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Data_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/model/class.tx_rnbase_tests_model_Data_testcase.php']);
}
