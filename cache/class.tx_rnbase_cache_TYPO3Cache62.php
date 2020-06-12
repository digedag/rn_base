<?php

use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

tx_rnbase::load('tx_rnbase_cache_ICache');

class tx_rnbase_cache_TYPO3Cache62 implements tx_rnbase_cache_ICache
{
    private $cache; // The cache instance

    private static $emptyArray = [];

    public function __construct($cacheName)
    {
        $cache = $this->checkCacheConfiguration($cacheName);
        if (!is_object($cache)) {
            throw new Exception('Error creating cache with name: '.$cacheName);
        }
        $this->setCache($cache);
    }

    /**
     * @return \TYPO3\CMS\Core\Cache\CacheManager
     */
    private function getT3CacheManager()
    {
        // Usage of $GLOBALS[\'typo3CacheManager\'] and $GLOBALS[\'typo3CacheFactory\'] are deprecated since 6.2
        // will be removed in two versions. Use \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance
        return tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
    }

    /**
     *
     * @param string $cacheName
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    private function checkCacheConfiguration($cacheName)
    {
        if (!array_key_exists($cacheName, $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'])) {
            // Der Cache ist nicht konfiguriert.
            // Wir konfigurieren einen mit Defaults
            // Das funktioniert aber nur noch bis zur 9.5
            // https://forge.typo3.org/issues/91641
            if (!TYPO3::isTYPO104OrHigher()) {
                $defaultCache = [$cacheName => [
                    'backend' => 'TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend',
                    'options' => [
                    ],
                ]];
                $this->getT3CacheManager()->setCacheConfigurations($defaultCache);
            }
            else {
                // Wir setzen einfach einen Null-Cache
                return new NullFrontend($cacheName);
            }
        }
        return $this->getT3CacheManager()->getCache($cacheName);
    }

    /**
     * Retrieve a value from cache.
     *
     * @param string $key
     */
    public function get($key)
    {
        return $this->getCache()->get($key);
    }

    public function has($key)
    {
        return $this->getCache()->has($key);
    }

    public function set($key, $value, $lifetime = null)
    {
        $this->getCache()->set($key, $value, self::$emptyArray, $lifetime);
    }

    public function remove($key)
    {
        $this->getCache()->remove($key);
    }

    /**
     * Set the TYPO3 cache instance.
     *
     * @param \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache
     */
    private function setCache(\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Set the TYPO3 cache instance.
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    private function getCache()
    {
        return $this->cache;
    }
}
