<?php
/**
 * Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche (rene@system25.de)
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
 */

/**
 * Linker interface for mod tables.
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Michael Wagner
 */
interface tx_rnbase_mod_linker_LinkerInterface
{
    /**
     * Link zur Detailseite erzeugen
     *
     * @param Tx_Rnbase_Domain_Model_Base $item
     * @param tx_rnbase_util_FormTool $formTool
     * @param int $currentPid
     * @param tx_rnbase_model_data $options
     * @return string
     */
    public function makeLink($item, $formTool, $currentPid, $options);
}
