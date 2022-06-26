<?php

namespace Sys25\RnBase\Domain\Model;

use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Rene Nitzsche (rene@system25.de)
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
 * Model for fe_user.
 */
class FeUser extends BaseModel
{
    public function getTableName()
    {
        return 'fe_users';
    }

    /**
     * Whether or not user details should be shown.
     *
     * @return bool
     */
    public function isDetailsEnabled()
    {
        return true;
    }

    /**
     * Whether or not user is disabled in FE.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return intval($this->getProperty('disable')) > 0;
    }

    /**
     * Returns the users email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getProperty('email');
    }

    /**
     * Whether or not user has an active session.
     */
    public function isSessionActive()
    {
        return tx_t3users_util_ServiceRegistry::getFeUserService()->isUserOnline($this->getUid());
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getProperty('username');
    }
}
