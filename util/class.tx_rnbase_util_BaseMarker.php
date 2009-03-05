<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2008 Rene Nitzsche
 *  Contact: rene@system25.de
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
 * Base class for Markers.
 */
class tx_rnbase_util_BaseMarker {
  private $defaultMarkerArr = array();
  /** Array for dummy objects */
  private static $emptyObjects = array();
  
  function tx_rnbase_util_BaseMarker() {
  }

  /**
   * Initialisiert die Labels für die eine Model-Klasse
   *
   * @param string $classname child class of tx_rnbase_model_base or null 
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param array $defaultMarkerArr
   */
  protected function prepareLabelMarkers($classname, &$formatter, $confId, $marker, $defaultMarkerArr = 0) {
    $arr1 = self::_getClassLabelMarkers($classname, $formatter, $confId, $defaultMarkerArr, $marker);
    $this->defaultMarkerArr = array_merge($arr1, $this->defaultMarkerArr);
    return $this->defaultMarkerArr;
  }

  /**
   * Initialisiert die Labels für die eine Model-Klasse
   * 
   * @param string $classname child class of tx_rnbase_model_base or null 
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param string $confId
   * @param array $defaultMarkerArr
   * @param string $marker
   * @return array
   */
  protected static function _getClassLabelMarkers($classname, &$formatter, $confId, $defaultMarkerArr = 0, $marker = 'PROFILE') {
    $ret = array();
    if($classname) {
      $clazz = tx_div::makeInstanceClassName($classname);
      $obj = new $clazz(array());
      $cols = $obj->getTCAColumns();
      $labelArr = array();
      foreach ($cols as $col => $colArr) {
        $labelId = str_replace('.','_', $confId.$col);
        $label = $formatter->configurations->getLL($labelId);
        $labelArr['label_'.$col] = strlen($label) ? $label : $formatter->configurations->getLL($colArr['label']);
      }
      $ret = $formatter->getItemMarkerArrayWrapped($labelArr, $confId , 0, $marker.'_');
    }
    return $ret;
  }

  /**
   * Return label markers defined by Typoscript
   *
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param string $confId
   * @param string $marker
   * @param array $defaultMarkerArr
   * @return array
   */
  public function initTSLabelMarkers(&$formatter, $confId, $marker, $defaultMarkerArr = 0) {
    $arr1 = array();
    if($labels = $formatter->configurations->get($confId.'labels')) {
      $labels = t3lib_div::trimExplode(',',$labels);
      $labelArr = array();
      foreach ($labels as $label) {
        // Für die Abfrage nach den Labels dürfen keine Punkte als Trenner verwendet werden
        // Daher Umwandlung in Underscores
        $labelId = str_replace('.','_', $confId.'label.'.$label);
        $labelArr['label_'.$label] = $formatter->configurations->getLL($labelId);
      }
      $arr1 = $formatter->getItemMarkerArrayWrapped($labelArr, $confId , 0, $marker.'_');
    }
//    t3lib_div::debug($labelId, 'tx_rnbase_util_BaseMarker');
    $this->defaultMarkerArr = array_merge($arr1, $this->defaultMarkerArr);
    return $this->defaultMarkerArr;
  }

	/**
	 * Link setzen
	 *
	 * @param array $markerArray
	 * @param array $subpartArray
	 * @param array $wrappedSubpartArray
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $linkId
	 * @param string $marker
	 * @param array $parameterArr
	 */
	public function initLink(&$markerArray, &$subpartArray, &$wrappedSubpartArray, $formatter, $confId, $linkId, $marker, $parameterArr) {
		$linkObj =& $formatter->configurations->createLink();
		$token = md5(microtime());
		$linkObj->label($token);
		$links = $formatter->configurations->get($confId.'links.');
		$linkMarker = $marker . '_' . strtoupper($linkId).'LINK';
		if($links[$linkId] || $links[$linkId.'.']) {
			$pid = $formatter->cObj->stdWrap($links[$linkId.'.']['pid'], $links[$linkId.'.']['pid.']);
			$qualifier = $links[$linkId.'.']['qualifier'];
			if($qualifier) $linkObj->designator($qualifier);
			$target = $links[$linkId.'.']['target'];
			if($target) $linkObj->target($target);
			$linkObj->destination(intval($pid) ? $pid : $GLOBALS['TSFE']->id); // Das Ziel der Seite vorbereiten
			if($links[$linkId.'.']['fixedUrl'])
				$linkObj->destination($links[$linkId.'.']['fixedUrl']); // feste URL für externen Link

			$linkObj->parameters($parameterArr);
			// Zusätzliche Parameter für den Link
			$atagParams = $links[$linkId.'.']['atagparams.'];
			if(is_array($atagParams)) {
				$linkObj->attributes($atagParams);
			}
			// KeepVars prüfen
			// Per Default sind die KeepVars aktiviert. Mit useKeepVars == 0 können sie wieder entfernt werden
			if(!$links[$linkId.'.']['useKeepVars']) {
				$linkObj->overruled();
			}
			elseif($links[$linkId.'.']['useKeepVars.']) {
				// Sonderoptionen für KeepVars gesetzt
				$newKeepVars = array();
				$keepVars = $formatter->configurations->getKeepVars();
				$allow = $links[$linkId.'.']['useKeepVars.']['allow'];
				$deny = $links[$linkId.'.']['useKeepVars.']['deny'];
				if($allow) {
					$allow = t3lib_div::trimExplode(',', $allow);
					foreach($allow As $allowed) {
						$newKeepVars[$allowed] = $keepVars->offsetGet($allowed);
					}
				}
				elseif($deny) {
					$deny = array_flip(t3lib_div::trimExplode(',', $deny));
					$keepVarsArr = $keepVars->getArrayCopy();
					foreach($keepVarsArr As $key => $value) {
						if(!array_key_exists($key, $deny))
							$newKeepVars[$key] = $value;
					}
				}
				$linkObj->overruled($newKeepVars);
			}

			$wrappedSubpartArray['###'.$linkMarker . '###'] = explode($token, $linkObj->makeTag());
			$markerArray['###'.$linkMarker . 'URL###'] = $linkObj->makeUrl(false);
		}
		else {
			self::disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, false);
		}
	}
	/**
	 * Remove Link-Markers
	 *
	 * @param string $linkMarker
	 * @param boolean $remove true removes the link with label
	 */
	public function disableLink(&$markerArray, &$subpartArray, &$wrappedSubpartArray, $linkMarker, $remove) {
  	if($remove)
			$subpartArray['###'.$linkMarker . '###'] = '';
  	else
			$wrappedSubpartArray['###'.$linkMarker . '###'] = array('', '');
		$markerArray['###'.$linkMarker . 'URL###'] = '';
	}

  /**
   * Den PageBrowser in ein Template integrieren
   *
   * @param string $template
   * @param tx_rnbase_util_PageBrowser $pagebrowser
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param string $confId
   * @return string
   */
  static function fillPageBrowser($template, &$pagebrowser, &$formatter, $confId) {
  	if(strlen(trim($template)) == 0) return '';
    if(!is_object($pagebrowser) || !is_object($pagebrowser->getMarker())) {
      return '';
    }
    $marker = $pagebrowser->getMarker();
    $out = $marker->parseTemplate($template, $formatter, $confId);
    return $out;
  }
  
  /**
   * Liefert das DefaultMarkerArray
   *
   * @return array
   */
  protected function getDefaultMarkerArray(){
    return $this->defaultMarkerArr;
  }

  /**
   * Returns an empty instance of given modelclass. This object must not be 
   * change, since it is cached. You will always get the same instance if you
   * call this method for the same class more than once.
   * The object will be initialized with a uid=0. The record-array will 
   * contain all tca-defined fields with an empty string as value.
   *
   * @param string $classname
   * @return object
   */
	protected static function getEmptyInstance($classname) {
		if(!is_object(self::$emptyObjects[$classname])) {
    	$clazz = tx_div::makeInstanceClassName($classname);
    	$dummy = new $clazz(array('uid' => 0));
    	$cols = $dummy->getColumnNames();
    	for($i=0, $cnt = count($cols); $i < $cnt; $i++) {
    		$dummy->record[$cols[$i]] = '';
    	}
    	self::$emptyObjects[$classname] = $dummy;
		}
		return self::$emptyObjects[$classname];
	}

	/**
	 *
	 * @param tx_rnbase_util_FormatUtil $formatter 
	 */
	public static function callModules($template, &$markerArray, &$subpartArray, &$wrappedSubpartArray, &$params, &$formatter) {
		self::callModuleSubparts($template, $subpartArray, $wrappedSubpartArray, $params, $formatter);
		self::callModuleMarkers($template, $markerArray, $params, $formatter);
	}
	/**
	 * Call services for single markers
	 * 
	 * @param string $template
	 * @param array $markerArray
	 * @param array $params
	 * @param tx_rnbase_util_FormatUtil $formatter 
	 */
	protected static function callModuleMarkers($template, &$markerArray, &$params, &$formatter) {
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);
		foreach ($allSingleMarkers as $marker) {
			if (preg_match('/MARKERMODULE__([A-Z0-9_-])*/', $marker)) {
				$module = t3lib_div::makeInstanceService('markermodule',substr($marker, 14));
				if (is_object($module)) {
					$subTemplate = $formatter->cObj->getSubpart($template,'###'.$marker.'###');
					$value = $module->getMarkerValue($params, $formatter);
					if($value !== false)
						$markerArray['###' . $marker . '###'] =  $value;
				}
			}
			elseif(preg_match('/LABEL_.*/',$marker)) {
				$markerArray['###'.$marker.'###'] = $formatter->configurations->getLL(strtolower($marker));
			}
		}
	}

	protected static function callModuleSubparts($template, &$subpartArray, &$wrappedSubpartArray, &$params, &$formatter) {
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		foreach ($allMarkers as $marker) {
			if (preg_match('/MARKERMODULE__([A-Z0-9_-])*/', $marker)) {
				$module = t3lib_div :: makeInstanceService('markermodule',substr($marker, 14));
				if (is_object($module)) {
					$subTemplate = $formatter->cObj->getSubpart($template,'###'.$marker.'###');
					$subpart = $module->parseTemplate($subTemplate, $params, $formatter);
					if($subpart !== false)
						if(is_array($subpart))
							$wrappedSubpartArray['###' . $marker . '###'] = $subpart;
						else
							$subpartArray['###' . $marker . '###'] = $subpart;
				}
			}
		}
	}
	/**
	 * @param string $template
	 * @param string $markerPrefix a string like MATCH_HOME
	 * @return boolean
	 */
	public static function containsMarker($template, $markerPrefix) {
		return (preg_match('/###'.$markerPrefix.'([A-Z0-9_-])*/', $template)) > 0;
	}
	/**
	 * Start TimeTrack section
	 *
	 * @param string $message
	 */
	protected function pushTT($message) {
		if(is_object($GLOBALS['TT']))
			$GLOBALS['TT']->push(get_class($this), $message);
	}
	/**
	 * End TimeTrack section
	 */
	protected function pullTT() {
		if(is_object($GLOBALS['TT']))
			$GLOBALS['TT']->pull();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']);
}
?>