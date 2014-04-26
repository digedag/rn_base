<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 René Nitzsche <rene@system25.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


/**
 * Basisklasse für Suchfunktionen in BE-Modulen
 *
 * @author René Nitzsche <rene@system25.de>
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_rnbase_mod_base_Lister {
	const KEY_SHOWHIDDEN = 'showhidden';


	/**
	 * Selector Klasse
	 * @var tx_rnbase_mod_IModule
	 */
	private $mod = NULL;
	/**
	 * Selector Klasse
	 * @var tx_mklib_mod1_util_Selector
	 */
	private $selector = NULL;
	/**
	 * Otions
	 * @var array
	 */
	protected $options = array();

	private $filterValues = array();

	/**
	 * Current hidden option
	 * @var 	string
	 */
	protected $currentShowHidden = 1;


	/**
	 * Constructor
	 *
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 * @param 	array 					$options
	 */
	public function __construct(tx_rnbase_mod_IModule $mod, array $options = array()) {
		$this->init($mod, $options);
	}

	/**
	 * Set a value to filter data
	 * @param string $key
	 * @param string $value
	 */
	public function setFilterValue($key, $value) {
		$this->filterValues[$key] = $value;
	}
	/**
	 * Returns a filter value
	 * @param string $key
	 */
	public function getFilterValue($key) {
		return $this->filterValues[$key];
	}
	public function clearFilterValues() {
		unset($this->filterValues);
		$this->filterValues = array();
	}

	/**
	 * Init object
	 *
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 * @param 	array 					$options
	 */
	protected function init(tx_rnbase_mod_IModule $mod, $options) {
		$this->options = $options;
		$this->mod = $mod;
	}


	/**
	 * @return string
	 */
	protected function getSearcherId(){
		// TODO: abstract??
//		tx_rnbase::load('tx_mklib_util_String');
//		$pageId = tx_mklib_util_String::toCamelCase(get_class($this));
//		return $pageId;
		return 'searcher';
	}

	/**
	 * Liefert den Service.
	 *
	 * @return tx_mklib_srv_Base
	 */
	abstract protected function getService();

	/**
	 * Returns the complete search form
	 * @return 	string
	 */
	public function getSearchForm() {
		$data = array();
		$options = array('pid' => $this->options['pid']);

		$this->setFilterValue('searchword', $this->showFreeTextSearchForm(
				$data['search'], $this->getSearcherId().'Search', $options));

		$this->setFilterValue(self::KEY_SHOWHIDDEN, $this->showHiddenSelector(
				$data['hidden'],
				$options
		));

		$this->addMoreFields($data, $options);
		if($updateButton = $this->getSearchButton())
			$data['updatebutton'] = array(
					'label' => '',
					'button'=> $updateButton
				);

		$out = $this->buildFilterTable($data);
		return $out;
	}

	/**
	 * Kindklassen haben die Möglichkeit weitere Formularfelder
	 * zu registrieren.
	 * @param array $data
	 * @param array $options
	 */
	protected function addMoreFields(&$data, &$options) {
		if (empty($options['pid'])) {
			$this->options['pid'] = $this->getModule()->getPid();
			if(isset($this->options['pid'])) {
				$options['pid'] = $this->options['pid'];
			}
		}
	}

	/**
	 * Returns the search button
	 * @return 	string|false
	 */
	protected function getSearchButton() {
		$out = $this->getFormTool()->createSubmit(
					$this->getSearcherId().'Search',
					'###LABEL_BTN_SEARCH###'
				);

		return $out;
	}

	/**
	 * Bildet die Resultliste mit Pager
	 *
	 * @param tx_mklib_mod1_searcher_Base $callingClass
	 * @param object $srv
	 * @param array $fields
	 * @param array $options
	 * @return string
	 */
	public function getResultList() {
		$srv = $this->getService();
		/* @var $pager tx_rnbase_util_BEPager */
		$pager = tx_rnbase::makeInstance(
				'tx_rnbase_util_BEPager',
				$this->getSearcherId().'Pager',
				$this->getModule()->getName(),
				$pid = $this->options['pid']
			);

		$fields = $options = array();
		$this->prepareFieldsAndOptions($fields, $options);

		// Get counted data
		$cnt = $this->getCount($fields, $options);

		$pager->setListSize($cnt);
		$pager->setOptions($options);

		// Get data
		$items = $srv->search($fields, $options);
		$content = '';
		$this->showItems($content, $items);
		$pagerData = $pager->render();

		//der zusammengeführte Pager für die Ausgabe
		//nur wenn es auch Ergebnisse gibt. sonst reicht die noItemsFoundMsg
		$sPagerData = '';
		if($cnt)
			$sPagerData = $pagerData['limits'] . ' - ' .$pagerData['pages'];

		return array(
				'table' 	=> $content,
				'totalsize' => $cnt,
				'pager' 	=> '<div class="pager">' . $sPagerData .'</div>',
			);
	}

	/**
	 * Kann von der Kindklasse überschrieben werden, um weitere Filter zu setzen.
	 *
	 * @param 	array 	$fields
	 * @param 	array 	$options
	 */
	protected function prepareFieldsAndOptions(array &$fields, array &$options) {
		$options['distinct'] = 1;
		self::buildFreeText($fields, $this->getFilterValue('searchword'), $this->getSearchColumns());

		if(($value = $this->getFilterValue(self::KEY_SHOWHIDDEN)) !== NULL) {
			// Wenn gesetzt, dann anzeigen
			if($value) {$options['enablefieldsbe'] = 1;}
			else { $options['enablefieldsfe'] = 1; }
		}
		$this->prepareSorting($options);

	}

	/**
	 * Sortierung vorbereiten
	 * @param array $options
	 */
	protected function prepareSorting(&$options) {
		$sortField = t3lib_div::_GET('sortField');
		$sortRev = t3lib_div::_GET('sortRev');

		if(!empty($sortField)) {
			$cols = $this->getColumns();

			if(!isset($cols[$sortField]) || !is_array($cols[$sortField]) || !isset($cols[$sortField]['sortable'])) {
				return;
			}

			// das Label in die notwendige SQL-Anweisung umwandeln. Normalerweise ein Spaltenname.
			$sortCol = $cols[$sortField]['sortable'];
			// Wenn am Ende ein Punkt steht, muss die Spalte zusammengefügt werden.
			$sortCol = substr($sortCol, -1) === '.' ? $sortCol.$sortField : $sortCol;
			$options['orderby'][$sortCol] = (strtolower($sortRev)== 'asc' ? 'asc':'desc');
		}
	}

	/**
	 * Liefert die Spalten, in denen gesucht werden soll
	 * @return array
	 */
	protected abstract function getSearchColumns();

	/**
	 * Start creation of result list.
	 * @param 	string 	$content
	 * @param 	array 	$items
	 * @return 	string
	 */
	protected function showItems(&$content, array $items) {
		if(count($items) === 0) {
			$content = $this->getNoItemsFoundMsg();
			return;//stop
		}

		$options = $this->getOptions();
		$decorator = $this->createDefaultDecorator();
		$columns = $this->getColumns($decorator);
		if(array_key_exists('linker', $columns)) {
			$options['linker'] = $columns['linker'];
			unset($columns['linker']);
		}
		tx_rnbase::load('tx_rnbase_mod_Tables');
		$arr = tx_rnbase_mod_Tables::prepareTable($items, $columns, $this->getFormTool(), $options);
		$out = $this->getModule()->getDoc()->table($arr[0]);
		$content .= $out;
		return $out;
	}
	/**
	 *
	 * @return tx_rnbase_mod_IDecorator
	 */
	protected abstract function createDefaultDecorator();

	/**
	 * Liefert die Spalten für den Decorator.
	 * @param 	tx_mklib_mod1_decorator_Base 	$oDecorator
	 * @return 	array
	 */
	protected function getColumns(){
		return array(
				'uid' => array(
					'title' => 'label_tableheader_uid',
				),
				'actions' => array(
					'title' => 'label_tableheader_actions',
				)
			);
	}

	/**
	 * Returns an instance of tx_mkhoga_beutil_Selector.
	 * Der Selector wird erst erzeugt, wenn er benötigt wird
	 * TODO: entfernen!!
	 * @return 	tx_mklib_mod1_util_Selector
	 */
	protected function getSelector() {
		if(!$this->selector) {
			$this->selector = tx_rnbase::makeInstance('tx_mklib_mod1_util_Selector');
			$this->selector->init($this->getModule());
		}
		return $this->selector;
	}

	/**
	 *
	 * @param array $fields
	 * @param array $options
	 */
	protected function getCount(array &$fields, array $options){
		// Get counted data
		$options['count'] = 1;
		return $this->getService()->search($fields, $options);
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getOptions() {
		return $this->options;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->mod->getFormTool();
	}

	/**
	 * Returns the message in case no items could be found in showItems()
	 *
	 * @return 	string
	 */
	protected function getNoItemsFoundMsg() {
		return '<p><strong>###LABEL_NO_'.strtoupper($this->getSearcherId()).'_FOUND###</strong></p><br/>';;
	}



//////
// Die folgenden Methoden sollten noch in andere Klassen verteilt werden.
//////

	/**
	 * Suche nach einem Freitext. Wird ein leerer String
	 * übergeben, dann wird nicht gesucht.
	 *
	 * @param array $fields
	 * @param string $searchword
	 * @param array $cols
	 */
	protected static function buildFreeText(&$fields, $searchword, array $cols = array()) {
		$result = FALSE;
	  	if(strlen(trim($searchword))) {
	   		$joined['value'] = trim($searchword);
	   		$joined['cols'] = $cols;
	   		$joined['operator'] = OP_LIKE;
	   		$fields[SEARCH_FIELD_JOINED][] = $joined;
	   		$result = TRUE;
	  	}
	  	return $result;
	}

	/**
	 *
	 * @param 	array 	$data
	 * @return 	string
	 */
	protected function buildFilterTable(array $data){
		$out = '';
		if(count($data)){
			$out .= '<table class="filters">';
			foreach($data as $label => $filter){
				$out .= '<tr>';
				$out .= '<td>'. (isset($filter['label']) ? $filter['label'] : $label).'</td>';
				unset($filter['label']);
				$out .= '<td>'. implode(' ', $filter) .'</td>';

				$out .= '</tr>';
			}
			$out .= '</table>';
		}
		return $out;
	}
	/**
	 * Method to display a form with an input array, a description and a submit button.
	 * Keys are 'field' and 'button'.
	 *
	 * @param string $out marker array with input fields
	 * @param string $key mod key
	 * @param array $options
	 * 				string 	buttonName 		name of the submit button. default is key.
	 * 				string 	buttonValue 	value of the sumbit button. default is LLL:label_button_search.
	 * 				string 	label 			label of the sumbit button. default is LLL:label_search.
	 * @return string search term
	 */
	protected function showFreeTextSearchForm (&$marker, $key, array $options = array()) {
		tx_rnbase::load('tx_rnbase_mod_Util');
		$searchstring = tx_rnbase_mod_Util::getModuleValue($key, $this->getModule(), array('changed' => t3lib_div::_GP('SET')));

		// Erst das Suchfeld, danach der Button.
		$marker['field'] 	= $this->getFormTool()->createTxtInput('SET['.$key.']', $searchstring, 10);
		$marker['label'] = $options['label'] ? $options['label'] : '###LABEL_SEARCH###';

		return $searchstring;
	}

	protected function showHiddenSelector(&$marker, $options=array()) {
		$items = array(
				0 => $GLOBALS['LANG']->getLL('label_select_hide_hidden'),
				1 => $GLOBALS['LANG']->getLL('label_select_show_hidden'),
		);
		tx_rnbase::load('tx_rnbase_mod_Util');
		$selectedItem = tx_rnbase_mod_Util::getModuleValue('showhidden', $this->getModule(), array('changed' => t3lib_div::_GP('SET')));

//		$selectedItem = array_key_exists('forcevalue', $aOptions) ? $aOptions['forcevalue'] : $this->getValueFromModuleData($id);
		$options['label'] = $options['label'] ? $options['label'] : $GLOBALS['LANG']->getLL('label_hidden');
		return tx_rnbase_mod_Util::showSelectorByArray($items, $selectedItem, 'showhidden', $marker, $options);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/base/class.tx_rnbase_mod_base_Lister.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/base/class.tx_rnbase_mod_base_Lister.php']);
}