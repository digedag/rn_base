<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('Tx_Rnbase_Backend_Decorator_InterfaceDecorator');

/**
 * Base Decorator
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 */
class Tx_Rnbase_Backend_Decorator_BaseDecorator
	implements Tx_Rnbase_Backend_Decorator_InterfaceDecorator
{
	/**
	 * The module
	 *
	 * @var tx_rnbase_mod_BaseModule
	 */
	private $mod = null;

	/**
	 * Constructor
	 *
	 * @param tx_rnbase_mod_BaseModule $mod
	 */
	public function __construct(
		tx_rnbase_mod_BaseModule $mod
	) {
		$this->mod = $mod;
	}

	/**
	 * Returns the module
	 *
	 * @return tx_rnbase_mod_IModule
	 */
	protected function getModule()
	{
		return $this->mod;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return tx_rnbase_util_FormTool
	 */
	protected function getFormTool()
	{
		return $this->getModule()->getFormTool();
	}

	/**
	 * Formats a value
	 *
	 * @param string $columnValue
	 * @param string $columnName
	 * @param array $record
	 * @param Tx_Rnbase_Domain_Model_DataInterface $entry
	 *
	 * @return string
	 */
	public function format(
		$columnValue,
		$columnName,
		array $record,
		Tx_Rnbase_Domain_Model_DataInterface $entry
	) {
		$return = $columnValue;

		tx_rnbase::load('tx_rnbase_util_Strings');
		$method = tx_rnbase_util_Strings::underscoredToLowerCamelCase($columnName);
		$method = 'format' . ucfirst($method) . 'Column';

		if (method_exists($this, $method)) {
			$return = $this->{$method}($entry);
		}

		return $this->wrapValue($return, $entry, $columnName);
	}

	/**
	 * Wraps the Value.
	 * A childclass can extend this and wrap each value in a spac.
	 * For example a strikethrough for disabled entries.
	 *
	 * @param string $formatedValue
	 * @param Tx_Rnbase_Domain_Model_DataInterface $entry
	 * @param string $columnName
	 *
	 * @return string
	 */
	protected function wrapValue(
		$formatedValue,
		Tx_Rnbase_Domain_Model_DataInterface $entry,
		$columnName
	) {
		return $formatedValue;
	}

	/**
	 * Renders the uid column.
	 *
	 * @param Tx_Rnbase_Domain_Model_DataInterface $entry
	 *
	 * @return string
	 */
	protected function formatUidColumn(
		Tx_Rnbase_Domain_Model_DataInterface $entry
	) {
		$value = $entry->getProperty('uid');

		return sprintf(
			'<span title="%2$s">%1$d</span>',
			$value,
			implode(CRLF, $this->buildSimpleEntryInfo($entry))
		);
	}

	/**
	 * Renders the uid column.
	 *
	 * @param Tx_Rnbase_Domain_Model_DataInterface $entry
	 *
	 * @return string
	 */
	protected function formatLabelColumn(
		Tx_Rnbase_Domain_Model_DataInterface $entry
	) {
		$label = '';

		$labelField = tx_rnbase_util_TCA::getLabelFieldForTable(
			$entry->getTableName()
		);
		if ($labelField !== 'uid' && $entry->getProperty($labelField)) {
			$label = $entry->getProperty($labelField);
		} elseif ($entry->getLabel()) {
			$label = $entry->getLabel();
		} elseif ($entry->getName()) {
			$label = $entry->getName();
		}

		return sprintf(
			'<span title="%2$s">%1$d</span>',
			(string) $label,
			implode(CRLF, $this->buildSimpleEntryInfo($entry))
		);
	}

	/**
	 * Builds a simple info of the entitiy.
	 * Is curently used for title tags
	 *
	 * @param Tx_Rnbase_Domain_Model_DataInterface $entry
	 *
	 * @return array
	 */
	protected function buildSimpleEntryInfo(
		Tx_Rnbase_Domain_Model_DataInterface $entry
	) {
		$infos = array();

		$infos['uid'] = 'UID: ' . $entry->getProperty('uid');

		// only for domain entries with table name
		if ($entry instanceof Tx_Rnbase_Domain_Model_DomainInterface) {
			$labelField = tx_rnbase_util_TCA::getLabelFieldForTable($entry->getTableName());
			if ($labelField !== 'uid' && $entry->getProperty($labelField)) {
				$infos['label'] = 'Label: ' . (string) $entry->getProperty($labelField);
			}

			$datefields = array(
				'Creation' => tx_rnbase_util_TCA::getCrdateFieldForTable($entry->getTableName()),
				'Last Change' => tx_rnbase_util_TCA::getTstampFieldForTable($entry->getTableName()),
			);
			foreach ($datefields as $dateTitle => $datefield) {
				$date = $entry->getProperty($datefield);
				if (!empty($date)) {
					$infos[$datefield] = $dateTitle . ': ' . strftime(
						'%d.%m.%y %H:%M:%S',
						$date
					);
				}
			}
		}

		return $infos;

	}

	/**
	 * Renders the useractions
	 *
	 * @param Tx_Rnbase_Domain_Model_DataInterface $item
	 *
	 * @return string
	 */
	protected function formatActionsColumn(
		Tx_Rnbase_Domain_Model_DataInterface $item
	) {
		$return = '';

		// only for domain entries with table name
		if (!$item instanceof Tx_Rnbase_Domain_Model_DomainInterface) {
			return $return;
		}

		$tableName = $item->getTableName();
		// we use the real uid, not the uid of the parent!
		$uid = $item->getProperty('uid');

		tx_rnbase::load('tx_rnbase_util_TCA');
		$actionConf = $this->getActionsConfig($item);

		foreach ($actionConf as $actionKey => $actionConfig) {
			switch ($actionKey) {
				case 'edit':
					$return .= $this->getFormTool()->createEditLink(
						$tableName,
						$uid,
						$actionConfig['title']
					);
					break;

				case 'hide':
					$return .= $this->getFormTool()->createHideLink(
						$tableName,
						$uid,
						$item->getDisabled(),
						array(
							'label' => $actionConfig['title']
						)
					);
					break;

				case 'remove':
					$return .= $this->getFormTool()->createDeleteLink(
						$tableName,
						$uid,
						$actionConfig['title'],
						array(
							'confirm' => $actionConfig['confirm']
						)
					);
					break;

				case 'moveup':
					// @TODO: implement! see tx_mklib_mod1_decorator_Base
					break;

				case 'movedown':
					// @TODO: implement! see tx_mklib_mod1_decorator_Base
					break;

				default:
					break;
			}

		}

		return $return;
	}

	/**
	 * Liefert die möglichen Optionen für die actions
	 *
	 * @param Tx_Rnbase_Domain_Model_DomainInterface $item
	 *
	 * @return array
	 */
	protected function getActionsConfig(
		Tx_Rnbase_Domain_Model_DomainInterface $item
	) {
		$def = array('title' => '');
		$actions = array(
			'edit' => $def,
			'hide' => $def,
		);

		// add mopve up and move down buttons for sortable entities
		if (tx_rnbase_util_TCA::getSortbyFieldForTable($item->getTableName())) {
			$actions['moveup'] = $def;
			$actions['movedown'] = $def;
		}

		// add remove button only for admins
		if ($this->isAdmin()) {
			$actions['remove'] = $def;
			$actions['remove']['confirm'] = '###LABEL_ENTRY_DELETE_CONFIRM###';
		}

		return $actions;
	}

	/**
	 * Is the current iser a admin?
	 *
	 * @return bool
	 */
	protected function isAdmin()
	{
		if (is_object($GLOBALS['BE_USER'])) {
			return (bool) $GLOBALS['BE_USER']->isAdmin();
		}

		return false;
	}
}
