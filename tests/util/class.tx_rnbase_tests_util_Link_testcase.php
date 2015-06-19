<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2015 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Link');


/**
 * Basis Testcase
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_tests
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_util_Link_testcase
	extends tx_rnbase_tests_BaseTestCase {



	/**
	 *
	 * @return void
	 *
	 * @dataProvider getMakeUrlOrTagData
	 * @group unit
	 * @test
	 */
	public function testMakeUrlOrTag($typolink, $absUrl, $schema, $expected, $method = 'makeUrl')
	{

		$cObject = $this->getMock('stdClass', array('typolink'));
		$cObject
			->expects($this->once())
			->method('typolink')
			->will($this->returnValue($typolink))
		;
		$link = $this->getMock(
			'tx_rnbase_util_Link',
			array('getCObj', 'isAbsUrl', 'getAbsUrlSchema')
		);
		$link
			->expects($this->once())
			->method('getCObj')
			->will($this->returnValue($cObject))
		;
		$link
			->expects($this->once())
			->method('isAbsUrl')
			->will($this->returnValue($absUrl))
		;
		$link
			->expects($this->any())
			->method('getAbsUrlSchema')
			->will($this->returnValue($schema))
		;

		$method = $method === 'makeTag' ? 'makeTag' : 'makeUrl';

		$this->assertEquals($expected, $link->{$method}());
	}

	/**
	 * Liefert die Daten für den testMakeUrlOrTag testcase.
	 *
	 * @return array
	 */
	public function getMakeUrlOrTagData()
	{
		return array(
			// makeUrl
			__LINE__ => array(
				'typolink' => 'service/faq.html',
				'absUrl' => FALSE,
				'schema' => 'http://www.system25.de/',
				'expected' => 'service/faq.html',
			),
			__LINE__ => array(
				'typolink' => 'service/faq.html',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => 'http://www.system25.de/service/faq.html',
			),
			__LINE__ => array(
				'typolink' => 'http://www.system25.de/service/faq.html',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => 'http://www.system25.de/service/faq.html',
			),
			__LINE__ => array(
				'typolink' => '//www.system25.de/service/faq.html',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => '//www.system25.de/service/faq.html',
			),
			// makeTag
			__LINE__ => array(
				'typolink' => '<img src="service/faq.jpg" />',
				'absUrl' => FALSE,
				'schema' => 'http://www.system25.de/',
				'expected' => '<img src="service/faq.jpg" />',
				'method' => 'makeTag',
			),
			__LINE__ => array(
				'typolink' => '<a href="service/faq.html">FAQ</a>',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
				'method' => 'makeTag',
			),
			__LINE__ => array(
				'typolink' => '<img src="service/faq.jpg" />',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => '<img src="http://www.system25.de/service/faq.jpg" />',
				'method' => 'makeTag',
			),
			__LINE__ => array(
				'typolink' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
				'method' => 'makeTag',
			),
			__LINE__ => array(
				'typolink' => '<a href="//www.system25.de/service/faq.html">FAQ</a>',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => '<a href="//www.system25.de/service/faq.html">FAQ</a>',
				'method' => 'makeTag',
			),
			// invalide tags bleiben unangetatset!
			__LINE__ => array(
				'typolink' => 'a href="service/faq.html">FAQ</a',
				'absUrl' => TRUE,
				'schema' => 'http://www.system25.de/',
				'expected' => 'a href="service/faq.html">FAQ</a',
				'method' => 'makeTag',
			),
		);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_PageBrowser_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_PageBrowser_testcase.php']);
}