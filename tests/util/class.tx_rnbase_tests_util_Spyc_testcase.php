<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2018 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Spyc');

/**
 *
 * @package TYPO3
 * @subpackage tx_rnbase
 * @author Rene Nitzsche <rene@system25.de>
 */
class tx_rnbase_tests_util_Spyc_testcase extends tx_rnbase_tests_BaseTestCase
{
    const YAML = '---
root_1:
  # some comment
  name: Max
  record:
    uid: 1
    label: Guy 1
root_2:
  # some comment
  name: Moritz
  record:
    uid: 2
    label: Guy 2
';
    public function testYamlLoad()
    {
        $result = tx_rnbase_util_Spyc::yamlLoad(static::YAML);
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals('--', $result[0]);
        $this->assertArrayHasKey('root_1', $result);
        $this->assertArrayHasKey('root_2', $result);

        $this->assertTrue(is_array($result['root_1']));
        $this->assertTrue(is_array($result['root_2']));
        $this->assertArrayHasKey('name', $result['root_1']);
        $this->assertEquals('Max', $result['root_1']['name']);
        $this->assertArrayHasKey('record', $result['root_1']);
        $this->assertArrayHasKey('name', $result['root_2']);
        $this->assertEquals('Moritz', $result['root_2']['name']);
        $this->assertArrayHasKey('record', $result['root_2']);

    }
}
