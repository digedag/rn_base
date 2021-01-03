<?php

namespace Sys25\RnBase\Search;

use Sys25\RnBase\Database\Connection;
use tx_rnbase_util_Misc;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2021 Rene Nitzsche (rene@system25.de)
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
    const SEARCH_FIELD_JOINED = 'JOINED';

    // Sonderfall freie Where-Bedingung
    const SEARCH_FIELD_CUSTOM = 'CUSTOM';

    const OP_IN = 'IN STR';
    const OP_NOTIN = 'NOTIN STR';
    // IN für numerische Werte
    const OP_NOTIN_INT = 'NOT IN';
    const OP_IN_INT = 'IN';
    const OP_IN_SQL = 'IN SQL';
    const OP_NOTIN_SQL = 'NOTIN SQL';
    const OP_INSET_INT = 'FIND_IN_SET';
    const OP_LIKE = 'LIKE';
    const OP_LIKE_CONST = 'OP_LIKE_CONST';
    const OP_EQ_INT = '=';
    const OP_NOTEQ = 'OP_NOTEQ';
    const OP_NOTEQ_INT = '!=';
    const OP_EQ_NOCASE = 'OP_EQ_NOCASE';
    const OP_LT_INT = '<';
    const OP_LTEQ_INT = '<=';
    const OP_GT_INT = '>';
    const OP_GTEQ_INT = '>=';
    const OP_GT = '>STR';
    const OP_GTEQ = '>=STR';
    const OP_LT = '<STR';
    const OP_LTEQ = '<=STR';
    const OP_EQ = '=STR';

    private $useAlias;
    private $dbConnection;

    public function __construct($useAlias, Connection $dbConnection)
    {
        $this->useAlias = $useAlias;
        $this->dbConnection = $dbConnection;
    }

    public function apply(QueryBuilder $qb, array $tableAliases, $joinedFields, $customFields)
    {
        $this->applyConditions($qb, $tableAliases);
        $this->applyJoinedConditions($qb, $joinedFields);
        $this->applyCustomConditions($qb, $customFields);
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
                            tx_rnbase_util_Misc::mayday('JOINED field required data array. Check up your search config.', 'rn_base');
                        }
                        $joinedValues = array_values($joinedValues);
                        for ($i = 0, $cnt = count($joinedValues); $i < $cnt; ++$i) {
                            $wherePart = $this->setSingleWhereField(
                                $qb,
                                $this->useAlias() ? $tableAlias : $this->tableMapping[$tableAlias],
                                $operator,
                                $col,
                                $joinedValues[$i]
                            );
                            if ('' !== trim($wherePart)) {
                                $qb->andWhere($wherePart);
                            }
                        }
                    } else {
                        $wherePart = $this->setSingleWhereField(
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
     * @param array $joinedFields
     *
     * @return string
     */
    private function applyJoinedConditions(QueryBuilder $qb, $joinedFields)
    {
        // Jetzt die Freitextsuche über mehrere Felder
        if (is_array($joinedFields)) {
            foreach ($joinedFields as $joinedField) {
                // Ignore invalid queries
                if (!isset($joinedField['value']) || !isset($joinedField['operator']) ||
                    !isset($joinedField['fields']) || !$joinedField['fields']) {
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
    private function setSingleWhereField(QueryBuilder $qb, $tableAlias, $operator, $col, $value)
    {
        switch ($operator) {
            case OP_NOTIN_INT:
            case OP_IN_INT:
                $value = \Tx_Rnbase_Utility_Strings::intExplode(',', $value);
                $qb->andWhere(sprintf('%s.%s IN (%s)', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)));
                break;
            case OP_NOTIN:
            case OP_IN:
                $value = \Tx_Rnbase_Utility_Strings::trimExplode(',', $value);
                $qb->andWhere(sprintf('%s.%s IN (%s)', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)));

                break;
            case OP_NOTIN_SQL:
            case OP_IN_SQL:
                $qb->andWhere(sprintf('%s.%s %s (%s)', $tableAlias, strtolower($col),
                    OP_IN_SQL == $operator ? 'IN' : 'NOT IN', $value));

                break;
            case OP_INSET_INT:
                // Values splitten und einzelne Abfragen mit OR verbinden
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col), 'FIND_IN_SET_OR');

                break;
            case OP_EQ:
                $qb->andWhere(sprintf('%s.%s = %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_NOTEQ:
                $qb->andWhere(sprintf('%s.%s != %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_LT:
                $qb->andWhere(sprintf('%s.%s < %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_LTEQ:
                $qb->andWhere(sprintf('%s.%s <= %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_GT:
                $qb->andWhere(sprintf('%s.%s > %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_GTEQ:
                $qb->andWhere(sprintf('%s.%s >= %s', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_EQ_INT:
            case OP_NOTEQ_INT:
            case OP_GT_INT:
            case OP_LT_INT:
            case OP_GTEQ_INT:
            case OP_LTEQ_INT:
                $qb->andWhere(sprintf('%s.%s %s %s', $tableAlias, strtolower($col), $operator,
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_EQ_NOCASE:
                $qb->andWhere(sprintf('lower(%s.%s) = lower(%s)', $tableAlias, strtolower($col),
                    $qb->createNamedParameter($value, \PDO::PARAM_STR)));

                break;
            case OP_LIKE:
                // Stringvergleich mit LIKE
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col));
                break;
            case OP_LIKE_CONST:
                $this->searchWhere($qb, $value, $tableAlias.'.'.strtolower($col), OP_LIKE_CONST);

                break;
            default:
                tx_rnbase_util_Misc::mayday('Unknown Operator for comparation defined: '.$operator);
        }
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
    private function searchWhere(QueryBuilder $qb, $sw, $searchFieldList, $operator = 'LIKE')
    {
        $where = '';
        if ('' !== $sw) {
            $searchFields = explode(',', $searchFieldList);
            $kw = preg_split('/[ ,]/', $sw);
            if ('LIKE' == $operator) {
                $this->_getSearchLike($qb, $kw, $searchFields);
            } elseif ('OP_LIKE_CONST' == $operator) {
                $kw = [$sw];
                $this->_getSearchLike($qb, $kw, $searchFields);
            } elseif ('FIND_IN_SET_OR' == $operator) {
                $this->_getSearchSetOr($qb, $kw, $searchFields);
            } else {
                $where = $this->_getSearchOr($qb, $kw, $searchFields, $operator);
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
    private function _getSearchSetOr(QueryBuilder $qb, $kw, $searchFields)
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
            $qb->andWhere(implode(' OR ', $where_p));
        }
    }

    /**
     * Create a where condition for string search in different database tables and columns.
     *
     * @param array $kw
     * @param array $searchFields
     */
    private function _getSearchLike(QueryBuilder $qb, $kw, $searchFields)
    {
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
                $qb->andWhere(implode(' OR ', $where_p));
            }
        }
    }
}
