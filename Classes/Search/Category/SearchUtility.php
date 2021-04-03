<?php

namespace Sys25\RnBase\Search\Category;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 René Nitzsche <rene@system25.de>
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
 * Tx_Rnbase_Category_SearchUtility.
 *
 * provides methods to enhance a searcher to support sys_category usage
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class SearchUtility
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
     * @return string
     */
    public function addJoins(
        $baseTableName,
        $baseTableAlias,
        $fieldName,
        array $givenTableAliases,
        $sysCategoryTableAlias = 'SYS_CATEGORY'
    ) {
        $joins = '';
        if (isset($givenTableAliases[$sysCategoryTableAlias])) {
            $joins =
                ' LEFT JOIN sys_category_record_mm AS '.$sysCategoryTableAlias.'_MM ON '.$sysCategoryTableAlias.'_MM.uid_foreign'.
                ' = '.$baseTableAlias.'.uid AND '.$sysCategoryTableAlias.'_MM.tablenames = "'.
                $baseTableName.'" AND '.$sysCategoryTableAlias.'_MM.fieldname = "'.$fieldName.'"'.
                ' LEFT JOIN sys_category AS '.$sysCategoryTableAlias.' ON '.$sysCategoryTableAlias.
                '.uid = '.$sysCategoryTableAlias.'_MM.uid_local';
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
        $sysCategoryTableAlias = 'SYS_CATEGORY'
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
