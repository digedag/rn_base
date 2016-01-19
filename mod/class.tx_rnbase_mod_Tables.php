<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2016 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_model_data');
tx_rnbase::load('tx_rnbase_mod_Util');

/**
 */
class tx_rnbase_mod_Tables {

	/**
	 *
	 * @param array $entries
	 * @param array $columns
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param tx_rnbase_model_data $options
	 * @return array 0 are data and 1 layout
	 */
	public static function prepareTable($entries, $columns, $formTool, $options) {
		$options = tx_rnbase_model_data::getInstance($options);
		$tableLayout = $formTool->getDoc()->tableLayout;
		$tableData = array(self::getHeadline($columns, $options, $formTool));
		$rowCount = 1;
		$isRowOdd = FALSE;
		foreach($entries As $entry) {
			$tableData[$rowCount] = self::prepareRow(
				$entry,
				$columns,
				$formTool,
				$options
			);
			$rowCount++;

			// now add the language overlays!
			// sample css for mod template:
			// table.typo3-dblist tr.localization { opacity: 0.5; font-size: 0.92em; }
			// table.typo3-dblist tr.localization td:nth-child(1), table.typo3-dblist tr.localization td:nth-child(2) { padding-left: 24px; }
			if (
				$entry instanceof Tx_Rnbase_Domain_Model_RecordInterface
				&& $options->getAddI18Overlays()
				// skip if the entry already translated!
				&& $entry->getUid() == $entry->getProperty('uid')
				&& !$entry->getSysLanguageUid()
			) {
				// set the layout for the original (last) row
				$defName = $isRowOdd ? 'defRowOdd' : 'defRowEven';
				$tableLayout[$rowCount-1] = is_array($tableLayout[$defName]) ? $tableLayout[$defName] : $tableLayout['defRow'];
				// the spacial layout for the overlay rows
				$layout = $tableLayout[$rowCount-1];
				$layout['tr'][0] = '<tr class="' . ($isRowOdd ? 'db_list_normal' : 'db_list_alt') . ' localization">';
				$isRowOdd = !$isRowOdd;

				// render the overlays with the special layout
				foreach(self::getLangOverlayEntries($entry) as $overlay) {
					$overlay->setProperty('_MOD_OVERLAY', TRUE);
					$tableData[$rowCount] = self::prepareRow(
						$overlay,
						$columns,
						$formTool,
						$options
					);
					$overlay->unsProperty('_MOD_OVERLAY');
					$tableLayout[$rowCount] = $layout;
					$rowCount++;
				}
			}
		}
		return array($tableData, $tableLayout);
	}

	/**
	 *
	 * @param array $entry
	 * @param array $columns
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param tx_rnbase_model_data $options
	 * @return array
	 */
	protected static function prepareRow($entry, $columns, $formTool, $options) {
		$record = is_object($entry) ? $entry->record : $entry;
		$row = array();
		if ($options->getCheckbox() !== NULL) {
			$checkName = $options->getCheckboxname() ? $options->getCheckboxname() : 'checkEntry';
			$dontcheck = is_array($options->getDontcheck()) ? $options->getDontcheck() : array();
			// Check if entry is checkable
			if(!array_key_exists($record['uid'], $dontcheck))
				$row[] = $formTool->createCheckbox($checkName.'[]', $record['uid']);
			else
				$row[] = '<img'.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/zoom2.gif', 'width="11" height="12"').' title="Info: '. $dontcheck[$record['uid']] .'" border="0" alt="" />';
		}

		if ($options->getAddRecordSprite()) {
			$spriteIconName = 'mimetypes-other-other';
			if ($entry instanceof Tx_Rnbase_Domain_Model_RecordInterface && $entry->getTableName()) {
				$spriteIconName = Tx_Rnbase_Backend_Utility_Icons::mapRecordTypeToSpriteIconName(
					$entry->getTableName(),
					$record
				);
			}
			$row[] = tx_rnbase_mod_Util::getSpriteIcon(
				$spriteIconName
			);
		}

		reset($columns);
		foreach($columns As $column => $data) {
			// Hier erfolgt die Ausgabe der Daten für die Tabelle. Wenn eine method angegeben
			// wurde, dann muss das Entry als Objekt vorliegen. Es wird dann die entsprechende
			// Methode aufgerufen. Es kann auch ein Decorator-Objekt gesetzt werden. Dann wird
			// von diesem die Methode format aufgerufen und der Wert, sowie der Name der aktuellen
			// Spalte übergeben. Ist nichts gesetzt wird einfach der aktuelle Wert verwendet.
			if(isset($data['method'])) {
				$row[] = call_user_func(array($entry, $data['method']));
			}
			elseif(isset($data['decorator'])) {
				$decor = $data['decorator'];
				$row[] = $decor->format($record[$column], $column, $record, $entry);
			}
			else {
				$row[] = $record[$column];
			}
		}
		if ($options->getLinker()) {
			$row[] = self::addLinker($options, $entry, $formTool);
		}

		return $row;
	}

	/**
	 * Liefert die passenden Überschrift für die Tabelle
	 *
	 * @param array $columns
	 * @param array $options
	 * @param tx_rnbase_util_FormTool $formTool
	 * @return array
	 */
	private static function getHeadline($columns= array(), $options, $formTool) {
		global $LANG;
		$arr = array();
		if($options->getCheckbox()) {
			$arr[] = '&nbsp;'; // Spalte für Checkbox
		}
		if ($options->getAddRecordSprite()) {
			$arr[] = '&nbsp;';
		}

		foreach($columns As $column => $data) {
			if ((int) $data['nocolumn']) {
				continue;
			}
			if ((int) $data['notitle']) {
				$arr[] = '';
				continue;
			}

			$label = $LANG->getLL(isset($data['title']) ? $data['title'] : $column);
			if (!$label && isset($data['title'])) {
				$label = $LANG->sL($data['title']);
			}
			//es gibt die Möglichkeit sortable zu setzen. damit wird
			//nach dem title eine sortierung eingeblendet.
			//in $data['sortable'] sollte ein prefix für das feld stehen, sprich
			//der alias der tabelle um damit direkt weiterabeiten zu können.
			//einfach leer lassen wenn auf einen prefix verzichtet werden soll
			if (isset($data['sortable'])){
				$label = $formTool->createSortLink($column, $label);
			}
			$arr[] = $label ? $label : $data['title'];
		}
		if ($options->getLinker()) {
			$arr[] = $LANG->getLL('label_action');
		}
		return $arr;
	}

	/**
	 * returns all language overlays.
	 *
	 * @param Tx_Rnbase_Domain_Model_Base $entry
	 * @return array[Tx_Rnbase_Domain_Model_Base]
	 */
	private static function getLangOverlayEntries(
		Tx_Rnbase_Domain_Model_RecordInterface $entry
	) {
		tx_rnbase::load('tx_rnbase_util_TCA');
		$parentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($entry->getTableName());
		$overlays = tx_rnbase_util_DB::doSelect(
			'*',
			$entry->getTableName(),
			array(
				'where' => $parentField . '=' . $entry->getUid(),
				'wrapperclass' => get_class($entry),
			)
		);
		return $overlays;
	}

	/**
	 *
	 * @param tx_rnbase_model_data $options
	 * @param Tx_Rnbase_Domain_Model_Base $obj
	 * @param tx_rnbase_util_FormTool $formTool
	 * @return string
	 */
	private static function addLinker($options, $obj, $formTool) {
		$out = '';
		$linkerArr = $options->getLinker();
		if ((is_array($linkerArr) || $linkerArr instanceof Traversable) && !empty($linkerArr)) {
			$linkerimplode = $options->getLinkerimplode() ? $options->getLinkerimplode() : '<br />';
			$currentPid = (int) $options->getPid();
			foreach($linkerArr As $linker) {
				if (!$linker instanceof tx_rnbase_mod_LinkerInterface) {
					// backward compatibility, the interface with the makeLink method is new!
					if (!is_callable(array($linker, 'makeLink'))) {
						throw new Exception(
							'Linker "' . get_class($linker) . '" has to implement interface "tx_rnbase_mod_LinkerInterface".'
						);
					}
					t3lib_div::deprecationLog(
						'Linker "' . get_class($linker) . '" has to implement interface "tx_rnbase_mod_LinkerInterface".'
					);
				}
				$out .= $linker->makeLink($obj, $formTool, $currentPid, $options);
				$out .= $linkerimplode;
			}
		}

		return $out;
	}


	/**
	 * Returns a table based on the input $data
	 * This method is taken from TYPO3 core. It will be removed there for version 8.
	 *
	 * Typical call until now:
	 * $content .= tx_rnbase_mod_Tables::buildTable($data, $module->getTableLayout());
	 * Should we include a better default layout here??
	 *
	 * @param array $data Multidim array with first levels = rows, second levels = cells
	 * @param array $layout If set, then this provides an alternative layout array instead of $this->tableLayout
	 * @return string The HTML table.
	 */
	public static function buildTable($data, $layout = null)
	{
		$resultHead = $result = '';
		if (is_array($data)) {
			$tableLayout = is_array($layout) ? $layout : self::getTableLayout();
			$rowCount = 0;
			foreach ($data as $tableRow) {
				if ($rowCount % 2) {
					$layout = is_array($tableLayout['defRowOdd']) ? $tableLayout['defRowOdd'] : $tableLayout['defRow'];
				} else {
					$layout = is_array($tableLayout['defRowEven']) ? $tableLayout['defRowEven'] : $tableLayout['defRow'];
				}
				$rowLayout = is_array($tableLayout[$rowCount]) ? $tableLayout[$rowCount] : $layout;
				$rowResult = '';
				if (is_array($tableRow)) {
					$cellCount = 0;
					foreach ($tableRow as $tableCell) {
						$cellWrap = is_array($layout[$cellCount]) ? $layout[$cellCount] : $layout['defCol'];
						$cellWrap = is_array($rowLayout['defCol']) ? $rowLayout['defCol'] : $cellWrap;
						$cellWrap = is_array($rowLayout[$cellCount]) ? $rowLayout[$cellCount] : $cellWrap;
						$rowResult .= $cellWrap[0] . $tableCell . $cellWrap[1];
						$cellCount++;
					}
				}
				$rowWrap = is_array($layout['tr']) ? $layout['tr'] : array('<tr>', '</tr>');
				$rowWrap = is_array($rowLayout['tr']) ? $rowLayout['tr'] : $rowWrap;

				if(is_array($tableLayout['headRows']) && in_array($rowCount, $tableLayout['headRows'])) {
					$resultHead .= $rowWrap[0] . $rowResult . $rowWrap[1];
				}
				else {
					$result .= $rowWrap[0] . $rowResult . $rowWrap[1];
				}
				$rowCount++;
			}
			if(is_array($tableLayout['headRows'])) {
				$result = '<thead>'.$resultHead.'</thead><tbody>'.$result.'</tbody>';
			}
			else
				$result = $resultHead . $result;
			$tableTag = tx_rnbase_util_TYPO3::isTYPO76OrHigher() ?
				'<table class="table table-striped table-hover table-condensed">' :
				'<table border="0" cellspacing="0" cellpadding="0" class="typo3-dblist" id="typo3-tmpltable">';
			$tableWrap = is_array($tableLayout['table']) ? $tableLayout['table'] : array($tableTag, '</table>');
			$result = $tableWrap[0] . $result . $tableWrap[1];
		}
		return $result;
	}

	/**
	 * Returns a default table layout
	 * @return array
	 */
	private static function getTableLayout() {
		return
		tx_rnbase_util_TYPO3::isTYPO76OrHigher() ?
		Array (
				'headRows' => Array(0),
				'table' => Array('<table class="table table-striped table-hover table-condensed">', '</table><br/>'),
				'0' => Array( // Format für 1. Zeile
						'tr'		=> Array('<tr class="">', '</tr>'),
						// Format für jede Spalte in der 1. Zeile
						'defCol' => array('<td>', '</td>')
				),
				'defRow' => Array ( // Formate für alle Zeilen
						'tr'	   => Array('<tr class="">', '</tr>'),
						'defCol' => Array('<td>', '</td>') // Format für jede Spalte in jeder Zeile
				),
				'defRowEven' => Array ( // Formate für alle geraden Zeilen
						'tr'	   => Array('<tr class="">', '</tr>'),
						// Format für jede Spalte in jeder Zeile
						'defCol' => array('<td>', '</td>')
				)
		)
		:
		Array (
				'table' => Array('<table class="typo3-dblist" width="100%" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'),
				'0' => Array( // Format für 1. Zeile
						'tr'		=> Array('<tr class="t3-row-header c-headLineTable">', '</tr>'),
						// Format für jede Spalte in der 1. Zeile
						'defCol' => array('<td>', '</td>')
				),
				'defRow' => Array ( // Formate für alle Zeilen
						'tr'	   => Array('<tr class="db_list_normal">', '</tr>'),
						'defCol' => Array('<td>', '</td>') // Format für jede Spalte in jeder Zeile
				),
				'defRowEven' => Array ( // Formate für alle geraden Zeilen
						'tr'	   => Array('<tr class="db_list_alt">', '</tr>'),
						// Format für jede Spalte in jeder Zeile
						'defCol' => array('<td>', '</td>')
				)
		);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php']);
}
