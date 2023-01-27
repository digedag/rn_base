<?php

namespace Sys25\RnBase\Backend\Utility;

use Sys25\RnBase\Testing\BaseTestCase;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
*  Copyright notice
*
*  (c) 2007-2021 Rene Nitzsche (rene@system25.de)
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
 * Tx_Rnbase_Backend_Utility_IconsTest.
 *
 * @author          Hannes Bochmann <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class IconsTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::setSingletonInstance(
            IconRegistry::class,
            $this->prophesize(IconRegistry::class)->reveal()
        );
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @group unit
     */
    public function testGetIconRegistry()
    {
        $factory = Icons::getIconRegistry();
        $this->assertInstanceOf(IconRegistry::class, $factory);
    }
}
