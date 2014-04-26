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
tx_rnbase::load('tx_rnbase_controller');
tx_rnbase::load('tx_rnbase_exception_IHandler');

class tx_rnbase_dummyController extends tx_rnbase_controller{

	public function callGetErrorMailHtml() {
		try {
			throw new Exception('My Exception');
		} catch (Exception $e) {
			return $this->getErrorMailHtml($e, 'someAction');
		}
	}
}

class tx_rnbase_tests_controller_testcase extends tx_phpunit_testcase {
	
	private $exceptionHandlerConfig;
	
	protected function setUp() {
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']);
		$this->exceptionHandlerConfig = $extConfig['exceptionHandler'];
		
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = TRUE;
	}
	
	protected function tearDown() {
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']);
		$extConfig['exceptionHandler'] = $this->exceptionHandlerConfig;
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base'] = serialize($extConfig);
	}

	/**
	 * @group unit
	 */
	public function testHandleExceptionWithDefautlExceptionHandler() {
		$this->setExceptionHandlerConfig();
		
		$method = new ReflectionMethod(
			'tx_rnbase_controller', 
			'handleException'
		);
		$method->setAccessible(TRUE);
		
		$controller = tx_rnbase::makeInstance('tx_rnbase_controller');
		$exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests');
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
		
		$handleExceptionReturn = $method->invoke(
			$controller, 'testAction', $exception, $configurations
		);
		
		$this->assertEquals(
			'testAction Exception for tx_rnbase_exception_HandlerForTests',
			$handleExceptionReturn,
			'wrong error message'
		);
	}
	
	/**
	 * @group unit
	 */
	public function testHandleExceptionUsesDefaultExceptionHandlerIfConfiguredExceptionHandlerImplementsNotIhandlerInterface() {
		$this->setExceptionHandlerConfig('tx_rnbase_exception_HandlerWithoutCorrectInterface');
	
		$method = new ReflectionMethod(
			'tx_rnbase_controller',
			'handleException'
		);
		$method->setAccessible(TRUE);
	
		$controller = tx_rnbase::makeInstance('tx_rnbase_controller');
		$exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests');
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
	
		$handleExceptionReturn = $method->invoke(
			$controller, 'testAction', $exception, $configurations
		);
	
		$this->assertEquals(
			'testAction Exception for tx_rnbase_exception_HandlerForTests',
			$handleExceptionReturn,
			'wrong error message'
		);
	}
	
	/**
	 * @group unit
	 */
	public function testHandleExceptionUsesConfiguredExceptionHandler() {
		$this->setExceptionHandlerConfig('tx_rnbase_exception_CustomHandler');
	
		$method = new ReflectionMethod(
				'tx_rnbase_controller',
				'handleException'
		);
		$method->setAccessible(TRUE);
	
		$controller = tx_rnbase::makeInstance('tx_rnbase_controller');
		$exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests');
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
	
		$handleExceptionReturn = $method->invoke(
			$controller, 'testAction', $exception, $configurations
		);
	
		$this->assertEquals(
			'custom handler',
			$handleExceptionReturn,
			'wrong error message'
		);
	}
	
	/**
	 * @return void
	 */
	private function setExceptionHandlerConfig($exceptionHandler = '') {
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']);
		$extConfig['exceptionHandler'] = $exceptionHandler;
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base'] = serialize($extConfig);
	}
}

/**
 * nochmal bereitstellen damit die original klasse nicht geladen wird
 * 
 * @author Hannes Bochmann
 *
 */
class tx_rnbase_exception_Handler implements tx_rnbase_exception_IHandler {

	public function handleException($actionName, Exception $e, tx_rnbase_configurations $configurations) {
		return $actionName . ' ' . $e->getMessage();
	}
}

/**
 * nochmal bereitstellen damit die original klasse nicht geladen wird
 *
 * @author Hannes Bochmann
 *
 */
class tx_rnbase_exception_HandlerWithoutCorrectInterface {

	public function handleException($actionName, Exception $e, tx_rnbase_configurations $configurations) {
		return 'should not be used';
	}
}

class tx_rnbase_exception_CustomHandler implements tx_rnbase_exception_IHandler {

	public function handleException($actionName, Exception $e, tx_rnbase_configurations $configurations) {
		return 'custom handler';
	}
}