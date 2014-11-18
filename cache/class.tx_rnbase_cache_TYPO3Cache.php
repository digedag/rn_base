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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_cache_ICache');

/**
 * This is a wrapper for the internal TYPO3-Cache-API since 4.3.
 * The cache is configured via TYPO3_CONF_VARS as usual: 
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['backend'],
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['options']
 * 
 */
class tx_rnbase_cache_TYPO3Cache implements tx_rnbase_cache_ICache {
	private $cache; // The cache instance
	private static $emptyArray = array();
	public function __construct($cacheName) {
		try {
			$this->fillBackendParameters($cacheName, $backendName, $backendOptions);

			$factory = self::initTYPO3Cache(TRUE);
			$cache = $factory->create(
				$cacheName,
				't3lib_cache_frontend_VariableFrontend',
				$backendName,
				$backendOptions
			);
		} catch(t3lib_cache_exception_DuplicateIdentifier $e) {
				// do nothing, a cache_pages cache already exists
				// This should never happen...
		}
		if(!is_object($cache)) throw new Exception('Error creating cache with name: ' . $cacheName);
		$this->setCache($cache);
	}
	private function fillBackendParameters($cacheName, &$backendName, &$backendOptions) {

		if(!array_key_exists($cacheName, $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'])) {
			// Der Cache ist nicht konfiguriert. Wir verwenden Defaults
			$backendName =  't3lib_cache_backend_TransientMemoryBackend';
			// Das Transient ist in der 4.3.0 nicht konfiguriert
			if(!array_key_exists('t3lib_cache_backend_TransientMemoryBackend', $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheBackends'])) {
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheBackends']['t3lib_cache_backend_TransientMemoryBackend'] = 't3lib/cache/backend/class.t3lib_cache_backend_transientmemorybackend.php:t3lib_cache_backend_TransientMemoryBackend';
			}
//			$backendName =  't3lib_cache_backend_NullBackend';
		}
		else {
			$backendName = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['backend'];
			$backendOptions = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['options'];
		}
		$backendOptions = $backendOptions ? $backendOptions : array();
	}
	/**
	 * Initialisiert den TYPO3 Cache und liefert die Factory zurück
	 *
	 * @param boolean $local
	 * @return t3lib_cache_Factory
	 */
	public static function initTYPO3Cache($local = TRUE) {
		if(!$local && is_object($GLOBALS['typo3CacheFactory'])) 
			return $GLOBALS['typo3CacheFactory']; // Globale Factory ist schon gesetzt

		$manager = t3lib_div::makeInstance('t3lib_cache_Manager');
		$factory = t3lib_div::makeInstance('t3lib_cache_Factory');
		$factory->setCacheManager($manager);
		if(!$local) {
			$GLOBALS['typo3CacheFactory'] = $factory;
			$GLOBALS['typo3CacheManager'] = $manager;
		}
		return $factory;
	}
	/**
	 * Retrieve a value from cache
	 *
	 * @param string $key
	 */
	public function get($key) {
		return $this->getCache()->get($key);
	}
	public function has($key) {
		return $this->getCache()->has($key);
	}
	public function set($key, $value, $lifetime = NULL) {
		$this->getCache()->set($key, $value, self::$emptyArray, $lifetime);
	}
	public function remove($key) {
		$this->getCache()->remove($key);
	}
	/**
	 * Set the TYPO3 cache instance.
	 *
	 * @param t3lib_cache_frontend_Frontend $cache
	 */
	private function setCache(t3lib_cache_frontend_Frontend $cache) {
		$this->cache = $cache;
	}
	/**
	 * Set the TYPO3 cache instance.
	 *
	 * @return t3lib_cache_frontend_Frontend
	 */
	private function getCache() {
		return $this->cache;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_TYPO3Cache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_TYPO3Cache.php']);
}
