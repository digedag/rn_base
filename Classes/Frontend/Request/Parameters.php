<?php

namespace Sys25\RnBase\Frontend\Request;

/***************************************************************
* Copyright notice
*
* (c) 2007-2019 René Nitzsche <rene@system25.de>
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

class Parameters extends \ArrayObject implements ParametersInterface
{
    private $qualifier = '';

    /**
     * Initialize this instance for a plugin.
     *
     * @param string $qualifier
     */
    public function init($qualifier)
    {
        $this->setQualifier($qualifier);
        // get parametersArray for defined qualifier
        $parametersArray = $this->getParametersPlain($qualifier);
        \tx_rnbase_util_Arrays::overwriteArray($this, $parametersArray);
    }

    public function setQualifier($qualifier)
    {
        $this->qualifier = $qualifier;
    }

    public function getQualifier()
    {
        return $this->qualifier;
    }

    public function get($paramName, $qualifier = '')
    {
        if ($qualifier) {
            $params = $this->getParametersPlain($qualifier);
            $value = array_key_exists($paramName, $params) ? $params[$paramName] : $params['NK_'.$paramName];

            return $value;
        }

        return $this->offsetExists($paramName) ? $this->offsetGet($paramName) : $this->offsetGet('NK_'.$paramName);
    }

    /**
     * removes xss from the value.
     *
     * @param string $field
     *
     * @return string
     */
    public function getCleaned($paramName, $qualifier = '')
    {
        $value = $this->get($paramName, $qualifier);
        // remove Cross-Site Scripting
        if (!empty($value) && strlen($value) > 3) {
            $value = htmlspecialchars($value);
        }

        return $value;
    }

    /**
     * Liefert den Parameter-Wert als int.
     *
     * @param string $paramName
     * @param string $qualifier
     *
     * @return int
     */
    public function getInt($paramName, $qualifier = '')
    {
        return intval($this->get($paramName, $qualifier));
    }

    private function getParametersPlain($qualifier)
    {
        $parametersArray = self::getPostAndGetParametersMerged($qualifier);

        return $parametersArray;
    }

    public function getAll($qualifier = '')
    {
        $ret = [];
        $qualifier = $qualifier ? $qualifier : $this->getQualifier();
        $params = $this->getParametersPlain($qualifier);
        foreach ($params as $key => $value) {
            $key = ('N' === $key[0] && 'NK_' === substr($key, 0, 3)) ? substr($key, 3) : $key;
            if (is_string($value)) {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     *
     * @return array|string returns the GET vars merged recursively onto the POST vars
     */
    public static function getPostAndGetParametersMerged($parameterName)
    {
        $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GPmerged($parameterName);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GP
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     *
     * @return array|string returns the GET vars merged recursively onto the POST vars
     */
    public static function getPostOrGetParameter($parameterName)
    {
        $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GP($parameterName);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GETset
     *
     * @param mixed  $inputGet
     * @param string $key
     */
    public static function setGetParameter($inputGet, $key = '')
    {
        $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
        $utility::_GETset($inputGet, $key);
    }

    /**
     * Returns the global $_GET array (or value from) normalized.
     *
     * @param string $var
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GET
     *
     * @return mixed
     */
    public static function getGetParameters($var = null)
    {
        $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GET($var);
    }

    /**
     * Returns the global $_POST array (or value from) normalized.
     *
     * @param string $var
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_POST
     *
     * @return mixed
     */
    public static function getPostParameters($var = null)
    {
        $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_POST($var);
    }
}
