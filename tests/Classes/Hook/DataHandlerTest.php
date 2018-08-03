<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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
 * Tx_Rnbase_Hook_DataHandlerTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Hook_DataHandlerTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        if (isset($GLOBALS['TCA']['rn_base_test_table'])) {
            unset($GLOBALS['TCA']['rn_base_test_table']);
        }
    }

    /**
     * @group unit
     */
    public function testHookIsRegistered()
    {
        self::assertEquals(
            'Tx_Rnbase_Hook_DataHandler->clearCacheForConfiguredTagsByTable',
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['rn_base'],
            'Hook falsch registriert'
        );
    }

    /**
     * @group unit
     */
    public function testGetCacheManager()
    {
        $cacheManager = $this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Rnbase_Hook_DataHandler'), 'getCacheManager');
        self::assertTrue(method_exists($cacheManager, 'flushCachesInGroupByTag'));
    }

    /**
     * @group unit
     */
    public function testClearCacheForConfiguredTagsByTable()
    {
        $GLOBALS['TCA']['rn_base_test_table']['ctrl']['cacheTags'] = ['first-tag', 'second-tag'];

        $cacheManager = $this->getMock(tx_rnbase_util_Typo3Classes::getCacheManagerClass(), ['flushCachesInGroupByTag']);
        $cacheManager->expects(self::at(0))
            ->method('flushCachesInGroupByTag')
            ->with('pages', 'first-tag');
        $cacheManager->expects(self::at(1))
            ->method('flushCachesInGroupByTag')
            ->with('pages', 'second-tag');

        $dataHandler = $this->getMock('Tx_Rnbase_Hook_DataHandler', ['getCacheManager']);
        $dataHandler->expects(self::once())
            ->method('getCacheManager')
            ->will(self::returnValue($cacheManager));

        $dataHandler->clearCacheForConfiguredTagsByTable(['table' => 'rn_base_test_table']);
    }

    /**
     * @group unit
     */
    public function testClearCacheForConfiguredTagsByTableIfNoneConfiguredInTca()
    {
        $dataHandler = $this->getMock('Tx_Rnbase_Hook_DataHandler', ['getCacheManager']);
        $dataHandler->expects(self::never())
            ->method('getCacheManager');

        $dataHandler->clearCacheForConfiguredTagsByTable(['table' => 'rn_base_test_table']);
    }
}
