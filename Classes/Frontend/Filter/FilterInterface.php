<?php

namespace Sys25\RnBase\Frontend\Filter;

use Sys25\RnBase\Frontend\Request\RequestInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2024 Rene Nitzsche
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

interface FilterInterface
{
    /**
     * Initialisiert den Filter.
     *
     * @param array $fields
     * @param array $options
     */
    public function init(&$fields, &$options);

    public function setRequest(RequestInterface $request, $confId);

    /**
     * Whether or not the result list should be displayed.
     * It is up to the list view to handle this result.
     * This can be used to hide a result output if a search view is
     * initially displayed.
     *
     * @return bool
     */
    public function hideResult();

    /**
     * Whether or not a user defined search is activated. This means some functions
     * like showing a charbrowser should be ignored.
     *
     * @return bool
     */
    public function isSpecialSearch();
}
