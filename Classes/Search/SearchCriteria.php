<?php

namespace Sys25\RnBase\Search;

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2024 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Data holder for search criteria.
 */
class SearchCriteria
{
    private $tableAliases;

    private $joinedFields;
    private $customFields;

    /**
     * Get the value of tableAliases.
     */
    public function getTableAliases()
    {
        return $this->tableAliases;
    }

    /**
     * Set the value of tableAliases.
     */
    public function setTableAliases($tableAliases): self
    {
        $this->tableAliases = $tableAliases;

        return $this;
    }

    /**
     * Get the value of joinedFields.
     */
    public function getJoinedFields()
    {
        return $this->joinedFields;
    }

    /**
     * Set the value of joinedFields.
     */
    public function setJoinedFields($joinedFields): self
    {
        $this->joinedFields = $joinedFields;

        return $this;
    }

    /**
     * Get the value of customFields.
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * Set the value of customFields.
     */
    public function setCustomFields($customFields): self
    {
        $this->customFields = $customFields;

        return $this;
    }
}
