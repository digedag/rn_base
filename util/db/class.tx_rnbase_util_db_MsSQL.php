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

tx_rnbase::load('tx_rnbase_util_db_Exception');
tx_rnbase::load('tx_rnbase_util_db_IDatabase');

/**
 * DB wrapper for external microsoft sql databases
 *
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_rnbase_util_db_MsSQL implements tx_rnbase_util_db_IDatabase {

	/**
	 *
	 * @var resource
	 */
	private $db = NULL;

	/**
	 *
	 * @var int
	 */
	private $lastInsertId = 0;

	/**
	 * @param array $credentials
	 * @throws tx_rnbase_util_db_Exception
	 */
	public function __construct($credentials) {
		if(!is_array($credentials)) {
			throw new tx_rnbase_util_db_Exception(
					'No credentials given for database!');
		}

		$this->db = $this->connectDB($credentials);
	}

	/**
	 *
	 * @param string $methodName
	 * @param array $args
	 * @throws Exception
	 * @deprecated never use undefined methods!
	 */
	public function __call($methodName, $args) {
		throw new Exception('Sorry, the class "'.get_class($this->db).'" does not support the method "'.$methodName.'".');
		return call_user_func_array(array($this->db, $methodName), $args);
	}

	/**
	 *
	 * @param unknown_type $data
	 * @return string|unknown
	 */
	protected function mssql_real_escape_string($data) {
		if (!isset($data) || $data === '') {
			return '';
		}
		if (is_numeric($data)) {
			return $data;
		}

// 		$unpacked = unpack('H*hex', $data);
// 		return '0x' . $unpacked['hex'];

		$nonDdisplayables = array(
				'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
				'/%1[0-9a-f]/',             // url encoded 16-31
				'/[\x00-\x08]/',            // 00-08
				'/\x0b/',                   // 11
				'/\x0c/',                   // 12
				'/[\x0e-\x1f]/'             // 14-31
		);
		foreach ($nonDdisplayables as $regex){
			$data = preg_replace( $regex, '', $data);
		}
		$data = str_replace("'", "''", $data);
		return $data;
	}


	/**
	 * Escaping and quoting values for MS SQL statements.
	 * Usage count/core: 100
	 *
	 * @param	string		Input string
	 * @param	string		Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @see quoteStr()
	 */
	public function fullQuoteStr($str, $table) {
		return '\'' . $this->mssql_real_escape_string($str) . '\'';
	}

	/**
	 * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
	 *
	 * @param	array		Array with values (either associative or non-associative array)
	 * @param	string		Table name for which to quote
	 * @param	string/array		List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
	 * @return	array		The input array with the values quoted
	 */
	public function fullQuoteArray($arr, $table, $noQuote = FALSE) {
		if (is_string($noQuote)) {
			$noQuote = explode(',', $noQuote);
			// sanity check
		} elseif (!is_array($noQuote)) {
			$noQuote = FALSE;
		}

		foreach ($arr as $k => $v) {
			if ($noQuote === FALSE || !in_array($k, $noQuote)) {
				$arr[$k] = $this->fullQuoteStr($v, $table);
			}
		}
		return $arr;
	}

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
		return $GLOBALS['TYPO3_DB']->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
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
	 * @return	pointer		MsSQL result pointer / DBAL object
	 */
	public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = ''){
		$query = $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
		$res = $this->sql_query($query, $this->db);
		return $res;
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
		// Table and fieldnames should be "SQL-injection-safe" when supplied to this
		// function (contrary to values in the arrays which may be insecure).
		if (is_array($fields_values) && !empty($fields_values)) {

			// quote and escape values
			$fields_values = $this->fullQuoteArray($fields_values, $table, $no_quote_fields);

			// Build query:
			$query = 'INSERT INTO ' . $table .
				' (' . implode(',', array_keys($fields_values)) . ') VALUES ' .
				'(' . implode(',', $fields_values) . ')';

			return $query;
		}
// 		return $GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values, $no_quote_fields);
	}
	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 *
	 * @see http://www.php.net/manual/en/function.mssql-query.php#25274 For lastInsertId.
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See fullQuoteArray()
	 * @return	pointer		MsSQL result pointer / DBAL object
	 */
	public function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
		$query = $this->INSERTquery($table, $fields_values, $no_quote_fields);

		// Wir müssen alle doublequotes (") durch "" escapen.
		// Da wir die komplette Query über einen exec in "QUERY" schreiben,
		// treten hier SQL-Fehler auf, wenn " im Datensatz vorkommt.
		$query = str_replace('"', '""', $query);

		$query = 'exec("'.$query.';'.PHP_EOL.'SELECT @@IDENTITY as uid");';
		$res = $this->sql_query($query);
		list($this->lastInsertId) = mssql_fetch_row($res);
		return $res;
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
			// Table and fieldnames should be "SQL-injection-safe" when supplied to this
			// function (contrary to values in the arrays which may be insecure).
		if (is_string($where)) {
			$fields = array();
			if (is_array($fields_values) && count($fields_values)) {

					// quote and escape values
				$nArr = $this->fullQuoteArray($fields_values, $table, $no_quote_fields);

				foreach ($nArr as $k => $v) {
					$fields[] = $k . '=' . $v;
				}
			}

				// Build query:
			$query = 'UPDATE ' . $table . ' SET ' . implode(',', $fields) .
					(strlen($where) > 0 ? ' WHERE ' . $where : '');

			return $query;
		} else {
			throw new InvalidArgumentException(
				'Fatal Error: "Where" clause argument for UPDATE query was not a string in $this->UPDATEquery() !',
				1270853880
			);
		}
// 		return $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
	}
	/**
	 * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See t3lib_db::fullQuoteArray()
	 * @return	pointer		MsSQL result pointer / DBAL object
	 */
	public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
		$query = $this->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
		$res = $this->sql_query($query);
		return $res;
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
	 * @return	pointer		MsSQL result pointer / DBAL object
	 */
	public function exec_DELETEquery($table, $where) {
		$query = $this->DELETEquery($table, $where);
		$res = $this->sql_query($query);
		return $res;
	}

	/**
	 * Connects to database for TYPO3 sites:
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $db
	 * @return	void
	 */
	private function connectDB($credArr) {
		$schema = $credArr['schema'];
		if (!$schema) {
			throw new RuntimeException(
				'TYPO3 Fatal Error: No database schema selected!',
				1271953883
			);
		}
		$link = $this->connect($credArr);

		// Select DB
		$ret = mssql_select_db($schema, $link);

		if (!$ret) {
			throw new RuntimeException(
				'Could not select MsSQL database '.$TYPO3_db,
				1271953993
			);

		}

		return $link;
	}

	/**
	 * Open a (persistent) connection to a MySQL server
	 * mssql_pconnect() wrapper function
	 * Method is taken from t3lib_db
	 *
	 * @param string Database host IP/domain
	 * @param string Username to connect with.
	 * @param string Password to connect with.
	 * @return pointer Returns a positive MySQL persistent link identifier on success, or FALSE on error.
	 */
	private function connect($credArr) {
		$dbHost = $credArr['host'] ? $credArr['host'] : 'localhost';
		$dbUsername = $credArr['username'];
		$dbPassword = $credArr['password'];

		// if the connection fails we need a different method to get the error message
		@ini_set('track_errors', 1);
		@ini_set('html_errors', 0);

		// check if MySQL extension is loaded
		if (!extension_loaded('mssql')) {
			$message = 'Database Error: It seems that MsSQL support for PHP is not installed!';
			throw new RuntimeException($message, 1271492606);
		}

		// Check for client compression
		if ($credArr['no_pconnect']) {
			$link = @mssql_connect($dbHost, $dbUsername, $dbPassword);
		} else {
			$link = @mssql_pconnect($dbHost, $dbUsername, $dbPassword);
		}

		$error_msg = $php_errormsg;
		@ini_restore('track_errors');
		@ini_restore('html_errors');

		if (!$link) {
			$message = 'Database Error: Could not connect to MySQL server ' . $dbHost .
					' with user ' . $dbUsername . ': ' . $error_msg;
			throw new RuntimeException($message, 1271492616);

		}

		$setDBinit = t3lib_div::trimExplode(LF,
				str_replace("' . LF . '", LF, $credArr['setDBinit']), TRUE);
		foreach ($setDBinit as $v) {
			if (mssql_query($v, $link) === FALSE) {
				// TODO: handler errors
			}
		}

		return $link;
	}

	/**
	 * Executes query
	 * mssql_query() wrapper function
	 *
	 * @param	string		Query to execute
	 * @return	pointer		Result pointer / DBAL object
	 */
	public function sql_query($query) {
		$res = mssql_query($query, $this->db);
		return $res;
	}
	/**
	 * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
	 * mssql_fetch_assoc() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query) / DBAL object
	 * @return	array		Associative array of result row.
	 */
	public function sql_fetch_assoc($res) {
		return mssql_fetch_assoc($res);
	}
	/**
	 * Free result memory
	 * mssql_free_result() wrapper function
	 *
	 * @param	pointer		MySQL result pointer to free / DBAL object
	 * @return	boolean		Returns TRUE on success or FALSE on failure.
	 */
	public function sql_free_result($res) {
		return mssql_free_result($res);
	}

	/**
	 * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
	 * mssql_affected_rows() wrapper function
	 *
	 * @return	integer		Number of rows affected by last query
	 */
	function sql_affected_rows() {
		return mssql_rows_affected($this->db);
	}

	/**
	 * Get the ID generated from the previous INSERT operation
	 *
	 * @see http://www.php.net/manual/en/function.mssql-query.php#25274 For lastInsertId.
	 *
	 * @return	integer		The uid of the last inserted record.
	 */
	public function sql_insert_id() {
		return $this->lastInsertId;
	}

	/**
	 * Returns the error status on the last sql() execution
	 * mssql_error() wrapper function
	 *
	 * @return	string		MySQL error string.
	 */
	public function sql_error() {
		return mssql_get_last_message();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MsSQL.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MsSQL.php']);
}
