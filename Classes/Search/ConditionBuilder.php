<?php

namespace Sys25\RnBase\Search;

use Doctrine\DBAL\ParameterType;
use PDO;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Database\QueryBuilderFacade;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2025 Rene Nitzsche (rene@system25.de)
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
class ConditionBuilder
{
    // Sonderfall Freitextsuche in mehreren Feldern
    public const SEARCH_FIELD_JOINED = 'JOINED';

    // Sonderfall freie Where-Bedingung
    public const SEARCH_FIELD_CUSTOM = 'CUSTOM';

    public const OP_IN = 'IN STR';
    public const OP_NOTIN = 'NOTIN STR';
    // IN für numerische Werte
    public const OP_NOTIN_INT = 'NOT IN';
    public const OP_IN_INT = 'IN';
    public const OP_IN_SQL = 'IN SQL';
    public const OP_NOTIN_SQL = 'NOTIN SQL';
    public const OP_INSET_INT = 'FIND_IN_SET';
    public const OP_LIKE = 'LIKE';
    public const OP_LIKE_CONST = 'OP_LIKE_CONST';
    public const OP_EQ_INT = '=';
    public const OP_NOTEQ = 'OP_NOTEQ';
    public const OP_NOTEQ_INT = '!=';
    public const OP_EQ_NOCASE = 'OP_EQ_NOCASE';
    public const OP_LT_INT = '<';
    public const OP_LTEQ_INT = '<=';
    public const OP_GT_INT = '>';
    public const OP_GTEQ_INT = '>=';
    public const OP_GT = '>STR';
    public const OP_GTEQ = '>=STR';
    public const OP_LT = '<STR';
    public const OP_LTEQ = '<=STR';
    public const OP_EQ = '=STR';

    private $useAlias;
    private $dbConnection;

    private $tableMapping;

    public function __construct($useAlias, array $tableMapping, Connection $dbConnection)
    {
        $this->useAlias = $useAlias;
        $this->tableMapping = $tableMapping;
        $this->dbConnection = $dbConnection;
    }

    public function apply(QueryBuilder $qb, SearchCriteria $searchCriteria)
    {
        $this->applyConditions($qb, $searchCriteria->getTableAliases());
        $this->applyJoinedConditions($qb, $searchCriteria->getJoinedFields());
        $this->applyCustomConditions($qb, $searchCriteria->getCustomFields());
    }

    private function applyConditions(QueryBuilder $qb, array $tableAliases)
    {
        foreach ($tableAliases as $tableAlias => $colData) {
            foreach ($colData as $col => $data) {
                foreach ($data as $operator => $value) {
                    if (is_array($value)) {
                        // There is more then one value to test against column
                        $joinedValues = $value[self::SEARCH_FIELD_JOINED];
                        if (!is_array($joinedValues)) {
                            Misc::mayday('JOINED field required data array. Check up your search config.', 'rn_base');
                        }
                        $joinedValues = array_values($joinedValues);
                        for ($i = 0, $cnt = count($joinedValues); $i < $cnt; ++$i) {
                            $wherePart = $this->buildSingleWhereField(
                                $qb,
                                $this->useAlias ? $tableAlias : $this->tableMapping[$tableAlias],
                                $operator,
                                $col,
                                $joinedValues[$i]
                            );
                            if ('' !== trim($wherePart)) {
                                $qb->andWhere($wherePart);
                            }
                        }
                    } else {
                        $wherePart = $this->buildSingleWhereField(
                            $qb,
                            $this->useAlias ? $tableAlias : $this->tableMapping[$tableAlias],
                            $operator,
                            $col,
                            $value
                        );

                        if ('' !== trim($wherePart)) {
                            $qb->andWhere($wherePart);
                        }
                    }
                }
            }
        }
    }

    /**
     * Freitextsuche über mehrere Felder.
     *
     * @param QueryBuilder $qb
     * @param array $joinedFields
     */
    private function applyJoinedConditions(QueryBuilder $qb, $joinedFields)
    {
        // Jetzt die Freitextsuche über mehrere Felder
        if (is_array($joinedFields)) {
            foreach ($joinedFields as $joinedField) {
                // Ignore invalid queries
                if (!isset($joinedField['value']) || !isset($joinedField['operator'])
                    || !isset($joinedField['fields']) || !$joinedField['fields']) {
                    continue;
                }

                if (self::OP_INSET_INT == $joinedField['operator']) {
                    // Values splitten und einzelne Abfragen mit OR verbinden
                    $addWhere = $this->searchWhere(
                        $qb,
                        $joinedField['value'],
                        implode(',', $joinedField['fields']),
                        'FIND_IN_SET_OR'
                    );
                } else {
                    $addWhere = $this->searchWhere(
                        $qb,
                        $joinedField['value'],
                        implode(',', $joinedField['fields']),
                        $joinedField['operator']
                    );
                }
                if ($addWhere) {
                    $qb->andWhere($addWhere);
                }
            }
        }
    }

    private function applyCustomConditions(QueryBuilder $qb, $customFields)
    {
        if ($customFields) {
            $qb->andWhere($customFields);
        }
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
     */
    private function buildSingleWhereField(QueryBuilder $qb, $tableAlias, $operator, $col, $value)
    {
        $where = '';
        switch ($operator) {
            case self::OP_NOTIN_INT:
            case self::OP_IN_INT:
                $value = Strings::intExplode(',', $value);
                $where = sprintf('%s.%s %s (%s)', $tableAlias, strtolower($col), $operator,
                    $qb->createNamedParameter($value, QueryBuilderFacade::getParamTypeIntArray()));
                break;
            case self::OP_NOTIN:
            case self::OP_IN:
                $value = Strings::trimExplode(',', $value);
                $where = sprintf('%s.%s %s (%s)', $tableAlias, strtolower($col), $operator,
                    $qb->createNamedParameter($value, QueryBuilderFacade::getParamTypeStringArray()));
                break;
            case self::OP_NOTIN_SQL:
            case self::OP_IN_SQL:
                $where = sprintf('%s.%s %s (%s)', $tableAlias, strtolower($col),
                    OP_IN_SQL == $operator ? 'IN' : 'NOT IN', $value);
                break;
            case self::OP_EQ:
                $where = sprintf('%s.%s = %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_NOTEQ:
                $where = sprintf('%s.%s != %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_LT:
                $where = sprintf('%s.%s < %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_LTEQ:
                $where = sprintf('%s.%s <= %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_GT:
                $where = sprintf('%s.%s > %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_GTEQ:
                $where = sprintf('%s.%s >= %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_EQ_INT:
            case self::OP_NOTEQ_INT:
            case self::OP_GT_INT:
            case self::OP_LT_INT:
            case self::OP_GTEQ_INT:
            case self::OP_LTEQ_INT:
                $where = sprintf('%s.%s %s %s', $tableAlias, strtolower($col), $operator,
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::INTEGER : PDO::PARAM_STR));

                break;
            case self::OP_EQ_NOCASE:
                $where = sprintf('lower(%s.%s) = lower(%s)', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, TYPO3::isTYPO121OrHigher() ? ParameterType::STRING : PDO::PARAM_STR));

                break;
            case self::OP_INSET_INT:
                // Values splitten und einzelne Abfragen mit OR verbinden
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col), 'FIND_IN_SET_OR');

                break;
            case self::OP_LIKE:
                // Stringvergleich mit LIKE
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col));
                break;
            case self::OP_LIKE_CONST:
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col), OP_LIKE_CONST);

                break;
            default:
                Misc::mayday('Unknown Operator for comparation defined: '.$operator);
        }

        return $where;
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
    private function searchWhere(QueryBuilder $qb, $sw, $searchFieldList, $operator = 'LIKE'): string
    {
        $where = '';
        if ('' !== $sw) {
            $searchFields = explode(',', $searchFieldList);
            $kw = preg_split('/[ ,]/', $sw);
            if (self::OP_LIKE == $operator) {
                $term = $this->_getSearchLike($qb, $kw, $searchFields);
                $qb->andWhere($term);
            } elseif (self::OP_LIKE_CONST == $operator) {
                $kw = [$sw];
                $term = $this->_getSearchLike($qb, $kw, $searchFields);
                $qb->andWhere($term);
            } elseif ('FIND_IN_SET_OR' == $operator) {
                $term = $this->_getSearchSetOr($qb, $kw, $searchFields);
                $qb->andWhere($term);
            } else {
                $term = $this->_getSearchOr($qb, $kw, $searchFields, $operator);
                $qb->andWhere($term);
            }
        }

        return $where;
    }

    private function _getSearchOr(QueryBuilder $qb, $kw, $searchFields, $operator)
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
                $wherePart = $this->buildSingleWhereField($qb, $tableAlias, $operator, $col, $val);
                if ('' !== trim($wherePart)) {
                    $where_p[] = $wherePart;
                }
            }
        }
        if (count($where_p)) {
            $where = implode(' OR ', $where_p);
        }

        return $where;
    }

    /**
     * @param array $kw
     * @param array $searchFields
     */
    private function _getSearchSetOr(QueryBuilder $qb, $kw, $searchFields): string
    {
        // Hier werden alle Felder und Werte mit OR verbunden
        // (FIND_IN_SET(1, match.player)) AND (FIND_IN_SET(4, match.player))
        // (FIND_IN_SET(1, match.player) OR FIND_IN_SET(4, match.player))
        $where_p = [];
        foreach ($kw as $val) {
            $val = trim($val);
            if (!strlen($val)) {
                continue;
            }
            $namedParam = $qb->createNamedParameter($val);
            foreach ($searchFields as $field) {
                $where_p[] = sprintf('FIND_IN_SET(%s, %s)', $namedParam, $field);
            }
        }
        if (!empty($where_p)) {
            return implode(' OR ', $where_p);
        }

        return '';
    }

    /**
     * Create a where condition for string search in different database tables and columns.
     *
     * @param array $kw
     * @param array $searchFields
     */
    private function _getSearchLike(QueryBuilder $qb, $kw, $searchFields)
    {
        $wheres = [];
        foreach ($kw as $val) {
            $val = trim($val);
            $where_p = [];
            if (strlen($val) >= 2) {
                $namedParam = $qb->createNamedParameter('%'.$val.'%');
                foreach ($searchFields as $field) {
                    $where_p[] = sprintf('%s LIKE %s', $field, $namedParam);
                }
            }
            if (!empty($where_p)) {
                $wheres[] = ' ('.implode(' OR ', $where_p).')';
            }
        }

        return !empty($wheres) ? implode(' AND ', $wheres) : '';
    }
}
