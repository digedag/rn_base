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

/**
 * Contains utility functions for formatting
 */
class tx_rnbase_util_DB {
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
   * </pre>
   * @param $what requested columns
   * @param $from either the name of on table or an array with index 0 the from clause 
   *              and index 1 the requested tablename
   * @param $arr the options array
   * @param $debug = 0 Set to 1 to debug sql-String
   */
  function doSelect($what, $from, $arr, $debug=0){
    $tableName = $from;
    $fromClause = $from;
    if(is_array($from)){
      $tableName = $from[1];
      $fromClause = $from[0];
    }

    $where = is_string($arr['where']) ? $arr['where'] : '1';
    $groupBy = is_string($arr['groupby']) ? $arr['groupby'] : '';
    $orderBy = is_string($arr['orderby']) ? $arr['orderby'] : '';
    $limit = intval($arr['limit']) > 0 ? intval($arr['limit']) : '';
    $pidList = is_string($arr['pidlist']) ? $arr['pidlist'] : '';
    $recursive = intval($arr['recursive']) ? intval($arr['recursive']) : 0;

    $wrapper = is_string($arr['wrapperclass']) ? tx_div::makeInstanceClassName($arr['wrapperclass']) : 0;

    if(!$arr['enablefieldsoff']) {
    // Zur Where-Clause noch die gültigen Felder hinzufügen
      $where .= tslib_cObj::enableFields($tableName);
    }

    if(strlen($pidList) > 0)
      $where .= ' AND pid IN (' . tx_rnbase_util_DB::_getPidList($pidList,$recursive) . ')';

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
      t3lib_div::debug(count($rows),'Rows retrieved');
    return $rows;

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
  
}

function tx_rnbase_util_DB_prependAlias(&$item, $key, $alias) {
  $item = $alias . '.' . $item;
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_DB.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_DB.php']);
}

