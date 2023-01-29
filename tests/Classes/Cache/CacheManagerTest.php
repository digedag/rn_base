<?php

namespace Sys25\RnBase\Cache;

use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2023 Rene Nitzsche (rene@system25.de)
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

/**
 * @group unit
 */
class CacheManagerTest extends BaseTestCase
{
    public function testCacheManager()
    {
        $cache = CacheManager::getCache('__rnbaseMgrCache__');
        $this->assertTrue(is_object($cache), 'No Cache instanciated');
    }

    public function testTYPO3Cache()
    {
        $cache = self::createTYPO3Cache('__rnbaseTestTYPO3Cache__');

        $this->assertTrue(is_object($cache), 'Cache not instanciated');
        if (TYPO3::isTYPO104OrHigher()) {
            // Ab T3 10 kann man Caches nicht mehr programmatisch konfigurieren.
            return;
        }
        $cache->set('key1', ['id' => '100']);
        $arr = $cache->get('key1');
        $this->assertTrue(1 == count($arr), 'Array has wrong size');
        $this->assertEquals($arr['id'], '100', 'Array content is wrong');
    }

    /**
     * Returns the cache.
     *
     * @param string $name
     *
     * @return TYPO3Cache62
     */
    private static function createTYPO3Cache($name)
    {
        return tx_rnbase::makeInstance(TYPO3Cache62::class, $name);
    }
}
