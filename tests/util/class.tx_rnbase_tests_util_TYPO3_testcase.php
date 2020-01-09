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

tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 */
class tx_rnbase_tests_util_TYPO3_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetHttpUtilityClass()
    {
        $httpUtilityClass = tx_rnbase_util_TYPO3::getHttpUtilityClass();
        $this->assertEquals(
            'HTTP/1.1 404 Not Found',
            $httpUtilityClass::HTTP_STATUS_404
        );
    }
}
