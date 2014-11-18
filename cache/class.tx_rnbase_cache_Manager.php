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
tx_rnbase::load('tx_rnbase_util_TYPO3');

/**
 * This class provides access to caches.
 */
class tx_rnbase_cache_Manager {
	private static $caches = array();

	/**
	 * Liefert einen Cache
	 *
	 * @param String $name
	 * @return tx_rnbase_cache_ICache
	 */
	public static function getCache($name) {
		// Es muss ein passender Cache erstellt werden
		if(!array_key_exists($name, self::$caches)) 
			self::$caches[$name] = self::getCacheImpl($name);
		return self::$caches[$name];
	}
	/**
	 * Returns the cache instance
	 *
	 * @param string $name
	 * @return tx_rnbase_cache_NoCache
	 */
	private static function getCacheImpl($name) {
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher())
			return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache60', $name);
		elseif(tx_rnbase_util_TYPO3::isTYPO46OrHigher())
			return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache46', $name);
		elseif(tx_rnbase_util_TYPO3::isTYPO43OrHigher())
			return tx_rnbase::makeInstance('tx_rnbase_cache_TYPO3Cache', $name);
		return tx_rnbase::makeInstance('tx_rnbase_cache_NoCache', $name);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_Manager.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/cache/class.tx_rnbase_cache_Manager.php']);
}
