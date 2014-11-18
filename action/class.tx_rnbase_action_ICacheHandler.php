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

/**
 * Interface for action based fe-caching handler.
 * 
 */
interface tx_rnbase_action_ICacheHandler {

	/**
	 * Save output data to cache
	 * @param string $output
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	public function setOutput($output, $configurations, $confId);

	/**
	 * Get output data from cache
	 * @param tx_rnbase_action_BaseIOC $plugin
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return string the output string
	 */
	public function getOutput($plugin, $configurations, $confId);

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_ICacheHandler.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_ICacheHandler.php']);
}


