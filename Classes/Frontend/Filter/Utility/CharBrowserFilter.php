<?php

namespace Sys25\RnBase\Frontend\Filter\Utility;

use ArrayObject;
use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Search\SearchBase;

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

class CharBrowserFilter
{
    private $dbConnection = null;

    public function __construct()
    {
        $this->dbConnection = Connection::getInstance();
    }

    /**
     * Bindet einen Buchstaben-Browser ein.
     *
     * @param ConfigurationInterface $configurations
     * @param string $confid Die Confid des PageBrowsers. z.B. myview.org.pagebrowser ohne Punkt!
     * @param ArrayObject $viewdata
     * @param array $fields
     * @param array $options
     * @param array $cfg You have to set 'searchcallback' and optional 'pbid'
     */
    public static function handle(ConfigurationInterface $configurations, $confid, $viewData, &$fields, &$options, $cfg = [])
    {
        if ($configurations->get($confid)) {
            $colName = $cfg['colname'];
            if (!$colName) {
                throw new Exception('No column name for charbrowser defined');
            }

            $pagerData = self::findPagerData($fields, $options, $cfg);

            $firstChar = $configurations->getParameters()->offsetExists($pagerData['pointername']) ? $configurations->getParameters()->offsetGet($pagerData['pointername']) : null;
            $firstChar = (strlen(trim($firstChar)) > 0) ? substr($firstChar, 0, '0' == $firstChar[0] ? 3 : 1) : $pagerData['default'];
            // Existiert der Point in den aktuellen Daten
            $firstChar = array_key_exists($firstChar, $pagerData['list']) ? $firstChar : $pagerData['default'];
            $viewData->offsetSet('pagerData', $pagerData);
            $viewData->offsetSet('charpointer', $firstChar);
        }
        $filter = $viewData->offsetExists('filter') ? $viewData->offsetGet('filter') : null;
        // Der CharBrowser beachten wir nur, wenn keine Suche aktiv ist
        // TODO: Der Filter sollte eine Methode haben, die sagt, ob ein Formular aktiv ist
        if ('' != $firstChar && !$filter->isSpecialSearch()) {
            $specials = SearchBase::getSpecialChars();
            $firsts = $specials[$firstChar];
            if ($firsts) {
                $firsts = implode('\',\'', $firsts);
            } else {
                $firsts = $firstChar;
            }

            if ($fields[SEARCH_FIELD_CUSTOM]) {
                $fields[SEARCH_FIELD_CUSTOM] .= ' AND ';
            }
            $fields[SEARCH_FIELD_CUSTOM] .= 'LEFT(UCASE('.$colName."),1) IN ('$firsts') ";
        }
    }

    /**
     * Wir verwenden einen alphabetischen Pager. Also muß zunächst ermittelt werden, welche
     * Buchstaben überhaupt vorkommen.
     *
     * @param array $fields
     * @param array $options
     * @param array $cfg
     *
     * @return array
     *
     * @throws Exception
     */
    private static function findPagerData($fields, $options, $cfg)
    {
        $colName = $cfg['colname'];

        $searchCallback = $cfg['searchcallback'];
        if (!$searchCallback) {
            throw new Exception('No search callback defined!');
        }

        $options['what'] = 'LEFT(UCASE('.$colName.'),1) As first_char, count(LEFT(UCASE('.$colName.'),1)) As size';
        $options['groupby'] = 'LEFT(UCASE('.$colName.'),1)';
        unset($options['limit']);

        $rows = call_user_func($searchCallback, $fields, $options);

        $specials = SearchBase::getSpecialChars();
        $wSpecials = [];
        foreach ($specials as $key => $special) {
            foreach ($special as $char) {
                $wSpecials[$char] = $key;
            }
        }

        $ret = [];
        foreach ($rows as $row) {
            if (array_key_exists($row['first_char'], $wSpecials)) {
                $ret[$wSpecials[$row['first_char']]] = intval($ret[$wSpecials[$row['first_char']]]) + $row['size'];
            } else {
                $ret[$row['first_char']] = $row['size'];
            }
        }

        if ('last' == $cfg['specials'] && isset($ret['0-9'])) {
            $specials = $ret['0-9'];
            unset($ret['0-9']);
            $ret['0-9'] = $specials;
        }

        $current = 0;
        if (count($ret)) {
            $keys = array_keys($ret);
            $current = $keys[0];
        }
        $data = [];
        $data['list'] = $ret;
        $data['default'] = $current;
        $data['pointername'] = array_key_exists('cbid', $cfg) && $cfg['cbid'] ? $cfg['cbid'] : 'charpointer';

        return $data;
    }
}
