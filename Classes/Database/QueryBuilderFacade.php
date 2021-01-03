<?php

namespace Sys25\RnBase\Database;

use Sys25\RnBase\Database\Query\From;
use Sys25\RnBase\Database\Query\Join;
use tx_rnbase;
use tx_rnbase_util_TYPO3;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2021 Rene Nitzsche
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
        $groupBy = is_string($arr['groupby']) ? $arr['groupby'] : '';
        $having = is_string($arr['having']) ? $arr['having'] : '';
        $debug = intval($arr['debug']) > 0;
        $orderBy = is_string($arr['orderby']) ? $arr['orderby'] : '';
        $offset = (int) intval($arr['offset']) > 0 ? $arr['offset'] : 0;
        $limit = (int) intval($arr['limit']) > 0 ? $arr['limit'] : '';
        $pidList = is_string($arr['pidlist']) ? $arr['pidlist'] : '';
        $recursive = intval($arr['recursive']) ? intval($arr['recursive']) : 0;
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
            $queryBuilder->add('orderBy', $orderBy);
        }
        if ($groupBy) {
            $queryBuilder->getConcreteQueryBuilder()->groupBy($groupBy);
            if ($having) {
                $queryBuilder->having($having);
            }
        }
        if (strlen($pidList) > 0) {
            $pidList = \Tx_Rnbase_Utility_Strings::intExplode(',', \tx_rnbase_util_Misc::getPidList($pidList, $recursive));
            // is there a problem with page aliases here?
            $placeholder = $queryBuilder->createNamedParameter($pidList, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
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
            $queryBuilder->where($where);
        } elseif (is_callable($where)) {
            $where($queryBuilder);
        }

        if ($debug) {
            \tx_rnbase_util_Debug::debug($queryBuilder->getSQL(), 'SQL');
            \tx_rnbase_util_Debug::debug(['what' => $what, 'from' => $from, 'options' => $arr, 'params' => $queryBuilder->getParameters()], 'Parts');
        }

        return $queryBuilder;
    }

    private function handleEnableFieldsOptions(QueryBuilder $queryBuilder, array $options)
    {
        if ($options['enablefieldsoff']) {
            $queryBuilder->getRestrictions()->removeAll();
        } else {
            // Für Redakteure versteckte Objekte im FE einblenden
            if (is_object($GLOBALS['BE_USER']) &&
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects'] &&
                !isset($options['enablefieldsfe'])
            ) {
                $options['enablefieldsbe'] = 1;
                if ($this->isFrontend()) {
                    // wir nehmen nicht tx_rnbase_util_TYPO3::getTSFE()->set_no_cache weil das durch
                    // $GLOBALS['TYPO3_CONF_VARS']['FE']['disableNoCacheParameter'] deaktiviert werden
                    // kann. Das wollen wir aber nicht. Der Cache muss in jedem Fall deaktiviert werden.
                    // Ansonsten könnten darin Dinge landen, die normale Nutzer nicht
                    // sehen dürfen.
                    tx_rnbase_util_TYPO3::getTSFE()->no_cache = true;
                }
            }

            if (intval($options['enablefieldsbe'])) {
                $queryBuilder
                    ->getRestrictions()
                    ->removeAll()
                    ->add(tx_rnbase::makeInstance(DeletedRestriction::class));
            }
        }
    }

    /**
     * @return bool
     */
    private function isFrontend()
    {
        return TYPO3_MODE == 'FE';
    }

    private function getConnectionPool(): ConnectionPool
    {
        return \tx_rnbase::makeInstance(ConnectionPool::class);
    }
}
