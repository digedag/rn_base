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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * tx_rnbase_tests_util_FormTool_testcase
 *
 * @package 		TYPO3
 * @subpackage		rn_base
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_util_FormTool_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @group unit
	 */
	public function testCreateSelectSingleByArrayCallsJustCreateSelectByArray() {
		$formTool = $this->getMock('tx_rnbase_util_FormTool', array('createSelectByArray'));
		$formTool->expects(self::once())
			->method('createSelectByArray')
			->with(1, 2, array('test1'), array('test2'))
			->will(self::returnValue('returned'));

		self::assertEquals('returned', $formTool->createSelectSingleByArray(1, 2, array('test1'), array('test2')));
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArray() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray('testSelect', 2, array(1 => 'John', 2 => 'Doe'));
		$expectedSelect = '<select name="testSelect" class="select"><option value="1" >John</option><option value="2" selected="selected">Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArrayIfReloadOption() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray(
			'testSelect', 1, array(1 => 'John', 2 => 'Doe'), array('reload' => TRUE)
		);
		$expectedSelect = '<select name="testSelect" class="select" onchange=" this.form.submit(); " ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArrayIfOnchangeOption() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray(
			'testSelect', 1, array(1 => 'John', 2 => 'Doe'), array('onchange' => 'myJsFunction')
		);
		$expectedSelect = '<select name="testSelect" class="select" onchange="myJsFunction" ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArrayIfReloadAndOnchangeOption() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray(
			'testSelect', 1, array(1 => 'John', 2 => 'Doe'), array('onchange' => 'myJsFunction', 'reload' => TRUE)
		);
		$expectedSelect = '<select name="testSelect" class="select" onchange=" this.form.submit(); myJsFunction" ><option value="1" selected="selected">John</option><option value="2" >Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArrayIfMultipleOption() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray(
			'testSelect', '1,2', array(1 => 'John', 2 => 'Doe'), array('multiple' => TRUE)
		);
		$expectedSelect = '<select name="testSelect" class="select" multiple="multiple"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}

	/**
	 * @group unit
	 */
	public function testCreateSelectByArrayIfSizeOption() {
		$formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$select = $formTool->createSelectByArray(
			'testSelect', '1,2', array(1 => 'John', 2 => 'Doe'), array('size' => 20)
		);
		$expectedSelect = '<select name="testSelect" class="select" size="20"><option value="1" selected="selected">John</option><option value="2" selected="selected">Doe</option></select>';

		self::assertEquals($expectedSelect, $select);
	}
}