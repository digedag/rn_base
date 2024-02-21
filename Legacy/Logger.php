<?php
/**
 * Copyright notice.
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Logger utility.
 *
 * Usage:
 *  Tx_Rnbase_Utility_Logger::warning('ext_key', 'logger warning');
 *  Tx_Rnbase_Utility_Logger::getLogger('ext_key')->emergency('logger emergency');
 *
 * @author Michael Wagner
 */
final class Tx_Rnbase_Utility_Logger
{
    /**
     * The TYPO3 log manager.
     *
     * @return TYPO3\CMS\Core\Log\LogManager
     */
    private static function getLogManager()
    {
        return tx_rnbase::makeInstance(
            'TYPO3\\CMS\\Core\\Log\\LogManager'
        );
    }

    /**
     * The logger for the specivic extension.
     *
     * @param string $extKey
     *
     * @return TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger($extKey)
    {
        return static::getLogManager()->getLogger($extKey);
    }

    /**
     * Shortcut to log an EMERGENCY record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function emergency($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->emergency($message, $data);
    }

    /**
     * Shortcut to log an ALERT record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function alert($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->alert($message, $data);
    }

    /**
     * Shortcut to log a CRITICAL record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function critical($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->critical($message, $data);
    }

    /**
     * Shortcut to log an ERROR record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function error($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->error($message, $data);
    }

    /**
     * Shortcut to log a WARNING record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function warning($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->warning($message, $data);
    }

    /**
     * Shortcut to log a NOTICE record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function notice($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->notice($message, $data);
    }

    /**
     * Shortcut to log an INFORMATION record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function info($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->info($message, $data);
    }

    /**
     * Shortcut to log a DEBUG record.
     *
     * @param string $extkey  extensionkey or logger name
     * @param string $message log message
     * @param array  $data    Additional data to log
     *
     * @return TYPO3\CMS\Core\Log\Logger $this
     */
    public static function debug($extkey, $message, array $data = [])
    {
        return static::getLogger($extkey)->debug($message, $data);
    }
}
