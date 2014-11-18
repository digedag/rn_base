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
tx_rnbase::load('tx_rnbase_util_PageBrowser');

class tx_rnbase_tests_util_PageBrowser_testcase extends tx_phpunit_testcase {

	public function test_getStateSimple() {
		$pb = new tx_rnbase_util_PageBrowser('test');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$parameters->offsetSet($pb->getParamName('pointer'), 3);
		$listSize = 103; //Gesamtgröße der darzustellenden Liste
		$pageSize = 10; //Größe einer Seite
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(30, $state['offset']);
		$this->assertEquals(10, $state['limit']);

		$parameters->offsetSet($pb->getParamName('pointer'), 10);
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(100, $state['offset']);
		$this->assertEquals(10, $state['limit']);

		// Listenende passt genau auf letzte Seite 
		$parameters->offsetSet($pb->getParamName('pointer'), 10);
		$listSize = 100; //Gesamtgröße der darzustellenden Liste
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(90, $state['offset'], 'Offset ist falsch');
		$this->assertEquals(10, $state['limit'], 'Limit ist falsch');

		$parameters->offsetSet($pb->getParamName('pointer'), 0);
		$listSize = 5; //Gesamtgröße der darzustellenden Liste
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(0, $state['offset'], 'Offset ist falsch');
		$this->assertEquals(5, $state['limit'], 'Limit ist falsch');
	}

	public function test_getStateWithEmptyListAndNoPointer() {
		$pb = new tx_rnbase_util_PageBrowser('test');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$listSize = 0; //Gesamtgröße der darzustellenden Liste
		$pageSize = 10; //Größe einer Seite
		$pb->setState($parameters, $listSize, $pageSize);
		$state = $pb->getState();
		$this->assertEquals(0, $state['offset'], 'Offset ist falsch');
		$this->assertEquals(0, $state['limit'], 'Limit ist falsch');
	}
	
	public function test_getStateWithPointerOutOfRange() {
		$pb = new tx_rnbase_util_PageBrowser('test');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$parameters->offsetSet($pb->getParamName('pointer'), 11);
		$listSize = 103; //Gesamtgröße der darzustellenden Liste
		$pageSize = 10; //Größe einer Seite
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(100, $state['offset']);
		$this->assertEquals(10, $state['limit']);

		$parameters->offsetSet($pb->getParamName('pointer'), 13);
		$pb->setState($parameters, $listSize, $pageSize);
		$state = $pb->getState();
		$this->assertEquals(100, $state['offset']);
		$this->assertEquals(10, $state['limit']);
		
		$listSize = 98; //Gesamtgröße der darzustellenden Liste
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(90, $state['offset']);
		$this->assertEquals(10, $state['limit']);
	}

	public function test_getStateWithIllegalPointer() {
		$pb = new tx_rnbase_util_PageBrowser('test');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$parameters->offsetSet($pb->getParamName('pointer'), -2);
		$listSize = 103; //Gesamtgröße der darzustellenden Liste
		$pageSize = 10; //Größe einer Seite
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();
		$this->assertEquals(0, $state['offset']);
		$this->assertEquals(10, $state['limit']);
	}

	public function test_getStateWithSmallList() {
		$pb = new tx_rnbase_util_PageBrowser('test');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$parameters->offsetSet($pb->getParamName('pointer'), 2);
		$listSize = 3; //Gesamtgröße der darzustellenden Liste
		$pageSize = 10; //Größe einer Seite
		$pb->setState($parameters, $listSize, $pageSize);

		$state = $pb->getState();

		$this->assertEquals(0, $state['offset'], 'Offset ist falsch');
		$this->assertEquals(3, $state['limit'], 'Limit ist falsch');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_PageBrowser_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/util/class.tx_rnbase_tests_util_PageBrowser_testcase.php']);
}

