<?php

namespace Sys25\RnBase\ExtBaseFluid\ViewHelper;

use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionProperty;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use tx_rnbase_util_Misc;

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
 * Sys25\RnBase\ExtBaseFluid\ViewHelper$BaseViewHelperTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
abstract class BaseViewHelperTest extends BaseTestCase
{
    use ProphecyTrait;

    /**
     * @var \TYPO3Fluid\Fluid\Core\Rendering\RenderingContext
     */
    protected $renderingContext;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        tx_rnbase_util_Misc::prepareTSFE();
        parent::setUp();
    }

    /**
     * @param string|\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
     *
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
     */
    protected function getPreparedVîewHelper($viewHelper)
    {
        if (TYPO3::isTYPO115OrHigher()) {
            self::markTestSkipped('This method needs refactoring to work since TYPO3 11.5');
        }

        if (!is_object($viewHelper)) {
            $viewHelper = tx_rnbase::makeInstance($viewHelper);
        }

        $this->renderingContext = new \TYPO3Fluid\Fluid\Core\Rendering\RenderingContext();
        $templateVariableProvider = new \TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider();
        $this->renderingContext->setVariableProvider($templateVariableProvider);
        $property = new ReflectionProperty(
            'TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContext',
            'viewHelperVariableContainer'
        );
        $property->setAccessible(true);
        $viewHelperVariableContainer = new \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer();
        $property->setValue($this->renderingContext, $viewHelperVariableContainer);

        $controllerContext = new \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext();
        $controllerContext->setRequest(new \TYPO3\CMS\Extbase\Mvc\Request());

        $uriBuilder = new \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder();
        $property = new ReflectionProperty(
            \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class,
            'environmentService'
        );
        $environmentService = new \TYPO3\CMS\Extbase\Service\EnvironmentService();
        $property->setAccessible(true);
        $property->setValue($uriBuilder, $environmentService);

        $controllerContext->setUriBuilder($uriBuilder);
        $this->renderingContext->setControllerContext($controllerContext);

        $objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
        $viewHelper->setRenderingContext($this->renderingContext);

        if (method_exists($viewHelper, 'injectObjectManager')) {
            $viewHelper->injectObjectManager($objectManager);
        }

        $reflectionService = $this->prophesize(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
        $viewHelper->injectReflectionService($reflectionService);

        return $viewHelper;
    }
}
