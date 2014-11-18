<?php

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
 */
class tx_rnbase_mod_Tables {
	/**
	 *
	 * @param array $entries
	 * @param array $columns
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 */
	public static function prepareTable($entries, $columns, $formTool, $options) {
		$arr = Array( 0 => Array( self::getHeadline($columns, $options, $formTool) ));
		foreach($entries As $entry){

			$record = is_object($entry) ? $entry->record : $entry;
			$row = array();
			if(isset($options['checkbox'])) {
				$checkName = isset($options['checkboxname']) ? $options['checkboxname'] : 'checkEntry';
				// Check if entry is checkable
				if(!is_array($options['dontcheck']) || !array_key_exists($record['uid'], $options['dontcheck']))
					$row[] = $formTool->createCheckbox($checkName.'[]', $record['uid']);
				else
					$row[] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/zoom2.gif', 'width="11" height="12"').' title="Info: '. $options['dontcheck'][$record['uid']] .'" border="0" alt="" />';
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
			if(isset($options['linker']))
				$row[] = self::addLinker($options, $entry, $formTool);
			$arr[0][] = $row;
		}
		return $arr;
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
		if(isset($options['checkbox'])) {
			$arr[] = '&nbsp;'; // Spalte für Checkbox
		}

		foreach($columns As $column => $data) {
			if(intval($data['nocolumn'])) continue;
			if(intval($data['notitle'])) {
				$arr[] = '';
				continue;
			}

			$label = $LANG->getLL(isset($data['title']) ? $data['title'] : $column);
			if(!$label && isset($data['title']))
				$label = $LANG->sL($data['title']);
			//es gibt die Möglichkeit sortable zu setzen. damit wird
			//nach dem title eine sortierung eingeblendet.
			//in $data['sortable'] sollte ein prefix für das feld stehen, sprich
			//der alias der tabelle um damit direkt weiterabeiten zu können.
			//einfach leer lassen wenn auf einen prefix verzichtet werden soll
			if(isset($data['sortable'])){
				$label = $formTool->createSortLink($column, $label);
			}
			$arr[] = $label ? $label : $data['title'];
		}
		if(isset($options['linker']))
			$arr[] = $LANG->getLL('label_action');
		return $arr;
	}

	private static function addLinker($options, $obj, $formTool) {
		$out = '';
		if(isset($options['linker'])) {
			$linkerArr = $options['linker'];
			if(is_array($linkerArr) && count($linkerArr)) {
				$currentPid = intval($options['pid']);
				foreach($linkerArr As $linker) {
					$out .= $linker->makeLink($obj, $formTool, $currentPid, $options);
					$out .= $options['linkerimplode'] ? $options['linkerimplode'] : '<br />';
				}
			}
		}
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_Tables.php']);
}

