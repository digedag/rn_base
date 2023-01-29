<?php

namespace Sys25\RnBase\Frontend\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

use Exception;
use Prophecy\Argument;
use ReflectionMethod;
use Sys25\RnBase\Configuration\ConfigurationBuilder;
use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Testing\TestErrorHandler;
use Sys25\RnBase\Testing\TestInvalidErrorHandler;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Utility\Typo3Classes;

/**
 * Die Sinnhaftigkeit der meisten Tests ist doch ziemlich zweifelhaft...
 */
class PluginControllerTest extends BaseTestCase
{
    private $exceptionHandlerConfig;
    private $controller;
    private $configurations;
    private $configMock;
    private $configBuilder;

    protected function setUp(): void
    {
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']);
        $this->exceptionHandlerConfig = $extConfig['exceptionHandler'];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;

        if (is_object($GLOBALS['TSFE'])) {
            unset($GLOBALS['TSFE']);
        }
        $this->configurations = $this->prophesize(Processor::class);
        $cObj = $this->prophesize(Typo3Classes::getContentObjectRendererClass());
        $this->configurations->getCObj()->willReturn($cObj->reveal());
        $this->configMock = $this->configurations->reveal();
        $this->configBuilder = $this->prophesize(ConfigurationBuilder::class);
        $this->configBuilder->buildConfigurationsObject(Argument::type('array'), Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn($this->configMock);

        $this->controller = new PluginController($this->configBuilder->reveal());
    }

    protected function tearDown(): void
    {
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']);
        $extConfig['exceptionHandler'] = $this->exceptionHandlerConfig;
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base'] = serialize($extConfig);
    }

    /**
     * @group unit
     */
    public function testHandleExceptionWithDefaultExceptionHandler()
    {
        $this->setExceptionHandlerConfig();
        $this->configurations->getConfigArray()->willReturn([]);
        $this->configurations->getInt('recursionCheck.maxCalls')->willReturn(25);
        $this->configurations->getInt('recursionCheck.maxThrows')->willReturn(5);
        $this->configurations->get('catchException.0', true)->willReturn('');
        $this->configurations->get('error.0', true)->willReturn('');
        $this->configurations->get('error.default', true)->willReturn('');
        $this->configurations->getLL('ERROR_0', '')->willReturn('');
        $this->configurations->getLL('ERROR_default', '')->willReturn('');
        $this->configurations->getExtensionConfigValue('rn_base', 'send503HeaderOnException')->willReturn(0);
        $this->configurations->getExtensionConfigValue('rn_base', 'verboseMayday')->willReturn(0);
        $this->configurations->get('send503HeaderOnException')->willReturn(0);
        $this->configurations->getExtensionKey()->willReturn('rn_base');
        $this->configurations->getExtensionConfigValue('rn_base', 'sendEmailOnException')->willReturn(0);

        $method = new ReflectionMethod(
            PluginController::class,
            'handleException'
        );
        $method->setAccessible(true);

        $controller = $this->controller;
        $exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests');

        $handleExceptionReturn = $method->invoke(
            $controller,
            'testAction',
            $exception,
            $this->configMock
        );

        $this->assertEquals(
            '<div><strong>Leider ist ein unerwarteter Fehler aufgetreten.</strong></div>',
            $handleExceptionReturn,
            'wrong error message'
        );
    }

    /**
     * Hier wird mehr der ErrorHandler als der Controller getestet.
     *
     * @group unit
     */
    public function testHandleExceptionUsesDefaultExceptionHandlerIfConfiguredExceptionHandlerImplementsNotIhandlerInterface()
    {
        $errorCode = 333;
        $this->setExceptionHandlerConfig(TestInvalidErrorHandler::class);
        $this->configurations->getConfigArray()->willReturn([]);
        $this->configurations->getInt('recursionCheck.maxCalls')->willReturn(25);
        $this->configurations->getInt('recursionCheck.maxThrows')->willReturn(5);
        $this->configurations->get('catchException.'.$errorCode, true)->willReturn('Some error');

        $method = new ReflectionMethod(
            PluginController::class,
            'handleException'
        );
        $method->setAccessible(true);

        $controller = $this->controller;
        $exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests', $errorCode);

        $handleExceptionReturn = $method->invoke(
            $controller,
            'testAction',
            $exception,
            $this->configMock
        );

        $this->assertEquals(
            'Some error',
            $handleExceptionReturn,
            'wrong error message'
        );
    }

    /**
     * @group unit
     */
    public function testHandleExceptionUsesConfiguredExceptionHandler()
    {
        if (TYPO3::isTYPO95OrHigher()) {
//            $this->markTestSkipped();
        }
        $this->setExceptionHandlerConfig(TestErrorHandler::class);

        $method = new ReflectionMethod(
            PluginController::class,
            'handleException'
        );
        $method->setAccessible(true);

        $controller = $this->controller;
        $exception = new Exception('Exception for tx_rnbase_exception_HandlerForTests');

        $handleExceptionReturn = $method->invoke(
            $controller,
            'testAction',
            $exception,
            $this->configMock
        );

        $this->assertEquals(
            'TestErrorHandler with testAction and Exception for tx_rnbase_exception_HandlerForTests',
            $handleExceptionReturn,
            'wrong error message'
        );
    }

    private function setExceptionHandlerConfig($exceptionHandler = '')
    {
        $extKey = 'rn_base';
        if (!TYPO3::isTYPO95OrHigher()) {
            $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
            $extConfig['exceptionHandler'] = $exceptionHandler;
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base'] = serialize($extConfig);

            return;
        }
        // Ab 9.5 kann man die ExtConfig für die Tests nicht mehr anpassen.
        $this->configurations->getExtensionConfigValue(
            'rn_base',
            'exceptionHandler'
        )->willReturn($exceptionHandler);
    }
}
