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
	private $db = null;

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
		return $GLOBALS['TYPO3_DB']->SELECTquery($select_fields, $from_table, $where_clause, $groupBy,$orderBy,$limit);
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
tx_rnbase_util_Debug::debug(array(
		$query,
		'@TODO: ADD " exec("$query SELECT @@IDENTITY as insertId") and store the insertId for the sql_insert_id method!!!',
		'@see http://www.php.net/manual/en/function.mssql-query.php#25274'
	), 'DEBUG: '.__FILE__.'&'.__METHOD__.' Line: '.__LINE__); // @TODO: remove me
		$res = mssql_query($query, $this->db);
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
		return $GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values, $no_quote_fields);
	}
	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @param	string/array		See fullQuoteArray()
	 * @return	pointer		MsSQL result pointer / DBAL object
	 */
	public function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
		$res = mssql_query($this->INSERTquery($table, $fields_values, $no_quote_fields), $this->db);
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
		return $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
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
		$res = mssql_query($this->UPDATEquery($table, $where, $fields_values, $no_quote_fields), $this->db);
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
		$res = mssql_query($this->DELETEquery($table, $where), $this->db);
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
		$this->setSqlMode($link);

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
		if (!extension_loaded('mysql')) {
			$message = 'Database Error: It seems that MySQL support for PHP is not installed!';
			throw new RuntimeException($message, 1271492606);
		}

		// Check for client compression
		$isLocalhost = (
			t3lib_div::isFirstPartOfStr($dbHost, 'localhost')
			|| t3lib_div::isFirstPartOfStr($dbHost, '127.0.0.1')
		);
		if ($credArr['no_pconnect']) {
			if ($credArr['dbClientCompress'] && !$isLocalhost) {
				// We use PHP's default value for 4th parameter (new_link), which is false.
				// See PHP sources, for example: file php-5.2.5/ext/mysql/php_mysql.c,
				// function php_mssql_do_connect(), near line 525
				$link = @mssql_connect($dbHost, $dbUsername, $dbPassword, FALSE, mssql_CLIENT_COMPRESS);
			} else {
				$link = @mssql_connect($dbHost, $dbUsername, $dbPassword);
			}
		} else {
			if ($credArr['dbClientCompress'] && !$isLocalhost) {
				// See comment about 4th parameter in block above
				$link = @mssql_pconnect($dbHost, $dbUsername, $dbPassword, mssql_CLIENT_COMPRESS);
			} else {
				$link = @mssql_pconnect($dbHost, $dbUsername, $dbPassword);
			}
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
	 * Fixes the SQL mode by unsetting NO_BACKSLASH_ESCAPES if found.
	 *
	 * @return void
	 */
	private function setSqlMode($dblink) {
		$resource = mssql_query('SELECT @@SESSION.sql_mode;', $dblink);
		if (is_resource($resource)) {
			$result = mssql_fetch_row($resource);
			if (isset($result[0]) && $result[0] && strpos($result[0], 'NO_BACKSLASH_ESCAPES') !== FALSE) {
				$modes = array_diff(
					t3lib_div::trimExplode(',', $result[0]),
					array('NO_BACKSLASH_ESCAPES')
				);
				$query = 'SET sql_mode=\'' . mssql_real_escape_string(implode(',', $modes)) . '\';';
				mssql_query($query, $dblink);
			}
		}
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
	 * mssql_insert_id() wrapper function
	 *
	 * @return	integer		The uid of the last inserted record.
	 */
	public function sql_insert_id() {
		throw new Exception('MS SQL doesnt support insert_id. @see '.__CLASS__.'::exec_SELECTquery');
		return mssql_insert_id($this->db);
	}

	/**
	 * Returns the error status on the last sql() execution
	 * mssql_error() wrapper function
	 *
	 * @return	string		MySQL error string.
	 */
	public function sql_error() {
		return mssql_get_last_message($this->db);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MsSQL.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MsSQL.php']);
}
