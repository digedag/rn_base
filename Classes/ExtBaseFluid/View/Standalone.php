<?php

namespace Sys25\RnBase\ExtBaseFluid\View;

use Sys25\RnBase\Configuration\ConfigurationInterface;

/***************************************************************
 * Copyright notice
 *
 * (c) René Nitzsche <rene@system25.de>
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

/**
 * Sys25\RnBase\ExtBaseFluid\View$Standalone.
 *
 * wrapper for thre standalone view of fluid
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Standalone extends \TYPO3\CMS\Fluid\View\StandaloneView
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     *
     * @deprecated unused since TYPO3 v11, will be removed in v12
     */
    protected $objectManager;

    /**
     * @var ConfigurationInterface
     */
    protected $configurations;

    /**
     * @return ConfigurationInterface
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param ConfigurationInterface $configurations
     */
    public function setConfigurations(ConfigurationInterface $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}
