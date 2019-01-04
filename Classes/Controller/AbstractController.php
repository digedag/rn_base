<?php
namespace Sys25\RnBase\Controller;

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
 * Abstract Controller
 *
 * Abstract methods to implement:
 *   - protected function doRequest() To start the Dance!
 *   - protected function getTemplateName() The Template Key
 *
 * Per default a fluid Template is used, which has to be configured.
 * See https://github.com/digedag/rn_base/blob/master/Documentation/fe_plugins.md#fluid
 *
 * @package TYPO3
 * @subpackage Sys25\RnBase
 * @author Michael Waner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractController extends \tx_rnbase_action_BaseIOC
{
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    protected function handleRequest(&$parameters, &$configurations, &$viewdata)
    {
        // @codingStandardsIgnoreEnd
        return $this->doRequest();
    }

    /**
     * Wrapper method clean code
     *
     * @return string Errorstring or NULL
     */
    abstract protected function doRequest();
    /*
     * {
     * $parameters = $this->getParameters();
     * $configurations = $this->getConfigurations();
     * $viewData = $this->getViewData();
     *
     * return null;
     * }
     */

    /**
     * The View to use.
     *
     * Per default it will be the new Fluid view.
     * This can be overwritten by child class.
     *
     * @return string
     */
    protected function getViewClassName()
    {
        return \Sys25\RnBase\Fluid\View\Action::class;
    }

    /**
     * Helper method to set some data directly to the view.
     *
     * @param string $name
     * @param mixed $data
     *
     * @return $this
     */
    protected function assignToView($name, $data)
    {
        $this->getViewData()->offsetSet($name, $data);

        return $this;
    }

    /**
     * Just a wraper for Tx_Rnbase_Configuration_ProcessorInterface::get
     *
     * The configuration id of this controller will be prefixed.
     *
     * @param string $confId
     *
     * @return array|string|null
     */
    protected function getConfigurationValue($confId)
    {
        return $this->getConfigurations()->get($this->getConfId() . $confId);
    }
}
