<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2018 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * DB wrapper for TYPO3 doctrine like dbal
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_db_TYPO3DBAL implements tx_rnbase_util_db_IDatabase, tx_rnbase_util_db_IDatabaseT3
{
    /**
     * Set "true" if you want database errors outputted.
     *
     * @var bool
     */
    public $debugOutput = false;

    /**
     * Internally: Set to last built query (not necessarily executed...)
     *
     * @var string
     */
    public $debug_lastBuiltQuery = '';

    /**
     * Set "TRUE" if you want the last built query to be stored in $debug_lastBuiltQuery independent of $this->debugOutput
     *
     * @var bool
     */
    public $store_lastBuiltQuery = false;

    /**
     * Internal property to store the afected rows of last update or delete query
     *
     * @var int
     */
    protected $lastAffectedRows = 0;

    /**
     * Internal Field to store the insert id of last insert query
     *
     * @var int
     */
    protected $lastInsertId = 0;

    /**
     * Returns the TYPO3 connection from connection pool
     *
     * @return \TYPO3\CMS\Core\Database\Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getConnection()
    {
        /* @var \TYPO3\CMS\Core\Database\ConnectionPool $pool */
        $pool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        return $pool->getConnectionByName(\TYPO3\CMS\Core\Database\ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    /**
     * @return tx_rnbase_util_db_Builder
     */
    protected function getBuilderUtil()
    {
        return tx_rnbase_util_db_Builder::instance();
    }

    /**
     * Creates a SELECT SQL-statement
     *
     * Taken from TYPO3/CMS-CORE
     *     \TYPO3\CMS\Core\Database\DatabaseConnection::SELECTquery
     *
     * @param string $selectFields See exec_SELECTquery()
     * @param string $fromTable See exec_SELECTquery()
     * @param string $whereClause See exec_SELECTquery()
     * @param string $groupBy See exec_SELECTquery()
     * @param string $orderBy See exec_SELECTquery()
     * @param string $limit See exec_SELECTquery()
     *
     * @return string Full SQL query for SELECT
     */
    public function SELECTquery(
        $selectFields,
        $fromTable,
        $whereClause,
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this function
        $query = $this->getBuilderUtil()->SELECTquery(
            $selectFields,
            $fromTable,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );

        // Return query
        if ($this->debugOutput || $this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes a SELECT SQL-statement
     *
     * @param string $selectFields List of fields to select from the table.
     *      This is what comes right after "SELECT ...". Required value.
     * @param string $fromTable Table(s) from which to select.
     *      This is what comes right after "FROM ...". Required value.
     * @param string $whereClause Additional WHERE clauses put in the end of the query.
     *      NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *      DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function exec_SELECTquery(
        $selectFields,
        $fromTable,
        $whereClause,
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        return $this->getConnection()->executeQuery(
            $this->SELECTquery(
                $selectFields,
                $fromTable,
                $whereClause,
                $groupBy,
                $orderBy,
                $limit
            )
        );
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     *
     * Taken from TYPO3/CMS-CORE
     *     \TYPO3\CMS\Core\Database\DatabaseConnection::INSERTquery
     *
     * @param string $table Table name
     * @param array $fieldsValues Field values as key=>value pairs.
     * @param bool|string|array $noQuoteFields
     *
     * @return string SQL query
     */
    public function INSERTquery($table, $fieldsValues, $noQuoteFields = false)
    {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this

        $query = $this->getBuilderUtil()->INSERTquery($table, $fieldsValues, $noQuoteFields);

        if ($query === null) {
            return null;
        }

        // Return query
        if ($this->debugOutput || $this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     *
     * @param string $table Table name
     * @param array $fieldsValues Field values as key=>value pairs.
     * @param bool|string|array $noQuoteFields
     *
     * @return void
     */
    public function exec_INSERTquery(
        $table,
        $fieldsValues,
        $noQuoteFields = false
    ) {
        $connection = $this->getConnection();
        $this->lastAffectedRows = $connection->insert($table, $fieldsValues);
        $this->lastInsertId = $connection->lastInsertId($table);
    }

    /**
     * Creates and executes an UPDATE SQL-statement
     * for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     *
     * Taken from TYPO3/CMS-CORE
     *     \TYPO3\CMS\Core\Database\DatabaseConnection::UPDATEquery
     *
     * @param string $table Database tablename
     * @param sring $where WHERE clause, eg. "uid=1".
     *      NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param array $fieldsValues Field values as key=>value pairs.
     * @param bool|string|array $noQuoteFields
     *
     * @return string sql query
     */
    public function UPDATEquery(
        $table,
        $where,
        $fieldsValues,
        $noQuoteFields = false
    ) {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this
        $query = $this->getBuilderUtil()->UPDATEquery(
            $table,
            $where,
            $fieldsValues,
            $noQuoteFields
        );

        if ($this->debugOutput || $this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes an UPDATE SQL-statement
     * for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1".
     *      NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param array $fieldsValues Field values as key=>value pairs.
     * @param bool|string|array $noQuoteFields
     *
     * @return  void
     */
    public function exec_UPDATEquery(
        $table,
        $where,
        $fieldsValues,
        $noQuoteFields = false
    ) {
        $query = $this->UPDATEquery($table, $where, $fieldsValues, $noQuoteFields);
        $this->lastAffectedRows = $this->getConnection()->executeUpdate($query);
    }

    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause
     *
     * Taken from TYPO3/CMS-CORE
     *     \TYPO3\CMS\Core\Database\DatabaseConnection::DELETEquery
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1".
     *      NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *
     * @return string sql query
     */
    public function DELETEquery($table, $where)
    {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this function
        $query = $this->getBuilderUtil()->DELETEquery($table, $where);

        if ($this->debugOutput || $this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }

        return $query;
    }

    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1".
     *      NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *
     * @return void
     */
    public function exec_DELETEquery($table, $where)
    {
        $query = $this->DELETEquery($table, $where);
        $this->lastAffectedRows = $this->getConnection()->executeUpdate($query);
    }

    /**
     * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
     *
     * @param \Doctrine\DBAL\Driver\Statement $res
     * @return  array Associative array of result row.
     */
    public function sql_fetch_assoc($res)
    {
        return $res->fetch(\PDO::FETCH_ASSOC);
    }
    /**
     * Free result memory
     *
     * @param \Doctrine\DBAL\Driver\Statement $res
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function sql_free_result($res)
    {
        return $res->closeCursor();
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
     * mysql_affected_rows() wrapper function
     *
     * @return  int     Number of rows affected by last query
     */
    public function sql_affected_rows()
    {
        return $this->lastAffectedRows;
    }

    /**
     * Get the ID generated from the previous INSERT operation
     * mysql_insert_id() wrapper function
     *
     * @return  int     The uid of the last inserted record.
     */
    public function sql_insert_id()
    {
        return $this->lastInsertId;
    }

    /**
     * Returns the error status on the last sql() execution
     * mysql_error() wrapper function
     *
     * @return  string      MySQL error string.
     */
    public function sql_error()
    {
        return $this->getConnection()->errorInfo();
    }

    /**
     * Substitution for PHP function "addslashes()"
     *
     * Use this function instead of the PHP addslashes() function when you build queries
     * this will prepare your code for DBAL.
     * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible.
     * Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
     *
     * @param string $str Input string
     * @param string $table Table name for which to quote string.
     * @return string Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     */
    public function quoteStr($str, $table)
    {
        return $str;
    }

    /**
     * Escaping values for SQL LIKE statements.
     *
     * @param string $str Input string
     * @param string $table Table name for which to escape string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     * @return string Output string; % and _ will be escaped with \ (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    public function escapeStrForLike($str, $table)
    {
        return addcslashes($str, '_%');
    }

    /**
     * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
     *
     * @param array $arr Array with values (either associative or non-associative array)
     * @param string $table Table name for which to quote
     * @param bool|array|string $noQuote List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
     * @param bool $allowNull Whether to allow NULL values
     *
     * @return array The input array with the values quoted
     */
    public function fullQuoteArray($arr, $table, $noQuote = false, $allowNull = false)
    {
        if (is_string($noQuote)) {
            $noQuote = explode(',', $noQuote);
        } elseif (!is_array($noQuote)) {
            $noQuote = (bool)$noQuote;
        }

        if ($noQuote === true) {
            return $arr;
        }

        foreach ($arr as $k => $v) {
            if ($noQuote === false || !in_array($k, $noQuote)) {
                $arr[$k] = $this->fullQuoteStr($v, $table, $allowNull);
            }
        }

        return $arr;
    }

    /**
     * Escaping and quoting values for SQL statements.
     *
     * @param string $str Input string
     * @param string $table Table name for which to quote string.
     * @param bool $allowNull Whether to allow NULL values
     *
     * @return string Output string
     */
    public function fullQuoteStr($str, $table, $allowNull = false)
    {
        if ($allowNull && $str === null) {
            return 'NULL';
        }
        if (is_bool($str)) {
            $str = (int)$str;
        }

        return $this->getConnection()->quote($str);
    }
}
