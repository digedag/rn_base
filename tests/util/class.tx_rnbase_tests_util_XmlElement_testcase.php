<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2015 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_XmlElement');


/**
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_tests
 * @author René Nitzsche <rene@system25.de>
 */
class tx_rnbase_tests_util_XmlElement_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * Simple test to ensure class is available
	 */
	public function testMakeInstance() {
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<root/>
EOT;
		$node = tx_rnbase::makeInstance('tx_rnbase_util_XmlElement', $xml);
		$this->assertTrue($node instanceof tx_rnbase_util_XmlElement);
	}

	public function testGetIntFromPath() {
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<root>
				<child1 value="123"/>
				<child2>22</child2>
</root>
EOT;
		$node = tx_rnbase::makeInstance('tx_rnbase_util_XmlElement', $xml);
		$this->assertEquals($node->getIntFromPath('child1.value'), 123);
		$this->assertEquals($node->getIntFromPath('child2'), 22);
	}

}

