<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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
 * @deprecated DON'T USE THIS CLASS ANYMORE!
 * @see Sys25\RnBase\Domain\Model\BaseModel
 */
class tx_rnbase_model_base extends Sys25\RnBase\Domain\Model\BaseModel implements Tx_Rnbase_Domain_Model_RecordInterface, Tx_Rnbase_Domain_Model_DataInterface
{
    public $uid;
    public $record = [];

    protected function init($rowOrUid = null)
    {
        parent::init($rowOrUid);
        $this->record = parent::getRecord();
        $this->uid = parent::getUid();
    }

    /**
     * @deprecated
     */
    public function getColumnWrapped($formatter, $columnName, $baseConfId, $colConfId = '')
    {
        $colConfId = (strlen($colConfId)) ? $colConfId : $columnName.'.';

        return $formatter->wrap($this->record[$columnName], $baseConfId.$colConfId);
    }
}
