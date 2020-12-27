<?php

namespace Sys25\RnBase\Database;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

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


/**
 */
class QueryBuilderFacade
{
    public function doSelect($what, From $from, $arr):?QueryBuilder
    {
        // if (isset($from['clause']) && !is_array($from['clause'])) {
        if (!empty($from->getClause())) {
            return null;
        }
        $joins = $from->getJoins();
        $tableName = $from->getTableName();
        $tableAlias = $from->getAlias();

        $where = is_string($arr['where']) ? $arr['where'] : '1=1';
        $groupBy = is_string($arr['groupby']) ? $arr['groupby'] : '';
        $having = is_string($arr['having']) ? $arr['having'] : '';
        $debug = intval($arr['debug']) > 0;
        $orderBy = is_string($arr['orderby']) ? $arr['orderby'] : '';
        $offset = (int) intval($arr['offset']) > 0 ? $arr['offset'] : 0;
        $limit = (int) intval($arr['limit']) > 0 ? $arr['limit'] : '';
        $pidList = is_string($arr['pidlist']) ? $arr['pidlist'] : '';
        $recursive = intval($arr['recursive']) ? intval($arr['recursive']) : 0;
        $i18n = is_string($arr['i18n']) > 0 ? $arr['i18n'] : '';
        $union = is_string($arr['union']) > 0 ? $arr['union'] : '';

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions();
        $queryBuilder->selectLiteral($what) // TODO: use selectLiteral on demand only
            ->from($tableName, $tableAlias)
            ->where($where);
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
            $queryBuilder->addGroupBy($groupBy);
            if ($having) {
                $queryBuilder->having($having);
            }
        }

        if (strlen($pidList) > 0) {
//             $queryBuilder->andWhere( )
//             $where .= ' AND '.($tableAlias ? $tableAlias : $tableName).'.pid'.
//                 ' IN ('.\tx_rnbase_util_Misc::getPidList($pidList, $recursive).')';
        }


        foreach ($joins as $join) {
            $queryBuilder->innerJoin($tableAlias, $join->getTable(), $join->getAlias(), $join->getOnClause());
        }

        if ($debug) {
            \tx_rnbase_util_Debug::debug($queryBuilder->getSQL(), 'SQL');
            \tx_rnbase_util_Debug::debug([$what, $from, $arr], 'Parts');
        }

        return $queryBuilder;
    }

    private function getConnectionPool():ConnectionPool
    {
        return \tx_rnbase::makeInstance(ConnectionPool::class);
    }
}
