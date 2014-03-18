<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Rene Nitzsche (rene@system25.de)
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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_SimpleMarker');
tx_rnbase::load('tx_rnbase_util_TS');

class tx_rnbase_util_SimpleMarkerTests extends tx_rnbase_util_SimpleMarker {
	// die methode public machen.
	// mit einer reflaction funktioniert es nicht, da die parameter als referenzen angelnommen werden müssen!
	public function prepareSubparts(array &$wrappedSubpartArray, array &$subpartArray,$template, $item, $formatter, $confId, $marker) {
		parent::prepareSubparts($wrappedSubpartArray, $subpartArray, $template, $item, $formatter, $confId, $marker);
	}
}
/**
 * @author Michael Wagner <mihcael.wagner@das-medienkombinat.de>
 */
class tx_rnbase_tests_util_SimpleMarker_testcase extends tx_rnbase_tests_BaseTestCase {

	public function testPrepareSubparts() {
		$formatter = $this->buildFormatter();
		$item = tx_rnbase::makeInstance('tx_rnbase_model_base', array(
			'uid' => 0,
			'fcol' => 'foo',
			'bcol' => 'bar',
		));
		// die marker müssen im template vorhanden sein, da diese sonnst nicht gerendert werden
		$template = <<<HTML
###ITEM_FCOL_IS_HIDDEN### ITEM_FCOL_IS_HIDDEN ###ITEM_FCOL_IS_HIDDEN###
###ITEM_FCOL_IS_VISIBLE### ITEM_FCOL_IS_VISIBLE ###ITEM_FCOL_IS_VISIBLE###
###ITEM_BCOL_IS_VERSTECKT### ITEM_BCOL_IS_VERSTECKT ###ITEM_BCOL_IS_VERSTECKT###
###ITEM_BCOL_IS_SICHTBAR### ITEM_BCOL_IS_SICHTBAR ###ITEM_BCOL_IS_SICHTBAR###
###ITEM_UNUSED_VISIBLE### ITEM_UNUSED_VISIBLE ###ITEM_UNUSED_VISIBLE###
###ITEM_UNUSED_HIDDEN### ITEM_UNUSED_HIDDEN ###ITEM_UNUSED_HIDDEN###
HTML;
		$marker = tx_rnbase::makeInstance('tx_rnbase_util_SimpleMarkerTests');
		$wrappedSubpartArray = $subpartArray = array();
		$marker->prepareSubparts(
			$wrappedSubpartArray, $subpartArray,
			$template, $item, $formatter, 'action.item.', 'ITEM'
		);

		// auszugebende subparts
		$this->assertTrue(array_key_exists('###ITEM_FCOL_IS_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertTrue(is_array($wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###']), 'FailedOn:'.__LINE__);
		$this->assertEquals('', $wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###'][0], 'FailedOn:'.__LINE__);
		$this->assertEquals('', $wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###'][1], 'FailedOn:'.__LINE__);
		$this->assertTrue(array_key_exists('###ITEM_BCOL_IS_VERSTECKT###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertTrue(array_key_exists('###ITEM_UNUSED_VISIBLE###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_FCOL_IS_VISIBLE###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_BCOL_IS_SICHTBAR###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_UNUSED_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_NOT_IN_TEMPLATE_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);

		// subparts, die nicht ausgegeben werden sollen
		$this->assertFalse(array_key_exists('###ITEM_FCOL_IS_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_BCOL_IS_VERSTECKT###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_UNUSED_VISIBLE###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertTrue(array_key_exists('###ITEM_FCOL_IS_VISIBLE###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertTrue(is_string('###ITEM_FCOL_IS_VISIBLE###'), 'FailedOn:'.__LINE__);
		$this->assertEquals('', $subpartArray['###ITEM_FCOL_IS_VISIBLE###'], 'FailedOn:'.__LINE__);
		$this->assertTrue(array_key_exists('###ITEM_BCOL_IS_SICHTBAR###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertTrue(array_key_exists('###ITEM_UNUSED_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);
		$this->assertFalse(array_key_exists('###ITEM_NOT_IN_TEMPLATE_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);

	}

	public function testPrepareItem() {
		$marker = tx_rnbase::makeInstance('tx_rnbase_util_SimpleMarker');

		$long61 = str_repeat('12.45', 12) . '!';
		$model = tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			array(
				'uid' => 1,
				'field' => 'name',
				'field.name' => 'fieldname',
				'fieldname' => 'field.name',
				// größer als 60 zeichen, wird ignoriert
				'longdot' => $long61,
			)
		);

		$this->callInaccessibleMethod($marker, 'prepareItem', $model);

		$data = $model->getRecord();

		$this->assertArrayHasKey('field', $data);
		$this->assertEquals($data['field'], 'name');

		$this->assertArrayHasKey('field.name', $data);
		$this->assertEquals($data['field.name'], 'fieldname');

		$this->assertArrayHasKey('longdot', $data);
		$this->assertEquals($data['longdot'], $long61);

		$this->assertArrayHasKey('fieldname', $data);
		$this->assertEquals($data['fieldname'], 'field.name');

		$this->assertArrayHasKey('_field_name', $data);
		$this->assertEquals($data['_field_name'], 'fieldname');

		$this->assertArrayHasKey('fieldname', $data);
		$this->assertEquals($data['_fieldname'], 'field_name');

		$this->assertArrayNotHasKey('_longdot', $data);
	}

	/**
	 * liefert einen formatter inklusive typoscript
	 * @return tx_rnbase_util_FormatUtil
	 */
	protected function buildFormatter() {
		$typoScript = <<<TS
action.item.subparts {
	fcol_is {
		visible = TEXT
		visible.value = 1
		visible.if {
		value = tt_content
			equals.data = field:baz
		}
	}
	bcol_is {
		marker {
			visible = SICHTBAR
			hidden = VERSTECKT
		}
		visible = TEXT
		visible.value = 1
		visible.if {
		value = tt_content
			equals.data = field:bar
		}
	}
	unused {
		visible = 1
	}
	not_in_template {
		visible = 1
	}
}
TS;
		$configurationArray = tx_rnbase_util_TS::parseTsConfig($typoScript);

		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
		$configurations->init($configurationArray, $cObj, 'extkey_text', 'rntest');
		$formatter = tx_rnbase::makeInstance('tx_rnbase_util_FormatUtil', $configurations);
		return $formatter;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_SimpleMarker_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_util_SimpleMarker_testcase.php']);
}

