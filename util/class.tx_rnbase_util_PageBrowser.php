<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2013 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_Math');

/**
 * Contains utility functions for HTML-Forms
 */
class tx_rnbase_util_PageBrowser implements PageBrowser {
	private $pbid;
	private $pointer;
	private $listSize;
	private $pageSize;

	/**
	 * Erstellung des PageBrowser mit einer eindeutigen ID
	 */
	public function __construct($pbid) {
		$this->pbid = $pbid;
	}

	/**
	 * Initialisierung des PageBrowser mit den aktuellen Zustand.
	 * Wenn keine Parameter übergeben werden, dann wird der Pager neu initialisiert
	 * und startet wieder bei Seite 1.
	 *
	 * @param ArrayObject $parameters die vorhandenen Parameter aus dem Request oder NULL
	 * @param int $listSize Gesamtgröße der darzustellenden Liste
	 * @param int $pageSize Größe einer Seite
	 */
	public function setState($parameters, $listSize, $pageSize) {
		$this->setPointerByParameters($parameters);
		$this->setListSize($listSize);
		$this->setPageSize($pageSize);
	}
	/**
	 * Set current page pointer from request parameters
	 * @param ArrayObject $parameters
	 */
	public function setPointerByParameters($parameters) {
		$this->setPointer(is_object($parameters) ? intval($parameters->offsetGet($this->getParamName('pointer'))) : 0);
	}
	/**
	 * Set a new page pointer
	 * @param int $newPointer
	 */
	public function setPointer($newPointer) {
		$this->pointer = $newPointer < 0 ? 0 : $newPointer;
	}

	/**
	 * Liefert die Limit-Angaben für die DB-Anfrage
	 * @return array with keys offset and limit
	 */
	public function getState() {
		$offset = $this->pointer * $this->pageSize;
		// Wenn der Offset größer ist als die verfügbaren Einträge, dann den Offset neu berechnen.
		$offset = $offset >= $this->listSize ? intval(((ceil($this->listSize/$this->pageSize))-1) * $this->pageSize) : $offset;
		// ensure offset is never lower than 0
		$offset = $offset >= 0 ? $offset : 0;

		// LS: 100 -> 90+10
		// 100/10 = 10 -1
		$limit = $this->listSize < $this->pageSize ? $this->listSize : $this->pageSize;
		$ret = array( 'offset' => $offset, 'limit' => $limit , );
		return $ret;
	}

	/**
	 * Returns the current pointer. This is the current page to show.
	 * @return int page to show
	 */
	public function getPointer() {
		return $this->pointer;
	}

	/**
	 * Returns the complete number of items in list.
	 * @return int complete number of items
	 */
	public function getListSize() {
		return $this->listSize;
	}
	/**
	 * Set the total number of items in list.
	 * @param int $totalSize
	 */
	public function setListSize($totalSize) {
		$this->listSize = intval($totalSize);
	}

	/**
	 * Returns the complete number of items per page.
	 * @return int complete number of items per page
	 */
	public function getPageSize() {
		return $this->pageSize;
	}
	/**
	 * Set number of items per page
	 * @param int $pageSize
	 */
	public function setPageSize($pageSize) {
		$this->pageSize = tx_rnbase_util_Math::intInRange(intval($pageSize) ? $pageSize : 10, 1, 1000);
	}

	/**
	 * Liefert einen Marker zur Erstellung des PageBrowsers im Template
	 */
	public function getMarker($markerClassName = 'tx_rnbase_util_PageBrowserMarker') {
		$pageBrowserMarker = tx_rnbase::makeInstance($markerClassName);
		$pageBrowserMarker->setPageBrowser($this);
		return $pageBrowserMarker;
	}

	/**
	 * @return the right parametername for this browser
	 */
	public function getParamName($param) {
		return strtolower('pb-'. $this->pbid . '-' . $param);
	}
}

interface PageBrowser {
  public function setState($parameters, $listSize, $pageSize);
  public function getState();
  public function getMarker($markerClassName = 'tx_rnbase_util_PageBrowserMarker');

  /**
   * Returns the current pointer. This is the current page to show.
   * @return int page to show
   */
  public function getPointer();
  /**
   * Returns the complete number of items in list.
   * @return int complete number of items
   */
  public function getListSize();
  /**
   * Returns the complete number of items per page.
   * @return int complete number of items per page
   */
  public function getPageSize();
}

interface PageBrowserMarker {
  public function setPageBrowser($pb);
  public function parseTemplate($template, &$formatter, $pbConfId, $pbMarker = 'PAGEBROWSER');
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowser.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_PageBrowser.php']);
}
