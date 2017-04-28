<?php
/**
 * Copyright notice
 *
 * (c) 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 */


/**
 * Tx_Rnbase_Hook_DataHandler
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Hook_DataHandler
{

    /**
     * Flushes the cache if a news record was edited.
     * This happens on two levels: by UID and by PID.
     *
     * @param array $params
     */
    public function clearCacheForConfiguredTagsByTable(array $params)
    {
        if (isset($params['table']) && !empty($GLOBALS['TCA'][$params['table']]['ctrl']['cacheTags'])) {
            $cacheManager = $this->getCacheManager();
            foreach ($GLOBALS['TCA'][$params['table']]['ctrl']['cacheTags'] as $cacheTag) {
                $cacheManager->flushCachesInGroupByTag('pages', $cacheTag);
            }
        }
    }

    /**
     * @return TYPO3\CMS\Core\Cache\CacheManager
     */
    protected function getCacheManager()
    {
        return tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getCacheManagerClass());
    }
}
