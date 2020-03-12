<?php
/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017-2020 René Nitzsche <rene@system25.de>
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
    const STATE_OK = -1;

    const STATE_NOTICE = 1;

    const STATE_WARNING = 2;

    const STATE_ERROR = 3;

    /**
     * Override deprecated and removed method.
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
     * Creates a tab menu from an array definition.
     *
     * Returns a tab menu for a module
     * Requires the JS function jumpToUrl() to be available
     *
     * @param mixed  $mainParams   is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $elementName  it the form elements name, probably something like "SET[...]
     * @param string $currentValue is the value to be selected currently
     * @param array  $menuItems    is an array with the menu items for the selector box
     * @param string $script       is the script to send the &id to, if empty it's automatically found
     * @param string $addparams    is additional parameters to pass to the script
     *
     * @return string HTML code for tab menu
     *
     * @deprecated in Core since TYPO3 CMS 8, was removed from Core in TYPO3 CMS 9
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
                $script = basename(\Sys25\RnBase\Utility\Environment::getCurrentScript());
            }
            $menuDef = [];
            foreach ($menuItems as $value => $label) {
                $menuDef[$value]['isActive'] = (string) $currentValue === (string) $value;
                $menuDef[$value]['label'] = htmlspecialchars($label, ENT_COMPAT, 'UTF-8', false);
                $menuDef[$value]['url'] = $script.'?'.$mainParams.$addparams.'&'.$elementName.'='.$value;
            }
            $content = $this->getTabMenuRaw($menuDef);
        }

        return $content;
    }

    /**
     * Creates the HTML content for the tab menu.
     *
     * @param array $menuItems Menu items for tabs
     *
     * @return string Table HTML
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
            $options .= '<li class="'.$class.'">'.'<a href="'.$url.'" '.$params.'>'.$label.'</a>'.'</li>';
        }

        return '<ul class="nav nav-tabs" role="tablist">'.$options.'</ul>';
    }

    /**
     * Returns a blank <div>-section with a height.
     *
     * @param int $dist Padding-top for the div-section (should be margin-top but konqueror (3.1) doesn't like it :-(
     *
     * @return string HTML content
     *
     * @todo Define visibility
     */
    public function spacer($dist)
    {
        if ($dist > 0) {
            return '<!-- Spacer element --><div style="padding-top: '.(int) $dist.'px;"></div>';
        }
    }

    /**
     * Begins an output section and sets header and content.
     *
     * @param string $label             The header
     * @param string $text              The HTML-content
     * @param bool   $nostrtoupper      A flag that will prevent the header from being converted to uppercase
     * @param bool   $sH                Defines the type of header (if set, "<h3>" rather than the default "h4")
     * @param int    $type              The number of an icon to show with the header (see the icon-function). -1,1,2,3
     * @param bool   $allowHTMLinHeader If set, HTML tags are allowed in $label (otherwise this value is by default htmlspecialchars()'ed)
     *
     * @return string HTML content
     *
     * @see icons(), sectionHeader()
     */
    public function section($label, $text, $nostrtoupper = false, $sH = false, $type = 0, $allowHTMLinHeader = false)
    {
        if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            return parent::section($label, $text, $nostrtoupper, $sH, $type, $allowHTMLinHeader);
        }
        $title = $label;
        $message = $text;
        $disableIcon = 0 == $type;

        $classes = [
            self::STATE_NOTICE => 'info',
            self::STATE_OK => 'success',
            self::STATE_WARNING => 'warning',
            self::STATE_ERROR => 'danger',
        ];
        $icons = [
            self::STATE_NOTICE => 'lightbulb-o',
            self::STATE_OK => 'check',
            self::STATE_WARNING => 'exclamation',
            self::STATE_ERROR => 'times',
        ];
        $stateClass = isset($classes[$type]) ? $classes[$type] : null;
        $icon = isset($icons[$type]) ? $icons[$type] : null;
        $iconTemplate = '';
        if (!$disableIcon) {
            $iconTemplate = ''.
                '<div class="media-left">'.
                  '<span class="fa-stack fa-lg callout-icon">'.
                    '<i class="fa fa-circle fa-stack-2x"></i>'.
                    '<i class="fa fa-'.htmlspecialchars($icon).' fa-stack-1x"></i>'.
                  '</span>'.
                '</div>';
        }
        $titleTemplate = '';
        if (null !== $title) {
            $title = $allowHTMLinHeader ? $title : htmlspecialchars($title);
            $titleTemplate = '<h4 class="callout-title">'.$title.'</h4>';
        }

        return '<div class="callout callout-'.htmlspecialchars($stateClass).'">'.
                 '<div class="media">'.
                    $iconTemplate.
                   '<div class="media-body">'.
                     $titleTemplate.
                   '<div class="callout-body">'.$message.'</div>'.
                   '</div>'.
                 '</div>'.
               '</div>';
    }

    /**
     * Inserts a hr tag divider.
     *
     * @param int $dist the margin-top/-bottom of the <hr> ruler
     *
     * @return string HTML content
     */
    public function divider($dist)
    {
        if (!tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            return parent::divider($dist);
        }
        $dist = (int) $dist;

        return '<!-- DIVIDER --><hr style="margin-top: '.$dist.'px; margin-bottom: '.$dist.'px;" />';
    }
}
