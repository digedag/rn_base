<?php

namespace Sys25\RnBase\Search\System;

use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Misc;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017-2023 René Nitzsche <rene@system25.de>
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
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CategorySearch extends SearchBase
{
    /**
     * {@inheritdoc}
     *
     * @see SearchBase::getTableMappings()
     */
    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping[$this->getBaseTableAlias()] = $this->getBaseTable();
        $tableMapping['SYS_CATEGORY_RECORD_MM'] = 'sys_category_record_mm';

        // Hook to append other tables
        Misc::callHook('rn_base', 'search_Category_getTableMapping_hook', [
            'tableMapping' => &$tableMapping,
        ], $this);

        return $tableMapping;
    }

    /**
     * {@inheritdoc}
     *
     * @see SearchBase::getBaseTableAlias()
     */
    protected function getBaseTableAlias()
    {
        return 'SYS_CATEGORY';
    }

    /**
     * {@inheritdoc}
     *
     * @see SearchBase::getBaseTable()
     */
    protected function getBaseTable()
    {
        return \tx_rnbase::makeInstance($this->getWrapperClass())->getTableName();
    }

    /**
     * {@inheritdoc}
     *
     * @see SearchBase::getWrapperClass()
     */
    public function getWrapperClass()
    {
        return \Sys25\RnBase\Domain\Model\Category::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see SearchBase::getJoins()
     */
    protected function getJoins($tableAliases)
    {
        $joins = [];
        if (isset($tableAliases['SYS_CATEGORY_RECORD_MM'])) {
            $joins[] = new Join('SYS_CATEGORY', 'sys_category_record_mm', 'SYS_CATEGORY_RECORD_MM.uid_local = SYS_CATEGORY.uid', 'SYS_CATEGORY_RECORD_MM');
        }

        // Hook to append other tables
        Misc::callHook('rn_base', 'search_Category_getJoins_hook', [
            'join' => &$joins,
            'tableAliases' => $tableAliases,
        ], $this);

        return $joins;
    }
}
