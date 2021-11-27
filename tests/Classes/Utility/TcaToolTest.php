<?php

namespace Sys25\RnBase\Utility;

/*
 *  Copyright notice.
 *
 *  (c) 2016-2021 René Nitzsche <rene@system25.de>
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
 */

use Sys25\RnBase\Backend\Utility\TcaTool;
use Sys25\RnBase\Testing\BaseTestCase;

/**
 * Tx_Rnbase_Utility_TcaToolTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class TcaToolTest extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetWizardsReturnsLinkWizardCorrect()
    {
        $linkWizard = TcaTool::getWizards(
            '',
            [
                'link' => [
                    'params' => [
                        'blindLinkOptions' => 'file,page,mail,spec,folder',
                    ],
                    'module' => ['urlParameters' => ['newKey' => 'wizard']],
                ],
            ]
        );

        $expectedLinkWizard = [
            '_PADDING' => 2,
            '_VERTICAL' => 1,
            'link' => [
                'type' => 'popup',
                'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
                'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                'params' => [
                        'blindLinkOptions' => 'file,page,mail,spec,folder',
                ],
                'module' => ['urlParameters' => ['mode' => 'wizard', 'newKey' => 'wizard']],
            ],
        ];
        $expectedLinkWizard['link']['icon'] = 'actions-add';
        $expectedLinkWizard['link']['module']['name'] = 'wizard_link';
        self::assertEquals(ksort($expectedLinkWizard), ksort($linkWizard), 'link wizard nicht korrekt');
    }

    /**
     * @group unit
     */
    public function testGetWizardsReturnsWizardsWithCorrectScriptOrModuleKey()
    {
        $wizards = TcaTool::getWizards(
            '',
            [
                'add' => 1,
                'edit' => 1,
                'list' => 1,
                'RTE' => 1,
                'colorpicker' => 1,
                'link' => 1,
            ]
        );

        self::assertArrayNotHasKey('script', $wizards['add']);
        self::assertArrayNotHasKey('script', $wizards['edit']);
        self::assertArrayNotHasKey('script', $wizards['list']);
        self::assertArrayNotHasKey('script', $wizards['RTE']);
        self::assertArrayNotHasKey('script', $wizards['link']);
        self::assertEquals('wizard_add', $wizards['add']['module']['name']);
        self::assertEquals('wizard_edit', $wizards['edit']['module']['name']);
        self::assertEquals('wizard_list', $wizards['list']['module']['name']);
        self::assertEquals('wizard_rte', $wizards['RTE']['module']['name']);
        self::assertEquals('wizard_colorpicker', $wizards['colorpicker']['module']['name']);

        self::assertEquals('wizard_link', $wizards['link']['module']['name']);
    }

    /**
     * @group unit
     */
    public function testGetWizardsReturnsWizardsWithCorrectIcons()
    {
        $wizards = TcaTool::getWizards(
            '',
            [
                'add' => 1,
                'edit' => 1,
                'list' => 1,
                'RTE' => 1,
                'link' => 1,
            ]
        );

        self::assertEquals(
            'actions-add',
            $wizards['add']['icon']
        );
        self::assertEquals(
            'actions-open',
            $wizards['edit']['icon']
        );
        self::assertEquals(
            'actions-system-list-open',
            $wizards['list']['icon']
        );
        self::assertEquals(
            'actions-wizard-rte',
            $wizards['RTE']['icon']
        );
        self::assertEquals(
            'actions-wizard-link',
            $wizards['link']['icon']
        );
    }

    /**
     * @group unit
     */
    public function testGetWizardsForColorpicker()
    {
        $wizards = TcaTool::getWizards('', ['colorpicker' => 1]);

        self::assertEquals('colorbox', $wizards['colorpicker']['type']);
    }

    /**
     * @group unit
     */
    public function testGetWizardsForColorpickerAndOverrides()
    {
        $wizards = TcaTool::getWizards(
            '',
            ['colorpicker' => ['type' => 'myOwnType']]
        );

        self::assertEquals('myOwnType', $wizards['colorpicker']['type']);
    }
}
