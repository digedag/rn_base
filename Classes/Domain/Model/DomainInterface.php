<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2007-2016 Rene Nitzsche <rene@system25.de>
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
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
interface Tx_Rnbase_Domain_Model_DomainInterface
{
    /**
     * Liefert den aktuellen Tabellenname
     *
     * @return string
     */
    public function getTableName();

    /**
     * Setzt den aktuellen Tabellenname
     *
     * @param string $tableName
     * @return Tx_Rnbase_Domain_Model_Base
     */
    /*public function setTableName($tableName = 0);*/

    /**
     * Returns the uid
     *
     * @return int
     */
    public function getUid();

    /**
     * Setzt einen Wert oder ersetzt alle Werte
     *
     * @param string|array $property
     * @param mixed $value
     * @return Tx_Rnbase_Domain_Model_Data
     */
    public function setProperty($property, $value = null);

    /**
     * Liefert einen bestimmten Wert oder alle.
     *
     * @param string $property
     * @return string
     */
    public function getProperty($property = null);

    /**
     * Entfernt einen Wert.
     *
     * @param string $property
     * @return Tx_Rnbase_Domain_Model_Data
     */
    public function unsProperty($property);

    /**
     * Prüft ob eine Spalte gesetzt ist.
     *
     * @param string $property
     * @return string
     */
    public function hasProperty($property);

    /**
     * Ist der Datensatz als gelöscht markiert?
     * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
     *
     * @return bool
     */
    public function isDeleted();

    /**
     * Ist der Datensatz als gelöscht markiert?
     * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
     *
     * @return bool
     */
    public function isHidden();

    /**
     * Check if this record is valid.
     * If FALSE, the record is maybe deleted in database.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Check if record is persisted in database. This is if uid is not 0.
     *
     * @return bool
     */
    public function isPersisted();

    /**
     * Liefert bei Tabellen, die im $TCA definiert sind,
     * die Namen der Tabellenspalten als Array.
     *
     * @return array mit Spaltennamen oder 0
     */
    public function getColumnNames();
}
