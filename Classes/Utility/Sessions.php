<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2022 René Nitzsche <rene@system25.de>
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

class Sessions
{
    /**
     * Set a session value.
     * The value is stored in TYPO3 session storage.
     *
     * @param string $key
     * @param mixed $value
     * @param string $extKey
     */
    public function setSessionValue($key, $value, $extKey = 'common')
    {
        $feuser = TYPO3::getFEUser();
        $vars = $feuser->getKey('ses', $extKey);
        $vars[$key] = &$value;
        $feuser->setKey('ses', $extKey, $vars);
    }

    /**
     * Returns a session value.
     *
     * @param string $key key of session value
     * @param string $extKey optional
     *
     * @return mixed or null
     */
    public function getSessionValue($key, $extKey = 'common')
    {
        $feuser = TYPO3::getFEUser();
        if (is_object($feuser)) {
            $vars = $feuser->getKey('ses', $extKey);

            return $vars[$key];
        }

        return null;
    }

    /**
     * Removes a session value.
     *
     * @param string $key key of session value
     * @param string $extKey optional
     */
    public function removeSessionValue($key, $extKey = 'common')
    {
        $feuser = TYPO3::getFEUser();
        if (is_object($feuser)) {
            $vars = $feuser->getKey('ses', $extKey);
            unset($vars[$key]);
            $feuser->setKey('ses', $extKey, $vars);
        }
    }

    public function forcePersists()
    {
        $feuser = TYPO3::getFEUser();
        $feuser->storeSessionData();
    }
}
