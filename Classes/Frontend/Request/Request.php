<?php

namespace Sys25\RnBase\Frontend\Request;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;

/***************************************************************
* Copyright notice
*
* (c) 2007-2019 René Nitzsche <rene@system25.de>
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

class Request implements RequestInterface
{
    private $configurations;

    private $confId;

    private $parameters;

    private $viewContext;

    public function __construct(
        ParametersInterface $parameters,
        ConfigurationInterface $configurations,
        $confId
    ) {
        $this->configurations = $configurations;
        $this->confId = $confId;
        $this->parameters = $parameters;
        $this->viewContext = $configurations->getViewData();
    }

    public function getConfigurations(): ConfigurationInterface
    {
        return $this->configurations;
    }

    public function getConfId(): string
    {
        return $this->confId;
    }

    public function getParameters(): ParametersInterface
    {
        return $this->parameters;
    }

    public function getViewContext(): ContextInterface
    {
        return $this->viewContext;
    }
}
