<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_mod_BaseModule');

/**
 * Backend Modul für rnbase.
 */
class tx_rnbase_tests_fixtures_classes_Mod extends Sys25\RnBase\Backend\Module\BaseModule
{
    public $pageinfo;

    public $tabs;

    /**
     * Method to get the extension key.
     *
     * @return string Extension key
     */
    public function getExtensionKey()
    {
        return 'rnbase';
    }

    /**
     * Method to set the tabs for the mainmenu
     * Umstellung von SelectBox auf Menu.
     */
    protected function getFuncMenu()
    {
        $mainmenu = $this->getFormTool()->showTabMenu($this->getPid(), 'function', $this->getName(), $this->MOD_MENU['function']);

        return $mainmenu['menu'];
    }

    /**
     * Returns the module ident name.
     *
     * @return string
     */
    public function getName()
    {
        return 'dummyMod';
    }

    /**
     * workaround, da ansonsten ->main() aufgerufen werden müsste, um $doc zu setzen.
     * In ->main() wird aber auch das rendering ausgeführt, was dann zu ungewollten Ausgaben,
     * Fehlern etc. führt.
     *
     * {@inheritdoc}
     *
     * @see tx_rnbase_mod_BaseModule::getDoc()
     */
    public function getDoc()
    {
        if (!$this->doc) {
            if (isset($GLOBALS['TBE_TEMPLATE'])) {
                $this->doc = $GLOBALS['TBE_TEMPLATE'];
            } else {
                $this->doc = tx_rnbase::makeInstance(
                    tx_rnbase_util_Typo3Classes::getDocumentTemplateClass()
                );
            }
        }

        return $this->doc;
    }
}
