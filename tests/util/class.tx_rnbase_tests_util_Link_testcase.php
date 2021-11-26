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

tx_rnbase::load('tx_rnbase_util_Link');

/**
 * Basis Testcase.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_util_Link_testcase extends BaseTestCase
{
    /**
     * @dataProvider getMakeUrlOrTagData
     * @group unit
     * @test
     */
    public function testMakeUrlOrTag($typolink, $absUrl, $schema, $expected, $method = 'makeUrl')
    {
        $cObject = $this->getMock('stdClass', ['typolink']);
        $cObject
            ->expects($this->once())
            ->method('typolink')
            ->will($this->returnValue($typolink));
        $link = $this->getMock(
            'tx_rnbase_util_Link',
            ['getCObj', 'isAbsUrl', 'getAbsUrlSchema']
        );
        $link
            ->expects($this->once())
            ->method('getCObj')
            ->will($this->returnValue($cObject));
        $link
            ->expects($this->once())
            ->method('isAbsUrl')
            ->will($this->returnValue($absUrl));
        $link
            ->expects($this->any())
            ->method('getAbsUrlSchema')
            ->will($this->returnValue($schema));

        $method = 'makeTag' === $method ? 'makeTag' : 'makeUrl';

        $this->assertEquals($expected, $link->{$method}());
    }

    /**
     * Liefert die Daten für den testMakeUrlOrTag testcase.
     *
     * @return array<int, array<string, string|bool>>
     */
    public function getMakeUrlOrTagData()
    {
        return [
            // makeUrl
            [
                'typolink' => 'service/faq.html',
                'absUrl' => false,
                'schema' => 'http://www.system25.de/',
                'expected' => 'service/faq.html',
            ],
            [
                'typolink' => 'service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => 'http://www.system25.de/service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => '//www.system25.de/service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => '/service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => 'http://www.digedag.de/service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => '//www.digedag.de/service/faq.html',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'http://www.system25.de/service/faq.html',
            ],
            [
                'typolink' => '//www.digedag.de/service/faq.html',
                'absUrl' => true,
                'schema' => '',
                'expected' => tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_DIR').'service/faq.html',
            ],
            [
                    'typolink' => '//www.digedag.de/service/faq.html',
                    'absUrl' => true,
                    'schema' => false,
                    'expected' => tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_DIR').'service/faq.html',
            ],
            // makeTag
            [
                'typolink' => '<img src="service/faq.jpg" />',
                'absUrl' => false,
                'schema' => 'http://www.system25.de/',
                'expected' => '<img src="service/faq.jpg" />',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<img src="service/faq.jpg" />',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<img src="http://www.system25.de/service/faq.jpg" />',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="//www.system25.de/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="http://www.digedag.de/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => '<a href="http://www.system25.de/service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="http://www.digedag.de/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => '',
                'expected' => '<a href="'.tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_DIR').'service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            [
                'typolink' => '<a href="http://www.digedag.de/service/faq.html">FAQ</a>',
                'absUrl' => true,
                'schema' => false,
                'expected' => '<a href="'.tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_DIR').'service/faq.html">FAQ</a>',
                'method' => 'makeTag',
            ],
            // invalide tags bleiben unangetastet!
            [
                'typolink' => 'a href="service/faq.html">FAQ</a',
                'absUrl' => true,
                'schema' => 'http://www.system25.de/',
                'expected' => 'a href="service/faq.html">FAQ</a',
                'method' => 'makeTag',
            ],
        ];
    }

    /**
     * @group unit
     * @test
     */
    public function testMakeUrlParam()
    {
        $link = $this->getMock(
            'tx_rnbase_util_Link',
            ['getCObj']
        );

        // check without qualifier
        $link->designatorString = '';
        self::assertSame(
            '&key=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'key', 'value'))
        );
        self::assertSame(
            '&key[sub]=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'key', ['sub' => 'value']))
        );

        // check with qualifier
        $link->designatorString = 'myext';
        self::assertSame(
            '&myext[key]=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'key', 'value'))
        );
        self::assertSame(
            '&myext[key][sub]=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'key', ['sub' => 'value']))
        );

        // override qualifier
        self::assertSame(
            '&yourext[key]=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'yourext::key', 'value'))
        );
        self::assertSame(
            '&yourext[key][sub]=value',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'yourext::key', ['sub' => 'value']))
        );

        // test large recursive param array!
        $largeParamKey = 'lvl0';
        $largeParamValues = [
            'value' => 'Level 0',
            'lvl1' => [
                'value' => 'Level 1',
                'lvl2' => [
                    'value' => 'Level 2',
                    'lvl3' => [
                        'value' => 'Level 3',
                        'lvl4' => [
                            'value' => 'Level 4',
                        ],
                    ],
                ],
            ],
        ];

        // the old functionality: goes only 3 levels down!
        // self::assertEquals('&myext[lvl0][value]=Level 0&myext[p][lvl1][value]=Level 1', rawurldecode($paramStr));

        self::assertEquals(
            '&myext[lvl0][value]=Level 0'.
            '&myext[lvl0][lvl1][value]=Level 1'.
            '&myext[lvl0][lvl1][lvl2][value]=Level 2'.
            '&myext[lvl0][lvl1][lvl2][lvl3][value]=Level 3'.
            '&myext[lvl0][lvl1][lvl2][lvl3][lvl4][value]=Level 4',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', $largeParamKey, $largeParamValues))
        );
        // check qualifier override
        self::assertEquals(
            '&yourext[lvl0][value]=Level 0'.
            '&yourext[lvl0][lvl1][value]=Level 1'.
            '&yourext[lvl0][lvl1][lvl2][value]=Level 2'.
            '&yourext[lvl0][lvl1][lvl2][lvl3][value]=Level 3'.
            '&yourext[lvl0][lvl1][lvl2][lvl3][lvl4][value]=Level 4',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', 'yourext::'.$largeParamKey, $largeParamValues))
        );
        // check without  override
        $link->designatorString = '';
        self::assertEquals(
            '&lvl0[value]=Level 0'.
            '&lvl0[lvl1][value]=Level 1'.
            '&lvl0[lvl1][lvl2][value]=Level 2'.
            '&lvl0[lvl1][lvl2][lvl3][value]=Level 3'.
            '&lvl0[lvl1][lvl2][lvl3][lvl4][value]=Level 4',
            rawurldecode($this->callInaccessibleMethod($link, 'makeUrlParam', $largeParamKey, $largeParamValues))
        );
    }
}
