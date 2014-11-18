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

/**
 * This is a generic interface for a Cache.
 */
interface tx_rnbase_cache_ICache {
	/**
	 * Retrieve a value from cache
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key);
	/**
	 * Check whether or not a cache entry with the specified identifier exists.
	 *
	 * @return boolean
	 */
	public function has($key);
	/**
	 * Put a value to the cache
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime
	 */
	public function set($key, $value, $lifetime = NULL);
	/**
	 * Remove a value from the cache
	 *
	 * @param String $key
	 */
	public function remove($key);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_ICache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_ICache.php']);
}
