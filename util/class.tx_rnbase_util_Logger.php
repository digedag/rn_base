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
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

/**
 * Default logger class.
 */
class tx_rnbase_util_Logger {
	private static $minLog = FALSE;
	const LOGLEVEL_DEBUG = -1;
	const LOGLEVEL_INFO = 0;
	const LOGLEVEL_NOTICE = 1;
	const LOGLEVEL_WARN = 2;
	const LOGLEVEL_FATAL = 3;

	/**
	 * Log a debug message
	 * @param string $msg
	 * @param string $extKey
	 * @param mixed $dataVar
	 */
	public static function debug($msg, $extKey, $dataVar=FALSE) {
		self::devLog($msg, $extKey, -1, $dataVar);
	}
	/**
	 * Log a notice
	 * @param string $msg
	 * @param string $extKey
	 * @param mixed $dataVar
	 */
	public static function info($msg, $extKey, $dataVar=FALSE) {
		self::devLog($msg, $extKey, 0, $dataVar);
	}
	/**
	 * Log a notice
	 * @param string $msg
	 * @param string $extKey
	 * @param mixed $dataVar
	 */
	public static function notice($msg, $extKey, $dataVar=FALSE) {
		self::devLog($msg, $extKey, 1, $dataVar);
	}
	/**
	 * Log a warning
	 * @param string $msg
	 * @param string $extKey
	 * @param mixed $dataVar
	 */
	public static function warn($msg, $extKey, $dataVar=FALSE) {
		self::devLog($msg, $extKey, 2, $dataVar);
	}
	/**
	 * Log a fatal error
	 * @param string $msg
	 * @param string $extKey
	 * @param mixed $dataVar
	 */
	public static function fatal($msg, $extKey, $dataVar=FALSE) {
		self::devLog($msg, $extKey, 3, $dataVar);
	}

	/**
	 * Whether or not log level notice is enabled.
	 * This works only in conjunction with extension devlog
	 * @return boolean
	 */
	public static function isDebugEnabled() {
		return self::isLogLevel(-1);
	}
	/**
	 * Whether or not log level notice is enabled.
	 * This works only in conjunction with extension devlog
	 * @return boolean
	 */
	public static function isInfoEnabled() {
		return self::isLogLevel(0);
	}
	/**
	 * Whether or not log level notice is enabled.
	 * This works only in conjunction with extension devlog
	 * @return boolean
	 */
	public static function isNoticeEnabled() {
		return self::isLogLevel(1);
	}
	/**
	 * Whether or not log level warning is enabled.
	 * This works only in conjunction with extension devlog
	 * @return boolean
	 */
	public static function isWarningEnabled() {
		return self::isLogLevel(2);
	}
	/**
	 * Whether or not log level fatal is enabled.
	 * This works only in conjunction with extension devlog
	 * @return boolean
	 */
	public static function isFatalEnabled() {
		return self::isLogLevel(3);
	}

	/**
	 * @param int $level
	 * @return boolean
	 */
	private static function isLogLevel($level) {
		if(self::$minLog === FALSE) {
			if(tx_rnbase_util_Extensions::isLoaded('devlog')) {
				$minLog = tx_rnbase_configurations::getExtensionCfgValue('devlog', 'minLogLevel');
				self::$minLog = $minLog !== FALSE ? $minLog : -1;
			}
		}
		$isEnabled = $level >= self::$minLog;
		return $isEnabled;
	}

	/**
	 * Wrapper method for t3lib_div::devLog() or \TYPO3\CMS\Core\Utility\GeneralUtility::devLog()
	 *
	 * @param string $msg Message (in english).
	 * @param string $extKey Extension key (from which extension you are calling the log)
	 * @param integer $severity Severity: 0 is info, 1 is notice, 2 is warning, 3 is fatal error, -1 is "OK" message
	 * @param mixed $dataVar Additional data you want to pass to the logger.
	 * @return void
	 */
	static public function devLog($msg, $extKey, $severity = 0, $dataVar = FALSE) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		$utility::devLog($msg, $extKey, $severity, $dataVar);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Logger.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Logger.php']);
}