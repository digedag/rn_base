<?php

namespace Sys25\RnBase\Domain\Repository;

use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015-2023 René Nitzsche <rene@system25.de>
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
 * An verschiedenen Stellen werden Repos in Klassen benötigt, bei denen DI noch
 * nicht möglich ist. Damit die Repos deswegen nicht public deklariert werden müssen,
 * werden sie in dieser Registry gesammmelt und können static angerufen werden.
 */
class RepositoryRegistry
{
    private static $repos = [];

    public function addRepository(AbstractRepository $repository): void
    {
        self::$repos[get_class($repository->getEmptyModel())] = $repository;
    }

    public static function getRepositoryForClass(string $modelClass): ?AbstractRepository
    {
        $instance = tx_rnbase::makeInstance(self::class);

        return $instance::$repos[$modelClass] ?? null;
    }
}
