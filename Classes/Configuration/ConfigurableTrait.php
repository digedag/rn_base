<?php

namespace Sys25\RnBase\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2020 Rene Nitzsche <rene@system25.de>
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
 * Trait to add configuration processor.
 *
 * @author Michael Wagner
 */
trait ConfigurableTrait
{
    /**
     * @var ConfigurationInterface
     */
    protected $configurations;

    /**
     * @var string
     */
    protected $confId = '';

    /**
     * Set the configuration object.
     *
     * @param ConfigurationInterface $configurations
     *
     * @return ConfigurableTrait
     */
    public function setConfigurations(
        ConfigurationInterface $configurations
    ) {
        $this->configurations = $configurations;

        return $this;
    }

    /**
     * The configuration object.
     *
     * @return ConfigurationInterface
     */
    protected function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Set the configuration id.
     *
     * @param string $configurations
     *
     * @return ConfigurableTrait
     */
    public function setConfId(
        $confId
    ) {
        $this->confId = $confId;

        return $this;
    }

    /**
     * The configuration id.
     *
     * @return string
     */
    protected function getConfId()
    {
        return $this->confId;
    }

    /**
     * Returns a value from config.
     *
     * @param string $path
     *
     * @return array|string|null
     */
    protected function getConfValue($path, $deep = false)
    {
        if (!$this->getConfigurations() instanceof ConfigurationInterface) {
            return null;
        }

        return $this->getConfigurations()->get(
            $this->getConfId().$path,
            $deep
        );
    }
}
