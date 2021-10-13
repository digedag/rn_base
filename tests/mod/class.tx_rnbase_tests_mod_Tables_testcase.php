<?php

use Sys25\RnBase\Tests\BaseTestCase;

/***************************************************************
 *  Copyright notice
*
*  (c) 2011 Hannes Bochmann (hannes.bochmann@das-medienkombinat.de)
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

class tx_rnbase_tests_mod_Tables_testcase extends BaseTestCase
{
    /**
     * @var tx_rnbase_util_FormTool
     */
    private $oFormTool;

    /**
     * @var string
     */
    private $currentRequestUri;

    /**
     * Initialisiert allgemeine Testdaten.
     */
    public function setUp()
    {
        $this->oFormTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
        $GLOBALS['LOCAL_LANG']['default']['Header Uid'][0] = ['source' => 'Header Uid', 'target' => 'Header Uid'];
        $GLOBALS['LOCAL_LANG']['default']['Header Col1'][0] = ['source' => 'Header Col1', 'target' => 'Header Col1'];

        $this->backupAndSetCurrentRequestUri();

        $this->resetIndependentEnvironmentCache();
    }

    protected function tearDown()
    {
        $this->restoreCurrentRequestUri();
    }

    /**
     * Auf der CLI ist keine URL vorhanden und somit ist auch
     * nicht die normale BE URL vorhanden, welche für die Sortlinks benötigt wird.
     * Also setzen wir die URL einfach fest damit die Tests auch auf der CLI laufen.
     */
    private function backupAndSetCurrentRequestUri()
    {
        $this->currentRequestUri = $_SERVER['REQUEST_URI'];
        $commonBeUrl = '/typo3/mod.php?M=tools_txphpunitbeM1';
        $_SERVER['REQUEST_URI'] = $commonBeUrl;
    }

    private function restoreCurrentRequestUri()
    {
        $_SERVER['REQUEST_URI'] = $this->currentRequestUri;
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn weder
     * ein decorator noch eine callback methode noch ein linker
     * gesetzt ist und die Einträge als models vorliegen?
     */
    public function testPrepareTableWithoutLinkerDecoratorOrCallbackMethodWithEntriesGivenAsModelsReturnsCorrectTable()
    {
        $aEntries = [
                0 => tx_rnbase::makeInstance('tx_rnbase_model_base', [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ]),
                1 => tx_rnbase::makeInstance('tx_rnbase_model_base', [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ]),
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertEquals('Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch.');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn weder
     * ein decorator noch eine callback methode noch ein linker
     * gesetzt ist und die Einträge als array vorliegen?
     */
    public function testPrepareTableWithoutLinkerDecoratorOrCallbackMethodWithEntriesGivenAsArraysReturnsCorrectTable()
    {
        $aEntries = [
                0 => [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ],
                1 => [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ],
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertEquals('Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch.');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn weder
     * eine callback methode noch ein linker gesetzt ist
     * aber ein decorator und die Einträge als models vorliegen?
     */
    public function testPrepareTableWithoutLinkerOrCallbackMethodWithDecoratorAndEntriesGivenAsModelsReturnsCorrectTable()
    {
        $oDecorator = tx_rnbase::makeInstance(
            'tx_rnbase_tests_fixtures_classes_Decorator',
            tx_rnbase::makeInstance('tx_rnbase_tests_fixtures_classes_Mod')
        );
        $aEntries = [
                0 => tx_rnbase::makeInstance('tx_rnbase_model_base', [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ]),
                1 => tx_rnbase::makeInstance('tx_rnbase_model_base', [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ]),
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                        'decorator' => &$oDecorator,
                ],
                'col1' => [
                        'title' => 'Header Col1',
                        'decorator' => &$oDecorator,
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertEquals('Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch.');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('spalte1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('spalte1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn noch keine Sortierung gewählt wurde?
     */
    public function testPrepareTableWithSortableButNothingSelectedReturnsCorrectTable()
    {
        $aEntries = [
                0 => [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ],
                1 => [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ],
        ];
        $aColumns = [
            'uid' => [
                        'title' => 'Header Uid',
                        'sortable' => 'TestPrefix.',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);
        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header - es sollte nur das sorting angegeben sein ohne pfeil
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertContains('&amp;sortField=uid&amp;sortRev=asc">Header Uid</a>', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch.');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn noch keine Sortierung gewählt wurde?
     */
    public function testPrepareTableWithSortableChangesSortingCorrect()
    {
        $_GET['sortField'] = 'uid';
        $aEntries = [
                0 => [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ],
                1 => [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ],
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                        'sortable' => 'TestPrefix.',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertContains('&amp;sortField=uid&amp;sortRev=desc">Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 1. teil');

        //der korrekte pfeil?
        $this->assertContains('icon-actions-move-down', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 2. teil');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn noch keine Sortierung gewählt wurde?
     */
    public function testPrepareTableWithSortableChangesSortingCorrectIfSortRevSetToDesc()
    {
        $_GET['sortField'] = 'uid';
        $_GET['sortRev'] = 'desc';
        $aEntries = [
                0 => [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ],
                1 => [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ],
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                        'sortable' => 'TestPrefix.',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertContains('&amp;sortField=uid&amp;sortRev=asc">Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 1. teil');
        //der korrekte pfeil?
        $this->assertContains('icon-actions-move-up', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 2. teil');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }

    /**
     * Wird eine korrekte Tabelle zurückgeliefert wenn noch keine Sortierung gewählt wurde?
     */
    public function testPrepareTableWithSortableAndExistingUrlParams()
    {
        $_GET['sortField'] = 'uid';
        $_GET['sortRev'] = 'desc';
        //weiterer Param, der erhalten bleiben sollte
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'].'&sortField=title&sortRev=desc&additionalParam=test';

        $aEntries = [
                0 => [
                        'uid' => 1,
                        'col1' => 'col1 Value 1',
                ],
                1 => [
                        'uid' => 2,
                        'col1' => 'col1 Value 2',
                ],
        ];
        $aColumns = [
                'uid' => [
                        'title' => 'Header Uid',
                        'sortable' => 'TestPrefix.',
                ],
                'col1' => [
                        'title' => 'Header Col1',
                ],
        ];
        $aRet = tx_rnbase_mod_Tables::prepareTable($aEntries, $aColumns, $this->oFormTool, []);

        //allgmein
        $this->assertEquals(3, count($aRet[0]), 'Das Array der gesamten Tabelle hat die falsche Anzahl an Elementen.');
        //Header
        $this->assertEquals(2, count($aRet[0][0]), 'Das Array des Headers hat die falsche Anzahl an Elementen.');
        $this->assertContains('&amp;additionalParam=test&amp;sortField=uid&amp;sortRev=asc">Header Uid', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 1. teil');
        //der korrekte pfeil?
        $this->assertContains('icon-actions-move-up', $aRet[0][0][0], 'Die erste Zelle des Headers ist falsch. 2. teil');
        $this->assertEquals('Header Col1', $aRet[0][0][1], 'Die zweite Zelle des Headers ist falsch.');
        //erste Zeile
        $this->assertEquals(2, count($aRet[0][1]), 'Das Array der ersten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(1, $aRet[0][1][0], 'Die erste Zelle der ersten Zeile ist falsch.');
        $this->assertEquals('col1 Value 1', $aRet[0][1][1], 'Die zweite Zelle der ersten Zeile ist falsch.');
        //zweite Zeile
        $this->assertEquals(2, count($aRet[0][2]), 'Das Array der zweiten Zeile hat die falsche Anzahl an Elementen.');
        $this->assertEquals(2, $aRet[0][2][0], 'Die erste Zelle der zweiten Zeile ist falsch.');
        $this->assertEquals('col1 Value 2', $aRet[0][2][1], 'Die zweite Zelle der zweiten Zeile ist falsch.');
    }
}
