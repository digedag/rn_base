<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2016 René Nitzsche <rene@system25.de>
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
tx_rnbase::load('Tx_Rnbase_Utility_Cache');

/**
 * Tx_Rnbase_Utility_MailTest
 *
 * @package         TYPO3
 * @subpackage      Tx_Rnbase
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_CacheTest extends tx_rnbase_tests_BaseTestCase
{

    /**
     * @var string $cHashExcludedParametersBackup
     */
    private $cHashExcludedParametersBackup = '';

    /**
     * @var string
     */
    private $encryptionKeyBackup;

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->cHashExcludedParametersBackup = $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = '';

        $this->encryptionKeyBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'Tx_Rnbase_Utility_CacheTest';
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = $this->cHashExcludedParametersBackup;
        tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')->setConfiguration(array(
            'excludedParameters' => explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'])));

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $this->encryptionKeyBackup;
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHash()
    {
        $property = new ReflectionProperty('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator', 'excludedParameters');
        $property->setAccessible(true);
        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayNotHasKey('john', $excludedParameters);
        self::assertArrayNotHasKey('doe', $excludedParameters);
        self::assertSame('', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayHasKey('john', $excludedParameters);
        self::assertArrayHasKey('doe', $excludedParameters);

        self::assertSame('john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHashIfSomeExistAlready()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = 'L';
        $property = new ReflectionProperty('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator', 'excludedParameters');
        $property->setAccessible(true);
        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayNotHasKey('john', $excludedParameters);
        self::assertArrayNotHasKey('doe', $excludedParameters);
        self::assertSame('L', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayHasKey('john', $excludedParameters);
        self::assertArrayHasKey('doe', $excludedParameters);
        self::assertArrayHasKey('L', $excludedParameters);

        self::assertSame('L,john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
    }

    /**
     * @group unit
     */
    public function testGenerateCacheHashForUrlQueryString()
    {
        $cacheHash = Tx_Rnbase_Utility_Cache::generateCacheHashForUrlQueryString('id=123&rn_base[parameter]=test');
        self::assertTrue(is_string($cacheHash));
        self::assertEquals(32, strlen($cacheHash));
    }
}
