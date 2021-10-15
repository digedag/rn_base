<?php

namespace Sys25\RnBase\Tests;

use tx_rnbase;
use tx_rnbase_util_Typo3Classes;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018-2021 Rene Nitzsche (rene@system25.de)
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
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TestUtility
{
    /**
     * Sample:
     * TestUtility::createConfigurations(
     *   array(), 'rn_base', 'rn_base',
     *   tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class),
     *   tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass())
     * );.
     *
     * @param array  $configurationArray
     * @param string $extensionKey
     * @param string $qualifier
     *
     * @return \Sys25\RnBase\Configuration\ConfigurationInterface
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
        $args = count($args) > 3 ? array_slice($args, 3) : [];

        foreach ($args as $arg) {
            if ($arg instanceof \Sys25\RnBase\Frontend\Request\Parameters) {
                $parameters = $arg;
            }
            $contentObjectRendererClass = tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();
            if ($arg instanceof $contentObjectRendererClass) {
                $cObj = $arg;
            }
        }

        /* @var $configurations \Sys25\RnBase\Configuration\ConfigurationInterface */
        $configurations = tx_rnbase::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
        $configurations->init(
            $configurationArray,
            $cObj,
            $extensionKey,
            $qualifier
        );
        if ($parameters instanceof \Sys25\RnBase\Frontend\Request\ParametersInterface) {
            $configurations->setParameters($parameters);
        }

        return $configurations;
    }
}
