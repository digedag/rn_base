<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2016-2021 René Nitzsche <rene@system25.de>
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
 ***************************************************************/

/**
 * Wrapper for t3lib_div / TYPO3\\CMS\\Core\\Utility\\GeneralUtility.
 *
 * @author René Nitzsche
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 *
 * @method static mixed _GP(string $var)
 * @method static string getIndpEnv(string $getEnvName)
 * @method static string fixWindowsFilePath(string $theFile)
 * @method static int md5int(string $str)
 * @method static bool isOnCurrentHost(string $url)
 */
class T3General
{
    /**
     * Maps the static method to the typo3 util.
     *
     * @param string $method    Method name
     * @param array  $arguments Parameters to pass throu the util
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array(
            [
                Typo3Classes::getGeneralUtilityClass(),
                $method,
            ],
            $arguments
        );
    }

    /* *** ************************************ *** *
     * *** Methods with Parameters as Reference *** *
     * *** ************************************ *** */

    /**
     * StripSlash array
     * This function traverses a multidimensional array
     * and strips slashes to the values.
     * NOTE that the input array is and argument by reference.!!
     * Twin-function to addSlashesOnArray.
     *
     * @param array $theArray Multidimensional input array, (REFERENCE!)
     *
     * @return array
     */
    public static function stripSlashesOnArray(array &$theArray)
    {
        // there is no need to do this anymore!
        if (TYPO3::isTYPO80OrHigher()) {
            return $theArray;
        }

        $util = Typo3Classes::getGeneralUtilityClass();
        $util::stripSlashesOnArray($theArray);

        return $theArray;
    }

    /**
     * @deprecated use Strings::isFirstPartOfStr()
     */
    public static function isFirstPartOfStr($haystack, $needle)
    {
        return Strings::isFirstPartOfStr($haystack, $needle);
    }

    /**
     * AddSlash array
     * This function traverses a multidimensional array
     * and adds slashes to the values.
     * NOTE that the input array is and argument by reference.!!
     * Twin-function to stripSlashesOnArray.
     *
     * @param array $theArray Multidimensional input array, (REFERENCE!)
     *
     * @return array
     */
    public static function addSlashesOnArray(array &$theArray)
    {
        // there is no need to do this anymore!
        if (TYPO3::isTYPO80OrHigher()) {
            return $theArray;
        }

        $util = Typo3Classes::getGeneralUtilityClass();
        $util::addSlashesOnArray($theArray);

        return $theArray;
    }

    /**
     * AddSlash array
     * This function traverses a multidimensional array
     * and adds slashes to the values.
     * NOTE that the input array is and argument by reference.!!
     * Twin-function to stripSlashesOnArray.
     *
     * @param array $theArray Multidimensional input array, (REFERENCE!)
     *
     * @return array
     */
    public static function slashArray(array $arr, $cmd)
    {
        // there is no need to do this anymore!
        if (TYPO3::isTYPO80OrHigher()) {
            return $arr;
        }

        $util = Typo3Classes::getGeneralUtilityClass();

        return $util::addSlashesOnArray($arr, $cmd);
    }

    /**
     * Rename Array keys with a given mapping table.
     *
     * @param array       $array        Array by reference which should be remapped
     * @param array|mixed $mappingTable Arraymap: array/$oldKey => $newKey)
     *
     * @return array
     */
    public static function remapArrayKeys(array &$array, $mappingTable)
    {
        $util = Typo3Classes::getGeneralUtilityClass();
        $util::remapArrayKeys($array, $mappingTable);

        return $array;
    }

    /**
     * Sorts an array by key recursive - uses natural sort order (aAbB-zZ).
     *
     * @param array|mixed $array Array to be sorted recursively, passed by reference
     *
     * @return bool TRUE if param is an array
     */
    public static function naturalKeySortRecursive(&$array)
    {
        $util = Typo3Classes::getGeneralUtilityClass();

        return $util::naturalKeySortRecursive($array);
    }

    /**
     * Minifies JavaScript.
     *
     * @param string $script Script to minify
     * @param string $error  Error message (if any)
     *
     * @return string Minified script or source string if error happened
     */
    public static function minifyJavaScript($script, &$error = '')
    {
        $util = Typo3Classes::getGeneralUtilityClass();

        return $util::minifyJavaScript($script, $error);
    }

    /**
     * Reads the file or url $url and returns the content
     * If you are having trouble with proxys when reading URLs
     * you can configure your way out of that
     * with settings like $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] etc.
     *
     * @param string     $url            File/URL to read
     * @param int        $includeHeader  whether the HTTP header should be fetched or not
     * @param array|bool $requestHeaders HTTP headers to be used in the request
     * @param array|bool $report         Error code/message and response meta data
     *
     * @return mixed The content from the resource or FALSE
     */
    public static function getUrl(
        $url,
        $includeHeader = 0,
        $requestHeaders = false,
        &$report = null,
    ) {
        return Network::getUrl(
            $url,
            $includeHeader,
            $requestHeaders,
            $report
        );
    }

    /**
     * Calls a user-defined function/method in class
     * Such a function/method should look like this:
     * "function proc(&$params, &$ref) {...}".
     *
     * @param string $funcName    Function/Method reference or Closure
     * @param mixed  $params      Parameters to be pass along (REFERENCE!)
     * @param mixed  $ref         Reference to be passed along (REFERENCE!)
     * @param string $checkPrefix Not used anymore since 6.0
     * @param int    $errorMode   Error mode
     *
     * @return mixed Content from call or FALSE
     */
    public static function callUserFunction(
        $funcName,
        &$params,
        &$ref,
        $checkPrefix = '',
        $errorMode = 0,
    ) {
        $util = Typo3Classes::getGeneralUtilityClass();

        return $util::callUserFunction($funcName, $params, $ref, $checkPrefix, $errorMode);
    }

    /**
     * Gets the unixtime as milliseconds.
     *
     * @return int The unixtime as milliseconds
     */
    public static function milliseconds()
    {
        if (TYPO3::isTYPO104OrHigher()) {
            return round(microtime(true) * 1000);
        }

        $util = Typo3Classes::getGeneralUtilityClass();

        return $util::milliseconds();
    }

    /**
     * Removes an item from a comma-separated list of items.
     *
     * If $element contains a comma, the behaviour of this method is undefined.
     * Empty elements in the list are preserved.
     *
     * @param string $element Element to remove
     * @param string $list Comma-separated list of items (string)
     * @return string New comma-separated list of items
     */
    public static function rmFromList($element, $list)
    {
        $items = explode(',', $list);
        foreach ($items as $k => $v) {
            if ($v == $element) {
                unset($items[$k]);
            }
        }

        return implode(',', $items);
    }
}
