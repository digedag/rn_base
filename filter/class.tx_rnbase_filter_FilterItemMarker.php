<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_BaseMarker');

class tx_rnbase_filter_FilterItemMarker extends tx_rnbase_util_BaseMarker {
	public function parseTemplate($template, &$item, &$formatter, $confId, $marker = 'FILTERITEM') {
    $markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , 0, $marker.'_');
		
		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_FilterItemMarker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_FilterItemMarker.php']);
}
