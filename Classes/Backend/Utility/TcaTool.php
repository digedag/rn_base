<?php

namespace Sys25\RnBase\Backend\Utility;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2015-2020 René Nitzsche <rene@system25.de>
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

use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase_util_Arrays;

/**
 * TCA Util and wrapper methods.
 *
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TcaTool
{
    /**
     * @var string
     */
    public const ICON_INDEX_TYPO3_87_OR_HIGHER = 'typo3-87-or-higher';

    /**
     * @var string
     */
    public const ICON_INDEX_TYPO3_76_OR_HIGHER = 'typo3-76-or-higher';

    /**
     * @var string
     */
    public const ICON_INDEX_TYPO3_62_OR_HIGHER = 'typo3-62-or-higher';

    public const WIZARD_EDIT = 'edit';

    public const WIZARD_ADD = 'add';

    public const WIZARD_LIST = 'list';

    public const WIZARD_SUGGEST = 'suggest';

    public const WIZARD_RTE = 'RTE';

    public const WIZARD_LINK = 'link';

    public const WIZARD_COLORPICKER = 'colorpicker';

    public const WIZARD_TARGETTABLE = 'targettable';

    /**
     * @var array
     */
    private static $iconsByWizards = [
        'edit' => [
            self::ICON_INDEX_TYPO3_87_OR_HIGHER => 'actions-open',
            self::ICON_INDEX_TYPO3_76_OR_HIGHER => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
            self::ICON_INDEX_TYPO3_62_OR_HIGHER => 'EXT:t3skin/icons/gfx/edit2.gif',
        ],
        'add' => [
            self::ICON_INDEX_TYPO3_87_OR_HIGHER => 'actions-add',
            self::ICON_INDEX_TYPO3_76_OR_HIGHER => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
            self::ICON_INDEX_TYPO3_62_OR_HIGHER => 'EXT:t3skin/icons/gfx/add.gif',
        ],
        'list' => [
            self::ICON_INDEX_TYPO3_87_OR_HIGHER => 'actions-system-list-open',
            self::ICON_INDEX_TYPO3_76_OR_HIGHER => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif',
            self::ICON_INDEX_TYPO3_62_OR_HIGHER => 'EXT:t3skin/icons/gfx/list.gif',
        ],
        'richText' => [
            self::ICON_INDEX_TYPO3_87_OR_HIGHER => 'actions-wizard-rte',
            self::ICON_INDEX_TYPO3_76_OR_HIGHER => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
            self::ICON_INDEX_TYPO3_62_OR_HIGHER => 'EXT:t3skin/icons/gfx/wizard_rte.gif',
        ],
        'link' => [
            self::ICON_INDEX_TYPO3_87_OR_HIGHER => 'actions-wizard-link',
            self::ICON_INDEX_TYPO3_76_OR_HIGHER => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
            self::ICON_INDEX_TYPO3_62_OR_HIGHER => 'EXT:t3skin/icons/gfx/link_popup.gif',
        ],
    ];

    /**
     * Add a wizard to column.
     * Usage:.
     *
     * tx_rnbase::load('Tx_Rnbase_Util_TCA');
     * $tca = new Tx_Rnbase_Util_TCA();
     * $tca->addWizard($tcaTableArray, 'teams', 'add', 'wizard_add', array());
     *
     * @param array  $tcaTable
     * @param string $colName
     * @param string $wizardName
     * @param string $moduleName
     * @param array  $urlParams
     *
     * @deprecated use getWizards()
     */
    public function addWizard(&$tcaTable, $colName, $wizardName, $moduleName, $urlParams = [])
    {
        $tcaTable['columns'][$colName]['config']['wizards'][$wizardName]['module'] = [
            'name' => $moduleName,
            'urlParameters' => $urlParams,
        ];
    }

    /**
     * Creates the wizard config for the tca. Support for TYPO3 7.6 and higher.
     *
     * usage:
     * $myTableTCA = [
     *   'ctrl' => [..]
     *   'columns' => [
     *     'col1' => [..],
     *     ..
     *   ]
     * ];
     * Tx_Rnbase_Utility_TcaTool::configureWizards($myTableTCA, [
     *   'col1' => [
     *       ### overwriting the default label
     *       ### or anything else
     *       'targettable' => 'tx_some_table',
     *       'add' => array('title'  => 'my new title'),
     *       'edit' => TRUE,
     *       'suggest' => TRUE,
     *       'RTE' => ['defaultExtras' => 'richtext[paste|bold...'],
     *   ]
     * ]);
     * return $myTableTCA;
     *
     * @param array $tcaTable complete TCA config array for table
     * @param array $options
     */
    public static function configureWizards(array &$tcaTable, array $options)
    {
        foreach ($options as $col => $wizardOptions) {
            $table = isset($wizardOptions[self::WIZARD_TARGETTABLE]) ? $wizardOptions[self::WIZARD_TARGETTABLE] : '';
            $wizards = self::getWizards($table, $wizardOptions);
            if (\tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
                // suggestWizard
                if (isset($wizards[self::WIZARD_SUGGEST])) {
                    $tcaTable['columns'][$col]['config']['suggestOptions'] = $wizards[self::WIZARD_SUGGEST];
                    unset($wizards[self::WIZARD_SUGGEST]);
                }
                $controls = [self::WIZARD_ADD => 'addRecord', self::WIZARD_EDIT => 'editPopup'];
                foreach ($controls as $wiz => $control) {
                    if (isset($wizards[$wiz])) {
                        $tcaTable['columns'][$col]['config']['fieldControl'][$control] = self::convertWiz2FieldControl(
                            $wiz,
                            $wizards[$wiz],
                            $wizardOptions[$wiz]
                        );
                        unset($wizards[$wiz]);
                    }
                }
            }
            // Add RTE config to columnsOverrides
            if (isset($wizardOptions[self::WIZARD_RTE])) {
                $tcaTable['types'][0]['columnsOverrides'][$col] = \tx_rnbase_util_TYPO3::isTYPO86OrHigher() ?
                    ['config' => ['enableRichtext' => 1, 'richtextConfiguration' => 'default']]
                    :
                    ['defaultExtras' => isset($wizardOptions[self::WIZARD_RTE]['defaultExtras']) ? $wizardOptions[self::WIZARD_RTE]['defaultExtras'] : ''];
                if (\tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
                    unset($wizards[self::WIZARD_RTE]);
                }
            }

            $tcaTable['columns'][$col]['config']['wizards'] = $wizards;
        }
    }

    protected static function convertWiz2FieldControl($type, $wizard, $wizardOptions)
    {
        $control = [
            'disabled' => false,
            'options' => [],
        ];
        if (self::WIZARD_ADD == $type) {
            $control['options'] = $wizard['params'];
        } elseif (self::WIZARD_EDIT == $type) {
            $control['options']['windowOpenParameters'] = $wizard['JSopenParams'];
        }

        if (isset($wizard['title'])) {
            $control['options']['title'] = $wizard['title'];
        }

        return $control;
    }

    /**
     * Creates the wizard config for the tca.
     *
     * usage:
     * ... 'wizards' => Tx_Rnbase_Utility_TcaTool::getWizards(
     *     'mytable',
     *     array(
     *         ### overwriting the default label
     *         ### or anything else
     *         'add' => array('title'  => 'my new title'),
     *         'edit' => TRUE,
     *         'suggest' => TRUE
     *     )
     * ),
     *
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    public static function getWizards($table, array $options = [])
    {
        $wizards = [
            '_PADDING' => 2,
            '_VERTICAL' => 1,
        ];

        if (isset($options[self::WIZARD_EDIT])) {
            $wizards[self::WIZARD_EDIT] = self::getEditWizard($table, $options);
        }

        if (isset($options[self::WIZARD_ADD])) {
            $wizards[self::WIZARD_ADD] = self::getAddWizard($table, $options);
        }

        if (isset($options[self::WIZARD_LIST])) {
            $wizards[self::WIZARD_LIST] = self::getListWizard($table, $options);
        }

        if (isset($options[self::WIZARD_SUGGEST])) {
            $wizards[self::WIZARD_SUGGEST] = self::getSuggestWizard($table, $options);
        }

        if (isset($options[self::WIZARD_RTE])) {
            $wizards[self::WIZARD_RTE] = self::getRichTextWizard($table, $options);
        }

        if (isset($options[self::WIZARD_LINK])) {
            $wizards[self::WIZARD_LINK] = self::getLinkWizard($table, $options);
        }

        if (isset($options[self::WIZARD_COLORPICKER])) {
            $wizards[self::WIZARD_COLORPICKER] = self::getColorPickerWizard($table, $options);
        }

        return $wizards;
    }

    /**
     * @return string
     */
    public static function buildGeneralLabel($label)
    {
        $sysLangFile = TYPO3::isTYPO87OrHigher() ?
            'Resources/Private/Language/locallang_general.xlf' : 'locallang_general.xml';

        return sprintf(
            'LLL:EXT:%s/%s:LGL.%s',
            TYPO3::isTYPO104OrHigher() ? 'core' : 'lang',
            $sysLangFile,
            $label
        );
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getEditWizard($table, array $options = [])
    {
        $wizard = [
            'type' => 'popup',
            'title' => 'Edit entry',
            'icon' => self::getIconByWizard('edit'),
            'popup_onlyOpenIfSelected' => 1,
            'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
        ];
        $wizard = self::addWizardScriptForTypo3Version('edit', $wizard);
        if (is_array($options['edit'])) {
            $wizard =
                tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                    $wizard,
                    $options['edit']
                );
        }

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getAddWizard($table, array $options = [])
    {
        $globalPid = isset($options['globalPid']) ? $options['globalPid'] : false;
        $wizard = [
            'type' => 'script',
            'title' => 'Create new entry',
            'icon' => self::getIconByWizard('add'),
            'params' => [
                'table' => $table,
                'pid' => ($globalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
                'setValue' => 'prepend',
            ],
        ];
        $wizard = self::addWizardScriptForTypo3Version('add', $wizard);
        if (is_array($options['add'])) {
            $wizard =
                tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                    $wizard,
                    $options['add']
                );
        }

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getListWizard($table, array $options = [])
    {
        $globalPid = isset($options['globalPid']) ? $options['globalPid'] : false;
        $wizard = [
            'type' => 'popup',
            'title' => 'List entries',
            'icon' => self::getIconByWizard('list'),
            'params' => [
                'table' => $table,
                'pid' => ($globalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
            ],
            'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
        ];
        $wizard = self::addWizardScriptForTypo3Version('list', $wizard);
        if (is_array($options['list'])) {
            $wizard =
                tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                    $wizard,
                    $options['list']
                );
        }

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getSuggestWizard($table, array $options = [])
    {
        $wizard = [
            'type' => 'suggest',
            'default' => [
                'maxItemsInResultList' => 8,
                // true: LIKE %term% false: LIKE term%
                'searchWholePhrase' => true,
            ],
        ];
        if (is_array($options['suggest'])) {
            $wizard =
                tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                    $wizard,
                    $options['suggest']
                );
        }

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getRichTextWizard($table, array $options = [])
    {
        $wizard = [
            'notNewRecords' => 1,
            'RTEonly' => 1,
            'type' => 'script',
            'title' => 'Full screen Rich Text Editing',
            'icon' => self::getIconByWizard('richText'),
        ];
        $wizard = self::addWizardScriptForTypo3Version('rte', $wizard);

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getLinkWizard($table, array $options = [])
    {
        $wizard = [
            'type' => 'popup',
            'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
            'icon' => self::getIconByWizard('link'),
            'script' => 'browse_links.php?mode=wizard',
            'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
            'params' => [
                'blindLinkOptions' => '',
            ],
            'module' => ['urlParameters' => ['mode' => 'wizard']],
        ];
        if (is_array($options['link'])) {
            $wizard =
                tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                    $wizard,
                    $options['link']
                );
        }

        $wizard = self::addWizardScriptForTypo3Version(
            TYPO3::isTYPO87OrHigher() ? 'link' : 'element_browser',
            $wizard
        );

        return $wizard;
    }

    /**
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    protected static function getColorPickerWizard($table, array $options = [])
    {
        $wizard = [
            'type' => 'colorbox',
            'title' => 'Colorpicker',
            'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
        ];

        if (is_array($options['colorpicker'])) {
            $wizard = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
                $wizard,
                $options['colorpicker']
            );
        }

        $wizard = self::addWizardScriptForTypo3Version('colorpicker', $wizard);

        return $wizard;
    }

    /**
     * @param string $wizard
     *
     * @return string
     */
    protected static function getIconByWizard($wizard)
    {
        if (TYPO3::isTYPO87OrHigher()) {
            $iconIndexByTypo3Version = self::ICON_INDEX_TYPO3_87_OR_HIGHER;
        } else {
            $iconIndexByTypo3Version = self::ICON_INDEX_TYPO3_76_OR_HIGHER;
        }

        return self::$iconsByWizards[$wizard][$iconIndexByTypo3Version];
    }

    /**
     * @param string $wizardType
     * @param array  $wizardConfig
     *
     * @return array
     */
    protected static function addWizardScriptForTypo3Version($wizardType, array $wizardConfig)
    {
        $completeWizardName = 'wizard_'.$wizardType;
        $wizardConfig['module']['name'] = $completeWizardName;
        if (isset($wizardConfig['script'])) {
            unset($wizardConfig['script']);
        }

        return $wizardConfig;
    }
}
