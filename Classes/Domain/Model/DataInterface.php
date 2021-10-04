<?php

namespace Sys25\RnBase\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche <rene@system25.de>
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
 * This interface defines a base domain model.
 * TODO: this interface should extend IteratorAggregate.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
interface DataInterface
{
    /**
     * Returns TRUE if the model was modified after initialisation.
     *
     * @return bool
     */
    public function isDirty();

    /**
     * Returns TRUE if the model has no data.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Setzt einen Wert oder ersetzt alle Werte.
     *
     * @param string|array $property
     * @param mixed        $value
     *
     * @return DataModel
     */
    public function setProperty($property, $value = null);

    /**
     * Liefert einen bestimmten Wert oder alle.
     *
     * @param string $property
     *
     * @return string
     */
    public function getProperty($property = null);

    /**
     * Entfernt einen Wert.
     *
     * @param string $property
     *
     * @return DataModel
     */
    public function unsProperty($property);

    /**
     * Prüft ob eine Spalte gesetzt ist.
     *
     * @param string $property
     *
     * @return string
     */
    public function hasProperty($property);
}
