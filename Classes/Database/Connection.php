<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2013 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('Tx_Rnbase_Interface_Singleton');

/**
 * Contains utility functions for database access
 *
 * Tx_Rnbase_Database_Connection
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Rene Nitzsche <rene@system25.de>
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Database_Connection
	implements Tx_Rnbase_Interface_Singleton
{

	/**
	 * returns an instance of this class.
	 *
	 * @return Tx_Rnbase_Database_Connection
	 */
	public static function getInstance() {
		return tx_rnbase::makeInstance(get_called_class());
	}

	/**
	 * Generische Schnittstelle für Datenbankabfragen. Anstatt vieler Parameter wird hier ein
	 * Hash als Parameter verwendet, der mögliche Informationen aufnimmt.
	 * Es sind die folgenden Parameter zulässig:
	 * <pre>
	 * - 'where' - the Where-Clause
	 * - 'groupby' - the GroupBy-Clause
	 * - 'orderby' - the OrderBy-Clause
	 * - 'sqlonly' - returns the generated SQL statement. No database access.
	 * - 'limit' - limits the number of result rows
	 * - 'wrapperclass' - A wrapper for each result rows
	 * - 'pidlist' - A list of page-IDs to search for records
	 * - 'recursive' - the recursive level to search for records in pages
	 * - 'enablefieldsoff' - deactivate enableFields check
	 * - 'enablefieldsbe' - force enableFields check for BE (this usually ignores hidden records)
	 * - 'enablefieldsfe' - force enableFields check for FE
	 * - 'db' - external database: tx_rnbase_util_db_IDatabase
	 * - 'ignorei18n' - do not translate record to fe language
	 * - 'i18nolmode' - translation mode, possible value: 'hideNonTranslated'
	 * </pre>
	 * @param string $what requested columns
	 * @param string $from either the name of on table or an array with index 0 the from clause
	 *              and index 1 the requested tablename and optional index 2 a table alias to use.
	 * @param array $arr the options array
	 * @param boolean $debug = 0 Set to 1 to debug sql-String
	 */
	public function doSelect($what, $from, $arr, $debug=0) {
		$debug = $debug ? $debug : intval($arr['debug']) > 0;
		if($debug) {
			$time = microtime(TRUE);
			$mem = memory_get_usage();
		}
		$tableName = $from;
		$fromClause = $from;
		if(is_array($from)){
			$tableName = $from[1];
			$fromClause = $from[0];
			$tableAlias = isset($from[2]) && strlen(trim($from[2])) > 0  ? trim($from[2]) : FALSE;
		}

		$where = is_string($arr['where']) ? $arr['where'] : '1=1';
		$groupBy = is_string($arr['groupby']) ? $arr['groupby'] : '';
		if($groupBy) {
			$groupBy .= is_string($arr['having']) > 0 ? ' HAVING ' . $arr['having'] : '';

		}
		$orderBy = is_string($arr['orderby']) ? $arr['orderby'] : '';
		$offset = intval($arr['offset']) > 0 ? intval($arr['offset']) : 0;
		$limit = intval($arr['limit']) > 0 ? intval($arr['limit']) : '';
		$pidList = is_string($arr['pidlist']) ? $arr['pidlist'] : '';
		$recursive = intval($arr['recursive']) ? intval($arr['recursive']) : 0;
		$i18n = is_string($arr['i18n']) > 0 ? $arr['i18n'] : '';
		$sqlOnly = intval($arr['sqlonly']) > 0 ? intval($arr['sqlonly']) : '';
		$union = is_string($arr['union']) > 0 ? $arr['union'] : '';

		// offset und limit kombinieren
		// bei gesetztem limit ist offset optional
		if($limit) {
			$limit = ($offset > 0) ? $offset . ',' . $limit : $limit;
		}
		// Bei gesetztem Offset ist limit Pflicht (default 1000)
		elseif($offset) {
			$limit = ($limit > 0) ? $offset . ',' . $limit : $offset . ',1000';
		}
		else $limit = '';

		$where .= $this->handleEnableFieldsOptions($arr, $tableName, $tableAlias);

		// Das sollte wegfallen. Die OL werden weiter unten geladen
		if(strlen($i18n) > 0) {
			$i18n = implode(',', tx_rnbase_util_Strings::intExplode(',', $i18n));
			$where .= ' AND ' . ($tableAlias ? $tableAlias : $tableName) . '.sys_language_uid IN (' . $i18n . ')';
		}

		if (strlen($pidList) > 0) {
			$where .= ' AND ' . ($tableAlias ? $tableAlias : $tableName) . '.pid IN (' . tx_rnbase_util_DB::_getPidList($pidList, $recursive) . ')';
		}

		if (strlen($union) > 0) {
			$where .= ' UNION ' . $union;
		}

		$database = $this->getDatabaseConnection($arr);
		if($debug || $sqlOnly) {
			$sql = $database->SELECTquery($what, $fromClause, $where, $groupBy, $orderBy, $limit);
			if($sqlOnly) return $sql;
			if($debug) {
				tx_rnbase_util_Debug::debug($sql, 'SQL');
				tx_rnbase_util_Debug::debug(array($what, $from, $arr));
			}
		}

		$storeLastBuiltQuery = $database->store_lastBuiltQuery;
		$database->store_lastBuiltQuery = TRUE;
		$res = $this->watchOutDB(
			$database->exec_SELECTquery(
				$what,
				$fromClause,
				$where,
				$groupBy,
				$orderBy,
				$limit
			), $database
		);
		$database->store_lastBuiltQuery = $storeLastBuiltQuery;

		// use classic arrays or the collection
		// should be ever an collection, but for backward compatibility is this an array by default
		$rows = array();
		if ($arr['collection']) {
			if (!is_string($arr['collection']) || !class_exists($arr['collection'])) {
				$arr['collection'] = 'Tx_Rnbase_Domain_Collection_Base';
			}
			$rows = tx_rnbase::makeInstance(
				$arr['collection'],
				$rows
			);
		}

		if($this->testResource($res)) {
			$wrapper = is_string($arr['wrapperclass']) ? trim($arr['wrapperclass']) : 0;
			$callback = isset($arr['callback']) ? $arr['callback'] : FALSE;

			while (($row = $database->sql_fetch_assoc($res))) {
				// Workspacesupport
				$this->lookupWorkspace($row, $tableName, $arr);
				$this->lookupLanguage($row, $tableName, $arr);
				if(!is_array($row)) continue;
				$item = ($wrapper) ? tx_rnbase::makeInstance($wrapper, $row) : $row;
				if (
					$item instanceof Tx_Rnbase_Domain_Model_DynamicTableInterface
					// @TODO: backward compatibility for old models will be removed soon
					|| $item instanceof tx_rnbase_model_base
				) {
					$item->setTablename($tableName);
				}
				if($callback) {
					call_user_func($callback, $item);
					unset($item);
				}
				else {
					if (is_array($rows)) {
						$rows[] = $item;
					} else {
						$rows->append($item);
					}
				}
			}
			$database->sql_free_result($res);
		}

		if ($debug) {
			tx_rnbase_util_Debug::debug(array(
				'Rows retrieved ' => count($rows),
				'Time ' => (microtime(TRUE) - $time),
				'Memory consumed ' => (memory_get_usage() - $mem),
			), 'SQL statistics');
		}

		return $rows;
	}

	/**
	 * The ressourc has to be a valid ressource or an mysqli instance
	 *
	 * @param mixed $res
	 * @return boolean
	 */
	private function testResource($res) {
		return is_resource($res) || $res instanceof mysqli_result;
	}

	/**
	 *
	 * @param array $row
	 */
	private function lookupWorkspace(&$row, $tableName, $options) {
		$sysPage = tx_rnbase_util_TYPO3::getSysPage();
		if (!$sysPage->versioningPreview || $options['enablefieldsoff'] || $options['ignoreworkspace']) {
			return;
		}
		$sysPage->versionOL($tableName, $row);
		$sysPage->fixVersioningPid($tableName, $row);
	}

	/**
	 * Autotranslate a record to fe language
	 * @param array $row
	 * @param string $tableName
	 * @param array $options
	 */
	private function lookupLanguage(&$row, $tableName, $options) {
		// ACHTUNG: Bei Aufruf im BE führt das zu einem Fehler in TCE-Formularen. Die
		// Initialisierung der TSFE ändert den backPath im PageRender auf einen falschen
		// Wert. Dadurch werden JS-Dateien nicht mehr geladen.
		// Ist dieser Aufruf im BE überhaupt sinnvoll?
		if(!(defined('TYPO3_MODE') && TYPO3_MODE === 'FE')) {
			return;
		}

		// Then get localization of record:
		// (if the content language is not the default language)
		$tsfe = tx_rnbase_util_TYPO3::getTSFE();
		$sysPage = tx_rnbase_util_TYPO3::getSysPage();
		if (!is_object($tsfe) || !$tsfe->sys_language_content || $options['enablefieldsoff'] || $options['ignorei18n']) {
			return;
		}
		$OLmode = (isset($options['i18nolmode']) ? $options['i18nolmode'] : '');
		$row = $sysPage->getRecordOverlay($tableName, $row, $tsfe->sys_language_content, $OLmode);
	}

	public function enableFields($tableName, $mode, $tableAlias='') {
		$sysPage = tx_rnbase_util_TYPO3::getSysPage();
		$enableFields = $sysPage->enableFields($tableName, $mode);
		if($tableAlias) {
			// Replace tablename with alias
			$enableFields = str_replace($tableName, $tableAlias, $enableFields);
		}
		return $enableFields;
	}

	/**
	 *
	 * @param array
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection|tx_rnbase_util_db_IDatabase
	 */
	public function getDatabaseConnection($options = NULL) {
		if (is_array($options) && !empty($options['db'])) {
			$dbConfig = &$options['db'];
			if (is_string($dbConfig)) {
				$db = $this->getDatabase($dbConfig);
			}
			elseif (is_object($dbConfig)) {
				$db = $dbConfig;
			}
			if (!$db instanceof tx_rnbase_util_db_IDatabase) {
				tx_rnbase::load('tx_rnbase_util_Logger');
				tx_rnbase_util_Logger::warn(
					'The db "' . get_class($db) . '" has to implement' .
						' the tx_rnbase_util_db_IDatabase interface',
					'rn_base'
				);
				$db = NULL;
			}
		}
		return is_object($db) ? $db : $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Make a SQL INSERT Statement
	 *
	 * @param string $tablename
	 * @param array $values
	 * @param int|array $debug
	 * @return int UID of created record
	 */
	public function doInsert($tablename, $values, $arr=array()) {
		// fallback, $arr war früher $debug
		if (!is_array($arr)) { $arr = array('debug' => $arr); }
		$debug = intval($arr['debug']) > 0;
		$database = $this->getDatabaseConnection($arr);

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_insert_pre',
			array(
				'tablename' => $tablename,
				'values' => &$values,
				'options' => $arr,
			)
		);

		if($debug) {
			$time = microtime(TRUE);
			$mem = memory_get_usage();
			$sqlQuery = $database->INSERTquery($tablename, $values);
		}

		$storeLastBuiltQuery = $database->store_lastBuiltQuery;
		$database->store_lastBuiltQuery = TRUE;
		$this->watchOutDB(
			$database->exec_INSERTquery(
				$tablename,
				$values
			), $database
		);
		$database->store_lastBuiltQuery = $storeLastBuiltQuery;

		if($debug) {
			tx_rnbase_util_Debug::debug(array(
				'SQL '=>$sqlQuery,
				'Time '=>(microtime(TRUE) - $time),
				'Memory consumed '=>(memory_get_usage()-$mem),
			), 'SQL statistics');
		}

		$insertId = $database->sql_insert_id();

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_insert_post',
			array(
				'tablename' => $tablename,
				'uid' => $insertId,
				'values' => $values,
				'options' => $arr,
			)
		);

		return $insertId;
	}
	/**
	 * Make a plain SQL Query.
	 * Notice: The db resource is not closed by this method. The caller is in charge to do this!
	 *
	 * @param string $sqlQuery
	 * @param int $debug
	 * @return result pointer for SELECT, EXPLAIN, SHOW, DESCRIBE or boolean
	 */
	public function doQuery($sqlQuery, array $options=array()) {
		$debug = array_key_exists('debug', $options) ? intval($options['debug']) > 0 : FALSE;
		$database = $this->getDatabaseConnection($options);
		if($debug) {
			$time = microtime(TRUE);
			$mem = memory_get_usage();
		}

		$storeLastBuiltQuery = $database->store_lastBuiltQuery;
		$database->store_lastBuiltQuery = TRUE;
		$res = $this->watchOutDB(
			$database->sql_query($sqlQuery)
		);
		$database->store_lastBuiltQuery = $storeLastBuiltQuery;

		if($debug) {
			tx_rnbase_util_Debug::debug(array(
				'SQL '=>$sqlQuery,
				'Time '=>(microtime(TRUE) - $time),
				'Memory consumed '=>(memory_get_usage()-$mem),
			), 'SQL statistics');
		}
		return $res;
	}
	/**
	 * Make a database UPDATE.
	 *
	 * @param string $tablename
	 * @param string $where
	 * @param array $values
	 * @param array $arr
	 * @param mixed $noQuoteFields Array or commaseparated string with fieldnames
	 * @return int number of rows affected
	 */
	public function doUpdate($tablename, $where, $values, $arr=array(), $noQuoteFields = FALSE) {
		// fallback, $arr war früher $debug
		if (!is_array($arr)) { $arr = array('debug' => $arr); }
		$debug = intval($arr['debug']) > 0;
		$database = $this->getDatabaseConnection($arr);

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_update_pre',
			array(
				'tablename' => $tablename,
				'where' => $where,
				'values' => &$values,
				'options' => $arr,
				'noQuoteFields' => $noQuoteFields,
			)
		);

		if($debug) {
			$sql = $database->UPDATEquery($tablename, $where, $values, $noQuoteFields);
			tx_rnbase_util_Debug::debug($sql, 'SQL');
			tx_rnbase_util_Debug::debug(array($tablename, $where, $values));
		}

		$storeLastBuiltQuery = $database->store_lastBuiltQuery;
		$database->store_lastBuiltQuery = TRUE;
		$this->watchOutDB(
			$database->exec_UPDATEquery(
				$tablename,
				$where,
				$values,
				$noQuoteFields
			), $database
		);
		$database->store_lastBuiltQuery = $storeLastBuiltQuery;

		$affectedRows = $database->sql_affected_rows();

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_update_post',
			array(
				'tablename' => $tablename,
				'where' => $where,
				'values' => $values,
				'affectedRows' => $affectedRows,
				'options' => $arr,
				'noQuoteFields' => $noQuoteFields,
			)
		);

		return $affectedRows;
	}
	/**
	 * Make a database DELETE
	 *
	 * @param string $tablename
	 * @param string $where
	 * @param array $arr
	 * @return int number of rows affected
	 */
	public function doDelete($tablename, $where, $arr=array()) {
		// fallback, $arr war früher $debug
		if (!is_array($arr)) { $arr = array('debug' => $arr); }
		$debug = intval($arr['debug']) > 0;
		$database = $this->getDatabaseConnection($arr);

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_delete_pre',
			array(
				'tablename' => $tablename,
				'where' => $where,
				'options' => $arr,
			)
		);

		if($debug) {
			$sql = $database->DELETEquery($tablename, $where);
			tx_rnbase_util_Debug::debug($sql, 'SQL');
			tx_rnbase_util_Debug::debug(array($tablename, $where));
		}

		$storeLastBuiltQuery = $database->store_lastBuiltQuery;
		$database->store_lastBuiltQuery = TRUE;
		$this->watchOutDB(
			$database->exec_DELETEquery(
				$tablename,
				$where
			), $database
		);
		$database->store_lastBuiltQuery = $storeLastBuiltQuery;

		$affectedRows = $database->sql_affected_rows();

		tx_rnbase_util_Misc::callHook(
			'rn_base',
			'util_db_do_delete_post',
			array(
				'tablename' => $tablename,
				'where' => $where,
				'affectedRows' => $affectedRows,
				'options' => $arr,
			)
		);

		return $affectedRows;
	}

	/**
	 * Escaping and quoting values for SQL statements.
	 *
	 * @param string $str Input string
	 * @param string $table Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
	 * @param boolean $allowNull Whether to allow NULL values
	 * @return string Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 */
	public function fullQuoteStr($str, $table, $allowNull = FALSE) {
		return $GLOBALS['TYPO3_DB']->fullQuoteStr($str, $table, $allowNull);
	}

	/**
	 * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
	 *
	 * @param array $arr Array with values (either associative or non-associative array)
	 * @param string $table Table name for which to quote
	 * @param boolean|array $noQuote List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
	 * @param boolean $allowNull Whether to allow NULL values
	 * @return array The input array with the values quoted
	 */
	public function fullQuoteArray($arr, $table, $noQuote = FALSE, $allowNull = FALSE) {
		return $GLOBALS['TYPO3_DB']->fullQuoteArray($arr, $table, $noQuote, $allowNull);
	}

	/**
	 * Substitution for PHP function "addslashes()"
	 * Use this function instead of the PHP addslashes() function when you build queries - this will prepare your code for DBAL.
	 * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible. Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
	 *
	 * @param string $str Input string
	 * @param string $table Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
	 * @return string Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 */
	public function quoteStr($str, $table) {
		return $GLOBALS['TYPO3_DB']->quoteStr($str, $table);
	}

	/**
	 * Returns an array with column names of a TCA defined table.
	 *
	 * @param string $tcaTableName
	 * @param string $prefix if set, each columnname is preceded by this alias
	 * @return array
	 */
	public function getColumnNames($tcaTableName, $prefix = '') {
		$cols = $this->getTCAColumns($tcaTableName);
		if(is_array($cols)) {
			$cols = array_keys($cols);
			if(strlen(trim($prefix)))
				array_walk($cols, 'tx_rnbase_util_DB_prependAlias', $prefix);
		}
		else $cols = array();
		return $cols;
	}

	/**
	 * Liefert die TCA-Definition der in der Tabelle definierten Spalten
	 *
	 * @param string $tcaTableName
	 * @return array or 0
	 */
	public function getTCAColumns($tcaTableName) {
		global $TCA;
		tx_rnbase_util_TCA::loadTCA($tcaTableName);
		return isset($TCA[$tcaTableName]) ? $TCA[$tcaTableName]['columns'] : 0;
	}
	/**
	 * Liefert eine initialisierte TCEmain
	 */
	public function &getTCEmain($data = 0, $cmd = 0) {
		$tce;

		if(!$tce || $data || $cmd) {
			// Die TCEmain laden
			tx_rnbase::load('tx_rnbase_util_Typo3Classes');
			$tce = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getDataHandlerClass());
			$tce->stripslashes_values = 0;
			// Wenn wir ein data-Array bekommen verwenden wir das
			$tce->start($data ? $data : array(), $cmd ? $cmd : array());

			// set default TCA values specific for the user
			$TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
			if (is_array($TCAdefaultOverride)) {
				$tce->setDefaultsFromUserTS($TCAdefaultOverride);
			}
		}
		return $tce;
	}

	/**
	 * Get record with uid from table.
	 *
	 * @param string $tableName
	 * @param int $uid
	 */
	public function getRecord($tableName, $uid, $options = array()) {
		if (!is_array($options)) $options = array();
		$options['where'] = 'uid='.intval($uid);
		if(!is_array($GLOBALS['TCA']) || !array_key_exists($tableName, $GLOBALS['TCA']))
			$options['enablefieldsoff'] = 1;
		$result = $this->doSelect('*', $tableName, $options);
		return count($result) > 0 ? $result[0] : array();
	}

	/**
	 * Same method as tx_rnbase_util_Typo3Classes::getTypoScriptFrontendControllerClass()::pi_getPidList()
	 * If you  need this functionality use tx_rnbase_util_Misc::getPidList()
	 * @deprecated use tx_rnbase_util_Misc::getPidList!
	 */
	function _getPidList($pid_list, $recursive=0)  {
		return tx_rnbase_util_Misc::getPidList($pid_list, $recursive);
	}

	/**
	 * Check whether the given resource is a valid sql result. Breaks with mayday if not!
	 * This method is taken from the great ameos_formidable extension.
	 *
	 * @param mixed $res
	 * @return boolean|\mysqli_result|object MySQLi result object / DBAL object
	 */
	public function watchOutDB($res, $database = NULL) {
		if (!is_object($database)) {
			$database = $GLOBALS['TYPO3_DB'];
		}

		if(!$this->testResource($res) && $database->sql_error()) {
			$msg = 'SQL QUERY IS NOT VALID';
			$msg .= '<br/>';
			$msg .= '<b>' . $database->sql_error() . '</b>';
			$msg .= '<br />';
			$msg .= $database->debug_lastBuiltQuery;
			// We need to pass the extKey, otherwise no devlog was written.
			tx_rnbase_util_Misc::mayday(nl2br($msg), 'rn_base');
		}

		return $res;
	}

	/**
	 * Generates a search where clause based on the input search words (AND operation - all search words must be found in record.)
	 * Example: The $sw is "content management, system" (from an input form) and the $searchFieldList is "bodytext,header" then the
	 * output will be ' (bodytext LIKE "%content%" OR header LIKE "%content%") AND (bodytext LIKE "%management%" OR header LIKE "%management%") AND (bodytext LIKE "%system%" OR header LIKE "%system%")'
	 *
	 * METHOD FROM tslib_content
	 *
	 * @param string $sw		The search words. These will be separated by space and comma.
	 * @param string $searchFieldList		The fields to search in
	 * @param string $operator  'LIKE' oder 'FIND_IN_SET'
	 * @param string $searchTable	The table name you search in (recommended for DBAL compliance. Will be prepended field names as well)
	 * @return	string		The WHERE clause.
	 */
	public function searchWhere($sw, $searchFieldList, $operator='LIKE')	{
		$where = '';
		if ($sw !== '')	{
			$searchFields = explode(',', $searchFieldList);
			$kw = preg_split('/[ ,]/', $sw);
			if($operator == 'LIKE')
				$where = $this->_getSearchLike($kw, $searchFields);
			elseif($operator == 'OP_LIKE_CONST') {
				$kw = array($sw);
				$where = $this->_getSearchLike($kw, $searchFields);
			}
			elseif($operator == 'FIND_IN_SET_OR')
				$where = $this->_getSearchSetOr($kw, $searchFields);
			else
				$where = $this->_getSearchOr($kw, $searchFields, $operator);

		}
		return $where;
	}
	private function _getSearchOr($kw, $searchFields, $operator) {
		$where = '';
		$where_p = array();
		while(list(, $val)=each($kw))	{
			$val = trim($val);
			if(!strlen($val)) continue;
			reset($searchFields);
			while(list(, $field)=each($searchFields))	{
	  		list($tableAlias, $col) = explode('.', $field); // Split alias and column
				$wherePart = $this->setSingleWhereField($tableAlias, $operator, $col, $val);
				if (trim($wherePart) !== '') {
					$where_p[] = $wherePart;
				}
			}
		}
		if (count($where_p))	{
			$where.=' ('.implode('OR ', $where_p).')';
		}
		return $where;
	}
	/**
	 *
	 * @param array $kw
	 * @param array $searchFields
	 */
	private function _getSearchSetOr($kw, $searchFields) {
		global $TYPO3_DB;
		$searchTable = '';
		// Aus den searchFields muss eine Tabelle geholt werden (Erstmal nur DBAL)
		if(tx_rnbase_util_TYPO3::isExtLoaded('dbal') && is_array($searchFields) && !empty($searchFields)) {
			$col = $searchFields[0];
			list($searchTable, $col) = explode('.', $col);
		}

		// Hier werden alle Felder und Werte mit OR verbunden
		// (FIND_IN_SET(1, match.player)) AND (FIND_IN_SET(4, match.player))
		// (FIND_IN_SET(1, match.player) OR FIND_IN_SET(4, match.player))
		$where = '';
		$where_p = array();
		reset($kw);
		while(list(, $val)=each($kw))	{
			$val = trim($val);
			if(!strlen($val)) continue;
			$val = $TYPO3_DB->escapeStrForLike($TYPO3_DB->quoteStr($val, $searchTable), $searchTable);
			reset($searchFields);
			while(list(, $field)=each($searchFields))	{
				$where_p[] = 'FIND_IN_SET(\''.$val.'\', '.$field.')';
			}
		}
		if (count($where_p))	{
			$where.=' ('.implode(' OR ', $where_p).')';
		}
		return $where;
	}
	/**
	 * Create a where condition for string search in different database tables and columns.
	 * @param array $kw
	 * @param array $searchFields
	 */
	private function _getSearchLike($kw, $searchFields) {
		global $TYPO3_DB;
		$searchTable = ''; // Für TYPO3 nicht relevant
		if(tx_rnbase_util_TYPO3::isExtLoaded('dbal')) {
			// Bei dbal darf die Tabelle nicht leer sein. Wir setzen die erste Tabelle in den searchfields
			$col = $searchFields[0];
			list($searchTable, $col) = explode('.', $col);
		}
		$wheres = array();
		while(list(, $val)=each($kw))	{
			$val = trim($val);
			$where_p = array();
			if (strlen($val)>=2)	{
				$val = $TYPO3_DB->escapeStrForLike($TYPO3_DB->quoteStr($val, $searchTable), $searchTable);
				reset($searchFields);
				while(list(, $field)=each($searchFields))	{
					$where_p[] = $field.' LIKE \'%'.$val.'%\'';
				}
			}
			if (count($where_p))	{
				$wheres[] =' ('.implode(' OR ', $where_p).')';
			}
		}
		$where = count($wheres) ? implode(' AND ', $wheres) : '';
		return $where;
 	}
	/**
	 * Build a single where clause. This is a compare of a column to a value with a given operator.
	 * Based on the operator the string is hopefully correctly build. It is up to the client to
	 * connect these single clauses with boolean operator for a complete where clause.
	 *
	 * @param string $tableAlias database tablename or alias
	 * @param string $operator operator constant
	 * @param string $col name of column
	 * @param string $value value to compare to
	 */
	function setSingleWhereField($tableAlias, $operator, $col, $value) {
		$where = '';
		switch ($operator) {
			case OP_NOTIN_INT:
			case OP_IN_INT:
				$value = implode(',', tx_rnbase_util_Strings::intExplode(',', $value));
				$where .= $tableAlias.'.' . strtolower($col) . ' '.$operator.' (' . $value . ')';
				break;
			case OP_NOTIN:
			case OP_IN:
				$values = tx_rnbase_util_Strings::trimExplode(',', $value);
				for($i=0, $cnt=count($values); $i < $cnt; $i++)
					$values[$i] = $GLOBALS['TYPO3_DB']->fullQuoteStr($values[$i], $tableAlias);
				$value = implode(',', $values);
				$where .= $tableAlias.'.' . strtolower($col) . ' '. ($operator == OP_IN ? 'IN' : 'NOT IN') .' (' . $value . ')';
				break;
			case OP_NOTIN_SQL:
			case OP_IN_SQL:
				$where .= $tableAlias.'.' . strtolower($col) . ' '. ($operator == OP_IN_SQL ? 'IN' : 'NOT IN') .' (' . $value . ')';
				break;
			case OP_INSET_INT:
				// Values splitten und einzelne Abfragen mit OR verbinden
				$where = $this->searchWhere($value, $tableAlias.'.' . strtolower($col), 'FIND_IN_SET_OR');
				break;
			case OP_EQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
			  break;
			case OP_NOTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
			  break;
			case OP_LT:
				$where .= $tableAlias.'.' . strtolower($col) . ' < ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
				break;
			case OP_LTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' <= ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
				break;
			case OP_GT:
				$where .= $tableAlias.'.' . strtolower($col) . ' > ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
				break;
			case OP_GTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' >= ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
				break;
			case OP_EQ_INT:
			case OP_NOTEQ_INT:
			case OP_GT_INT:
			case OP_LT_INT:
			case OP_GTEQ_INT:
			case OP_LTEQ_INT:
				$where .= $tableAlias.'.' . strtolower($col) . ' '.$operator.' ' . intval($value);
				break;
			case OP_EQ_NOCASE:
				$where .= 'lower('.$tableAlias.'.' . strtolower($col) . ') = lower(' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ')';
				break;
			case OP_LIKE:
				// Stringvergleich mit LIKE
				$where .= $this->searchWhere($value, $tableAlias . '.' . strtolower($col));
				break;
			case OP_LIKE_CONST:
				$where .= $this->searchWhere($value, $tableAlias . '.' . strtolower($col), OP_LIKE_CONST);
				break;
			default:
				tx_rnbase_util_Misc::mayday('Unknown Operator for comparation defined: ' . $operator);
		}
		return $where . ' ';
	}

	/**
	 * Format a MySQL-DATE (ISO-Date) into mm-dd-YYYY.
	 *
	 * @param string $date Format: yyyy-mm-dd
	 * @return string Format mm-dd-YYYY or empty string, if $date is not valid
	 */
	function date_mysql2mdY($date) {
		if(strlen($date) < 2)
			return '';
		list($year, $month, $day) = explode('-', $date);
		return sprintf('%02d%02d%04d', $day, $month, $year);
	}

	/**
	 * Format a MySQL-DATE (ISO-Date) into dd-mm-YYYY.
	 * @param string $date Format: yyyy-mm-dd
	 * @return string Format dd-mm-yyyy or empty string, if $date is not valid
	 */
	function date_mysql2dmY($date) {
		if(strlen($date) < 2)
			return '';
		list($year, $month, $day) = explode('-', $date);
		return sprintf('%02d-%02d-%04d', $day, $month, $year);
	}

	/**
	 * Returns the database instance
	 * @param string $key database identifier defined in localconf.php. Always in lowercase!
	 * @return tx_rnbase_util_db_IDatabase
	 */
	public function getDatabase($key = 'typo3') {
		$key = strtolower($key);
		tx_rnbase::load('tx_rnbase_cache_Manager');
		$cache= tx_rnbase_cache_Manager::getCache('rnbase_databases');
		$db = $cache->get('db_'.$key);
		if(!$db) {
			if($key == 'typo3') {
				$db = tx_rnbase::makeInstance('tx_rnbase_util_db_TYPO3');
			}
			else {
				$dbCfg = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']['db'][$key];
				if(!is_array($dbCfg)) {
					tx_rnbase::load('tx_rnbase_util_db_Exception');
					throw new tx_rnbase_util_db_Exception('No config for database ' . $key . ' found!');
				}
				$db = tx_rnbase::makeInstance('tx_rnbase_util_db_MySQL', $dbCfg);
			}
			$cache->set($key, $db);
		}
		return $db;
	}

	/**
	 * Make a query to database. You will receive an array with result rows. All
	 * database resources are closed after each call.
	 * A Hidden and Delete-Clause for FE-Requests is added for requested table.
	 *
	 * @param $what requested columns
	 * @param $from either the name of on table or an array with index 0 the from clause
	 *              and index 1 the requested tablename
	 * @param $where
	 * @param $groupby
	 * @param $orderby
	 * @param $wrapperClass Name einer WrapperKlasse für jeden Datensatz
	 * @param $limit = '' Limits number of results
	 * @param $debug = 0 Set to 1 to debug sql-String
	 * @deprecated use tx_rnbase_util_DB::doSelect()
	 */
	function queryDB($what, $from, $where, $groupBy = '', $orderBy = '', $wrapperClass = 0, $limit = '', $debug=0){
		$tableName = $from;
		$fromClause = $from;
		if(is_array($from)){
			$tableName = $from[1];
			$fromClause = $from[0];
		}

		$limit = intval($limit) > 0 ? intval($limit) : '';

		// Zur Where-Clause noch die gültigen Felder hinzufügen
		$contentObjectRendererClass = tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();
		$where .= $contentObjectRendererClass::enableFields($tableName);

    if($debug) {
      $sql = $GLOBALS['TYPO3_DB']->SELECTquery($what, $fromClause, $where, $groupBy, $orderBy);
      tx_rnbase_util_Debug::debug($sql, 'SQL');
      tx_rnbase_util_Debug::debug(array($what, $from, $where));
    }

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      $what,
      $fromClause,
      $where,
      $groupBy,
      $orderBy,
      $limit
    );

    $wrapper = is_string($wrapperClass) ? tx_rnbase::makeInstanceClassName($wrapperClass) : 0;
    $rows = array();
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
      $rows[] = ($wrapper) ? new $wrapper($row) : $row;
    }
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    if($debug)
      tx_rnbase_util_Debug::debug(count($rows), 'Rows retrieved');
    return $rows;
  }

	/**
	 * @param array $options
	 * @param string $tableName
	 * @param string $tableAlias
	 * @return string
	 */
	public function handleEnableFieldsOptions(array $options, $tableName, $tableAlias) {
		$enableFields = '';

		if (!$options['enablefieldsoff']) {
			if(
				is_object($GLOBALS['BE_USER']) &&
				$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects']
			) {
				$options['enablefieldsbe'] = 1;
			}

			// Zur Where-Clause noch die gültigen Felder hinzufügen
			$sysPage = tx_rnbase_util_TYPO3::getSysPage();
			$mode = (TYPO3_MODE == 'BE') ? 1 : 0;
			$ignoreArr = array();
			if (intval($options['enablefieldsbe'])) {
				$mode = 1;
				// Im BE alle sonstigen Enable-Fields ignorieren
				$ignoreArr = array('starttime' => 1, 'endtime' => 1, 'fe_group' => 1);
			} elseif(intval($options['enablefieldsfe'])) {
				$mode = 0;
			}
			// Workspaces: Bei Tabellen mit Workspace-Support werden die EnableFields automatisch reduziert. Die Extension
			// Muss aus dem ResultSet ggf. Datensätze entfernen.
			$enableFields = $sysPage->enableFields($tableName, $mode, $ignoreArr);
			// Wir setzen zusätzlich pid >=0, damit Version-Records nicht erscheinen
			// allerdings nur, wenn die Tabelle versionierbar ist!
			if (!empty($GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'])) {
				$enableFields .= ' AND ' . $tableName . '.pid >=0';
			}
			// Replace tablename with alias
			if($tableAlias) {
				$enableFields = str_replace($tableName, $tableAlias, $enableFields);
			}
		}

		return $enableFields;
	}
}

function tx_rnbase_util_DB_prependAlias(&$item, $key, $alias) {
  $item = $alias . '.' . $item;
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/Classes/Database/Connection.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/Classes/Database/Connection.php']);
}
