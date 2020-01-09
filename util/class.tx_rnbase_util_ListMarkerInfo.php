<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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

interface ListMarkerInfo
{
    public function init($template, &$formatter, $marker);

    public function getTemplate(&$item);
}

/**
 * Default implementation of ListMarkerInfo.
 */
class tx_rnbase_util_ListMarkerInfo implements ListMarkerInfo
{
    public function __construct()
    {
    }

    public function init($template, &$formatter, $marker)
    {
        $this->template = $template;
    }

    public function getTemplate(&$item)
    {
        return $this->template;
    }
}
