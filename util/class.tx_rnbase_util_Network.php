<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * tx_rnbase_util_Network
 *
 * Wrapper for Network related functions
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_Network {

	/**
	 * (non-PHPdoc)
	 * @see t3lib_div::cmpIP()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP()
	 */
	public static function cmpIP($baseIP, $list) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::cmpIP($baseIP, $list);
	}

	/**
	 * @see t3lib_div::getUrl()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::getUrl()
	 *
	 * @param string $url File/URL to read
	 * @param integer $includeHeader Whether the HTTP header should be fetched or not. 0=disable, 1=fetch header+content, 2=fetch header only
	 * @param array $requestHeaders HTTP headers to be used in the request
	 * @param array $report Error code/message and, if $includeHeader is 1, response meta data (HTTP status and content type)
	 * @return mixed The content from the resource given as input. FALSE if an error has occurred.
	 */
	static public function getUrl($url, $includeHeader = 0, $requestHeaders = FALSE, &$report = NULL) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::getUrl($url, $includeHeader, $requestHeaders, $report);
	}

	/**
	 * @see t3lib_div::isValidUrl()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl()
	 *
	 * @param string $url The URL to be validated
	 * @return boolean Whether the given URL is valid
	 */
	static public function isValidUrl($url) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::isValidUrl($url);
	}

	/**
	 * @see t3lib_div::locationHeaderUrl()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl()
	 *
	 * @param string $path URL / path to prepend full URL addressing to.
	 * @return string
	 */
	public static function locationHeaderUrl($path) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::locationHeaderUrl($path);
	}

	/**
	 * Sends a redirect header response and exits. Additionally the URL is
	 * checked and if needed corrected to match the format required for a
	 * Location redirect header. By default the HTTP status code sent is
	 * a 'HTTP/1.1 303 See Other'.
	 *
	 * @param string $url The target URL to redirect to
	 * @param string $httpStatus An optional HTTP status header. Default is 'HTTP/1.1 303 See Other'
	 *
	 * @return	void
	 */
	public static function redirect($url, $httpStatus = null) {
		$utility = tx_rnbase_util_Typo3Classes::getHttpUtilityClass();
		if ($httpStatus === null) {
			$httpStatus = $utility::HTTP_STATUS_303;
		}
		$utility::redirect($url, $httpStatus);
	}

	/**
	 * @param string $remoteAddress
	 * @param string $devIPmask
	 * @return boolean
	 */
	public static function isDevelopmentIp($remoteAddress = '', $devIPmask = ''){
		$devIPmask = trim(strcmp($devIPmask, '') ?
			$devIPmask : $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
		$remoteAddress = trim(strcmp($remoteAddress, '') ?
			$remoteAddress : tx_rnbase_util_Misc::getIndpEnv('REMOTE_ADDR'));
		return self::cmpIP($remoteAddress, $devIPmask);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Network.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Network.php']);
}
