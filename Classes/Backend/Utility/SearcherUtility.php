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
	 * Constructor
	 *
	 * @return Tx_Rnbase_Backend_Utility_SearcherUtility
	 */
	public static function getInstance()
	{
		return tx_rnbase::makeInstance(
			'Tx_Rnbase_Backend_Utility_SearcherUtility'
		);
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
		$items = $repository->search($fields, $options);

		return $items;
	}
}
