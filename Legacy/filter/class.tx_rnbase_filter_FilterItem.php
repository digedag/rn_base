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
 * tx_rnbase_IFilter.
 *
 * @author          Rene Nitzsche <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_filter_FilterItem implements tx_rnbase_IFilterItem
{
    public $record;

    public function __construct($name, $value)
    {
        $this->record = [];
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Returns the name of item.
     *
     * @return string
     */
    public function getName()
    {
        return $this->record['name'];
    }

    public function setName($name)
    {
        $this->record['name'] = $name;
    }

    /**
     * Returns the current value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->record['value'];
    }

    public function setValue($value)
    {
        $this->record['value'] = $value;
    }
}
