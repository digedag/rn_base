<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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

/**
 * Searcher Utility.
 *
 * @author Michael Wagner
 */
class Tx_Rnbase_Backend_Utility_SearcherUtility
{
    /**
     * The internal options object.
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $options = null;

    /**
     * Constructor.
     *
     * @param array|Tx_Rnbase_Domain_Model_Data $options
     *
     * @return Tx_Rnbase_Backend_Utility_SearcherUtility
     */
    public static function getInstance(
        $options = []
    ) {
        return tx_rnbase::makeInstance(
            'Tx_Rnbase_Backend_Utility_SearcherUtility',
            $options
        );
    }

    /**
     * Constructor.
     *
     * @param array|Tx_Rnbase_Domain_Model_Data $options
     */
    public function __construct(
        $options = []
    ) {
        tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
        $this->options = Tx_Rnbase_Domain_Model_Data::getInstance($options);
    }

    /**
     * The internal options object.
     *
     * @return Tx_Rnbase_Domain_Model_Data
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * The decorator instace.
     *
     * @param Tx_Rnbase_Domain_Repository_InterfaceSearch $repository
     * @param array                                       $fields
     * @param array                                       $options
     *
     * @return array|Traversable
     */
    public function performSearch(
        Tx_Rnbase_Domain_Repository_InterfaceSearch $repository,
        array $fields,
        array $options
    ) {
        // we has to build a uid map for sortable tables!
        $firstPrev = $lastNext = false;
        $baseTableName = $this->getOptions()->getBaseTableName();
        tx_rnbase::load('tx_rnbase_util_TCA');
        $downStep = 1;
        if ((
            $baseTableName
            && tx_rnbase_util_TCA::getSortbyFieldForTable($baseTableName)
            && ($options['limit'] || $options['offset'])
        )) {
            // normalize limit and offset values to int
            array_key_exists('offset', $options) ? $options['offset'] = (int) $options['offset'] : null;
            array_key_exists('limit', $options) ? $options['limit'] = (int) $options['limit'] : null;
            // wir haben ein offset und benötigen die beiden elemente element davor.
            if (!empty($options['offset'])) {
                $firstPrev = true;
                $downStep = $options['offset'] > 2 ? 2 : 1;
                $options['offset'] -= $downStep;
                // das limit um eins erhöhen um das negative offset zu korrigieren
                if (isset($options['limit'])) {
                    $options['limit'] += $downStep;
                }
            }
            // wir haben ein limit und benötigen das element danach.
            if (!empty($options['limit'])) {
                $lastNext = true;
                ++$options['limit'];
            }
        }

        // perform the search
        $items = $repository->search($fields, $options);

        $secondPrev = null;
        // reduce the itemy by first and last
        if ($firstPrev || $lastNext) {
            $isCollection = is_object($items);
            $slice = ['offset' => 0, 'length' => count($items)];
            // das letzte entfernen, aber nur wenn genügend elemente im result sind
            if ($lastNext && count($items) >= $options['limit']) {
                --$slice['length'];
                $lastNext = $isCollection ? $items->last() : end($items);
            }
            // das erste entfernen, wenn der offset reduziert wurde.
            if ($firstPrev) {
                ++$slice['offset'];
                $firstPrev = $isCollection ? $items->first() : reset($items);
                // das zweite entfernen, wenn der offset um 2 reduziert wurde
                if ($downStep > 1) {
                    ++$slice['offset'];
                    $secondPrev = $isCollection ? $items->next() : next($items);
                }
            }
            // reduce the items collection by the elements to show ans remove them map elements
            if ($isCollection) {
                $items->exchangeArray(
                    $items->slice($slice['offset'], $slice['length'])
                );
            } else {
                $items = array_slice($items, $slice['offset'], $slice['length'], true);
            }
        }

        // now build the uid map
        $map = [];
        if ($firstPrev instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
            $map[$firstPrev->getUid()] = [];
        }
        if ($secondPrev instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
            $map[$secondPrev->getUid()] = [];
        }
        foreach ($items as $item) {
            $map[$item->getUid()] = [];
        }
        if ($lastNext instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
            $map[$lastNext->getUid()] = [];
        }

        // store the uid map to the options array
        $this->getOptions()->setUidMap($map);

        return $items;
    }
}
