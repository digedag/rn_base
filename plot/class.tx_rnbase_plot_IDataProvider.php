<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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

// Die Datenbank-Klasse

/**
 * This interface defines a data provider for plots
 */
interface tx_rnbase_plot_IDataProvider
{
    /**
     * Returns the data sets
     * @param $confArr
     * @param string $plotType
     * @return tx_rnbase_plot_IDataSet
     */
    public function getDataSets($confArr, $plotType);

    /**
     * Returns the style for each data set. This is either an instance of tx_pbimagegraph_Fill_Array or
     * a simple php array with style data
     * @param $confArr
     * @return tx_pbimagegraph_Fill_Array or array
     */
    public function getDataStyles($plotId, $confArr);

    /**
     * Returns the chartTitle
     */
    public function getChartTitle($confArr);
}
