<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Rene Nitzsche
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
class tx_rnbase_util_PageBrowser implements PageBrowser {
  private $pdid;

  /**
   * Erstellung des PageBrowser mit einer eindeutigen ID
   */
  function tx_rnbase_util_PageBrowser($pbid) {
    $this->pbid = $pbid;
  }

  /**
   * Initialisierung des PageBrowser mit den aktuellen Zustand
   */
  function setState($parameters, $listSize, $pageSize) {
    $this->pointer = intval($parameters->offsetGet($this->getParamName('pointer')));
    $this->listSize = $listSize;
    $this->pageSize = t3lib_div::intInRange(intval($pageSize), 1, 1000);
  }

  /**
   * Liefert die Limit-Angaben für die DB-Anfrage
   */
  function getState() {
    $ret = array( 'offset' => ($this->pointer * $this->pageSize), 'limit' => $this->pageSize , );
    return $ret;
  }

  /**
   * Returns the current pointer. This is the current page to show.
   * @return int page to show
   */
  function getPointer() {
    return $this->pointer;
  }

  /**
   * Returns the complete number of items in list.
   * @return int complete number of items
   */
  function getListSize() {
    return $this->listSize;
  }

  /**
   * Returns the complete number of items per page.
   * @return int complete number of items per page
   */
  function getPageSize() {
    return $this->pageSize;
  }

  /**
   * Liefert einen Marker zur Erstellung des PageBrowsers im Template
   */
  function getMarker($markerClassName = 'tx_rnbase_util_PageBrowserMarker') {

    $pageBrowserMarker = tx_div::makeInstance($markerClassName);
    $pageBrowserMarker->setPageBrowser($this);
    return $pageBrowserMarker;

  }

  /**
   * @return the right parametername for this browser
   */
  function getParamName($param) {
    return strtolower('pb-'. $this->pdid . '-' . $param);
  }
}

interface PageBrowser {
  function setState($parameters, $listSize, $pageSize);
  function getState();
  function getMarker($markerClassName = 'tx_rnbase_util_PageBrowserMarker');

  /**
   * Returns the current pointer. This is the current page to show.
   * @return int page to show
   */
  function getPointer();
  /**
   * Returns the complete number of items in list.
   * @return int complete number of items
   */
  function getListSize();
  /**
   * Returns the complete number of items per page.
   * @return int complete number of items per page
   */
  function getPageSize();
}

interface PageBrowserMarker {
  function setPageBrowser($pb);
  function parseTemplate($template, &$formatter, &$link, $pbConfId, $pbMarker = 'PAGEBROWSER');
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowser.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowser.php']);
}
?>