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
 * Data provider for plots configured by Typoscript
 */
class tx_rnbase_plot_DataProviderTS implements tx_rnbase_plot_IDataProvider {
	public function getChartTitle($confArr) {
		return $confArr['text'];
	}

	/**
	 * Returns the dataset
	 * @return tx_rnbase_plot_IDataSetXY
	 */
	public function getDataSets($confArr, $plotType) {
		return array($this->readDatasets($confArr['dataset.']));
	}

	/**
	 * Returns the style for each data set. This is either an instance of tx_pbimagegraph_Fill_Array or
	 * a simple php array with style data
	 * @param $confArr
	 * @return tx_pbimagegraph_Fill_Array
	 */
	public function getDataStyles($plotId, $confArr) {
		$arrConf = $confArr['dataStyle.'];
		$objFillStyle = tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
		if(!is_object($arrConf)) return $objFillStyle;
		$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
		foreach($arrKeys as $strKey) {
			$strType=$arrConf[$strKey];
			if (intval($strKey) && !strstr($strKey, '.')) {
				$strId = $arrConf[$strKey.'.']['id'] ? $arrConf[$strKey.'.']['id'] : FALSE;
				switch($strType) {
					case 'color':
					case 'addColor':
						$strColor = $arrConf[$strKey.'.']['color'];
						$objFillStyle->addColor($strColor, $strId);
					break;
					case 'gradient':
						$intDirection = tx_rnbase_plot_Builder::readConstant('IMAGE_GRAPH_GRAD_'.strtoupper($arrConf[$strKey.'.']['direction']));
						$strStartColor = $arrConf[$strKey.'.']['startColor'];
						$strEndColor = $arrConf[$strKey.'.']['endColor'];
						$objFillStyle->addNew('gradient', array($intDirection, $strStartColor, $strEndColor), $strId);
					break;
				}
			}
		}
		return $objFillStyle;
	}

	/**
	 * Read the datasets
	 *
	 * @param	array		The array with TypoScript properties for the object
	 * @return	object		The Dataset object
	 */
	private function readDatasets($arrConf) {
		$objDatasets = array();
		$intCount = 0;
		if (is_array($arrConf)) {
			$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($arrKeys as $strKey) {
				$strValue=$arrConf[$strKey];
				if (intval($strKey) && !strstr($strKey, '.')) {
					switch($strValue) {
						case 'trivial':
							$objDatasets[$intCount] = $this->datasetTrivial($arrConf[$strKey.'.']);
						break;
						case 'random':
							$objDatasets[$intCount] = $this->datasetRandom($arrConf[$strKey.'.']);
						break;
					}
					$intCount++;
				}
			}
		}
		return $objDatasets;
	}

	/**
	 * Set a single trivial dataset
	 *
	 * @param	object		The parent Dataset object
	 * @param	array		The array with TypoScript properties for the object
	 */
	private function datasetTrivial($arrConf) {
		$dataSet =& tx_pbimagegraph::factory('dataset');
		if (is_array($arrConf)) {
			$strName = $arrConf['name'];
			$dataSet->setName($strName);
			$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($arrKeys as $strKey) {
				$strValue=$arrConf[$strKey];
				if (intval($strKey) && !strstr($strKey, '.')) {
					if($strValue=='point') {
						$mixX = $arrConf[$strKey.'.']['x'];
						if ($arrConf[$strKey.'.']['y']=='null') {
							$mixY = NULL;
						} elseif (is_array($arrConf[$strKey.'.']['y.'])) {
							$mixY = $arrConf[$strKey.'.']['y.'];
						} else {
							$mixY = $arrConf[$strKey.'.']['y'];
						}
						//$mixY = ($arrConf[$strKey.'.']['y']=='null')?NULL:$arrConf[$strKey.'.']['y'];
						$strId = $arrConf[$strKey.'.']['id'];
						$dataSet->addPoint($mixX, $mixY, $strId);
					}
				}
			}
		}
		return $dataSet;
	}

	/**
	 * Create a single random dataset
	 *
	 * @param	array		The array with TypoScript properties for the object
	 * @return	object		Single dataset
	 */
	private function datasetRandom($arrConf) {
		$intCount = $arrConf['count'];
		$intMinimum = $arrConf['minimum'];
		$intMaximum = $arrConf['maximum'];
		$boolIncludeZero = $arrConf['includeZero']=='true' ? TRUE : FALSE;
		$strName = $arrConf['name'];
		$objRandom = tx_pbimagegraph::factory('random', array($intCount, $intMinimum, $intMaximum, $boolIncludeZero));
		$objRandom->setName($strName);
		return $objRandom;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_DataProviderTS.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_DataProviderTS.php']);
}

