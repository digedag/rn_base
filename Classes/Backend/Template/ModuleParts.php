<?php

namespace Sys25\RnBase\Backend\Template;

/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017-2021 René Nitzsche <rene@system25.de>
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

class ModuleParts
{
    private $title;

    private $header;

    private $content;

    private $subMenu;

    private $funcMenu;

    private $buttons;

    private $selector;

    /**
     * @var array
     */
    private $pageInfo;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getSubMenu()
    {
        return $this->subMenu;
    }

    public function setSubMenu($subMenu)
    {
        $this->subMenu = $subMenu;

        return $this;
    }

    public function getFuncMenu()
    {
        return $this->funcMenu;
    }

    public function setFuncMenu($funcMenu)
    {
        $this->funcMenu = $funcMenu;

        return $this;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    public function setButtons($buttons)
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function setSelector($selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * @return array
     */
    public function getPageInfo()
    {
        return $this->pageInfo;
    }

    /**
     * @param array $pageInfo
     *
     * @return ModuleParts
     */
    public function setPageInfo(array $pageInfo)
    {
        $this->pageInfo = $pageInfo;

        return $this;
    }
}
