<?php

namespace Sys25\RnBase\Utility;

use InvalidArgumentException;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2019-2021 René Nitzsche <rene@system25.de>
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

/**
 * Wrapper for TYPO3 registry.
 */
class Registry
{
    private $domain;

    /**
     * @param string $domain Extension key for extensions starting with 'tx_' / 'Tx_' / 'user_' or 'core' for core registry entries
     */
    public function __construct($domain = '')
    {
        $this->domain = $domain;
    }

    /**
     * Read a value from registry.
     *
     * @param string $key          the key of the entry to return
     * @param string $domain       Extension key for extensions starting with 'tx_' / 'Tx_' / 'user_' or 'core' for core registry entries
     * @param mixed  $defaultValue Optional default value to use if this entry has never been set. Defaults to NULL.
     *
     * @return mixed the value of the entry
     *
     * @throws InvalidArgumentException Throws an exception if the given namespace is not valid
     */
    public function get($key, $domain = '')
    {
        $domain = $domain ? $domain : $this->domain;
        /* @var $registry \TYPO3\CMS\Core\Registry */
        $registry = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Registry::class);

        return $registry->get($domain, $key);
    }

    /**
     * @param string $key    the key of the entry to set
     * @param mixed  $value  The value to set. This can be any PHP data type; this class takes care of serialization if necessary.
     * @param string $domain extension key for extensions starting with 'tx_' / 'Tx_' / 'user_' or 'core' for core registry entries
     *
     * @throws InvalidArgumentException Throws an exception if the given namespace is not valid
     */
    public function set($key, $value, $domain = '')
    {
        $domain = $domain ? $domain : $this->domain;
        /* @var $registry \TYPO3\CMS\Core\Registry */
        $registry = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Registry::class);

        return $registry->set($domain, $key, $value);
    }
}
