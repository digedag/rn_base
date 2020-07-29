<?php

namespace Sys25\RnBase\Utility;

use Psr\Log\LogLevel;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2020 Rene Nitzsche
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
 * Default logger class.
 */
class Logger
{
    private static $minLog = false;

    const LOGLEVEL_DEBUG = -1;

    const LOGLEVEL_INFO = 0;

    const LOGLEVEL_NOTICE = 1;

    const LOGLEVEL_WARN = 2;

    const LOGLEVEL_FATAL = 3;

    /**
     * Log a debug message.
     *
     * @param string $msg
     * @param string $extKey
     * @param mixed  $dataVar
     */
    public static function debug($msg, $extKey, $dataVar = false)
    {
        self::devLog($msg, $extKey, self::LOGLEVEL_DEBUG, $dataVar);
    }

    /**
     * Log a notice.
     *
     * @param string $msg
     * @param string $extKey
     * @param mixed  $dataVar
     */
    public static function info($msg, $extKey, $dataVar = false)
    {
        self::devLog($msg, $extKey, self::LOGLEVEL_INFO, $dataVar);
    }

    /**
     * Log a notice.
     *
     * @param string $msg
     * @param string $extKey
     * @param mixed  $dataVar
     */
    public static function notice($msg, $extKey, $dataVar = false)
    {
        self::devLog($msg, $extKey, self::LOGLEVEL_NOTICE, $dataVar);
    }

    /**
     * Log a warning.
     *
     * @param string $msg
     * @param string $extKey
     * @param mixed  $dataVar
     */
    public static function warn($msg, $extKey, $dataVar = false)
    {
        self::devLog($msg, $extKey, self::LOGLEVEL_WARN, $dataVar);
    }

    /**
     * Log a fatal error.
     *
     * @param string $msg
     * @param string $extKey
     * @param mixed  $dataVar
     */
    public static function fatal($msg, $extKey, $dataVar = false)
    {
        self::devLog($msg, $extKey, self::LOGLEVEL_FATAL, $dataVar);
    }

    /**
     * Whether or not log level notice is enabled.
     * This works only in conjunction with extension devlog.
     *
     * @return bool
     */
    public static function isDebugEnabled()
    {
        return self::isLogLevel(-1);
    }

    /**
     * Whether or not log level notice is enabled.
     * This works only in conjunction with extension devlog.
     *
     * @return bool
     */
    public static function isInfoEnabled()
    {
        return self::isLogLevel(0);
    }

    /**
     * Whether or not log level notice is enabled.
     * This works only in conjunction with extension devlog.
     *
     * @return bool
     */
    public static function isNoticeEnabled()
    {
        return self::isLogLevel(1);
    }

    /**
     * Whether or not log level warning is enabled.
     * This works only in conjunction with extension devlog.
     *
     * @return bool
     */
    public static function isWarningEnabled()
    {
        return self::isLogLevel(2);
    }

    /**
     * Whether or not log level fatal is enabled.
     * This works only in conjunction with extension devlog.
     *
     * @return bool
     */
    public static function isFatalEnabled()
    {
        return self::isLogLevel(self::LOGLEVEL_FATAL);
    }

    /**
     * @param int $level
     *
     * @return bool
     */
    private static function isLogLevel($level)
    {
        return true;
    }

    /**
     * Wrapper method for t3lib_div::devLog() or \TYPO3\CMS\Core\Utility\GeneralUtility::devLog().
     *
     * @param string $msg      message (in english)
     * @param string $extKey   Extension key (from which extension you are calling the log)
     * @param int    $severity Severity: 0 is info, 1 is notice, 2 is warning, 3 is fatal error, -1 is "OK" message
     * @param mixed  $dataVar  additional data you want to pass to the logger
     */
    public static function devLog($msg, $extKey, $severity = 0, $dataVar = false)
    {
        $logger = static::getLogger($extKey);
        $methods = [
            self::LOGLEVEL_DEBUG => LogLevel::DEBUG,
            self::LOGLEVEL_INFO => LogLevel::INFO,
            self::LOGLEVEL_NOTICE => LogLevel::NOTICE,
            self::LOGLEVEL_WARN => LogLevel::WARNING,
            self::LOGLEVEL_FATAL => LogLevel::ERROR,
        ];
        $level = \array_key_exists($severity, $methods) ? $methods[$severity] : LogLevel::INFO;
        $logger->log($level, $msg, \is_array($dataVar) ? $dataVar : [$dataVar]);
    }

    /**
     * The logger for the specific extension.
     *
     * @param string $extKey
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger($extKey)
    {
        /* @var $logManager \TYPO3\CMS\Core\Log\LogManager */
        $logManager = \tx_rnbase::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class);

        return $logManager->getLogger($extKey);
    }
}
