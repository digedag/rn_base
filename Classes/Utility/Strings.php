<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2016 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('Tx_Rnbase_Utility_T3General');

/**
 * Wrapper for t3lib_div / TYPO3\\CMS\\Core\\Utility\\GeneralUtility.
 *
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_Strings
{
    /**
     * Check whether or not the given string ist utf-8 encoded.
     *
     * @param string $str
     *
     * @return int utf-8 level or false if non-utf-8 string found
     */
    public static function isUtf8String($str)
    {
        return self::valid_utf8($str);

        return 0 != preg_match('%(?:
        [\x09\x0A\x0D\x20-\x7E]
        |[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $str);
    }

    /**
     * Returns byte data about a string.
     *
     * @param string $str
     *
     * @return array
     */
    public static function debugString($str)
    {
        $bytes = [];
        $hexArr = [];
        for ($i = 0, $cnt = mb_strlen($str, '8bit'); $i < $cnt; ++$i) {
            $bytes[$i] = [
                    'ascii' => $str[$i],
                    'dec' => ord($str[$i]),
                    'hex' => dechex(ord($str[$i])),
            ];
            $hexArr[$i] = dechex(ord($str[$i]));
        }
        $ret = [
                'bytelength' => mb_strlen($str, '8bit'),
                'bin2hex' => bin2hex($str),
                'bytes' => $bytes,
                'hexArr' => serialize($hexArr),
        ];

        return $ret;
    }

    /**
     * Returns TRUE if the first part of $haystack matches the string $needle.
     *
     * @param string $haystack Full string to check
     * @param string $needle   Reference string which must be found as the "first part" of the full string
     *
     * @return bool TRUE if $partStr was found to be equal to the first part of $str
     */
    public static function isFirstPartOfStr($haystack, $needle)
    {
        return Tx_Rnbase_Utility_T3General::isFirstPartOfStr($haystack, $needle);
    }

    /**
     * Wrapper for t3lib_div::testInt and \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($var).
     *
     * @param mixed $var
     *
     * @return bool
     *
     * @deprecated use tx_rnbase_util_Math::isInteger
     */
    public static function isInteger($var)
    {
        return tx_rnbase_util_Math::isInteger($var);
    }

    /**
     * Returns TRUE if the last part of $haystack matches the string $needle.
     *
     * @param string $haystack Full string to check
     * @param string $needle   Reference string which must be found as the "first part" of the full string
     *
     * @return bool TRUE if $partStr was found to be equal to the first part of $str
     */
    public static function isLastPartOfStr($haystack, $needle)
    {
        // crop the stack
        $crop = substr($haystack, strlen($haystack) - strlen($needle));

        return $crop === $needle;
    }

    /**
     * Wrapper method for t3lib_div::intExplode().
     *
     * @param string $delimiter
     * @param string $string
     * @param bool   $onlyNonEmptyValues
     * @param int    $limit
     */
    public static function intExplode($delimiter, $string, $onlyNonEmptyValues = false, $limit = 0)
    {
        return Tx_Rnbase_Utility_T3General::intExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
    }

    /**
     * Wrapper method for t3lib_div::trimExplode().
     *
     * @param string $delimiter
     * @param string $string
     * @param bool   $removeEmptyValues
     * @param int    $limit
     *
     * @return string[]
     */
    public static function trimExplode($delimiter, $string, $removeEmptyValues = false, $limit = 0)
    {
        return Tx_Rnbase_Utility_T3General::trimExplode($delimiter, $string, $removeEmptyValues, $limit);
    }

    /**
     * Wrapped Method t3lib_div::getRandomHexString().
     *
     * @param int $count
     *
     * @return string
     */
    public static function getRandomHexString($count)
    {
        return Tx_Rnbase_Utility_T3General::getRandomHexString($count);
    }

    /**
     * Returns a given CamelCasedString as an lowercase string with underscores.
     * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
     * Taken from t3lib_div for backward compatibility.
     *
     * @param string $string: String to be converted to lowercase underscore
     *
     * @return string lowercase_and_underscored_string
     */
    public static function camelCaseToLowerCaseUnderscored($string)
    {
        return Tx_Rnbase_Utility_T3General::camelCaseToLowerCaseUnderscored($string);
    }

    /**
     * Returns a given string with underscores as lowerCamelCase.
     * Example: Converts minimal_value to minimalValue.
     *
     * @param string $string: String to be converted to lowercase underscore
     *
     * @return string lowercase_and_underscored_string
     */
    public static function underscoredToLowerCamelCase($string)
    {
        return Tx_Rnbase_Utility_T3General::underscoredToLowerCamelCase($string);
    }

    /**
     * Convert an array with hexadecimal byte values to binary string.
     *
     * @param array $arr
     *
     * @return string
     */
    public static function hexArr2bin($arr)
    {
        $ret = '';
        foreach ($arr as $byte) {
            $ret .= chr(hexdec($byte));
        }

        return $ret;
    }

    /**
     *  Ist String korrektes UTF-8?
     */
    private static function valid_utf8($string)
    {
        $result = 1;
        $len = strlen($string);
        $i = 0;
        while ($i < $len) {
            $char = ord($string[$i++]);
            if (self::valid_1byte($char)) { // continue
                continue;
            } elseif (self::valid_2byte($char)) { // check 1 byte
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
                $result = max($result, 2);
            } elseif (self::valid_3byte($char)) { // check 2 bytes
                $result = max($result, 3);
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
            } elseif (self::valid_4byte($char)) { // check 3 bytes
                $result = max($result, 4);
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
                if (!self::valid_nextbyte(ord($string[$i++]))) {
                    return false;
                }
            } else {
                return false; // 10xxxxxx occuring alone
            } // goto next char
        }

        return $result; // done
    }

    private static function valid_1byte($char)
    {
        if (!is_int($char)) {
            return false;
        }

        return 0x00 == ($char & 0x80);
    }

    private static function valid_2byte($char)
    {
        if (!is_int($char)) {
            return false;
        }

        return 0xC0 == ($char & 0xE0);
    }

    private static function valid_3byte($char)
    {
        if (!is_int($char)) {
            return false;
        }

        return 0xE0 == ($char & 0xF0);
    }

    private static function valid_4byte($char)
    {
        if (!is_int($char)) {
            return false;
        }

        return 0xF0 == ($char & 0xF8);
    }

    private static function valid_nextbyte($char)
    {
        if (!is_int($char)) {
            return false;
        }

        return 0x80 == ($char & 0xC0);
    }

    /**
     * Wrapper method for t3lib_div::inList() or \TYPO3\CMS\Core\Utility\GeneralUtility::inList().
     *
     * @param string $list Comma-separated list of items (string)
     * @param string $item Item to check for
     *
     * @return bool TRUE if $item is in $list
     */
    public static function inList($list, $item)
    {
        return Tx_Rnbase_Utility_T3General::inList($list, $item);
    }

    /**
     * Makes the given string safe against XSS.
     *
     * @param string $string Input string
     *
     * @return string Input string with potential XSS code removed
     */
    public static function removeXSS($string)
    {
        return htmlspecialchars($string);
    }

    /**
     * Wrapper method for GLOBALS[LANG]::JScharCode() or \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue()
     * Converts the input string to a JavaScript function returning the same string, but charset-safe.
     * Used for confirm and alert boxes where we must make sure that any string content
     * does not break the script AND want to make sure the charset is preserved.
     *
     * @param string $string Input string
     *
     * @return string Input string with potential XSS code removed
     */
    public static function quoteJSvalue($string)
    {
        return Tx_Rnbase_Utility_T3General::quoteJSvalue($string);
    }

    /**
     * Wrapper method for t3lib_div::validEmail() or \TYPO3\CMS\Core\Utility\GeneralUtility::validEmail().
     *
     * @param string $email Input string to evaluate
     *
     * @return bool Returns TRUE if the $email address (input string) is valid
     */
    public static function validEmail($email)
    {
        return Tx_Rnbase_Utility_T3General::validEmail($email);
    }
}
