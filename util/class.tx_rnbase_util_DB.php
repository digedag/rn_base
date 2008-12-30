<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2008 Rene Nitzsche
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

/**
 * Contains utility functions for database access
 */
class tx_rnbase_util_DB {
	private static $sysPage = null;
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
		$where .= tslib_cObj::enableFields($tableName);

    if($debug) {
      $sql = $GLOBALS['TYPO3_DB']->SELECTquery($what,$fromClause,$where,$groupBy,$orderBy);
      t3lib_div::debug($sql, 'SQL');
      t3lib_div::debug(array($what,$from,$where));
    }

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      $what,
      $fromClause,
      $where,
      $groupBy,
      $orderBy,
      $limit
    );

    $wrapper = is_string($wrapperClass) ? tx_div::makeInstanceClassName($wrapperClass) : 0;
    $rows = array();
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
      $rows[] = ($wrapper) ? new $wrapper($row) : $row;
    }
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    if($debug)
      t3lib_div::debug(count($rows),'Rows retrieved');
    return $rows;
  }

	/**
	 * Generische Schnittstelle für Datenbankabfragen. Anstatt vieler Parameter wird hier ein
	 * Hash als Parameter verwendet, der mögliche Informationen aufnimmt.
	 * Es sind die folgenden Parameter zulässig:
	 * <pre>
	 * - 'where' - the Where-Clause
	 * - 'groupby' - the GroupBy-Clause
	 * - 'orderby' - the OrderBy-Clause
	 * - 'limit' - limits the number of result rows
	 * - 'wrapperclass' - A wrapper for each result rows
	 * - 'pidlist' - A list of page-IDs to search for records
	 * - 'recursive' - the recursive level to search for records in pages
	 * - 'enablefieldsoff' - deactivate enableFields check
	 * - 'enablefieldsbe' - force enableFields check for BE (this usually ignores hidden records)
	 * - 'enablefieldsfe' - force enableFields check for FE
	 * </pre>
	 * @param string $what requested columns
	 * @param string $from either the name of on table or an array with index 0 the from clause 
	 *              and index 1 the requested tablename
	 * @param array $arr the options array
	 * @param boolean $debug = 0 Set to 1 to debug sql-String
	 */
	function doSelect($what, $from, $arr, $debug=0){
		$debug = $debug ? $debug : intval($arr['debug']) > 0;
		if($debug)
			$time = microtime(true);
		$tableName = $from;
		$fromClause = $from;
		if(is_array($from)){
			$tableName = $from[1];
			$fromClause = $from[0];
		}

		$where = is_string($arr['where']) ? $arr['where'] : '1';
		$groupBy = is_string($arr['groupby']) ? $arr['groupby'] : '';
		$orderBy = is_string($arr['orderby']) ? $arr['orderby'] : '';
		$offset = intval($arr['offset']) > 0 ? intval($arr['offset']) : 0;
		$limit = intval($arr['limit']) > 0 ? intval($arr['limit']) : '';
		$pidList = is_string($arr['pidlist']) ? $arr['pidlist'] : '';
		$recursive = intval($arr['recursive']) ? intval($arr['recursive']) : 0;
		$i18n = is_string($arr['i18n']) > 0 ? $arr['i18n'] : '';

		// offset und limit kombinieren
		if($limit) { // bei gesetztem limit ist offset optional
			$limit = ($offset > 0) ? $offset . ',' . $limit : $limit;
		}
		elseif($offset) { // Bei gesetztem Offset ist limit Pflicht (default 1000)
			$limit = ($limit > 0) ? $offset . ',' . $limit : $offset . ',1000';
		}
		else $limit = '';

		$wrapper = is_string($arr['wrapperclass']) ? tx_div::makeInstanceClassName($arr['wrapperclass']) : 0;

		if(!$arr['enablefieldsoff']) {
			// Zur Where-Clause noch die gültigen Felder hinzufügen
			if (!is_object(self::$sysPage)) {
				require_once(PATH_t3lib.'class.t3lib_page.php');
				self::$sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
				self::$sysPage->init($this->showHiddenPage);
			}
			$mode = (TYPO3_MODE == 'BE') ? 1 : 0;
			if(intval($arr['enablefieldsbe']))
				$mode = 1;
			elseif(intval($arr['enablefieldsfe']))
				$mode = 0;
			$where .= self::$sysPage->enableFields($tableName, $mode);
//			$where .= tslib_cObj::enableFields($tableName);
		}

		if(strlen($i18n) > 0) {
			$i18n = implode(',', t3lib_div::intExplode(',', $i18n));
			$where .= ' AND '.$tableName.'.sys_language_uid IN (' . $i18n . ')';
		}

		if(strlen($pidList) > 0)
			$where .= ' AND '.$tableName.'.pid IN (' . tx_rnbase_util_DB::_getPidList($pidList,$recursive) . ')';

		if($debug) {
			$sql = $GLOBALS['TYPO3_DB']->SELECTquery($what,$fromClause,$where,$groupBy,$orderBy,$limit);
			t3lib_div::debug($sql, 'SQL');
			t3lib_div::debug(array($what,$from,$where));
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$what,
			$fromClause,
			$where,
			$groupBy,
			$orderBy,
			$limit
		);

		$rows = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$rows[] = ($wrapper) ? new $wrapper($row) : $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if($debug)
			t3lib_div::debug(count($rows),'Rows retrieved. Time: ' . (microtime(true) - $time) . 's');
		return $rows;
	}

	/**
	 * Make a SQL INSERT Statement
	 *
	 * @param string $tablename
	 * @param array $values
	 * @param int $debug
	 * @return int UID of created record
	 */
	function doInsert($tablename, $values, $debug=0) {
		if($debug) {
			$sql = $GLOBALS['TYPO3_DB']->INSERTquery($tablename,$values);
			t3lib_div::debug($sql, 'SQL');
			t3lib_div::debug(array($tablename,$values));
		}
		self::watchOutDB(
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$tablename,
				$values
			)
		);
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	/**
	 * Make a database UPDATE.
	 *
	 * @param string $tablename
	 * @param string $where
	 * @param array $values
	 * @param int $debug 0/1
	 * @param mixed $noQuoteFields Array or commaseparated string with fieldnames
	 * @return int number of rows affected
	 */
	function doUpdate($tablename, $where, $values, $debug=0, $noQuoteFields = false) {
		if($debug) {
			$sql = $GLOBALS['TYPO3_DB']->UPDATEquery($tablename,$where,$values, $noQuoteFields);
			t3lib_div::debug($sql, 'SQL');
			t3lib_div::debug(array($tablename,$where,$values));
		}
		self::watchOutDB(
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$tablename,
				$where,
				$values, 
				$noQuoteFields
			)
		);
		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}
	/**
	 * Make a database DELETE
	 *
	 * @param string $tablename
	 * @param string $where
	 * @param boolean $debug
	 * @return int number of rows affected
	 */
	function doDelete($tablename, $where, $debug=0) {
		if($debug) {
			$sql = $GLOBALS['TYPO3_DB']->DELETEquery($tablename,$where);
			t3lib_div::debug($sql, 'SQL');
			t3lib_div::debug(array($tablename,$where));
		}
		self::watchOutDB(
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				$tablename,
				$where
			)
		);
		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}

	/**
	 * Returns an array with column names of a TCA defined table.
	 *
	 * @param string $tcaTableName
	 * @param string $prefix if set, each columnname is preceded by this alias
	 * @return array
	 */
	function getColumnNames($tcaTableName, $prefix = '') {
		$cols = self::getTCAColumns($tcaTableName);
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
	function getTCAColumns($tcaTableName) {
		global $TCA;
		t3lib_div::loadTCA($tcaTableName);
		return isset($TCA[$tcaTableName]) ? $TCA[$tcaTableName]['columns'] : 0;
	}
	/**
	 * Liefert eine initialisierte TCEmain
	 */
	static function &getTCEmain($data = 0, $cmd = 0) {
		static $tce;

		if(!$tce || $data || $cmd) {
			// Die TCEmain laden
			require_once(PATH_t3lib.'class.t3lib_tcemain.php');
			$tce = t3lib_div::makeInstance('t3lib_tcemain');
			$tce->stripslashes_values = 0;
			// Wenn wir ein data-Array bekommen verwenden wir das
			$tce->start($data ? $data : Array(),$cmd ? $cmd : Array());

			// set default TCA values specific for the user
			$TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
			if (is_array($TCAdefaultOverride)) {
				$tce->setDefaultsFromUserTS($TCAdefaultOverride);
			}
		}
		return $tce;
	}

  /**
   * Same method as tslib_pibase::pi_getPidList()
   */
  function _getPidList($pid_list,$recursive=0)  {
    if (!strcmp($pid_list,''))      $pid_list = $GLOBALS['TSFE']->id;
    $recursive = t3lib_div::intInRange($recursive,0);

    $pid_list_arr = array_unique(t3lib_div::trimExplode(',',$pid_list,1));
    $pid_list = array();

    foreach($pid_list_arr as $val)  {
      $val = t3lib_div::intInRange($val,0);
      if ($val)       {
        $_list = tslib_cObj::getTreeList(-1*$val, $recursive);
        if ($_list)  $pid_list[] = $_list;
      }
    }

    return implode(',', $pid_list);
  }
  /**
   * Check whether the given resource is a valid sql result. Breaks with mayday if not!
   * This method is taken from the great ameos_formidable extension.
   *
   * @param resource $rRes
   * @return resource
   */
	function watchOutDB(&$rRes) {

		if(!is_resource($rRes) && $GLOBALS['TYPO3_DB']->sql_error()) {

			$sMsg = 'SQL QUERY IS NOT VALID';
			$sMsg .= '<br/>';
			$sMsg .= '<b>' . $GLOBALS['TYPO3_DB']->sql_error() . '</b>';
			$sMsg .= '<br />';
			$sMsg .= $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;

			tx_div::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::mayday($sMsg);
		}

		return $rRes;
	}

	/**
	 * Generates a search where clause based on the input search words (AND operation - all search words must be found in record.)
	 * Example: The $sw is "content management, system" (from an input form) and the $searchFieldList is "bodytext,header" then the output will be ' (bodytext LIKE "%content%" OR header LIKE "%content%") AND (bodytext LIKE "%management%" OR header LIKE "%management%") AND (bodytext LIKE "%system%" OR header LIKE "%system%")'
	 *
	 * METHOD FROM tslib_content
	 * 
	 * @param	string $sw		The search words. These will be separated by space and comma.
	 * @param	string $searchFieldList		The fields to search in
	 * @package string $operator  'LIKE' oder 'FIND_IN_SET'
	 * @param	string $searchTable	The table name you search in (recommended for DBAL compliance. Will be prepended field names as well)
	 * @return	string		The WHERE clause.
	 */
	static function searchWhere($sw,$searchFieldList,$operator='LIKE')	{
		$where = '';
		if ($sw)	{
			$searchFields = explode(',',$searchFieldList);
			$kw = split('[ ,]',$sw);
			if($operator == 'LIKE')
				$where = self::_getSearchLike($kw, $searchFields);
			elseif($operator == 'FIND_IN_SET_OR')
				$where = self::_getSearchSetOr($kw, $searchFields);
			else
				$where = self::_getSearchOr($kw, $searchFields, $operator);
			
		}
		return $where;
	}
	static function _getSearchOr($kw, $searchFields, $operator) {
		$where = '';
		$where_p = array();
		while(list(,$val)=each($kw))	{
			reset($searchFields);
			while(list(,$field)=each($searchFields))	{
	  		list($tableAlias, $col) = explode('.', $field); // Split alias and column
				$where_p[] = self::setSingleWhereField($tableAlias, $operator, $col, $val);
//				$where_p[] = $field.' ' . $operator . ' \'%'.$val.'%\'';
//				$where_p[] = 'FIND_IN_SET('.$val.', '.$prefixTableName.$field.')';
			}
		}
		if (count($where_p))	{
			$where.=' AND ('.implode(' OR ',$where_p).')';
		}
		return $where;
	}
	static function _getSearchSetOr($kw, $searchFields) {
		// Hier werden alle Felder und Werte mit OR verbunden
		// (FIND_IN_SET(1, match.player)) AND (FIND_IN_SET(4, match.player))
		// (FIND_IN_SET(1, match.player) OR FIND_IN_SET(4, match.player))
		$where = '';
		$where_p = array();
		while(list(,$val)=each($kw))	{
			$val = intval(trim($val));
			reset($searchFields);
			while(list(,$field)=each($searchFields))	{
				$where_p[] = 'FIND_IN_SET('.$val.', '.$field.')';
			}
		}
		if (count($where_p))	{
			$where.=' AND ('.implode(' OR ',$where_p).')';
		}
		return $where;
	}
	static function _getSearchLike($kw, $searchFields) {
		global $TYPO3_DB;
		$searchTable = ''; // TODO Check if possible to delete
		$where = '';
		while(list(,$val)=each($kw))	{
			$val = trim($val);
			$where_p = array();
			if (strlen($val)>=2)	{
				$val = $TYPO3_DB->escapeStrForLike($TYPO3_DB->quoteStr($val,$searchTable),$searchTable);
				reset($searchFields);
				while(list(,$field)=each($searchFields))	{
					$where_p[] = $field.' LIKE \'%'.$val.'%\'';
				}
			}
			if (count($where_p))	{
				$where.=' AND ('.implode(' OR ',$where_p).')';
			}
		}
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
	static function setSingleWhereField($tableAlias, $operator, $col, $value) {
		$where = '';
		switch ($operator) {
			case OP_NOTIN_INT:
			case OP_IN_INT:
				$value = implode(',', t3lib_div::intExplode(',', $value));
				$where .= $tableAlias.'.' . strtolower($col) . ' '.$operator.' (' . $value . ')';
				break;
			case OP_IN:
				$value = implode('\',\'', t3lib_div::trimExplode(',', $value));
				$where .= $tableAlias.'.' . strtolower($col) . ' IN (\'' . $value . '\')';
				break;
			case OP_IN_SQL:
				$where .= $tableAlias.'.' . strtolower($col) . ' IN (' . $value . ')';
				break;
			case OP_INSET_INT:
				$where .= ' FIND_IN_SET(' . $value . ', '.$tableAlias.'.' . strtolower($col).')';
				break;
			case OP_EQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
			  break;
			case OP_NOTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' != ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
			  break;
			case OP_LT:
				$where .= $tableAlias.'.' . strtolower($col) . ' < ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
				break;
			case OP_LTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' <= ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
				break;
			case OP_GT:
				$where .= $tableAlias.'.' . strtolower($col) . ' > ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
				break;
			case OP_GTEQ:
				$where .= $tableAlias.'.' . strtolower($col) . ' >= ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias) . ' ';
				break;
			case OP_EQ_INT:
			case OP_NOTEQ_INT:
			case OP_GT_INT:
			case OP_LT_INT:
			case OP_GTEQ_INT:
			case OP_LTEQ_INT:
				$where .= $tableAlias.'.' . strtolower($col) . ' '.$operator.' ' . intval($value) . ' ';
				break;
			case OP_EQ_NOCASE:
				$where .= 'lower('.$tableAlias.'.' . strtolower($col) . ') = lower(\'' . $value . '\') ';
				break;
			case OP_LIKE:
				// Stringvergleich mit LIKE
				$where .= self::searchWhere($value, strtolower($tableAlias.'.'.$col));
				break;
			default:
				tx_div::load('tx_rnbase_util_misc');
				tx_rnbase_util_misc::mayday('Unknown Operator for comparation defined: ' . $operator);
		}
		return $where;
	}
  
	/**
	 * Format a MySQL-DATE (ISO-Date) into mm-dd-YYYY.
	 *
	 * @param string $date Format: yyyy-mm-dd
	 * @return string Format mm-dd-YYYY or empty string, if $date is not valid
	 */
  static function date_mysql2mdY($date) {
		if(strlen($date) < 2)
			return '';
		list($year, $month, $day) = explode('-', $date);
		return sprintf("%02d%02d%04d", $day, $month, $year);
  }

	/**
	 * Format a MySQL-DATE (ISO-Date) into dd-mm-YYYY.
	 * @param string $date Format: yyyy-mm-dd
	 * @return string Format dd-mm-yyyy or empty string, if $date is not valid
	 */
	static function date_mysql2dmY($date) {
		if(strlen($date) < 2)
			return '';
		list($year, $month, $day) = explode('-', $date);
		return sprintf("%02d-%02d-%04d", $day, $month, $year);
	}
}

function tx_rnbase_util_DB_prependAlias(&$item, $key, $alias) {
  $item = $alias . '.' . $item;
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_DB.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_DB.php']);
}

