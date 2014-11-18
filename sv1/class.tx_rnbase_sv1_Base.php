<?php
/**
 *
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
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Base service class
 * Class was originally written by Lars Heber for extension mklib.
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_sv1
 * @author René Nitzsche, Lars Heber, Michael Wagner
 */
abstract class tx_rnbase_sv1_Base extends t3lib_svbase {

	// 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE
	const DELETION_MODE_HIDE = 0;
	const DELETION_MODE_SOFTDELETE = 1;
	const DELETION_MODE_REALLYDELETE = 2;

	/**
	 * Return name of search class
	 *
	 * @return string
	 */
	abstract public function getSearchClass();

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search($fields, $options) {
		$searcher = tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
		return $searcher->search($fields, $options);
	}

	/**
	 * Search the item for the given uid
	 * 
	 * @param int $ct
	 * @return tx_rnbase_model_base
	 */
	public function get($uid) {
		$searcher = tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
		return tx_rnbase::makeInstance($searcher->getWrapperClass(), $uid);
	}

	/**
	 * Find all records
	 *
	 * @return array[tx_rnbase_model_base]
	 */
	public function findAll(){
		return $this->search(array(), array());
	}


	/************************
	 * Manipulation methods *
	 ************************/


	/**
	 * Dummy model instance
	 *
	 * @var tx_rnbase_model_base
	 */
	private $dummyModel;

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @return tx_rnbase_model_base
	 */
	private function getDummyModel() {
		if (!$this->dummyModel) {
			$searcher = tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
			$this->dummyModel = tx_rnbase::makeInstance($searcher->getWrapperClass(), array('uid' => 0));
		}
		return $this->dummyModel;
	}

	/**
	 * Create a new record
	 *
	 * @param array		$data
	 * @param string	$table
	 * @return int	UID of just created record
	 */
	public function create(array $data) {
		$model = $this->getDummyModel();
		$table = $model->getTableName();
		
//		tx_rnbase::load('tx_mklib_util_TCA');
//		$data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);

		tx_rnbase::load('tx_rnbase_util_DB');
		$newUid = tx_rnbase_util_DB::doInsert(
			$table,
			self::insertCrdateAndTimestamp($data, $table)
		);
		return $newUid;

	}

	/**
	 * Save model with new data
	 *
	 * Overwrite this method to specify a specialised method signature,
	 * and just call THIS method via parent::handleUpdate().
	 * Additionally, the deriving implementation may perform further checks etc.
	 *
	 * @param tx_rnbase_model_base	$model			This model is being updated.
	 * @param array					$data			New data
	 * @param string				$where			Override default restriction by defining an explicite where clause
	 * @return tx_rnbase_model_base Updated model
	 */
	public function handleUpdate(tx_rnbase_model_base $model, array $data, $where='') {

		$table = $model->getTableName();
		$uid = intval($model->getUid());

		if (!$where)
		$where = '1=1 AND `'.$table . '`.`uid`='.$uid;

		// remove uid if exists
		if(array_key_exists('uid', $data))
			unset($data['uid']);

		// Eleminate columns not in TCA
//		tx_rnbase::load('tx_mklib_util_TCA');
//		$data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);

		tx_rnbase::load('tx_rnbase_util_DB');
		tx_rnbase_util_DB::doUpdate($table, $where, self::insertTimestamp($data, $table));

		$model->reset();
		return $model;
	}

	/**
	 * Wrapper for actual deletion
	 *
	 * Delete records according to given ready-constructed "where" condition and deletion mode
	 *
	 * @param string	$table
	 * @param string	$where		Ready-to-use where condition containing uid restriction
	 * @param int		$mode		@see self::handleDelete()
	 */
	protected function delete($table, $where, $mode) {
		global $GLOBALS;
		tx_rnbase::load('tx_rnbase_util_DB');
		switch ($mode) {
			// Hide model
			case self::DELETION_MODE_HIDE:
				// Set hidden field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']))
				throw new Exception("tx_rnbase_sv1_Base->delete(): Cannot hide records in table $table - no \$TCA entry found!");

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] => 1);
//				self::doUpdate($table, $where, $data);
				tx_rnbase_util_DB::doUpdate($table, $where, self::insertTimestamp($data, $table));
				break;

				// Soft-delete model
			case self::DELETION_MODE_SOFTDELETE:
				// Set deleted field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['delete']))
				throw new Exception("tx_rnbase_sv1_Base->delete(): Cannot soft-delete records in table $table - no \$TCA entry found!");

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['delete'] => 1);
				tx_rnbase_util_DB::doUpdate($table, $where, self::insertTimestamp($data, $table));
				break;

				// Really hard-delete model
			case self::DELETION_MODE_REALLYDELETE:
				tx_rnbase_util_DB::doDelete($table, $where);
				break;

			default:
				throw new Exception("tx_rnbase_sv1_Base->delete(): Unknown deletion mode ($mode)");

		}
	}

	/**
	 * Delete one model
	 *
	 * Overwrite this method to specify a specialised method signature,
	 * and just call THIS method via parent::handleDelete().
	 * Additionally, the deriving implementation may perform further checks etc.
	 *
	 * @param tx_rnbase_model_base	$model		This model is being updated.
	 * @param string				$where		Override default restriction by defining an explicite where clause
	 * @param int					$mode		Deletion mode with the following options: 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE record.
	 * @param int					$table		Wenn eine Tabelle angegeben wird, wird die des Models missachtet (wichtig für temp anzeigen)
	 * @return tx_rnbase_model_base				Updated (on success actually empty) model.
	 */
	public function handleDelete(tx_rnbase_model_base $model, $where='', $mode=0, $table=NULL) {
		if(empty($table)) {
			$table = $model->getTableName();
		}
		
		$uid = intval($model->getUid());
		
		if (!$where) {
			$where = '1=1 AND `'.$table.'`.`uid`='.$uid;
		}

		$this->delete($table, $where, $mode);

		$model->reset();
		return $model;
	}

	/**
	 * Einen Datensatz in der DB anlegen
	 *
	 * Diese Methode kann in Child-Klassen einfach überschrieben werden um zusätzliche Funktionen
	 * zu implementieren. Dann natürlich nicht vergessen diese Methode via parent::handleCreation()
	 * aufzurufen ;)
	 *
	 * @param array $data
	 *
	 * @return model
	 */
	public function handleCreation(array $data){
		// datensatz anlegen and model holen
		$model = $this->get($this->create($data));
		return $model;
	}

	/*
	 * ******************************************************
	 * Private methods
	 * ******************************************************
	 */


	/**
	 * Insert crdate and timestamp into correct field (gotten from TCA)
	 *
	 * @param 	array 	$data
	 * @param 	string 	$tablename
	 * @return 	array
	 */
	private static function insertCrdateAndTimestamp($data, $tablename) {
		global $GLOBALS;
		// Force creation of timestamp
		if (
				isset($GLOBALS['TCA'][$tablename]['ctrl']['crdate'])
			&& !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']])
		) {
			$data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']] = time();
		}
			
		return self::insertTimestamp($data, $tablename);
	}

	/**
	 * Insert timestamp into correct field (gotten from TCA)
	 *
	 * @param 	array 	$data
	 * @param 	string 	$tablename
	 * @return 	array
	 */
	private static function insertTimestamp($data, $tablename) {
		global $GLOBALS;
		// Force creation of timestamp
		if (
				isset($GLOBALS['TCA'][$tablename]['ctrl']['tstamp'])
			&& !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']])
		) {
			$data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']] = time();
		}
			
		return $data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/sv1/class.tx_rnbase_sv1_Base.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/sv1/class.tx_rnbase_sv1_Base.php']);
}