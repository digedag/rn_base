<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

class tx_rnbase_tests_rnbase_testcase extends tx_rnbase_tests_BaseTestCase
{
    public function testMakeInstanceSimpleObject()
    {
        $obj = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $this->assertTrue(is_object($obj), 'Object not instantiated');
    }

    public function testMakeInstanceObjectWithParameters()
    {
        $obj = tx_rnbase::makeInstance('tx_rnbase_filter_FilterItem', 'name', 'value');
        $this->assertTrue(is_object($obj), 'Object not instantiated');
        $this->assertEquals($obj->record['name'], 'name', 'Attribute not set');
        $this->assertEquals($obj->record['value'], 'value', 'Attribute not set');
    }

    public function testMakeInstanceOfExtBaseClass()
    {
        if (!$this->isExtBasePossible()) {
            $this->markTestSkipped();
        }
        $obj = tx_rnbase::makeInstance('Tx_T3sponsors_Domain_Model_Category');
        $this->assertTrue(is_object($obj), 'Object not instantiated');
    }

    private function isExtBasePossible()
    {
        tx_rnbase::load('tx_rnbase_util_TYPO3');
        // TODO: bessere Testklasse finden
        return false && tx_rnbase_util_TYPO3::isExtLoaded('extbase') &&
            tx_rnbase_util_TYPO3::isExtMinVersion('t3sponsors', 2001);
    }
}
