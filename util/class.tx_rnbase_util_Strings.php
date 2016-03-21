<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Rene Nitzsche
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
tx_rnbase::load('Tx_Rnbase_Utility_Strings');

/**
 * String utilities.
 * @deprecated use Tx_Rnbase_Utility_Strings
 */
class tx_rnbase_util_Strings {
	/**
	 * Check whether or not the given string ist utf-8 encoded
	 * @param string $str
	 * @return int utf-8 level or false if non-utf-8 string found
	 */
	public static function isUtf8String($str) {
		return Tx_Rnbase_Utility_Strings::isUtf8String($str);
	}

	/**
	 * Returns byte data about a string.
	 * @param string $str
	 * @return array
	 */
	public static function debugString($str) {
		return Tx_Rnbase_Utility_Strings::debugString($str);
	}

	/**
	 * Returns TRUE if the first part of $haystack matches the string $needle
	 *
	 * @param string $haystack Full string to check
	 * @param string $needle Reference string which must be found as the "first part" of the full string
	 * @return boolean TRUE if $partStr was found to be equal to the first part of $str
	 */
	public static function isFirstPartOfStr($haystack, $needle) {
		return Tx_Rnbase_Utility_Strings::isFirstPartOfStr($haystack, $needle);
	}

	/**
	 * Wrapper for t3lib_div::testInt and \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($var)
	 * @param mixed $var
	 * @return boolean
	 * @deprecated use tx_rnbase_util_Math::isInteger
	 */
	public static function isInteger($var) {
		return Tx_Rnbase_Utility_Strings::isInteger($var);
	}

	/**
	 * Returns TRUE if the last part of $haystack matches the string $needle
	 *
	 * @param string $haystack Full string to check
	 * @param string $needle Reference string which must be found as the "first part" of the full string
	 * @return boolean TRUE if $partStr was found to be equal to the first part of $str
	 */
	public static function isLastPartOfStr($haystack, $needle) {
		return Tx_Rnbase_Utility_Strings::isLastPartOfStr($haystack, $needle);
	}

	/**
	 * Wrapper method for t3lib_div::intExplode()
	 * @param string $delimiter
	 * @param string $string
	 * @param boolean $onlyNonEmptyValues
	 * @param int $limit
	 */
	public static function intExplode($delimiter, $string, $onlyNonEmptyValues = FALSE, $limit = 0) {
		return Tx_Rnbase_Utility_Strings::intExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
	}
	/**
	 * Wrapper method for t3lib_div::trimExplode()
	 * @param string $delimiter
	 * @param string $string
	 * @param boolean $removeEmptyValues
	 * @param int $limit
	 */
	public static function trimExplode($delimiter, $string, $removeEmptyValues = FALSE, $limit = 0) {
		return Tx_Rnbase_Utility_Strings::trimExplode($delimiter, $string, $removeEmptyValues, $limit);
	}

	/**
	 * Wrapped Method t3lib_div::getRandomHexString()
	 *
	 * @param int $count
	 * @return string
	 */
	public static function getRandomHexString($count) {
		return Tx_Rnbase_Utility_Strings::getRandomHexString($count);
	}

	/**
	 * Returns a given CamelCasedString as an lowercase string with underscores.
	 * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
	 * Taken from t3lib_div for backward compatibility
	 *
	 * @param string $string: String to be converted to lowercase underscore
	 * @return string lowercase_and_underscored_string
	 */
	public static function camelCaseToLowerCaseUnderscored($string) {
		return Tx_Rnbase_Utility_Strings::camelCaseToLowerCaseUnderscored($string);
	}

	/**
	 * Returns a given string with underscores as lowerCamelCase.
	 * Example: Converts minimal_value to minimalValue
	 *
	 * @param string $string: String to be converted to lowercase underscore
	 * @return string lowercase_and_underscored_string
	 */
	public static function underscoredToLowerCamelCase($string) {
		return Tx_Rnbase_Utility_Strings::underscoredToLowerCamelCase($string);
	}

	/**
	 * Convert an array with hexadecimal byte values to binary string.
	 * @param array $arr
	 * @return string
	 */
	public static function hexArr2bin($arr) {
		return Tx_Rnbase_Utility_Strings::hexArr2bin($arr);
	}


	/**
	 * Wrapper method for t3lib_div::inList() or \TYPO3\CMS\Core\Utility\GeneralUtility::inList()
	 *
	 * @param string $list Comma-separated list of items (string)
	 * @param string $item Item to check for
	 * @return boolean TRUE if $item is in $list
	 */
	public static function inList($list, $item) {
		return Tx_Rnbase_Utility_Strings::inList($list, $item);
	}

	/**
	 * Wrapper method for t3lib_div::removeXSS() or \TYPO3\CMS\Core\Utility\GeneralUtility::removeXSS()
	 *
	 * @param string $string Input string
	 * @return string Input string with potential XSS code removed
	 */
	public static function removeXSS($string) {
		return Tx_Rnbase_Utility_Strings::removeXSS($string);
	}

	/**
	 * Wrapper method for GLOBALS[LANG]::JScharCode() or \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue()
	 * Converts the input string to a JavaScript function returning the same string, but charset-safe.
	 * Used for confirm and alert boxes where we must make sure that any string content
	 * does not break the script AND want to make sure the charset is preserved.
	 *
	 * @param string $string Input string
	 * @return string Input string with potential XSS code removed
	 */
	public static function quoteJSvalue($string) {
		return Tx_Rnbase_Utility_Strings::quoteJSvalue($string);
	}

	/**
	 * Wrapper method for t3lib_div::validEmail() or \TYPO3\CMS\Core\Utility\GeneralUtility::validEmail()
	 *
	 * @param string $email Input string to evaluate
	 * @return boolean Returns TRUE if the $email address (input string) is valid
	 */
	public static function validEmail($email) {
		return Tx_Rnbase_Utility_Strings::validEmail($email);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Strings.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Strings.php']);
}
