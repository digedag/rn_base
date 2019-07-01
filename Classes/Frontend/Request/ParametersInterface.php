<?php

namespace Sys25\RnBase\Frontend\Request;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2019 Rene Nitzsche <rene@system25.de>
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
 * Interface for configuiration processor
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 */
interface ParametersInterface
{
    /**
     * Liefert den Parameter-Wert
     *
     * @param string $paramName
     * @param string $qualifier
     * @return mixed
     */
    public function get($paramName, $qualifier = '');
    /**
     * removes xss etc. from the value
     *
     * @param string $field
     * @return string
     */
    public function getCleaned($paramName, $qualifier = '');
    /**
     * Liefert den Parameter-Wert als int
     *
     * @param string $paramName
     * @param string $qualifier
     * @return int
     */
    public function getInt($paramName, $qualifier = '');
    /**
     * Liefert alle Parameter-Werte
     *
     * @param string $qualifier
     * @return array
     */
    public function getAll($qualifier = '');
}
