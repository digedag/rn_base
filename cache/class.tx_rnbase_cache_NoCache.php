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
 * This is a dummy cache. It will not hold any data.
 */
class tx_rnbase_cache_NoCache implements tx_rnbase_cache_ICache {
	public function __construct($cacheName) {
	}
	/**
	 * Retrieve a value from cache
	 *
	 * @param string $key
	 * @return NULL
	 */
	public function get($key) {
		return NULL;
	}
	public function has($key) {
		return FALSE;
	}
	public function set($key, $value, $lifetime = NULL) {
		
	}
	public function remove($key) {
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_NoCache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_NoCache.php']);
}
