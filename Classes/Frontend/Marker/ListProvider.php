<?php

namespace Sys25\RnBase\Frontend\Marker;

use Exception;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2023 Rene Nitzsche
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
 * Provide data for ListBuilder.
 */
class ListProvider implements IListProvider
{
    private $fields;
    private $options;
    private $mode;
    private $searchCallback;

    public function initBySearch($searchCallback, $fields, $options)
    {
        $this->mode = 1;
        $this->searchCallback = $searchCallback;
        $this->fields = $fields;
        $this->options = $options;
    }

    /**
     * Starts iteration over all items. The callback method is called for each single item.
     *
     * @param array $callback
     */
    public function iterateAll($itemCallback)
    {
        switch ($this->mode) {
            case 1:
                $this->options['callback'] = $itemCallback;
                call_user_func($this->searchCallback, $this->fields, $this->options);

                break;
            default:
                throw new Exception('Undefined list mode.');
        }
    }
}
