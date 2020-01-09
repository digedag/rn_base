<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 René Nitzsche (nitzsche@das-medienkombinat.de)
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
 * tx_rnbase_tests_fixtures_classes_Searcher.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_fixtures_classes_Searcher extends tx_rnbase_util_SearchBase
{
    /**
     * @var bool
     */
    private $useAlias = false;

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getTableMappings()
     */
    public function getTableMappings()
    {
        $tableMapping = array();
        $tableMapping[$this->getBaseTableAlias()] = $this->getBaseTable();
        $tableMapping['CONTENT'] = 'tt_content';
        $tableMapping['FEUSER'] = 'fe_users';

        return $tableMapping;
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getBaseTable()
     */
    public function getBaseTable()
    {
        return 'pages';
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getBaseTableAlias()
     */
    protected function getBaseTableAlias()
    {
        return 'PAGE';
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getWrapperClass()
     */
    public function getWrapperClass()
    {
        return 'tx_rnbase_model_Base';
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getJoins()
     */
    protected function getJoins($tableAliases)
    {
        return '';
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::useAlias()
     */
    protected function useAlias()
    {
        return $this->useAlias;
    }

    /**
     * @param bool $useAlias
     */
    public function setUseAlias($useAlias)
    {
        $this->useAlias = $useAlias;
    }
}
