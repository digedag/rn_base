<?php

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

interface tx_rnbase_IParameters
{
    /**
     * Liefert den Parameter-Wert
     *
     * @param string $paramName
     * @param string $qualifier
     * @return mixed
     */
    public function get($paramName, $qualifier = '');
    /**
     * removes xss etc. from the value
     *
     * @param string $field
     * @return string
     */
    public function getCleaned($paramName, $qualifier = '');
    /**
     * Liefert den Parameter-Wert als int
     *
     * @param string $paramName
     * @param string $qualifier
     * @return int
     */
    public function getInt($paramName, $qualifier = '');
    /**
     * Liefert alle Parameter-Werte
     *
     * @param string $qualifier
     * @return array
     */
    public function getAll($qualifier = '');
}


class tx_rnbase_parameters extends ArrayObject implements tx_rnbase_IParameters
{
    private $qualifier = '';

    /**
     * Initialize this instance for a plugin
     * @param string $qualifier
     */
    public function init($qualifier)
    {
        $this->setQualifier($qualifier);
        // get parametersArray for defined qualifier
        $parametersArray = $this->getParametersPlain($qualifier);
        tx_rnbase_util_Arrays::overwriteArray($this, $parametersArray);
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
     * removes xss from the value
     *
     * @param string $field
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
     * Liefert den Parameter-Wert als int
     *
     * @param string $paramName
     * @param string $qualifier
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
            $key = ($key{0} === 'N' && substr($key, 0, 3) === 'NK_') ? substr($key, 3) : $key;
            if (is_string($value)) {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * @see t3lib_div::_GPmerged
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged
     *
     * @param string $parameter Key (variable name) from GET or POST vars
     * @return array Returns the GET vars merged recursively onto the POST vars.
     */
    public static function getPostAndGetParametersMerged($parameterName)
    {
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GPmerged($parameterName);
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
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GP($parameterName);
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
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
        $utility::_GETset($inputGet, $key);
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
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_GET($var);
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
        $utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

        return $utility::_POST($var);
    }
}
