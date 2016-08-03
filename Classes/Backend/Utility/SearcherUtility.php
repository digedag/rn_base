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
 * Searcher Utility
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 */
class Tx_Rnbase_Backend_Utility_SearcherUtility
{
	/**
	 * The internal options object.
	 *
	 * @var Tx_Rnbase_Domain_Model_Data $options
	 */
	private $options = null;

	/**
	 * Constructor
	 *
	 * @param array|Tx_Rnbase_Domain_Model_Data $options
	 *
	 * @return Tx_Rnbase_Backend_Utility_SearcherUtility
	 */
	public static function getInstance(
		$options = array()
	) {
		return tx_rnbase::makeInstance(
			'Tx_Rnbase_Backend_Utility_SearcherUtility',
			$options
		);
	}

	/**
	 * Constructor
	 *
	 * @param array|Tx_Rnbase_Domain_Model_Data $options
	 *
	 * @return void
	 */
	public function __construct(
		$options = array()
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
	 * @param array $fields
	 * @param array $options
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
		if (
			$baseTableName
			&& tx_rnbase_util_TCA::getSortbyFieldForTable($baseTableName)
			&& ($options['limit'] || $options['offset'])
		) {
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
				$options['limit']++;
			}
		}

		// perform the search
		$items = $repository->search($fields, $options);

		// reduce the itemy by first and last
		if ($firstPrev || $lastNext) {
			// @FIXME !!! That's only an workaround. An ArrayObject shoul be retain!
			$items = (array) $items;
			// das letzte entfernen, aber nur wenn genügend elemente im result sind
			if ($lastNext && count($items) >= $options['limit']) {
				$lastNext = array_pop($items);
			}
			// das erste entfernen, wenn der offset reduziert wurde.
			if ($firstPrev) {
				$firstPrev = array_shift($items);
				// das zweite entfernen, wenn der offset um 2 reduziert wurde
				if ($downStep > 1) {
					$secondPrev = array_shift($items);
				}
			}
		}

		// now build the uid map

		$map = array();
		if ($firstPrev instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
			$map[$firstPrev->getUid()] = array();
		}
		if ($secondPrev instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
			$map[$secondPrev->getUid()] = array();
		}
		foreach ($items as $item) {
			$map[$item->getUid()] = array();
		}
		if ($lastNext instanceof Tx_Rnbase_Domain_Model_RecordInterface) {
			$map[$lastNext->getUid()] = array();
		}

		// store the uid map to the options array
		$this->getOptions()->setUidMap($map);

		return $items;
	}
}
