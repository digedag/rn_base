<?php

use Sys25\RnBase\Frontend\Request\ParametersInterface;
use Sys25\RnBase\Frontend\Request\Parameters;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
 *  Contact: rene@system25.de
 *
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

tx_rnbase::load('tx_rnbase_util_Arrays');

/**
 * @deprecated
 */
interface tx_rnbase_IParameters extends ParametersInterface
{
}

/**
 * @deprecated
 */
class tx_rnbase_parameters extends Parameters implements tx_rnbase_IParameters
{
    /**
     * @see t3lib_div::_GPmerged
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     * @return array Returns the GET vars merged recursively onto the POST vars.
     */
    public static function getPostAndGetParametersMerged($parameterName)
    {
        return parent::getPostAndGetParametersMerged($parameterName);
    }

    /**
     * @see t3lib_div::_GP
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GP
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     * @return array Returns the GET vars merged recursively onto the POST vars.
     */
    public static function getPostOrGetParameter($parameterName)
    {
        return parent::getPostOrGetParameter($parameterName);
    }

    /**
     * @see t3lib_div::_GETset
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GETset
     *
     * @param mixed $inputGet
     * @param string $key
     * @return void
     */
    public static function setGetParameter($inputGet, $key = '')
    {
        parent::setGetParameter($inputGet, $key);
    }

    /**
     * Returns the global $_GET array (or value from) normalized.
     *
     * @param string $var
     *
     * @see t3lib_div::_GET
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GET
     *
     * @return mixed
     */
    public static function getGetParameters($var = null)
    {
        parent::getGetParameters($var);
    }

    /**
     * Returns the global $_POST array (or value from) normalized.
     *
     * @param string $var
     *
     * @see t3lib_div::_POST
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_POST
     *
     * @return mixed
     */
    public static function getPostParameters($var = null)
    {
        return parent::getPostParameters($var);
    }
}
