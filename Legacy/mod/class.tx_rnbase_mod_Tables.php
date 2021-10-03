<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2016 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author rene
 *
 * @deprecated use Tx_Rnbase_Backend_Utility_Tables
 */
class tx_rnbase_mod_Tables
{
    /**
     * @param array                                $entries
     * @param array                                $columns
     * @param tx_rnbase_util_FormTool              $formTool
     * @param Tx_Rnbase_Domain_Model_DataInterface $options
     *
     * @deprecated
     *
     * @return array 0 are data and 1 layout
     */
    public static function prepareTable($entries, $columns, $formTool, $options)
    {
        return static::getUtility()->prepareTable($entries, $columns, $formTool, $options);
    }

    /**
     * Returns a table based on the input $data
     * This method is taken from TYPO3 core. It will be removed there for version 8.
     *
     * Typical call until now:
     * $content .= tx_rnbase_mod_Tables::buildTable($data, $module->getTableLayout());
     * Should we include a better default layout here??
     *
     * @param array $data   Multidim array with first levels = rows, second levels = cells
     * @param array $layout If set, then this provides an alternative layout array instead of $this->tableLayout
     *
     * @deprecated
     *
     * @return string the HTML table
     */
    public static function buildTable($data, $layout = null)
    {
        return static::getUtility()->buildTable($data, $layout);
    }

    /**
     * Returns a default table layout.
     *
     * @deprecated
     *
     * @return array
     */
    public static function getTableLayout()
    {
        return static::getUtility()->getTableLayout();
    }

    /**
     * Returns the new utility.
     *
     * @return Tx_Rnbase_Backend_Utility_Tables
     */
    private static function getUtility()
    {
        return tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
    }
}
