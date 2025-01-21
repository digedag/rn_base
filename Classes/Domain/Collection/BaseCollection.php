<?php

namespace Sys25\RnBase\Domain\Collection;

use Sys25\RnBase\Domain\Model\RecordInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2021 Rene Nitzsche <rene@system25.de>
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
 * Only a wrapper for doctrine array collection.
 *
 * @author Michael Wagner
 */
class BaseCollection extends \Contrib\Doctrine\Common\Collections\ArrayCollection
{
    /**
     * Only a wrapper for add.
     *
     * @param mixed $value The element to add
     *
     * @return bool Always true
     */
    public function append($value)
    {
        return $this->add($value);
    }

    /**
     * Exchange the collection for another one and returns the old elements.
     *
     * @param array $elements
     *
     * @return array
     */
    public function exchange(
        array $elements = [],
    ) {
        $old = $this->toArray();

        $this->clear();

        foreach ($elements as $offset => $value) {
            $this->offsetSet($offset, $value);
        }

        return $old;
    }

    /**
     * Only an ArrayObject alias for exchange.
     *
     * @param array $elements
     *
     * @return array
     */
    public function exchangeArray(
        array $elements = [],
    ) {
        return $this->exchange($elements);
    }

    /**
     * Returns a list of uids.
     *
     * @return array
     */
    public function getUids()
    {
        return $this->map(
            function (RecordInterface $model) {
                return $model->getUid();
            }
        )->toArray();
    }
}
