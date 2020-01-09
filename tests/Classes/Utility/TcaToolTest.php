<?php
/**
 *  Copyright notice.
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
tx_rnbase::load('Tx_Rnbase_Utility_TcaTool');

/**
 * Tx_Rnbase_Utility_TcaToolTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_TcaToolTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetWizardsReturnsLinkWizardCorrect()
    {
        $linkWizard = Tx_Rnbase_Utility_TcaTool::getWizards(
            '',
            array(
                'link' => array(
                    'params' => array(
                        'blindLinkOptions' => 'file,page,mail,spec,folder',
                    ),
                    'module' => array('urlParameters' => array('newKey' => 'wizard')),
                ),
            )
        );

        $expectedLinkWizard = array(
            '_PADDING' => 2,
            '_VERTICAL' => 1,
            'link' => array(
                'type' => 'popup',
                'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
                'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                'params' => array(
                        'blindLinkOptions' => 'file,page,mail,spec,folder',
                ),
                'module' => array('urlParameters' => array('mode' => 'wizard', 'newKey' => 'wizard')),
            ),
        );
        if (tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            $expectedLinkWizard['link']['icon'] = 'actions-add';
            $expectedLinkWizard['link']['module']['name'] = 'wizard_link';
        } elseif (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            $expectedLinkWizard['link']['icon'] = 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif';
            $expectedLinkWizard['link']['module']['name'] = 'wizard_element_browser';
        } else {
            $expectedLinkWizard['link']['icon'] = 'EXT:t3skin/icons/gfx/link_popup.gif';
            $expectedLinkWizard['link']['module']['name'] = 'wizard_element_browser';
        }
        self::assertEquals(ksort($expectedLinkWizard), ksort($linkWizard), 'link wizard nicht korrekt');
    }

    /**
     * @group unit
     */
    public function testGetWizardsReturnsWizardsWithCorrectScriptOrModuleKey()
    {
        $wizards = Tx_Rnbase_Utility_TcaTool::getWizards(
            '',
            array(
                'add' => 1,
                'edit' => 1,
                'list' => 1,
                'RTE' => 1,
                'colorpicker' => 1,
                'link' => 1,
            )
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

        if (tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::assertEquals('wizard_link', $wizards['link']['module']['name']);
        } else {
            self::assertEquals('wizard_element_browser', $wizards['link']['module']['name']);
        }
    }

    /**
     * @group unit
     */
    public function testGetWizardsReturnsWizardsWithCorrectIcons()
    {
        $wizards = Tx_Rnbase_Utility_TcaTool::getWizards(
            '',
            array(
                'add' => 1,
                'edit' => 1,
                'list' => 1,
                'RTE' => 1,
                'link' => 1,
            )
        );

        if (tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
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
        } elseif (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            self::assertEquals(
                'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
                $wizards['add']['icon']
            );
            self::assertEquals(
                'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
                $wizards['edit']['icon']
            );
            self::assertEquals(
                'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif',
                $wizards['list']['icon']
            );
            self::assertEquals(
                'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
                $wizards['RTE']['icon']
            );
            self::assertEquals(
                'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                $wizards['link']['icon']
            );
        } else {
            self::assertEquals(
                'EXT:t3skin/icons/gfx/add.gif',
                $wizards['add']['icon']
            );
            self::assertEquals(
                'EXT:t3skin/icons/gfx/edit2.gif',
                $wizards['edit']['icon']
            );
            self::assertEquals(
                'EXT:t3skin/icons/gfx/list.gif',
                $wizards['list']['icon']
            );
            self::assertEquals(
                'EXT:t3skin/icons/gfx/wizard_rte.gif',
                $wizards['RTE']['icon']
            );
            self::assertEquals(
                'EXT:t3skin/icons/gfx/link_popup.gif',
                $wizards['link']['icon']
            );
        }
    }

    /**
     * @group unit
     */
    public function testGetWizardsForColorpicker()
    {
        $wizards = Tx_Rnbase_Utility_TcaTool::getWizards('', array('colorpicker' => 1));

        self::assertEquals('colorbox', $wizards['colorpicker']['type']);
    }

    /**
     * @group unit
     */
    public function testGetWizardsForColorpickerAndOverrides()
    {
        $wizards = Tx_Rnbase_Utility_TcaTool::getWizards(
            '',
            array('colorpicker' => array('type' => 'myOwnType'))
        );

        self::assertEquals('myOwnType', $wizards['colorpicker']['type']);
    }
}
