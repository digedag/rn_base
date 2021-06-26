<?php

namespace Sys25\RnBase\Search\Category;

/***************************************************************
 * Copyright notice
 *
 * (c) René Nitzsche <rene@system25.de>
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
 * Sys25\RnBase\Search$Category.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Category extends \tx_rnbase_util_SearchBase
{
    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getTableMappings()
     */
    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping[$this->getBaseTableAlias()] = $this->getBaseTable();
        $tableMapping['SYS_CATEGORY_RECORD_MM'] = 'sys_category_record_mm';

        return $tableMapping;
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::useAlias()
     */
    protected function useAlias()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getBaseTableAlias()
     */
    protected function getBaseTableAlias()
    {
        return 'SYS_CATEGORY';
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getBaseTable()
     */
    protected function getBaseTable()
    {
        return \tx_rnbase::makeInstance($this->getWrapperClass())->getTableName();
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getWrapperClass()
     */
    public function getWrapperClass()
    {
        return \Sys25\RnBase\Domain\Model\Category::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getJoins()
     */
    protected function getJoins($tableAliases)
    {
        $joins = '';
        $tableMappings = $this->getTableMappings();
        $baseAlias = $this->getBaseTableAlias();
        if (isset($tableAliases['SYS_CATEGORY_RECORD_MM'])) {
            $joins = ' LEFT JOIN '.$tableMappings['SYS_CATEGORY_RECORD_MM'].' AS SYS_CATEGORY_RECORD_MM ON'.
                        ' SYS_CATEGORY_RECORD_MM.uid_local = '.$baseAlias.'.uid';
        }

        return $joins;
    }
}
