<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2011 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_action_ICacheHandler');
tx_rnbase::load('tx_rnbase_cache_Manager');




/**
 * A default CacheHandler.
 * This cache has the same rules as the default TYPO3 page cache. The only difference is seperate
 * expire time for the plugin. It can be set by Typoscript:
 * plugints._caching.expires = 60 # time in seconds
 * The plugin will also expire if the page expires!
 */
class tx_rnbase_action_CacheHandlerDefault implements tx_rnbase_action_ICacheHandler {
	private $cacheConfId;
	/** @var tx_rnbase_configurations $configurations */
	private $configurations;
	private $cacheName;

	public function __construct($configurations, $confId) {
		$this->configurations = $configurations;
		$this->cacheConfId = $confId;
		$this->cacheName = $this->getConfigValue('name', 'rnbase');
	}
	protected function getConfigValue($confId, $altValue='') {
		$ret = $this->getConfigurations()->get($this->getCacheConfId() . $confId);
		return isset($ret) ? $ret : $altValue;
	}
	protected function getCacheName() {
		return $this->cacheName;
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
	protected function generateKey($plugin) {
		// TODO: parameter bzw. cHash einbeziehen
		// Der Key muss die Seite, das Plugin und den ausgewählten view eindeutig identifizieren
		// Der View kann über die confid ermittelt werden
		// Das Plugin eigentlich über 
		$key = tx_rnbase_util_TYPO3::getTSFE()->getHash().'_';
		$key .= md5($this->getConfigurations()->getPluginId().$this->getCacheConfId());
		return 'ac_p'. $key;
	}

	protected function getTimeout() {
		$timeout = $this->getConfigValue('expire');
		return $timeout ? $timeout : 60; // default timeout 1 minute
	}
	/**
	 * Save output data to cache
	 * @param string $output
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function setOutput($output, $configurations, $confId) {
		$cache = tx_rnbase_cache_Manager::getCache($this->getCacheName());
		$cache->set($this->generateKey(), $output, $this->getTimeout());
	}

	/**
	 * Get output data from cache
	 * @param tx_rnbase_action $plugin
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return string the output string
	 */
	public function getOutput($plugin, $configurations, $confId) {
		$cache = tx_rnbase_cache_Manager::getCache($this->getCacheName());
		$key = $this->generateKey($plugin);
		$out = $cache->get($key);

//t3lib_div::debug(array($configurations->getPluginId(), $configurations->cObj->data), $confId.' - class.tx_rnbase_action_CacheHandlerDefault.php Line: ' . __LINE__); // TODO: remove me
//t3lib_div::debug($out, $key.' - From CACHE class.tx_rnbase_action_CacheHandlerDefault.php Line: ' . __LINE__); // TODO: remove me
		
		return $out;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerDefault.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_CacheHandlerDefault.php']);
}


