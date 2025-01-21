<?php

namespace Sys25\RnBase\Search\System;

use Sys25\RnBase\Database\Query\Join;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017-2021 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * provides methods to enhance a searcher to support sys_category usage.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CategorySearchUtility
{
    /**
     * @param array  $tableMappings
     * @param string $tableAlias
     *
     * @return array
     */
    public function addTableMapping(array $tableMappings, $tableAlias = 'SYS_CATEGORY')
    {
        $tableMappings[$tableAlias] = 'sys_category';

        return $tableMappings;
    }

    /**
     * @param string $baseTableName
     * @param string $baseTableAlias
     * @param string $fieldName
     * @param array  $givenTableAliases
     * @param string $sysCategoryTableAlias
     *
     * @return array
     */
    public function addJoins(
        $baseTableName,
        $baseTableAlias,
        $fieldName,
        array $givenTableAliases,
        $sysCategoryTableAlias = 'SYS_CATEGORY',
    ) {
        $joins = [];
        if (isset($givenTableAliases[$sysCategoryTableAlias.'_MM']) || isset($givenTableAliases[$sysCategoryTableAlias])) {
            $joins[] = new Join($baseTableAlias, 'sys_category_record_mm',
                sprintf('%s_MM.uid_foreign = %s.uid AND %s_MM.tablenames = \'%s\' AND %s_MM.fieldname = \'%s\'',
                    $sysCategoryTableAlias,
                    $baseTableAlias,
                    $sysCategoryTableAlias,
                    $baseTableName,
                    $sysCategoryTableAlias,
                    $fieldName
                ),
                $sysCategoryTableAlias.'_MM'
            );
        }
        if (isset($givenTableAliases[$sysCategoryTableAlias])) {
            $joins[] = new Join($sysCategoryTableAlias.'_MM', 'sys_category',
                sprintf('%s.uid = %s_MM.uid_local', $sysCategoryTableAlias, $sysCategoryTableAlias), $sysCategoryTableAlias);
        }

        return $joins;
    }

    /**
     * @param string $baseTableName
     * @param string $baseTableAlias
     * @param string $fieldName
     * @param array  $givenTableAliases
     * @param string $sysCategoryTableAlias
     *
     * @return string
     */
    public function addJoinsWithoutAlias(
        $baseTableName,
        $baseTableAlias,
        $fieldName,
        array $givenTableAliases,
        $sysCategoryTableAlias = 'SYS_CATEGORY',
    ) {
        $joins = '';
        if (isset($givenTableAliases[$sysCategoryTableAlias])) {
            $joins =
            ' LEFT JOIN sys_category_record_mm ON sys_category_record_mm.uid_foreign'.
            ' = '.$baseTableName.'.uid AND sys_category_record_mm.tablenames = "'.
            $baseTableName.'" AND sys_category_record_mm.fieldname = "'.$fieldName.'"'.
            ' LEFT JOIN sys_category ON sys_category.uid = sys_category_record_mm.uid_local';
        }

        return $joins;
    }
}
