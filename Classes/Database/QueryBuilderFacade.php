<?php

namespace Sys25\RnBase\Database;

use Sys25\RnBase\Database\Query\From;
use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Environment;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2024 Rene Nitzsche
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

class QueryBuilderFacade
{
    public function doInsert(From $from, $arr): ?QueryBuilder
    {
        if (!empty($from->getClause()) || $from->isComplexTable()) {
            return null;
        }
        $tableName = $from->getTableName();
        $debug = intval($arr['debug'] ?? null) > 0;
        $values = $arr['values'] ?? null;

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->insert($tableName, $tableName)
            ->values($values);

        if ($debug) {
            Debug::debug($queryBuilder->getSQL(), 'SQL');
            Debug::debug(['from' => $from, 'options' => $arr, 'params' => $queryBuilder->getParameters()], 'Parts');
        }

        return $queryBuilder;
    }

    public function doUpdate(From $from, $arr): ?QueryBuilder
    {
        if (!empty($from->getClause()) || $from->isComplexTable()) {
            return null;
        }
        $joins = $from->getJoins();
        $tableName = $from->getTableName();
        $tableAlias = $from->getAlias();
        $where = isset($arr['where']) ? $arr['where'] : null;
        $debug = intval($arr['debug'] ?? null) > 0;
        $values = $arr['values'] ?? null;

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->update($tableName, $tableAlias != $tableName ? $tableAlias : null);
        foreach ($values as $col => $value) {
            $queryBuilder->set($col, $value);
        }

        foreach ($joins as $join) {
            if (Join::TYPE_INNER == $join->getType()) {
                $queryBuilder->innerJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_LEFT == $join->getType()) {
                $queryBuilder->leftJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_RIGHT == $join->getType()) {
                $queryBuilder->rightJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            }
        }

        if (is_string($where)) {
            $queryBuilder->andWhere($where);
        } elseif (is_callable($where)) {
            $where($queryBuilder);
        }

        if ($debug) {
            Debug::debug($queryBuilder->getSQL(), 'SQL');
            Debug::debug(['from' => $from, 'options' => $arr, 'params' => $queryBuilder->getParameters()], 'Parts');
        }

        return $queryBuilder;
    }

    public function doDelete(From $from, $arr): ?QueryBuilder
    {
        if (!empty($from->getClause()) || $from->isComplexTable()) {
            return null;
        }
        $joins = $from->getJoins();
        $tableName = $from->getTableName();
        $tableAlias = $from->getAlias();
        $where = isset($arr['where']) ? $arr['where'] : null;
        $debug = intval($arr['debug'] ?? null) > 0;

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->delete($tableName, $tableAlias != $tableName ? $tableAlias : null);
        foreach ($joins as $join) {
            if (Join::TYPE_INNER == $join->getType()) {
                $queryBuilder->innerJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_LEFT == $join->getType()) {
                $queryBuilder->leftJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_RIGHT == $join->getType()) {
                $queryBuilder->rightJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            }
        }

        if (is_string($where)) {
            $queryBuilder->andWhere($where);
        } elseif (is_callable($where)) {
            $where($queryBuilder);
        }

        if ($debug) {
            Debug::debug($queryBuilder->getSQL(), 'SQL');
            Debug::debug(['from' => $from, 'options' => $arr, 'params' => $queryBuilder->getParameters()], 'Parts');
        }

        return $queryBuilder;
    }

    public function doSelect($what, From $from, $arr): ?QueryBuilder
    {
        // if (isset($from['clause']) && !is_array($from['clause'])) {
        if (!empty($from->getClause()) || $from->isComplexTable()) {
            return null;
        }
        $joins = $from->getJoins();
        $tableName = $from->getTableName();
        $tableAlias = $from->getAlias();

        $where = isset($arr['where']) ? $arr['where'] : null;
        $groupBy = is_string($arr['groupby'] ?? null) ? $arr['groupby'] : '';
        $having = is_string($arr['having'] ?? null) ? $arr['having'] : '';
        $debug = intval($arr['debug'] ?? null) > 0;
        $orderBy = is_string($arr['orderby'] ?? null) ? $arr['orderby'] : '';
        $offset = (int) intval($arr['offset'] ?? null) > 0 ? $arr['offset'] : 0;
        $limit = (int) intval($arr['limit'] ?? null) > 0 ? $arr['limit'] : '';
        $pidList = (is_string($arr['pidlist'] ?? null) || is_int($arr['pidlist'] ?? null)) ? $arr['pidlist'] : '';
        $recursive = intval($arr['recursive'] ?? null) ? intval($arr['recursive']) : 0;
        // TODO: is i18n still necessary?
        // $i18n = is_string($arr['i18n']) > 0 ? $arr['i18n'] : '';
        // TODO: how to handle UNIONs?
        // $union = is_string($arr['union']) > 0 ? $arr['union'] : '';

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->selectLiteral($what) // TODO: use selectLiteral on demand only
            ->from($tableName, $tableAlias != $tableName ? $tableAlias : null);

        $this->handleEnableFieldsOptions($queryBuilder, $arr);

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }
        if ($offset) {
            $queryBuilder->setFirstResult($offset);
        }
        if ($orderBy) {
            if (!method_exists($queryBuilder, 'add')) {
                $queryBuilder->getConcreteQueryBuilder()->orderBy($orderBy);
            } else {
                $queryBuilder->add('orderBy', $orderBy);
            }
        }
        if ($groupBy) {
            $queryBuilder->getConcreteQueryBuilder()->groupBy($groupBy);
            if ($having) {
                $queryBuilder->having($having);
            }
        }
        if (strlen($pidList) > 0) {
            $pidList = Strings::intExplode(',', Misc::getPidList($pidList, $recursive));
            // is there a problem with page aliases here?
            $placeholder = $queryBuilder->createNamedParameter($pidList, self::getParamTypeIntArray());
            $queryBuilder->andWhere(sprintf('%s.pid IN (%s)', $tableAlias, $placeholder));
        }

        foreach ($joins as $join) {
            if (Join::TYPE_INNER == $join->getType()) {
                $queryBuilder->innerJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_LEFT == $join->getType()) {
                $queryBuilder->leftJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            } elseif (Join::TYPE_RIGHT == $join->getType()) {
                $queryBuilder->rightJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
            }
        }

        if (is_string($where)) {
            $queryBuilder->andWhere($where);
        } elseif (is_callable($where)) {
            $where($queryBuilder);
        }

        if ($debug) {
            Debug::debug($queryBuilder->getSQL(), 'SQL');
            Debug::debug(['what' => $what, 'from' => $from, 'options' => $arr, 'params' => $queryBuilder->getParameters()], 'Parts');
        }

        return $queryBuilder;
    }

    private function handleEnableFieldsOptions(QueryBuilder $queryBuilder, array $options)
    {
        if ($options['enablefieldsoff'] ?? false) {
            $queryBuilder->getRestrictions()->removeAll();
        } else {
            // Für Redakteure versteckte Objekte im FE einblenden
            if (is_object($GLOBALS['BE_USER'] ?? null)
                && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects']
                && !isset($options['enablefieldsfe'])
            ) {
                $options['enablefieldsbe'] = 1;
                if (Environment::isFrontend() && !TYPO3::isTYPO130OrHigher()) {
                    // wir nehmen nicht Sys25\RnBase\Utility\TYPO3::getTSFE()->set_no_cache weil das durch
                    // $GLOBALS['TYPO3_CONF_VARS']['FE']['disableNoCacheParameter'] deaktiviert werden
                    // kann. Das wollen wir aber nicht. Der Cache muss in jedem Fall deaktiviert werden.
                    // Ansonsten könnten darin Dinge landen, die normale Nutzer nicht
                    // sehen dürfen.
                    TYPO3::getTSFE()->no_cache = true;
                }
            }

            if (Environment::isFrontend()) {
                $restrictions = $queryBuilder->getRestrictions()
                    ->removeAll();
                if (intval($options['enablefieldsbe'] ?? null)) {
                    $restrictions->add(tx_rnbase::makeInstance(DeletedRestriction::class))
                        ->add(tx_rnbase::makeInstance($this->getWorkspaceRestrictionClass()));
                } else {
                    $restrictions->add(tx_rnbase::makeInstance(FrontendRestrictionContainer::class));
                }
            } else {
                $restrictions = $queryBuilder->getRestrictions()
                    ->removeAll()
                    ->add(tx_rnbase::makeInstance(DeletedRestriction::class))
                    ->add(tx_rnbase::makeInstance($this->getWorkspaceRestrictionClass()));
                if (!($options['enablefieldsbe'] ?? null)) {
                    $restrictions->add(tx_rnbase::makeInstance(HiddenRestriction::class));
                }
            }
        }
    }

    public static function getParamTypeIntArray()
    {
        return TYPO3::isTYPO121OrHigher() ? \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY : \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
    }

    public static function getParamTypeStringArray()
    {
        return TYPO3::isTYPO121OrHigher() ? \TYPO3\CMS\Core\Database\Connection::PARAM_STR_ARRAY : \Doctrine\DBAL\Connection::PARAM_STR_ARRAY;
    }

    private function getWorkspaceRestrictionClass(): string
    {
        return TYPO3::isTYPO121OrHigher() ?
            WorkspaceRestriction::class :
            \TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction::class;
    }

    private function getConnectionPool(): ConnectionPool
    {
        return tx_rnbase::makeInstance(ConnectionPool::class);
    }
}
