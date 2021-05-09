<?php

namespace Sys25\RnBase\Domain\Model;

use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche <rene@system25.de>
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
 * Trait, der einen storage bereit stellt, um daten abzulegen.
 *
 * Example:
 *     protected function getUser()
 *     {
 *         if (!$this->getStorage()->hasUser()) {
 *             $repo = Factory::getUserRepository();
 *             $storage->setFeUser($repo->findByUid($this->getUserId));
 *         }
 *         return $storage->getUser();
 *     }
 *
 * @author Michael Wagner
 */
trait StorageTrait
{
    /**
     * The storage.
     *
     * @var DataModel
     */
    private $storage = null;

    /**
     * Returns a storage.
     *
     * @return DataModel
     */
    protected function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = tx_rnbase::makeInstance(DataModel::class);
        }

        return $this->storage;
    }
}
