<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2014 Rene Nitzsche (rene@system25.de)
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

/**
 * parameters testcase.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_tests_parameters_testcase extends tx_rnbase_tests_BaseTestCase
{
    public function testGet()
    {
        $params = array(
            'empty' => '',
            'zero' => '0',
            'seven' => '7',
            'hello' => 'hello',
            'NK_world' => 'world',
        );

        /* @var $parameters \Sys25\RnBase\Frontend\Request\Parameters */
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class, $params);

        $this->assertEquals('', $parameters->get('empty'));
        $this->assertEquals('0', $parameters->get('zero'));
        $this->assertEquals('7', $parameters->get('seven'));
        $this->assertEquals('hello', $parameters->get('hello'));
        $this->assertEquals('world', $parameters->get('world'));
        $this->assertEquals(null, $parameters->get('null'));
    }

    public function testGetInt()
    {
        $params = array(
            'empty' => '',
            'zero' => '0',
            'seven' => '7',
            'hello' => 'hello',
            'NK_world' => 'world',
        );

        /* @var $parameters \Sys25\RnBase\Frontend\Request\Parameters */
        $parameters = tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class, $params);

        $this->assertEquals(0, $parameters->getInt('empty'));
        $this->assertEquals(0, $parameters->getInt('zero'));
        $this->assertEquals(7, $parameters->getInt('seven'));
        $this->assertEquals(0, $parameters->getInt('hello'));
        $this->assertEquals(0, $parameters->getInt('world'));
        $this->assertEquals(0, $parameters->getInt('null'));
    }
}
