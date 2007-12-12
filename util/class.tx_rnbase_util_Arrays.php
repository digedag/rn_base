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
 * Contains utility functions for ArrayObject
 */
class tx_rnbase_util_Arrays {

  /**
   * Overwrite some of the array values
   *
   * Overwrite a selection of the values by providing new ones 
   * in form of a data structure of the tx_div hash family.
   *  
   * @param    mixed    hash array, SPL object or hash string ( i.e. "key1 : value1, key2 : valu2, ... ") 
   * @param    string   possible split charaters in case the first parameter is a hash string 
   * @return   void
   */
  function overwriteArray(&$arrayObj, $hashData, $splitCharacters = ',;:') {
    $array = tx_div::toHashArray($hashData, $splitCharacters);
    foreach((array) $array as $key => $value) {
      $arrayObj->offsetSet($key, $value);
    }
  }


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Arrays.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Arrays.php']);
}

?>