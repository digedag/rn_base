<?php

namespace Sys25\RnBase\Domain\Repository;

use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Search\System\FeGroupSearch;

/***************************************************************
 * Copyright notice
 *
 * (c) 2022 Rene Nitzsche (rene@system25.de)
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

class FeGroupRepository extends PersistenceRepository
{
    /**
     * (non-PHPdoc).
     *
     * @see AbstractRepository::getSearchClass()
     */
    protected function getSearchClass()
    {
        return FeGroupSearch::class;
    }

    /**
     * Returns all usergroups.
     *
     * @return array of tx_t3users_models_fegroup or empty array
     */
    public function getGroupsByUser(FeUser $feuser)
    {
        if (!$feuser->getProperty('usergroup')) {
            return [];
        }

        $fields = $options = [];
        $fields['FEGROUP.UID'][OP_IN_INT] = $feuser->getProperty('usergroup');

        return $this->search($fields, $options);
    }

    /**
     * Prüft ob der User der gegebenen Gruppe angehört.
     *
     * @param FeUser $feuser
     * @param int $groupUid
     *
     * @return bool
     */
    public function isUserInGroup(FeUser $feuser, $groupUid)
    {
        foreach ($this->getGroupsByUser($feuser) as $group) {
            if ($group->getUid() === (int) $groupUid) {
                return true;
            }
        }

        return false;
    }
}
