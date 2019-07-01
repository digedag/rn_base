<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class tx_rnbase_tests_Utility.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_Utility
{
    /**
     * Sample:
     * tx_rnbase_tests_Utility::createConfigurations(
     *   array(), 'rn_base', 'rn_base',
     *   tx_rnbase::makeInstance('tx_rnbase_parameters'),
     *   tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass())
     * );
     *
     * @param array $configurationArray
     * @param string $extensionKey
     * @param string $qualifier
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    public static function createConfigurations(
        array $configurationArray,
        $extensionKey,
        $qualifier = ''
    ) {
        $qualifier = empty($qualifier) ? $extensionKey : $qualifier;

        $parameters = null;
        $cObj = null;

        $args = func_get_args();
        $args = count($args) > 3 ? array_slice($args, 3) : array();

        foreach ($args as $arg) {
            if ($arg instanceof tx_rnbase_parameters) {
                $parameters = $arg;
            }
            $contentObjectRendererClass = tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();
            if ($arg instanceof $contentObjectRendererClass) {
                $cObj = $arg;
            }
        }

        /* @var $configurations Tx_Rnbase_Configuration_ProcessorInterface */
        $configurations = tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
        $configurations->init(
            $configurationArray,
            $cObj,
            $extensionKey,
            $qualifier
        );
        if ($parameters instanceof tx_rnbase_parameters) {
            $configurations->setParameters($parameters);
        }

        return $configurations;
    }
}
