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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');

tx_div::load('tx_rnbase_util_SearchBase');


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
	 */
	public function init(&$fields, &$options) {
		tx_rnbase_util_SearchBase::setConfigFields($fields, $this->getConfigurations(), $this->getConfId().'fields.');
		// Optionen
		tx_rnbase_util_SearchBase::setConfigOptions($options, $this->getConfigurations(), $this->getConfId().'options.');

		$this->initFilter($fields, $options, $this->getParameters(), $this->getConfigurations(), $this->getConfId());
	}
	public function hideResult() {
		return false;
	}
	/**
	 * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen
	 *
	 * @param array $fields
	 * @param array $options
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	protected function initFilter(&$fields, &$options, &$parameters, &$configurations, $confId) {
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
			$configurations->addKeepVar($configurations->createParamName($idstr),$fields[$idstr]);
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
		$filterClass = ($filterClass) ? $filterClass : $configurations->get($confId.'filter');
		$filterClass = ($filterClass) ? $filterClass : 'tx_rnbase_filter_BaseFilter';
		$filterClass = tx_div::makeInstanceClassname($filterClass);
		$filter = new $filterClass($parameters, $configurations, $confId);
		if(is_object($viewData))
			$viewData->offsetSet('filter', $filter);
		return $filter;
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php']);
}
?>