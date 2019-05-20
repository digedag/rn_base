<?php
/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('tx_rnbase_util_TYPO3');

class Tx_Rnbase_Backend_Template_Override_Doc extends TYPO3\CMS\Backend\Template\DocumentTemplate
{
}

class Tx_Rnbase_Backend_Template_Override_DocumentTemplate extends Tx_Rnbase_Backend_Template_Override_Doc
{
    /**
     * Override deprecated and removed method
     */
    public function getPageRenderer()
    {
        if (tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            return $this->pageRenderer;
        } elseif (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $this->initPageRenderer();
            return $this->pageRenderer;
        }
        return parent::getPageRenderer();
    }


    /* *** ************************************************ *** *
     * *** ************************************************ *** *
     * *** Removed Tab-menu Methods (removed since TYPO3 9) *** *
     * *** ************************************************ *** *
     * *** ************************************************ *** */

    /**
     * Creates a tab menu from an array definition
     *
     * Returns a tab menu for a module
     * Requires the JS function jumpToUrl() to be available
     *
     * @param mixed $mainParams is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $elementName it the form elements name, probably something like "SET[...]
     * @param string $currentValue is the value to be selected currently.
     * @param array $menuItems is an array with the menu items for the selector box
     * @param string $script is the script to send the &id to, if empty it's automatically found
     * @param string $addparams is additional parameters to pass to the script.
     * @return string HTML code for tab menu
     * @deprecated in Core since TYPO3 CMS 8, was removed from Core in TYPO3 CMS 9.
     */
    public function getTabMenu($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '')
    {
        $content = '';
        if (is_array($menuItems)) {
            if (!is_array($mainParams)) {
                $mainParams = ['id' => $mainParams];
            }
            $mainParams = Tx_Rnbase_Utility_T3General::implodeArrayForUrl('', $mainParams);
            if (!$script) {
                $script = basename(PATH_thisScript);
            }
            $menuDef = [];
            foreach ($menuItems as $value => $label) {
                $menuDef[$value]['isActive'] = (string)$currentValue === (string)$value;
                $menuDef[$value]['label'] = htmlspecialchars($label, ENT_COMPAT, 'UTF-8', false);
                $menuDef[$value]['url'] = $script . '?' . $mainParams . $addparams . '&' . $elementName . '=' . $value;
            }
            $content = $this->getTabMenuRaw($menuDef);
        }
        return $content;
    }

    /**
     * Creates the HTML content for the tab menu
     *
     * @param array $menuItems Menu items for tabs
     * @return string Table HTML
     * @access private
     */
    public function getTabMenuRaw($menuItems)
    {
        if (!is_array($menuItems)) {
            return '';
        }
        $options = '';
        foreach ($menuItems as $id => $def) {
            $class = $def['isActive'] ? 'active' : '';
            $label = $def['label'];
            $url = htmlspecialchars($def['url']);
            $params = $def['addParams'];
            $options .= '<li class="' . $class . '">' . '<a href="' . $url . '" ' . $params . '>' . $label . '</a>' . '</li>';
        }
        return '<ul class="nav nav-tabs" role="tablist">' . $options . '</ul>';
    }
}
