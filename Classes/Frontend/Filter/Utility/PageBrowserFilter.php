<?php

namespace Sys25\RnBase\Frontend\Filter\Utility;

use ArrayObject;
use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Utility\PageBrowser;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017-2021 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class PageBrowserFilter
{
    private $dbConnection = null;

    public function __construct()
    {
        $this->dbConnection = Connection::getInstance();
    }

    /**
     * Pagebrowser vorbereiten. Wir im Plugin nach dem init() des Filters aufgerufen:.
     *
     *      // Soll ein PageBrowser verwendet werden
     *      tx_rnbase_filter_BaseFilter::handlePageBrowser($configurations,
     *          $this->getConfId().'myitem.pagebrowser', $viewdata, $fields, $options, array(
     *          'searchcallback'=> array($service, 'search'),
     *          'pbid' => 'mt'.$configurations->getPluginId(),
     *          )
     *      );
     *
     * @param ConfigurationInterface $configurations
     * @param string $confid         Die Confid des PageBrowsers. z.B. myview.org.pagebrowser ohne Punkt!
     * @param ArrayObject $viewdata
     * @param array $fields
     * @param array $options
     * @param array $cfg   You have to set 'searchcallback' and optional 'pbid'
     */
    public function handle(ConfigurationInterface $configurations, $confid, $viewdata, &$fields, &$options, $cfg = [])
    {
        $confid .= '.';
        if (is_array($configurations->get($confid))) {
            $searchCallback = $cfg['searchcallback'];
            if (!$searchCallback) {
                throw new Exception('No search callback defined!');
            }
            // Die Gesamtzahl der Items ist entweder im Limit gesetzt oder muss ermittelt werden
            $listSize = (int) $options['limit'];
            if (!$listSize) {
                // Mit Pagebrowser benötigen wir zwei Zugriffe, um die Gesamtanzahl der Items zu ermitteln
                $pageBrowserOptions = $options;
                $pageBrowserOptions['count'] = 1;
                // eigenes what?
                if ($configurations->get($confid.'what')) {
                    $pageBrowserOptions['what'] = $configurations->get($confid.'what');
                    // wir brauchen keinen countwrap wenn sich das what selbst darum
                    // kümmert
                    if (false !== strpos(strtoupper($pageBrowserOptions['what']), 'COUNT(')) {
                        $pageBrowserOptions['disableCountWrap'] = true;
                    }
                }

                $listSize = call_user_func($searchCallback, $fields, $pageBrowserOptions);
                //$listSize = $service->search($fields, $options);
                unset($options['count']);
            }
            // PageBrowser initialisieren
            $pbId = $cfg['pbid'] ? $cfg['pbid'] : 'pb';
            /**
             * @var $pageBrowser PageBrowser
             */
            $pageBrowser = tx_rnbase::makeInstance(PageBrowser::class, $pbId);
            $pageSize = $configurations->getInt($confid.'limit');
            $pageBrowser->setState($configurations->getParameters(), $listSize, $pageSize);

            // Nach dem Item nur suchen wenn über die Parameter kein Pointer gesetzt wurde.
            if (is_array($cfg['pointerFromItem'])
                && !$configurations->getParameters()->offsetExists($pageBrowser->getParamName('pointer'))
                && ($itemId = $configurations->getParameters()->get($cfg['pointerFromItem']['param']))) {
                // Wir erzeugen uns das SQl der eigentlichen Abfrage.
                // Dabei wollen wir auch die rownum haben!
                $sql = call_user_func(
                    $searchCallback,
                    $fields,
                    array_merge($options, ['sqlonly' => 1, 'rownum' => 1])
                );
                // Jetzt besorgen wir uns die Position des aktuellen Eintrages
                $res = $this->dbConnection->doSelect(
                    'ROW.rownum',
                    '('.$sql.') as ROW',
                    [
                        'where' => 'ROW.'.$cfg['pointerFromItem']['field'].'='.
                        $this->dbConnection->fullQuoteStr($itemId),
                        'enablefieldsoff' => true,
                    ]
                );
                // Jetzt haben wir ein Ergebnis, mit der Zeilennummer des Datensatzes.
                if (!empty($res)) {
                    $rownum = (int) $res[0]['rownum'];
                    // Wir berechnen die Seite, auf der sich der aktuelle Eintrag befindet.
                    // intval schneidet die Dezimalzahlen ab, erspart uns das runden.
                    // -1, weil Bei 10 Einträgen pro Seite rownum 20 auf seite 2 ist,
                    // 20/10 allerdings 2 (für seite 3) ergibt.
                    $pageBrowser->setPointer(intval(($rownum - 1) / $pageSize));
                }
            }

            $limit = $pageBrowser->getState();
            $options = array_merge($options, $limit);
            $pageBrowser->markPageNotFoundIfPointerOutOfRange($configurations, $confid);
            if ($viewdata) {
                $viewdata->offsetSet('pagebrowser', $pageBrowser);
            }
        }
    }
}
