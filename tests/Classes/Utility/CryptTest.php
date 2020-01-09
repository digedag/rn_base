<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2015 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('Tx_Rnbase_Utility_Crypt');

/**
 * Mcrypt.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_CryptTest extends tx_rnbase_tests_BaseTestCase
{
    private $backup = array();

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // Function mcrypt_module_open() is deprecated since PHP 7.1 and was removed in PHP 7.2
        // switch to openssl like this https://github.com/contao/core/pull/8589/files

        if (version_compare(PHP_VERSION, '7.1.0', '>')) {
            $this->markTestSkipped(
                'Function mcrypt_module_open() is deprecated since PHP 7.1 and was removed in PHP 7.2'
            );
        }

        $this->backup['encryptionKey']
            = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            = 'FIKUmW4TMTJgcohLr2VZc6fIHD8yZV1Ey8pRurYEJiVErT5'.
              'oYMAXVSxAPRZRZPwXUCroqD7REmnhxC64ck54gfiQP1fj3V';
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            = $this->backup['encryptionKey'];
    }

    /**
     * Test the encrypt method.
     *
     *
     * @group unit
     * @test
     * @dataProvider getCryptionData
     */
    public function testCryption(array $config = array())
    {
        $data = Tx_Rnbase_Domain_Model_Data::getInstance(
            array(
                'uid' => 5,
                'body' => str_shuffle(
                    substr(
                        str_repeat(
                            '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'.LF,
                            32768
                        ),
                        0,
                        10 // 32768
                    )
                ),
            )
        );

        $crypt = Tx_Rnbase_Utility_Crypt::getInstance(
            array_merge(
                array(
                    'cipher' => MCRYPT_BLOWFISH,
                    'mode' => MCRYPT_MODE_ECB,
                    'key' => 'th3S3cr3t',
                ),
                $config
            )
        );

        $crypted = $crypt->encrypt($data);
        self::assertTrue(is_string($crypted));
        self::assertNotEquals($crypted, $data);

        $decrypted = $crypt->decrypt($crypted);
        self::assertEquals($decrypted, $data);
    }

    /**
     * Gets the array for the testCryption testcase.
     *
     * @return array
     */
    public function getCryptionData()
    {
        return array(
            __LINE__ => array(
                'config' => array(
                    'key' => 'FoOB4r',
                    'urlencode' => false,
                    'base64' => false,
                ),
            ),
            __LINE__ => array(
                'config' => array(
                    'key' => 'Crypt',
                    'urlencode' => false,
                    'base64' => true,
                ),
            ),
            __LINE__ => array(
                'config' => array(
                    'key' => 'S3cure',
                    'urlencode' => true,
                    'base64' => false,
                ),
            ),
            __LINE__ => array(
                'config' => array(
                    'key' => 'K4y',
                    'urlencode' => true,
                    'base64' => true,
                ),
            ),
        );
    }
}
