<?php

namespace Sys25\RnBase\Backend\Form\Element;

use Sys25\RnBase\Utility\T3General;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;

/***************************************************************
*  Copyright notice
*
*  (c) 2023 Rene Nitzsche (rene@system25.de)
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
 * Erweitert den LinkButton von TYPO3 um zusätzliche Funktionen.
 * - man kann links ohne Icon setzen
 * - der Hover-Tooltip kann abweichend vom Label sein
 * - die default btn-CSS-Klassen können komplett ersetzt werden.
 */
class EnhancedLinkButton extends LinkButton
{
    private $overrideCss = true;

    /**
     * If set given css classes will override default button classes.
     *
     * @param bool $value
     *
     * @return LinkButton
     */
    public function setOverrideCss($value)
    {
        $this->overrideCss = (bool) $value;

        return $this;
    }

    /**
     * hover tooltip attribute of the link.
     *
     * @var string
     */
    private $hoverText = '';

    /**
     * Get hoverText.
     *
     * @return string
     */
    public function getHoverText()
    {
        return ''.$this->hoverText;
    }

    /**
     * Set tooltip text.
     *
     * @param string $value hover text
     *
     * @return LinkButton
     */
    public function setHoverText($value)
    {
        $this->hoverText = $value;

        return $this;
    }

    /**
     * Renders the markup for the button.
     *
     * @return string
     */
    public function render()
    {
        $cssClasses = 'btn btn-default btn-sm ';
        if ($this->overrideCss && !empty($this->getClasses())) {
            $cssClasses = '';
        }

        $attributes = [
            'href' => $this->getHref(),
            'class' => trim($cssClasses.$this->getClasses()),
            'title' => $this->getHoverText() ?: $this->getTitle(),
        ];
        $labelText = '';
        if ($this->showLabelText) {
            $labelText = ' '.$this->title;
        }
        foreach ($this->dataAttributes as $attributeName => $attributeValue) {
            $attributes['data-'.$attributeName] = $attributeValue;
        }
        if ($this->isDisabled()) {
            $attributes['disabled'] = 'disabled';
            $attributes['class'] .= ' disabled';
        }
        $attributesString = T3General::implodeAttributes($attributes, true);

        $icon = $this->getIcon() ? $this->getIcon()->render() : '';

        return '<a '.$attributesString.'>'
            .trim($icon.htmlspecialchars($labelText))
            .'</a>';
    }
}
