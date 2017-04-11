<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2013-2017 René Nitzsche <rene@system25.de>
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
 * Contains utility functions for TypoScript
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *		  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_TypoScript
{
	/**
	 * Creates an instance of the ts parser
	 *
	 * @return \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser
	 */
	private static function getTsParser()
	{
		return tx_rnbase::makeInstance(
			tx_rnbase_util_Typo3Classes::getTypoScriptParserClass()
		);
	}

	/**
	 * Parse the configuration of the given models
	 *
	 * @param string $typoScript
	 *
	 * @return array
	 */
	public static function parseTsConfig($typoScript)
	{
		$parser = self::getTsParser();

		$parser->parse(
			$parser->checkIncludeLines($typoScript)
		);

		return $parser->setup;
	}

	/**
	 * Removes all trailing dots recursively from TS settings array
	 *
	 * This method is taken from extbase TypoScriptService
	 *
	 * @param array $typoScriptArray
	 *
	 * @return array
	 */
	public static function convertTypoScriptArrayToPlainArray(array $typoScriptArray)
	{
		foreach ($typoScriptArray as $key => $value) {
			if (substr($key, -1) === '.') {
				$keyWithoutDot = substr($key, 0, -1);
				$typoScriptNodeValue = isset($typoScriptArray[$keyWithoutDot]) ? $typoScriptArray[$keyWithoutDot] : null;
				if (is_array($value)) {
					$typoScriptArray[$keyWithoutDot] = self::convertTypoScriptArrayToPlainArray($value);
					if (!is_null($typoScriptNodeValue)) {
						$typoScriptArray[$keyWithoutDot]['_typoScriptNodeValue'] = $typoScriptNodeValue;
					}
					unset($typoScriptArray[$key]);
				} else {
					$typoScriptArray[$keyWithoutDot] = null;
				}
			}
		}
		return $typoScriptArray;
	}

	/**
	 * Returns an array with Typoscript the old way (with dot).
	 *
	 * This method is taken from extbase TypoScriptService
	 *
	 * @param array $plainArray
	 *
	 * @return array
	 */
	public static function convertPlainArrayToTypoScriptArray(array $plainArray)
	{
		$typoScriptArray = array();
		foreach ($plainArray as $key => $value) {
			if (is_array($value)) {
				if (isset($value['_typoScriptNodeValue'])) {
					$typoScriptArray[$key] = $value['_typoScriptNodeValue'];
					unset($value['_typoScriptNodeValue']);
				}
				// add dot only if not exists
				$key = substr($key, -1) === '.' ? $key : $key . '.';
				$typoScriptArray[$key] = self::convertPlainArrayToTypoScriptArray($value);
			} else {
				$typoScriptArray[$key] = is_null($value) ? '' : $value;
			}
		}
		return $typoScriptArray;
	}
}
