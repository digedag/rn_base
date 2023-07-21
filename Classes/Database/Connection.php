<?php

namespace Sys25\RnBase\Database;

use Sys25\RnBase\Backend\Utility\TCA;
use Sys25\RnBase\Database\Driver\DatabaseException;
use Sys25\RnBase\Database\Driver\IDatabase;
use Sys25\RnBase\Database\Driver\LegacyQueryBuilder;
use Sys25\RnBase\Database\Driver\MySqlDatabase;
use Sys25\RnBase\Database\Driver\TYPO3Database;
use Sys25\RnBase\Database\Driver\TYPO3DBAL;
use Sys25\RnBase\Database\Query\From;
use Sys25\RnBase\Domain\Collection\BaseCollection;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Domain\Model\DynamicTableInterface;
use Sys25\RnBase\Typo3Wrapper\Core\SingletonInterface;
use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Environment;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Utility\Typo3Classes;
use tx_rnbase;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2023 Rene Nitzsche
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
 * Contains utility functions for database access.
 *
 * @author Rene Nitzsche
 * @author Michael Wagner
 * @author Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Connection implements SingletonInterface
{
    /**
     * returns an instance of this class.
     *
     * @return Connection
     */
    public static function getInstance()
    {
        return tx_rnbase::makeInstance(get_called_class());
    }

    /**
     * Generische Schnittstelle für Datenbankabfragen.
     *
     * Anstatt vieler Parameter wird hier ein
     * Hash als Parameter verwendet, der mögliche Informationen aufnimmt.
     * der from sollte wiefolgt aussehen:
     * <pre>
     * - 'table' - the table name,
     * - 'alias' - the table alias
     * - 'clause' - the complete where clause with joins or subselects or whatever you want
     * </pre>
     * Es sind die folgenden Parameter zulässig:
     * <pre>
     * - 'where' - the Where-Clause
     * - 'groupby' - the GroupBy-Clause
     * - 'orderby' - the OrderBy-Clause
     * - 'sqlonly' - returns the generated SQL statement or prepared QueryBuilder instance. No database access.
     * - 'limit' - limits the number of result rows
     * - 'wrapperclass' - A wrapper for each result rows
     * - 'pidlist' - A list of page-IDs to search for records
     * - 'recursive' - the recursive level to search for records in pages
     * - 'enablefieldsoff' - deactivate enableFields check
     * - 'enablefieldsbe' - force enableFields check for BE (this usually ignores hidden records)
     * - 'enablefieldsfe' - force enableFields check for FE
     * - 'db' - external database: Sys25\RnBase\Database\Driver\IDatabase
     * - 'ignorei18n' - do not translate record to fe language
     * - 'forcei18n' - force the translation of the record
     * - 'i18nolmode' - translation mode, possible value: 'hideNonTranslated'
     * </pre>
     *
     * @param string $what  Requested columns
     * @param array|string|From $from  Either the name of on table or an array with index 0 an array of Join or a from clause string
     *                   and index 1 the requested tablename and optional index 2 a table alias to use
     * @param array $arr The options array
     * @param bool $debug Set to true to debug the sql string
     *
     * @return array
     */
    public function doSelect($what, $from, $arr, $debug = false)
    {
        Misc::callHook(
            'rn_base',
            'util_db_do_select_pre',
            [
                'what' => &$what,
                'from' => &$from,
                'options' => &$arr,
                'debug' => &$debug,
            ]
        );

        $debug = $debug ?? intval($arr['debug'] ?? 0) > 0;
        if ($debug) {
            $time = microtime(true);
            $mem = memory_get_usage();
        }

        $arr['debug'] = $debug;
        $arr['what'] = $what;
        $from = From::buildInstance($from);

        $queryBuilder = null;
        if (TYPO3::isTYPO87OrHigher()) {
            $qbFacade = new QueryBuilderFacade();
            $queryBuilder = $qbFacade->doSelect($what, $from, $arr);
        }

        if ($queryBuilder) {
            $rows = $this->doSelectByQueryBuilder($queryBuilder, $from, $arr);
        } else {
            $rows = $this->doSelectLegacy($what, $from, $arr, $debug);
        }

        if (is_string($rows) || (TYPO3::isTYPO87OrHigher() && $rows instanceof QueryBuilder)) {
            // sqlOnly
            return $rows;
        }

        if ($debug) {
            Debug::debug([
                'Rows retrieved ' => count($rows),
                'Time ' => (microtime(true) - $time),
                'Memory consumed ' => (memory_get_usage() - $mem),
                'QB used' => is_object($queryBuilder),
            ], 'SQL statistics');
        }

        Misc::callHook(
            'rn_base',
            'util_db_do_select_post',
            [
                'rows' => &$rows,
            ]
        );

        return $rows;
    }

    private function doSelectByQueryBuilder(QueryBuilder $queryBuilder, From $from, array $arr)
    {
        $sqlOnly = intval($arr['sqlonly'] ?? null) > 0;

        if ($sqlOnly) {
            return $queryBuilder;
        }

        $rows = $this->initRows($arr);
        $wrapper = is_string($arr['wrapperclass'] ?? null) ? trim($arr['wrapperclass']) : 0;
        $callback = isset($arr['callback']) ? $arr['callback'] : false;

        foreach ($queryBuilder->execute()->fetchAll() as $row) {
            $this->appendRow($rows, $row, $from->getTableName(), $wrapper, $callback, $arr);
        }

        return $rows;
    }

    private function doSelectLegacy($what, From $from, $arr, $debug)
    {
        $tableName = $from->getTableName();
        $fromClause = $from->getClause();
        $fromClause = $fromClause ?: ($from->isComplexTable() ? $tableName : '');
        $tableAlias = $from->getAlias();
        $fromClause = $fromClause ?: trim(sprintf('%s %s', $tableName, $tableAlias));

        $where = is_string($arr['where'] ?? false) ? $arr['where'] : '1=1';
        $groupBy = $arr['groupby'] ?? '';
        if ($groupBy) {
            $groupBy .= empty($arr['having'] ?? '') ? '' : ' HAVING '.$arr['having'];
        }
        $orderBy = $arr['orderby'] ?? '';
        $offset = intval($arr['offset'] ?? 0) > 0 ? intval($arr['offset']) : 0;
        $limit = intval($arr['limit'] ?? 0) > 0 ? intval($arr['limit']) : '';
        $arr['pidlist'] = $arr['pidlist'] ?? '';
        $pidList = (is_string($arr['pidlist']) || is_int($arr['pidlist'])) ? $arr['pidlist'] : '';
        $recursive = (int) ($arr['recursive'] ?? 0);
        $i18n = empty($arr['i18n']) ? '' : $arr['i18n'];
        $sqlOnly = (int) ($arr['sqlonly'] ?? 0);
        $union = empty($arr['union']) ? '' : $arr['union'];

        // offset und limit kombinieren
        // bei gesetztem limit ist offset optional
        if ($limit) {
            $limit = ($offset > 0) ? $offset.','.$limit : $limit;
        } elseif ($offset) {
            // Bei gesetztem Offset ist limit Pflicht (default 1000)
            $limit = ($limit > 0) ? $offset.','.$limit : $offset.',1000';
        } else {
            $limit = '';
        }

        $where .= $this->handleEnableFieldsOptions($arr, $tableName, $tableAlias);

        // Das sollte wegfallen. Die OL werden weiter unten geladen
        if (strlen($i18n) > 0) {
            $i18n = implode(',', Strings::intExplode(',', $i18n));
            $where .= ' AND '.($tableAlias ? $tableAlias : $tableName).'.sys_language_uid IN ('.$i18n.')';
        }

        if (strlen($pidList) > 0) {
            $where .= ' AND '.($tableAlias ? $tableAlias : $tableName).'.pid'.
                ' IN ('.Misc::getPidList($pidList, $recursive).')';
        }

        if (strlen($union) > 0) {
            $where .= ' UNION '.$union;
        }

        $database = $this->getDatabaseConnection($arr);
        if ($debug || $sqlOnly) {
            $sql = $database->SELECTquery($what, $fromClause, $where, $groupBy, $orderBy, $limit);
            if ($sqlOnly) {
                return $sql;
            }
            if ($debug) {
                Debug::debug($sql, 'SQL');
                Debug::debug([$what, $from, $arr], 'Parts');
            }
        }

        $storeLastBuiltQuery = $database->store_lastBuiltQuery;
        $database->store_lastBuiltQuery = true;
        $res = $this->watchOutDB(
            $database->exec_SELECTquery(
                $what,
                $fromClause,
                $where,
                $groupBy,
                $orderBy,
                $limit
            ),
            $database
        );
        $database->store_lastBuiltQuery = $storeLastBuiltQuery;

        // use classic arrays or the collection
        // should be ever an collection, but for backward compatibility is this an array by default
        $rows = $this->initRows($arr);
        if ($this->testResource($res)) {
            $wrapper = is_string($arr['wrapperclass'] ?? null) ? trim($arr['wrapperclass']) : 0;
            $callback = isset($arr['callback']) ? $arr['callback'] : false;

            while ($row = $database->sql_fetch_assoc($res)) {
                $this->appendRow($rows, $row, $tableName, $wrapper, $callback, $arr);
            }

            $database->sql_free_result($res);
        }

        return $rows;
    }

    private function appendRow(&$rows, $row, $tableName, $wrapper, $callback, $arr)
    {
        // Workspacesupport
        $this->lookupWorkspace($row, $tableName, $arr);
        $this->lookupLanguage($row, $tableName, $arr);
        if (!is_array($row)) {
            return;
        }
        $item = ($wrapper) ? tx_rnbase::makeInstance($wrapper, $row) : $row;
        if ($item instanceof DynamicTableInterface
            // @TODO: backward compatibility for old models will be removed soon
            || $item instanceof BaseModel
        ) {
            $item->setTablename($tableName);
        }
        if ($callback) {
            call_user_func($callback, $item);
            unset($item);
        } else {
            if (is_array($rows)) {
                $rows[] = $item;
            } else {
                $rows->append($item);
            }
        }
    }

    private function initRows(array $options)
    {
        $rows = [];
        if ($options['collection'] ?? '') {
            if (!is_string($options['collection']) || !class_exists($options['collection'])) {
                $options['collection'] = BaseCollection::class;
            }
            $rows = tx_rnbase::makeInstance(
                $options['collection'],
                $rows
            );
        }

        return $rows;
    }

    /**
     * The ressourc has to be a doctrine statement, a valid ressource or an mysqli instance.
     *
     * @param mixed $res
     *
     * @return bool
     */
    private function testResource($res)
    {
        return
            is_a($res, 'Doctrine\\DBAL\\Result') ||
            // the new doctrine statemant since typo3 8
            is_a($res, 'Doctrine\\DBAL\\Driver\\Statement') ||
            // the old mysqli ressources
            is_a($res, 'mysqli_result') ||
            // the very, very old mysql ressources
            is_resource($res)
        ;
    }

    /**
     * Check for workspace overlays.
     *
     * @param array $row
     */
    private function lookupWorkspace(&$row, $tableName, $options)
    {
        if (($options['enablefieldsoff'] ?? false) || ($options['ignoreworkspace'] ?? false)) {
            return;
        }

        $sysPage = TYPO3::getSysPage();
        $sysPage->versionOL($tableName, $row);
        if (!TYPO3::isTYPO115OrHigher()) {
            $sysPage->fixVersioningPid($tableName, $row);
        }
    }

    /**
     * Autotranslate a record to fe language.
     *
     * @param array  $row
     * @param string $tableName
     * @param array  $options
     */
    private function lookupLanguage(&$row, $tableName, $options)
    {
        // ACHTUNG: Bei Aufruf im BE führt das zu einem Fehler in TCE-Formularen. Die
        // Initialisierung der TSFE ändert den backPath im PageRender auf einen falschen
        // Wert. Dadurch werden JS-Dateien nicht mehr geladen.
        // Ist dieser Aufruf im BE überhaupt sinnvoll?
        if (
            (
                !Environment::isFrontend() ||
                ($options['enablefieldsoff'] ?? false) ||
                ($options['ignorei18n'] ?? false)
            ) &&
            empty($options['forcei18n'] ?? false)
        ) {
            return;
        }

        // Then get localization of record:
        // (if the content language is not the default language)
        $tsfe = TYPO3::getTSFE();
        if (!is_object($tsfe) || !\Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe)) {
            return;
        }

        $OLmode = ($options['i18nolmode'] ?? '');
        $sysPage = TYPO3::getSysPage();

        if ('pages' === $tableName) {
            $row = $sysPage->getPageOverlay($row);
        } else {
            $row = $sysPage->getRecordOverlay(
                $tableName,
                $row,
                \Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe),
                $OLmode
            );
        }
    }

    /**
     * Returns the where for the enablefields of the table.
     *
     * @param string $tableName
     * @param string $mode
     * @param string $tableAlias
     *
     * @return mixed|string
     */
    public function enableFields($tableName, $mode, $tableAlias = '')
    {
        $sysPage = TYPO3::getSysPage();
        $enableFields = $sysPage->enableFields($tableName, $mode);
        if ($tableAlias) {
            // Replace tablename with alias
            $enableFields = str_replace($tableName, $tableAlias, $enableFields);
        }

        return $enableFields;
    }

    /**
     * Returns the database connection.
     *
     * @param array|string $options
     *
     * @return IDatabase
     *
     * @throws \Sys25\RnBase\Typo3Wrapper\Core\Error\Exception
     */
    public function getDatabaseConnection($options = null)
    {
        $dbKey = is_string($options) ? $options : 'typo3';
        $db = null;

        if (is_array($options) && !empty($options['db'] ?? [])) {
            $dbConfig = &$options['db'];
            if (is_string($dbConfig)) {
                $dbKey = $dbConfig;
            } elseif (is_object($dbConfig)) {
                $db = $dbConfig;
            }
        }

        // use the doctrine dbal connection instead of $GLOBALS['TYPO3_DB']
        if ('typo3' == $dbKey && TYPO3::isTYPO87OrHigher()) {
            $dbKey = 'typo3dbal';
        }

        if (null === $db) {
            $db = $this->getDatabase($dbKey);
        }

        if (!$db instanceof IDatabase) {
            throw tx_rnbase::makeInstance(\Sys25\RnBase\Typo3Wrapper\Core\Error\Exception::class, sprintf('The db "%s" has to implement the %s interface', get_class($db), IDatabase::class));
        }

        return $db;
    }

    /**
     * Returns the database instance.
     *
     * @param string $key Database identifier defined in localconf.php. Always in lowercase!
     *
     * @return IDatabase
     */
    protected function getDatabase($key = 'typo3')
    {
        $key = strtolower($key);
        if ('typo3' == $key) {
            $db = tx_rnbase::makeInstance(TYPO3Database::class);
        } elseif ('typo3dbal' == $key) {
            $db = tx_rnbase::makeInstance(TYPO3DBAL::class);
        } else {
            $dbCfg = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rn_base']['db'][$key];
            if (!is_array($dbCfg)) {
                throw tx_rnbase::makeInstance(DatabaseException::class, 'No config for database '.$key.' found!');
            }
            $db = tx_rnbase::makeInstance(MySqlDatabase::class, $dbCfg);
        }

        return $db;
    }

    /**
     * Make a SQL INSERT Statement.
     *
     * @param string    $tablename
     * @param array     $values
     * @param int|array $debug
     *
     * @return int UID of created record
     */
    public function doInsert($tablename, $values, $arr = [])
    {
        // fallback, $arr war früher $debug
        if (!is_array($arr)) {
            $arr = ['debug' => $arr];
        }
        $debug = intval($arr['debug'] ?? 0) > 0;

        $database = $this->getDatabaseConnection($arr);

        Misc::callHook(
            'rn_base',
            'util_db_do_insert_pre',
            [
                'tablename' => &$tablename,
                'values' => &$values,
                'options' => &$arr,
            ]
        );

        if ($debug || !empty($arr['sqlonly'] ?? false)) {
            $sqlQuery = $database->INSERTquery($tablename, $values);
            if (!empty($arr['sqlonly'] ?? false)) {
                return $sqlQuery;
            }
            $time = microtime(true);
            $mem = memory_get_usage();
        }

        $storeLastBuiltQuery = $database->store_lastBuiltQuery;
        $database->store_lastBuiltQuery = true;
        $this->watchOutDB(
            $database->exec_INSERTquery(
                $tablename,
                $values
            ),
            $database
        );
        $database->store_lastBuiltQuery = $storeLastBuiltQuery;

        if ($debug) {
            Debug::debug([
                'SQL ' => $sqlQuery,
                'Time ' => (microtime(true) - $time),
                'Memory consumed ' => (memory_get_usage() - $mem),
            ], 'SQL statistics');
        }

        $insertId = $database->sql_insert_id();

        Misc::callHook(
            'rn_base',
            'util_db_do_insert_post',
            [
                'tablename' => &$tablename,
                'uid' => &$insertId,
                'values' => &$values,
                'options' => &$arr,
            ]
        );

        return $insertId;
    }

    /**
     * Make a plain SQL Query.
     * Notice: The db resource is not closed by this method. The caller is in charge to do this!
     *
     * @param string $sqlQuery
     * @param int    $debug
     *
     * @return bool
     */
    public function doQuery($sqlQuery, array $options = [])
    {
        $debug = $options['debug'] ?? false;
        $database = $this->getDatabaseConnection($options);
        if ($debug) {
            $time = microtime(true);
            $mem = memory_get_usage();
        }

        $storeLastBuiltQuery = $database->store_lastBuiltQuery;
        $database->store_lastBuiltQuery = true;
        $res = $this->watchOutDB(
            $database->sql_query($sqlQuery)
        );
        $database->store_lastBuiltQuery = $storeLastBuiltQuery;

        if ($debug) {
            Debug::debug([
                'SQL ' => $sqlQuery,
                'Time ' => (microtime(true) - $time),
                'Memory consumed ' => (memory_get_usage() - $mem),
            ], 'SQL statistics');
        }

        return $res;
    }

    /**
     * Make a database UPDATE.
     *
     * @param string $tablename
     * @param string $where
     * @param array  $values
     * @param array  $arr
     * @param mixed  $noQuoteFields Array or commaseparated string with fieldnames
     *
     * @return int number of rows affected
     */
    public function doUpdate($tablename, $where, $values, $arr = [], $noQuoteFields = false)
    {
        // fallback, $arr war früher $debug
        if (!is_array($arr)) {
            $arr = ['debug' => $arr];
        }
        $debug = intval($arr['debug'] ?? 0) > 0;
        $database = $this->getDatabaseConnection($arr);

        Misc::callHook(
            'rn_base',
            'util_db_do_update_pre',
            [
                'tablename' => &$tablename,
                'where' => &$where,
                'values' => &$values,
                'options' => &$arr,
                'noQuoteFields' => &$noQuoteFields,
            ]
        );

        if ($debug || !empty($arr['sqlonly'])) {
            $sql = $database->UPDATEquery($tablename, $where, $values, $noQuoteFields);
            if (!empty($arr['sqlonly'])) {
                return $sql;
            }
            Debug::debug($sql, 'SQL');
            Debug::debug([$tablename, $where, $values]);
        }

        $storeLastBuiltQuery = $database->store_lastBuiltQuery;
        $database->store_lastBuiltQuery = true;
        $this->watchOutDB(
            $database->exec_UPDATEquery(
                $tablename,
                $where,
                $values,
                $noQuoteFields
            ),
            $database
        );
        $database->store_lastBuiltQuery = $storeLastBuiltQuery;

        $affectedRows = $database->sql_affected_rows();

        Misc::callHook(
            'rn_base',
            'util_db_do_update_post',
            [
                'tablename' => &$tablename,
                'where' => &$where,
                'values' => &$values,
                'affectedRows' => &$affectedRows,
                'options' => &$arr,
                'noQuoteFields' => &$noQuoteFields,
            ]
        );

        return $affectedRows;
    }

    /**
     * Make a database DELETE.
     *
     * @param string $tablename
     * @param string $where
     * @param array  $arr
     *
     * @return int number of rows affected
     */
    public function doDelete($tablename, $where, $arr = [])
    {
        // fallback, $arr war früher $debug
        if (!is_array($arr)) {
            $arr = ['debug' => $arr];
        }
        $debug = intval($arr['debug'] ?? 0) > 0;
        $database = $this->getDatabaseConnection($arr);

        Misc::callHook(
            'rn_base',
            'util_db_do_delete_pre',
            [
                'tablename' => &$tablename,
                'where' => &$where,
                'options' => &$arr,
            ]
        );

        if ($debug || !empty($arr['sqlonly'] ?? false)) {
            $sql = $database->DELETEquery($tablename, $where);
            if (!empty($arr['sqlonly'] ?? false)) {
                return $sql;
            }
            Debug::debug($sql, 'SQL');
            Debug::debug([$tablename, $where]);
        }

        $storeLastBuiltQuery = $database->store_lastBuiltQuery;
        $database->store_lastBuiltQuery = true;
        $this->watchOutDB(
            $database->exec_DELETEquery(
                $tablename,
                $where
            ),
            $database
        );
        $database->store_lastBuiltQuery = $storeLastBuiltQuery;

        $affectedRows = $database->sql_affected_rows();

        Misc::callHook(
            'rn_base',
            'util_db_do_delete_post',
            [
                'tablename' => &$tablename,
                'where' => &$where,
                'affectedRows' => &$affectedRows,
                'options' => &$arr,
            ]
        );

        return $affectedRows;
    }

    /**
     * Escaping and quoting values for SQL statements.
     *
     * @param string $str       Input string
     * @param string $table     Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     * @param bool   $allowNull Whether to allow NULL values
     *
     * @return string Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     */
    public function fullQuoteStr($str, $table = '', $allowNull = false)
    {
        return LegacyQueryBuilder::instance()->fullQuoteStr($str, $table, $allowNull);
    }

    /**
     * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
     *
     * @param array      $arr       Array with values (either associative or non-associative array)
     * @param string     $table     Table name for which to quote
     * @param bool|array $noQuote   List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
     * @param bool       $allowNull Whether to allow NULL values
     *
     * @return array The input array with the values quoted
     */
    public function fullQuoteArray($arr, $table = '', $noQuote = false, $allowNull = false)
    {
        return LegacyQueryBuilder::instance()->fullQuoteArray($arr, $table, $noQuote, $allowNull);
    }

    /**
     * Substitution for PHP function "addslashes()"
     * Use this function instead of the PHP addslashes() function when you build queries - this will prepare your code for DBAL.
     * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible. Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
     *
     * @param string $str   Input string
     * @param string $table Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     *
     * @return string Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     */
    public function quoteStr($str, $table = '')
    {
        return LegacyQueryBuilder::instance()->quoteStr($str, $table);
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
        return LegacyQueryBuilder::instance()->escapeStrForLike($str, $table);
    }

    /**
     * Returns an array with column names of a TCA defined table.
     *
     * @param string $tcaTableName
     * @param string $prefix       if set, each columnname is preceded by this alias
     *
     * @return array
     */
    public function getColumnNames($tcaTableName, $prefix = '')
    {
        $cols = $this->getTCAColumns($tcaTableName);
        if (is_array($cols)) {
            $cols = array_keys($cols);
            if (strlen(trim($prefix))) {
                array_walk($cols, function (&$item) use ($prefix) {
                    $item = $prefix.'.'.$item;
                });
            }
        } else {
            $cols = [];
        }

        return $cols;
    }

    /**
     * Liefert die TCA-Definition der in der Tabelle definierten Spalten.
     *
     * @param string $tcaTableName
     *
     * @return array or 0
     */
    public function getTCAColumns($tcaTableName)
    {
        global $TCA;
        TCA::loadTCA($tcaTableName);

        return isset($TCA[$tcaTableName]) ? $TCA[$tcaTableName]['columns'] : 0;
    }

    /**
     * Liefert eine initialisierte TCEmain.
     */
    public function &getTCEmain($data = 0, $cmd = 0)
    {
        // Die TCEmain laden
        $tce = tx_rnbase::makeInstance(Typo3Classes::getDataHandlerClass());
        $tce->stripslashes_values = 0;
        // Wenn wir ein data-Array bekommen verwenden wir das
        $tce->start($data ? $data : [], $cmd ? $cmd : []);

        // set default TCA values specific for the user
        $TCAdefaultOverride = TYPO3::isTYPO95OrHigher() ?
            (TYPO3::getBEUser()->getTSConfig('TCAdefaults')['properties'] ?? null) :
            (TYPO3::getBEUser()->getTSConfigProp('TCAdefaults') ?? null)
        ;
        if (is_array($TCAdefaultOverride)) {
            $tce->setDefaultsFromUserTS($TCAdefaultOverride);
        }

        return $tce;
    }

    /**
     * Get record with uid from table.
     *
     * @param string $tableName
     * @param int    $uid
     */
    public function getRecord($tableName, $uid, $options = [])
    {
        if (!is_array($options)) {
            $options = [];
        }
        $options['where'] = 'uid='.intval($uid);
        if (!is_array($GLOBALS['TCA']) || !array_key_exists($tableName, $GLOBALS['TCA'])) {
            $options['enablefieldsoff'] = 1;
        }
        $result = $this->doSelect('*', $tableName, $options);

        return count($result) > 0 ? $result[0] : [];
    }

    /**
     * Same method as Typo3Classes::getTypoScriptFrontendControllerClass()::pi_getPidList()
     * If you  need this functionality use Sys25\RnBase\Utility\Misc::getPidList().
     *
     * @deprecated use Sys25\RnBase\Utility\Misc::getPidList!
     */
    public function _getPidList($pid_list, $recursive = 0)
    {
        return Misc::getPidList($pid_list, $recursive);
    }

    /**
     * Check whether the given resource is a valid sql result. Breaks with mayday if not!
     * This method is taken from the great ameos_formidable extension.
     *
     * @param mixed $res
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function watchOutDB($res, $database = null)
    {
        if (!is_object($database)) {
            $database = $this->getDatabaseConnection();
        }
        if (TYPO3::isTYPO121OrHigher()) {
            return $res;
        }
        $error = $database->sql_error();
        // When PDO is used as driver sql_error() will always return an not empty array. If actually no error
        // happened the error code (first array element) will be "00000" (\PDO::ERR_NONE)
        // @todo this check should only be done when Doctrine\DBAL\Driver\PDO\MySQL\Driver is used as driver
        // but there is no way to get that information in the moment so we determine this by $error being an array (PDO)
        // or not (native mysqli).
        // Sidenote: PDO will be used by default when the database connection is configured through a URL instead
        // of providing user, host, password and database separately even if the driver is set to mysqli.
        $hasError = is_array($error) ? (\PDO::ERR_NONE !== $error[0]) : !empty($error);
        if (!$this->testResource($res) && $hasError) {
            $msg = 'SQL QUERY IS NOT VALID';
            $msg .= '<br/>';
            $msg .= '<b>'.is_array($error) ? print_r($error, true) : $error.'</b>';
            $msg .= '<br />';
            $msg .= $database->debug_lastBuiltQuery;
            // We need to pass the extKey, otherwise no devlog was written.
            Misc::mayday(nl2br($msg), 'rn_base');
        }

        return $res;
    }

    /**
     * Generates a search where clause based on the input search words (AND operation - all search words must be found in record.)
     * Example: The $sw is "content management, system" (from an input form) and the $searchFieldList is "bodytext,header" then the
     * output will be ' (bodytext LIKE "%content%" OR header LIKE "%content%") AND (bodytext LIKE "%management%" OR header LIKE "%management%") AND (bodytext LIKE "%system%" OR header LIKE "%system%")'.
     *
     * METHOD FROM tslib_content
     *
     * @param string $sw              The search words. These will be separated by space and comma.
     * @param string $searchFieldList The fields to search in
     * @param string $operator        'LIKE' oder 'FIND_IN_SET'
     * @param string $searchTable     The table name you search in (recommended for DBAL compliance. Will be prepended field names as well)
     *
     * @return string the WHERE clause
     */
    public function searchWhere($sw, $searchFieldList, $operator = 'LIKE')
    {
        $where = '';
        if ('' !== $sw) {
            $searchFields = explode(',', $searchFieldList);
            $kw = preg_split('/[ ,]/', $sw);
            if ('LIKE' == $operator) {
                $where = $this->_getSearchLike($kw, $searchFields);
            } elseif ('OP_LIKE_CONST' == $operator) {
                $kw = [$sw];
                $where = $this->_getSearchLike($kw, $searchFields);
            } elseif ('FIND_IN_SET_OR' == $operator) {
                $where = $this->_getSearchSetOr($kw, $searchFields);
            } else {
                $where = $this->_getSearchOr($kw, $searchFields, $operator);
            }
        }

        return $where;
    }

    private function _getSearchOr($kw, $searchFields, $operator)
    {
        $where = '';
        $where_p = [];
        foreach ($kw as $val) {
            $val = trim($val);
            if (!strlen($val)) {
                continue;
            }
            foreach ($searchFields as $field) {
                list($tableAlias, $col) = explode('.', $field); // Split alias and column
                $wherePart = $this->setSingleWhereField($tableAlias, $operator, $col, $val);
                if ('' !== trim($wherePart)) {
                    $where_p[] = $wherePart;
                }
            }
        }
        if (count($where_p)) {
            $where .= ' ('.implode('OR ', $where_p).')';
        }

        return $where;
    }

    /**
     * @param array $kw
     * @param array $searchFields
     */
    private function _getSearchSetOr($kw, $searchFields)
    {
        $searchTable = '';
        // Aus den searchFields muss eine Tabelle geholt werden (Erstmal nur DBAL)
        if (TYPO3::isExtLoaded('dbal') && is_array($searchFields) && !empty($searchFields)) {
            $col = $searchFields[0];
            list($searchTable, $col) = explode('.', $col);
        }

        // Hier werden alle Felder und Werte mit OR verbunden
        // (FIND_IN_SET(1, match.player)) AND (FIND_IN_SET(4, match.player))
        // (FIND_IN_SET(1, match.player) OR FIND_IN_SET(4, match.player))
        $where = '';
        $where_p = [];
        foreach ($kw as $val) {
            $val = trim($val);
            if (!strlen($val)) {
                continue;
            }
            $val = $this->escapeStrForLike($this->quoteStr($val, $searchTable), $searchTable);
            foreach ($searchFields as $field) {
                $where_p[] = 'FIND_IN_SET(\''.$val.'\', '.$field.')';
            }
        }
        if (count($where_p)) {
            $where .= ' ('.implode(' OR ', $where_p).')';
        }

        return $where;
    }

    /**
     * Create a where condition for string search in different database tables and columns.
     *
     * @param array $kw
     * @param array $searchFields
     */
    private function _getSearchLike($kw, $searchFields)
    {
        $searchTable = ''; // Für TYPO3 nicht relevant
        if (TYPO3::isExtLoaded('dbal')) {
            // Bei dbal darf die Tabelle nicht leer sein. Wir setzen die erste Tabelle in den searchfields
            $col = $searchFields[0];
            list($searchTable, $col) = explode('.', $col);
        }
        $wheres = [];
        foreach ($kw as $val) {
            $val = trim($val);
            $where_p = [];
            if (strlen($val) >= 2) {
                $val = $this->escapeStrForLike($this->quoteStr($val, $searchTable), $searchTable);
                foreach ($searchFields as $field) {
                    $where_p[] = $field.' LIKE \'%'.$val.'%\'';
                }
            }
            if (count($where_p)) {
                $wheres[] = ' ('.implode(' OR ', $where_p).')';
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
     * @param string $operator   operator constant
     * @param string $col        name of column
     * @param string $value      value to compare to
     *
     * @deprecated moved to ConditionBuilder
     */
    public function setSingleWhereField($tableAlias, $operator, $col, $value)
    {
        $where = '';
        switch ($operator) {
            case OP_NOTIN_INT:
            case OP_IN_INT:
                $value = implode(',', Strings::intExplode(',', $value));
                $where .= $tableAlias.'.'.strtolower($col).' '.$operator.' ('.$value.')';

                break;
            case OP_NOTIN:
            case OP_IN:
                $values = Strings::trimExplode(',', $value);
                for ($i = 0, $cnt = count($values); $i < $cnt; ++$i) {
                    $values[$i] = $this->fullQuoteStr($values[$i], $tableAlias);
                }
                $value = implode(',', $values);
                $where .= $tableAlias.'.'.strtolower($col).' '.(OP_IN == $operator ? 'IN' : 'NOT IN').' ('.$value.')';

                break;
            case OP_NOTIN_SQL:
            case OP_IN_SQL:
                $where .= $tableAlias.'.'.strtolower($col).' '.(OP_IN_SQL == $operator ? 'IN' : 'NOT IN').' ('.$value.')';

                break;
            case OP_INSET_INT:
                // Values splitten und einzelne Abfragen mit OR verbinden
                $where = $this->searchWhere($value, $tableAlias.'.'.strtolower($col), 'FIND_IN_SET_OR');

                break;
            case OP_EQ:
                $where .= $tableAlias.'.'.strtolower($col).' = '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_NOTEQ:
                $where .= $tableAlias.'.'.strtolower($col).' != '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_LT:
                $where .= $tableAlias.'.'.strtolower($col).' < '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_LTEQ:
                $where .= $tableAlias.'.'.strtolower($col).' <= '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_GT:
                $where .= $tableAlias.'.'.strtolower($col).' > '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_GTEQ:
                $where .= $tableAlias.'.'.strtolower($col).' >= '.$this->fullQuoteStr($value, $tableAlias);

                break;
            case OP_EQ_INT:
            case OP_NOTEQ_INT:
            case OP_GT_INT:
            case OP_LT_INT:
            case OP_GTEQ_INT:
            case OP_LTEQ_INT:
                $where .= $tableAlias.'.'.strtolower($col).' '.$operator.' '.intval($value);

                break;
            case OP_EQ_NOCASE:
                $where .= 'lower('.$tableAlias.'.'.strtolower($col).') = lower('.$this->fullQuoteStr($value, $tableAlias).')';

                break;
            case OP_LIKE:
                // Stringvergleich mit LIKE
                $where .= $this->searchWhere($value, $tableAlias.'.'.strtolower($col));

                break;
            case OP_LIKE_CONST:
                $where .= $this->searchWhere($value, $tableAlias.'.'.strtolower($col), OP_LIKE_CONST);

                break;
            default:
                Misc::mayday('Unknown Operator for comparation defined: '.$operator);
        }

        return $where.' ';
    }

    /**
     * Format a MySQL-DATE (ISO-Date) into mm-dd-YYYY.
     *
     * @param string $date Format: yyyy-mm-dd
     *
     * @return string Format mm-dd-YYYY or empty string, if $date is not valid
     */
    public function date_mysql2mdY($date)
    {
        if (strlen($date) < 2) {
            return '';
        }
        list($year, $month, $day) = explode('-', $date);

        return sprintf('%02d%02d%04d', $day, $month, $year);
    }

    /**
     * Format a MySQL-DATE (ISO-Date) into dd-mm-YYYY.
     *
     * @param string $date Format: yyyy-mm-dd
     *
     * @return string Format dd-mm-yyyy or empty string, if $date is not valid
     */
    public function date_mysql2dmY($date)
    {
        if (strlen($date) < 2) {
            return '';
        }
        list($year, $month, $day) = explode('-', $date);

        return sprintf('%02d-%02d-%04d', $day, $month, $year);
    }

    /**
     * @param array  $options
     * @param string $tableName
     * @param string $tableAlias
     *
     * @return string
     */
    public function handleEnableFieldsOptions(array $options, $tableName, $tableAlias)
    {
        $enableFields = '';

        if (!($options['enablefieldsoff'] ?? false)) {
            if (is_object($GLOBALS['BE_USER'] ?? null) &&
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] &&
                !isset($options['enablefieldsfe'])
            ) {
                $options['enablefieldsbe'] = 1;
                if (Environment::isFrontend()) {
                    // wir nehmen nicht Sys25\RnBase\Utility\TYPO3::getTSFE()->set_no_cache weil das durch
                    // $GLOBALS['TYPO3_CONF_VARS']['FE']['disableNoCacheParameter'] deaktiviert werden
                    // kann. Das wollen wir aber nicht. Der Cache muss in jedem Fall deaktiviert werden.
                    // Ansonsten könnten darin Dinge landen, die normale Nutzer nicht
                    // sehen dürfen.
                    TYPO3::getTSFE()->no_cache = true;
                }
            }

            // Zur Where-Clause noch die gültigen Felder hinzufügen
            $sysPage = TYPO3::getSysPage();
            $mode = Environment::isBackend() ? 1 : 0;
            $ignoreArr = [];
            if (intval($options['enablefieldsbe'] ?? 0)) {
                $mode = 1;
                // Im BE alle sonstigen Enable-Fields ignorieren
                $ignoreArr = ['starttime' => 1, 'endtime' => 1, 'fe_group' => 1];
            } elseif (intval($options['enablefieldsfe'] ?? 0)) {
                $mode = 0;
            }
            // Workspaces: Bei Tabellen mit Workspace-Support werden die EnableFields automatisch reduziert. Die Extension
            // Muss aus dem ResultSet ggf. Datensätze entfernen.
            $enableFields = $sysPage->enableFields($tableName, $mode, $ignoreArr);
            // Wir setzen zusätzlich pid >=0, damit Version-Records nicht erscheinen
            // allerdings nur, wenn die Tabelle versionierbar ist!
            if (!empty($GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'])) {
                $enableFields .= ' AND '.$tableName.'.pid >=0';
            }
            // Replace tablename with alias
            if ($tableAlias) {
                $enableFields = str_replace($tableName, $tableAlias, $enableFields);
            }
        }

        return $enableFields;
    }
}
