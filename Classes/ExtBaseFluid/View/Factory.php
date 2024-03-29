<?php

namespace Sys25\RnBase\ExtBaseFluid\View;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;

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
 * Sys25\RnBase\ExtBaseFluid\View$Factory.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Factory
{
    /**
     * @param ConfigurationInterface $configurations
     * @param array                  $frameworkSettings
     *
     * @return Standalone
     */
    public static function getViewInstance(ConfigurationInterface $configurations, $frameworkSettings = [])
    {
        if (!TYPO3::isTYPO115OrHigher()) {
            /* @var $view \Sys25\RnBase\ExtBaseFluid\View\Standalone */
            $view = tx_rnbase::makeInstance('Sys25\\RnBase\\ExtBaseFluid\\View\\Standalone', $configurations->getCObj());

            $objectManager = $view->getObjectManager();
            $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
            $configurationManager->setConfiguration($frameworkSettings);
            $view->injectObjectManager($objectManager);
            $view->setConfigurations($configurations);

            return $view;
        }

        $view = tx_rnbase::makeInstance(Standalone::class);
        $configurationManager = GeneralUtility::getContainer()->get(ConfigurationManager::class);
        $configurationManager->setContentObject($configurations->getCObj());
        $configurationManager->setConfiguration($frameworkSettings);
        $view->setConfigurations($configurations);
        if (TYPO3::isTYPO121OrHigher()) {
            $request = new Request($GLOBALS['TYPO3_REQUEST']->withAttribute('extbase', new ExtbaseRequestParameters()));
            $view->getRenderingContext()->setRequest($request);
            $view->setRequest($request);
        }

        return $view;
    }
}
