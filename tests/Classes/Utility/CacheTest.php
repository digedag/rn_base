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
     * @var string $cHashExcludedParametersBackup
     */
    private $cHashRequiredParameters = '';

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

        $this->cHashRequiredParameters = $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'] = '';

        tx_rnbase_util_Misc::prepareTSFE(['force' => true]);
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = $this->cHashExcludedParametersBackup;
        tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')->setConfiguration([
            'excludedParameters' => explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'])
        ]
        );

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $this->encryptionKeyBackup;

        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'] = $this->cHashRequiredParameters;
        \tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')->setConfiguration([
            'requireCacheHashPresenceParameters' =>
                explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'])
        ]
        );

        $property = new ReflectionProperty(get_class(tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        $property->setValue(\tx_rnbase_util_TYPO3::getTSFE(), []);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddExcludedParametersForCacheHash()
    {
        $property = new ReflectionProperty('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator', 'excludedParameters');
        $property->setAccessible(true);
        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayNotHasKey('john', $excludedParameters);
        self::assertArrayNotHasKey('doe', $excludedParameters);
        self::assertSame('', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(['john', 'doe']);

        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayHasKey('john', $excludedParameters);
        self::assertArrayHasKey('doe', $excludedParameters);

        self::assertSame('john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
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

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(['john', 'doe']);

        $excludedParameters = array_flip($property->getValue(tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayHasKey('john', $excludedParameters);
        self::assertArrayHasKey('doe', $excludedParameters);
        self::assertArrayHasKey('L', $excludedParameters);

        self::assertSame('L,john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testGenerateCacheHashForUrlQueryString()
    {
        $cacheHash = Tx_Rnbase_Utility_Cache::generateCacheHashForUrlQueryString('id=123&rn_base[parameter]=test');
        self::assertTrue(is_string($cacheHash));
        self::assertEquals(32, strlen($cacheHash));
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheHashRequiredParameters()
    {
        $property = new \ReflectionProperty(
            'TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator',
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = array_flip($property->getValue(
            \tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')
        ));

        self::assertArrayNotHasKey('john', $requiredParameters);
        self::assertArrayNotHasKey('doe', $requiredParameters);
        self::assertSame('', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(['john', 'doe']);

        $requiredParameters = array_flip($property->getValue(\tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')));

        self::assertArrayHasKey('john', $requiredParameters);
        self::assertArrayHasKey('doe', $requiredParameters);

        self::assertSame('john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheHashRequiredParametersIfSomeExistAlready()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'] = 'L';
        $property = new \ReflectionProperty(
            'TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator',
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = array_flip(
            $property->getValue(\tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')
        ));

        self::assertArrayNotHasKey('john', $requiredParameters);
        self::assertArrayNotHasKey('doe', $requiredParameters);
        self::assertSame('L', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(['john', 'doe']);

        $requiredParameters = array_flip(
            $property->getValue(\tx_rnbase::makeInstance('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')
        ));

        self::assertArrayHasKey('john', $requiredParameters);
        self::assertArrayHasKey('doe', $requiredParameters);
        self::assertArrayHasKey('L', $requiredParameters);

        self::assertSame('L,john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheTagsToPage()
    {
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Utility_Cache');

        self::assertSame('test', $utility->addCacheTagsToPage('test', [0 => 'firstTag', 1 => 'secondTag']));

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        self::assertSame(['firstTag', 'secondTag'], $property->getValue(\tx_rnbase_util_TYPO3::getTSFE()));
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheTagsToPageIfNoConfiguration()
    {
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Utility_Cache');

        self::assertSame('test', $utility->addCacheTagsToPage('test', []));

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        self::assertEmpty($property->getValue(\tx_rnbase_util_TYPO3::getTSFE()));
    }
}
