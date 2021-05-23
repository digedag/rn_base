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

tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_PageBrowser');
tx_rnbase::load('tx_rnbase_util_Math');

/**
 * Contains utility functions for HTML-Forms.
 */
class tx_rnbase_util_PageBrowserMarker implements PageBrowserMarker
{
    private $pagePartsDef = ['normal', 'current', 'first', 'last', 'prev', 'next', 'prev_bullets', 'next_bullets'];

    /**
     * Erstellung des PageBrowserMarkers.
     */
    public function __construct()
    {
    }

    /**
     * Initialisierung des PageBrowserMarkers mit den PageBrowser.
     */
    public function setPageBrowser($pageBrowser)
    {
        $this->pageBrowser = $pageBrowser;
    }

    /**
     * Liefert die Limit-Angaben für die DB-Anfrage.
     *
     *  <!-- ###PAGEBROWSER### START -->
     *      <div class="pagebrowser">
     *          <p>###PAGEBROWSER_RANGEFROM###-###PAGEBROWSER_RANGETO### von ###PAGEBROWSER_COUNT### ###LABEL_RECIPES###</p>
     *          <ul>
     *              ###PAGEBROWSER_FIRST_PAGE###
     *                  <li>###PAGEBROWSER_FIRST_PAGE_LINK###&laquo;###PAGEBROWSER_FIRST_PAGE_LINK###</li>
     *              ###PAGEBROWSER_FIRST_PAGE###
     *              ###PAGEBROWSER_PREV_PAGE###
     *                  <li>###PAGEBROWSER_PREV_PAGE_LINK###&#x8B;###PAGEBROWSER_PREV_PAGE_LINK###</li>
     *              ###PAGEBROWSER_PREV_PAGE###
     *              ###PAGEBROWSER_PREV_BULLETS_PAGE###
     *                  <li>...</li>
     *              ###PAGEBROWSER_PREV_BULLETS_PAGE###
     *              ###PAGEBROWSER_CURRENT_PAGE###
     *                  <li><span class="current">###PAGEBROWSER_CURRENT_PAGE_NUMBER###</span></li>
     *              ###PAGEBROWSER_CURRENT_PAGE###
     *              ###PAGEBROWSER_NORMAL_PAGE###
     *                  <li>###PAGEBROWSER_NORMAL_PAGE_LINK######PAGEBROWSER_NORMAL_PAGE_NUMBER######PAGEBROWSER_NORMAL_PAGE_LINK###</li>
     *              ###PAGEBROWSER_NORMAL_PAGE###
     *              ###PAGEBROWSER_NEXT_BULLETS_PAGE###
     *                  <li>...</li>
     *              ###PAGEBROWSER_NEXT_BULLETS_PAGE###
     *              ###PAGEBROWSER_NEXT_PAGE###
     *                  <li>###PAGEBROWSER_NEXT_PAGE_LINK###&#x9B;###PAGEBROWSER_NEXT_PAGE_LINK###</li>
     *              ###PAGEBROWSER_NEXT_PAGE###
     *              ###PAGEBROWSER_LAST_PAGE###
     *                  <li>###PAGEBROWSER_LAST_PAGE_LINK###&raquo;###PAGEBROWSER_LAST_PAGE_LINK###</li>
     *              ###PAGEBROWSER_LAST_PAGE###
     *          </ul>
     *      </div>
     *  <!-- ###PAGEBROWSER### END -->
     */
    public function parseTemplate($template, &$formatter, $pbConfId, $pbMarker = 'PAGEBROWSER')
    {
        // Configs: maxPages, pagefloat
        // Obsolete da Template: showResultCount, showPBrowserText, dontLinkActivePage, showFirstLast
//    showRange
        $configurations = $formatter->configurations;

        $this->initLink($configurations, $pbConfId);

        $pointer = $this->pageBrowser->getPointer();
        $count = $this->pageBrowser->getListSize();
        $results_at_a_time = $this->pageBrowser->getPageSize();
        $totalPages = ceil($count / $results_at_a_time);
        $state = $this->pageBrowser->getState();
        // current entry range
        // example: "9-16 von 19 News"
        $rangeFrom = $state['offset'] + 1;
        $rangeTo = ($pointer != $totalPages - 1) ? ($pointer + 1) * $state['limit'] : $count;
        if (1 == $totalPages && $configurations->get($pbConfId.'hideIfSinglePage')) {
            return '';
        }
        $maxPages = intval($configurations->get($pbConfId.'maxPages'));
        $maxPages = tx_rnbase_util_Math::intInRange($maxPages ? $maxPages : 10, 1, 100);
        $templates = $this->getTemplates($template, $pbMarker);

        $pageFloat = $this->getPageFloat($configurations->get($pbConfId.'pagefloat'), $maxPages);
        $firstLastArr = $this->getFirstLastPage($pointer, $pageFloat, $totalPages, $maxPages);

        $arr = [
            'count' => $count,
            'rangefrom' => $rangeFrom,
            'rangeto' => $rangeTo,
            'totalpages' => $totalPages,
        ];
        $markerArray = $formatter->getItemMarkerArrayWrapped($arr, $pbConfId, 0, $pbMarker.'_');

        $subpartArray = $this->createSubpartArray($pbMarker);

        //---- Ab jetzt werden die Templates gefüllt
        $parts = []; // Hier werden alle Teile des Browser gesammelt
        // Der Marker für die erste Seite
        if ($templates['first'] && 0 != $pointer) {
            $parts[] = $this->getPageString(0, $pointer, 'first', $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Der Marker für die vorherige Seite
        if ($templates['prev'] && $pointer > 0) {
            $parts[] = $this->getPageString($pointer - 1, $pointer, 'prev', $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Der Marker "..." bei vielen Seiten
        if ($templates['prev_bullets'] && $pointer > $pageFloat - 1 && $totalPages > $maxPages) {
            $parts[] = $this->getPageString($pointer - 1, $pointer, 'prev_bullets', $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Jetzt über alle Seiten iterieren
        for ($i = $firstLastArr['first']; $i < $firstLastArr['last']; ++$i) {
            $pageId = ($i == $pointer && $templates['current']) ? 'current' : 'normal';
            $parts[] = $this->getPageString($i, $pointer, $pageId, $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Der Marker "..." bei vielen Seiten
        if ($templates['next_bullets'] && $pointer + $pageFloat < $totalPages - 1 && $totalPages > $maxPages) {
            $parts[] = $this->getPageString($pointer - 1, $pointer, 'next_bullets', $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Der Marker für die nächste Seite
        if ($templates['next'] && $pointer < $totalPages - 1) {
            $parts[] = $this->getPageString($pointer + 1, $pointer, 'next', $templates, $formatter, $pbConfId, $pbMarker);
        }

        // Der Marker für die letzte Seite
        if ($templates['last'] && $pointer != $totalPages - 1) {
            $parts[] = $this->getPageString($totalPages - 1, $pointer, 'last', $templates, $formatter, $pbConfId, $pbMarker);
        }

        $implode = $configurations->get($pbConfId.'.implode');
        $subpartArray['###'.$pbMarker.'_NORMAL_PAGE###'] = implode($implode ? $implode : ' ', $parts);
        $ret = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray);

        return $ret;
    }

    /**
     * Liefert das passende Template für die aktuelle Seite.
     */
    protected function getPageString($currentPage, $pointer, $pageId, &$templates, &$formatter, $pbConfId, $pbMarker)
    {
        $rec = [];
        $rec['number'] = $currentPage + 1;

        $pageTemplate = $templates[$pageId];
        $pageConfId = $pbConfId.'page.'.$pageId.'.';
        $pageMarker = $pbMarker.'_'.strtoupper($pageId).'_PAGE_';

        $pageMarkerArray = $formatter->getItemMarkerArrayWrapped($rec, $pageConfId, 0, $pageMarker);
        $pageSubpartArray = [];

        $pageWrappedSubpartArray = [];
        if ($this->link) {
            $this->link->parameters([$this->pageBrowser->getParamName('pointer') => $currentPage]);
            $pageWrappedSubpartArray['###'.$pageMarker.'LINK###'] = explode($this->token, $this->link->makeTag());
            $pageMarkerArray['###'.$pageMarker.'LINKURL###'] = $this->link->makeUrl();
        } else {
            $pageWrappedSubpartArray['###'.$pageMarker.'LINK###'] = '';
            $pageMarkerArray['###'.$pageMarker.'LINKURL###'] = '';
        }

        $out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($pageTemplate, $pageMarkerArray, $pageSubpartArray, $pageWrappedSubpartArray);

        return $out;
    }

    /**
     * Ermittelt die erste und die letzte Seite, die im Browser gezeigt wird.
     *
     * @return array with keys 'first' and 'last'
     */
    private function getFirstLastPage($pointer, $pageFloat, $totalPages, $maxPages)
    {
        $ret = [];
        if ($pageFloat > -1) {
            $ret['last'] = min($totalPages, max($pointer + 1 + $pageFloat, $maxPages));
            $ret['first'] = max(0, $ret['last'] - $maxPages);
        } else {
            $ret['first'] = 0;
            $ret['last'] = tx_rnbase_util_Math::intInRange($totalPages, 1, $maxPages);
        }

        return $ret;
    }

    /**
     * Liefert den korrekten Wert für den PageFloat. Das richtet den Ausschnitt der gezeigten
     * Seiten im PageBrowser ein.
     */
    private function getPageFloat($pageFloat, $maxPages)
    {
        if ($pageFloat) {
            if ('CENTER' == strtoupper($pageFloat)) {
                $pageFloat = ceil(($maxPages - 1) / 2);
            } else {
                $pageFloat = tx_rnbase_util_Math::intInRange($pageFloat, -1, $maxPages - 1);
            }
        } else {
            $pageFloat = -1;
        }

        return $pageFloat;
    }

    /**
     * Liefert ein Array mit allen verfügbaren Subtemplates der Seiten.
     *
     * @param string $template
     * @param string $pbMarker
     *
     * @return array
     */
    protected function getTemplates($template, $pbMarker)
    {
        $ret = [];
        foreach ($this->pagePartsDef as $part) {
            $ret[$part] = tx_rnbase_util_Templates::getSubpart($template, '###'.$pbMarker.'_'.strtoupper($part).'_PAGE###');
        }

        return $ret;
    }

    /**
     * Initialisiert das globale SubpartArray und entfernt alle Subpartmarker.
     */
    private function createSubpartArray($pbMarker)
    {
        $ret = [];

        foreach ($this->pagePartsDef as $part) {
            $ret['###'.$pbMarker.'_'.strtoupper($part).'_PAGE###'] = '';
        }

        return $ret;
    }

    /**
     * Initialisiert die interne Link-Instanz
     * TODO: Konfigurierbar machen!!
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configuration
     */
    protected function initLink(&$configuration, $pbConfId)
    {
        $this->link = $configuration->createLink();
        $this->link->initByTS($configuration, $pbConfId.'link.', []);
        $this->token = md5(microtime());
        $this->link->label($this->token);
        $this->noLink = ['', ''];
    }
}
