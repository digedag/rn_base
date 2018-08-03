<?php
/***************************************************************
*  Copyright notice
*
*  (c) Rene Nitzsche (rene@system25.de)
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
 * tx_rnbase_cache_Manager_testcase.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_cache_Manager_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetCacheClass()
    {
        $expectedCacheClass = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';

        self::assertSame(
            $expectedCacheClass, tx_rnbase_cache_Manager::getCacheClass(tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE)
        );
    }
}
