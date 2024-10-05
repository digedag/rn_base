<?php

namespace Sys25\RnBase\Frontend\Service;

use ReflectionClass;
use Sys25\RnBase\Frontend\Controller\AbstractAction;
use Sys25\RnBase\Frontend\View\AbstractView;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2024 Rene Nitzsche <rene@system25.de>
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

class FrontendServiceProvider
{
    private $actions = [];
    private $views = [];
    private $filters = [];

    public function addFrontendAction(AbstractAction $action): void
    {
        $this->actions[get_class($action)] = $action;
    }

    public function getActionForClass(string $actionClass): ?AbstractAction
    {
        if (!array_key_exists($actionClass, $this->actions) && class_exists($actionClass)) {
            // Hier werden häufig aliases verwendet.
            $reflection = new ReflectionClass($actionClass);
            $action = $reflection->getName();
            $this->actions[$actionClass] = $this->actions[$action] ?? null;
        }

        return $this->actions[$actionClass] ?? null;
    }

    public function addFrontendView(AbstractView $view): void
    {
        $this->views[get_class($view)] = $view;
    }

    public function getViewForClass(string $viewClass): ?AbstractView
    {
        return $this->views[$viewClass] ?? null;
    }
}
