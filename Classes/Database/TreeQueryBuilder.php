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
 * methods for generating queries on a hierarchical tree structure
 *
 * Tx_Rnbase_Database_TreeQueryBuilder
 *
 * @package           TYPO3
 * @subpackage        rn_base
 * @author            <mario.seidel> <mario.seidel@dmk-ebusines.de
 */
class Tx_Rnbase_Database_TreeQueryBuilder
{
	/**
	 * returns an array of tree-like assigned entities like a pagetree
	 * but could also handle any other hierarchical db structure
	 *
	 * @param int|string $id      id or list of ids comma separated
	 * @param int        $depth
	 * @param int        $begin
	 * @param array      $options All options except "where" are forwarded to "doSelect" directly.
	 *                            The parentField (pid) will be added to the where clausle automaticly.
	 *                            additional options:
	 *                              tableName   - what table should be used (default: pages)
	 *                              parentField - the field where the parent id is stored (default: pid)
	 *                              idField     - the field of the identifier that will be returned (default: uid)
	 *
	 * @return array
	 */
	public function getTreeListRecursive($id, $depth, $begin = 0, $options = array())
	{
		$depth = (int)$depth;
		$begin = (int)$begin;
		$id = abs((int)$id);

		$parentField = array_key_exists('parentField', $options) ? $options['parentField'] : 'pid';
		$idField = array_key_exists('idField', $options) ? $options['idField'] : 'uid';

		if ($begin == 0) {
			$uidList = array($id);
		} else {
			$uidList = array();
		}
		if ($id && $depth > 0) {

			if (!array_key_exists('tableName', $options)) {
				$options['tableName'] = 'pages';
			}

			$sqlOptions = $options;
			$sqlOptions['where'] = array_key_exists('where', $options) && strlen($options['where']) > 0
				? $options['where'] . ' AND ' . $parentField . ' IN (' . (int)$id . ')'
				: $parentField . ' IN (' . (int)$id . ')';

			/** @var Tx_Rnbase_Domain_Collection_Base $rows */
			$rows = $this->getConnection()->doSelect(
				$idField,
				$sqlOptions['tableName'],
				$sqlOptions
			);

			if ($rows) {
				foreach ($rows as $row) {
					if ($begin <= 0) {
						array_push($uidList, $row[$idField]);
					}
					if ($depth > 1) {
						$uidList = array_merge(
							$uidList,
							$this->getTreeListRecursive($row[$idField], $depth - 1, $begin - 1, $options)
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
