<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2015 René Nitzsche <rene@system25.de>
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

/**
 * TCA Util and wrapper methods
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_TcaTool {

	/**
	 * Add a wizard to column.
	 * Usage:
	 *
	 * tx_rnbase::load('Tx_Rnbase_Util_TCA');
	 * $tca = new Tx_Rnbase_Util_TCA();
	 * $tca->addWizard($tcaTableArray, 'teams', 'add', 'wizard_add', array());
	 *
	 * @param array &$tcaTable
	 * @param string $colName
	 * @param string $wizardName
	 * @param string $moduleName
	 * @param array $urlParams
	 * @return void
	 * @deprecated use getWizards()
	 */
	public function addWizard(&$tcaTable, $colName, $wizardName, $moduleName, $urlParams = array()) {
		if(\tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$tcaTable['columns'][$colName]['config']['wizards'][$wizardName]['module'] = array(
					'name' => $moduleName,
					'urlParameters' => $urlParams
			);
		}
		else {
			$tcaTable['columns'][$colName]['config']['wizards'][$wizardName]['script'] =
					$moduleName . '.php?' . http_build_query($urlParams);
		}
	}

	/**
	 * Creates the wizard config for the tca
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
	 * @param 	string 	$table
	 * @param 	array 	$options
	 * @return 	array
	 */
	public static function getWizards($table, array $options = array()) {
		$globalPid = isset($options['globalPid']) ? $options['globalPid'] : false;
		$wizards = array (
			'_PADDING' => 2,
			'_VERTICAL' => 1,
		);

		if(isset($options['edit'])) {
			$wizards['edit'] = array (
				'type' => 'popup',
				'title' => 'Edit entry', // LLL:EXT:mketernit/locallang.
				'icon' => tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif' :
				'edit2.gif',
				'popup_onlyOpenIfSelected' => 1,
				'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
			);
			$wizards['edit'] = self::addWizardScriptForTypo3Version('edit', $wizards['edit']);
			if (is_array($options['edit'])) {
				$wizards['edit'] =
					tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
						$wizards['edit'], $options['edit']
					);
			}
		}

		if(isset($options['add'])) {
			$wizards['add'] = array (
				'type' => 'script',
				'title' => 'Create new entry',
				'icon' => tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif' :
				'add.gif',
				'params' => array (
					'table' => $table,
					'pid' => ($globalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
					'setValue' => 'prepend',
				),
			);
			$wizards['add'] = self::addWizardScriptForTypo3Version('add', $wizards['add']);
			if (is_array($options['add'])) {
				$wizards['add'] =
					tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
						$wizards['add'], $options['add']
					);
			}
		}

		if(isset($options['list'])) {
			$wizards['list'] = array (
				'type' => 'popup',
				'title' => 'List entries',
				'icon' => tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif' :
				'list.gif',
				'params' => array (
					'table' => $table,
					'pid' => ($globalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
				),
				'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
			);
			$wizards['list'] = self::addWizardScriptForTypo3Version('list', $wizards['list']);
			if (is_array($options['list'])) {
				$wizards['list'] =
					tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
						$wizards['list'], $options['list']
					);
			}
		}

		if(isset($options['suggest'])) {
			$wizards['suggest'] = array (
				'type' => 'suggest',
				'default' => array(
					'maxItemsInResultList' => 8,
					'searchWholePhrase' => true, // true: LIKE %term% false: LIKE term%
				),
			);
			if (is_array($options['suggest'])) {
				$wizards['suggest'] =
					tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
						$wizards['suggest'], $options['suggest']
					);
			}
		}

		if(isset($options['RTE'])) {
			$wizards['RTE'] = Array(
				'notNewRecords' => 1,
				'RTEonly' => 1,
				'type' => 'script',
				'title' => 'Full screen Rich Text Editing',
				'icon' => tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif' :
				'wizard_rte2.gif',
			);
			$wizards['RTE'] = self::addWizardScriptForTypo3Version('rte', $wizards['RTE']);
		}

		if(isset($options['link'])) {
			$wizards['link'] = Array(
				'type' => 'popup',
				'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
				'icon' => tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif' :
				'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
				'params' => Array(
					'blindLinkOptions' => '',
				)
			);
			if (is_array($options['link'])) {
				$wizards['link'] =
					tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
						$wizards['link'], $options['link']
					);
			}

			$wizards['link'] = self::addWizardScriptForTypo3Version('link', $wizards['link']);
		}

		if(isset($options['colorpicker'])) {
			$wizards['colorpicker'] = Array(
				'type' => 'colorbox',
				'title' => 'Colorpicker',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
			);

			if (is_array($options['colorpicker'])) {
				$wizards['colorpicker'] = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
					$wizards['colorpicker'], $options['colorpicker']
				);
			}

			$wizards['colorpicker'] = self::addWizardScriptForTypo3Version('colorpicker', $wizards['colorpicker']);
		}

		return $wizards;
	}

	/**
	 * @param string $wizardType
	 * @param array $wizardConfig
	 * @return array
	 */
	private static function addWizardScriptForTypo3Version($wizardType, array $wizardConfig) {
		$completeWizardName = 'wizard_' . $wizardType;
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$wizardConfig['module']['name'] = $completeWizardName;
			if (isset($wizardConfig['script'])) {
				unset($wizardConfig['script']);
			}
		} else {
			if (!isset($wizardConfig['script'])) {
				$wizardConfig['script'] = $completeWizardName . '.php';
			}
		}

		return $wizardConfig;
	}
}

/**
 * the old class for backwards compatibility
 *
 * @deprecated: will be dropped in the feature!
 */
class Tx_Rnbase_Util_TCATool
	extends Tx_Rnbase_Utility_TcaTool {
	/**
	 * constructor to log deprecation!
	 *
	 * @return void
	 */
	function __construct() {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		$utility::deprecationLog(
			'Usage of "Tx_Rnbase_Util_TCATool" is deprecated' .
			'Please use "Tx_Rnbase_Utility_TcaTool" instead!'
		);
	}
}
