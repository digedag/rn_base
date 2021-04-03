<?php

namespace Sys25\RnBase\ExtBaseFluid\Controller;

use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Controller\AbstractController;

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
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class AbstractControllerTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * Test the handleRequest method.
     *
     * @group unit
     * @test
     */
    public function testHandleRequestCallsDoRequest()
    {
        $action = $this->getMockForAbstractClass(AbstractController::class);
        $action
            ->expects(self::once())
            ->method('doRequest')
            ->with()
            ->will(self::returnArgument(0));
        $dummy = new \ArrayObject();
        $reflectionObject = new \ReflectionObject($action);
        $reflectionMethod = $reflectionObject->getMethod('handleRequest');
        $reflectionMethod->setAccessible(true);
        $ret = $reflectionMethod->invokeArgs(
            $action,
            [&$dummy, &$dummy, &$dummy]
        );
        // the handleRequest expects returns the first argument
        // this argument should be null. doRequest has no argument!
        $this->assertSame(null, $ret);
    }

    /**
     * Test the assignToView method.
     *
     * @group unit
     * @test
     */
    public function testAssignToViewShouldStoreDataCorrectly()
    {
        $action = $this->getMockForAbstractClass(AbstractController::class);
        $configuration = \tx_rnbase::makeInstance(Processor::class);
        $action->setConfigurations($configuration);
        $this->callInaccessibleMethod($action, 'assignToView', 'test', '57');
        $this->assertSame('57', $configuration->getViewData()->offsetGet('test'));
    }

    /**
     * Test the getConfigurationValue method.
     *
     * @group unit
     * @test
     */
    public function testGetConfigurationValueShouldCallConfigurationProcessorCorrectly()
    {
        $action = $this->getMockForAbstractClass(AbstractController::class);
        $action->expects($this->once())->method('getTemplateName')->willReturn('action');
        $configuration = $this->getMock(Processor::class);
        $configuration->expects($this->once())->method('get')->with('action.cid')->willReturn('works');
        $action->setConfigurations($configuration);
        $this->assertSame('works', $this->callInaccessibleMethod($action, 'getConfigurationValue', 'cid'));
    }
}
