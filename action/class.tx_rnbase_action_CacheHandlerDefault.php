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

tx_rnbase::load('tx_rnbase_action_ICacheHandler');
tx_rnbase::load('tx_rnbase_cache_Manager');




/**
 * A default CacheHandler.
 * 
 */
class tx_rnbase_action_CacheHandlerDefault implements tx_rnbase_action_ICacheHandler {
	private $cacheConfId;
	private $configurations;
	
	public function __construct($configurations, $confId) {
		$this->configurations = $configurations;
		$this->cacheConfId = $confId;
	}
	protected function getConfigValue($confId) {
		return $this->getConfigurations()->get($this->getCacheConfId() . $confId);
	}
	/**
	 * @return tx_rnbase_configurations
	 */
	protected function getConfigurations() {
		return $this->configurations;
	}
	/**
	 * @return string
	 */
	protected function getCacheConfId() {
		return $this->cacheConfId;
	}
	/**
	 * Generate a key used to store data to cache.
	 * @return string
	 */
	protected function generateKey() {
		// TODO: parameter bzw. cHash einbeziehen
		return 'ac_p'. $this->getConfigurations()->getPluginId();
	}

	protected function getTimeout() {
		$timeout = $this->getConfigValue('timeout');
		return $timeout ? $timeout : 60; // default timeout 1 minute
	}
	/**
	 * Save output data to cache
	 * @param string $output
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function setOutput($output, $configurations, $confId) {
		$cache = tx_rnbase_cache_Manager::getCache('rnbase');
		$cache->set($this->generateKey(), $output, 10);
	}

	/**
	 * Get output data from cache
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return string the output string
	 */
	public function getOutput($configurations, $confId) {
		$cache = tx_rnbase_cache_Manager::getCache('rnbase');
		$out = $cache->get($this->generateKey());
		return $out;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerDefault.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerDefault.php']);
}

?>
