<?php

use Sys25\RnBase\Frontend\Filter\FilterItem;
use Sys25\RnBase\Frontend\Marker\ListBuilder;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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
 * @group unit
 */
class tx_rnbase_tests_rnbase_testcase extends BaseTestCase
{
    public function testMakeInstanceSimpleObject()
    {
        $obj = tx_rnbase::makeInstance(ListBuilder::class);
        $this->assertTrue(is_object($obj), 'Object not instantiated');
    }

    public function testMakeInstanceObjectWithParameters()
    {
        /** @var FilterItem $obj */
        $obj = tx_rnbase::makeInstance(FilterItem::class, 'name', 'value');
        $this->assertTrue(is_object($obj), 'Object not instantiated');
        $this->assertEquals($obj->record['name'], 'name', 'Attribute not set');
        $this->assertEquals($obj->record['value'], 'value', 'Attribute not set');
    }
}
