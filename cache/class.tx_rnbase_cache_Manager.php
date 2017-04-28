<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_TYPO3');

/**
 * This class provides access to caches.
 */
class tx_rnbase_cache_Manager
{
    private static $caches = array();
    const CACHE_FRONTEND_VARIABLE = 'VariableFrontend';
    const CACHE_FRONTEND_STRING = 'StringFrontend';
    const CACHE_FRONTEND_PHP = 'PhpFrontend';
    const CACHE_BACKEND_T3DATABASE = 'T3Database';
    const CACHE_BACKEND_MEMCACHED = 'Memcached';
    const CACHE_BACKEND_FILE = 'File';
    const CACHE_BACKEND_REDIS = 'Redis';
    const CACHE_BACKEND_APC = 'Apc';
    const CACHE_BACKEND_PDO = 'Pdo';
    const CACHE_BACKEND_TRANSIENTMEMORY = 'TransientMemory';
    const CACHE_BACKEND_NULL = 'Null';

    private static $aliases = array(
        self::CACHE_FRONTEND_VARIABLE => array('TYPO3\CMS\Core\Cache\Frontend\VariableFrontend','t3lib_cache_frontend_VariableFrontend'),
        self::CACHE_FRONTEND_STRING => array('TYPO3\CMS\Core\Cache\Frontend\StringFrontend','t3lib_cache_frontend_StringFrontend'),
        self::CACHE_FRONTEND_PHP => array('TYPO3\CMS\Core\Cache\Frontend\PhpFrontend','t3lib_cache_frontend_PhpFrontend'),
        self::CACHE_BACKEND_T3DATABASE => array('TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend','t3lib_cache_backend_DbBackend'),
        self::CACHE_BACKEND_MEMCACHED => array('TYPO3\CMS\Core\Cache\Backend\MemcachedBackend','t3lib_cache_backend_MemcachedBackend'),
        self::CACHE_BACKEND_FILE => array('TYPO3\CMS\Core\Cache\Backend\FileBackend','t3lib_cache_backend_FileBackend'),
        self::CACHE_BACKEND_REDIS => array('TYPO3\CMS\Core\Cache\Backend\RedisBackend','t3lib_cache_backend_RedisBackend'),
        self::CACHE_BACKEND_APC => array('TYPO3\CMS\Core\Cache\Backend\ApcBackend','t3lib_cache_backend_ApcBackend'),
        self::CACHE_BACKEND_PDO => array('TYPO3\CMS\Core\Cache\Backend\PdoBackend','t3lib_cache_backend_PdoBackend'),
        self::CACHE_BACKEND_TRANSIENTMEMORY => array('TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend','t3lib_cache_backend_TransientMemoryBackend'),
        self::CACHE_BACKEND_NULL => array('TYPO3\CMS\Core\Cache\Backend\NullBackend','t3lib_cache_backend_NullBackend'),
    );

    /**
     * Register a TYPO3 cache
     * @param string $name
     * @param string $frontendKey see constants
     * @param string $backendKey see constants
     * @param array $options
     */
    public static function registerCache($name, $frontendKey, $backendKey, $options = array())
    {
        $offset = tx_rnbase_util_TYPO3::isTYPO60OrHigher() ? 0 : 1;
        $frontend = array_key_exists($frontendKey, self::$aliases) ? self::$aliases[$frontendKey][$offset] : $frontendKey;
        $backend = array_key_exists($backendKey, self::$aliases) ? self::$aliases[$backendKey][$offset] : $backendKey;

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$name] = array(
                'frontend' => $frontend,
                'backend' => $backend,
                'options' => $options
        );
    }
    /**
     * Liefert einen Cache
     *
     * @param string $name
     * @return tx_rnbase_cache_ICache
     */
    public static function getCache($name)
    {
        // Es muss ein passender Cache erstellt werden
        if (!array_key_exists($name, self::$caches)) {
            self::$caches[$name] = self::getCacheImpl($name);
        }

        return self::$caches[$name];
    }
    /**
     * Returns the cache instance
     *
     * @param string $name
     * @return tx_rnbase_cache_ICache
     */
    private static function getCacheImpl($name)
    {
        if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache62', $name);
        } elseif (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
            return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache60', $name);
        } elseif (tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
            return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache46', $name);
        }

        return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache', $name);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_Manager.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_Manager.php']);
}
