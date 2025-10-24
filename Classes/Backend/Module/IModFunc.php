<?php

namespace Sys25\RnBase\Backend\Module;

use Psr\Http\Message\ServerRequestInterface;

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2021 Rene Nitzsche (rene@system25.de)
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

interface IModFunc
{
    public const ICON_OK = -1;

    public const ICON_INFO = 1;

    public const ICON_WARN = 2;

    public const ICON_FATAL = 3;

    //    public function init(IModule $module, $conf);

    /**
     * Module identifier for ts_config.
     */
    public function getModuleIdentifier();

    /**
     * Liefert den HTML-String des Moduls.
     *
     * @return string
     */
    public function main(?ServerRequestInterface $request = null);
}
