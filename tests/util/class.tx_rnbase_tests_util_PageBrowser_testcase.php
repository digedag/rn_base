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

use Sys25\RnBase\Testing\BaseTestCase;

class tx_rnbase_tests_util_PageBrowser_testcase extends BaseTestCase
{
    public function testGetStateSimple()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $parameters->offsetSet($pb->getParamName('pointer'), 3);
        $listSize = 103; // Gesamtgröße der darzustellenden Liste
        $pageSize = 10; // Größe einer Seite
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
        $listSize = 100; // Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(90, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(10, $state['limit'], 'Limit ist falsch');

        $parameters->offsetSet($pb->getParamName('pointer'), 0);
        $listSize = 5; // Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(5, $state['limit'], 'Limit ist falsch');
    }

    public function testGetStateWithEmptyListAndNoPointer()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $listSize = 0; // Gesamtgröße der darzustellenden Liste
        $pageSize = 10; // Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);
        $state = $pb->getState();
        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(0, $state['limit'], 'Limit ist falsch');
    }

    public function testGetStateWithPointerOutOfRange()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $parameters->offsetSet($pb->getParamName('pointer'), 11);
        $listSize = 103; // Gesamtgröße der darzustellenden Liste
        $pageSize = 10; // Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(100, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        $parameters->offsetSet($pb->getParamName('pointer'), 13);
        $pb->setState($parameters, $listSize, $pageSize);
        $state = $pb->getState();
        $this->assertEquals(100, $state['offset']);
        $this->assertEquals(10, $state['limit']);

        $listSize = 98; // Gesamtgröße der darzustellenden Liste
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(90, $state['offset']);
        $this->assertEquals(10, $state['limit']);
    }

    public function testGetStateWithIllegalPointer()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $parameters->offsetSet($pb->getParamName('pointer'), -2);
        $listSize = 103; // Gesamtgröße der darzustellenden Liste
        $pageSize = 10; // Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();
        $this->assertEquals(0, $state['offset']);
        $this->assertEquals(10, $state['limit']);
    }

    public function testGetStateWithSmallList()
    {
        $pb = new tx_rnbase_util_PageBrowser('test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
        $parameters->offsetSet($pb->getParamName('pointer'), 2);
        $listSize = 3; // Gesamtgröße der darzustellenden Liste
        $pageSize = 10; // Größe einer Seite
        $pb->setState($parameters, $listSize, $pageSize);

        $state = $pb->getState();

        $this->assertEquals(0, $state['offset'], 'Offset ist falsch');
        $this->assertEquals(3, $state['limit'], 'Limit ist falsch');
    }

    /**
     * @dataProvider dataProviderGetPointer
     *
     * @param int $pointer
     * @param int $expectedPointer
     */
    public function testGetPointer($pointer, $expectedPointer)
    {
        /* @var $pageBrowser tx_rnbase_util_PageBrowser */
        $pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 'test');
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
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
        return [
            // before first page
            [-1, 0],
            // at first page
            [0, 0],
            // inside range
            [5, 5],
            // last page
            [10, 10],
            // outside range
            [11, 10],
        ];
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
     * @param int  $pointer
     * @param bool $pageMarkedAsNotFound
     * @param bool $ignorePageNotFound
     *
     * @group unit
     *
     * @dataProvider dataProviderMarkPageNotFoundIfPointerOutOfRange
     */
    public function testMarkPageNotFoundIfPointerOutOfRange($pointer, $pageMarkedAsNotFound, $ignorePageNotFound)
    {
        $httpUtility = $this->getMock('HttpUtility_Dummy', ['setResponseCode']);
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
            ['getHttpUtilityClass'],
            ['test']
        );
        $pageBrowser->expects($pageMarkedAsNotFound ? $this->once() : $this->never())
            ->method('getHttpUtilityClass')
            ->will($this->returnValue($httpUtility));

        $pageBrowser->setPointer($pointer);
        $pageBrowser->setListSize(10);
        $pageBrowser->setPageSize(5);
        // dieser Aufruf stellt fest, ob der Pointer außerhalb des Bereich ist
        $pageBrowser->getState();

        $configurations = $this->getMock('tx_rnbase_configurations', ['convertToUserInt']);
        $configurationArray = ['test.' => ['ignorePageNotFound' => $ignorePageNotFound]];
        $configurations->init($configurationArray, null, 'rn_base', 'rn_base');
        $configurations->expects($pageMarkedAsNotFound ? $this->once() : $this->never())
            ->method('convertToUserInt');

        $pageBrowser->markPageNotFoundIfPointerOutOfRange($configurations, 'test.');
    }

    /**
     * @return number[][]|bool[][]
     */
    public function dataProviderMarkPageNotFoundIfPointerOutOfRange()
    {
        return [
            [123, true, false],
            [2, true, false],
            [1, false, false],
            [0, false, false],
            [123, false, true],
            [2, false, true],
            [1, false, true],
            [0, false, true],
        ];
    }
}

class HttpUtility_Dummy
{
    public const HTTP_STATUS_404 = 404;
}
