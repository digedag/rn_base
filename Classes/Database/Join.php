<?php

namespace Sys25\RnBase\Database;

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

class Join
{
    const TYPE_INNER = 'inner';
    const TYPE_LEFT = 'left';
    const TYPE_RIGHT = 'right';
    private $fromAlias;
    private $table;
    private $onClause;
    private $alias;
    private $type;

    public function __construct($fromAlias, $table, $onClause, $alias = '', $type = self::TYPE_INNER)
    {
        $this->fromAlias = $fromAlias;
        $this->table = $table;
        $this->onClause = $onClause;
        $this->alias = $alias ?: $table;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getFromAlias()
    {
        return $this->fromAlias;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getOnClause()
    {
        return $this->onClause;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
