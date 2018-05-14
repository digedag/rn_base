<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
 *  Contact: rene@system25.de
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

tx_rnbase::load('tx_rnbase_action_CacheHandlerDefault');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_cache_Manager');




/**
 * Caching handler that saves data for feusers. For unregistered users the handler uses PHP-Session-ID
 * for identification.
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <michael.wagner@dmk-ebusines.de>
 */
class tx_rnbase_action_CacheHandlerUser extends tx_rnbase_action_CacheHandlerDefault
{

    /**
     * Returns the current session id.
     *
     * @return string
     */
    protected function getSessionId()
    {
        if (session_id() == '') {
            session_start();
        }

        return session_id();
    }


    /**
     * @return array
     */
    protected function getCacheKeyParts()
    {
        $keys = parent::getCacheKeyParts();
        $keys[] = $this->getSessionId();

        return $keys;
    }
}
