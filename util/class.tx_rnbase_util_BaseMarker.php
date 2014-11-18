<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2009 Rene Nitzsche
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Templates');



/**
 * Base class for Markers.
 */
class tx_rnbase_util_BaseMarker {
	private $defaultMarkerArr = array();
	/** Array for dummy objects */
	private static $emptyObjects = array();

	public function __construct() {
	}

  /**
   * Initialisiert die Labels für die eine Model-Klasse
   *
   * @param string $classname child class of tx_rnbase_model_base or NULL
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
   * @param string $classname child class of tx_rnbase_model_base or NULL
   * @param tx_rnbase_util_FormatUtil $formatter
   * @param string $confId
   * @param array $defaultMarkerArr
   * @param string $marker
   * @return array
   */
  protected static function _getClassLabelMarkers($classname, &$formatter, $confId, $defaultMarkerArr = 0, $marker = 'PROFILE') {
    $ret = array();
    if($classname) {
      $obj = tx_rnbase::makeInstance($classname, array());
      $cols = $obj->getTCAColumns();
      $labelArr = array();
      foreach ($cols as $col => $colArr) {
        $labelId = str_replace('.', '_', $confId.$col);
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
      $labels = t3lib_div::trimExplode(',', $labels);
      $labelArr = array();
      foreach ($labels as $label) {
        // Für die Abfrage nach den Labels dürfen keine Punkte als Trenner verwendet werden
        // Daher Umwandlung in Underscores
        $labelId = str_replace('.', '_', $confId.'label.'.$label);
        $labelArr['label_'.$label] = $formatter->configurations->getLL($labelId);
      }
      $arr1 = $formatter->getItemMarkerArrayWrapped($labelArr, $confId , 0, $marker.'_');
    }
//    t3lib_div::debug($labelId, 'tx_rnbase_util_BaseMarker');
    $this->defaultMarkerArr = array_merge($arr1, $this->defaultMarkerArr);
    return $this->defaultMarkerArr;
  }
  /**
   * Returns an array with all column names not used in template
   *
   * @param array $record
   * @param string $template
   * @param string $marker
   * @return array
   */
  public static function findUnusedCols(&$record, $template, $marker) {
		$ignore = array();
		$minfo = self::containsMarker($template, $marker.'___MINFO');
		$minfoArr = array();
		foreach($record As $key=>$value) {
			if($minfo) {
				$minfoArr[$key] = $marker.'_'.strtoupper($key);
			}
			if(!self::containsMarker($template, $marker.'_'.strtoupper($key)))
				$ignore[] = $key;
		}
		if($minfo) {
			tx_rnbase::load('tx_rnbase_util_Debug');
			$record['__MINFO'] = tx_rnbase_util_Debug::viewArray($minfoArr);
		}
		return $ignore;
  }

  protected static $token = '';
  /**
   * Returns a token string.
   * @return string
   */
  protected static function getToken() {
  	if(!self::$token)
  		self::$token = md5(microtime());
  	return self::$token;
  }
  /**
   * Check existing of a link or url in template string.
   * @param string $linkId
   * @param string $marker
   * @param string $template
   * @param boolean $makeUrl is set to TRUE if url was found
   * @param boolean $makeLink is set to TRUE if link was found
   * @return boolean is TRUE if link or url was found
   */
  public static function checkLinkExistence($linkId, $marker, $template, &$makeUrl=TRUE, &$makeLink=TRUE) {
		$linkMarker = $marker . '_' . strtoupper($linkId).'LINK';
		// Do we need links
		$makeUrl = $makeLink = TRUE;
		if($template) {
			$makeLink = self::containsMarker($template, $linkMarker.'#');
			$makeUrl = self::containsMarker($template, $linkMarker.'URL');
		}
		if(!$makeLink && !$makeUrl) {
			return FALSE; // Nothing to do
		}
		return $linkMarker;
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
	 * @param string $template the HTML template used. This enabled check if link is necessary.
	 */
	public function initLink(&$markerArray, &$subpartArray, &$wrappedSubpartArray, $formatter, $confId, $linkId, $marker, $parameterArr, $template='') {
		$linkMarker = self::checkLinkExistence($linkId, $marker, $template, $makeUrl, $makeLink);
/*
		$linkMarker = $marker . '_' . strtoupper($linkId).'LINK';
		// Do we need links
		$makeUrl = $makeLink = TRUE;
		if($template) {
			$makeLink = self::containsMarker($template, $linkMarker);
			$makeUrl = self::containsMarker($template, $linkMarker.'URL');
		}
		if(!$makeLink && !$makeUrl) {
			return; // Nothing to do
		}
*/
		if(!$linkMarker) {
			return; // Nothing to do
		}

		$linkObj =& $formatter->getConfigurations()->createLink();
		$token = self::getToken();
		$linkObj->label($token);
		$links = $formatter->configurations->get($confId.'links.');
		if($links[$linkId] || $links[$linkId.'.']) {
			$linkObj->initByTS($formatter->getConfigurations(), $confId.'links.'.$linkId.'.', $parameterArr);

			if($makeLink)
				$wrappedSubpartArray['###'.$linkMarker . '###'] = explode($token, $linkObj->makeTag());
			if($makeUrl)
				$markerArray['###'.$linkMarker . 'URL###'] = $linkObj->makeUrl(
						$formatter->getConfigurations()->getBool($confId.'links.'.$linkId.'.applyHtmlSpecialChars', FALSE, FALSE)
					);
		}
		else {
			self::disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, FALSE);
		}
	}
	/**
	 * Remove Link-Markers
	 *
	 * @param string $linkMarker
	 * @param boolean $remove TRUE removes the link with label
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
	public static function fillPageBrowser($template, &$pagebrowser, &$formatter, $confId) {
		if(strlen(trim($template)) == 0) return '';
		if(!is_object($pagebrowser) || !is_object($pagebrowser->getMarker())) {
			return '';
		}
		// Markerklasse kann per TS gesetzt werden
		$markerClass = $formatter->getConfigurations()->get($confId.'markerclass');

		$marker = $markerClass ? $pagebrowser->getMarker($markerClass) : $pagebrowser->getMarker();
		if(!is_object($marker)) {
			return '';
		}

		$out = $marker->parseTemplate($template, $formatter, $confId);
		return $out;
	}

	/**
	 * Returns the filled template for a character browser
	 * @param string $template
	 * @param tx_rnbase_configurations $configurations
	 */
	public static function fillCharBrowser($template, $markerArray, $pagerData, $curr_pointer, $configurations, $confId) {
		if(!$template) return '';
		$pagerItems = $pagerData['list'];
		if(!is_array($pagerItems) || !count($pagerItems)) return '';

		$out = array();
		$link = $configurations->createLink(); // Link auf die eigene Seite
		$link->initByTS($configurations, $confId.'link.', array());
//		$link->destination($GLOBALS['TSFE']->id); // Das Ziel der Seite vorbereiten
		$token = md5(microtime());
		$link->label($token);
		$emptyArr = array();

		$pagerName = $pagerData['pointername'] ? $pagerData['pointername'] : 'charpointer';

		while(list($pointer, $size) = each($pagerItems)) {
			$myMarkerArray = $markerArray;
			$myMarkerArray['###PB_ITEM###'] = $pointer;
			$myMarkerArray['###PB_ITEM_SIZE###'] = $size;

			if(strcmp($pointer, $curr_pointer)) {
				$link->parameters(array($pagerName => $pointer));
				$wrappedSubpartArray['###PB_ITEM_LINK###'] = explode($token, $link->makeTag());
			}
			else
				$wrappedSubpartArray['###PB_ITEM_LINK###'] = $emptyArr;
			$out[] = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $myMarkerArray, $emptyArray, $wrappedSubpartArray);
		}

		return implode($configurations->get($confId.'implode'), $out);
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
    	$dummy = tx_rnbase::makeInstance($classname, array('uid' => 0));
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
	protected static function callModuleMarkers($template, &$markerArray, &$params, $formatter) {
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_\-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);
		preg_match_all('!\###([A-Z0-9_\-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);

		$suffixesToTry = self::getSuffixesToTry($formatter->getConfigurations());
		foreach ($allSingleMarkers as $marker) {
			if (preg_match('/MARKERMODULE__([A-Z0-9_\-])*/', $marker)) {
				$module = t3lib_div::makeInstanceService('markermodule', substr($marker, 14));
				if (is_object($module)) {
					$subTemplate = tx_rnbase_util_Templates::getSubpart($template, '###'.$marker.'###');
					$value = $module->getMarkerValue($params, $formatter);
					if($value !== FALSE)
						$markerArray['###' . $marker . '###'] =  $value;
				}
			}
			elseif(preg_match('/LABEL_.*/', $marker)) {
				$markerArray['###'.$marker.'###'] = ''; // remove marker per default
				foreach ($suffixesToTry as $suffix) {
					$completeKey = $marker.$suffix;
					// Hier kommt immer ein leerer String zurück, weil T3 keinen Alternativ-String unterstützt
					$translation = $formatter->getConfigurations()->getLL(strtolower($completeKey));
					if($translation !== '') {
						$markerArray['###'.$marker.'###'] = $translation;
						break;
					}
				}
			}
		}
	}

	protected static function callModuleSubparts($template, &$subpartArray, &$wrappedSubpartArray, &$params, &$formatter) {
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_\-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		foreach ($allMarkers as $marker) {
			if (preg_match('/MARKERMODULE__([A-Z0-9_\-])*/', $marker)) {
				$module = t3lib_div :: makeInstanceService('markermodule', substr($marker, 14));
				if (is_object($module)) {
					$subTemplate = $formatter->cObj->getSubpart($template, '###'.$marker.'###');
					$subpart = $module->parseTemplate($subTemplate, $params, $formatter);
					if($subpart !== FALSE)
						if(is_array($subpart))
							$wrappedSubpartArray['###' . $marker . '###'] = $subpart;
						else
							$subpartArray['###' . $marker . '###'] = $subpart;
				}
			}
		}
	}
	/**
	 * Gets an ordered list of language label suffixes that should be tried to
	 * get localizations in the preferred order of formality.
	 *
	 * Method copied from Tx_Oelib_SalutationSwitcher of Oliver Klee
	 *
	 * @param tx_rnbase_configurations $configurations
	 * @return array ordered list of suffixes from "", "_formal" and "_informal", will not be empty
	 */
	private static function getSuffixesToTry($configurations) {
		$suffixesToTry = array();
		$salutation = $configurations->get('salutation');

		if ($salutation	&& ($salutation == 'informal')) {
			$suffixesToTry[] = '_informal';
		}
		$suffixesToTry[] = '_formal';
		$suffixesToTry[] = '';

		return $suffixesToTry;
	}

	/**
	 * @param string $template
	 * @param string $markerPrefix a string like MATCH_HOME
	 * @return boolean
	 */
	public static function containsMarker($template, $markerPrefix) {
		return (strpos($template, '###'.$markerPrefix) !== FALSE);
//		return (preg_match('/###'.$markerPrefix.'([A-Z0-9_-])*/', $template)) > 0;
	}
	/**
	 * Start TimeTrack section
	 *
	 * @param string $message
	 */
	protected function pushTT($message) {
		tx_rnbase_util_Misc::pushTT(get_class($this), $message);
	}
	/**
	 * End TimeTrack section
	 */
	protected function pullTT() {
		tx_rnbase_util_Misc::pullTT();
	}

	public static function substituteMarkerArrayCached($content, $markContentArray=array(), $subpartContentArray=array(), $wrappedSubpartContentArray=array())	{
		return tx_rnbase_util_Templates::substituteMarkerArrayCached($content, $markContentArray, $subpartContentArray, $wrappedSubpartContentArray);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']);
}
