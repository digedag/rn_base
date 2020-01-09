<?php
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Network');

/**
 * tx_rnbase_tests_util_Network_testcase.
 *
 * @author          Hannes Bochmann <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_util_Network_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var string
     */
    protected $devIpMaskBackup;

    /**
     * @var string
     */
    protected $remoteAddressBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->devIpMaskBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];
        $this->remoteAddressBackup = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $this->devIpMaskBackup;
        $_SERVER['REMOTE_ADDR'] = $this->remoteAddressBackup;
    }

    /**
     * @group unit
     */
    public function testLocationHeaderUrl()
    {
        self::assertEquals(
            'http://www.google.de/url.html',
            tx_rnbase_util_Network::locationHeaderUrl('http://www.google.de/url.html')
        );
    }

    /**
     * @param string $globalDevIpMask
     * @param string $devIpMask
     * @param string $remoteIp
     * @param bool   $expectedReturn
     * @group unit
     * @dataProvider dataProviderIsDevelopmentIp
     */
    public function testIsDevelopmentIp($globalDevIpMask, $devIpMask, $remoteIp, $expectedReturn)
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $globalDevIpMask;
        self::assertSame($expectedReturn, tx_rnbase_util_Network::isDevelopmentIp($remoteIp, $devIpMask));
    }

    /**
     * @return array
     */
    public function dataProviderIsDevelopmentIp()
    {
        return array(
            array('127.0.0.1', '', '', true, $_SERVER['REMOTE_ADDR'] = '127.0.0.1'),
            array('1.2.3.4', '1.2.3.4', '1.2.3.4', true),
            array('4.3.2.1', '1.2.3.4', '1.2.3.4', true),
            array('4.3.2.1', '4.3.2.1', '1.2.3.4', false),
            array('4.3.2.1', '4.3.2.1', '', false),
            array('4.3.2.1', '', '1.2.3.4', false),
            array('', '1.2.3.4', '1.2.3.4', true),
            array('', '4.3.2.1', '1.2.3.4', false),
            array('4.3.2.1', '', '', false),
            array('', '', '4.3.2.1', false),
        );
    }
}
