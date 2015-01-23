<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_model_media');

/**
 *
 * @author Hannes Bochmann <hannes.bochmann@dmk-business.de>
 *
 */
class tx_rnbase_tests_model_Media_testcase extends tx_phpunit_testcase {

	/**
	 * @group unit
	 */
	public function testInitMediaForFalMediaSetsFalPropertiesToRecord() {
		if (!tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$this->markTestSkipped('Runs only in TYPO3 6.0 and higher');
		}

		$falModel = $this->getMock(
			'stdClass',
			array('getProperties', 'getUid', 'getPublicUrl')
		);
		$falModel->expects($this->once())
			->method('getProperties')
			->will($this->returnValue(array(
				'title' => 'sample picture reference',
				'description' => 'this is a sample picture',
				'otherField' => '/some/path',
				'otherField2' => '/some/other/path'
			)));

		$mediaModel = tx_rnbase::makeInstance('tx_rnbase_model_media', $falModel);

		$this->assertEquals(
			'sample picture reference', $mediaModel->record['title'],
			'not the title of the reference'
		);
		$this->assertEquals(
			'/some/path', $mediaModel->record['otherField'],
			'not the otherField of the original'
		);
		$this->assertEquals(
			'/some/other/path', $mediaModel->record['otherField2'],
			'not the otherField2 of the otherField2'
		);
		$this->assertEquals(
			'this is a sample picture', $mediaModel->record['description'],
			'not the description of the original'
		);
	}
}