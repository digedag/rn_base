<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2015 René Nitzsche <rene@system25.de>
 * All rights reserved
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

tx_rnbase::load('Tx_Rnbase_Repository_InterfaceSearch');
tx_rnbase::load('Tx_Rnbase_Interface_Singleton');

/**
 * Abstracte Repository Klasse
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 */
abstract class Tx_Rnbase_Repository_AbstractRepository
	implements Tx_Rnbase_Repository_InterfaceSearch, Tx_Rnbase_Interface_Singleton
{

	/**
	 * Liefert den Namen der Suchklasse
	 *
	 * @return 	string
	 */
	abstract protected function getSearchClass();

	/**
	 * Liefert den Searcher
	 *
	 * @return 	tx_rnbase_util_SearchBase
	 */
	protected function getSearcher() {
		tx_rnbase::load('tx_rnbase_util_SearchBase');
		$searcher = tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
		if (!$searcher instanceof tx_rnbase_util_SearchBase) {
			throw new Exception(
				get_class($this) . '->getSearchClass() has to return a classname' .
				' of class which extends tx_rnbase_util_SearchBase!'
			);
		}
		return $searcher;
	}

	/**
	 * Liefert die Model Klasse.
	 *
	 * @return 	string
	 */
	protected function getWrapperClass() {
		return $this->getSearcher()->getWrapperClass();
	}

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @return tx_rnbase_model_base
	 */
	public function getEmptyModel() {
		return tx_rnbase::makeInstance($this->getWrapperClass());
	}

	/**
	 * Holt einen bestimmten Datensatz aus dem Repo.
	 *
	 * @param integer|array $rowOrUid
	 * @return tx_rnbase_model_base|null
	 */
	public function findByUid($rowOrUid) {
		/* @var $model tx_rnbase_model_base */
		$model = tx_rnbase::makeInstance(
			$this->getWrapperClass(),
			$rowOrUid
		);
		return $model->isPersisted() && $model->isValid() ? $model : NULL;
	}

	/**
	 * @return array[tx_rnbase_model_base]
	 */
	public function findAll() {
		return $this->search(array(), array());
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search(array $fields, array $options) {
		$this->prepareFieldsAndOptions($fields, $options);
		$items = $this->getSearcher()->search($fields, $options);
		return $this->prepareItems($items, $options);
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return tx_rnbase_model_base
	 */
	public function searchSingle(array $fields = array(), array $options = array()) {
		$options['limit'] = 1;
		$items =  $this->search($fields, $options);
		return !empty($items[0]) ? $items[0] : NULL;
	}

	/**
	 * On default, return hidden and deleted fields in backend
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function prepareFieldsAndOptions(&$fields, &$options) {
		$this->handleEnableFieldsOptions($fields, $options);
		$this->handleLanguageOptions($fields, $options);
	}


	/**
	 * On default, return hidden and deleted fields in backend
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function handleEnableFieldsOptions(&$fields, &$options) {
		if (
			TYPO3_MODE == 'BE' &&
			!isset($options['enablefieldsoff']) &&
			!isset($options['enablefieldsbe']) &&
			!isset($options['enablefieldsfe'])
		) {
			$options['enablefieldsbe'] = TRUE;
		}
	}

	/**
	 * Setzt eventuelle Sprachparameter,
	 * damit nur valide Daten für die aktuelle Sprache ausgelesen werden.
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function handleLanguageOptions(&$fields, &$options) {
		if (
			!isset($options['i18n'])
			&& !isset($options['ignorei18n'])
			&& !isset($options['enablefieldsoff'])
		) {
			$tableName = $this->getEmptyModel()->getTableName();
			$languageField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);;
			// Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
			if (!empty($languageField)) {
				$tsfe = tx_rnbase_util_TYPO3::getTSFE();
				$languages = array();
				if (isset($options['additionali18n'])) {
					$languages = tx_rnbase_util_Strings::trimExplode(',', $options['additionali18n'], TRUE);
				}
				$languages[] = '-1'; // for all languages
				// Wenn eine bestimmte Sprache gesetzt ist,
				// laden wir diese ebenfalls.
				if (is_object($tsfe) && $tsfe->sys_language_content) {
					$languages[] = $tsfe->sys_language_content;
				}
				// andernfalls nutzen wir die default sprache
				else {
					$languages[] = '0'; // default language
				}
				$options['i18n'] = implode(',', array_unique($languages, SORT_NUMERIC));
			}
		}
	}

	/**
	 * Modifiziert die Ergebisliste
	 *
	 * @param array $items
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	protected function prepareItems($items, $options) {
		if (!is_array($items)) {
			return $items;
		}
		$items = $this->uniqueItems($items, $options);
		return $items;
	}

	/**
	 * Entfernt alle doppelten Datensatze, wenn die Option distinct gesetzt ist.
	 * Dabei werden die Sprachoverlays bevorzugt.
	 *
	 * @param array $items
	 * @param unknown_type $options
	 * @return array[tx_rnbase_model_base]
	 */
	protected function uniqueItems(array $items, $options) {
		// uniqueue, if there are models and the distinct option
		if (
			reset($items) instanceof tx_rnbase_model_base
			&& isset($options['distinct'])
			&& $options['distinct']
		) {
			// seperate master and overlays
			$master = $overlay = array();
			/* @var $item tx_rnbase_model_base */
			foreach ($items as $item) {
				$uid = (int) $item->getUid();
				$realUid = (int) $item->getProperty('uid');
				if ($uid === $realUid) {
					$master[$uid] = $item;
				} else {
					$overlay[$uid] = $item;
				}
			}
			// merge master and overlays and keep the order!
			$new = array();
			// uniquemode can be master or overlay!
			$preferOverlay = empty($options['uniquemode']) || strtolower($options['uniquemode']) !== 'master';
			foreach ($items as $item) {
				$uid = (int) $item->getUid();
				$new[$uid] = !empty($overlay[$uid]) && $preferOverlay ? $overlay[$uid] : $master[$uid];
			}
			$items = array_values($new);
		}
		return $items;
	}

	/************************
	 * Manipulation methods *
	 ************************/
	// @TODO Manipulation methods with own interface

}
