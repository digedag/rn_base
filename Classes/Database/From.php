<?php

namespace Sys25\RnBase\Database;

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

class From
{
    private $tableName;
    private $alias;
    /* @var Join[] */
    private $joins;
    private $clause;

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias ?: $this->getTableName();
    }

    /**
     * @return Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @return mixed
     */
    public function getClause()
    {
        return $this->clause;
    }

    public function __construct($tableName, $alias, array $joins = [])
    {
        $this->tableName = $tableName;
        $this->alias = $alias;
        $this->joins = $joins;
    }

    public function setClause($clause)
    {
        $this->clause = $clause;
    }

    public static function buildInstance($fromRaw): From
    {
        if ($fromRaw instanceof From) {
            return $fromRaw;
        }

        $tableName = $fromRaw;
        $joinsOrFromClause = $fromRaw;
        $tableAlias = false;

        if (is_array($fromRaw)) {
            // we have already the new assoc array!
            if (isset($fromRaw['table'])) {
                // check the required fields
                $fromRaw['alias'] = $fromRaw['alias'] ?: $tableAlias;
                $fromRaw['clause'] = $fromRaw['clause'] ?: $fromRaw['table'].($fromRaw['alias'] ? ' AS '.$fromRaw['alias'] : '');

                return $fromRaw;
            }
            // else the old array
            $tableName = $fromRaw[1];
            $joinsOrFromClause = $fromRaw[0];
            $tableAlias = isset($fromRaw[2]) && strlen(trim($fromRaw[2])) > 0 ? trim($fromRaw[2]) : $tableAlias;
        }

        $from = new From($tableName, $tableAlias, is_array($joinsOrFromClause) ? $joinsOrFromClause : []);
        if (!is_array($joinsOrFromClause)) {
            $from->setClause($joinsOrFromClause);
        }

        return $from;
    }
}
