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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 * tx_rnbase_tests_action_BaseIOC_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_action_BaseIOC_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * {@inheritDoc}
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->cleanUpPageRenderer();

		tx_rnbase_util_Misc::prepareTSFE(array('force' => TRUE));
	}

	/**
	 * {@inheritDoc}
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$this->cleanUpPageRenderer();
	}

	/**
	 * @return void
	 */
	protected function cleanUpPageRenderer() {
		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsFiles');
		$property->setAccessible(TRUE);
		$property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), array());

		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsLibs');
		$property->setAccessible(TRUE);
		$property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), array());

		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'cssFiles');
		$property->setAccessible(TRUE);
		$property->setValue(tx_rnbase_util_TYPO3::getPageRenderer(), array());
	}

	/**
	 * @group unit
	 */
	public function testAddRessourcesAddsCssFiles() {
		$action = $this->getAction();
		$configurations = $this->createConfigurations(array(
			'testConfId.' => array(
				'includeCSS.' => array(
					1 => 'typo3conf/ext/rn_base/ext_emconf.php',
					2 => 'EXT:rn_base/ext_icon.gif'
				)
			)
		), 'rn_base');
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'cssFiles');
		$property->setAccessible(TRUE);
		$files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

		self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['typo3conf/ext/rn_base/ext_emconf.php']['file']);
		self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['typo3conf/ext/rn_base/ext_icon.gif']['file']);
	}

	/**
	 * @group unit
	 */
	public function testAddRessourcesAddsJavaScriptFooterFiles() {
		$action = $this->getAction();
		$configurations = $this->createConfigurations(array(
			'testConfId.' => array(
				'includeJSFooter.' => array(
					'1' => 'typo3conf/ext/rn_base/ext_emconf.php',
					'2' => 'EXT:rn_base/ext_icon.gif',
					'3' => '//www.dmk-ebusiness.de',
					'3.' => array('external' => 1)
				)
			)
		), 'rn_base');
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsFiles');
		$property->setAccessible(TRUE);
		$files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

		self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['typo3conf/ext/rn_base/ext_emconf.php']['file']);
		self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['typo3conf/ext/rn_base/ext_icon.gif']['file']);
		self::assertEquals('//www.dmk-ebusiness.de', $files['//www.dmk-ebusiness.de']['file']);
	}

	/**
	 * @group unit
	 */
	public function testAddRessourcesAddsJavaScriptLibraryFiles() {
		$action = $this->getAction();
		$configurations = $this->createConfigurations(array(
			'testConfId.' => array(
				'includeJSlibs.' => array(
					'first' => 'typo3conf/ext/rn_base/ext_emconf.php',
					'second' => 'EXT:rn_base/ext_icon.gif',
					'third' => '//www.dmk-ebusiness.de',
					'third.' => array('external' => 1)
				)
			)
		), 'rn_base');
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod($action, 'addResources', $configurations, 'testConfId.');

		$property = new ReflectionProperty('\\TYPO3\\CMS\\Core\\Page\\PageRenderer', 'jsLibs');
		$property->setAccessible(TRUE);
		$files = $property->getValue(tx_rnbase_util_TYPO3::getPageRenderer());

		self::assertEquals('typo3conf/ext/rn_base/ext_emconf.php', $files['first']['file']);
		self::assertEquals('typo3conf/ext/rn_base/ext_icon.gif', $files['second']['file']);
		self::assertEquals('//www.dmk-ebusiness.de', $files['third']['file']);
	}

	/**
	 * @group unit
	 */
	public function testAddCacheTags() {
		$action = $this->getAction();
		$configurations = $this->createConfigurations(array(
			'testConfId.' => array(
				'cacheTags.' => array(
					0 => 'first',
					1 => 'second',
				)
			)
		), 'rn_base');
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod($action, 'addCacheTags');

		$property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
		$property->setAccessible(TRUE);
		$cacheTags = $property->getValue(tx_rnbase_util_TYPO3::getTSFE());

		self::assertEquals(array('first', 'second'), $cacheTags);
	}

	/**
	 * @group unit
	 */
	public function testAddCacheTagsIfNotConfigured() {
		$action = $this->getAction();
		$configurations = $this->createConfigurations(array('testConfId.' => array()), 'rn_base');
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod($action, 'addCacheTags');

		$property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
		$property->setAccessible(TRUE);
		$cacheTags = $property->getValue(tx_rnbase_util_TYPO3::getTSFE());

		self::assertEquals(array(), $cacheTags);
	}

	/**
	 * @return tx_rnbase_action_BaseIOC
	 */
	protected function getAction() {
		$action = $this->getMockForAbstractClass(
			'tx_rnbase_action_BaseIOC', array(),'', true, true, true,
			array('getTemplateName', 'getViewClassName', 'handleRequest')
		);

		$action->expects(self::any())
			->method('getTemplateName')
			->will(self::returnValue('testConfId'));

		return $action;
	}
}
