<?php

namespace Sys25\RnBase\Search\System;

use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Misc;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017-2022 René Nitzsche <rene@system25.de>
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

class FeGroupSearch extends SearchBase
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
        $tableMapping['FEUSER'] = 'fe_users';

        // Hook to append other tables
        Misc::callHook('rn_base', 'search_FeGroup_getTableMapping_hook', [
            'tableMapping' => &$tableMapping,
        ], $this);

        return $tableMapping;
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getBaseTableAlias()
     */
    protected function getBaseTableAlias()
    {
        return 'FEGROUP';
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getBaseTable()
     */
    protected function getBaseTable()
    {
        return tx_rnbase::makeInstance($this->getWrapperClass())->getTableName();
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getWrapperClass()
     */
    public function getWrapperClass()
    {
        return \Sys25\RnBase\Domain\Model\FeGroup::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see \tx_rnbase_util_SearchBase::getJoins()
     */
    protected function getJoins($tableAliases)
    {
        $joins = [];
        if (isset($tableAliases['FEUSER'])) {
            $joins[] = new Join('FEGROUP', 'fe_users', 'FIND_IN_SET( FEGROUP.uid, FEUSER.usergroup )', 'FEUSER');
        }

        // Hook to append other tables
        Misc::callHook('rn_base', 'search_FeGroup_getJoins_hook', [
            'join' => &$joins,
            'tableAliases' => $tableAliases,
        ], $this);

        return $joins;
    }
}