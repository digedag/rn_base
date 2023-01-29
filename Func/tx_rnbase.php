<?php

use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Typo3Classes;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2022 Rene Nitzsche
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

class tx_rnbase
{
    private static $loadedClasses = [];

    /**
     * Load the class file.
     *
     * Load the file for a given classname 'tx_key_path_file'
     * or a given part of the filepath that contains enough information to find the class.
     *
     * This method is taken from tx_div. There is an additional cache to avoid double calls.
     * This can save a lot of time.
     *
     * TODO: lookup for classes folder
     *
     * @param string $classNameOrPathInformation classname or path matching for the type of loader
     *
     * @return bool true if successful, false otherwise
     *
     * @deprecated version
     */
    public static function load($classNameOrPathInformation)
    {
        return true;
    }

    /**
     * Load a t3 class and make an instance.
     * Usage:
     * $obj = tx_rnbase::makeInstance('tx_ext_myclass');
     * or with parameters:
     * $obj = tx_rnbase::makeInstance('tx_ext_myclass', 'arg1', 'arg2', ...);.
     *
     * This works also for TYPO3 4.2 and lower.
     *
     * Returns ux_ extension class if any by make use of t3lib_div::makeInstance
     *
     * @template T of object
     *
     * @param string|class-string<T> $className name of the class to instantiate, must not be empty and not start with a backslash
     * @param mixed optional more parameters for constructor
     *
     * @return T the created instance
     *
     * @throws \InvalidArgumentException if $className is empty or starts with a backslash
     *
     * @see         load()
     */
    public static function makeInstance($class)
    {
        $ret = false;
        if (self::load($class)) {
            $utility = Typo3Classes::getGeneralUtilityClass();
            if (func_num_args() > 1) {
                // Das ist ein Konstruktor Aufruf mit Parametern
                // phpcs:disable -- $class has never changed
                $args = func_get_args();
                $ret = call_user_func_array([$utility, 'makeInstance'], $args);
            } else {
                $ret = $utility::makeInstance($class);
            }
        }

        return $ret;
    }

    /**
     * Find the best service and check if it works.
     * Returns object of the service class.
     *
     * @param string $serviceType        type of service (service key)
     * @param string $serviceSubType     Sub type like file extensions or similar. Defined by the service.
     * @param mixed  $excludeServiceKeys List of service keys which should be excluded in the search for a service. Array or comma list.
     *
     * @return object the service object or an array with error info's
     */
    public static function makeInstanceService($serviceType, $serviceSubType = '', $excludeServiceKeys = [])
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::makeInstanceService($serviceType, $serviceSubType, $excludeServiceKeys);
    }

    /**
     * Shortcut for Sys25\RnBase\Utility\Debug::debug().
     */
    public static function debug($var = '', $header = '', $group = 'Debug')
    {
        Debug::debug($var, $header, $group);
    }
}
