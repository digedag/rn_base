<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2016 Rene Nitzsche
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

tx_rnbase::load('Tx_Rnbase_Database_Connection');

/**
 * methods for generating queries on a hierarchical tree structure.
 *
 * Tx_Rnbase_Database_TreeQueryBuilder
 *
 * @author            <mario.seidel> <mario.seidel@dmk-ebusines.de
 */
class Tx_Rnbase_Database_TreeQueryBuilder
{
    /**
     * returns an array of pids from a page tree.
     *
     * @param int|string $id      start page
     * @param array      $options additional options:
     *                            tableName: which table is to be used (default: pages)
     *                            depth: how many levels are descended in the page tree
     *                            (default: 999)
     *                            begin: at which level do we start (default: 0)
     *
     * @return array
     *
     * @see Tx_Rnbase_Database_TreeQueryBuilder::getTreeUidListRecursive
     */
    public function getPageTreeUidList($id, $options = [])
    {
        //@TODO: support page aliases in id parameter
        $sqlOptions = array_merge(['tableName' => 'pages'], $options);
        $depth = !empty($options['depth']) ? $options['depth'] : 999;
        $begin = !empty($options['begin']) ? $options['begin'] : 0;

        unset($sqlOptions['depth']);
        unset($sqlOptions['begin']);

        return $this->getTreeUidListRecursive($id, $depth, $begin, $sqlOptions);
    }

    /**
     * returns an array of tree-like assigned entities like a pagetree
     * but could also handle any other hierarchical db structure.
     *
     * @param int|string $id      id or list of ids comma separated
     * @param int        $depth
     * @param int        $begin
     * @param array      $options All options except "where" are forwarded to "doSelect"
     *                            directly. The parentField (pid) will be added to the where
     *                            clausle automaticly. additional options:
     *                            tableName: what table should be used (required)
     *                            parentField: the field where the parent id is stored
     *                            (default: pid)
     *                            idField: the field of the identifier that will be returned
     *                            (default: uid)
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTreeUidListRecursive($id, $depth, $begin = 0, $options = [])
    {
        $depth = (int) $depth;
        $begin = (int) $begin;
        $parentField = !empty($options['parentField']) ? $options['parentField'] : 'pid';
        $idField = !empty($options['idField']) ? $options['idField'] : 'uid';

        if (0 == $begin) {
            $uidList = Tx_Rnbase_Utility_Strings::intExplode(',', $id);
        } else {
            $uidList = [];
        }
        if ($id && $depth > 0) {
            if (empty($options['tableName'])) {
                throw new \Exception('tableName must be set in $options');
            }

            if (empty($options['where'])) {
                $options['where'] = '1=1';
            }
            $sqlOptions = $options;
            $sqlOptions['where'] .= ' AND ' . $parentField . ' IN (' . $id . ')';

            /**
             * @var Tx_Rnbase_Domain_Collection_Base
             */
            $rows = $this->getConnection()->doSelect(
                $idField,
                $sqlOptions['tableName'],
                $sqlOptions
            );

            if ($rows) {
                foreach ($rows as $row) {
                    if ($begin <= 0) {
                        $uidList[] = $row[$idField];
                    }
                    if ($depth > 1) {
                        $uidList = array_merge(
                            $uidList,
                            $this->getTreeUidListRecursive(
                                $row[$idField],
                                $depth - 1,
                                $begin - 1,
                                $options
                            )
                        );
                    }
                }
            }
        }

        return $uidList;
    }

    /**
     * @return Tx_Rnbase_Database_Connection
     */
    protected function getConnection()
    {
        return Tx_Rnbase_Database_Connection::getInstance();
    }
}
