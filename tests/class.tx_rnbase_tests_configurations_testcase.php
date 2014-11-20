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



class tx_rnbase_tests_configurations_testcase extends tx_phpunit_testcase {
  function test_tsSetup() {
  	$GLOBALS['TSFE'] = new tx_rnbase_tsfeDummy();
		$GLOBALS['TSFE']->tmpl->setup['lib.']['match.'] = array('limit' => '10' , 'count' => '99');

		$configurationArray['matchtable.']['match'] = '< lib.match';
  	$configurationArray['matchtable.']['match.']['limit'] = '100';
    $cObj = t3lib_div::makeInstance('tslib_cObj');
    $configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
    $configurations->init($configurationArray, $cObj, 'extkey_text', 'rntest');

    $this->assertEquals(100, $configurations->get('matchtable.match.limit'), 'Limit should be 100');
    $this->assertEquals(99, $configurations->get('matchtable.match.count'), 'count should be 99');
  }
  /**
   * Test flexform value with pointed keys.
   *
   */
  function test_flexformSetup() {
  	$GLOBALS['TSFE'] = new tx_rnbase_tsfeDummy();
		$GLOBALS['TSFE']->tmpl->setup['lib.']['feuser.']['link'] = array('pid' => '10');

		$flexXml = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?> <T3FlexForms>  <data>  <sheet index="sDEF">  <language index="lDEF">  <field index="action">  <value index="vDEF">tx_rnuserregister_actions_Login</value>  </field>  <field index="feuserPages">  <value index="vDEF"></value>  </field>  <field index="feuserPagesRecursive">  <value index="vDEF"></value>  </field>  </language>  </sheet>  <sheet index="s_loginbox">  <language index="lDEF">  <field index="view.loginbox.header">  <value index="vDEF">Welcome</value>  </field>  <field index="view.loginbox.message">  <value index="vDEF"></value>   </field>  <field index="listview.fegroup.link.pid">  <value index="vDEF">25</value>   </field> <field index="detailview.feuser.link.pid">  <value index="vDEF">35</value>   </field>  </language>  </sheet>  </data> </T3FlexForms>';
		$configurationArray['template'] = 'test.html';
		$configurationArray['view.']['dummy'] = '1';
		$configurationArray['view.']['dummy.']['test'] = '1';
		$configurationArray['view.']['loginbox.']['header.']['enable'] = '1';
		$configurationArray['view.']['loginbox.']['header'] = 'Wrong Header';
		$configurationArray['view.']['loginbox.']['message'] = 'Hello';
		$configurationArray['listview.']['feuser'] = '< lib.feuser';

    $cObj = t3lib_div::makeInstance('tslib_cObj');
    $cObj->data['pi_flexform'] = $flexXml;
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
    $configurations->init($configurationArray, $cObj, 'extkey_text', 'rntest');

    $this->assertEquals('Welcome', $configurations->get('view.loginbox.header'), 'Header should be Welcome');
    $this->assertEquals('Hello', $configurations->get('view.loginbox.message'), 'Message should be Hello');
    $this->assertEquals('test.html', $configurations->get('template'), 'Template should be test.html');
    $this->assertEquals('1', $configurations->get('view.dummy'), 'Dummy should be 1');

    $pid = $configurations->get('listview.fegroup.link.pid');
    $this->assertEquals('25', $pid, 'PID from flexform should be 25 but was: ' . $pid);

    $pid = $configurations->get('detailview.feuser.link.pid');
    $this->assertEquals('35', $pid, 'PID from flexform should be 35 but was: ' . $pid);
  }

	/**
	 * Wir testen, ob die Links richtig aufgelöst werden
	 * Beispiel TS:
	 * 	lib.rnbase.root {
	 * 		name = Root
	 * 		version = 0.1.0
	 * 	}
	 * 	lib.rnbase.child = < lib.rnbase.root
	 * 	lib.rnbase.child {
	 * 		name = Child
	 * 	}
	 */
	public function test_TsReference() {
		$GLOBALS['TSFE'] = new tx_rnbase_tsfeDummy();
		$GLOBALS['TSFE']->tmpl->setup['lib.']['rnbase.'] = array();
		$lib = &$GLOBALS['TSFE']->tmpl->setup['lib.']['rnbase.'];
		$lib['root.'] = array(
			'root' => 'Root',
			'version' => '0.1.0',
		);
		$lib['child'] = '< lib.rnbase.root';
		$lib['child.'] = array(
			'child' => 'Child',
		);
		/* @var $configurations tx_rnbase_configurations */
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
		$configurationArray = array(
			'recursive' => '< lib.rnbase.child',
			'recursive.' => array(
				'current' => 'This'
			),
		);
		$configurations->init($configurationArray, $configurations->getCObj(), 'rnbase', 'rnbase');

		$noDot = $configurations->get('recursive');
		$this->assertEquals('< lib.rnbase.child', $noDot);

		$withDot = $configurations->get('recursive.');
		$this->assertTrue(is_array($withDot));
		$this->assertEquals('Root', $withDot['root']);
		$this->assertEquals('0.1.0', $withDot['version']);
		$this->assertEquals('Child', $withDot['child']);
		$this->assertEquals('This', $withDot['current']);
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

