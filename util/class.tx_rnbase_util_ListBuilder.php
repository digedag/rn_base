<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2011 Rene Nitzsche
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
tx_rnbase::load('tx_rnbase_util_ListBuilderInfo');
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_IListProvider');
tx_rnbase::load('tx_rnbase_util_Debug');


/**
 * Generic List-Builder. Creates a list of data with Pagebrowser.
 */
class tx_rnbase_util_ListBuilder {
	private $visitors = array();
	/**
	 * Constructor
	 *
	 * @param ListBuilderInfo $info
	 * @return tx_rnbase_util_ListBuilder
	 */
	public function __construct(ListBuilderInfo $info = NULL) {
		if($info)
			$this->info =& $info;
		else
			$this->info = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilderInfo');
	}
	/**
	 * Add a visitor callback. It is called for each item before rendering
	 * @param array $callback
	 */
	public function addVisitor(array $callback) {
		$this->visitors[] = $callback;
	}

	public function renderEach(tx_rnbase_util_IListProvider $provider, $viewData, $template, $markerClassname, $confId, $marker, $formatter, $markerParams = NULL) {
		$viewData = is_object($viewData) ? $viewData : new ArrayObject();
		$debugKey = $formatter->getConfigurations()->get($confId.'_debuglb');
		$debug = ($debugKey && ($debugKey==='1' ||
				($_GET['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_GET['debug'])))) ||
				($_POST['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_POST['debug']))))
				)
		);
		if($debug) {
		  $time = microtime(TRUE);
		  $mem = memory_get_usage();
		  $wrapTime = tx_rnbase_util_FormatUtil::$time;
		  $wrapMem = tx_rnbase_util_FormatUtil::$mem;
		}

		$outerMarker = $this->getOuterMarker($marker, $template);
		$listMarker = tx_rnbase::makeInstance('tx_rnbase_util_ListMarker', $this->info->getListMarkerInfo());
		while($templateList = t3lib_parsehtml::getSubpart($template, '###'.$outerMarker.'S###')) {
			$templateEntry = t3lib_parsehtml::getSubpart($templateList, '###'.$marker.'###');
			$offset = 0;
			$pageBrowser =& $viewData->offsetGet('pagebrowser');
			if($pageBrowser) {
				$state = $pageBrowser->getState();
				$offset = $state['offset'];
			}
			// charbrowser
			$pagerData = $viewData->offsetGet('pagerData');
			$charPointer = $viewData->offsetGet('charpointer');
			$subpartArray['###CHARBROWSER###'] = tx_rnbase_util_BaseMarker::fillCharBrowser(
														tx_rnbase_util_Templates::getSubpart($template, '###CHARBROWSER###'),
														$markerArray, $pagerData, $charPointer,
														$formatter->getConfigurations(), $confId.'charbrowser.');

			$listMarker->addVisitors($this->visitors);
			$ret = $listMarker->renderEach($provider, $templateEntry, $markerClassname,
					$confId, $marker, $formatter, $markerParams, $offset);
			if($ret['size'] > 0) {
				$subpartArray['###'.$marker.'###'] = $ret['result'];
				$subpartArray['###'.$marker.'EMPTYLIST###'] = '';
				// Das Menu für den PageBrowser einsetzen
				if($pageBrowser) {
					$subpartArray['###PAGEBROWSER###'] = tx_rnbase_util_BaseMarker::fillPageBrowser(
									t3lib_parsehtml::getSubpart($template, '###PAGEBROWSER###'),
									$pageBrowser, $formatter, $confId.'pagebrowser.');
					$listSize = $pageBrowser->getListSize();
				}
				else {
					$listSize = $ret['size'];
				}
				$markerArray['###'.$marker.'COUNT###'] = $formatter->wrap($listSize, $confId.'count.');

				$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($templateList, $markerArray, $subpartArray);
			}
			else {
				// Support für EMPTYLIST-Block
				if(tx_rnbase_util_BaseMarker::containsMarker($template, $marker.'EMPTYLIST')) {
					$out = t3lib_parsehtml::getSubpart($template, '###'.$marker.'EMPTYLIST###');
				}
				else
					$out = $this->info->getEmptyListMessage($confId, $viewData, $formatter->getConfigurations());
			}
			$template = tx_rnbase_util_Templates::substituteSubpart($template, '###'.$outerMarker.'S###', $out, 0);
		}

		$markerArray = array();
		$subpartArray = array();
		$subpartArray['###'.$outerMarker.'S###'] = $out;

		// Muss ein Formular mit angezeigt werden
		// Zuerst auf einen Filter prüfen
		$filter  =& $viewData->offsetGet('filter');
		if($filter) {
			$template = $filter->getMarker()->parseTemplate($template, $formatter, $confId.'filter.', $marker);
		}

		$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		if($debug) {
			tx_rnbase::load('class.tx_rnbase_util_Misc.php');

			$wrapTime = tx_rnbase_util_FormatUtil::$time - $wrapTime;
			$wrapMem = tx_rnbase_util_FormatUtil::$mem - $wrapMem;
			tx_rnbase_util_Debug::debug(array(
					'Rows'=>count($dataArr),
					'Execustion time'=>(microtime(TRUE) -$time),
					'WrapTime'=>$wrapTime,
					'WrapMem'=>$wrapMem,
					'Memory start'=> $mem,
					'Memory consumed'=> (memory_get_usage()-$mem)
				), 'ListBuilder Statistics for: ' . $confId . ' Key: ' . $debugKey);
		}
		return $out;
	}

	/**
	 * Render an array of data entries with an html template. The html template should look like this:
	 * ###DATAS###
	 * ###DATA###
	 * ###DATA_UID###
	 * ###DATA###
	 * ###DATAEMPTYLIST###
	 * Shown if list is empty
	 * ###DATAEMPTYLIST###
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
	function render(&$dataArr, $viewData, $template, $markerClassname, $confId, $marker, $formatter, $markerParams = NULL) {

		$viewData = is_object($viewData) ? $viewData : new ArrayObject();
		$debugKey = $formatter->getConfigurations()->get($confId.'_debuglb');
		$debug = ($debugKey && ($debugKey==='1' ||
				($_GET['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_GET['debug'])))) ||
				($_POST['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_POST['debug']))))
				)
		);
		if($debug) {
		  $time = microtime(TRUE);
		  $mem = memory_get_usage();
		  $wrapTime = tx_rnbase_util_FormatUtil::$time;
		  $wrapMem = tx_rnbase_util_FormatUtil::$mem;
		}

		$outerMarker = $this->getOuterMarker($marker, $template);
		while($templateList = t3lib_parsehtml::getSubpart($template, '###'.$outerMarker.'S###')) {
			if(is_array($dataArr) && count($dataArr)) {
				$listMarker = tx_rnbase::makeInstance('tx_rnbase_util_ListMarker', $this->info->getListMarkerInfo());

				$templateEntry = t3lib_parsehtml::getSubpart($templateList, '###'.$marker.'###');
				$offset = 0;
				$pageBrowser =& $viewData->offsetGet('pagebrowser');
				if($pageBrowser) {
					$state = $pageBrowser->getState();
					$offset = $state['offset'];
				}

				$listMarker->addVisitors($this->visitors);
				$out = $listMarker->render($dataArr, $templateEntry, $markerClassname,
						$confId, $marker, $formatter, $markerParams, $offset);
				$subpartArray['###'.$marker.'###'] = $out;
				$subpartArray['###'.$marker.'EMPTYLIST###'] = '';
				// Das Menu für den PageBrowser einsetzen
				if($pageBrowser) {
					$subpartArray['###PAGEBROWSER###'] = tx_rnbase_util_BaseMarker::fillPageBrowser(
									t3lib_parsehtml::getSubpart($template, '###PAGEBROWSER###'),
									$pageBrowser, $formatter, $confId.'pagebrowser.');
					$listSize = $pageBrowser->getListSize();
				}
				else {
					$listSize = count($dataArr);
				}
				$markerArray['###'.$marker.'COUNT###'] = $formatter->wrap($listSize, $confId.'count.');

				// charbrowser
				$pagerData = $viewData->offsetGet('pagerData');
				$charPointer = $viewData->offsetGet('charpointer');
				$subpartArray['###CHARBROWSER###'] = tx_rnbase_util_BaseMarker::fillCharBrowser(
															tx_rnbase_util_Templates::getSubpart($template, '###CHARBROWSER###'),
															$markerArray, $pagerData, $charPointer,
															$formatter->getConfigurations(), $confId.'charbrowser.');

				$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($templateList, $markerArray, $subpartArray);
			}
			else {
				// Support für EMPTYLIST-Block
				if(tx_rnbase_util_BaseMarker::containsMarker($template, $marker.'EMPTYLIST')) {
					$out = t3lib_parsehtml::getSubpart($template, '###'.$marker.'EMPTYLIST###');
				}
				else
					$out = $this->info->getEmptyListMessage($confId, $viewData, $formatter->getConfigurations());
			}
			$template = tx_rnbase_util_Templates::substituteSubpart($template, '###'.$outerMarker.'S###', $out, 0);
		}

		$markerArray = array();
		$subpartArray = array();
//		$subpartArray['###'.$outerMarker.'S###'] = $out;

		// Muss ein Formular mit angezeigt werden
		// Zuerst auf einen Filter prüfen
		$filter  =& $viewData->offsetGet('filter');
		if($filter) {
			$template = $filter->getMarker()->parseTemplate($template, $formatter, $confId.'filter.', $marker);
		}
		// Jetzt noch die alte Variante
		$markerArray['###SEARCHFORM###'] = '';
		$seachform  =& $viewData->offsetGet('searchform');
		if($seachform)
			$markerArray['###SEARCHFORM###'] = $seachform;

		$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		if($debug) {
			tx_rnbase::load('class.tx_rnbase_util_Misc.php');

			$wrapTime = tx_rnbase_util_FormatUtil::$time - $wrapTime;
			$wrapMem = tx_rnbase_util_FormatUtil::$mem - $wrapMem;
			tx_rnbase_util_Debug::debug(array(
					'Rows'=>count($dataArr),
					'Execustion time'=>(microtime(TRUE) -$time),
					'WrapTime'=>$wrapTime,
					'WrapMem'=>$wrapMem,
					'Memory start'=> $mem,
					'Memory consumed'=> (memory_get_usage()-$mem)
				), 'ListBuilder Statistics for: ' . $confId . ' Key: ' . $debugKey);
		}
		return $out;
	}

	protected function getOuterMarker($marker, $template) {
		$outerMarker = $marker;
		$len = strlen($marker)-1;
		if($marker{$len} == 'Y' &&
			!tx_rnbase_util_BaseMarker::containsMarker($template, $marker.'S###')) {
			$outerMarker = substr($marker, 0, $len).'IE';
		}
		return $outerMarker;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_ListBuilder.php']);
}
