<?php

namespace Sys25\RnBase\ExtBaseFluid\Controller;

use Sys25\RnBase\Testing\BaseTestCase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
 * Sys25\RnBase\Controller\Extbase$CacheTagsTraitTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class ExtbaseControllerWithCacheTagsTraitTest extends BaseTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        if (!method_exists($this, 'getMockForTrait')) {
            self::markTestSkipped('mocking traits is not supported in this phpunit version.');
        }

        \tx_rnbase_util_Misc::prepareTSFE(['force' => true]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $property->setValue(\tx_rnbase_util_TYPO3::getTSFE(), []);
    }

    /**
     * @group functional
     * @TODO: refactor, requires database connection!
     */
    public function testHandleCacheTags()
    {
        $trait = $this->getTrait();

        $settings = [
            'cacheTags' => [
                'controller' => [
                    'action' => [
                        0 => 'firstTag',
                        1 => 'secondTag',
                    ],
                ],
            ],
        ];
        $property = new \ReflectionProperty(ActionController::class, 'settings');
        $property->setAccessible(true);
        $property->setValue($trait, $settings);

        $this->callInaccessibleMethod($trait, 'handleCacheTags');

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $cacheTags = $property->getValue(\tx_rnbase_util_TYPO3::getTSFE());

        self::assertEquals(['firstTag', 'secondTag'], $cacheTags);
    }

    /**
     * @group functional
     * @TODO: refactor, requires database connection!
     */
    public function testHandleCacheTagsIfNotConfigured()
    {
        $trait = $this->getTrait();

        $this->callInaccessibleMethod($trait, 'handleCacheTags');

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $cacheTags = $property->getValue(\tx_rnbase_util_TYPO3::getTSFE());

        self::assertEquals([], $cacheTags);
    }

    /**
     * @group functional
     * @TODO: refactor, requires database connection!
     */
    public function testHandleCacheTagsIfConfiguredForOtherAction()
    {
        $trait = $this->getTrait();

        $settings = [
            'cacheTags' => [
                'controller' => [
                    'otherAction' => [
                        0 => 'firstTag',
                        1 => 'secondTag',
                    ],
                ],
            ],
        ];
        $property = new \ReflectionProperty(ActionController::class, 'settings');
        $property->setAccessible(true);
        $property->setValue($trait, $settings);

        $this->callInaccessibleMethod($trait, 'handleCacheTags');

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $cacheTags = $property->getValue(\tx_rnbase_util_TYPO3::getTSFE());

        self::assertEquals([], $cacheTags);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTrait($actionMethod = 'action')
    {
        $trait = $this->getMockForTrait(CacheTagsTrait::class);

        $mockRequest = $this->getMock(\TYPO3\CMS\Extbase\Mvc\Request::class);
        $mockRequest->expects($this->once())->method('getControllerActionName')->will($this->returnValue('action'));
        $mockRequest->expects($this->once())->method('getControllerName')->will($this->returnValue('Controller'));
        $property = new \ReflectionProperty(ActionController::class, 'request');
        $property->setAccessible(true);
        $property->setValue($trait, $mockRequest);

        return $trait;
    }
}
