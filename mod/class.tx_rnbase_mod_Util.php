<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('Tx_Rnbase_Backend_Utility_Icons');

class tx_rnbase_mod_Util
{
    /**
     * Retrieve (and update) a value from module data.
     *
     * @param string                $key
     * @param tx_rnbase_mod_IModule $mod
     * @param array                 $options
     */
    public static function getModuleValue($key, tx_rnbase_mod_IModule $mod, $options = [])
    {
        $changedSettings = is_array($options['changed']) ? $options['changed'] : [];
        $type = isset($options['type']) ? $options['type'] : '';
        $modData = Tx_Rnbase_Backend_Utility::getModuleData([$key => ''], $changedSettings, $mod->getName(), $type);

        return isset($modData[$key]) ? $modData[$key] : null;
    }

    /**
     * Returns all data for a module for current BE user.
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param string                $type If type is 'ses' then the data is stored as session-lasting data. This means that it'll be wiped out the next time the user logs in.
     */
    public static function getUserData(tx_rnbase_mod_IModule $mod, $type = '')
    {
        $settings = $GLOBALS['BE_USER']->getModuleData($mod->getName(), $type);

        return $settings;
    }

    /**
     * Returns a TYPO3 sprite icon.
     *
     * @param string $iconName
     * @param array  $options
     * @param array  $overlays
     *
     * @return string The full HTML tag (usually a <span>)
     */
    public static function getSpriteIcon($iconName, array $options = [], array $overlays = [])
    {
        return Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon($iconName, $options, $overlays);
    }

    /**
     * Returns a string with all available Icons in TYPO3 system. Each icon has a tooltip with its identifier.
     *
     * @return string
     */
    public static function debugSprites()
    {
        return Tx_Rnbase_Backend_Utility_Icons::debugSprites();
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück.
     *
     * @TODO: move to an selector!
     *
     * @param array  $aItems       Array mit den werten der Auswahlbox
     * @param mixed  $selectedItem
     * @param string $sDefId       ID-String des Elements
     * @param array  $aData        enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array  $aOptions     zusätzliche Optionen: label, id
     *
     * @return string selected item
     */
    public static function showSelectorByArray($aItems, $selectedItem, $sDefId, &$aData, $aOptions = [])
    {
        $id = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;
        $pid = isset($aOptions['pid']) && $aOptions['pid'] ? $aOptions['pid'] : 0;

        // Build select box items
        $aData['selector'] = Tx_Rnbase_Backend_Utility::getFuncMenu(
            $pid,
            'SET['.$id.']',
            $selectedItem,
            $aItems
        );

        //label
        $aData['label'] = $aOptions['label'];

        // as the deleted fe users have always to be hidden the function returns always FALSE
        //@todo wozu die alte abfrage? return $defId==$id ? FALSE : $selectedItem;
        return $selectedItem;
    }
}
