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
     * @var string
     */
    private $encryptionKeyBackup;

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = [];
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters'] = [];
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = '';
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'] = '';
        }
        tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)->setConfiguration(array(
            'excludedParameters' => [],
            'requireCacheHashPresenceParameters' => [],
        ));

        $this->encryptionKeyBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'Tx_Rnbase_Utility_CacheTest';
    }

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $this->encryptionKeyBackup;
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHash()
    {
        if (!\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run since TYPO3 9');
        }

        $property = new ReflectionProperty(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class, 'excludedParameters');
        $property->setAccessible(true);
        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame([], $excludedParameters);
        self::assertSame([], $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters']);

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['john', 'doe'], $excludedParameters);
        self::assertSame(['john' ,'doe'], $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters']);
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHashBeforeTypo39()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run below TYPO3 9');
        }

        $property = new ReflectionProperty(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class, 'excludedParameters');
        $property->setAccessible(true);
        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame([], $excludedParameters);
        self::assertSame('', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['john', 'doe'], $excludedParameters);
        self::assertSame('john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHashIfSomeExistAlready()
    {
        if (!\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run since TYPO3 9');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = ['L', 'gclid'];
        $property = new ReflectionProperty(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class, 'excludedParameters');
        $property->setAccessible(true);
        $property->setValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class),
            ['L', 'gclid']
        );

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['L', 'gclid', 'john', 'doe'], $excludedParameters);
        self::assertSame(
            ['L', 'gclid', 'john', 'doe'],
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters']
        );
    }

    /**
     * @group unit
     */
    public function testAddExcludedParametersForCacheHashIfSomeExistAlreadyBeforeTypo39()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run below TYPO3 9');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'] = 'L, gclid';
        $property = new ReflectionProperty(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class, 'excludedParameters');
        $property->setAccessible(true);
        $property->setValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class),
            ['L', 'gclid']
        );

        Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array('john', 'doe'));

        $excludedParameters = $property->getValue(
            tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['L', 'gclid', 'john', 'doe'], $excludedParameters);
        self::assertSame('L, gclid,john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters']);
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

    /**
     * @group unit
     */
    public function testAddCacheHashRequiredParameters()
    {
        if (!\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run since TYPO3 9');
        }

        $property = new \ReflectionProperty(
            \TYPO3\CMS\Frontend\Page\CacheHashCalculator::class,
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame([], $requiredParameters);
        self::assertSame([], $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters']);

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(array('john', 'doe'));

        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['john', 'doe'], $requiredParameters);
        self::assertSame(
            ['john', 'doe'],
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters']
        );
    }

    /**
     * @group unit
     */
    public function testAddCacheHashRequiredParametersBeforeTypo39()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run below TYPO3 9');
        }

        $property = new \ReflectionProperty(
            \TYPO3\CMS\Frontend\Page\CacheHashCalculator::class,
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame([], $requiredParameters);
        self::assertSame('', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(array('john', 'doe'));

        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['john', 'doe'], $requiredParameters);
        self::assertSame('john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);
    }

    /**
     * @group unit
     */
    public function testAddCacheHashRequiredParametersIfSomeExistAlready()
    {
        if (!\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run since TYPO3 9');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters'] = ['L', 'gclid'];
        $property = new \ReflectionProperty(
            \TYPO3\CMS\Frontend\Page\CacheHashCalculator::class,
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = $property->setValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class),
            ['L', 'gclid']
        );

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(array('john', 'doe'));

        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['L', 'gclid', 'john', 'doe'], $requiredParameters);
        self::assertSame(
            ['L', 'gclid', 'john', 'doe'],
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['requireCacheHashPresenceParameters']
        );
    }

    /**
     * @group unit
     */
    public function testAddCacheHashRequiredParametersIfSomeExistAlreadyBeforeTypo39()
    {
        if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            self::markTestSkipped('This test is designed to run below TYPO3 9');
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters'] = 'L, gclid';
        $property = new \ReflectionProperty(
            \TYPO3\CMS\Frontend\Page\CacheHashCalculator::class,
            'requireCacheHashPresenceParameters'
        );
        $property->setAccessible(true);
        $requiredParameters = $property->setValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class),
            ['L', 'gclid']
        );

        Tx_Rnbase_Utility_Cache::addCacheHashRequiredParameters(array('john', 'doe'));

        $requiredParameters = $property->getValue(
            \tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)
        );

        self::assertSame(['L', 'gclid', 'john', 'doe'], $requiredParameters);
        self::assertSame('L, gclid,john,doe', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashRequiredParameters']);
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheTagsToPage()
    {
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Utility_Cache');

        self::assertSame('test', $utility->addCacheTagsToPage('test', array(0 => 'firstTag', 1 => 'secondTag')));

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        self::assertSame(array('firstTag', 'secondTag'), $property->getValue(\tx_rnbase_util_TYPO3::getTSFE()));
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testAddCacheTagsToPageIfNoConfiguration()
    {
        $utility = tx_rnbase::makeInstance('Tx_Rnbase_Utility_Cache');

        self::assertSame('test', $utility->addCacheTagsToPage('test', array()));

        $property = new \ReflectionProperty(get_class(\tx_rnbase_util_TYPO3::getTSFE()), 'pageCacheTags');
        $property->setAccessible(true);
        self::assertEmpty($property->getValue(\tx_rnbase_util_TYPO3::getTSFE()));
    }
}
