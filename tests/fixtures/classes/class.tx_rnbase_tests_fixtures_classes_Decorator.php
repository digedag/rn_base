<?php

/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 */

use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Domain\Model\DataInterface;

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 */
class tx_rnbase_tests_fixtures_classes_Decorator implements Sys25\RnBase\Backend\Decorator\InterfaceDecorator
{
    private $mod;

    /**
     * @param IModule $mod
     */
    public function __construct(IModule $mod)
    {
        $this->mod = $mod;
    }

    /**
     * @param string $columnValue
     * @param string $columnName
     * @param array $record
     * @param DataInterface $entry
     */
    public function format($columnValue, $columnName, array $record, DataInterface $entry)
    {
        $ret = $columnValue;

        // wir manipulieren ein bisschen die daten um zu sehen ob der decorator ansprint
        if ('col1' == $columnName) {
            $ret = str_replace('col1', 'spalte1', $ret);
        }

        return $ret;
    }
}
