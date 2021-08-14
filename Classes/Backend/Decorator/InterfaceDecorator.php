<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * Decorator interface.
 *
 * @method void __construct(tx_rnbase_mod_IModule $mod)
 *
 * @author Michael Wagner
 */
interface Tx_Rnbase_Backend_Decorator_InterfaceDecorator
{
    /**
     * Formats a value.
     *
     * @param string                                $columnValue
     * @param string                                $columnName
     * @param array                                 $record
     * @param \Tx_Rnbase_Domain_Model_DataInterface $entry
     *
     * @return string
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        Tx_Rnbase_Domain_Model_DataInterface $entry
    );
}
