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

tx_rnbase::load('tx_rnbase_plot_IDataProvider');

/**
 * This interface defines a data provider for plots
 */
class tx_rnbase_plot_DataProvider implements tx_rnbase_plot_IDataProvider {
	private $dataSets = array();

	public function getChartTitle($confArr) {
		return $this->chartTitle;
	}
	public function setChartTitle($title) {
		$this->chartTitle = $title;
	}
	/**
	 * Returns the datasets for all plots
	 * @return tx_rnbase_plot_IDataSetXY
	 */
	public function getDataSets($confArr, $plotType) {
		return $this->dataSets;
	}
	/**
	 * Returns the style for each data set. This is either an instance of tx_pbimagegraph_Fill_Array or
	 * a simple php array with style data
	 * @param $confArr
	 * @return array
	 */
	public function getDataStyles($plotId, $confArr) {
		// Es wird der Style mit der ID 0 ausgelesen. Hier können mehrere Angaben gemacht werden
		$ret = array();
		$type = $confArr['dataStyle.']['0'];
		$data = $confArr['dataStyle.']['0.'];
		switch($type) {
			case 'color':
			case 'addColor':
				$strColors = t3lib_div::trimExplode(',', $data['color']);
				foreach($strColors As $color)
					$ret[] = array('type' => 'color', 'color' => $color);
			break;
			case 'gradient':
				$intDirection = tx_rnbase_plot_Builder::readConstant('IMAGE_GRAPH_GRAD_'.strtoupper($data['direction']));
				$strColorsStart = t3lib_div::trimExplode(',', $data['startColor']);
				$strColorsEnd = t3lib_div::trimExplode(',', $data['endColor']);
				$maxStart = count($strColorsStart);
				$maxEnd = count($strColorsEnd);
				$max = $maxStart > $maxEnd ? $maxStart : $maxEnd;
				for($i=0; $i<$max; $i++) {
					$strStartColor = $i < $maxStart ? $strColorsStart[$i] : $strColorsStart[$maxStart-1];
					$strEndColor = $i < $maxEnd ? $strColorsEnd[$i] : $strColorsEnd[$maxEnd-1];
					$ret[] = array('type' => 'gradient', 'color' => array($intDirection, $strStartColor, $strEndColor));
				}
			break;
		}
		return $ret;
	}

	public function addPlot() {
		$id = count($this->dataSets);
		$this->dataSets[] = array();
		return $id;
	}
	/**
	 * Returns the uid
	 * @return tx_rnbase_plot_IDataSetXY
	 */
	public function addDataSet($plotId, $dataSet) {
		$this->dataSets[$plotId][] = $dataSet;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_DataProvider.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_DataProvider.php']);
}

