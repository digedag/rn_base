<?php
namespace Sys25\RnBase\Fluid\ViewHelper\Configurations;

use Sys25\RnBase\Fluid\View\Factory;

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
 * Sys25\RnBase\Fluid\ViewHelper\Configurations$GetViewHelperTest
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class GetViewHelperTest extends \tx_rnbase_tests_BaseTestCase
{

    /**
     * @group integration
     */
    public function testRender()
    {
        $configurations = $this->createConfigurations(array('myConfId.' => array('mySubPath' => 'testValue')), 'rn_base');
        $view = Factory::getViewInstance($configurations);
        $view->setTemplatePathAndFilename(
            \tx_rnbase_util_Files::getFileAbsFileName('EXT:rn_base/tests/fixtures/html/GetViewHelper.html')
        );

        self::assertEquals('testValue', trim($view->render()));
    }
}
