<?php

namespace Sys25\RnBase\Utility;

use ArrayObject;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\Marker\PageBrowserMarker;
use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche
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
 * Contains utility functions for HTML-Forms.
 */
class PageBrowser implements PageBrowserInterface
{
    private $pbid;

    private $pointer;

    private $listSize;

    private $pageSize;

    private $pointerOutOfRange;

    /**
     * Erstellung des PageBrowser mit einer eindeutigen ID.
     */
    public function __construct($pbid)
    {
        $this->pbid = $pbid;
        $this->pointerOutOfRange = false;
    }

    /**
     * Initialisierung des PageBrowser mit den aktuellen Zustand.
     * Wenn keine Parameter übergeben werden, dann wird der Pager neu initialisiert
     * und startet wieder bei Seite 1.
     *
     * @param ArrayObject $parameters die vorhandenen Parameter aus dem Request oder NULL
     * @param int         $listSize   Gesamtgröße der darzustellenden Liste
     * @param int         $pageSize   Größe einer Seite
     */
    public function setState($parameters, $listSize, $pageSize)
    {
        $this->setPointerByParameters($parameters);
        $this->setListSize($listSize);
        $this->setPageSize($pageSize);
    }

    /**
     * Set current page pointer from request parameters.
     *
     * @param ArrayObject $parameters
     */
    public function setPointerByParameters($parameters)
    {
        $this->setPointer(
            (is_object($parameters) && $parameters->offsetExists($this->getParamName('pointer')))
                ? intval($parameters->offsetGet($this->getParamName('pointer')))
                : 0
        );
    }

    /**
     * Set a new page pointer.
     *
     * @param int $newPointer
     */
    public function setPointer($newPointer)
    {
        $this->pointer = $newPointer < 0 ? 0 : $newPointer;
    }

    /**
     * Liefert die Limit-Angaben für die DB-Anfrage.
     *
     * @return array with keys offset and limit
     */
    public function getState()
    {
        $offset = $this->pointer * $this->pageSize;
        // Wenn der Offset größer ist als die verfügbaren Einträge, dann den Offset neu berechnen.
        if ($offset >= $this->listSize) {
            $offset = intval((ceil($this->listSize / $this->pageSize) - 1) * $this->pageSize);
            if ($this->listSize > 0) {
                $this->pointerOutOfRange = true;
            }
        }
        // ensure offset is never lower than 0
        $offset = $offset >= 0 ? $offset : 0;

        // LS: 100 -> 90+10
        // 100/10 = 10 -1
        $limit = $this->listSize < $this->pageSize ? $this->listSize : $this->pageSize;
        $ret = ['offset' => $offset, 'limit' => $limit];

        return $ret;
    }

    /**
     * Returns the count of pages.
     *
     * @return int page to show
     */
    public function getLastPage()
    {
        return intval($this->getListSize() / $this->getPageSize());
    }

    /**
     * Returns the current pointer. This is the current page to show. If it's higher than
     * the possible.
     *
     * @return int page to show
     */
    public function getPointer()
    {
        $lastPage = $this->getLastPage();
        if ($this->pointer > $lastPage) {
            $this->pointer = $lastPage;
            $this->pointerOutOfRange = true;
        }

        return $this->pointer;
    }

    /**
     * Returns the complete number of items in list.
     *
     * @return int complete number of items
     */
    public function getListSize()
    {
        return $this->listSize;
    }

    /**
     * Set the total number of items in list.
     *
     * @param int $totalSize
     */
    public function setListSize($totalSize)
    {
        $this->listSize = intval($totalSize);
    }

    /**
     * Returns the complete number of items per page.
     *
     * @return int complete number of items per page
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set number of items per page.
     *
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $pageSize = (int) $pageSize;
        $this->pageSize = $pageSize > 0 ? $pageSize : 10;
    }

    /**
     * Liefert einen Marker zur Erstellung des PageBrowsers im Template.
     */
    public function getMarker($markerClassName = PageBrowserMarker::class)
    {
        /** @var PageBrowserMarker $pageBrowserMarker */
        $pageBrowserMarker = tx_rnbase::makeInstance($markerClassName);
        $pageBrowserMarker->setPageBrowser($this);

        return $pageBrowserMarker;
    }

    /**
     * If page pointer was set to a value greater then max pages, then this method
     * will return true.
     *
     * @return bool
     */
    public function isPointerOutOfRange()
    {
        return $this->pointerOutOfRange;
    }

    /**
     * @return string the right parametername for this browser
     */
    public function getParamName($param)
    {
        return strtolower('pb-'.$this->pbid.'-'.$param);
    }

    /**
     * Die angefragte Seite war nicht vorhanden, also 404 schicken.
     *
     * @see https://webmasters.googleblog.com/2014/02/infinite-scroll-search-friendly.html
     * Außerdem darf das Plugin dann nicht gecached werden, da ansonsten beim nächsten Aufruf
     * ein 200er Response kommt, da die Seite dann aus dem Cache kommt. (TYPO3 cached nur
     * das HTML, nicht die Header)
     *
     * @param PageBrowser $pageBrowser
     * @param ConfigurationInterface $configurations
     * @param string $confid
     */
    public function markPageNotFoundIfPointerOutOfRange(ConfigurationInterface $configurations, $confid)
    {
        if ($this->isPointerOutOfRange() && !$configurations->getBool($confid.'ignorePageNotFound')) {
            $utilityClass = $this->getHttpUtilityClass();
            header($utilityClass::HTTP_STATUS_404);
            $configurations->convertToUserInt();
        }
    }

    /**
     * @return string|\TYPO3\CMS\Core\Utility\HttpUtility
     */
    protected function getHttpUtilityClass()
    {
        return Typo3Classes::getHttpUtilityClass();
    }
}
