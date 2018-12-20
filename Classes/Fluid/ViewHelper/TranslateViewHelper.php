<?php
namespace Sys25\RnBase\Fluid\ViewHelper;

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

use \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Sys25\RnBase\Fluid\ViewHelper\TranslateViewHelper
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslateViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Translates a rn base label
     *
     * @param string $key The key for the label to get the translation for
     *
     * @return string
     */
    public function render($key)
    {
        return $this->getConfigurations()->getLL($key);
    }

    /**
     * @return \Tx_Rnbase_Configuration_Processor
     */
    protected function getConfigurations()
    {
        return $this->controllerContext->configurations;
    }
}
