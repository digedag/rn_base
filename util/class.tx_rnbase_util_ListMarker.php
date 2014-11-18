<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_ListMarkerInfo');

/**
 * Base class for Markers.
 */
class tx_rnbase_util_ListMarker {

	public function __construct(ListMarkerInfo $listMarkerInfo = NULL) {
		if($listMarkerInfo)
			$this->info =& $listMarkerInfo;
		else
			$this->info = tx_rnbase::makeInstance('tx_rnbase_util_ListMarkerInfo');
	}

	/**
	 * Add a visitor callback. It is called for each item before rendering
	 * @param array $visitors array of callback arrays
	 */
	public function addVisitors(array $visitors) {
		$this->visitors = $visitors;
	}

  /**
   * 
   * @param tx_rnbase_util_IListProvider $provider
   * @param string $template
   * @param string $markerClassname
   * @param string $confId
   * @param string $marker
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param mixed $markerParams
   * @param int $offset
   * @return array
   */
	public function renderEach(tx_rnbase_util_IListProvider $provider, $template, $markerClassname, $confId, $marker, $formatter, $markerParams = FALSE, $offset=0) {
		$this->entryMarker = ($markerParams) ? tx_rnbase::makeInstance($markerClassname, $markerParams) : tx_rnbase::makeInstance($markerClassname);

		$this->info->init($template, $formatter, $marker);
		$this->template = $template;
		$this->confId = $confId;
		$this->marker = $marker;
		$this->formatter = $formatter;
		$this->offset = $offset;
		
		$this->parts = array();
		$this->rowRoll = intval($formatter->configurations->get($confId.'roll.value'));
		$this->rowRollCnt = 0;
		$this->totalLineStart = intval($formatter->configurations->get($confId.'totalline.startValue'));
		$this->i=0;
		$provider->iterateAll(array($this, 'renderNext'));

		$parts = implode($formatter->configurations->get($confId.'implode'), $this->parts);
		return array('result'=>$parts, 'size'=>$this->i);
	}
	/**
	 * Callback function for next item
	 * @param object $data
	 */
	public function renderNext($data) {
		$data->record['roll'] = $this->rowRollCnt;
		$data->record['line'] = $this->i; // Marker für aktuelle Zeilenummer
		$data->record['totalline'] = $this->i+$this->totalLineStart+$this->offset; // Marker für aktuelle Zeilenummer der Gesamtliste
		$this->handleVisitors($data);
		$part = $this->entryMarker->parseTemplate($this->info->getTemplate($data), $data, $this->formatter, $this->confId, $this->marker);
		$this->parts[] = $part;
		$this->rowRollCnt = ($this->rowRollCnt >= $this->rowRoll) ? 0 : $this->rowRollCnt + 1;
		$this->i++;
	}
	/**
	 * Call all visitors for an item
	 * @param object $data
	 */
	private function handleVisitors($data) {
		if(!is_array($this->visitors)) return;
		foreach($this->visitors As $visitor)
			call_user_func($visitor, $data);
	}

	/**
	 * Render an array of objects
	 * @param array $dataArr
	 * @param string $template
	 * @param string $markerClassname
	 * @param string $confId
	 * @param string $marker
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param mixed $markerParams
	 * @param int $offset
	 * @return array
	 */
	public function render($dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = FALSE, $offset=0) {
		$entryMarker = ($markerParams) ? tx_rnbase::makeInstance($markerClassname, $markerParams) : tx_rnbase::makeInstance($markerClassname);

		$this->info->init($template, $formatter, $marker);
  
		$parts = array();
		$rowRoll = intval($formatter->configurations->get($confId.'roll.value'));
		$rowRollCnt = 0;
		$totalLineStart = intval($formatter->configurations->get($confId.'totalline.startValue'));
		// Gesamtzahl der Liste als Register speichern
		$GLOBALS['TSFE']->register['RNBASE_LB_SIZE'] = count($dataArr);
		for($i=0, $cnt=count($dataArr); $i < $cnt; $i++) {
			$data = $dataArr[$i];
			// Check for object to avoid warning.
			if(!is_object($data)) continue;
			$data->record['roll'] = $rowRollCnt;
			$data->record['line'] = $i; // Marker für aktuelle Zeilenummer
			$data->record['totalline'] = $i+$totalLineStart+$offset; // Marker für aktuelle Zeilenummer der Gesamtliste
			$this->handleVisitors($data);
			$part = $entryMarker->parseTemplate($this->info->getTemplate($data), $data, $formatter, $confId, $marker);
			$parts[] = $part;
			$rowRollCnt = ($rowRollCnt >= $rowRoll) ? 0 : $rowRollCnt + 1;
		}
		$parts = implode($formatter->configurations->get($confId.'implode', TRUE), $parts);
		return $parts;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListMarker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListMarker.php']);
}
