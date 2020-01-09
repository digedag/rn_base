<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche <rene@system25.de>
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
 * This interface defines a base model.
 * for backwards compatibility we use tx_rnbase_IModel as base interface
 * but please use Tx_Rnbase_Domain_Model_RecordInterface!
 *
 * @deprecated please use Tx_Rnbase_Domain_Model_RecordInterface!
 *
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
interface tx_rnbase_IModel
{
    /**
     * Returns the uid.
     *
     * @return int
     */
    public function getUid();

    /**
     * Returns the data record as array.
     *
     * @return array
     */
    public function getRecord();
}
/**
 * The realy to use interface for models!
 *
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
interface Tx_Rnbase_Domain_Model_RecordInterface extends tx_rnbase_IModel
{
}
