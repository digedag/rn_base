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
 * DB utility class, which implements the old TYPO3_DB functionality
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_db_Builder implements Tx_Rnbase_Interface_Singleton
{
    /**
     * @return tx_rnbase_util_db_Builder
     */
    public static function instance()
    {
        return tx_rnbase::makeInstance(get_called_class());
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
        // Build basic query
        $query = 'SELECT ' . $selectFields . ' FROM ' . $fromTable;
        $query .= ((string)$whereClause !== '' ? ' WHERE ' . $whereClause : '');
        // Group by
        $query .= (string)$groupBy !== '' ? ' GROUP BY ' . $groupBy : '';
        // Order by
        $query .= (string)$orderBy !== '' ? ' ORDER BY ' . $orderBy : '';
        // Group by
        $query .= (string)$limit !== '' ? ' LIMIT ' . $limit : '';

        return $query;
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
        // function (contrary to values in the arrays which may be insecure).
        if (!is_array($fieldsValues) || empty($fieldsValues)) {
            return null;
        }

        // Quote and escape values
        $fieldsValues = $this->fullQuoteArray($fieldsValues, $table, $noQuoteFields, true);

        // Build query
        $query = 'INSERT INTO ' . $table . ' (' . implode(',', array_keys($fieldsValues)) . ')' .
            ' VALUES (' . implode(',', $fieldsValues) . ')';

        return $query;
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
        // function (contrary to values in the arrays which may be insecure).
        if (!is_string($where)) {
            throw new \InvalidArgumentException(
                'TYPO3 Fatal Error: "Where" clause argument for UPDATE query must be a string!',
                1270853880
            );
        }

        $fields = [];
        if (is_array($fieldsValues) && !empty($fieldsValues)) {
            // Quote and escape values
            $nArr = $this->fullQuoteArray($fieldsValues, $table, $noQuoteFields, true);
            foreach ($nArr as $k => $v) {
                $fields[] = $k . '=' . $v;
            }
        }

        // Build query
        $query = 'UPDATE ' . $table;
        $query .= ' SET ' . implode(',', $fields);
        $query .= ((string)$where !== '' ? ' WHERE ' . $where : '');

        return $query;
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
        if (!is_string($where)) {
            throw new \InvalidArgumentException(
                'TYPO3 Fatal Error: "Where" clause argument for DELETE query must be a string!',
                1270853881
            );
        }

        // Table and fieldnames should be "SQL-injection-safe" when supplied to this function
        $query = 'DELETE FROM ' . $table;
        $query .= ((string)$where !== '' ? ' WHERE ' . $where : '');

        return $query;
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
        // @FIXME: how to mysql_real_escape_string without a mysqli link?

        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $str);
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

        return '\'' . $this->quoteStr($str, $table) . '\'';
    }
}
