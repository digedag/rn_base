<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Rene Nitzsche
 *  Contact: rene@system25.de
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
 * Baseclass for all actions. An Actions is intended to be the controller
 * for a request. The action prepares the model and view.
 */
class tx_rnbase_action_Base {
  var $cObject;

  /**
   * This is the starting point of request processing.
   * @param $parameters
   * @param $configurations
   */
  function execute($parameters, $configurations){
    return 'You should overwrite execute in your child action!';
  }

  /**
   * Returns an instanceof tslib_cObj.
   * Since this object has functions for database access and frontend your ControllerAction
   * should always provide cObj for model and view. This ensures only one instance per request.
   *
   * @return an instance of tslib_cObj
   */
  function getCObj() {
    if(!$this->cObject){
      $this->cObject = t3lib_div::makeInstance('tslib_cObj');
    }
    return $this->cObject;
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_Base.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_Base.php']);
}


