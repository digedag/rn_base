<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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
   * Liefert das DefaultMarkerArray
   *
   * @return array
   */
  protected function getDefaultMarkerArray(){
    return $this->defaultMarkerArr;
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_BaseMarker.php']);
}
?>