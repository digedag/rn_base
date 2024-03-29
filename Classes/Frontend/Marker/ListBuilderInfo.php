<?php

namespace Sys25\RnBase\Frontend\Marker;

use ArrayObject;
use Sys25\RnBase\Configuration\ConfigurationInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2021 Rene Nitzsche
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
 * Default implementation for ListBuilderInfo.
 */
class ListBuilderInfo implements IListBuilderInfo
{
    /**
     * Get a message string for empty list. This is an language string. The key is
     * taken from ts-config: [item].listinfo.llkeyEmpty.
     *
     * @param ArrayObject $viewData
     * @param ConfigurationInterface $configurations
     *
     * @return string
     */
    public function getEmptyListMessage($confId, $viewData, &$configurations)
    {
        return $configurations->getLL($configurations->get($confId.'listinfo.llkeyEmpty'));
    }

    public function setMarkerArrays(&$markerArray, &$subpartArray, &$wrappedSubpartArray)
    {
    }

    public function getListMarkerInfo()
    {
        return null;
    }
}
