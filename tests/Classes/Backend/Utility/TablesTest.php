<?php

namespace Sys25\RnBase\Backend\Utility;

use Prophecy\PhpUnit\ProphecyTrait;
use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\LanguageTool;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2016-2025 Rene Nitzsche (rene@system25.de)
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

class TablesTest extends BaseTestCase
{
    use ProphecyTrait;

    /**
     * @group unit
     */
    public function testPrepareTable()
    {
        $lang = $this->prophesize(LanguageTool::class);
        $lang->getLL('label_uid')->willReturn('LABEL_UID');
        $lang->getLL('Name')->willReturn('NAME');
        $lang->getLL('Other')->willReturn('Other');
        /* @var $tablesUtil Tables */
        $tablesUtil = tx_rnbase::makeInstance(Tables::class, $lang->reveal());
        $entries = [
            tx_rnbase::makeInstance(DataModel::class, ['uid' => 2, 'name' => 'foo']),
            tx_rnbase::makeInstance(DataModel::class, ['uid' => 5, 'name' => 'bar']),
        ];
        $columns = [
            'uid' => ['title' => 'label_uid'],
            'name' => ['title' => 'Name'],
            'other' => ['title' => 'Other', 'method' => 'getUid'],
        ];
        $options = [];
        $options['checkbox'] = 1;

        // TODO: refactor, IconFactory not initialized
        // $options['dontcheck'][2] = 'XX';
        $formTool = tx_rnbase::makeInstance(ToolBox::class);

        $result = $tablesUtil->prepareTable($entries, $columns, $formTool, $options);
        $tableData = $result[0];
        $this->assertEquals(3, count($tableData), 'Number of rows wrong');

        // Header prüfen
        $this->assertEquals(4, count($tableData[0]), 'Number of cols wrong');
        $this->assertEquals('&nbsp;', $tableData[0][0], 'Unexpected title for column 1');
        $this->assertEquals('LABEL_UID', $tableData[0][1], 'Unexpected title for column 2');
        $this->assertEquals('NAME', $tableData[0][2], 'Unexpected title for column 3');

        // erste Zeile
        $this->assertEquals(4, count($tableData[1]), 'Number of cols wrong');
        // TODO: refactor, IconFactory not initialized
        // $this->assertContains('Info: XX', $tableData[1][0], 'Unexpected title for row 1');
        // $this->assertStringStartsNotWith('<input type="checkbox"', $tableData[1][0], 'Checkbox found');
        $this->assertEquals('2', $tableData[1][1], 'Unexpected uid');
        $this->assertEquals('foo', $tableData[1][2], 'Unexpected name');
        $this->assertEquals('2', $tableData[1][3], 'Unexpected other');

        $this->assertEquals(4, count($tableData[2]), 'Number of cols wrong');
        $this->assertStringStartsWith('<input type="checkbox"', $tableData[2][0], 'Checkbox not found');
        $this->assertEquals('5', $tableData[2][1], 'Unexpected uid');
        $this->assertEquals('bar', $tableData[2][2], 'Unexpected name');
        $this->assertEquals('5', $tableData[2][3], 'Unexpected other');

        // \tx_rnbase_util_Debug::debug($tableData,__FILE__.':'.__LINE__); // TODO: remove me
    }
}
