<?php

namespace Sys25\RnBase\Frontend\Provider;

use Sys25\RnBase\Frontend\Controller\AbstractAction;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2024 Rene Nitzsche (rene@system25.de)
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
 * Zentrale Klasse für den Zugriff auf verschiedene Frontend-Services.
 * Zunächst für DI von Action-Klassen.
 */
class FrontendServiceProvider implements \TYPO3\CMS\Core\SingletonInterface
{
    private $actions = [];

    public function addAction(AbstractAction $action)
    {
        $alias = $action->getAlias();
        $this->actions[get_class($action)] = $action;
        if ($alias) {
            $this->actions[$alias] = $action;
        }
    }

    /**
     * @return AbstractAction|null
     */
    public function getAction(string $type)
    {
        return $this->actions[$type] ?? null;
    }
}
