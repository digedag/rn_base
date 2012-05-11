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

class tx_rnbase_dummyController extends tx_rnbase_controller{

	public function callGetErrorMailHtml() {
		try {
			throw new Exception('My Exception');
		} catch (Exception $e) {
			return $this->getErrorMailHtml($e,'someAction');
		}
	}
}

class tx_rnbase_tests_controller_testcase extends tx_phpunit_testcase {

	function testGetErrorMailHtmlRemovesPasswordParams() {
		$_GET['getSubArray']['password'] = 'somePass';
		$_GET['getSubArray']['getSubDontRemove'] = 'inSubArray';
		$_GET['getDontRemove'] = 'inRootArray';
		$_POST['passwort'] = 'somePass';
		$_POST['postDontRemove'] = 'somePass';
		$controller = tx_rnbase::makeInstance('tx_rnbase_dummyController');
		
		$html = $controller->callGetErrorMailHtml();
		// hier wird nur die removePasswordParams Methode getestet,
		// lässt sich im HTML schwierig prüfen.
		// Besser direkt den removePasswordParams Aufruf testen?
		$this->assertNotContains('password', $html, '"Password" Params not removed!');
		$this->assertNotContains('passwort', $html, '"Passwort" Params not removed!');
		$this->assertContains('postDontRemove', $html, '"postDontRemove" Params removed!');
		$this->assertContains('getDontRemove', $html, '"getDontRemove" Params removed!');
		$this->assertContains('getSubDontRemove', $html, '"getSubDontRemove" Params removed!');
  }
}