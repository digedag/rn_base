<?php

namespace Sys25\RnBase\Database;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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


class FromTest extends \tx_rnbase_tests_BaseTestCase
{

    /**
     * Tests the getFrom method.
     *
     * @group unit
     * @dataProvider getGetFromTestData
     * @test
     */
    public function testGetFrom($from, array $expects)
    {
        if (!empty($expects['raw']) && 'autofill' == $expects['raw']) {
            $expects['raw'] = $from;
        }

        $fromInstance = From::buildInstance($from);

        $this->assertEquals($expects['table'], $fromInstance->getTableName());
        $this->assertEquals($expects['alias'], $fromInstance->getAlias());
        $this->assertEquals($expects['clause'], $fromInstance->getClause());

    }

    /**
     * Dataprovider for getFrom test.
     *
     * @return array
     */
    public function getGetFromTestData()
    {
        return [
            __LINE__ => [
                'from' => 'tt_content AS CONTENT',
                'expects' => [
                    'raw' => 'autofill',
                    'table' => 'tt_content AS CONTENT', // this is senseless
                    'alias' => 'tt_content AS CONTENT',
                    'clause' => 'tt_content AS CONTENT',
                ],
            ],
            __LINE__ => [
                'from' => [
                    'tt_content AS CONTENT',
                    'tt_content',
                    'CONTENT',
                ],
                'expects' => [
                    'raw' => 'autofill',
                    'table' => 'tt_content',
                    'alias' => 'CONTENT',
                    'clause' => 'tt_content AS CONTENT',
                ],
            ],
            __LINE__ => [
                'from' => [
                    'table' => 'tt_content',
                ],
                'expects' => [
                    'table' => 'tt_content',
                    'alias' => 'tt_content',
                    'clause' => 'tt_content',
                ],
            ],
            __LINE__ => [
                'from' => [
                    'table' => 'tt_content',
                    'alias' => 'C',
                ],
                'expects' => [
                    'table' => 'tt_content',
                    'alias' => 'C',
                    'clause' => 'tt_content AS C',
                ],
            ],
        ];
    }
}
