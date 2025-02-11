<?php

namespace Sys25\RnBase\Utility;

use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2021 Rene Nitzsche
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
 * Wrapper for Network related functions.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Network
{
    /**
     * (non-PHPdoc).
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP()
     */
    public static function cmpIP($baseIP, $list)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::cmpIP($baseIP, $list);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl()
     *
     * @param string      $url            File/URL to read
     * @param int         $includeHeader  Whether the HTTP header should be fetched or not. 0=disable, 1=fetch header+content, 2=fetch header only
     * @param array|false $requestHeaders HTTP headers to be used in the request
     * @param array       $report         Error code/message and, if $includeHeader is 1, response meta data (HTTP status and content type)
     *
     * @return mixed The content from the resource given as input. FALSE if an error has occurred.
     */
    public static function getUrl($url, $includeHeader = 0, $requestHeaders = false, &$report = null)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::getUrl($url, $includeHeader, $requestHeaders, $report);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl()
     *
     * @param string $url The URL to be validated
     *
     * @return bool Whether the given URL is valid
     */
    public static function isValidUrl($url)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::isValidUrl($url);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl()
     *
     * @param string $path URL / path to prepend full URL addressing to
     *
     * @return string
     */
    public static function locationHeaderUrl($path)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::locationHeaderUrl($path);
    }

    /**
     * Sends a redirect header response and exits. Additionally the URL is
     * checked and if needed corrected to match the format required for a
     * Location redirect header. By default the HTTP status code sent is
     * a 'HTTP/1.1 303 See Other'.
     *
     * @param string $url        The target URL to redirect to
     * @param string $httpStatus An optional HTTP status header. Default is 'HTTP/1.1 303 See Other'
     */
    public static function redirect($url, $httpStatus = null)
    {
        if (TYPO3::isTYPO115OrHigher()) {
            throw new ImmediateResponseException(new RedirectResponse($url, $httpStatus ?? 303));
        }

        $utility = Typo3Classes::getHttpUtilityClass();
        if (null === $httpStatus) {
            $httpStatus = $utility::HTTP_STATUS_303;
        }
        $utility::redirect($url, $httpStatus);
    }

    /**
     * @param string $remoteAddress
     * @param string $devIPmask
     *
     * @return bool
     */
    public static function isDevelopmentIp($remoteAddress = '', $devIPmask = '')
    {
        $devIPmask = trim(strcmp($devIPmask, '') ?
            $devIPmask : $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
        $remoteAddress = trim(strcmp($remoteAddress, '') ?
            $remoteAddress : Misc::getIndpEnv('REMOTE_ADDR'));

        return self::cmpIP($remoteAddress, $devIPmask);
    }
}
