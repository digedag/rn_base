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
	public function testIsEmpty() {
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<root>
				<child1 value="123"/>
				<child2>value</child2>
				<child3><child31>44</child31><child32/></child3>
</root>
EOT;
		$node = $this->parseXml($xml);
 		$this->assertFalse($node->isEmpty(), 'root is empty');
 		$child = $node->getNodeFromPath('child1');
 		$this->assertFalse($child->isEmpty(), 'child1 is empty');
 		$child = $node->getNodeFromPath('child3');
 		$this->assertFalse($child->isEmpty(), 'child3 is empty');
 		$child = $node->getNodeFromPath('child3.child31');
 		$this->assertTrue($child->isEmpty(), 'child3.child31 is not empty');
 		$child = $node->getNodeFromPath('child3.child32');
 		$this->assertTrue($child->isEmpty(), 'child3.child32 is not empty');
	}

	public function testGetIntFromPath() {
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<root>
				<child1 value="123"/>
				<child2>22</child2>
				<child3>12.3</child3>
				<child4>vierzehn</child4>
</root>
EOT;

		$node = $this->parseXml($xml);
		$this->assertSame(123, $node->getIntFromPath('child1.value'));
		$this->assertSame(22, $node->getIntFromPath('child2'));
		$this->assertSame(12, $node->getIntFromPath('child3'));
		$this->assertSame(0, $node->getIntFromPath('child4'));
	}

	/**
	 * @param string $xml
	 * @return tx_rnbase_util_XmlElement
	 */
	private function parseXml($xml) {
		return tx_rnbase::makeInstance('tx_rnbase_util_XmlElement', $xml);
	}
}

