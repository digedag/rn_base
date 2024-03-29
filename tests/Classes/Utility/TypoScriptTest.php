<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2017-2021 René Nitzsche <rene@system25.de>
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

use Sys25\RnBase\Testing\BaseTestCase;

/**
 * Mcrypt.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 */
class TypoScriptTest extends BaseTestCase
{
    /**
     * Testdata for ts array convertion.
     *
     * @var array
     */
    private static $configArrayWithDot = [
        'lib.' => [
            '10.' => [
                'value' => 'Hello World!',
                'foo.' => [
                    'bar' => 5,
                ],
            ],
            '10' => 'TEXT',
        ],
    ];

    /**
     * Testdata for ts array convertion.
     *
     * @var array
     */
    private static $configArrayWithoutDot = [
        'lib' => [
            '10' => [
                'value' => 'Hello World!',
                'foo' => [
                    'bar' => 5,
                ],
                '_typoScriptNodeValue' => 'TEXT',
            ],
        ],
    ];

    /**
     * Test the convertTypoScriptArrayToPlainArray method.
     *
     * @group unit
     *
     * @test
     */
    public function testConvertTypoScriptArrayToPlainArray()
    {
        $this->assertEquals(
            self::$configArrayWithoutDot,
            TypoScript::convertTypoScriptArrayToPlainArray(
                self::$configArrayWithDot
            )
        );
    }

    /**
     * Test the convertPlainArrayToTypoScriptArray method.
     *
     * @group unit
     *
     * @test
     */
    public function testConvertPlainArrayToTypoScriptArray()
    {
        $this->assertEquals(
            self::$configArrayWithDot,
            TypoScript::convertPlainArrayToTypoScriptArray(
                self::$configArrayWithoutDot
            )
        );
        // converting of conf array with dot should produce the same, without double dot keys!
        $this->assertEquals(
            self::$configArrayWithDot,
            TypoScript::convertPlainArrayToTypoScriptArray(
                self::$configArrayWithDot
            )
        );
    }
}
