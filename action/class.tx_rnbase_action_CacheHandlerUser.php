<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
 *  Contact: rene@system25.de
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

tx_rnbase::load('tx_rnbase_action_CacheHandlerDefault');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_cache_Manager');




/**
 * Caching handler that saves data for feusers. For unregistered users the handler uses PHP-Session-ID 
 * for identification.
 * 
 */
class tx_rnbase_action_CacheHandlerUser extends tx_rnbase_action_CacheHandlerDefault {
	private $sessionId = FALSE;
	public function __construct($configurations, $confId) {
		parent::__construct($configurations, $confId);
		session_start();
		$this->sessionId = session_id();
		
	}

	/**
	 * Generate a key used to store data to cache.
	 * @return string
	 */
	protected function generateKey($plugin) {
		return parent::generateKey().'_usr'. $this->sessionId;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerUser.php']);
}


