<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2015 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Testing\BaseTestCase;

/**
 * tests for tx_rnbase_util_Templates.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusines.de>
 */
class tx_rnbase_tests_action_CacheHandlerDefault_testcase extends BaseTestCase
{
    /**
     * @var array
     */
    private $backup = [];

    protected function setUp(): void
    {
        $this->backup['_GET'] = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->backup['_GET'];
    }

    /**
     * getCacheName method test.
     *
     * @group unit
     * @test
     */
    public function testGetCacheNameFromTs()
    {
        self::assertSame(
            'myext',
            $this->callInaccessibleMethod(
                $this->getHandlerMock(['name' => 'myext']),
                'getCacheName'
            )
        );
    }

    /**
     * getCacheName method test.
     *
     * @group unit
     * @test
     */
    public function testGetCacheNameFromDef()
    {
        self::assertSame(
            'rnbase',
            $this->callInaccessibleMethod(
                $this->getHandlerMock(),
                'getCacheName'
            )
        );
    }

    /**
     * getTimeout method test.
     *
     * @group unit
     * @test
     */
    public function testGetTimeoutFromTs()
    {
        self::assertSame(
            19,
            $this->callInaccessibleMethod(
                $this->getHandlerMock(['expire' => '19']),
                'getTimeout'
            )
        );
    }

    /**
     * getTimeout method test.
     *
     * @group unit
     * @test
     */
    public function testGetTimeoutFromDef()
    {
        self::assertSame(
            60,
            $this->callInaccessibleMethod(
                $this->getHandlerMock(),
                'getTimeout'
            )
        );
    }

    /**
     * getSalt method test.
     *
     * @group unit
     * @test
     */
    public function testGetSaltFromTs()
    {
        self::assertSame(
            '$4lt',
            $this->callInaccessibleMethod(
                $this->getHandlerMock(['salt' => '$4lt']),
                'getSalt'
            )
        );
    }

    /**
     * getSalt method test.
     *
     * @group unit
     * @test
     */
    public function testGetSaltFromDef()
    {
        self::assertSame(
            'default',
            $this->callInaccessibleMethod(
                $this->getHandlerMock(),
                'getSalt'
            )
        );
    }

    /**
     * getIcludeParams method test.
     *
     * @group unit
     * @test
     */
    public function testGetIcludeParamsFromTs()
    {
        self::assertSame(
            [
                'myaction|uid',
            ],
            $this->callInaccessibleMethod(
                $this->getHandlerMock(
                    [
                        'include.' => [
                            'params' => 'myaction|uid',
                        ],
                    ]
                ),
                'getIcludeParams'
            )
        );
    }

    /**
     * getIcludeParams method test.
     *
     * @group unit
     * @test
     */
    public function testGetIcludeParamsFromDef()
    {
        self::assertSame(
            [],
            $this->callInaccessibleMethod(
                $this->getHandlerMock(),
                'getIcludeParams'
            )
        );
    }

    /**
     * getCache method test.
     *
     * @group unit
     * @test
     */
    public function testGetCacheShouldReturnCachingInterfaceInstance()
    {
        self::assertInstanceOf(
            'tx_rnbase_cache_ICache',
            $this->callInaccessibleMethod(
                $this->getHandlerMock(),
                'getCache'
            )
        );
    }

    /**
     * getCacheKey method test.
     *
     * @param string $initialKey
     * @param string $cleanedKey
     *
     * @dataProvider getCleanupCacheKeyData
     * @group unit
     * @test
     */
    public function testCleanupCacheKey(
        $initialKey,
        $cleanedKey,
        array $config = []
    ) {
        self::assertSame(
            $cleanedKey,
            $this->callInaccessibleMethod(
                $this->getHandlerMock($config),
                'cleanupCacheKey',
                $initialKey
            )
        );
    }

    /**
     * Returns the test data for the cleanupCacheKey test.
     *
     * @return array<int, array<string, string, array<string, int>>>
     */
    public function getCleanupCacheKeyData()
    {
        // 124 sign long string
        $s124 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz--ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz';

        return [
            [
                'initialKey' => 'myaction._caching.',
                'cleanedKey' => 'myaction_caching_',
                'config' => [],
            ],
            [
                'initialKey' => 'abcABC!"§$%&/()=?+#*\'-.,_:;',
                'cleanedKey' => 'abcABC_%&_-_',
                'config' => [],
            ],
            [
                'initialKey' => $s124,
                'cleanedKey' => substr($s124, 0, 50 - 33).'-'.md5($s124),
                'config' => ['keylength' => 50],
            ],
        ];
    }

    /**
     * getCacheKey method test.
     *
     * @group unit
     * @test
     */
    public function testGetCacheKey()
    {
        $handler = $this->getHandlerMock(
            [],
            ['getCacheKeyParts']
        );

        $handler
            ->expects($this->once())
            ->method('getCacheKeyParts')
            ->will(
                $this->returnValue(
                    [
                        '5cf65f278437ad017929be07800927d3',
                        '57',
                        'myaction._caching',
                        'salt',
                    ]
                )
            );

        $this->testCleanupCacheKey(
            $this->callInaccessibleMethod(
                $handler,
                'getCacheKey'
            ),
            'AC-4-5cf65f278437ad017929be07800927d3-57-myaction_caching-salt'
        );
    }

    /**
     * getCacheKeyParts method test.
     *
     * @group unit
     * @test
     */
    public function testGetCacheKeyParts()
    {
        $handler = $this->getHandlerMock(
            [
                'salt' => 'wuerze',
                'include.' => [
                    'params' => 'myext|uid',
                ],
            ]
        );
        // the get parameter for myext|uid
        $_GET['myext']['uid'] = 57;

        $keys = $this->callInaccessibleMethod(
            $handler,
            'getCacheKeyParts'
        );

        // at first position has to be a md5 hash
        // we could not test the validity.
        // the value will be diffrent for each typo3 instance and backend/cli
        self::assertSame(32, strlen($keys[0]));
        // at second position are the pluginid
        self::assertEquals(75, $keys[1]);
        // at third are the conf id without trailing dot
        self::assertEquals('myaction._caching', $keys[2]);
        // at fourth are the salt
        self::assertEquals('wuerze', $keys[3]);
        // now check the params
        self::assertEquals('P-myext|uid-57', $keys[4]);
    }

    /**
     * setOutput method test.
     *
     * @group unit
     * @test
     */
    public function testSetOutput()
    {
        self::markTestIncomplete();
    }

    /**
     * getOutput method test.
     *
     * @group unit
     * @test
     */
    public function testGetOutput()
    {
        self::markTestIncomplete();
    }

    /**
     * @param array $config
     *
     * @return PHPUnit_Framework_MockObject_MockObject|tx_rnbase_action_CacheHandlerDefault
     */
    protected function getHandlerMock(
        array $config = [],
        array $methods = []
    ) {
        $handler = $this->getMock(
            'tx_rnbase_action_CacheHandlerDefault',
            array_merge(['getConfigurations', 'getConfId'], $methods)
        );

        // create cobject with the plugin id.
        $cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $cObj->data['uid'] = '75';

        $confId = 'myaction.';
        $configurations = $this->createConfigurations(
            [
                $confId => [
                    '_caching.' => $config,
                ],
            ],
            'rn_base',
            'rn_base',
            $cObj
        );

        $handler
            ->expects($this->atLeastOnce())
            ->method('getConfigurations')
            ->will($this->returnValue($configurations));
        $handler
            ->expects($this->any())
            ->method('getConfId')
            ->will($this->returnValue($confId.'_caching.'));

        return $handler;
    }
}
