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

tx_rnbase::load('tx_rnbase_util_SearchBase');


interface tx_rnbase_IFilter {
	/**
	 * Initialisiert den Filter
	 *
	 * @param array $fields
	 * @param array $options
	 */
	public function init(&$fields, &$options);

	/**
	 * Liefert den Marker für den Filter
	 * @return tx_rnbase_FilterMarker
	 */
	public function getMarker();

	/**
	 * Whether or not the result list should be displayed.
	 * It is up to the list view to handle this result.
	 * This can be used to hide a result output if a search view is
	 * initially displayed.
	 * @return boolean
	 */
	public function hideResult();
	/**
	 * Whether or not a user defined search is activated. This means some functions
	 * like showing a charbrowser should be ignored.
	 * @return boolean
	 */
	public function isSpecialSearch();
}
interface tx_rnbase_IFilterMarker {
  function parseTemplate($template, &$formatter, $confId, $marker = 'FILTER');
}

class tx_rnbase_filter_BaseFilter implements tx_rnbase_IFilter, tx_rnbase_IFilterMarker {
	private $configurations;
	private $parameters;
	private $confId;
	protected $filterItems;

	public function tx_rnbase_filter_BaseFilter(&$parameters, &$configurations, $confId) {
		$this->configurations = $configurations;
		$this->parameters = $parameters;
		$this->confId = $confId;
	}
	/**
	 * Liefert das Config-Objekt
	 *
	 * @return tx_rnbase_configurations
	 */
	protected function getConfigurations() {
		return $this->configurations;
	}
	/**
	 * Liefert die Parameter
	 *
	 * @return tx_rnbase_parameters
	 */
	protected function getParameters() {
		return $this->parameters;
	}
	/**
	 * Liefert die Basis-ConfigId. Diese sollte immer mit einem Punkt enden: myview.
	 *
	 * @return string
	 */
	protected function getConfId() {
		return $this->confId;
	}
	/**
	 * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen
	 *
	 * @param array $fields
	 * @param array $options
	 * @return boolean if FALSE no search should be done
	 */
	public function init(&$fields, &$options) {
		tx_rnbase_util_SearchBase::setConfigFields($fields, $this->getConfigurations(), $this->getConfId().'fields.');
		// Optionen
		tx_rnbase_util_SearchBase::setConfigOptions($options, $this->getConfigurations(), $this->getConfId().'options.');

		return $this->initFilter($fields, $options, $this->getParameters(), $this->getConfigurations(), $this->getConfId());
	}
	public function hideResult() {
		return FALSE;
	}
	/**
	 * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen
	 *
	 * @param array $fields
	 * @param array $options
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return boolean
	 */
	protected function initFilter(&$fields, &$options, &$parameters, &$configurations, $confId) {
		return TRUE;
	}
	/**
	 * Hilfsmethode zum Setzen von Filtern aus den Parametern. Ein schon gesetzter Wert im Field-Array
	 * wird nicht überschrieben. Die
	 *
	 * @param string $idstr
	 * @param array $fields
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param string $operator Operator-Konstante
	 */
	function setField($idstr, &$fields, &$parameters, &$configurations, $operator = OP_LIKE) {
		// Wenn der Wert schon gesetzt ist, wird er nicht überschrieben
		if(!isset($fields[$idstr][$operator]) && $parameters->offsetGet($idstr)) {
			$fields[$idstr][$operator] = $parameters->offsetGet($idstr);
			// Parameter als KeepVar merken TODO: Ist das noch notwendig
			$configurations->addKeepVar($configurations->createParamName($idstr), $fields[$idstr]);
		}
	}

	public function addFilterItem(tx_rnbase_IFilterItem $item) {
		$this->filterItems[] = $item;
	}
	/**
	 * Returns all filter items set.
	 *
	 * @return array[tx_rnbase_IFilterItem]
	 */
	public function getFilterItems() {
		return $this->filterItems;
	}
	/**
	 * Fabrikmethode zur Erstellung von Filtern. Die Klasse des Filters kann entweder direkt angegeben werden oder
	 * wird über die Config gelesen. Klappt beides nicht, wird der Standardfilter geliefert.
	 *
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array_object $viewData
	 * @param string $confId ConfId des Filters
	 * @param string $filterClass Klassenname des Filters
	 * @return tx_rnbase_IFilter
	 */
	static function createFilter($parameters, $configurations, $viewData, $confId, $filterClass = '') {
		$filterClass = ($filterClass) ? $filterClass : $configurations->get($confId.'class');
		$filterClass = ($filterClass) ? $filterClass : $configurations->get($confId.'filter');
		$filterClass = ($filterClass) ? $filterClass : 'tx_rnbase_filter_BaseFilter';
		$filter = tx_rnbase::makeInstance($filterClass, $parameters, $configurations, $confId);
		if(is_object($viewData))
			$viewData->offsetSet('filter', $filter);
		return $filter;
	}

	/**
	 * Whether or not a charbrowser should be ignored
	 * @return boolean
	 */
	public function isSpecialSearch() {
		// In den meisten Projekten liegen die Nutzerdaten im Array inputData
		return is_array($this->inputData) && count($this->inputData);
	}

	public function getMarker() {
		return $this;
	}
	/**
	 * Liefert einfach das Template zurück. Ein echter FilterMarker hat hier die Möglichkeit sein
	 * Such-Formular in das HTML-Template zu schreiben.
	 *
	 * @param string $template HTML-Template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	function parseTemplate($template, &$formatter, $confId, $marker = 'FILTER') {
		return $template;
	}

	/**
	 * Pagebrowser vorbereiten
	 *
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confid Die Confid des PageBrowsers. z.B. myview.org.pagebrowser ohne Punkt!
	 * @param ArrayObject $viewdata
	 * @param array $fields
	 * @param array $options
	 * @param array $cfg You have to set 'searchcallback' and optional 'pbid'
   */
	public static function handlePageBrowser(&$configurations, $confid, &$viewdata, &$fields, &$options, $cfg = array()) {
		$confid .= '.';
		if(is_array($configurations->get($confid))) {
			$searchCallback=$cfg['searchcallback'];
			if(!$searchCallback) throw new Exception('No search callback defined!');
			// Die Gesamtzahl der Items ist entweder im Limit gesetzt oder muss ermittelt werden
			$listSize = intval($options['limit']);
			if(!$listSize) {
				// Mit Pagebrowser benötigen wir zwei Zugriffe, um die Gesamtanzahl der Items zu ermitteln
				$pageBrowserOptions = $options;
				$pageBrowserOptions['count'] = 1;
				// eigenes what?
				if ($configurations->get($confid . 'what')) {
					$pageBrowserOptions['what'] = $configurations->get($confid . 'what');
				}

				$listSize = call_user_func($searchCallback, $fields, $pageBrowserOptions);
				//$listSize = $service->search($fields, $options);
				unset($options['count']);
			}
			// PageBrowser initialisieren
			$pbId = $cfg['pbid'] ? $cfg['pbid'] : 'pb';
			/**
			 * @var tx_rnbase_util_PageBrowser $pageBrowser
			 */
			$pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', $pbId);
			$pageSize = intval($configurations->get($confid.'limit'));
			$pageBrowser->setState($configurations->getParameters(), $listSize, $pageSize);

			// Nach dem Item nur suchen wenn über die Parameter kein Pointer gesetzt wurde.
			if (is_array($cfg['pointerFromItem'])
				&& !$configurations->getParameters()->offsetExists($pageBrowser->getParamName('pointer'))
				&& ($itemId = $configurations->getParameters()->get($cfg['pointerFromItem']['param']))) {
				// Wir erzeugen uns das SQl der eigentlichen Abfrage.
				// Dabei wollen wir auch die rownum haben!
				$sql = call_user_func($searchCallback,
							$fields, array_merge($options, array('sqlonly'=>1, 'rownum'=>1))
				);
				// Jetzt besorgen wir uns die Position des aktuellen Eintrages
				$res = tx_rnbase_util_DB::doSelect(
					'ROW.rownum',
					'('.$sql.') as ROW',
					array(
						'where' => 	'ROW.'.$cfg['pointerFromItem']['field'].'='.
									$GLOBALS['TYPO3_DB']->fullQuoteStr($itemId, ''),
						'enablefieldsoff' => TRUE,
					)
				);
				// Jetzt haben wir ein Ergebnis, mit der Zeilennummer des Datensatzes.
				if (!empty($res)) {
					$rownum = intval($res[0]['rownum']);
					// Wir berechnen die Seite, auf der sich der aktuelle Eintrag befindet.
					// intval schneidet die Dezimalzahlen ab, erspart uns das runden.
					// -1, weil Bei 10 Einträgen pro Seite rownum 20 auf seite 2 ist,
					// 20/10 allerdings 2 (für seite 3) ergibt.
					$pageBrowser->setPointer(intval(($rownum - 1) / $pageSize));
				}
			}

			$limit = $pageBrowser->getState();
			$options = array_merge($options, $limit);
			if($viewdata)
				$viewdata->offsetSet('pagebrowser', $pageBrowser);
		}
	}

	/**
	 * Bindet einen Buchstaben-Browser ein
	 *
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confid
	 * @param ArrayObject $viewData
	 * @param array $fields
	 * @param array $options
	 * @param array $cfg You have to set 'colname'. The database column used for character browsing.
	 */
	public static function handleCharBrowser(&$configurations, $confid, &$viewData, &$fields, &$options, $cfg = array()) {

		if($configurations->get($confid)) {
			$colName = $cfg['colname'];
			if(!$colName) throw new Exception('No column name for charbrowser defined');

			$pagerData = self::findPagerData($fields, $options, $cfg);

			$firstChar = $configurations->getParameters()->offsetGet($pagerData['pointername']);
			$firstChar = (strlen(trim($firstChar)) > 0) ? substr($firstChar, 0, ($firstChar{0} == '0' ? 3:1)) : $pagerData['default'];
			// Existiert der Point in den aktuellen Daten
			$firstChar = array_key_exists($firstChar, $pagerData['list']) ? $firstChar : $pagerData['default'];
			$viewData->offsetSet('pagerData', $pagerData);
			$viewData->offsetSet('charpointer', $firstChar);
		}
		$filter = $viewData->offsetGet('filter');
		// Der CharBrowser beachten wir nur, wenn keine Suche aktiv ist
		// TODO: Der Filter sollte eine Methode haben, die sagt, ob ein Formular aktiv ist
		if($firstChar != '' && !$filter->isSpecialSearch()) {
			$specials = tx_rnbase_util_SearchBase::getSpecialChars();
			$firsts = $specials[$firstChar];
			if($firsts) {
				$firsts = implode('\',\'', $firsts);
			}
			else $firsts = $firstChar;

			if($fields[SEARCH_FIELD_CUSTOM]) $fields[SEARCH_FIELD_CUSTOM] .= ' AND ';
			$fields[SEARCH_FIELD_CUSTOM] .= 'LEFT(UCASE('.$colName."),1) IN ('$firsts') ";
		}
	}

	/**
	 * Wir verwenden einen alphabetischen Pager. Also muß zunächst ermittelt werden, welche
	 * Buchstaben überhaupt vorkommen.
	 * @param tx_cfcleaguefe_ProfileService $service
	 * @param tx_rnbase_configurations $configurations
	 */
	private static function findPagerData($fields, $options, $cfg) {
		$colName = $cfg['colname'];

		$searchCallback=$cfg['searchcallback'];
		if(!$searchCallback) throw new Exception('No search callback defined!');


		$options['what'] = 'LEFT(UCASE('.$colName.'),1) As first_char, count(LEFT(UCASE('.$colName.'),1)) As size';
		$options['groupby'] = 'LEFT(UCASE('.$colName.'),1)';
		unset($options['limit']);

		$rows = call_user_func($searchCallback, $fields, $options);

		$specials = tx_rnbase_util_SearchBase::getSpecialChars();
		$wSpecials = array();
		foreach($specials As $key => $special) {
			foreach ($special As $char) {
				$wSpecials[$char] = $key;
			}
		}

		$ret = array();
		foreach($rows As $row) {
			if(array_key_exists(($row['first_char']), $wSpecials)) {
				$ret[$wSpecials[$row['first_char']]] = intval($ret[$wSpecials[$row['first_char']]]) + $row['size'];
			}
			else
				$ret[$row['first_char']] = $row['size'];
		}

		if($cfg['specials'] == 'last' && isset($ret['0-9'])) {
			$specials = $ret['0-9'];
			unset($ret['0-9']);
			$ret['0-9'] = $specials;
		}

		$current = 0;
		if(count($ret)) {
			$keys = array_keys($ret);
			$current = $keys[0];
		}
		$data['list'] = $ret;
		$data['default'] = $current;
		$data['pointername'] = array_key_exists('cbid', $cfg) && $cfg['cbid'] ? $cfg['cbid'] : 'charpointer';

		return $data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php']);
}
