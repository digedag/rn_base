<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2015 Rene Nitzsche
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
 * Replacement for tx_div
 * Some method come from TYPO3 Extension lib/div from author Elmar Hinz <elmar.hinz@team-red.net>
 */
class tx_rnbase
{
    private static $loadedClasses = array();
    /**
     * Load the class file
     *
     * Load the file for a given classname 'tx_key_path_file'
     * or a given part of the filepath that contains enough information to find the class.
     *
     * This method is taken from tx_div. There is an additional cache to avoid double calls.
     * This can save a lot of time.
     *
     * TODO: lookup for classes folder
     *
     * @param   string $classNameOrPathInformation classname or path matching for the type of loader
     * @return  bool     true if successful, false otherwise
     * @see     tx_lib_t3Loader
     * @see     tx_lib_pearLoader
     */
    public static function load($classNameOrPathInformation)
    {
        if (array_key_exists($classNameOrPathInformation, self::$loadedClasses)) {
            return self::$loadedClasses[$classNameOrPathInformation];
        }

        if (self::loadT3($classNameOrPathInformation)) {
            self::$loadedClasses[$classNameOrPathInformation] = true;

            return true;
        }

        self::$loadedClasses[$classNameOrPathInformation] = false;

        return false;
    }

    /**
     * Load a t3 class and make an instance.
     * Usage:
     * $obj = tx_rnbase::makeInstance('tx_ext_myclass');
     * or with parameters:
     * $obj = tx_rnbase::makeInstance('tx_ext_myclass', 'arg1', 'arg2', ...);
     *
     * This works also for TYPO3 4.2 and lower.
     *
     * Returns ux_ extension class if any by make use of t3lib_div::makeInstance
     *
     * @param   string      classname
     * @param   mixed optional more parameters for constructor
     * @return  object|Exception    instance of the class or FALSE if it fails
     * @see         t3lib_div::makeInstance
     * @see         load()
     */
    public static function makeInstance($class)
    {
        $ret = false;
        if (self::load($class)) {
            self::load('tx_rnbase_util_Typo3Classes');
            $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
            if (func_num_args() > 1) {
                // Das ist ein Konstruktor Aufruf mit Parametern
                $args = func_get_args();
                $ret = call_user_func_array(array($utility, 'makeInstance'), $args);
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
     * @param string $serviceType Type of service (service key).
     * @param string $serviceSubType Sub type like file extensions or similar. Defined by the service.
     * @param mixed $excludeServiceKeys List of service keys which should be excluded in the search for a service. Array or comma list.
     * @return object The service object or an array with error info's.
     */
    public static function makeInstanceService($serviceType, $serviceSubType = '', $excludeServiceKeys = array())
    {
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::makeInstanceService($serviceType, $serviceSubType, $excludeServiceKeys);
    }

    /**
     * Load the class file, return the classname or the ux_classname
     *
     * This is an extension to t3lib_div::makeInstanceClassName. The advantage
     * is that it tries to autoload the file. In combination with the shorter
     * notation it simplyfies the finding of the classname.
     *
     * @param   string      classname
     * @return  string      classname or ux_classsname (maybe  service classname)
     * @see     tx_div::makeInstance
     * @see     tx_lib_t3Loader
     * @see     tx_lib_pearLoader
     * @deprecated use makeInstance() with optional parameters for constructor
     */
    public static function makeInstanceClassName($inputName)
    {
        $outputName = false;
        if (!$outputName) {
            $outputName = self::makeInstanceClassNameT3($inputName);
        }
        if (!$outputName && tx_rnbase_util_Extensions::isLoaded('lib')) {
            require_once(tx_rnbase_util_Extensions::extPath('lib') . 'class.tx_lib_pearLoader.php');
            $outputName = tx_lib_pearLoader::makeInstanceClassName($inputName);
        }

        return $outputName;
    }

    /**
     * Load a t3 class
     *
     * Loads from extension directories ext, sysext, etc.
     *
     * Loading: '.../ext/key/subs/prefix.class.suffix
     *
     * The files are searched on two levels:
     *
     * <pre>
     * tx_key           '.../ext/key/class.tx_key.php'
     * tx_key_file      '.../ext/key/class.tx_key_file.php'
     * tx_key_file      '.../ext/key/file/class.tx_key_file.php'
     * tx_key_subs_file '.../ext/key/subs/class.tx_key_subs_file.php'
     * tx_key_subs_file '.../ext/key/subs/file/class.tx_key_subs_file.php'
     * </pre>
     *
     * @param   string      classname or speaking part of path
     * @param   string      extension key that varies from classname
     * @param   string      prefix of classname
     * @param   string      ending of classname
     * @return  bool     TRUE if class was loaded
     */
    private static function loadT3($minimalInformation, $alternativeKey = '', $prefix = 'class.', $suffix = '.php')
    {
        // Class still exists
        if (class_exists($minimalInformation) || interface_exists($minimalInformation)) {
            return true;
        }

        $path = self::_findT3($minimalInformation, $alternativeKey, $prefix, $suffix);

        if ($path) {
            if (tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
                // Needed for require_once, only fallback, should do the autoloading!
                global $TYPO3_CONF_VARS;
                require_once $path;
            } elseif (class_exists('\TYPO3\CMS\Core\Utility\GeneralUtility')) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::requireOnce($path);
            } else {
                t3lib_div::requireOnce($path);
            }
        }

        return class_exists($minimalInformation) || interface_exists($minimalInformation);
    }

    /**
     * Load a t3 class and make an instance
     *
     * Returns ux_ extension classname if any by, making use of t3lib_div::makeInstanceClassName
     *
     * @param   string      classname
     * @param   string      extension key that varies from classnames
     * @param   string      prefix of classname
     * @param   string      ending of classname
     * @return  string      classname or ux_ classname
     * @see         t3lib_div::makeInstanceClassName
     * @see         load()
     */
    private static function makeInstanceClassNameT3($class, $alternativeKey = '', $prefix = 'class.', $suffix = '.php')
    {
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return (self::loadT3($class, $alternativeKey, $prefix, $suffix)) ?
            $utility::makeInstanceClassName($class) : false;
    }
    /**
     * Returns an array with information about a class
     *
     * @param string $minimalInformation
     * @param string $alternativeKey
     */
    public static function getClassInfo($minimalInformation, $alternativeKey = '', $prefix = 'class.', $suffix = '.php')
    {
        $info = trim($minimalInformation);
        if (!$info) {
            throw new Exception('getClassInfo called with empty parameter');
        }

        $qSuffix = preg_quote($suffix, '/');
        // If it is a path extract the key first.
        // Either the relevant part starts with a slash: xyz/[tx_].....php
        if (preg_match('/^.*\/([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
            $class = $matches[1];
        } elseif (preg_match('/^.*\.([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
            // Or it starts with a Dot: class.[tx_]....php
            $class = $matches[1];
        } elseif (preg_match('/^([0-9A-Za-z_]+)' . $qSuffix . '$/', $info, $matches)) {
            // Or it starts directly with the relevant part
            $class = $matches[1];
        } elseif (preg_match('/^[0-9a-zA-Z_]+$/', trim($info), $matches)) {
            // It may be the key itself
            $class = $info;
        } else {
            throw new Exception('Classname contains invalid characters or has invalid format: ' . $info);
        }

            // With this a possible alternative Key is also validated
        if (!$key = self::guessKey($alternativeKey ? $alternativeKey : $class)) {
            throw new Exception('No extension key found for classname: ' . $info);
        }

        $isExtBase = false;
        if (preg_match('/^tx_[0-9A-Za-z_]*$/', $class)) {  // with tx_ prefix
            $parts = explode('_', trim($class));
            array_shift($parts); // strip tx
        } elseif (preg_match('/^Tx_[0-9A-Za-z_]*$/', $class)) {  // with Tx_ prefix (ExtBase)
            $parts = explode('_', trim($class));
            array_shift($parts); // strip tx
            $isExtBase = true;
        } elseif (preg_match('/^[0-9A-Za-z_]*$/', $class)) { // without tx_ prefix
            $parts = explode('_', trim($class));
        } else {
            throw new Exception('getClassInfo() called with invalid classname: ' . $info);
        }

        // Set extPath for key (first element)
        array_shift($parts);

        // Save last element of path
        if (count($parts) > 0) {
            $last = array_pop($parts);
        }

        $dir = $isExtBase ? 'Classes/' : '';
        // Build the relative path if any
        foreach ((array)$parts as $part) {
            $dir .= $part . '/';
        }
        // if an alternative Key is given use that
        $ret['class'] = $class;
        $ret['dir'] = $dir;
        $ret['extkey'] = $key;
        $ret['extpath'] = tx_rnbase_util_Extensions::extPath($key);
        if ($isExtBase) {
            $path = $ret['extpath'] . $dir . $last . $suffix;
        } else {
            $path = $ret['extpath'] . $dir . $prefix . $class . $suffix;
        }
        if (!is_file($path)) {
            // Now we try INSIDE the last directory (dir and last may be empty)
            // ext(/dir)/last
            // ext(/dir)/last/prefix.tx_key_parts_last.php.
            $path = $ret['extpath'] . $dir . $last. '/' . $prefix . $class . $suffix;
            if (!is_file($path)) {
                throw new Exception('Class path not found: ' . $path);
            }
        }
        $ret['path'] = $path;

        return $ret;
    }

    /**
     * Find path to load
     * Method from tx_lib_t3Loader
     *
     * see load
     *
     * @param   string      classname
     * @param   string      extension key that varies from classnames
     * @param   string      prefix of classname
     * @param   string      ending of classname
     * @return  string      the path, FALSE if invalid
     * @see         load()
     */
    private static function _findT3($minimalInformation, $alternativeKey = '', $prefix = 'class.', $suffix = '.php')
    {
        $info = self::getClassInfo($minimalInformation, $alternativeKey);

        return $info['path'];
    }

    /**
     * Check if the given extension key is within the loaded extensions
     *
     * The key can be given in the regular format or with underscores stripped.
     *
     * @author Elmar Hinz
     * @param   string      extension key to check
     * @return  bool     is the key valid?
     */
    private static function getValidKey($rawKey)
    {
        if (!isset($GLOBALS['TYPO3_LOADED_EXT']) || !is_array($GLOBALS['TYPO3_LOADED_EXT'])) {
            false;
        }
        $uKeys = array_keys($GLOBALS['TYPO3_LOADED_EXT']);
        foreach ($uKeys as $uKey) {
            if (str_replace('_', '', $uKey) == str_replace('_', '', $rawKey)) {
                $result =  $uKey;
            }
        }

        return $result ? $result : false;
    }


    /**
     * Guess the key from the given information
     *
     * Guessing has the following order:
     *
     * 1. A KEY itself is tried.
     *    <pre>
     *     Example: my_extension
     *    </pre>
     * 2. A classnmae of the pattern tx_KEY_something_else is tried.
     *    <pre>
     *     Example: tx_myextension_view
     *    </pre>
     * 3. A full classname of the pattern ' * tx_KEY_something_else.php' is tried.
     *    <pre>
     *     Example: class.tx_myextension_view.php
     *     Example: brokenPath/class.tx_myextension_view.php
     *    </pre>
     * 4. A path that starts with the KEY is tried.
     *    <pre>
     *     Example: my_extension/class.view.php
     *    </pre>
     *
     * @author Elmar Hinz
     * @param   string      the minimal necessary information (see 1-4)
     * @return  string      the guessed key, FALSE if no result
     */
    private static function guessKey($minimalInformation)
    {
        $info = trim($minimalInformation);
        $key = false;
        if ($info) {
            // Can it be the key itself?
            if (!$key && preg_match('/^([A-Za-z_]*)$/', $info, $matches)) {
                $key = $matches[1];
                $key = self::getValidKey($key);
            }
            // Is it a classname that contains the key?
            if (!$key && (preg_match('/^tx_([^_]*)(.*)$/', $info, $matches) || preg_match('/^user_([^_]*)(.*)$/', $info, $matches))) {
                $key = $matches[1];
                $key = self::getValidKey($key);
            }
            // Test again for extbase class
            if (!$key && (preg_match('/^Tx_([^_]*)(.*)$/', $info, $matches))) {
                $key = strtolower($matches[1]);
                $key = self::getValidKey($key);
            }
            // Is there a full filename that contains the key in it?
            if (!$key && (preg_match('/^.*?tx_([^_]*)(.*)\.php$/', $info, $matches) || preg_match('/^.*?user_([^_]*)(.*)\.php$/', $info, $matches))) {
                $key = $matches[1];
                $key = self::getValidKey($key);
            }
            // Is it a path that starts with the key?
            if (!$key && $last = strstr('/', $info)) {
                $key = substr($info, 0, $last);
                $key = self::getValidKey($key);
            }
        }

        return $key ? $key : false;
    }
}
