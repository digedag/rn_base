<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_BaseMarker');

/**
 * A generic marker class. 
 */
class tx_rnbase_util_SimpleMarker extends tx_rnbase_util_BaseMarker {
	public function __construct($options = array()) {
		if(array_key_exists('classname', $options))	
			$this->setClassname($options['classname']);
	}

	/**
	 * @param string $template das HTML-Template
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config
	 * @param string $marker Name des Markers
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$item, &$formatter, $confId, $marker) {
		if(!is_object($item)) {
			if($this->classname) return '';
			// Ist kein Item vorhanden wird ein leeres Objekt verwendet.
			$item = self::getEmptyInstance($this->classname);
		}

		// Es wird das MarkerArray mit den Daten des Teams gefüllt.
		$ignore = self::findUnusedCols($item->record, $template, $marker);
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , $ignore, $marker.'_',$item->getColumnNames());
		$wrappedSubpartArray = array();
		$subpartArray = array();
		$this->prepareLinks($item, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter, $template);

		$out = self::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * Links vorbereiten
	 *
	 * @param tx_cfcleague_models_Stadium $item
	 * @param string $marker
	 * @param array $markerArray
	 * @param array $wrappedSubpartArray
	 * @param string $confId
	 * @param tx_rnbase_util_FormatUtil $formatter
	 */
	protected function prepareLinks($item, $marker, &$markerArray, &$subpartArray, &$wrappedSubpartArray, $confId, $formatter, $template) {
		$pluginData = $formatter->getConfigurations()->getCObj()->data;
		$formatter->getConfigurations()->getCObj()->data = $item->record;

		$linkIds = $formatter->getConfigurations()->getKeyNames($confId.'links.');
		for($i=0, $cnt=count($linkIds); $i < $cnt; $i++) {
			$linkId = $linkIds[$i];
			// Check if link is defined in template
			if(!self::checkLinkExistence($linkId, $marker, $template))
				continue;

			// Die Parameter erzeugen
			$params = array();
			$paramMap = $formatter->getConfigurations()->get($confId.'links.'.$linkId.'._cfg.params.');
			foreach($paramMap As $paramName => $colName) {
				if(is_scalar($colName) && array_key_exists($colName, $item->record))
					$params[$paramName] = $item->record[$colName];
				elseif(is_array($colName)) {
					$paramName = substr($paramName, 0, strlen($paramName)-1);
					$params[$paramName] = $this->createParam($paramName, $colName, $item);
				}
			}
/*			$paramNames = $formatter->getConfigurations()->getKeyNames($confId.'links.'.$linkId.'._cfg.params.');
			foreach($paramNames As $paramName) {
				$colName = $formatter->getConfigurations()->get($confId.'links.'.$linkId.'._cfg.params.'.$paramName, false);
				if($colName)
					$params[$paramName] = $item->record[$colName];
			}
*/

			if($item->isPersisted()) {
				$this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, $linkId, $marker, $params, $template);
			}
			else {
				$linkMarker = $marker . '_' . strtoupper($linkId).'LINK';
				$remove = intval($formatter->getConfigurations()->get($confId.'links.'.$linkId.'.removeIfDisabled')); 
				$this->disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, $remove > 0);
			}
		}
		$formatter->getConfigurations()->getCObj()->data = $pluginData;
	}

	/**
	 * Create a user defined link parameter. This is an example Typoscript config:
	 *  links.show {
	 *  	_cfg.params.param1.class = tx_mkeasy_marker_EasyDoc
	 *  	_cfg.params.param1.method = createSecureLinkParam
	 *  }
	 *  In this case the call is static. It is also possible to subclass SimpleMarker 
	 *  an use the keyword "this" as class. In that case the current marker instance is
	 *  called.
	 *
	 * @param string $paramName
	 * @param array $cfgArr
	 * @param object $item
	 */
	protected function createParam($paramName, $cfgArr, $item) {
		$ret = '';
		$clazz = $cfgArr['class'];
		$method = $cfgArr['method'];
		if($clazz == 'this') {
			$ret = $this->$method($paramName, $cfgArr, $item);
		}
		else {
			tx_rnbase::load($clazz);
			$ret = call_user_func_array(array($clazz,$method),array($paramName, $cfgArr, $item));
		}
		return $ret;
	}
	/**
	 * Set classname for items
	 * @param $name
	 */
	public function setClassname($name) {
		$this->classname = $name;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SimpleMarker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_SimpleMarker.php']);
}
?>