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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
tx_div::load('tx_rnbase_util_ListBuilderInfo');

/**
 * Generic List-Builder. Creates a list of data with Pagebrowser.
 */
class tx_rnbase_util_ListBuilder {
	
	/**
	 * Constructor
	 *
	 * @param ListBuilderInfo $info
	 * @return tx_rnbase_util_ListBuilder
	 */
	public function tx_rnbase_util_ListBuilder(ListBuilderInfo $info = null) {
		if($info)
			$this->info =& $info;
		else
			$this->info =& new tx_rnbase_util_ListBuilderInfo;
	}

	/**
	 * Render an array of data entries with an html template. The html template should look like this:
	 * ###DATAS###
	 * ###DATA###
	 * ###DATA_UID###
	 * ###DATA###
	 * ###DATAS###
	 * We have some conventions here:
	 * The given parameter $marker should be named 'DATA' for this example. The the list subpart 
	 * is experted to be named '###'.$marker.'S###'. Please notice the trailing S!
	 * If you want to render a pagebrowser add it to the $viewData with key 'pagebrowser'.
	 * A filter will be detected and rendered too. It should be available in $viewData with key 'filter'.
	 *
	 * @param array $dataArr entries
	 * @param string $template
	 * @param string $markerClassname item-marker class
	 * @param string $confId ts-Config for data entries like team.
	 * @param string $marker name of marker like TEAM
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param array $markerParams array of settings for itemmarker
	 * @return string
	 */
	function render(&$dataArr, &$viewData, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = null) {

		$debug = $formatter->getConfigurations()->get($confId.'_debuglb');
		if($debug) {
		  $time = microtime(true);
		  $mem = memory_get_usage();
		  $wrapTime = tx_rnbase_util_FormatUtil::$time;
		  $wrapMem = tx_rnbase_util_FormatUtil::$mem;
		}
		if(is_array($dataArr) && count($dataArr)) {
			$markerClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListMarker');
			$listMarker = new $markerClass($this->info->getListMarkerInfo());
			$cObj =& $formatter->configurations->getCObj(0);
			$templateList = $cObj->getSubpart($template,'###'.$marker.'S###');

			$templateEntry = $cObj->getSubpart($templateList,'###'.$marker.'###');
			$out = $listMarker->render($dataArr, $templateEntry, $markerClassname,
					$confId, $marker, $formatter, $markerParams);
			$subpartArray['###'.$marker.'###'] = $out;
			// Das Menu für den PageBrowser einsetzen
			$pageBrowser =& $viewData->offsetGet('pagebrowser');
			if($pageBrowser) {
				tx_div::load('tx_rnbase_util_BaseMarker');
				$subpartArray['###PAGEBROWSER###'] = tx_rnbase_util_BaseMarker::fillPageBrowser(
								$cObj->getSubpart($template,'###PAGEBROWSER###'),
								$pageBrowser, $formatter, $confId.'pagebrowser.');
				$markerArray['###'.$marker.'COUNT###'] = $pageBrowser->getListSize();
			}
			else {
				$markerArray['###'.$marker.'COUNT###'] = count($dataArr);
			}
			$out = $formatter->cObj->substituteMarkerArrayCached($templateList, $markerArray, $subpartArray);
		}
		else {
			$out = $this->info->getEmptyListMessage($confId, $viewData, $formatter->configurations);
		}

		$markerArray = array();
		$subpartArray['###'.$marker.'S###'] = $out;

		// Muss ein Formular mit angezeigt werden
		// Zuerst auf einen Filter prüfen
		$filter  =& $viewData->offsetGet('filter');
		if($filter) {
			$template = $filter->getMarker()->parseTemplate($template, $formatter, $confId.'filter.',$marker);
		}
		// Jetzt noch die alte Variante
		$markerArray['###SEARCHFORM###'] = '';
		$seachform  =& $viewData->offsetGet('searchform');
		if($seachform)
			$markerArray['###SEARCHFORM###'] = $seachform;

		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		if($debug) {
			tx_div::load('class.tx_rnbase_util_Misc.php');

			$wrapTime = tx_rnbase_util_FormatUtil::$time - $wrapTime;
			$wrapMem = tx_rnbase_util_FormatUtil::$mem - $wrapMem;
			t3lib_div::debug(array(
					'Rows'=>count($dataArr),
					'Execustion time'=>(microtime(true) -$time),
					'WrapTime'=>$wrapTime,
					'WrapMem'=>$wrapMem,
					'Memory start'=> $mem,
					'Memory consumed'=> (memory_get_usage()-$mem)
				), 'ListBuilder Statistics for: ' . $confId);
		}
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilder.php']);
}
?>