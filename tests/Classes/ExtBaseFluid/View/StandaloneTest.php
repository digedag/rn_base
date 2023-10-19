<?php

namespace Sys25\RnBase\ExtBaseFluid\View;

use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Testing\TestUtility;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use tx_rnbase_util_Typo3Classes;

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
 * Sys25\RnBase\ExtBaseFluid\View$StandloneTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class StandaloneTest extends BaseTestCase
{
    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testGetConfigurations()
    {
        $contentObject = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $view = tx_rnbase::makeInstance('Sys25\\RnBase\\ExtBaseFluid\\View\\Standalone', $contentObject);
        $configurations = TestUtility::createConfigurations([], 'rn_base');
        $view->setConfigurations($configurations);

        self::assertSame($configurations, $view->getConfigurations());
    }

    /**
     * @group integration
     *
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testInjectAndGetObjectManager()
    {
        if (TYPO3::isTYPO115OrHigher()) {
            self::markTestSkipped('Only relevant before TYPO3 11.5');
        }
        $objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
        $contentObject = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $view = tx_rnbase::makeInstance('Sys25\\RnBase\\ExtBaseFluid\\View\\Standalone', $contentObject);
        $view->injectObjectManager($objectManager);

        self::assertSame($objectManager, $view->getObjectManager());
    }
}
