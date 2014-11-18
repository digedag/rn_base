<?php
/*  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *  (c) 2005 Patrick Broens (patrick@patrickbroens.nl)
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
define('IMAGE_CANVAS_SYSTEM_FONT_PATH', PATH_site);
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph.php');
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph_canvas.php');


/**
 * Builder class for the 'pbimagegraph' extension. The class is modifed version of 
 * tx_pbimagegraph_ts. It can be used to build graphs from PHP.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @author Patrick Broens <patrick@patrickbroens.nl>
 * @package TYPO3
 * @subpackage rn_base
 */
class tx_rnbase_plot_Builder {
	private $ctx;
	private static $arrPlotClassAlias = array(
			'AREA'           => 'tx_pbimagegraph_Plot_Area',
			'BAND'           => 'tx_pbimagegraph_Plot_Band',
			'BAR'            => 'tx_pbimagegraph_Plot_Bar',
			'BOXWHISKER'     => 'tx_pbimagegraph_Plot_BoxWhisker',
			'CANDLESTICK'    => 'tx_pbimagegraph_Plot_CandleStick',
			'DOT'            => 'tx_pbimagegraph_Plot_Dot',
			'IMPULSE'        => 'tx_pbimagegraph_Plot_Impulse',
			'LINE'           => 'tx_pbimagegraph_Plot_Line',
			'ODO'            => 'tx_pbimagegraph_Plot_Odo',
			'PIE'            => 'tx_pbimagegraph_Plot_Pie',
			'RADAR'          => 'tx_pbimagegraph_Plot_Radar',
			'SCATTER'        => 'tx_pbimagegraph_Plot_Dot',
			'STEP'           => 'tx_pbimagegraph_Plot_Step',
			'SMOOTH_AREA'    => 'tx_pbimagegraph_Plot_Smoothed_Area',
			'SMOOTH_LINE'    => 'tx_pbimagegraph_Plot_Smoothed_Line',
			'SMOOTH_RADAR'   => 'tx_pbimagegraph_Plot_Smoothed_Radar',
			'FIT_LINE'       => 'tx_pbimagegraph_Plot_Fit_Line',
		);

	private function __construct() {
		$this->ctx = new ArrayObject();
	}

	/**
	 * @return tx_rnbase_plot_Builder
	 */
	public static function getInstance() {
		return new tx_rnbase_plot_Builder();
	}
	/**
	 * Initialisation of the ImageGraph object. Checks if the file is already generated,
	 * otherwise generation of the file is not necessary.
	 *
	 * @param	array		TS Configuration of the image
	 * @param tx_rnbase_plot_IDataProvider $dp
	 * @return	string		The img tag
	 */
	public function make($arrConf, $dp)	{
		$this->setDataProvider($dp);

		if ($arrConf) {
			$strFileName = $this->getFileName('ImageGraph/', $arrConf, $arrConf['factory']);
			$arrConf['factory'] = $arrConf['factory'] ? $arrConf['factory']:'png';
			$arrConf['width'] = $arrConf['width'] ? $arrConf['width']:'400';
			$arrConf['height'] = $arrConf['height'] ? $arrConf['height']:'300';
			if (!@file_exists(PATH_site . $strFileName) || TRUE) { // TODO: remove me!!!
				$objGraph = $this->makeCanvas($arrConf, $dp);
				$objGraph->done(array('filename' => PATH_site . $strFileName));
			}
			$strAltParam = $this->getAltParam($arrConf);
			switch(strtolower($arrConf['factory'])) {
				case 'svg':
					$strOutput = '<object width="' . $arrConf['width'] . '" height="' . $arrConf['height'] . '" type="image/svg+xml" data="' . $strFileName . '">Browser does not support SVG files!</object>';
					break;
				case 'pdf':
					header('Location: '.t3lib_div::locationHeaderUrl($strFileName));
        			exit;
				default:
					$strOutput = '<img width="' . $arrConf['width'] . '" height="' . $arrConf['height'] . '" src="/'.$strFileName.'" '.$strAltParam.' />';
			}
		}
		return $strOutput;
	}

	/**
	 * Initialisation of the ImageGraph canvas, according to the TS configuration.
	 * Call cObjGet to fill the canvas with content.
	 *
	 * @param	array		TS Configuration of the image
	 * @return tx_pbimagegraph
	 */
	private function makeCanvas($arrConf) {
		$arrParams['width'] = $arrConf['width'];
		$arrParams['height'] = $arrConf['height'];
		$arrParams['left'] = $arrConf['left'];
		$arrParams['top'] = $arrConf['top'];
		$arrParams['top'] = $arrConf['top'];
		$arrParams['noalpha'] = $arrConf['noalpha'];
//		When antialias = native is used Image_Graph is going to call the PHP function imageantialias
//		It checks on GD installation but not on the PHP version.
//		The function imageantialias became available in PHP version 4.3.2
//		I didn't change this in the PEAR package itself because I want the code as untouched as possible
//		because that way it is easier to implement updates from the PEAR package.
		if (function_exists('imageantialias') && $arrConf['antialias']=='native') {
			$arrConf['antialias'] = 'native';
		} else {
			$arrConf['antialias'] = 'off';
		}
		$arrParams['antialias'] = $arrConf['antialias']?$arrConf['antialias']:'off';
		$canvas =& tx_pbimagegraph_Canvas::factory($arrConf['factory'], $arrParams);
		$objGraph =& tx_pbimagegraph::factory('graph', $canvas);
		$this->setElementProperties($objGraph, $arrConf);

		$objEmpty = NULL;
		$objLayout = $this->cObjGet($arrConf, $objEmpty);
		$objGraph->add($objLayout);
		return $objGraph;
	}

	/**
	 * Calculates the ImageGraph output filename/path based on a serialized, hashed value of $arrConf
	 *
	 * @param	string		Filename prefix, eg. "ImageGraph/"
	 * @param	array		TS Configuration of the image
	 * @param	string		Filename extension, eg. "png"
	 * @return	string		The relative filepath (relative to PATH_site)
	 * @access private
	 */
	private function getFileName($strPre, $arrConf, $strExtension) {
		$tempPath = 'typo3temp/'; // Path to the temporary directory
		$data = serialize($this->getDataProvider()). serialize($arrConf);
		return $tempPath.$strPre.t3lib_div::shortMD5($data).'.'.$strExtension;
	}

	/**
	 * An abstraction method which creates an alt or title parameter for an HTML img tag.
	 * From the $arrConf array it implements the properties "altText", "titleText" and "longdescURL"
	 *
	 * @param	array		TypoScript configuration properties
	 * @return	string		Parameter string containing alt and title parameters (if any)
	 */
	function getAltParam($arrConf)	{
		$strAltText = $arrConf['altText'];
		$strTitleText = $arrConf['titleText'];
		$strLongDesc = $arrConf['longdescURL'];
		$strAltParam = ' alt="'.htmlspecialchars(strip_tags($strAltText)).'"';
		$strEmptyTitleHandling = 'useAlt';
		if ($arrConf['emptyTitleHandling'])	{
				// choices: 'keepEmpty' | 'useAlt' | 'removeAttr'
			$strEmptyTitleHandling = $arrConf['emptyTitleHandling'];
		}
		if ($strTitleText || $strEmptyTitleHandling == 'keepEmpty')	{
			$strAltParam.= ' title="'.htmlspecialchars(strip_tags($strTitleText)).'"';
		} elseif (!$strTitleText && $strEmptyTitleHandling == 'useAlt')	{
			$strAltParam.= ' title="'.htmlspecialchars(strip_tags($strAltText)).'"';
		}
		if ($strLongDesc)	{
			$strAltParam.= ' longdesc="'.htmlspecialchars(strip_tags($strLongDesc)).'"';
		}
		return $strAltParam;
	}

	/**
	 * Rendering of a "numerical array" of cObjects from TypoScript
	 * Will call ->cObjGetSingle() for each cObject found and accumulate the output.
	 *
	 * @param	array		Array with cObjects as values.
	 * @param	object		Reference object.
	 * @param tx_rnbase_plot_IDataProvider $dp
	 * @return	object		The object.
	 */
	private function cObjGet($arrSetup, &$objRef) {
		if (is_array($arrSetup)) {
			$currVersionStr = $TYPO3_CONF_VARS['SYS']['compat_version']?$TYPO3_CONF_VARS['SYS']['compat_version']:TYPO3_version;
			if (t3lib_div::int_from_ver($currVersionStr) < t3lib_div::int_from_ver('4.0.0')) {
				require_once(PATH_site.'t3lib/class.t3lib_tstemplate.php');
			}
			$arrSortedKeys=t3lib_TStemplate::sortedKeyList($arrSetup);
			foreach($arrSortedKeys as $strKey) {
				$strCobjName=$arrSetup[$strKey];
				if (intval($strKey) && !strstr($strKey, '.')) {
					$arrConf=$arrSetup[$strKey.'.'];
					$objOutput = $this->cObjGetSingle($strCobjName, $arrConf, $objRef);
				}
			}
		}
		return $objOutput;
	}

	/**
	 * Renders a content object
	 *
	 * @param	string		The content object name, eg. "CANVAS" or "PLOTAREA" or "LEGEND"
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	object		Reference object.
	 * @return	object		The object
	 */
	private function cObjGetSingle($strCobjName, $arrConf, &$objRef)	{
		$objEmpty = NULL;
		$strCobjName = trim($strCobjName);
		if (substr($strCobjName, 0, 1)=='<')	{
			$strKey = trim(substr($strCobjName, 1));
			$objTSparser = t3lib_div::makeInstance('t3lib_TSparser');
			$arrOldConf=$arrConf;
			list($strCobjName, $arrConf) = $objTSparser->getVal($strKey, $GLOBALS['TSFE']->tmpl->setup);
			if (is_array($arrOldConf) && count($arrOldConf))	{
				$conf = $this->joinTSarrays($arrConf, $arrOldConf);
			}
			$GLOBALS['TT']->incStackPointer();
			$objOutput =& $this->cObjGetSingle($strCobjName, $arrConf, $objEmpty);
			$GLOBALS['TT']->decStackPointer();
		} else {
			switch($strCobjName) {
				case 'PLOTAREA':
					$objOutput =& $this->PLOTAREA($arrConf);
					break;
				case 'AXIS_MARKER':
					$this->AXIS_MARKER($objRef, $arrConf);
					break;
				// Types of charts
				case 'LINE':
				case 'AREA':
				case 'BAR':
				case 'BOXWHISKER':
				case 'CANDLESTICK':
				case 'SMOOTH_LINE':
				case 'SMOOTH_AREA':
				case 'ODO':
				case 'PIE':
				case 'RADAR':
				case 'STEP':
				case 'IMPULSE':
				case 'DOT':
				case 'SCATTER':
				case 'BAND':
				case 'SMOOTH_RADAR':
				case 'FIT_LINE':
					$this->PLOT($strCobjName, $objRef, $arrConf);
					break;
				// Data
				case 'DATASET':
					$objOutput = self::DATASET($arrConf); // ??
					break;
				case 'RANDOM':
					$objOutput = self::RANDOM($arrConf); // ??
					break;
				case 'FUNCTION':
					$objOutput = self::FUNCTIO($arrConf); // ??
					break;
				case 'VECTOR':
					$objOutput = self::VECTOR($arrConf); // ??
					break;
				// Axis
				case 'CATEGORY':
					$objOutput = self::CATEGORY($arrConf); // ??
					break;
				case 'AXIS':
					$objOutput = self::AXIS($arrConf); // ??
					break;
				case 'AXIS_LOG':
					$objOutput = self::AXIS_LOG($arrConf); // ??
					break;
				// Title
				case 'TITLE':
					$objOutput = $this->TITLE($arrConf);
					break;
				// Grids
				case 'GRID':
					$this->GRID($objRef, $arrConf);
					break;
				// Various
				case 'LEGEND':
					$objOutput = $this->LEGEND($objRef, $arrConf);
					break;
				// Layout
				case 'VERTICAL':
				case 'HORIZONTAL':
					$objOutput = $this->VERT_HOR($arrConf, $strCobjName);
					break;
				case 'MATRIX':
					$objOutput = $this->MATRIX($arrConf);
					break;
			}
		}
		return $objOutput;
	}

	/**
	 * Draws the Plot Area
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Plot Area object
	 */
	private function PLOTAREA($arrConf) {
		$id = $arrConf['id'];
		switch($arrConf['type']) {
			case 'radar':
				$Plotarea = tx_pbimagegraph::factory('tx_pbimagegraph_Plotarea_Radar');
				break;
			default:
				$strAxisX = $arrConf['axis.']['x.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['x.']['type']):'tx_pbimagegraph_Axis_Category';
				$strAxisY = $arrConf['axis.']['y.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['y.']['type']):'tx_pbimagegraph_Axis';
				$strDirection = $arrConf['direction']?$arrConf['direction']:'vertical';
				$Plotarea = tx_pbimagegraph::factory('plotarea', array($strAxisX, $strAxisY, $strDirection));
		}

		$this->cObjGet($arrConf, $Plotarea);
		$this->setPlotareaProperties($Plotarea, $arrConf);
		$this->setElementProperties($Plotarea, $arrConf);

		if ($id) {
//			eval("self:\$ctx->".$id." =& \$Plotarea;");
			$this->ctx->offsetSet($id, $Plotarea);
		}
		return $Plotarea;
	}

	/**
	 * Draws a Axis Marker
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @param tx_rnbase_plot_IDataProvider $dp
	 */
	private function AXIS_MARKER(&$objRef, $arrConf, $dp) {
		$strType = $arrConf['type'];
		$intAxis = IMAGE_GRAPH_AXIS_Y;
		eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($arrConf['axis']).";");
		$Marker =& $objRef->addNew('tx_pbimagegraph_Axis_Marker_'.ucfirst($strType), NULL, $intAxis);
		$this->setElementProperties($Marker, $arrConf);
		switch($strType) {
			case 'area':
				$this->setAxisMarkerAreaProperties($Marker, $arrConf);
			break;
			case 'line':
				$this->setAxisMarkerLineProperties($Marker, $arrConf);
			break;
		}
	}

	public static function getClass4Plot($alias) {
		return self::$arrPlotClassAlias[$alias];
	}
	/**
	 * @return tx_rnbase_plot_IDataProvider
	 */
	private function getDataProvider() {
		if(!is_object($this->dp))
			$this->dp = tx_rnbase::makeInstance('tx_rnbase_plot_DataProviderTS');
		return $this->dp;
	}
	/**
	 * @param tx_rnbase_plot_IDataProvider $dp
	 */
	private function setDataProvider($dp) {
		$this->dp = $dp;
	}
	/**
	 * Draws one of the Plot Types
	 *
	 * @param	string		Name of the content object
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 */
	private function PLOT($strCobjName, &$objRef, $arrConf) {
		$intAxis = FALSE;
		if (isset($arrConf['axis'])) {
			eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($arrConf['axis']).";");
		}

		// Für jedes DataSet muss ein Plot angelegt werden
		$plotDataSets = $this->getDataProvider()->getDataSets($arrConf, $strCobjName);
		foreach($plotDataSets As $plotId => $dataSets) {
			if(!($dataSets[0] instanceof tx_pbimagegraph_Dataset)) {
				$dataSets = $this->convertDataSet($dataSets);
			}

			$arrParams[] = $dataSets;
			$arrParams[] = isset($arrConf['plottype'])?$arrConf['plottype']:'normal'; // normal, stacked, stacked100pct
			$strClass = self::getClass4Plot($strCobjName);
			$objPlot =& $objRef->addNew($strClass, $arrParams, $intAxis);
			$this->setPlotProperties($objPlot, $arrConf);

			// Set datastyle. formerly fillStyle
			$dataStyles = $this->getDataStyles($plotId, $arrConf);
			$objPlot->setFillStyle($dataStyles);
			$this->setElementProperties($objPlot, $arrConf);

			$this->setMarker($objPlot, $arrConf);
			switch($strCobjName) {
				case 'BAR':
					$this->setBarProperties($objPlot, $arrConf);
					break;
				case 'BOXWHISKER':
					$this->setBoxWhiskerProperties($objPlot, $arrConf);
					break;
				case 'ODO':
					$this->setOdoProperties($objPlot, $arrConf);
					break;
				case 'PIE':
					$this->setPieProperties($objPlot, $arrConf);
					break;
			}
		}
	}

	private function convertDataSet($dataSets) {
		$ret = array();
		// Zunächst mal einfache Arrays unterstützen
		foreach($dataSets As $dataSet) {
			if(is_array($dataSet)) {
				$objDataSet =& tx_pbimagegraph::factory('dataset');
				$objDataSet->setName($dataSet['name']);
				foreach($dataSet As $dataArr) {					
					$mixX = $dataArr['x'];
					$mixY = $dataArr['y'];
					$strId = $dataArr['id'];
					$objDataSet->addPoint($mixX, $mixY, $strId);
				}
				$ret[] = $objDataSet;
			}
		}
		return $ret;
	}
	/**
	 * Divide the Plot Area into Vertical and/or Horizontal parts
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	string		Name of the content object
	 * @return	object		The Vertical or Horizontal object
	 */
	function VERT_HOR($arrConf, $strCobjName) {
		$objEmpty = NULL;
		$percentage = $arrConf['percentage']?$arrConf['percentage']:'50';
		$Vert_Hor = '';
		$cObjCount = 1;
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey, '.')) {
					$conf=$arrConf[$theKey.'.'];
					if ($cObjCount == 1) {
						$objTopLeft = $this->cObjGetSingle($theValue, $conf, $objEmpty);
						$cObjCount++;
					} elseif ($cObjCount == 2) {
						$objBottomRight = $this->cObjGetSingle($theValue, $conf, $objEmpty);
						$cObjCount++;
					} else {
						break;
					}
				}
			}
		}
		eval("\$Vert_Hor = tx_pbimagegraph::".strtolower($strCobjName)."(\$objTopLeft, \$objBottomRight, \$percentage);");
	    return $Vert_Hor;
	}

	/**
	 * Divide the Plot Area into a Matrix
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	string		Name of the content object
	 * @param tx_rnbase_plot_IDataProvider $dp
	 * @return	object		The Matrix object
	 */
	private function MATRIX($arrConf, $dp) {
		$intCols = 0;
		$intRows = 0;
		$objEmpty = NULL;
		$boolAutoCreate = $arrConf['autoCreate']?$arrConf['autoCreate']:TRUE;
		if (is_array($arrConf)) {
			foreach ($arrConf as $strRow => $mixRow) {
				if (intval(rtrim($strRow, '.'))) {
					$intRows++;
					$intThisCols = count(t3lib_TStemplate::sortedKeyList($mixRow));
					if ($intCols==0) {
						$intCols = $intThisCols;
					} elseif ($intThisCols<$intCols) {
						$intCols = $intThisCols;
					}
				}
			}
		}
		$objMatrix = tx_pbimagegraph::factory('tx_pbimagegraph_Layout_Matrix', array($intRows, $intCols, $boolAutoCreate));
		$intRow = 0;
		if (is_array($arrConf)) {
			foreach ($arrConf as $strRow => $mixRow) {
				if (intval(rtrim($strRow, '.'))) {
					$arrSortedCols = t3lib_TStemplate::sortedKeyList($mixRow);
					foreach($arrSortedCols as $intCol=>$intColKey) {
						$strcObj=$mixRow[$intColKey];
						$arrcObjProperties = $mixRow[$intColKey.'.'];
						if (intval($intColKey) && !strstr($intColKey, '.') && $strcObj=='PLOTAREA') {
							if ($strcObj=='PLOTAREA') {
								$objPlotarea =& $objMatrix->getEntry($intRow, $intCol);
								$this->cObjGet($arrcObjProperties, $objPlotarea, $dp);
							} else {
								$this->cObjGetSingle($strcObj, $arrcObjProperties, $objEmpty, $dp);
							}
						}
					}
				$intRow++;
				}
			}
		}
		return $objMatrix;
	}

	/**
	 * Create the Title
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Title object
	 */
	private function TITLE($arrConf) {
		$intSize = $arrConf['size'];
		$intAngle = $arrConf['angle'];
		$strColor = $arrConf['color'];
		$objTitle = tx_pbimagegraph::factory('title', array('Title', array('size' => $intSize, 'angle' => $intAngle, 'color' => $strColor)));
		$this->setElementProperties($objTitle, $arrConf);
		$objTitle->setText($this->getDataProvider()->getChartTitle($arrConf));
		return $objTitle;
	}

	/**
	 * Create a Grid
	 *
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 */
	private function GRID(&$objRef, $arrConf) {
		$strType = $arrConf['type'].'_grid';
		$strAxis = $arrConf['axis'];
		$intAxis = 1;
		eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strAxis).";");
		$Grid =& $objRef->addNew($strType, $intAxis);
		$this->setElementProperties($Grid, $arrConf);
	}

	/**
	 * Create the Legend
	 *
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Legend object
	 */
	private function LEGEND(&$objRef, $arrConf) {
		if ($objRef) {
			$Legend =& $objRef->addNew('legend');
		} else {
			$Legend = tx_pbimagegraph::factory('legend');
		}
		$this->setElementProperties($Legend, $arrConf);
		$this->setLegendProperties($Legend, $arrConf);
		return $Legend;
	}



	/**
	 * Set a marker
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setMarker(&$objRef, $arrConf) {
		if ($arrConf['marker']) {
			switch($arrConf['marker']) {
				case 'value':
					$intAxis = 0;
					eval("\$intAxis = IMAGE_GRAPH_".strtoupper($arrConf['marker.']['useValue']).";");
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']), $intAxis);
					break;
				case 'array':
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Array');
					if (is_array($arrConf['marker.'])) {
						$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf['marker.']);
						foreach($arrKeys as $strKey) {
							$strType=$arrConf['marker.'][$strKey];
							if (intval($strKey) && !strstr($strKey, '.')) {
								switch($strType) {
									case 'icon':
									//$Marker->addNew('icon_marker', './images/audi.png');
										$objArrayMarker[$strKey] =& tx_pbimagegraph::factory('tx_pbimagegraph_Marker_Icon', PATH_site.$arrConf['marker.'][$strKey.'.']['image']);
										break;
									default:
										$objArrayMarker[$strKey] =& tx_pbimagegraph::factory('tx_pbimagegraph_Marker_'.ucfirst($strType));
								}
								$this->setMarkerProperties($objArrayMarker[$strKey], $arrConf['marker.'][$strKey.'.']);
								$this->setElementProperties($objArrayMarker[$strKey], $arrConf['marker.'][$strKey.'.']);
								$objMarker->add($objArrayMarker[$strKey]);
							}
						}
					}
					break;
				case 'icon':
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Icon', PATH_site.$arrConf['marker.']['image']);
					break;
				default:
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']));
			}
			$this->setMarkerProperties($objMarker, $arrConf['marker.']);
			$this->setElementProperties($objMarker, $arrConf['marker.']);
			if ($arrConf['marker.']['pointing']) {
				$objPointing =& $objRef->addNew('tx_pbimagegraph_Marker_Pointing_'.ucfirst($arrConf['marker.']['pointing']), array($arrConf['marker.']['pointing.']['radius'], $objMarker));
				$objSetMarker =& $objPointing;
			} else {
				$objSetMarker =& $objMarker;
			}
			$objRef->setMarker($objSetMarker);
		}
	}

	/**
	 * Set the range marker for ODO cObject
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setRangeMarker(&$objRef, $arrConf) {
		foreach($arrConf as $strKey=>$strValue) {
			$mixId = $strValue['id'] ? $strValue['id'] : FALSE;
			$objRef->addRangeMarker($strValue['min'], $strValue['max'], $mixId);
		}
	}

	/**
	 * Preprocess data before entering in a marker
	 *
	 * @param	object		The parent cObject
	 * @param	string		Type of preprocessing
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setDataPreprocessor(&$objRef, $strType, $arrConf) {
		switch($strType) {
			case 'array':
					$objRef->setDataPreProcessor(tx_pbimagegraph::factory('tx_pbimagegraph_DataPreprocessor_Array', array($arrConf)));
			break;
			default:
				$objRef->setDataPreProcessor(tx_pbimagegraph::factory('tx_pbimagegraph_DataPreprocessor_'.ucfirst($strType), $arrConf['format']));
		}
	}

	/**
	 * Return data styles for each dataset
	 * @param tx_pbimagegraph_Plot $objRef
	 * @param array $arrConf
	 * @return tx_pbimagegraph_Fill_Array
	 */
	private function getDataStyles($plotId, $arrConf) {
		// Die Styles müssen vom Provider kommen. Der liefert entweder direkt
		// ein Fill_Array, oder aber ein normales PHP-Array, das konvertiert wird
		$fillStyle = $this->getDataProvider()->getDataStyles($plotId, $arrConf);
		if(!$fillStyle instanceof tx_pbimagegraph_Fill_Array) {
			$fillStyle = $this->convertDataStyle($fillStyle);
		}

		return $fillStyle;
	}

	private function convertDataStyle($fillStyleArr) {
		$objFillStyle = tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
		foreach($fillStyleArr As $fillStyle) {
			$strId = $fillStyle['id'] ? $fillStyle['id'] : FALSE;
			switch($fillStyle['type']) {
				case 'color':
					$objFillStyle->addColor($fillStyle['color'], $strId);
				break;
				case 'gradient':
					$objFillStyle->addNew('gradient', $fillStyle['color'], $strId);
				break;
			}
		}
		return $objFillStyle;
	}
	/**
	 * Sets the fill style of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of fill style
	 * @param	array		Configuration of the fill style
	 */
	private function setFillStyle(&$objRef, $strValue, $arrConf, $strAction) {
		switch ($strValue) {
			case 'gradient':
				$intDirection = $this->readConstant('IMAGE_GRAPH_GRAD_'.strtoupper($arrConf['direction']));
				$strStartColor = $arrConf['startColor'];
				$strEndColor = $arrConf['endColor'];
				$intSolidColor = $arrConf['color'];
				$objFillStyle =& tx_pbimagegraph::factory('gradient', array($intDirection, $strStartColor, $strEndColor));
				break;
			case 'fill_array':
				// deprecated: use getDataStyle()
				$objFillStyle =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
				if (is_array($arrConf)) {
					$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
					foreach($arrKeys as $strKey) {
						$strType=$arrConf[$strKey];
						if (intval($strKey) && !strstr($strKey, '.')) {
							switch($strType) {
								case 'addColor':
									$strColor = $arrConf[$strKey.'.']['color'];
									$strId = $arrConf[$strKey.'.']['id']?$arrConf[$strKey.'.']['id']:FALSE;
									$objFillStyle->addColor($strColor, $strId);
								break;
								case 'gradient':
									$intDirection = $this->readConstant('IMAGE_GRAPH_GRAD_'.strtoupper($arrConf[$strKey.'.']['direction']));
									
									$strStartColor = $arrConf[$strKey.'.']['startColor'];
									$strEndColor = $arrConf[$strKey.'.']['endColor'];
									$intSolidColor = $arrConf[$strKey.'.']['color'];
									$strId = $arrConf[$strKey.'.']['id'];
									$objFillStyle->addNew('gradient', array($intDirection, $strStartColor, $strEndColor), $strId);
								break;
							}
						}
					}
				}
				break;
			case 'image':
				$strImage = $arrConf['image'];
				$objFillStyle =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Image', PATH_site.$strImage);
				break;
		}

		// Setze FillStyle in Element
		$objRef->$strAction($objFillStyle);

//		switch ($strAction) {
//			case 'setBackground':
//				$objRef->setBackground($objFillStyle);
//				break;
//			case 'setFillStyle':
//				$objRef->setFillStyle($objFillStyle);
//				break;
//			case 'setArrowFillStyle':
//				$objRef->setArrowFillStyle($objFillStyle);
//				break;
//			case 'setRangeMarkerFillStyle':
//				$objRef->setRangeMarkerFillStyle($objFillStyle);
//				break;
//		}
	}

	public static function readConstant($constName, $defaultValue='') {
		return defined($constName) ? constant($constName) : '';
	}
	/**
	 * Sets the line style of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of line style
	 * @param	array		Configuration of the line style
	 */
	function setLineStyle(&$objRef, $strValue, $arrConf, $strAction) {
		$arrConf['color'] = $arrConf['color']?$arrConf['color']:'red';
		$arrConf['color1'] = $arrConf['color1']?$arrConf['color1']:'red';
		$arrConf['color2'] = $arrConf['color2']?$arrConf['color2']:'white';
		switch($strValue) {
			case 'dashed':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Dashed', array($arrConf['color1'], $arrConf['color2']));
				break;
			case 'dotted':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Dotted', array($arrConf['color1'], $arrConf['color2']));
				break;
			case 'solid':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Solid', $arrConf['color']);
				if (isset($arrConf['thickness'])) {
					$objLineStyle->setThickness($arrConf['thickness']);
				}
				break;
			case 'array':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Array');
				if (is_array($arrConf)) {
					$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
					foreach($arrKeys as $strKey) {
						$strType=$arrConf[$strKey];
						if (intval($strKey) && !strstr($strKey, '.')) {
							switch($strType) {
								case 'addColor':
									$strColor = $arrConf[$strKey.'.']['color']?$arrConf[$strKey.'.']['color']:'red';
									$strId = $arrConf[$strKey.'.']['id']?$arrConf[$strKey.'.']['id']:FALSE;
									$objLineStyle->addColor($strColor, $strId);
								break;
							}
						}
					}
				}
				break;
		}
		switch($strAction) {
			case 'setBorderStyle':
				$objRef->setBorderStyle($objLineStyle);
				break;
			case 'setLineStyle':
				$objRef->setLineStyle($objLineStyle);
				break;
			case 'setArrowLineStyle':
				$objRef->setArrowLineStyle($objLineStyle);
				break;
		}
	}

	/**
	 * Shows shadow of the element
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the shadow
	 */
	function showShadow(&$objRef, $arrConf) {
		$strColor = $arrConf['color']?$arrConf['color']:'black@0.2';
		$intSize = $arrConf['size']?$arrConf['size']:'5';
		$objRef->showShadow($strColor, $intSize);
	}

	/**
	 * Sets the font of an element
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the font
	 */
	function setFont(&$objRef, $arrConf) {
		$strDefaultFont = $arrConf['default'];
		$strDefaultColor = $arrConf['default.']['color'];
		$intDefaultSize = $arrConf['default.']['size'];
		$intDefaultAngle = $arrConf['default.']['angle'];

		if ($strDefaultFont) {
			$Font =& $objRef->addNew('font', $strDefaultFont);
			$Font->setColor($strDefaultColor);
			$Font->setSize($intDefaultSize);
			$Font->setAngle($intDefaultAngle);
			$objRef->setFont($Font);
		}

		$strColor = $arrConf['color'];
		$intSize = $arrConf['size'];
		$intAngle = $arrConf['angle'];

		$objRef->setFontColor($strColor);
		$objRef->setFontSize($intSize);
		$objRef->setFontAngle($intAngle);
	}

	/**
	 * Sets the alignment of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Alignment
	 */
	function setAlignment(&$objRef, $strValue) {
		$strAlignment = IMAGE_GRAPH_ALIGN_CENTER_X;
		eval("\$strAlignment = IMAGE_GRAPH_ALIGN_".strtoupper($strValue).";");
		$objRef->setAlignment($strAlignment);
	}

	/**
	 * Sets the plot area for the legend
	 *
	 * @param	object 		Reference object
	 * @param	string		Name of the plot area
	 * @param	array		Array with multiple plot area's
	 */
	function setPlotarea(&$objRef, $strValue, $arrConf) {
		$Plotarea = '';
		if (is_array($arrConf)) {
			foreach ($arrConf as $strValue) {
//				eval("\$Plotarea =& self::\$ctx->".$strValue.";");
				$Plotarea =$this->ctx->offsetGet($strValue);
				$objRef->setPlotarea($Plotarea);
			}
		} else {
			//eval("\$Plotarea =& self::\$ctx->".$strValue.";");
			$Plotarea =$this->ctx->offsetGet($strValue);
			$objRef->setPlotarea($Plotarea);
		}
	}

	/**
	 * Sets the dataselector to specify which data should be displayed on the
     * plot as markers and which are not
	 *
	 * @param	object 		Reference object
	 * @param	string 		Type of selector
	 * @param	array		Configuration of the data selector
	 */
	function setDataSelector(&$objRef, $strValue, $arrConf) {
		switch($strValue) {
			case 'noZeros':
				$objRef->setDataSelector(tx_pbimagegraph::factory('tx_pbimagegraph_DataSelector_NoZeros'));
				break;
		}
	}

	/**
	 * Get Axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the data selector
	 */
	function getAxis(&$objRef, $arrConf) {
		$intAxis = 1;
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strKey).";");
			$objAxis =& $objRef->getAxis($intAxis);
			$this->setAxisProperties($objAxis, $strValue);
			$this->setElementProperties($objAxis, $strValue);
			if (is_array($strValue)) {
				$arrKeys=t3lib_TStemplate::sortedKeyList($strValue);
				foreach($arrKeys as $strKey) {
					$strCobjName=$strValue[$strKey];
					if (intval($strKey) && !strstr($strKey, '.')) {
						$arrConfAxis=$strValue[$strKey.'.'];
						switch($strCobjName) {
							case 'marker':
								$this->setAxisMarker($objRef, $intAxis, $arrConfAxis);
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * Set options for the label at a specific level
	 *
	 * 'showtext' TRUE or FALSE
	 * 'showoffset' TRUE or FALSE
	 * 'font' The font options as an associated array
	 * 'position' 'inside' or 'outside'
	 * 'format' To format the label text according to a sprintf statement
	 * 'dateformat' To format the label as a date, fx. j. M Y = 29. Jun 2005
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 * @param	integer		Level
	 */
	function setLabelOptions(&$objRef, $arrConf, $intLevel) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if ($strKey=='showtext' || $strKey=='showoffset') {
					$strValue=$strValue==1?TRUE:FALSE;
				}
				$arrLabelOptions[rtrim($strKey, '.')] = $strValue;
			}
		}
		$objRef->setLabelOptions($arrLabelOptions, $intLevel);
	}

	/**
	 * Set an interval for where labels are shown on the axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 * @param	integer		Level
	 */
	function setLabelInterval(&$objRef, $arrConf, $intLevel) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					$arrInterval[] = $strValue['value'];
				}
			}
			$objRef->setLabelInterval($arrInterval, $intLevel);
		} else {
			$objRef->setLabelInterval($arrConf, $intLevel);
		}
	}

	/**
	 * Set specific level for the axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 */
	function getAxisLevel(&$objRef, $arrConf) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					$this->setAxisProperties($objRef, $strValue, intval(rtrim($strKey, '.')));
				}
			}
		}
	}

	/**
	 * Adds a mark to the axis at the specified value
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the mark
	 */
	function axisAddMark(&$objRef, $arrConf) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					$strValue['value2'] = isset($strValue['value2'])?$strValue['value2']:FALSE;
					$strValue['text'] = isset($strValue['text'])?$strValue['text']:FALSE;
					$objRef->addMark($strValue['value'], $strValue['value2'], $strValue['text']);
				}
			}
		}
	}

	/**
	 * Sets the properties for the plot area
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the plot area
	 */
	function setPlotareaProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'axis':
					$this->getAxis($objRef, $strValue) ;
				break;
				case 'hideAxis':
					$objRef->hideAxis($strValue);
				break;
				case 'clearAxis':
					$objRef->clearAxis();
				break;
				case 'axisPadding':
					$objRef->setAxisPadding($strValue);
				break;
			}
		}
	}

	/**
	 * Set the properties for all elements
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setElementProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				// element
				case 'background':
					$this->setFillStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setBackground');
				break;
				case 'backgroundColor':
					$objRef->setBackgroundColor($strValue);
				break;
				case 'shadow':
					$this->showShadow($objRef, $arrConf[$strKey.'.']);
				break;
				case 'borderStyle':
					$this->setLineStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setBorderStyle');
				break;
				case 'borderColor':
					$objRef->setBorderColor($strValue);
				break;
				case 'lineStyle':
					$this->setLineStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setLineStyle');
				break;
				case 'lineColor':
					$objRef->setLineColor($strValue);
				break;
				case 'fillStyle':
					$this->setFillStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setFillStyle');
				break;
				case 'fillColor':
					$objRef->setFillColor($strValue);
				break;
				case 'font';
					$this->setFont($objRef, $arrConf[$strKey.'.']);
				break;
				case 'padding':
					$objRef->setPadding($strValue);
				break;
				case 'alignment':
					$this->setAlignment($objRef, $strValue);
				break;
			}
		}
	}

	/**
	 * Set the properties for all plot elements
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setPlotProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				// element
				case 'title':
					$objRef->setTitle($strValue);
					break;
				case 'dataSelector':
					$this->setDataSelector($objRef, $strValue, $arrConf[$strKey.'.']);
					break;
			}
		}
	}

	/**
	 * Set the properties for the Axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis
	 * @param	integer		Level
	 */
	function setAxisProperties(&$objRef, $arrConf, $intLevel=1) {
		foreach($arrConf as $strKey => $strValue) {
			if (strpos($strKey, '.') === FALSE || (strpos($strKey, '.') !== FALSE && !isset($arrConf[rtrim($strKey, '.')]))) {
				$strKey = rtrim($strKey, '.');
				switch($strKey) {
					case 'level':
						$this->getAxisLevel($objRef, $strValue);
					break;
					case 'label':
						$intLabel = IMAGE_GRAPH_LABEL_ZERO;
						eval("\$intLabel = IMAGE_GRAPH_LABEL_".strtoupper($strValue).";");
						$objRef->showLabel($intLabel);
					break;
					case 'dataPreProcessor':
						$this->setDataPreprocessor($objRef, $strValue, $arrConf['dataPreProcessor.']);
					break;
					case 'forceMinimum':
						$objRef->forceMinimum($strValue);
					break;
					case 'forceMaximum':
						$objRef->forceMaximum($strValue);
					break;
					case 'showArrow':
						$objRef->showArrow();
					break;
					case 'hideArrow':
						$objRef->hideArrow();
					break;
					case 'labelInterval':
						$this->setLabelInterval($objRef, $strValue, $intLevel);
					break;
					case 'labelOption':
						//$objRef->setLabelOption($option, $value, $level = 1);
					break;
					case 'labelOptions':
						$this->setLabelOptions($objRef, $strValue, $intLevel);
					break;
					case 'title':
						$objRef->setTitle($strValue, $arrConf['title.']);
					break;
					case 'fixedSize':
						$objRef->setFixedSize($strValue);
					break;
					case 'addMark':
						$this->axisAddMark($objRef, $strValue);
					break;
					case 'tickOptions':
						$objRef->setTickOptions($strValue['start'], $strValue['end'], $intLevel);
					break;
					case 'inverted':
						$objRef->setInverted($strValue);
					break;
					case 'axisIntersection':
						$objRef->setAxisIntersection($strValue);
					break;
				}
			}
		}
	}

	/**
	 * Set the properties for the Axis Marker Area
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis Marker Area
	 */
	function setAxisMarkerAreaProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'lowerBound':
					$objRef->setLowerBound($strValue);
				break;
				case 'upperBound':
					$objRef->setUpperBound($strValue);
				break;
			}
		}
	}

	/**
	 * Set the properties for the Axis Marker Line
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis Marker Line
	 */
	function setAxisMarkerLineProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'value':
					$objRef->setValue($strValue);
				break;
			}
		}
	}

	/**
	 * Set the properties for a marker
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setMarkerProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'size':
					$objRef->setSize($strValue);
					break;
				case 'secondaryMarker':
					//$objRef->setSecondaryMarker($secondaryMarker);
					break;
				case 'maxRadius':
					$objRef->setMaxRadius($strValue);
					break;
				case 'pointX':
					$objRef->setPointX($strValue);
					break;
				case 'pointY':
					$objRef->setPointY($strValue);
					break;
				case 'markerStart':
					//$objRef->setMarkerStart($markerStart);
					break;
				case 'dataPreProcessor':
					$this->setDataPreprocessor($objRef, $strValue, $arrConf['dataPreProcessor.']);
					break;
			}
		}
	}

	/**
	 * Sets the properties for the legend
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the legend
	 */
	function setLegendProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'alignment':
					$this->setAlignment($objRef, $strValue);
				break;
				case 'plotarea':
					$this->setPlotarea($objRef, $strValue, $arrConf[$strKey.'.']);
				break;
				case 'showMarker':
					$objRef->setShowMarker($strValue);
				break;
			}
		}
	}

	/**
	 * Set the properties for cObject BAR
	 *
	 * @param	object		The parent BAR cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	private function setBarProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'spacing':
					$objRef->setSpacing($strValue);
					break;
				case 'barWidth.':
					$strUnit = $strValue['unit']?$strValue['unit']:FALSE;
					$objRef->setBarWidth($strValue['value'], $strUnit);
					break;
			}
		}
	}

	/**
	 * Set the properties for cObject BOXWHISKER
	 *
	 * @param	object		The parent BOXWHISKER cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	private function setBoxWhiskerProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'whiskerSize':
					$strSize = $strValue ? $strValue : FALSE;
					$objRef->setWhiskerSize($strSize);
					break;
			}
		}
	}

	/**
	 * Set the properties for cObject PIE
	 *
	 * @param	object		The parent PIE cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	private function setPieProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'explode.':
					$objRef->explode($strValue['radius'], isset($strValue['id'])?$strValue['id']:FALSE);
					break;
				case 'startingAngle.':
					$strDirection = $strValue['direction']?$strValue['direction']:'ccw';
					$objRef->setStartingAngle($strValue['angle'], $strDirection);
					break;
				case 'diameter':
					$objRef->setDiameter($strValue);
					break;
			}
		}
	}

	/**
	 * Set the properties for cObject ODO
	 *
	 * @param	object		The parent ODO cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	private function setOdoProperties(&$objRef, $arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'center.':
					$objRef->setCenter($strValue['x'], $strValue['y']);
					break;
				case 'range.':
					$objRef->setRange($strValue['min'], $strValue['max']);
					break;
				case 'angles.':
					$objRef->setAngles($strValue['offset'], $strValue['width']);
					break;
				case 'radiusWidth':
					$objRef->setRadiusWidth($strValue);
					break;
				case 'arrowSize.':
					$objRef->setArrowSize($strValue['length'], $strValue['width']);
					break;
				case 'arrowMarker.':
					$intAxis = 0;
					eval("\$intAxis = IMAGE_GRAPH_".strtoupper($strValue['useValue']).";");
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Value', $intAxis);
					$objRef->setArrowMarker($objMarker);
					$this->setElementProperties($objMarker, $strValue);
					$this->setMarkerProperties($objMarker, $strValue);
					break;
				case 'tickLength':
					$objRef->setTickLength($strValue);
					break;
				case 'axisTicks':
					$objRef->setAxisTicks($strValue);
					break;
				case 'arrowLineStyle':
					$this->setLineStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setArrowLineStyle');
					break;
				case 'arrowFillStyle':
					$this->setFillStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setArrowFillStyle');
					break;
				case 'rangeMarker.':
					$this->setRangeMarker($objRef, $strValue);
					break;
				case 'rangeMarkerFillStyle':
					$this->setFillStyle($objRef, $strValue, $arrConf[$strKey.'.'], 'setRangeMarkerFillStyle');
					break;
			}
		}
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_Builder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/plot/class.tx_rnbase_plot_Builder.php']);
}
