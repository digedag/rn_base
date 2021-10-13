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

use Sys25\RnBase\Tests\BaseTestCase;

/**
 * tx_rnbase_tests_controller_testcase.
 *
 * @author          Hannes Bochmann <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_UtilityTest extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetRecordTitle()
    {
        Tx_Rnbase_Backend_UtilityForTests::getRecordTitle(1, 2, 3, 4);

        self::assertEquals(
            ['getRecordTitle' => [1, 2, 3, 4]],
            Typo3BackendUtilityClass::$lastCalledMethod
        );
    }
}

class Tx_Rnbase_Backend_UtilityForTests extends Tx_Rnbase_Backend_Utility
{
    /**
     * @return Typo3BackendUtilityClass
     */
    protected static function getBackendUtilityClass()
    {
        return 'Typo3BackendUtilityClass';
    }
}

/**
 * mit diser Klasse stellen wir fest welche Methode mit welchen Parametern aufgerufen wurde.
 */
class Typo3BackendUtilityClass
{
    /**
     * @var array der key ist der methoden name, der value die übergebenen Parameter
     */
    public static $lastCalledMethod = [];

    /**
     * @param string $method
     * @param array  $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        self::$lastCalledMethod = [$method => $arguments];
    }
}
