<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2013 Rene Nitzsche
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
tx_rnbase::load('tx_rnbase_util_PageBrowser');
tx_rnbase::load('tx_rnbase_util_Math');



/**
 * Contains utility functions for HTML-Forms
 */
class tx_rnbase_util_PageBrowserMarker implements PageBrowserMarker {
	private $pdid;
	private $pagePartsDef = array('normal', 'current', 'first', 'last', 'prev', 'next');

	/**
	 * Erstellung des PageBrowserMarkers
	 */
	function __construct() {
	}

	/**
	 * Initialisierung des PageBrowserMarkers mit den PageBrowser
	 */
	public function setPageBrowser($pageBrowser) {
		$this->pageBrowser = $pageBrowser;
	}

	/**
	 * Liefert die Limit-Angaben für die DB-Anfrage
	 */
	public function parseTemplate($template, &$formatter, $pbConfId, $pbMarker = 'PAGEBROWSER') {
// Configs: maxPages, pagefloat
// Obsolete da Template: showResultCount, showPBrowserText, dontLinkActivePage, showFirstLast
//    showRange
		$out = '';
		$configurations = $formatter->configurations;

		$this->initLink($configurations, $pbConfId);
    
		$pointer = $this->pageBrowser->getPointer();
		$count = $this->pageBrowser->getListSize();
		$results_at_a_time = $this->pageBrowser->getPageSize();
		$totalPages = ceil($count / $results_at_a_time);
		if($totalPages == 1 && $configurations->get($pbConfId.'hideIfSinglePage')) return '';
		$maxPages = intval($configurations->get($pbConfId.'maxPages'));
		$maxPages = tx_rnbase_util_Math::intInRange($maxPages ? $maxPages : 10, 1, 100);
		$templates = $this->getTemplates($template, $formatter, $pbMarker);

		$pageFloat = $this->getPageFloat($configurations->get($pbConfId.'pagefloat'), $maxPages);
		$firstLastArr = $this->getFirstLastPage($pointer, $pageFloat, $totalPages, $maxPages);

		$arr = array('count' => $count, 'totalpages' => $totalPages);
		$markerArray = $formatter->getItemMarkerArrayWrapped($arr, $pbConfId, 0, $pbMarker.'_');

		$subpartArray = $this->createSubpartArray($pbMarker);

		//---- Ab jetzt werden die Templates gefüllt
		$parts = array(); // Hier werden alle Teile des Browser gesammelt
		// Der Marker für die erste Seite
		if($templates['first'] && $pointer != 0) {
			$parts[] = $this->getPageString(0, $pointer, 'first', $templates, $formatter, $pbConfId, $pbMarker);
		}

		// Der Marker für die vorherige Seite
		if($templates['prev'] && $pointer > 0) {
			$parts[] = $this->getPageString($pointer-1, $pointer, 'prev', $templates, $formatter, $pbConfId, $pbMarker);
		}
		// Jetzt über alle Seiten iterieren
		for($i=$firstLastArr['first']; $i < $firstLastArr['last']; $i++) {
			$pageId = ($i == $pointer && $templates['current']) ? 'current' : 'normal';
			$parts[] = $this->getPageString($i, $pointer, $pageId, $templates, $formatter, $pbConfId, $pbMarker);
		}

		// Der Marker für die nächste Seite
		if($templates['next'] && $pointer < $totalPages-1) {
			$parts[] = $this->getPageString($pointer+1, $pointer, 'next', $templates, $formatter, $pbConfId, $pbMarker);
		}

		// Der Marker für die letzte Seite
		if($templates['last'] && $pointer != $totalPages-1) {
			$parts[] = $this->getPageString($totalPages-1, $pointer, 'last', $templates, $formatter, $pbConfId, $pbMarker);
		}

		$implode = $configurations->get($pbConfId.'.implode');
		$subpartArray['###'.$pbMarker.'_NORMAL_PAGE###'] = implode($parts, $implode ? $implode : ' ');
		$ret = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		return $ret;
	}

	/**
	 * Liefert das passende Template für die aktuelle Seite
	 */
	protected function getPageString($currentPage, $pointer, $pageId,  &$templates, &$formatter, $pbConfId, $pbMarker) {
		$rec = array();
		$rec['number'] = $currentPage + 1;

		$pageTemplate = $templates[$pageId];
		$pageConfId = $pbConfId.'page.'.$pageId.'.';
		$pageMarker = $pbMarker.'_'.strtoupper($pageId).'_PAGE_';

		$pageMarkerArray = $formatter->getItemMarkerArrayWrapped($rec, $pageConfId, 0, $pageMarker);
		$pageSubpartArray = array();

		if($this->link) {
			$this->link->parameters(array($this->pageBrowser->getParamName('pointer') => $currentPage));
			$pageWrappedSubpartArray['###'.$pageMarker.'LINK###'] = explode($this->token, $this->link->makeTag());
			$pageMarkerArray['###'.$pageMarker.'LINKURL###'] = $this->link->makeUrl();
		}
		else {
			$pageWrappedSubpartArray['###'.$pageMarker.'LINK###'] = $noLink;
			$pageMarkerArray['###'.$pageMarker.'LINKURL###'] = '';
		}
 
		$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($pageTemplate, $pageMarkerArray, $pageSubpartArray, $pageWrappedSubpartArray);
		return $out;
	}

	/**
	 * Ermittelt die erste und die letzte Seite, die im Browser gezeigt wird.
	 * @return array with keys 'first' and 'last'
	 */
	private function getFirstLastPage($pointer, $pageFloat, $totalPages, $maxPages) {
		$ret = array();
		if($pageFloat > -1) {
			$ret['last'] = min($totalPages, max($pointer + 1 + $pageFloat, $maxPages));
			$ret['first'] = max(0, $ret['last'] - $maxPages);
		}
		else {
			$ret['first'] = 0;
			$ret['last'] = tx_rnbase_util_Math::intInRange($totalPages, 1, $maxPages);
		}
		return $ret;
	}

	/**
	 * Liefert den korrekten Wert für den PageFloat. Das richtet den Ausschnitt der gezeigten
	 * Seiten im PageBrowser ein.
	 */
	private function getPageFloat($pageFloat, $maxPages) {
		if($pageFloat) {
			if(strtoupper($pageFloat) == 'CENTER') {
				$pageFloat = ceil(($maxPages - 1) / 2);
			}
			else
				$pageFloat = tx_rnbase_util_Math::intInRange($pageFloat, -1, $maxPages - 1);
		}
		else
			$pageFloat = -1;
		return $pageFloat;
	}

	/**
	 * Liefert ein Array mit allen verfügbaren Subtemplates der Seiten
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $pbMarker
	 * @return array
	 */
	function getTemplates($template, &$formatter, $pbMarker) {
		$ret = array();
		foreach($this->pagePartsDef As $part) {
			$ret[$part] = $formatter->cObj->getSubpart($template, '###'.$pbMarker.'_' . strtoUpper($part) . '_PAGE###');
		}
		return $ret;
	}

	/**
	 * Initialisiert das globale SubpartArray und entfernt alle Subpartmarker.
	 */
	private function createSubpartArray($pbMarker) {
		$ret = array();

		foreach($this->pagePartsDef As $part) {
			$ret['###'.$pbMarker.'_' . strtoUpper($part) . '_PAGE###'] = '';
		}
		return $ret;
	}

	/**
	 * Initialisiert die interne Link-Instanz
	 * TODO: Konfigurierbar machen!!
	 * @param tx_rnbase_configurations $configuration
	 */
	protected function initLink(&$configuration, $pbConfId) {
		$this->link = $configuration->createLink();
		$this->link->initByTS($configuration, $pbConfId.'link.', array());
		$this->token = md5(microtime());
		$this->link->label($this->token);
//		$this->link->destination($GLOBALS['TSFE']->id); // Link auf aktuelle Seite
		$this->noLink = array('', '');
	}
}

/*
###PAGEBROWSER###
<div>
###PAGEBROWSER_NORMAL_PAGE###
###PAGEBROWSER_NORMAL_PAGE_LINK### ###PAGEBROWSER_NORMAL_PAGE_NUMBER### ###PAGEBROWSER_NORMAL_PAGE_LINK###
###PAGEBROWSER_NORMAL_PAGE###

###PAGEBROWSER_CURRENT_PAGE###
###PAGEBROWSER_CURRENT_PAGE_NUMBER###
###PAGEBROWSER_CURRENT_PAGE###

###PAGEBROWSER_FIRST_PAGE###
###PAGEBROWSER_FIRST_PAGE_LINK### |< ###PAGEBROWSER_FIRST_PAGE_LINK###
###PAGEBROWSER_FIRST_PAGE###

###PAGEBROWSER_LAST_PAGE###
###PAGEBROWSER_LAST_PAGE_LINK### >| ###PAGEBROWSER_LAST_PAGE_LINK###
###PAGEBROWSER_LAST_PAGE###

###PAGEBROWSER_PREV_PAGE###
&nbsp;###PAGEBROWSER_PREV_PAGE_LINK###>###PAGEBROWSER_PREV_PAGE_LINK###&nbsp;
###PAGEBROWSER_PREV_PAGE###

###PAGEBROWSER_NEXT_PAGE###
&nbsp;###PAGEBROWSER_NEXT_PAGE_LINK###>###PAGEBROWSER_NEXT_PAGE_LINK###&nbsp;
###PAGEBROWSER_NEXT_PAGE###
</div>
###PAGEBROWSER###
*/


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowserMarker.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowserMarker.php']);
}
