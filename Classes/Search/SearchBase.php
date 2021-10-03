<?php

namespace Sys25\RnBase\Search;

use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Database\Query\From;
use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use tx_rnbase;
use Tx_Rnbase_Database_Connection;
use tx_rnbase_util_Misc;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2020 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Rene Nitzsche
 */
abstract class SearchBase
{
    private static $instances = [];

    private $tableMapping;

    private $generic = false;

    private $genericData;

    /**
     * Liefert eine Instanz einer konkreten Suchklasse. Der
     * Klassenname sollte aber stimmen.
     *
     * @param string $classname
     *
     * @return SearchBase
     */
    public static function getInstance($classname)
    {
        if (!isset(self::$instances[$classname])) {
            self::$instances[$classname] = tx_rnbase::makeInstance($classname);
        }

        return self::$instances[$classname];
    }

    /**
     * returns the database connection to use.
     *
     * @return Tx_Rnbase_Database_Connection
     */
    public function getDatabaseConnection()
    {
        return \Sys25\RnBase\Database\Connection::getInstance();
    }

    /**
     * Suchanfrage an die Datenbank
     * Bei den Felder findet ein Mapping auf die eigentlichen DB-Felder statt. Dadurch werden
     * SQL-Injections erschwert und es sind JOINs möglich.
     * Field-Schema: TABLEALIAS.COLNAME
     * Beispiel: TEAM.NAME, TEAM.UID.
     *
     * Options: Zusätzliche Bedingungen für Abfrage.
     * LIMIT, ORDERBY
     *
     * Sonderfall Freitextsuche über mehrere Felder:
     * Hierfür gibt es das Sonderfeld SEARCH_FIELD_JOINED. Dieses erwartet ein Array der Form
     * 'value' => 'Suchbegriff'
     * 'operator' => OP_LIKE
     * 'cols' => array(FIELD1, FIELD2,...)
     *
     * Sonderfall SQL Sub-Select:
     * Hierfür gibt es das Sonderfeld SEARCH_FIELD_CUSTOM. Dieses erwartet ein String mit dem
     * Sub-Select. Dieser wird direkt in die Query eingebunden.
     *
     * @param array $fields  Felder nach denen gesucht wird
     * @param array $options
     *
     * @return array oder int
     */
    public function search(array $fields, array $options = [])
    {
        $this->_initSearch($options);
        $tableAliases = [];
        if (isset($fields[SEARCH_FIELD_JOINED])) {
            $joinedFields = $fields[SEARCH_FIELD_JOINED];
            unset($fields[SEARCH_FIELD_JOINED]);
        }
        $customFields = null;
        if (isset($fields[SEARCH_FIELD_CUSTOM])) {
            $customFields = $fields[SEARCH_FIELD_CUSTOM];
            unset($fields[SEARCH_FIELD_CUSTOM]);
        }
        // Die normalen Suchfelder abarbeiten
        foreach ($fields as $field => $data) {
            // Tabelle und Spalte ermitteln
            list($tableAlias, $col) = explode('.', $field);
            $tableAliases[$tableAlias][$col] = $data;
        }
        // Prüfen, ob in orderby noch andere Tabellen liegen
        $orderbyArr = $options['orderby'];
        if (is_array($orderbyArr)) {
            $aliases = array_keys($orderbyArr);
            foreach ($aliases as $alias) {
                if (strstr($alias, SEARCH_FIELD_CUSTOM)) {
                    continue;
                } // CUSTOM ignorieren
                list($tableAlias, $col) = explode('.', $alias);
                if (!array_key_exists($tableAlias, $tableAliases)) {
                    $tableAliases[$tableAlias] = [];
                }
            }
        }
        if (is_array($joinedFields)) {
            foreach ($joinedFields as $key => $joinedField) {
                // Für die JOINED-Fields müssen die Tabellen gesetzt werden, damit der SQL-JOIN passt
                foreach ($joinedField['cols'] as $field) {
                    list($tableAlias, $col) = explode('.', $field);
                    if (!isset($tableAliases[$tableAlias])) {
                        $tableAliases[$tableAlias] = [];
                    }
                    $joinedFields[$key]['fields'][] = ($this->useAlias() ? $tableAlias : $this->tableMapping[$tableAlias]).'.'.strtolower($col);
                }
            }
        }
        // Deprecated: Diese Option nicht verwenden. Dafür gibt es den Hook!
        if (is_array($additionalTableAliases = $options['additionalTableAliases'])) {
            foreach ($additionalTableAliases as $additionalTableAlias) {
                if (!isset($tableAliases[$additionalTableAlias])) {
                    $tableAliases[$additionalTableAlias] = [];
                }
            }
        }

        tx_rnbase_util_Misc::callHook('rn_base', 'searchbase_handleTableMapping', [
            'tableAliases' => &$tableAliases, 'joinedFields' => &$joinedFields,
            'customFields' => &$customFields, 'options' => &$options,
            'tableMappings' => &$this->tableMapping,
        ], $this);
        $what = $this->getWhat($options, $tableAliases);
        $from = $this->getFrom($options, $tableAliases);

        $sqlOptions = $this->initSqlOptions($options);

        $where = null;
        if ($this->useQueryBuilder($tableAliases)) {
            $conditionBuilder = new \Sys25\RnBase\Search\ConditionBuilder($this->useAlias(), $this->getDatabaseConnection());
            $where = function (QueryBuilder $qb) use ($conditionBuilder, $tableAliases, $joinedFields, $customFields) {
                $conditionBuilder->apply($qb, $tableAliases, $joinedFields, $customFields);
            };
        } else {
            $where = '1=1';
            $where .= $this->buildConditions($tableAliases);
            $where .= $this->buildJoinedConditions($joinedFields);
            $where .= $this->buildCustomConditions($customFields);
            $where .= $this->setEnableFieldsForAdditionalTableAliases($tableAliases, $options);
        }

        $sqlOptions['where'] = $where;

        if (!isset($options['count']) && is_array($options['orderby'])) {
            // Aus dem Array einen String bauen
            $orderby = [];
            if (array_key_exists('RAND', $options['orderby']) && $options['orderby']['RAND']) {
                $orderby[] = 'RAND()';
            } else {
                if (array_key_exists('RAND', $options['orderby'])) {
                    unset($options['orderby']['RAND']);
                }
                foreach ($options['orderby'] as $field => $order) {
                    // free Order-Clause
                    if (strstr($field, SEARCH_FIELD_CUSTOM)) {
                        $orderby[] = $order;

                        continue;
                    }
                    list($tableAlias, $col) = explode('.', $field);
                    $tableAlias = $this->useAlias() ? $tableAlias : $this->tableMapping[$tableAlias];
                    if ($tableAlias) {
                        $orderby[] = $tableAlias.'.'.strtolower($col).' '.('DESC' == strtoupper($order) ? 'DESC' : 'ASC');
                    } else {
                        $orderby[] = $field.' '.('DESC' == strtoupper($order) ? 'DESC' : 'ASC');
                    }
                }
            }
            $sqlOptions['orderby'] = implode(',', $orderby);
        }
        if (!(isset($options['count'])) && (
                !(
                    isset($options['what']) ||
                    isset($options['groupby']) ||
                    isset($options['sqlonly'])
                ) || isset($options['forcewrapper']))) {
            // der Filter kann ebenfalls eine Klasse setzen. Diese hat Vorrang.
            $sqlOptions['wrapperclass'] = $options['wrapperclass'] ? $options['wrapperclass'] : $this->getGenericWrapperClass();
        }

        // if we have to do a count and there still is a count in the custom what
        // or there is a having or a groupby
        // so we have to wrap the query into a subquery to count the results
        if (!$options['disableCountWrap'] &&
            isset($options['count'])
            && (
                (
                    isset($options['what'])
                    && false !== strpos(strtoupper($options['what']), 'COUNT(')
                )
                || $options['groupby']
                || $options['having']
            )
        ) {
            $sqlOptions['sqlonly'] = 1;
            $queryOrBuilder = $this->getDatabaseConnection()->doSelect(
                $what,
                $from,
                $sqlOptions,
                $options['debug'] ? 1 : 0
            );

            if ($queryOrBuilder instanceof QueryBuilder) {
                return $this->countQuery($queryOrBuilder);
            } else {
                $what = 'COUNT(*) AS cnt';
                $from = '('.$queryOrBuilder.') AS COUNTWRAP';
                $sqlOptions = [
                    'enablefieldsoff' => true,
                    'sqlonly' => empty($options['sqlonly']) ? 0 : $options['sqlonly'],
                ];
            }
        }
        $result = $this->getDatabaseConnection()->doSelect(
            $what,
            $from,
            $sqlOptions,
            $options['debug'] ? 1 : 0
        );

        if (isset($options['sqlonly'])) {
            return $result;
        }
        // else:
        return isset($options['count']) ? $result[0]['cnt'] : $result;
    }

    private function countQuery(QueryBuilder $qb): int
    {
        $countQuery = sprintf('SELECT COUNT(*) AS cnt FROM (%s) AS COUNTWRAP', $qb->getSQL());
        $stmt = $this->getConnection()->executeQuery($countQuery, $qb->getParameters(), $qb->getParameterTypes());
        $result = $stmt->fetchAll();

        return $result[0]['cnt'];
    }

    private function initSqlOptions($options)
    {
        $sqlOptions = [];
        if ($options['pidlist']) {
            $sqlOptions['pidlist'] = $options['pidlist'];
        }
        if ($options['recursive']) {
            $sqlOptions['recursive'] = $options['recursive'];
        }
        if ($options['limit']) {
            $sqlOptions['limit'] = $options['limit'];
        }
        if ($options['offset']) {
            $sqlOptions['offset'] = $options['offset'];
        }
        if ($options['enablefieldsoff']) {
            $sqlOptions['enablefieldsoff'] = $options['enablefieldsoff'];
        }
        if ($options['enablefieldsbe']) {
            $sqlOptions['enablefieldsbe'] = $options['enablefieldsbe'];
        }
        if ($options['enablefieldsfe']) {
            $sqlOptions['enablefieldsfe'] = $options['enablefieldsfe'];
        }
        if ($options['groupby']) {
            $sqlOptions['groupby'] = $options['groupby'];
        }
        if ($options['having']) {
            $sqlOptions['having'] = $options['having'];
        }
        if ($options['callback']) {
            $sqlOptions['callback'] = $options['callback'];
        }
        if ($options['ignorei18n']) {
            $sqlOptions['ignorei18n'] = $options['ignorei18n'];
        }
        if ($options['i18nolmode']) {
            $sqlOptions['i18nolmode'] = $options['i18nolmode'];
        }
        if ($options['i18n']) {
            $sqlOptions['i18n'] = $options['i18n'];
        }
        if ($options['ignoreworkspace']) {
            $sqlOptions['ignoreworkspace'] = $options['ignoreworkspace'];
        }
        if ($options['sqlonly']) {
            $sqlOptions['sqlonly'] = $options['sqlonly'];
        }
        if ($options['union']) {
            $sqlOptions['union'] = $options['union'];
        }
        if ($options['collection']) {
            $sqlOptions['collection'] = $options['collection'];
        }
        if ($options['array_object']) {
            $sqlOptions['collection'] = 'ArrayObject';
        }

        return $sqlOptions;
    }

    /**
     * Freitextsuche über mehrere Felder.
     *
     * @param array $joinedFields
     *
     * @return string
     */
    private function buildJoinedConditions($joinedFields): string
    {
        $where = '';
        // Jetzt die Freitextsuche über mehrere Felder
        if (is_array($joinedFields)) {
            foreach ($joinedFields as $joinedField) {
                // Ignore invalid queries
                if (!isset($joinedField['value']) || !isset($joinedField['operator']) ||
                    !isset($joinedField['fields']) || !$joinedField['fields']) {
                    continue;
                }

                if (OP_INSET_INT == $joinedField['operator']) {
                    // Values splitten und einzelne Abfragen mit OR verbinden
                    $addWhere = $this->getDatabaseConnection()->searchWhere(
                        $joinedField['value'],
                        implode(',', $joinedField['fields']),
                        'FIND_IN_SET_OR'
                    );
                } else {
                    $addWhere = $this->getDatabaseConnection()->searchWhere(
                        $joinedField['value'],
                        implode(',', $joinedField['fields']),
                        $joinedField['operator']
                    );
                }
                if ($addWhere) {
                    $where .= ' AND '.$addWhere;
                }
            }
        }

        return $where;
    }

    private function buildCustomConditions($customFields): string
    {
        $where = '';
        if ($customFields) {
            $where .= ' AND '.$customFields;
        }

        return $where;
    }

    private function buildConditions($tableAliases): string
    {
        $where = '';
        foreach ($tableAliases as $tableAlias => $colData) {
            foreach ($colData as $col => $data) {
                foreach ($data as $operator => $value) {
                    if (is_array($value)) {
                        // There is more then one value to test against column
                        $joinedValues = $value[SEARCH_FIELD_JOINED];
                        if (!is_array($joinedValues)) {
                            Misc::mayday('JOINED field required data array. Check up your search config.', 'rn_base');
                        }
                        $joinedValues = array_values($joinedValues);
                        for ($i = 0, $cnt = count($joinedValues); $i < $cnt; ++$i) {
                            $wherePart = $this->getDatabaseConnection()->setSingleWhereField(
                                $this->useAlias() ? $tableAlias : $this->tableMapping[$tableAlias],
                                $operator,
                                $col,
                                $joinedValues[$i]
                            );
                            if ('' !== trim($wherePart)) {
                                $where .= ' AND '.$wherePart;
                            }
                        }
                    } else {
                        $wherePart = $this->getDatabaseConnection()->setSingleWhereField(
                            $this->useAlias() ? $tableAlias : $this->tableMapping[$tableAlias],
                            $operator,
                            $col,
                            $value
                        );
                        if ('' !== trim($wherePart)) {
                            $where .= ' AND '.$wherePart;
                        }
                    }
                }
            }
        }

        return $where;
    }

    /**
     * Wurden DB-Beziehungen per Options-Array übergeben.
     *
     * @return bool
     */
    protected function isGeneric()
    {
        return $this->generic;
    }

    private function setGeneric($options)
    {
        if (is_array($options)) {
            $this->generic = array_key_exists('searchdef', $options) && is_array($options['searchdef']);
            $this->genericData = $options['searchdef'];
        }
    }

    /**
     * Returns the configured basetable. If this call is not generic it returns the value
     * from getBaseTable().
     *
     * @return string
     */
    private function getGenericBaseTable()
    {
        if ($this->isGeneric()) {
            return $this->genericData['basetable'];
        }

        return $this->getBaseTable();
    }

    /**
     * Returns the configured wrapper class. If this call is not generic it returns the value
     * from getWrapperClass().
     *
     * @return string
     */
    private function getGenericWrapperClass()
    {
        if ($this->isGeneric()) {
            return $this->genericData['wrapperclass'];
        }

        return $this->getWrapperClass();
    }

    /**
     * Returns the configured basetable. If this call is not generic it returns the value.
     *
     * @return string
     */
    private function getGenericJoins($tableAliases)
    {
        $join = '';
        if ($this->isGeneric()) {
            // FIXME: how should this work and is it possible to migrate to Join Array?
            $aliasArr = $this->genericData['alias'];
            if (is_array($aliasArr)) {
                foreach ($aliasArr as $alias => $data) {
                    $makeJoin = isset($tableAliases[$alias]);
                    if (!$makeJoin && array_key_exists('joincondition', $data)) {
                        $jconds = Strings::trimExplode(',', $data['joincondition']);
                        foreach ($jconds as $jcond) {
                            $makeJoin = $makeJoin || isset($tableAliases[$jcond]);
                            if ($makeJoin) {
                                break;
                            }
                        }
                    }

                    if ($makeJoin) {
                        $join .= ' '.$data['join'];
                    }
                }
            }
        }
        $joins = $this->getJoins($tableAliases);
        if (is_array($joins)) {
            // FIXME: merge with generics
            $join = $joins;
        } else {
            $join .= $joins;
        }

        return $join ?: [];
    }

    private function useQueryBuilder($tableAliases): bool
    {
        // erstmal wird der QB genutzt, wenn die Implementierung ein Array für die Joins liefert
        return is_array($this->getJoins($tableAliases));
    }

    private function _initSearch(array $options)
    {
        $this->setGeneric($options);
        if (!is_array($this->tableMapping)) {
            $tableMapping = $this->getTableMappings();
            $tableMapping = is_array($tableMapping) ? $tableMapping : [];
            if ($this->isGeneric()) {
                $this->addGenericTableMappings($tableMapping, $options['searchdef']);
            }
            $this->tableMapping = array_merge($tableMapping, array_flip($tableMapping));
        }
    }

    /**
     * Erstellt weitere Tablemappings, die per Konfiguration definiert wurden.
     *
     * @param array $tableMapping
     * @param array $options
     */
    protected function addGenericTableMappings(&$tableMapping, $options)
    {
        $aliasArr = $options['alias'];
        if (is_array($aliasArr)) {
            foreach ($aliasArr as $alias => $data) {
                $tableMapping[$alias] = $data['table'];
            }
        }
    }

    /**
     * Kindklassen müssen ein Array bereitstellen, in denen die Aliases der
     * Tabellen zu den eigentlichen Tabellennamen gemappt werden.
     *
     * @return array(alias => tablename, ...)
     */
    abstract protected function getTableMappings();

    /**
     * Name der Basistabelle, in der gesucht wird.
     */
    abstract protected function getBaseTable();

    /**
     * Name des Alias' der Basistabelle, in der gesucht wird
     * Nicht abstract wg. Abwärts-Kompatibilität.
     */
    protected function getBaseTableAlias()
    {
        return '';
    }

    /**
     * Liefert den Alias der Basetable.
     *
     * @return string
     */
    private function getGenericBaseTableAlias()
    {
        if ($this->isGeneric()) {
            return $this->genericData['basetablealias'];
        }

        return $this->getBaseTableAlias();
    }

    /**
     * Name der Klasse, in die die Ergebnisse gemappt werden.
     *
     * @return string
     */
    abstract public function getWrapperClass();

    /**
     * Kindklassen liefern hier die notwendigen DB-Joins. Ist kein JOIN erforderlich
     * sollte ein leerer String geliefert werden.
     *
     * @param array $tableAliases
     *
     * @return string
     */
    abstract protected function getJoins($tableAliases);

    /**
     * As default the sql statement is build with tablenames. If this method returns TRUE, the aliases will
     * be used instead. But keep in mind, to use aliases for Joins too and to overwrite getBaseTableAlias()!
     *
     * @return bool
     */
    protected function useAlias()
    {
        if ($this->isGeneric()) {
            return intval($this->genericData['usealias']) > 0;
        }

        return '' !== $this->getBaseTableAlias();
    }

    protected function getWhat($options, $tableAliases)
    {
        if (isset($options['what'])) {
            // Wenn "what" gesetzt ist, dann sollte es passen...
            return $options['what'];
        }
        $distinct = isset($options['distinct']) ? 'DISTINCT ' : '';
        $rownum = isset($options['rownum']) ? ', @rownum:=@rownum+1 AS rownum ' : '';
        $table = $this->getGenericBaseTable();
        $table = $this->useAlias() ? $this->getGenericBaseTableAlias() : $table;
        $table = sprintf('`%s`', $table);
        $ret = $distinct.$table.'.*'.$rownum;
        if (isset($options['count'])) {
            $cntWhat = isset($options['distinct']) ? $table.'.uid' : '*';
            $ret = 'count('.$distinct.$cntWhat.') as cnt';
        }

        return $ret;
    }

    /**
     * Build the from part of sql statement.
     *
     * @param array $options
     * @param array $tableAliases
     *
     * @return array
     */
    protected function getFrom($options, $tableAliases)
    {
        $table = $this->getGenericBaseTable();
        if (!$table) {
            throw new Exception('SearchBase: No base table found!');
        }
        $useFromClass = true;

        $from = [$table, $table];
        if ($this->useAlias()) {
            $alias = $this->getGenericBaseTableAlias();
            // Wenn vorhanden einen Alias für die Basetable setzen
            if ($alias) {
                $from[0] .= ' AS '.$alias;
                $from[2] = $alias;
            }
        }
        // remove support for rownum
        if (isset($options['rownum'])) {
            $useFromClass = false;
            $from[0] = '(SELECT @rownum:=0) _r, '.$from[0];
        }

        $joins = $this->getGenericJoins($tableAliases);
        if (is_array($joins)) {
            $from[0] = $joins;
        } elseif (strlen($joins)) {
            $useFromClass = false;
            $from[0] .= $joins;
        }

        if ($useFromClass) {
            $from = new From($table, $alias, $joins);
        }

        return $from;
    }

    /**
     * @param array $tableAliases
     * @param array $options
     *
     * @return string
     */
    protected function setEnableFieldsForAdditionalTableAliases(array $tableAliases, array $options): string
    {
        $where = '';
        if (!$options['enableFieldsForAdditionalTableAliases']) {
            return $where;
        }

        // FIXME: keys für Optionen sind grundsätzlich klein geschrieben
        $tableAliasesToSetEnableFields = Strings::trimExplode(
            ',',
            $options['enableFieldsForAdditionalTableAliases']
        );
        foreach ($tableAliasesToSetEnableFields as $tableAliaseToSetEnableFields) {
            if (isset($tableAliases[$tableAliaseToSetEnableFields])) {
                $tableAlias = $this->useAlias() ? $tableAliaseToSetEnableFields : '';
                $where .= $this->getDatabaseConnection()->handleEnableFieldsOptions(
                    $options,
                    $this->tableMapping[$tableAliaseToSetEnableFields],
                    $tableAlias
                );
            }
        }

        return $where;
    }

    /**
     * Optionen aus der TS-Config setzen.
     *
     * @param array $options
     * @param ConfigurationInterface $configurations
     * @param string $confId         Id der TS-Config z.B. myview.options.
     */
    public static function setConfigOptions(&$options, $configurations, $confId)
    {
        $cfgOptions = $configurations->get($confId);
        if (is_array($cfgOptions)) {
            foreach ($cfgOptions as $option => $cfg) {
                // Auf einfache Option ohne Klammerung prüfen
                if ('.' != substr($option, -1)) {
                    $options[$option] = $cfg;

                    continue;
                }
                // Ohne Angaben nix zu tun
                if (!is_array($cfg)) {
                    continue;
                }

                // Zuerst den Namen der Option holen. Dieser ist immer klein
                // Beispiel orderby, count...
                $optionName = strtolower(substr($option, 0, strlen($option) - 1));

                // Hier jetzt die Implementierung für orderby. da gibt es mehr
                // Angaben als z.B. bei count.
                foreach ($cfg as $table => $data) {
                    /*
                     * was, wenn im ts etwas wie folgt angegeben ist?
                     * options.limit = 5
                     * options.limit.override = 10
                     * das führt zu php offset fehlern,
                     * da limit bereits 5 ist und deshalb kein array werden kann.
                     * der code sieht aktuell nur eines der beiden methoden vor.
                     * entweder eine zuweisung als array oder skalaren Wert.
                     * Wir ignorieren daher bereits existierende skalare Werte
                     * und schreiben eine Log, es sei denn es sind bekannte Werte
                     * wie override oder force, dann wird direkt ignoriert
                     */
                    if (isset($options[$optionName]) && !is_array($options[$optionName])) {
                        if (!in_array($optionName, ['override', 'force'])) {
                            Logger::warn(
                                'Invalid configuration for config option "'.$optionName.'".',
                                'rn_base',
                                [
                                    'option_name' => $optionName,
                                    'cfg' => $cfg,
                                ]
                            );
                        }

                        continue;
                    }
                    $tableAlias = strtoupper(substr($table, 0, strlen($table) - 1));
                    if (is_array($data) && 'searchdef.' == $option) {
                        foreach ($data as $col => $value) {
                            $options[$optionName][strtolower($tableAlias)][substr($col, 0, strlen($col) - 1)] = $value;
                        }
                    } elseif (is_array($data)) {
                        foreach ($data as $col => $value) {
                            $options[$optionName][$tableAlias.'.'.$col] = $value;
                        }
                    } else { // Ohne Array erfolgt direkt eine Ausgabe (Beispiel RAND = 1)
                        $options[$optionName][$table] = $data;
                    }
                }
            }
        }
    }

    /**
     * Felder über ein Configarray setzen.
     *
     * @param array $fields
     * @param array $cfgFields
     */
    public static function setConfigFieldsByArray(&$fields, &$cfgFields)
    {
        if (is_array($cfgFields)) {
            foreach ($cfgFields as $field => $cfg) {
                // Tabellen-Alias
                $tableAlias = ('.' == substr($field, strlen($field) - 1, 1)) ?
                    strtoupper(substr($field, 0, strlen($field) - 1)) : strtoupper($field);

                if (SEARCH_FIELD_JOINED == $tableAlias) {
                    // Hier sieht die Konfig etwas anders aus
                    foreach ($cfg as $jField) {
                        $jField['operator'] = constant($jField['operator']);
                        $jField['cols'] = Strings::trimExplode(',', $jField['cols']);
                        $fields[SEARCH_FIELD_JOINED][] = $jField;
                    }

                    continue;
                }
                if (SEARCH_FIELD_CUSTOM == $tableAlias) {
                    $fields[SEARCH_FIELD_CUSTOM] = $cfg;
                }

                // Spaltenname
                if (!is_array($cfg)) {
                    continue;
                }

                foreach ($cfg as $col => $data) {
                    $colName = strtoupper(substr($col, 0, strlen($col) - 1));
                    // Operator und Wert
                    if (!is_array($data)) {
                        continue;
                    }
                    foreach ($data as $op => $value) {
                        $fields[$tableAlias.'.'.$colName][constant($op)] = $value;
                    }
                }
            }
        }
    }

    /**
     * Vergleichsfelder aus der TS-Config setzen.
     *
     * @param array                                      $fields
     * @param ConfigurationInterface $configurations
     * @param string                                     $confId         Id der TS-Config z.B. myview.fields.
     */
    public static function setConfigFields(&$fields, $configurations, $confId)
    {
        $cfgFields = $configurations->get($confId);
        self::setConfigFieldsByArray($fields, $cfgFields);
    }

    /**
     * Checks existence of search field in parameters and adds it to fieldarray.
     *
     * @param string $idstr
     * @param array $fields
     * @param \ArrayObject $parameters
     * @param ConfigurationInterface $configurations
     * @param string $operator
     */
    public function setField($idstr, &$fields, $parameters, $configurations, $operator = OP_LIKE)
    {
        if (!isset($fields[$idstr][$operator]) && $parameters->offsetGet($idstr)) {
            $fields[$idstr][$operator] = $parameters->offsetGet($idstr);
            // Parameter als KeepVar merken
            // TODO: Ist das noch notwendig??
            $configurations->addKeepVar($configurations->createParamName($idstr), $fields[$idstr]);
        }
    }

    /**
     * Special Chars used by Charbrowser.
     *
     * @return array
     */
    public static function getSpecialChars()
    {
        $specials = [];
        $specials['0-9'] = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.', '@', ''];
        $specials['A'] = ['A', 'Ä'];
        $specials['O'] = ['O', 'Ö'];
        $specials['U'] = ['U', 'Ü'];

        return $specials;
    }

    private function getConnection(): \TYPO3\CMS\Core\Database\Connection
    {
        $pool = tx_rnbase::makeInstance(ConnectionPool::class);

        return $pool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }
}
