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
  
  function tx_rnbase_util_ListMarker(ListMarkerInfo $listMarkerInfo = null) {
  	if($listMarkerInfo)
	  	$this->info =& $listMarkerInfo;
	  else
	  	$this->info =& new tx_rnbase_util_ListMarkerInfo();
  }

  function render(&$dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = false) {
//    $markerClass = tx_div::makeInstanceClassName($markerClassname);
//    if($markerParams)
//	    $entryMarker = new $markerClass($markerParams);
//	  else
//	    $entryMarker = new $markerClass();
		$entryMarker = ($markerParams) ? tx_rnbase::makeInstance($markerClassname, $markerParams) : tx_rnbase::makeInstance($markerClassname);

		$this->info->init($template, $formatter, $marker);
  
		$parts = array();
		$rowRoll = intval($formatter->configurations->get($confId.'roll.value'));
		$rowRollCnt = 0;
		for($i=0, $cnt=count($dataArr); $i < $cnt; $i++) {
			$data = $dataArr[$i];
			$data->record['roll'] = $rowRollCnt;
			$data->record['line'] = $i; // Marker für aktuelle Zeilenummer
			$part = $entryMarker->parseTemplate($this->info->getTemplate($data), $data, $formatter, $confId, $marker);
			$parts[] = $part;
			$rowRollCnt = ($rowRollCnt >= $rowRoll) ? 0 : $rowRollCnt + 1;
		}
		$parts = implode($formatter->configurations->get($confId.'implode'), $parts);
		return $parts;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListMarker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListMarker.php']);
}
?>