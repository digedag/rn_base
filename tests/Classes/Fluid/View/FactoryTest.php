<?php
namespace Sys25\RnBase\Fluid\View;

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
 * Sys25\RnBase\Fluid\View$Factory
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class FactoryTest extends \tx_rnbase_tests_BaseTestCase
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!\tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            self::markTestSkipped('Fluid is only supported since TYPO3 8.7');
        }
    }

    /**
     * @group integration
     * @TODO: refactor, requires tx_rnbase_util_TYPO3::getTSFE() which requires initialized database connection class
     */
    public function testGetViewInstance()
    {
        $configurations = $this->createConfigurations(array('someConfig'), 'rn_base');
        $view = Factory::getViewInstance($configurations, array('someFrameworkSettings' => 'mySettings'));

        self::assertSame($configurations, $view->getRenderingContext()->getViewHelperVariableContainer()->getView()->getConfigurations());

        $extbaseConfiguration = $view
            ->getObjectManager()
            ->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface')
            ->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        self::assertSame('mySettings', $extbaseConfiguration['someFrameworkSettings']);
    }
}
