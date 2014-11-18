<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Rene Nitzsche
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

/**
 * DB wrapper for TYPO3 database
 */
class tx_rnbase_util_db_TYPO3 {
	/**
	 * Creates a SELECT SQL-statement
	 *
	 * @param string List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @param string Table(s) from which to select. This is what comes right after "FROM ...". Required value.
	 * @param string additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param string Optional GROUP BY field(s), if none, supply blank string.
	 * @param string Optional ORDER BY field(s), if none, supply blank string.
	 * @param string Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return string SQL Query
	 */
	public function SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '') {
		return $GLOBALS['TYPO3_DB']->SELECTquery($what, $fromClause, $where, $groupBy, $orderBy, $limit);
	}

	/**
	 * Creates and executes a SELECT SQL-statement
	 *
	 * @param	string		List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @param	string		Table(s) from which to select. This is what comes right after "FROM ...". Required value.
	 * @param	string		additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = ''){
		return $GLOBALS['TYPO3_DB']->exec_SELECTquery($what, $fromClause, $where, $groupBy, $orderBy, $limit);
	}
	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 *
	 * @param string Table name
	 * @param array Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param string/array See t3lib_db::fullQuoteArray()
	 * @return string SQL query
	 */
	public function INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
		return $GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values, $no_quote_fields);
	}
	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See fullQuoteArray()
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	public function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
		return $GLOBALS['TYPO3_DB']->execINSERTquery($table, $fields_values, $no_quote_fields);
	}

	/**
	 * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See t3lib_db::fullQuoteArray()
	 * @return string sql query
	 */
	public function UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
		return $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
	}
	/**
	 * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See t3lib_db::fullQuoteArray()
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
		return $GLOBALS['TYPO3_DB']->execUPDATEquery($table, $where, $fields_values, $no_quote_fields);
	}

	/**
	 * Creates and executes a DELETE SQL-statement for $table where $where-clause
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @return string sql query
	 */
	public function DELETEquery($table, $where) {
		return $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);
	}

	/**
	 * Creates and executes a DELETE SQL-statement for $table where $where-clause
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	public function exec_DELETEquery($table, $where) {
		return $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
	}

	/**
	 * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
	 * mysql_fetch_assoc() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query) / DBAL object
	 * @return	array		Associative array of result row.
	 */
	public function sql_fetch_assoc($res) {
		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}
	/**
	 * Free result memory
	 * mysql_free_result() wrapper function
	 *
	 * @param	pointer		MySQL result pointer to free / DBAL object
	 * @return	boolean		Returns TRUE on success or FALSE on failure.
	 */
	public function sql_free_result($res) {
		return $GLOBALS['TYPO3_DB']->sql_free_result($res);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_db_TYPO3.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_db_TYPO3.php']);
}
