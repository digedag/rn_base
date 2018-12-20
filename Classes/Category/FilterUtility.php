<?php
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
 * Tx_Rnbase_Category_FilterUtility
 *
 * @package         TYPO3
 * @subpackage      Tx_Rnbase
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Category_FilterUtility
{

    /**
     * @param array $fields
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     *
     * @return array
     */
    public function setFieldsBySysCategoriesOfItemFromParameters(
        array $fields, Tx_Rnbase_Configuration_ProcessorInterface $configurations, $confId
    ) {
        if ($categories = $this->getCategoryUidsOfCurrentDetailViewItem($configurations, $confId)) {
            $fields = $this->getFieldsByCategories($categories, $fields, $configurations, $confId);
        }

        return $fields;
    }

    /**
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     *
     * @return array
     */
    protected function getCategoryUidsOfCurrentDetailViewItem(Tx_Rnbase_Configuration_ProcessorInterface $configurations, $confId)
    {
        $categories = [];
        foreach ($configurations->get($confId . 'supportedParameters.') as $configurationPerParameter) {
            $detailViewParameter = $configurations->getParameters()->getInt(
                $configurationPerParameter['parameterName'], $configurationPerParameter['parameterQualifier']
            );

            if ($detailViewParameter) {
                $categories = $this->getCategoryUidsByReference(
                    $configurationPerParameter['table'], $configurationPerParameter['categoryField'], $detailViewParameter
                );
                continue;
            }
        }

        return $categories;
    }

    /**
     * @param string $table
     * @param string $categoryField
     * @param int $foreignUid
     *
     * @return array
     */
    protected function getCategoryUidsByReference($table, $categoryField, $foreignUid)
    {
        $databaseConnection = $this->getDatabaseConnection();
        $categories =  $databaseConnection->doSelect(
            'uid_local', 'sys_category_record_mm',
            [
                'where' =>
                    'sys_category_record_mm.tablenames = ' .
                    $databaseConnection->fullQuoteStr($table) . ' AND ' .
                    'sys_category_record_mm.fieldname = ' .
                    $databaseConnection->fullQuoteStr($categoryField) . ' AND ' .
                    'sys_category_record_mm.uid_foreign = ' . intval($foreignUid),
                'enablefieldsoff' => true
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
     * @return Tx_Rnbase_Database_Connection
     */
    protected function getDatabaseConnection()
    {
        return Tx_Rnbase_Database_Connection::getInstance();
    }

    /**
     * @param array $categories
     * @param array $fields
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     * @return array
     */
    protected function getFieldsByCategories(
        array $categories, array $fields, Tx_Rnbase_Configuration_ProcessorInterface $configurations, $confId
    ) {
        $sysCategoryTableAlias =
            $configurations->get($confId . 'sysCategoryTableAlias') ?
                $configurations->get($confId . 'sysCategoryTableAlias') :
                'SYS_CATEGORY';
        $fields[$sysCategoryTableAlias . '.uid'] = [OP_IN_INT => join(',', $categories)];

        return $fields;
    }

    /**
     * @param array $fields
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     *
     * @return array
     */
    public function setFieldsBySysCategoriesOfContentElement(
        array $fields, Tx_Rnbase_Configuration_ProcessorInterface $configurations, $confId
    ) {
        $categories = $this->getCategoryUidsByReference(
            'tt_content', 'categories', $configurations->getContentObject()->data['uid']
        );
        if ($categories) {
            $fields = $this->getFieldsByCategories($categories, $fields, $configurations, $confId);
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @param string $confId
     *
     * @return array
     */
    public function setFieldsBySysCategoriesFromParameters(
        array $fields, Tx_Rnbase_Configuration_ProcessorInterface $configurations, $confId
    ) {
        $categoryUid = $configurations->getParameters()->getInt(
            $configurations->get($confId . 'parameterName'), $configurations->get($confId . 'parameterQualifier')
        );
        if ($categoryUid) {
            $fields = $this->getFieldsByCategories([$categoryUid], $fields, $configurations, $confId);
        }

        return $fields;
    }
}
