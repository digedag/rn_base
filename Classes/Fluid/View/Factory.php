<?php

namespace Sys25\RnBase\Fluid\View;

/***************************************************************
 * Copyright notice
 *
 * (c) René Nitzsche <rene@system25.de>
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
 * Sys25\RnBase\Fluid\View$Factory.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Factory
{
    /**
     * @param tx_rnbase_configurations $configurations
     * @param array                    $frameworkSettings
     *
     * @return \Sys25\RnBase\Fluid\View\Standalone
     */
    public static function getViewInstance(\tx_rnbase_configurations $configurations, $frameworkSettings = [])
    {
        $view = \tx_rnbase::makeInstance('Sys25\\RnBase\\Fluid\\View\\Standalone', $configurations->getCObj());

        $objectManager = $view->getObjectManager();
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
        $configurationManager->setConfiguration($frameworkSettings);
        $view->injectObjectManager($objectManager);

        $controllerContext = $view->getRenderingContext()->getControllerContext();
        $controllerContext->configurations = $configurations;

        $view->setControllerContext($controllerContext);

        return $view;
    }
}
