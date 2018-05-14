<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_PageBrowser');

class tx_rnbase_tests_util_PageBrowser_testcase extends tx_rnbase_tests_BaseTestCase
{
    public function test_getStateSimple()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet($pb->getParamName('pointer'), 3);
        $listSize = 103; //Gesamtgröße der darzustellenden Liste
        $pageSize = 10; //Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(30, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        $parameters->offsetSet($pb->getParamName('pointer'), 10);
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(100, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        // Listenende passt genau auf letzte Seite
        $parameters->offsetSet($pb->getParamName('pointer'), 10);
        $listSize = 100; //Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(90, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(10, $state['limit'], 'Limit ist falsch');

        $parameters->offsetSet($pb->getParamName('pointer'), 0);
        $listSize = 5; //Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(5, $state['limit'], 'Limit ist falsch');
    }

    public function test_getStateWithEmptyListAndNoPointer()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $listSize = 0; //Gesamtgröße der darzustellenden Liste
        $pageSize = 10; //Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);
        $state = $pb->getState();
        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(0, $state['limit'], 'Limit ist falsch');
    }

    public function test_getStateWithPointerOutOfRange()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet($pb->getParamName('pointer'), 11);
        $listSize = 103; //Gesamtgröße der darzustellenden Liste
        $pageSize = 10; //Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(100, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        $parameters->offsetSet($pb->getParamName('pointer'), 13);
        $pb->setState($parameters, $listSize, $pageSize);
        $state = $pb->getState();
        $this->assertEquals(100, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        $listSize = 98; //Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(90, $state['offset']);
        $this->assertEquals(10, $state['limit']);
    }

    public function test_getStateWithIllegalPointer()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet($pb->getParamName('pointer'), -2);
        $listSize = 103; //Gesamtgröße der darzustellenden Liste
        $pageSize = 10; //Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(0, $state['offset']);
        $this->assertEquals(10, $state['limit']);
    }

    public function test_getStateWithSmallList()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet($pb->getParamName('pointer'), 2);
        $listSize = 3; //Gesamtgröße der darzustellenden Liste
        $pageSize = 10; //Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();

        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(3, $state['limit'], 'Limit ist falsch');
    }

    /**
     * @dataProvider dataProviderGetPointer
     * @param int $pointer
     * @param int $expectedPointer
     */
    public function test_getPointer($pointer, $expectedPointer)
    {
        /* @var $pageBrowser tx_rnbase_util_PageBrowser */
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 'test');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
        $parameters->offsetSet($pageBrowser->getParamName('pointer'), $pointer);
        $listSize = 100;
        $pageSize = 10;
        $pageBrowser->setState($parameters, $listSize, $pageSize);

        self::assertSame($expectedPointer, $pageBrowser->getPointer());
    }

    /**
     * @return array
     */
    public function dataProviderGetPointer()
    {
        return array(
            // before first page
            array(-1, 0),
            // at first page
            array(0, 0),
            // inside range
            array(5, 5),
            // last page
            array(10, 10),
            // outside range
            array(11, 10),
        );
    }

    /**
     * @group unit
     */
    public function testGetHttpUtilityClass()
    {
        self::assertEquals(
            tx_rnbase_util_Typo3Classes::getHttpUtilityClass(),
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 'test'), 'getHttpUtilityClass')
        );
    }

    /**
     * @param int $pointer
     * @param bool $pageMarkedAsNotFound
     * @param bool $ignorePageNotFound
     * @group unit
     * @dataProvider dataProviderMarkPageNotFoundIfPointerOutOfRange
     */
    public function testMarkPageNotFoundIfPointerOutOfRange($pointer, $pageMarkedAsNotFound, $ignorePageNotFound)
    {
        $httpUtility = $this->getMock('HttpUtility_Dummy', array('setResponseCode'));
        if ($pageMarkedAsNotFound) {
            $httpUtility
                ->expects($this->once())
                ->method('setResponseCode')
                ->with(404);
        } else {
            $httpUtility
                ->expects($this->never())
                ->method('setResponseCode');
        }

        $pageBrowser = $this->getMock(
            'tx_rnbase_util_PageBrowser',
            array('getHttpUtilityClass'),
            array('test')
        );
        $pageBrowser->expects($pageMarkedAsNotFound ? $this->once() : $this->never())
            ->method('getHttpUtilityClass')
            ->will($this->returnValue($httpUtility));

        $pageBrowser->setPointer($pointer);
        $pageBrowser->setListSize(10);
        $pageBrowser->setPageSize(5);
        // dieser Aufruf stellt fest, ob der Pointer außerhalb des Bereich ist
        $pageBrowser->getState();

        $configurations = $this->getMock('tx_rnbase_configurations', array('convertToUserInt'));
        $configurationArray = array('test.' => array('ignorePageNotFound' => $ignorePageNotFound));
        $configurations->init($configurationArray, null, 'rn_base', 'rn_base');
        $configurations->expects($pageMarkedAsNotFound ? $this->once() : $this->never())
            ->method('convertToUserInt');

        $pageBrowser->markPageNotFoundIfPointerOutOfRange($configurations, 'test.');
    }

    /**
     * @return number[][]|boolean[][]
     */
    public function dataProviderMarkPageNotFoundIfPointerOutOfRange()
    {
        return array(
            array(123, true, false),
            array(2, true, false),
            array(1, false, false),
            array(0, false, false),
            array(123, false, true),
            array(2, false, true),
            array(1, false, true),
            array(0, false, true),
        );
    }
}

class HttpUtility_Dummy
{
    const HTTP_STATUS_404 = 404;
}
