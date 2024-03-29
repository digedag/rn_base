<?php

namespace Sys25\RnBase\Exception;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Throwable;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * @author Hannes Bochmann
 */
interface ExceptionHandlerInterface
{
    /**
     * Interne Verarbeitung der Exception.
     *
     * @param string                                     $actionName
     * @param Throwable                                  $e
     * @param ConfigurationInterface $configurations
     *
     * @return string error message
     */
    public function handleException($actionName, Throwable $e, ConfigurationInterface $configurations);
}
