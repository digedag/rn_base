<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2015 Rene Nitzsche
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
 * DB wrapper for other (external) databases
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_util_db_MySQL implements tx_rnbase_util_db_IDatabase {
	/**
	 * @var boolean
	 */
	protected $isConnected = FALSE;
	/**
	 * @var mysqli
	 */
	private $db = NULL;

	/**
	 * constructor
	 *
	 * @param array $credentials
	 * @throws tx_rnbase_util_db_Exception
	 */
	public function __construct($credentials) {
		if(empty($credentials) || !is_array($credentials)) {
			throw new tx_rnbase_util_db_Exception(
				'No credentials given for database!'
			);
		}
		$this->connectDB($credentials);
	}

	/**
	 * mapps all function calls to the mysql object
	 *
	 * @param string $methodName
	 * @param array $args
	 * @return mixed
	 */
	public function __call($methodName, $args) {
		return call_user_func_array(array($this->db, $methodName), $args);
	}

	/**
	 * Central query method. Also checks if there is a database connection.
	 * Use this to execute database queries instead of directly calling $this->link->query()
	 *
	 * @param string $query The query to send to the database
	 * @return bool|mysqli_result
	 */
	protected function query($query) {
		return $this->db->query($query);
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
	 * @return boolean|mysqli_result
	 */
	public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = ''){
		$query = $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
		$res = $this->query($query);
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
	 * @return boolean|mysqli_result
	 */
	public function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
		$res = $this->query(
			$this->INSERTquery($table, $fields_values, $no_quote_fields)
		);
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
	 * @return	boolean|mysqli_result
	 */
	public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
		$res = $this->query(
			$this->UPDATEquery($table, $where, $fields_values, $no_quote_fields)
		);
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
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	public function exec_DELETEquery($table, $where) {
		$res = $this->query($this->DELETEquery($table, $where));
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
		$schema = isset($credArr['database']) ? $credArr['database'] : $credArr['schema'];
		if (!$schema) {
			throw new RuntimeException(
				'TYPO3 Fatal Error: No database selected!',
				1271953882
			);
		}
		$this->connect($credArr);
		// Select DB
		$ret = $this->db->select_db($schema);
		if (!$ret) {
			throw new RuntimeException(
				'Could not select MySQL database ' . $schema . ': ' . mysql_error(),
				1271953992
			);
		}
		$this->setSqlMode();
	}

	/**
	 * Open a (persistent) connection to a MySQL server
	 * mysql_pconnect() wrapper function
	 * Method is taken from t3lib_db
	 *
	 * @param string Database host IP/domain
	 * @param string Username to connect with.
	 * @param string Password to connect with.
	 */
	private function connect($credArr) {

		if (!extension_loaded('mysqli')) {
			throw new \RuntimeException(
				'Database Error: PHP mysqli extension not loaded. This is a must have for TYPO3 CMS!',
				1271492607
			);
		}

		$dbHost = $credArr['host'] ? $credArr['host'] : 'localhost';
		$dbUsername = $credArr['username'];
		$dbPassword = $credArr['password'];
		$dbPort = isset($credArr['port']) ? (int) $credArr['port'] : 3306;
		$dbSocket = empty($credArr['socket']) ? NULL : $credArr['socket'];
		$dbCompress = !empty($credArr['dbClientCompress']) && $dbHost != 'localhost' && $dbHost != '127.0.0.1';
		if (isset($credArr['no_pconnect']) && !$credArr['no_pconnect']) {
			$dbHost = 'p:' . $dbHost;
		}

		$this->db = mysqli_init();

		$connected = $this->db->real_connect(
			$dbHost,
			$dbUsername,
			$dbPassword,
			NULL,
			$dbPort,
			$dbSocket,
			$dbCompress ? MYSQLI_CLIENT_COMPRESS : 0
		);

		if (!$connected) {
			$message = 'Database Error: Could not connect to MySQL server ' . $dbHost .
				' with user ' . $dbUsername . ': ' . $this->sql_error();
			throw new RuntimeException($message, 1271492616);
		}

		$this->isConnected = TRUE;

		$connectionCharset = empty($credArr['connectionCharset']) ? 'utf8' : $credArr['connectionCharset'];
		$this->db->set_charset($connectionCharset);

		$setDBinit = t3lib_div::trimExplode(LF, str_replace("' . LF . '", LF, $credArr['setDBinit']), TRUE);
		foreach ($setDBinit as $v) {
			if ($this->query($v) === FALSE) {
				// TODO: handler errors
			}
		}
	}

	/**
	 * Fixes the SQL mode by unsetting NO_BACKSLASH_ESCAPES if found.
	 *
	 * @return void
	 */
	private function setSqlMode() {
		$resource = $this->sql_query('SELECT @@SESSION.sql_mode;');
		if ($resource) {
			$result = $resource->fetch_row();
			if (isset($result[0]) && $result[0] && strpos($result[0], 'NO_BACKSLASH_ESCAPES') !== FALSE) {
				$modes = array_diff(GeneralUtility::trimExplode(',', $result[0]), array('NO_BACKSLASH_ESCAPES'));
				$query = 'SET sql_mode=\'' . $this->db->real_escape_string(implode(',', $modes)) . '\';';
				$this->sql_query($query);
				GeneralUtility::sysLog(
					'NO_BACKSLASH_ESCAPES could not be removed from SQL mode: ' . $this->sql_error(),
					'rn_base',
					GeneralUtility::SYSLOG_SEVERITY_ERROR
				);
			}
		}
	}
	/**
	 * Executes query
	 * mysql_query() wrapper function
	 *
	 * @param	string		Query to execute
	 * @return	pointer		Result pointer / DBAL object
	 */
	public function sql_query($query) {
		return $this->query($query);
	}
	/**
	 * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
	 * mysql_fetch_assoc() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query) / DBAL object
	 * @return	array		Associative array of result row.
	 */
	public function sql_fetch_assoc($res) {
		return $res->fetch_assoc();
	}
	/**
	 * Free result memory
	 * mysql_free_result() wrapper function
	 *
	 * @param	pointer		MySQL result pointer to free / DBAL object
	 * @return	boolean		Returns TRUE on success or FALSE on failure.
	 */
	public function sql_free_result($res) {
		return $res->free();
	}

	/**
	 * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
	 * mysql_affected_rows() wrapper function
	 *
	 * @return	integer		Number of rows affected by last query
	 */
	function sql_affected_rows() {
		return $this->db->affected_rows;
	}

	/**
	 * Get the ID generated from the previous INSERT operation
	 * mysql_insert_id() wrapper function
	 *
	 * @return	integer		The uid of the last inserted record.
	 */
	public function sql_insert_id() {
		return $this->db->insert_id;
	}

	/**
	 * Returns the error status on the last sql() execution
	 * mysql_error() wrapper function
	 *
	 * @return	string		MySQL error string.
	 */
	public function sql_error() {
		return $this->db->error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MySQL.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/db/class.tx_rnbase_util_db_MySQL.php']);
}
