<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013  <hannes.bochmann@das-medienkombinat.de>
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

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_exception_IHandler');

/**
 * @author Hannes Bochmann
 *
 */
class tx_rnbase_exception_Handler implements tx_rnbase_exception_IHandler {

  	/**
	 * Interne Verarbeitung der Exception
	 * 
	 * @param string $actionName
	 * @param Exception $e
	 * @param tx_rnbase_configurations $configurations
	 * 
	 * @return string error message
	 */
	public function handleException($actionName, Exception $e, tx_rnbase_configurations $configurations) {
		if($this->send503HeaderOnException($configurations)) {
			header('HTTP/1.1 503 Service Unavailable');
		}
		tx_rnbase::load('tx_rnbase_util_Logger');
		if(tx_rnbase_util_Logger::isFatalEnabled()) {
			$extKey = $configurations->getExtensionKey();
			$extKey = $extKey ? $extKey : 'rn_base';
			tx_rnbase_util_Logger::fatal('Fatal error for action ' . $actionName, $extKey,
				array('Exception'=> $e, '_GET' => $_GET, '_POST' => $_POST));
		}
		$addr = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'sendEmailOnException');
		if($addr) {
			tx_rnbase_util_Misc::sendErrorMail($addr, $actionName, $e);
		}

		// Now message for FE
		$ret = $this->getErrorMessage($actionName, $e, $configurations);
		return $ret;
	}
 
	/**
	 * 
	 * @param tx_rnbase_configurations $configurations
	 * 
	 * @return void
	 */
	protected function send503HeaderOnException(tx_rnbase_configurations $configurations) {
		//sending a 503 header?
		return ((
				//shall we basically send a 503 header?
				intval(tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'send503HeaderOnException')) && (
					//the plugin has the oppurtunity to prevent sending a 503 header
					//by setting plugin.plugin_name.send503HeaderOnException = 0 in the TS config.
					//if this option is not set we use the ext config
					!array_key_exists('send503HeaderOnException', $configurations->getConfigArray()) ||
					$configurations->get('send503HeaderOnException') != 0
				)
			) ||
			(
				//did the plugin define to send the 503 header
				$configurations->get('send503HeaderOnException') == 1
			)
		);
	}
	
	/**
	 * Build an error message string for frontend
	 * @param string $actionName
	 * @param Exception $e
	 * @param tx_rnbase_Configurations $configurations
	 */
	protected function getErrorMessage($actionName, Exception $e, $configurations) {
		$verbose = intval(tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'verboseMayday')) ?
		'<br /><pre>'.$e->__toString().'</pre>' : '';
	
		$ret = $configurations->getLL('ERROR_'.$e->getCode(), '');
		$ret = $ret ? $ret : $configurations->getLL('ERROR_default', '');
		if($verbose) {
			$ret = '<div><strong>UNCAUGHT EXCEPTION FOR VIEW: ' . $actionName .'</strong>'.$verbose.'</div>';
		}
		elseif(!$ret) {
			$ret = '<div><strong>Leider ist ein unerwarteter Fehler aufgetreten.</strong></div>';
		}
		return $ret;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/exception/class.tx_rnbase_exception_Handler.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/exception/class.tx_rnbase_exception_Handler.php']);
}