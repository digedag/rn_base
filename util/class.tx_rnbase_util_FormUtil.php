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
 * Contains utility functions for HTML-Forms
 */
class tx_rnbase_util_FormUtil {

  /**
   * Creates a HTML-Select.
   */
  function createSelect($name, $arr, $attr='class="inputField"') {
    $out = '<select name="' . $name . '" ' . $attr . '>';
    $value = $arr[1];
    // Die Options ermitteln
    foreach($arr[0] As $key => $val){
      $sel = '';
      if (strval($value) == strval($key)) $sel = 'selected="selected"';
      $out .= '<option value="' . $key . '" ' . $sel . '>' . $val . '</option>';
    }

    $out .= '</select>';

    return $out;
  }
  /**
   * Returns Array as Hiddenfields
   */
  function getAsHiddenFields($arr, $qualifier = '') {
    $out = '';
    foreach($arr As $key => $value) {
      $key = strlen($qualifier) > 0 ? $qualifier.'['.$key.']' : $key;
      $out .= '
              <input type="hidden" name="' . $key . '" value="' . $value . '">' ;
    }
    return $out;
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormUtil.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormUtil.php']);
}
?>
