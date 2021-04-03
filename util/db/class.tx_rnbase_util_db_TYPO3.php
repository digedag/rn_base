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
 * DB wrapper for TYPO3 database.
 */
class tx_rnbase_util_db_TYPO3 implements tx_rnbase_util_db_IDatabase, tx_rnbase_util_db_IDatabaseT3
{
    /**
     * Internally: Set to last built query (not necessarily executed...).
     *
     * @var string
     */
    public $debug_lastBuiltQuery = '';

    /**
     * Set "TRUE" if you want the last built query to be stored in $debug_lastBuiltQuery independent of $this->debugOutput.
     *
     * @var bool
     */
    public $store_lastBuiltQuery = false;

    /**
     * Creates a SELECT SQL-statement.
     *
     * @param string List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string optional GROUP BY field(s), if none, supply blank string
     * @param string optional ORDER BY field(s), if none, supply blank string
     * @param string optional LIMIT value ([begin,]max), if none, supply blank string
     *
     * @return string SQL Query
     */
    public function SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        $query = $GLOBALS['TYPO3_DB']->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);

        // Return query
        if ($this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes a SELECT SQL-statement.
     *
     * @param   string      List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param   string      Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string      additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param   string      optional GROUP BY field(s), if none, supply blank string
     * @param   string      optional ORDER BY field(s), if none, supply blank string
     * @param   string      optional LIMIT value ([begin,]max), if none, supply blank string
     *
     * @return pointer MySQL result pointer / DBAL object
     */
    public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        if ($this->store_lastBuiltQuery) {
            $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
        }

        return $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     *
     * @param string Table name
     * @param array Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param array
     *
     * @return string SQL query
     */
    public function INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        $query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values, $no_quote_fields);

        // Return query
        if ($this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     *
     * @param   string      Table name
     * @param   array       Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param   array
     *
     * @return pointer MySQL result pointer / DBAL object
     */
    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        if ($this->store_lastBuiltQuery) {
            $this->INSERTquery($table, $fields_values, $no_quote_fields);
        }

        return $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fields_values, $no_quote_fields);
    }

    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     *
     * @param   string      Database tablename
     * @param string      WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param   array       Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param   array
     *
     * @return string sql query
     */
    public function UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fields_values, $no_quote_fields);

        // Return query
        if ($this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     *
     * @param   string      Database tablename
     * @param string      WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param   array       Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param   array
     *
     * @return pointer MySQL result pointer / DBAL object
     */
    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        if ($this->store_lastBuiltQuery) {
            $this->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
        }

        return $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields);
    }

    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause.
     *
     * @param   string      Database tablename
     * @param string      WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *
     * @return string sql query
     */
    public function DELETEquery($table, $where)
    {
        $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);

        // Return query
        if ($this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause.
     *
     * @param   string      Database tablename
     * @param string      WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *
     * @return pointer MySQL result pointer / DBAL object
     */
    public function exec_DELETEquery($table, $where)
    {
        if ($this->store_lastBuiltQuery) {
            $this->DELETEquery($table, $where);
        }

        return $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
    }

    /**
     * Executes query
     * mysql_query() wrapper function.
     *
     * @param   string      Query to execute
     *
     * @return pointer Result pointer / DBAL object
     */
    public function sql_query($query)
    {
        return $GLOBALS['TYPO3_DB']->sql_query($query);
    }

    /**
     * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
     * mysql_fetch_assoc() wrapper function.
     *
     * @param   pointer     MySQL result pointer (of SELECT query) / DBAL object
     *
     * @return array associative array of result row
     */
    public function sql_fetch_assoc($res)
    {
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    }

    /**
     * Free result memory
     * mysql_free_result() wrapper function.
     *
     * @param   pointer     MySQL result pointer to free / DBAL object
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function sql_free_result($res)
    {
        return $GLOBALS['TYPO3_DB']->sql_free_result($res);
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
     * mysql_affected_rows() wrapper function.
     *
     * @return int Number of rows affected by last query
     */
    public function sql_affected_rows()
    {
        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
    }

    /**
     * Get the ID generated from the previous INSERT operation
     * mysql_insert_id() wrapper function.
     *
     * @return int the uid of the last inserted record
     */
    public function sql_insert_id()
    {
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

    /**
     * Returns the error status on the last sql() execution
     * mysql_error() wrapper function.
     *
     * @return string mySQL error string
     */
    public function sql_error()
    {
        return $GLOBALS['TYPO3_DB']->sql_error();
    }

    /**
     * Substitution for PHP function "addslashes()".
     *
     * Use this function instead of the PHP addslashes() function when you build queries
     * this will prepare your code for DBAL.
     * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible.
     * Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
     *
     * @param string $str   Input string
     * @param string $table table name for which to quote string
     *
     * @return string Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     */
    public function quoteStr($str, $table)
    {
        return $GLOBALS['TYPO3_DB']->quoteStr($str, $table);
    }

    /**
     * Escaping values for SQL LIKE statements.
     *
     * @param string $str   Input string
     * @param string $table table name for which to escape string
     *
     * @return string Output string; % and _ will be escaped with \ (or otherwise based on DBAL handler)
     */
    public function escapeStrForLike($str, $table)
    {
        return $GLOBALS['TYPO3_DB']->escapeStrForLike($str, $table);
    }

    /**
     * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
     *
     * @param array             $arr       Array with values (either associative or non-associative array)
     * @param string            $table     Table name for which to quote
     * @param bool|array|string $noQuote   List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
     * @param bool              $allowNull Whether to allow NULL values
     *
     * @return array The input array with the values quoted
     */
    public function fullQuoteArray($arr, $table, $noQuote = false, $allowNull = false)
    {
        return $GLOBALS['TYPO3_DB']->fullQuoteArray($arr, $table, $noQuote, $allowNull);
    }

    /**
     * Escaping and quoting values for SQL statements.
     *
     * @param string $str       Input string
     * @param string $table     table name for which to quote string
     * @param bool   $allowNull Whether to allow NULL values
     *
     * @return string Output string
     */
    public function fullQuoteStr($str, $table, $allowNull = false)
    {
        return $GLOBALS['TYPO3_DB']->fullQuoteStr($str, $table, $allowNull);
    }

    /**
     * Whether an actual connection to the database is established.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $GLOBALS['TYPO3_DB']->isConnected();
    }
}
