<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2014 Rene Nitzsche
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
 * Contains utility functions for ArrayObject
 */
class tx_rnbase_util_Arrays {

  /**
   * Overwrite some of the array values
   *
   * Overwrite a selection of the values by providing new ones
   * in form of a data structure of the tx_div hash family.
   *
   * @param    mixed    hash array, SPL object or hash string ( i.e. "key1 : value1, key2 : valu2, ... ")
   * @param    string   possible split charaters in case the first parameter is a hash string
   * @return   void
   */
  public static function overwriteArray(&$arrayObj, $hashData, $splitCharacters = ',;:') {
    $array = self::toHashArray($hashData, $splitCharacters);
    foreach((array) $array as $key => $value) {
      $arrayObj->offsetSet($key, $value);
    }
  }

	/**
	 * Converts the given mixed data into an hashArray
	 * Method taken from tx_div
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  array       an hashArray
	 */
	private static function toHashArray($mixed, $splitCharacters = ',;:\s' ) {
		if(is_string($mixed)) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			$array = tx_rnbase_util_Misc::explode($mixed, $splitCharacters); // TODO: Enable empty values by defining a better explode functions.
			for($i = 0, $len = count($array); $i < $len; $i = $i + 2) {
				$hashArray[$array[$i]] = $array[$i+1];
			}
		} elseif(is_array($mixed)) {
			$hashArray = $mixed;
		} elseif(is_object($mixed) && method_exists($mixed, 'getArrayCopy')) {
			$hashArray = $mixed->getArrayCopy();
		} else {
			$hashArray = array();
		}
		return $hashArray;
	}

	/**
	 * Merges two arrays recursively and "binary safe".
	 *
	 * @see \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule();
	 *
	 * @param array $original
	 * @param array $overrule
	 * @param boolean $addKeys
	 * @param boolean $includeEmptyValues
	 * @param boolean $enableUnsetFeature
	 * @return void
	 */
	public static function mergeRecursiveWithOverrule(
		array $original,
		array $overrule,
		$addKeys = TRUE,
		$includeEmptyValues = TRUE,
		$enableUnsetFeature = TRUE
	) {
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
				$original,
				$overrule,
				$addKeys,
				$includeEmptyValues,
				$enableUnsetFeature
			);
			return $original;
		} else {
			return t3lib_div::array_merge_recursive_overrule(
				$original,
				$overrule,
				!$addKeys,
				$includeEmptyValues,
				$enableUnsetFeature
			);
		}
	}


	/**
	 * @see \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule();
	 *
	 * @param array $arr Data array which should be outputted
	 * @param mixed $valueList List of keys which should be listed in the output string. Pass a comma list or an array. An empty list outputs the whole array.
	 * @param integer $valueLength Long string values are shortened to this length. Default: 20
	 * @return string Output string with key names and their value as string
	 */
	static public function arrayToLogString(array $arr, $valueList = array(), $valueLength = 20) {
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$array = \TYPO3\CMS\Core\Utility\GeneralUtility::arrayToLogString($arr, $valueList, $valueLength);
		} else {
			$array = t3lib_div::arrayToLogString($arr, $valueList, $valueLength);
		}

		return $array;
	}

	/**
	 * @see \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array
	 * @see t3lib_div::xml2array
	 *
	 * @param string $string XML content to convert into an array
	 * @param string $NSprefix The tag-prefix resolve, eg. a namespace like "T3:"
	 * @param boolean $reportDocTag If set, the document tag will be set in the key "_DOCUMENT_TAG" of the output array
	 * @return mixed If the parsing had errors, a string with the error message is returned. Otherwise an array with the content.
	 * @see array2xml(),xml2arrayProcess()
	 */
	static public function xml2array($string, $NSprefix = '', $reportDocTag = FALSE) {
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$array = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($string, $NSprefix, $reportDocTag);
		} else {
			$array = t3lib_div::xml2array($string, $NSprefix, $reportDocTag);
		}

		return $array;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Arrays.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Arrays.php']);
}