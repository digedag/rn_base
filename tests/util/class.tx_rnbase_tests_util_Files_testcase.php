<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Files');

/**
 * tx_rnbase_tests_util_Files_testcase
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_tests_util_Files_testcase extends Tx_Phpunit_TestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = array();

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        foreach ($this->testFilesToDelete as $absoluteFileName) {
            rmdir($absoluteFileName);
        }
        parent::tearDown();
    }

    /**
     * Create and return a unique id optionally prepended by a given string
     *
     * This function is used because on windows and in cygwin environments uniqid() has a resolution of one second which
     * results in identical ids if simply uniqid('Foo'); is called.
     *
     * @param string $prefix
     * @return string
     *
     * @todo use TYPO3 Unit Testcase to have this method provided
     */
    protected function getUniqueId($prefix = '')
    {
        return $prefix . str_replace('.', '', uniqid(mt_rand(), true));
    }

    /**
     * @unit
     */
    public function testMkdirDeepCreatesDirectory()
    {
        $directory = 'typo3temp/' . $this->getUniqueId('test_');
        tx_rnbase_util_Files::mkdir_deep(PATH_site, $directory);
        $this->testFilesToDelete[] = PATH_site . $directory;
        $this->assertTrue(is_dir(PATH_site . $directory));
    }

    /**
     * @unit
     */
    public function testMkdirDeepCreatesSubdirectoriesRecursive()
    {
        $directory = 'typo3temp/' . $this->getUniqueId('test_');
        $subDirectory = $directory . '/foo';
        tx_rnbase_util_Files::mkdir_deep(PATH_site, $subDirectory);
        $this->testFilesToDelete[] = PATH_site . $subDirectory;
        $this->testFilesToDelete[] = PATH_site . $directory;
        $this->assertTrue(is_dir(PATH_site . $subDirectory));
    }

    /**
     * @unit
     * @expectedException RuntimeException
     */
    public function testMkdirDeepThrowsExceptionIfDirectoryCreationFails()
    {
        tx_rnbase_util_Files::mkdir_deep('http://localhost');
    }
}
