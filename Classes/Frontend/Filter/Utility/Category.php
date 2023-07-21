<?php

namespace Sys25\RnBase\Frontend\Filter\Utility;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Database\Connection;

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
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Category
{
    protected $configurations;

    protected $confId;

    private $dbConnection;

    /**
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     */
    public function __construct(ConfigurationInterface $configurations, $confId)
    {
        $this->configurations = $configurations;
        $this->confId = $confId;
    }

    /**
     * @param array $fields
     *
     * @return bool|null
     */
    public function handleSysCategoryFilter(array &$fields, $doSearch)
    {
        $typoScriptPathsToFilterUtilityMethod = [
            'useSysCategoriesOfItemFromParameters' => 'setFieldsBySysCategoriesOfItemFromParameters',
            'useSysCategoriesOfContentElement' => 'setFieldsBySysCategoriesOfContentElement',
            'useSysCategoriesFromParameters' => 'setFieldsBySysCategoriesFromParameters',
        ];

        foreach ($typoScriptPathsToFilterUtilityMethod as $typoScriptPath => $filterUtilityMethod) {
            if ($this->configurations->get($this->confId.$typoScriptPath)) {
                $fieldsBefore = $fields;
                $fields = $this->$filterUtilityMethod(
                    $fields,
                    $this->configurations,
                    $this->confId.$typoScriptPath.'.'
                );

                if (
                    $this->configurations->get($this->confId.$typoScriptPath.'.dontSearchIfNoCategoriesFound') &&
                    // wenn sich die $fields nicht geändert haben, dann wurden keine Kategorie
                    // gefunden.
                    $fieldsBefore == $fields
                ) {
                    $doSearch = false;
                }
            }
        }

        return $doSearch;
    }

    /**
     * @param array                  $fields
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     *
     * @return array
     */
    protected function setFieldsBySysCategoriesOfItemFromParameters(
        array $fields,
        $configurations,
        $confId
    ) {
        if ($categories = $this->lookupCategoryUidsFromParameters($configurations, $confId)) {
            $fields = $this->getFieldsByCategories($categories, $fields, $configurations, $confId);
        }

        return $fields;
    }

    /**
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     *
     * @return array
     */
    protected function lookupCategoryUidsFromParameters($configurations, $confId)
    {
        $parameters = $configurations->getParameters();
        $categories = [];
        foreach ($configurations->get($confId.'supportedParameters.') as $paramConfig) {
            $referencedUid = $parameters->getInt(
                $paramConfig['parameterName'],
                $paramConfig['parameterQualifier']
            );

            if ($referencedUid) {
                $categories = $this->getCategoryUidsByReference(
                    $paramConfig['table'],
                    $paramConfig['categoryField'],
                    $referencedUid
                );

                continue;
            }
        }

        return $categories;
    }

    /**
     * @param string $table
     * @param string $categoryField
     * @param int    $foreignUid
     *
     * @return array
     */
    protected function getCategoryUidsByReference($table, $categoryField, $foreignUid)
    {
        $databaseConnection = $this->getDatabaseConnection();
        $categories = $databaseConnection->doSelect(
            'uid_local',
            'sys_category_record_mm',
            [
                'where' => 'sys_category_record_mm.tablenames = '.
                    $databaseConnection->fullQuoteStr($table).' AND '.
                    'sys_category_record_mm.fieldname = '.
                    $databaseConnection->fullQuoteStr($categoryField).' AND '.
                    'sys_category_record_mm.uid_foreign = '.intval($foreignUid),
                'enablefieldsoff' => true,
            ]
        );

        $categories = array_map(
            function ($value) {
                return $value['uid_local'];
            },
            $categories
        );

        return $categories;
    }

    /**
     * @return Connection
     */
    protected function getDatabaseConnection()
    {
        return $this->dbConnection ?: Connection::getInstance();
    }

    public function setDatabaseConnection(Connection $connection)
    {
        $this->dbConnection = $connection;
    }

    /**
     * @param array                  $categories
     * @param array                  $fields
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     *
     * @return array
     */
    protected function getFieldsByCategories(
        array $categories,
        array $fields,
        $configurations,
        $confId
    ) {
        $sysCategoryTableAlias =
            $configurations->get($confId.'sysCategoryTableAlias') ?
                $configurations->get($confId.'sysCategoryTableAlias') :
                'SYS_CATEGORY';
        $fields[$sysCategoryTableAlias.'.uid'] = [OP_IN_INT => implode(',', $categories)];

        return $fields;
    }

    /**
     * @param array                  $fields
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     *
     * @return array
     */
    protected function setFieldsBySysCategoriesOfContentElement(array $fields, $configurations, $confId)
    {
        $categories = $this->getCategoryUidsByReference(
            'tt_content',
            'categories',
            $configurations->getContentObject()->data['uid']
        );
        if ($categories) {
            $fields = $this->getFieldsByCategories($categories, $fields, $configurations, $confId);
        }

        return $fields;
    }

    /**
     * @param array                  $fields
     * @param ConfigurationInterface $configurations
     * @param string                 $confId
     *
     * @return array
     */
    protected function setFieldsBySysCategoriesFromParameters(
        array $fields,
        $configurations,
        $confId
    ) {
        $categoryUid = $configurations->getParameters()->getInt(
            $configurations->get($confId.'parameterName'),
            $configurations->get($confId.'parameterQualifier')
        );
        if ($categoryUid) {
            $fields = $this->getFieldsByCategories([$categoryUid], $fields, $configurations, $confId);
        }

        return $fields;
    }
}
