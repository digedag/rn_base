<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Rene Nitzsche
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

/**
 * This is a wrapper for the internal TYPO3-Cache-API since 4.3.
 * The cache is configured via TYPO3_CONF_VARS as usual:
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['backend'],
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['options']
 */
class tx_rnbase_cache_TYPO3Cache60 implements tx_rnbase_cache_ICache
{
    private $cache; // The cache instance
    private static $emptyArray = array();
    public function __construct($cacheName)
    {
        $this->checkCacheConfiguration($cacheName);
        $cache = $this->getT3CacheManager()->getCache($cacheName);
        if (!is_object($cache)) {
            throw new Exception('Error creating cache with name: ' . $cacheName);
        }
        $this->setCache($cache);
    }

    /**
     * @return \TYPO3\CMS\Core\Cache\CacheManager
     */
    private function getT3CacheManager()
    {
        return $GLOBALS['typo3CacheManager'];
    }
    private function checkCacheConfiguration($cacheName)
    {
        if (!array_key_exists($cacheName, $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'])) {
            // Der Cache ist nicht konfiguriert.
            // Wir konfigurieren einen mit Defaults
            $defaultCache[$cacheName] = array(
                'backend' => 't3lib_cache_backend_TransientMemoryBackend',
                'options' => array(
                )
            );
            $this->getT3CacheManager()->setCacheConfigurations($defaultCache);
        }
    }

    /**
     * Retrieve a value from cache
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_TYPO3Cache.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_TYPO3Cache.php']);
}
